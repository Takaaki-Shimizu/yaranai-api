<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailySaving;
use App\Models\IncomeSetting;
use App\Models\YaranaiItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailySavingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $targetDate = isset($validated['date'])
            ? Carbon::parse($validated['date'])->toDateString()
            : Carbon::today()->toDateString();

        $hourlyRate = IncomeSetting::orderByDesc('id')->value('hourly_rate');

        if ($hourlyRate === null) {
            return response()->json([
                'message' => '時給が登録されていません。先に収入設定を登録してください。',
            ], 422);
        }

        $hoursSaved = (float) YaranaiItem::sum('hours_per_day');

        if ($hoursSaved <= 0) {
            return response()->json([
                'message' => 'やらないことが登録されていないため、節約時間を計算できません。',
            ], 422);
        }

        $amountSaved = round($hoursSaved * $hourlyRate, 2);

        $saving = DailySaving::updateOrCreate(
            ['date' => $targetDate],
            [
                'hourly_rate' => $hourlyRate,
                'hours_saved' => round($hoursSaved, 2),
                'amount_saved' => $amountSaved,
            ]
        );

        return response()->json([
            'date' => $saving->date->toDateString(),
            'hourly_rate' => $saving->hourly_rate,
            'hours_saved' => $saving->hours_saved,
            'amount_saved' => $saving->amount_saved,
        ], 201);
    }

    public function preview(): JsonResponse
    {
        $hourlyRate = IncomeSetting::orderByDesc('id')->value('hourly_rate');

        if ($hourlyRate === null) {
            return response()->json([
                'message' => '時給が登録されていません。先に収入設定を登録してください。',
            ], 404);
        }

        $hoursPerDay = (float) YaranaiItem::sum('hours_per_day');

        if ($hoursPerDay <= 0) {
            return response()->json([
                'message' => 'やらないことが登録されていないため、節約時間を計算できません。',
            ], 422);
        }

        $amountSavedPerDay = round($hourlyRate * $hoursPerDay, 2);

        return response()->json([
            'hourly_rate' => (float) $hourlyRate,
            'hours_saved_per_day' => round($hoursPerDay, 2),
            'amount_saved_per_day' => $amountSavedPerDay,
        ]);
    }
}
