<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryProof extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'package_id',
        'rider_id',
        'proof_type',
        'proof_data',
        'delivery_latitude',
        'delivery_longitude',
        'delivered_to_name',
        'delivered_to_phone',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }
}
