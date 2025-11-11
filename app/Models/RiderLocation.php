<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderLocation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'rider_id',
        'package_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'created_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
