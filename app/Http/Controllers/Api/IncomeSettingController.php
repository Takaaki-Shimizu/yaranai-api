<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomeSetting;
use Illuminate\Http\Request;

class IncomeSettingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'income_type' => ['required', 'in:annual,monthly,hourly'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $hourlyRate = $this->calculateHourlyRate(
            $validated['income_type'],
            (float) $validated['amount']
        );

        $roundedHourlyRate = round($hourlyRate, 2);

        IncomeSetting::create([
            'income_type' => $validated['income_type'],
            'amount' => $validated['amount'],
            'hourly_rate' => $roundedHourlyRate,
        ]);

        return response()->json([
            'hourly_rate' => $roundedHourlyRate,
        ], 201);
    }

    private function calculateHourlyRate(string $incomeType, float $amount): float
    {
        return match ($incomeType) {
            'annual' => $amount / 12 / 160,
            'monthly' => $amount / 160,
            default => $amount,
        };
    }
}
