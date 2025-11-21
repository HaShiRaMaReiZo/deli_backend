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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use PDOException;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->user()->merchant;
        
        // Only load necessary relationships - statusHistory is heavy and not needed for list view
        // Only load specific columns to reduce data transfer
        // Exclude draft packages from regular list
        $packages = Package::where('merchant_id', $merchant->id)
            ->where('is_draft', false)
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
        try {
            // Ensure output buffer is clean
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Normalize empty strings to null for optional fields
            // Also handle invalid email formats (if it doesn't contain @, treat as null)
            $packages = $request->packages;
            foreach ($packages as &$package) {
                // Handle customer_email - if empty or doesn't look like an email, set to null
                if (isset($package['customer_email'])) {
                    $email = trim($package['customer_email']);
                    if (empty($email) || strpos($email, '@') === false) {
                        $package['customer_email'] = null;
                    }
                }
                // Handle package_description - if empty, set to null
                if (isset($package['package_description']) && empty(trim($package['package_description']))) {
                    $package['package_description'] = null;
                }
            }
            $request->merge(['packages' => $packages]);
            
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
            $imageUploadErrors = []; // Track image upload errors

            foreach ($packages as $index => $packageData) {
                try {
                    // Generate unique tracking code
                    $trackingCode = TrackingCodeService::generate();

                    // Handle package image (base64 to Supabase)
                    $packageImageUrl = null;
                    $imageError = null;
                    if (!empty($packageData['package_image'])) {
                        try {
                            $base64String = $packageData['package_image'];
                            
                            // Remove data URL prefix if present (e.g., "data:image/jpeg;base64,")
                            if (strpos($base64String, ',') !== false) {
                                $base64String = explode(',', $base64String, 2)[1];
                            }
                            
                            // Decode base64 image
                            $imageData = base64_decode($base64String, true); // strict mode
                            
                            if ($imageData === false) {
                                $imageError = 'Failed to decode base64 image data';
                                Log::warning('Failed to decode base64 image', [
                                    'tracking_code' => $trackingCode,
                                    'base64_length' => strlen($packageData['package_image']),
                                    'base64_preview' => substr($packageData['package_image'], 0, 50) . '...'
                                ]);
                            } elseif (strlen($imageData) === 0) {
                                $imageError = 'Decoded image data is empty';
                                Log::warning('Decoded image data is empty', [
                                    'tracking_code' => $trackingCode
                                ]);
                            } else {
                                // Check if Supabase is configured
                                $supabaseUrl = env('SUPABASE_URL');
                                $supabaseKey = env('SUPABASE_KEY');
                                
                                if (empty($supabaseUrl) || empty($supabaseKey)) {
                                    $imageError = 'Supabase not configured (missing SUPABASE_URL or SUPABASE_KEY)';
                                    Log::error('Supabase credentials missing', [
                                        'tracking_code' => $trackingCode,
                                        'supabase_url_set' => !empty($supabaseUrl),
                                        'supabase_key_set' => !empty($supabaseKey)
                                    ]);
                                } else {
                                    // Generate unique filename
                                    $filename = 'package_' . $trackingCode . '_' . time() . '_' . uniqid() . '.jpg';
                                    $path = 'package_images/' . $filename;
                                    
                                    // Upload to Supabase Storage
                                    $supabase = new SupabaseStorageService();
                                    $supabaseErrorMessage = null;
                                    $packageImageUrl = $supabase->upload($path, $imageData, $supabaseErrorMessage);
                                    
                                    if ($packageImageUrl) {
                                        Log::info('Package image uploaded to Supabase', [
                                            'tracking_code' => $trackingCode,
                                            'path' => $path,
                                            'url' => $packageImageUrl,
                                            'size' => strlen($imageData)
                                        ]);
                                    } else {
                                        // Sanitize error message to ensure valid UTF-8 for JSON encoding
                                        $sanitizedError = $supabaseErrorMessage ?? 'Supabase upload failed (unknown error)';
                                        $sanitizedError = mb_convert_encoding($sanitizedError, 'UTF-8', 'UTF-8');
                                        $sanitizedError = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $sanitizedError);
                                        $imageError = substr($sanitizedError, 0, 200);
                                        
                                        Log::warning('Failed to upload package image to Supabase', [
                                            'tracking_code' => $trackingCode,
                                            'path' => $path,
                                            'image_size' => strlen($imageData),
                                            'supabase_url' => $supabaseUrl,
                                            'supabase_bucket' => env('SUPABASE_BUCKET', 'package-images'),
                                            'error' => $imageError
                                        ]);
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            // Sanitize exception message to ensure valid UTF-8 for JSON encoding
                            $exceptionMsg = mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8');
                            $exceptionMsg = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $exceptionMsg);
                            $imageError = 'Exception: ' . substr($exceptionMsg, 0, 200);
                            
                            Log::error('Failed to upload package image', [
                                'tracking_code' => $trackingCode,
                                'error' => $imageError,
                            ]);
                        }
                        
                        // Store image error for this package
                        if ($imageError) {
                            $imageUploadErrors[] = [
                                'tracking_code' => $trackingCode,
                                'error' => $imageError
                            ];
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
                'image_upload_errors' => $imageUploadErrors, // Include image upload errors
            ], count($createdPackages) > 0 ? 201 : 422)->header('Content-Type', 'application/json');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ensure output buffer is clean
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error_details' => 'Please check the validation errors and correct the input data.',
            ], 422)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            Log::error('Bulk package creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'An error occurred while creating packages',
                'error' => $e->getMessage(),
            ], 500)->header('Content-Type', 'application/json');
        }
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

    /**
     * Save packages as drafts (without tracking codes or status)
     */
    public function saveDraft(Request $request)
    {
        // Ensure we return JSON for API routes
        $request->headers->set('Accept', 'application/json');
        
        try {
            // Normalize empty strings to null for optional fields
            $packages = $request->packages;
            
            if (!is_array($packages) || empty($packages)) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => ['packages' => ['The packages field is required and must be a non-empty array.']],
                ], 422, ['Content-Type' => 'application/json']);
            }
            foreach ($packages as &$package) {
                if (isset($package['customer_email'])) {
                    $email = trim($package['customer_email']);
                    if (empty($email) || strpos($email, '@') === false) {
                        $package['customer_email'] = null;
                    }
                }
                if (isset($package['package_description']) && empty(trim($package['package_description']))) {
                    $package['package_description'] = null;
                }
            }
            $request->merge(['packages' => $packages]);

            $request->validate([
                'packages' => 'required|array|min:1|max:50',
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
            $createdDrafts = [];
            $errors = [];
            $imageUploadErrors = [];

            foreach ($packages as $index => $packageData) {
                try {
                    // Handle package image (base64 to Supabase)
                    $packageImageUrl = null;
                    $imageError = null;
                    if (!empty($packageData['package_image'])) {
                        try {
                            $base64String = $packageData['package_image'];
                            
                            if (strpos($base64String, ',') !== false) {
                                $base64String = explode(',', $base64String, 2)[1];
                            }
                            
                            $imageData = base64_decode($base64String, true);
                            
                            if ($imageData === false) {
                                $imageError = 'Failed to decode base64 image data';
                            } else {
                                $supabaseService = new SupabaseStorageService();
                                $packageImageUrl = $supabaseService->uploadPackageImage($imageData);
                            }
                        } catch (\Exception $e) {
                            $imageError = 'Failed to upload image: ' . $e->getMessage();
                            Log::warning('Draft image upload error', [
                                'index' => $index,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    // Create draft package (no tracking code, no status, is_draft = true)
                    try {
                        $package = Package::create([
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
                            'is_draft' => true,
                            // No tracking_code, no status - these will be set when submitting
                        ]);
                    } catch (QueryException $dbEx) {
                        // Re-throw to be caught by outer handler
                        throw $dbEx;
                    } catch (PDOException $pdoEx) {
                        // Re-throw to be caught by outer handler
                        throw $pdoEx;
                    } catch (\Exception $ex) {
                        // Re-throw to be caught by outer handler
                        throw $ex;
                    }

                    if ($imageError) {
                        $imageUploadErrors[] = [
                            'index' => $index,
                            'customer_name' => $packageData['customer_name'],
                            'error' => $imageError,
                        ];
                    }

                    $createdDrafts[] = $package;
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'customer_name' => $packageData['customer_name'] ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'message' => count($createdDrafts) . ' draft package(s) saved successfully',
                'created_count' => count($createdDrafts),
                'failed_count' => count($errors),
                'packages' => $createdDrafts,
                'errors' => $errors,
                'image_upload_errors' => $imageUploadErrors,
            ], count($createdDrafts) > 0 ? 201 : 422)->header('Content-Type', 'application/json');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ensure output buffer is clean
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422)->header('Content-Type', 'application/json');
        } catch (QueryException $e) {
            // Ensure output buffer is clean
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            Log::error('Draft package save database error', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'code' => $e->getCode(),
            ]);
            
            // Check if it's a constraint violation (likely tracking_code NOT NULL)
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'tracking_code') !== false || 
                strpos($errorMessage, 'null value') !== false ||
                strpos($errorMessage, 'NOT NULL') !== false ||
                $e->getCode() == '23502') { // PostgreSQL NOT NULL violation code
                return response()->json([
                    'message' => 'Database error: Please run migrations. The tracking_code column needs to be nullable for drafts.',
                    'error' => 'Database constraint violation. Migrations may not have been run.',
                    'details' => 'Error: ' . $errorMessage,
                ], 500, ['Content-Type' => 'application/json']);
            }
            
            return response()->json([
                'message' => 'Database error occurred while saving drafts',
                'error' => $errorMessage,
            ], 500, ['Content-Type' => 'application/json']);
        } catch (PDOException $e) {
            // Ensure output buffer is clean
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            Log::error('Draft package save PDO error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'tracking_code') !== false || 
                strpos($errorMessage, 'null value') !== false ||
                strpos($errorMessage, 'NOT NULL') !== false) {
                return response()->json([
                    'message' => 'Database error: Please run migrations. The tracking_code column needs to be nullable for drafts.',
                    'error' => 'Database constraint violation. Migrations may not have been run.',
                    'details' => 'Error: ' . $errorMessage,
                ], 500, ['Content-Type' => 'application/json']);
            }
            
            return response()->json([
                'message' => 'Database error occurred while saving drafts',
                'error' => $errorMessage,
            ], 500, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            // Ensure output buffer is clean
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            Log::error('Draft package save error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'An error occurred while saving drafts',
                'error' => $e->getMessage(),
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Get all draft packages for the merchant
     */
    public function getDrafts(Request $request)
    {
        $merchant = $request->user()->merchant;
        
        $drafts = Package::where('merchant_id', $merchant->id)
            ->where('is_draft', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($drafts);
    }

    /**
     * Submit draft packages to the system (convert to regular packages)
     */
    public function submitDrafts(Request $request)
    {
        try {
            $request->validate([
                'package_ids' => 'required|array|min:1',
                'package_ids.*' => 'required|integer|exists:packages,id',
            ]);

            $merchant = $request->user()->merchant;
            $packageIds = $request->package_ids;
            
            // Get draft packages that belong to this merchant
            $draftPackages = Package::where('merchant_id', $merchant->id)
                ->where('is_draft', true)
                ->whereIn('id', $packageIds)
                ->get();

            if ($draftPackages->isEmpty()) {
                return response()->json([
                    'message' => 'No valid draft packages found',
                ], 404)->header('Content-Type', 'application/json');
            }

            $submittedPackages = [];
            $errors = [];

            foreach ($draftPackages as $package) {
                try {
                    // Generate tracking code
                    $trackingCode = TrackingCodeService::generate();

                    // Update package: set tracking code, status, and is_draft = false
                    $package->update([
                        'tracking_code' => $trackingCode,
                        'status' => 'registered',
                        'is_draft' => false,
                    ]);

                    // Create initial status history entry
                    PackageStatusHistory::create([
                        'package_id' => $package->id,
                        'status' => 'registered',
                        'changed_by_id' => $merchant->user_id,
                        'changed_by_type' => 'merchant',
                        'created_at' => now(),
                    ]);

                    // Broadcast status change via WebSocket
                    event(new PackageStatusChanged($package->id, 'registered', $package->merchant_id));

                    $submittedPackages[] = $package;
                } catch (\Exception $e) {
                    $errors[] = [
                        'package_id' => $package->id,
                        'customer_name' => $package->customer_name,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'message' => count($submittedPackages) . ' package(s) submitted successfully',
                'submitted_count' => count($submittedPackages),
                'failed_count' => count($errors),
                'packages' => $submittedPackages,
                'errors' => $errors,
            ], count($submittedPackages) > 0 ? 200 : 422)->header('Content-Type', 'application/json');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ensure output buffer is clean
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // Ensure output buffer is clean
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            Log::error('Draft submission error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'An error occurred while submitting drafts',
                'error' => $e->getMessage(),
            ], 500)->header('Content-Type', 'application/json');
        }
    }
}
