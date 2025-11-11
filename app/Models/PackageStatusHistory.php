<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'package_status_history';

    protected $fillable = [
        'package_id',
        'status',
        'changed_by_user_id',
        'changed_by_type',
        'notes',
        'latitude',
        'longitude',
        'created_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
