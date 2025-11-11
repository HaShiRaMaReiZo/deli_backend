<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:merchant,rider',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => 'active',
        ]);

        // Create merchant or rider profile
        if ($request->role === 'merchant') {
            $request->validate([
                'business_name' => 'required|string|max:255',
                'business_address' => 'required|string',
                'business_phone' => 'required|string',
                'business_email' => 'required|email',
            ]);

            Merchant::create([
                'user_id' => $user->id,
                'business_name' => $request->business_name,
                'business_address' => $request->business_address,
                'business_phone' => $request->business_phone,
                'business_email' => $request->business_email,
                'status' => 'pending',
            ]);
        } elseif ($request->role === 'rider') {
            $request->validate([
                'vehicle_type' => 'required|in:bike,motorcycle,car,van',
                'phone' => 'required|string',
            ]);

            Rider::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'license_number' => $request->license_number,
                'status' => 'offline',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load($request->role);
        
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Account is not active',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load($user->role);
        
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $user->load($user->role);
        
        return response()->json([
            'user' => $user,
        ]);
    }
}
