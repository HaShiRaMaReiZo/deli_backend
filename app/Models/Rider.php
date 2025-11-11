<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rider extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'zone_id',
        'status',
        'current_latitude',
        'current_longitude',
        'last_location_update',
        'rating',
        'total_deliveries',
    ];

    protected $casts = [
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'last_location_update' => 'datetime',
        'rating' => 'decimal:2',
        'total_deliveries' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'current_rider_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RiderAssignment::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(RiderLocation::class);
    }
}
