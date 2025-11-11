<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $fillable = [
        'name',
        'description',
        'boundaries',
        'status',
    ];

    protected $casts = [
        'boundaries' => 'array',
    ];

    // Relationships
    public function riders(): HasMany
    {
        return $this->hasMany(Rider::class);
    }
}
