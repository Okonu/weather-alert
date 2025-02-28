<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'lat',
        'lon',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'precipitation_enabled',
                'uv_enabled',
                'precipitation_threshold',
                'uv_threshold',
            ])
            ->withTimestamps();
    }
}
