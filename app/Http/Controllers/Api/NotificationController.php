<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Register FCM token for push notifications
     */
    public function registerToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
            'device_type' => 'nullable|string|in:mobile,web',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $fcmToken = $request->input('fcm_token');
        $deviceType = $request->input('device_type', 'mobile');

        // Store or update FCM token
        DB::table('fcm_tokens')->updateOrInsert(
            [
                'user_id' => $user->id,
                'fcm_token' => $fcmToken,
            ],
            [
                'device_type' => $deviceType,
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        return response()->json([
            'message' => 'FCM token registered successfully',
        ]);
    }

    /**
     * Unregister FCM token
     */
    public function unregisterToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $fcmToken = $request->input('fcm_token');

        // Delete FCM token
        DB::table('fcm_tokens')
            ->where('user_id', $user->id)
            ->where('fcm_token', $fcmToken)
            ->delete();

        return response()->json([
            'message' => 'FCM token unregistered successfully',
        ]);
    }
}

