<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderAssignment extends Model
{
    protected $fillable = [
        'package_id',
        'rider_id',
        'assigned_by_user_id',
        'assigned_at',
        'delivery_sequence',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'delivery_sequence' => 'integer',
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

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }
}
