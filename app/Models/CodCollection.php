<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodCollection extends Model
{
    protected $fillable = [
        'package_id',
        'rider_id',
        'amount',
        'collected_at',
        'collection_proof',
        'status',
        'settled_at',
        'settled_by_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collected_at' => 'datetime',
        'settled_at' => 'datetime',
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

    public function settledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by_user_id');
    }
}
