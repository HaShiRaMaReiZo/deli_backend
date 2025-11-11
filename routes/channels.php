<?php

use Illuminate\Support\Facades\Broadcast;

// Office can always see all riders' locations
Broadcast::channel('office.riders.locations', function ($user) {
    return in_array($user->role, ['super_admin', 'office_manager', 'office_staff']);
});

// Office can see all package updates
Broadcast::channel('office.packages', function ($user) {
    return in_array($user->role, ['super_admin', 'office_manager', 'office_staff']);
});

// Merchant can see their own package updates
Broadcast::channel('merchant.{merchantId}', function ($user, $merchantId) {
    return $user->role === 'merchant' && $user->merchant && $user->merchant->id == $merchantId;
});

// Merchant can see rider location for specific package (only when status = on_the_way)
Broadcast::channel('merchant.package.{packageId}.location', function ($user, $packageId) {
    if ($user->role !== 'merchant' || !$user->merchant) {
        return false;
    }
    
    $package = \App\Models\Package::find($packageId);
    if (!$package || $package->merchant_id !== $user->merchant->id) {
        return false;
    }
    
    // Only allow if status is on_the_way
    return $package->status === 'on_the_way';
});

