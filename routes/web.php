<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\OfficeController;

Route::get('/', function () {
	// If user is authenticated and has office role, redirect to office dashboard
	if (auth()->check()) {
		$user = auth()->user();
		$officeRoles = ['super_admin', 'office_manager', 'office_staff'];
		if (in_array($user->role, $officeRoles)) {
			return redirect()->route('office.dashboard');
		}
	}
	return view('welcome');
});

// Temporary route to seed users (REMOVE AFTER FIRST USE)
Route::get('/seed-users', function () {
	try {
		Artisan::call('db:seed', ['--class' => 'OfficeUserSeeder', '--force' => true]);
		$output = Artisan::output();
		return response()->json([
			'success' => true,
			'message' => 'Users seeded successfully!',
			'output' => $output,
			'users' => [
				'Super Admin: erickboyle@superadmin.com / erick2004',
				'Office Manager: manager@delivery.com / manager123',
				'Office Staff: staff@delivery.com / staff123'
			]
		]);
	} catch (\Exception $e) {
		return response()->json([
			'success' => false,
			'message' => 'Error seeding users: ' . $e->getMessage()
		], 500);
	}
});

// Office Authentication
Route::prefix('office')->group(function () {
	Route::get('/login', [AuthController::class, 'showLogin'])->name('office.login')->middleware('guest');
	Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
	
	// Protected routes
	Route::middleware('auth')->group(function () {
		Route::post('/logout', [AuthController::class, 'logout'])->name('office.logout');
		Route::get('/', [OfficeController::class, 'dashboard'])->name('office.dashboard');
		Route::get('/packages', [OfficeController::class, 'packages'])->name('office.packages');
		Route::get('/packages/registered-by-merchant', [OfficeController::class, 'registeredPackagesByMerchant'])->name('office.registered_packages_by_merchant');
		Route::get('/packages/picked-up', [OfficeController::class, 'pickedUpPackages'])->name('office.picked_up_packages');
		Route::get('/riders', [OfficeController::class, 'riders'])->name('office.riders');
		Route::get('/map', [OfficeController::class, 'map'])->name('office.map');
		Route::get('/register-user', [OfficeController::class, 'showRegisterUser'])->name('office.register_user');
		Route::post('/register-user', [OfficeController::class, 'registerUser'])->name('office.register_user.post');
	});
});
