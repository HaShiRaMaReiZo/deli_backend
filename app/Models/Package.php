<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'tracking_code',
        'merchant_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'payment_type',
        'amount',
        'package_image',
        'package_description',
        'status',
        'is_draft',
        'current_rider_id',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'delivery_attempts',
        'delivery_notes',
    ];

    protected $casts = [
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'amount' => 'decimal:2',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'delivery_attempts' => 'integer',
    ];

    // Relationships
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function currentRider(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'current_rider_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(PackageStatusHistory::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RiderAssignment::class);
    }

    public function deliveryProof(): HasMany
    {
        return $this->hasMany(DeliveryProof::class);
    }

    public function codCollection(): HasMany
    {
        return $this->hasMany(CodCollection::class);
    }
}
