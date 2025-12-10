<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySaving extends Model
{
    protected $fillable = [
        'date',
        'hourly_rate',
        'hours_saved',
        'amount_saved',
    ];

    protected $casts = [
        'date' => 'date',
        'hourly_rate' => 'float',
        'hours_saved' => 'float',
        'amount_saved' => 'float',
    ];
}
