<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already authenticated, redirect to dashboard
        if (auth()->check()) {
            $user = auth()->user();
            $officeRoles = ['super_admin', 'office_manager', 'office_staff'];
            if (in_array($user->role, $officeRoles)) {
                return redirect()->route('office.dashboard');
            }
        }
        return view('office.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ])->withInput();
        }

        // Check if user has office role
        $officeRoles = ['super_admin', 'office_manager', 'office_staff'];
        if (!in_array($user->role, $officeRoles)) {
            return back()->withErrors([
                'email' => 'You do not have access to the office panel.',
            ])->withInput();
        }

        if ($user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Your account is not active.',
            ])->withInput();
        }

        // Create session
        auth()->login($user, $request->has('remember'));

        // Redirect to office dashboard
        return redirect()->route('office.dashboard');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/office/login');
    }
}

