<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomeSetting;
use App\Models\YaranaiItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class SavingsSummaryController extends Controller
{
    public function show(): JsonResponse
    {
        $hourlyRate = IncomeSetting::orderByDesc('id')->value('hourly_rate');

        if ($hourlyRate === null) {
            return response()->json([
                'message' => '時給が登録されていません。先に収入設定を登録してください。',
            ], 422);
        }

        $items = YaranaiItem::where('hours_per_day', '>', 0)->get();

        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'やらないことが登録されていないため、節約時間を計算できません。',
            ], 422);
        }

        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $yearStart = $today->copy()->startOfYear();

        $hoursAllTime = 0.0;
        $hoursThisMonth = 0.0;
        $hoursThisYear = 0.0;

        foreach ($items as $item) {
            $created = $item->created_at?->copy()->startOfDay() ?? $today->copy();
            $hoursPerDay = (float) $item->hours_per_day;

            if ($hoursPerDay <= 0) {
                continue;
            }

            $hoursAllTime += $hoursPerDay * $this->calculateActiveDays($created, $today);
            $hoursThisMonth += $hoursPerDay * $this->calculateActiveDays(
                $this->maxDate($created, $monthStart),
                $today
            );
            $hoursThisYear += $hoursPerDay * $this->calculateActiveDays(
                $this->maxDate($created, $yearStart),
                $today
            );
        }

        $hoursAllTime = round($hoursAllTime, 2);
        $hoursThisMonth = round($hoursThisMonth, 2);
        $hoursThisYear = round($hoursThisYear, 2);

        $amountSaved = round($hoursAllTime * $hourlyRate, 2);

        return response()->json([
            'hourly_rate' => (float) $hourlyRate,
            'amount_saved_total' => $amountSaved,
            'hours_saved' => [
                'this_month' => $hoursThisMonth,
                'this_year' => $hoursThisYear,
                'all_time' => $hoursAllTime,
            ],
        ]);
    }

    private function calculateActiveDays(Carbon $start, Carbon $end): int
    {
        if ($start->gt($end)) {
            return 0;
        }

        return $start->diffInDays($end) + 1;
    }

    private function maxDate(Carbon $candidate, Carbon $limit): Carbon
    {
        return $candidate->greaterThan($limit) ? $candidate->copy() : $limit->copy();
    }
}
