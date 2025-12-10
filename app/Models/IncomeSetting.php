<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeSetting extends Model
{
    protected $fillable = [
        'income_type',
        'amount',
        'hourly_rate',
    ];

    protected $casts = [
        'amount' => 'float',
        'hourly_rate' => 'float',
    ];
}
