<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'vehicle_type',
        'pickup',
        'destination',
        'distance_km',
        'price',
        'free_wheels',
        'unlocked_gearbox',
        'empty_load',
    ];
}
