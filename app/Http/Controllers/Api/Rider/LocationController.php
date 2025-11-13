<?php

namespace App\Http\Controllers\Api\Rider;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\RiderLocation;
use App\Events\RiderLocationUpdated;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
            'package_id' => 'nullable|exists:packages,id',
        ]);

        $rider = $request->user()->rider;

        // Update rider's current location
        $rider->current_latitude = $request->latitude;
        $rider->current_longitude = $request->longitude;
        $rider->last_location_update = now();
        
        // Set rider status to 'available' if they're offline (they're using the app)
        if ($rider->status === 'offline') {
            $rider->status = 'available';
        }
        
        $rider->save();

        // Store location history
        RiderLocation::create([
            'rider_id' => $rider->id,
            'package_id' => $request->package_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'created_at' => now(),
        ]);

        // Broadcast location update via WebSocket
        event(new RiderLocationUpdated(
            $rider->id,
            $request->latitude,
            $request->longitude,
            $request->package_id
        ));

        return response()->json([
            'message' => 'Location updated successfully',
            'location' => [
                'latitude' => $rider->current_latitude,
                'longitude' => $rider->current_longitude,
                'last_update' => $rider->last_location_update,
            ],
        ]);
    }

    public function current(Request $request)
    {
        $rider = $request->user()->rider;

        return response()->json([
            'location' => [
                'latitude' => $rider->current_latitude,
                'longitude' => $rider->current_longitude,
                'last_update' => $rider->last_location_update,
            ],
        ]);
    }
}
