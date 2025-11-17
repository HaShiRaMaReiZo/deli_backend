<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageStatusHistory;
use App\Services\TrackingCodeService;
use App\Services\SupabaseStorageService;
use App\Events\PackageStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->user()->merchant;
        
        // Only load necessary relationships - statusHistory is heavy and not needed for list view
        // Only load specific columns to reduce data transfer
        $packages = Package::where('merchant_id', $merchant->id)
            ->with(['currentRider:id,name,phone', 'statusHistory' => function ($query) {
                // Only get the latest status history entry for each package
                $query->orderBy('created_at', 'desc')->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($packages);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'packages' => 'required|array|min:1|max:50', // Limit to 50 packages per request
            'packages.*.customer_name' => 'required|string|max:255',
            'packages.*.customer_phone' => 'required|string|max:20',
            'packages.*.customer_email' => 'nullable|email',
            'packages.*.delivery_address' => 'required|string',
            'packages.*.delivery_latitude' => 'nullable|numeric',
            'packages.*.delivery_longitude' => 'nullable|numeric',
            'packages.*.payment_type' => 'required|in:cod,prepaid',
            'packages.*.amount' => 'required|numeric|min:0',
            'packages.*.package_description' => 'nullable|string',
            'packages.*.package_image' => 'nullable|string', // Base64 encoded image
        ]);

        $merchant = $request->user()->merchant;
        $packages = $request->packages;
        $createdPackages = [];
        $errors = [];

        foreach ($packages as $index => $packageData) {
            try {
                // Generate unique tracking code
                $trackingCode = TrackingCodeService::generate();

                // Handle package image (base64 to Supabase)
                $packageImageUrl = null;
                if (!empty($packageData['package_image'])) {
                    try {
                        // Decode base64 image
                        $imageData = base64_decode($packageData['package_image']);
                        if ($imageData !== false && strlen($imageData) > 0) {
                            // Generate unique filename
                            $filename = 'package_' . $trackingCode . '_' . time() . '_' . uniqid() . '.jpg';
                            $path = 'package_images/' . $filename;
                            
                            // Upload to Supabase Storage
                            $supabase = new SupabaseStorageService();
                            $packageImageUrl = $supabase->upload($path, $imageData);
                            
                            if ($packageImageUrl) {
                                Log::info('Package image uploaded to Supabase', [
                                    'tracking_code' => $trackingCode,
                                    'path' => $path,
                                    'url' => $packageImageUrl,
                                    'size' => strlen($imageData)
                                ]);
                            } else {
                                Log::warning('Failed to upload package image to Supabase', [
                                    'tracking_code' => $trackingCode,
                                    'path' => $path
                                ]);
                            }
                        } else {
                            Log::warning('Invalid base64 image data', [
                                'tracking_code' => $trackingCode
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Log error but don't fail package creation
                        Log::error('Failed to upload package image', [
                            'tracking_code' => $trackingCode,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }

                $package = Package::create([
                    'tracking_code' => $trackingCode,
                    'merchant_id' => $merchant->id,
                    'customer_name' => $packageData['customer_name'],
                    'customer_phone' => $packageData['customer_phone'],
                    'customer_email' => $packageData['customer_email'] ?? null,
                    'delivery_address' => $packageData['delivery_address'],
                    'delivery_latitude' => $packageData['delivery_latitude'] ?? null,
                    'delivery_longitude' => $packageData['delivery_longitude'] ?? null,
                    'payment_type' => $packageData['payment_type'],
                    'amount' => $packageData['amount'],
                    'package_image' => $packageImageUrl,
                    'package_description' => $packageData['package_description'] ?? null,
                    'status' => 'registered',
                ]);

                // Log status history
                PackageStatusHistory::create([
                    'package_id' => $package->id,
                    'status' => 'registered',
                    'changed_by_user_id' => $request->user()->id,
                    'changed_by_type' => 'merchant',
                    'created_at' => now(),
                ]);

                // Broadcast status change via WebSocket
                event(new PackageStatusChanged($package->id, 'registered', $package->merchant_id));

                $createdPackages[] = $package;
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'customer_name' => $packageData['customer_name'] ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => count($createdPackages) . ' package(s) created successfully',
            'created_count' => count($createdPackages),
            'failed_count' => count($errors),
            'packages' => $createdPackages,
            'errors' => $errors,
        ], count($createdPackages) > 0 ? 201 : 422);
    }

    public function show(Request $request, $id)
    {
        $merchant = $request->user()->merchant;
        
        $package = Package::where('merchant_id', $merchant->id)
            ->with(['merchant', 'currentRider', 'statusHistory', 'deliveryProof', 'codCollection'])
            ->findOrFail($id);

        return response()->json($package);
    }

    public function track(Request $request, $id)
    {
        $merchant = $request->user()->merchant;
        
        $package = Package::where('merchant_id', $merchant->id)
            ->with(['currentRider', 'statusHistory'])
            ->findOrFail($id);

        return response()->json([
            'package' => $package,
            'status_history' => $package->statusHistory()->orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function liveLocation(Request $request, $id)
    {
        $merchant = $request->user()->merchant;
        
        $package = Package::where('merchant_id', $merchant->id)
            ->with('currentRider')
            ->findOrFail($id);

        $rider = $package->currentRider;
        
        // If status is delivered, return last location from status history
        if ($package->status === 'delivered') {
            $lastStatusHistory = \App\Models\PackageStatusHistory::where('package_id', $package->id)
                ->where('status', 'delivered')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($lastStatusHistory && $lastStatusHistory->latitude && $lastStatusHistory->longitude) {
                return response()->json([
                    'rider' => $rider ? [
                        'id' => $rider->id,
                        'name' => $rider->name,
                        'latitude' => (float) $lastStatusHistory->latitude,
                        'longitude' => (float) $lastStatusHistory->longitude,
                        'last_update' => $lastStatusHistory->created_at,
                    ] : null,
                    'package' => [
                        'id' => $package->id,
                        'tracking_code' => $package->tracking_code,
                        'status' => $package->status,
                        'delivery_address' => $package->delivery_address,
                        'delivery_latitude' => $package->delivery_latitude,
                        'delivery_longitude' => $package->delivery_longitude,
                    ],
                    'is_live' => false, // Indicates this is last location, not live tracking
                ]);
            }
            
            // If no location in history, return error
            return response()->json([
                'message' => 'Package is delivered but no delivery location found',
                'location' => null,
                'package' => [
                    'id' => $package->id,
                    'tracking_code' => $package->tracking_code,
                    'status' => $package->status,
                ],
                'is_live' => false,
            ]);
        }

        // Only return live location if status is on_the_way
        if ($package->status !== 'on_the_way') {
            return response()->json([
                'message' => 'Package is not on the way',
                'location' => null,
                'package' => [
                    'id' => $package->id,
                    'tracking_code' => $package->tracking_code,
                    'status' => $package->status,
                ],
                'is_live' => false,
            ]);
        }
        
        if (!$rider) {
            return response()->json([
                'message' => 'No rider assigned',
                'location' => null,
                'package' => [
                    'id' => $package->id,
                    'tracking_code' => $package->tracking_code,
                    'status' => $package->status,
                ],
                'is_live' => false,
            ]);
        }

        return response()->json([
            'rider' => [
                'id' => $rider->id,
                'name' => $rider->name,
                'latitude' => $rider->current_latitude,
                'longitude' => $rider->current_longitude,
                'last_update' => $rider->last_location_update,
            ],
            'package' => [
                'id' => $package->id,
                'tracking_code' => $package->tracking_code,
                'status' => $package->status,
                'delivery_address' => $package->delivery_address,
                'delivery_latitude' => $package->delivery_latitude,
                'delivery_longitude' => $package->delivery_longitude,
            ],
            'is_live' => true, // Indicates this is live tracking
        ]);
    }

    public function history(Request $request, $id)
    {
        $merchant = $request->user()->merchant;
        
        $package = Package::where('merchant_id', $merchant->id)
            ->findOrFail($id);

        $history = PackageStatusHistory::where('package_id', $package->id)
            ->with('changedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }
}
