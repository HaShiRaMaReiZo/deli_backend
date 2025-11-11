<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Support\Str;

class TrackingCodeService
{
    /**
     * Generate a unique tracking code
     *
     * @return string
     */
    public static function generate(): string
    {
        do {
            $code = 'DELI-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Package::where('tracking_code', $code)->exists());

        return $code;
    }
}

