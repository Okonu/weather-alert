<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'city',
        'precipitation_enabled',
        'uv_enabled',
        'precipitation_threshold',
        'uv_threshold',
    ];

    protected $casts = [
        'precipitation_enabled' => 'boolean',
        'uv_enabled' => 'boolean',
        'precipitation_threshold' => 'float',
        'uv_threshold' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
