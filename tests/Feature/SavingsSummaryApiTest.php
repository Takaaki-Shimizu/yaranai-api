<?php

use App\Models\IncomeSetting;
use App\Models\YaranaiItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('returns savings summary including monthly, yearly, and all time hours', function () {
    Carbon::setTestNow('2024-01-10');

    IncomeSetting::create([
        'income_type' => 'hourly',
        'amount' => 2000,
        'hourly_rate' => 2000,
    ]);

    $itemRecent = YaranaiItem::create([
        'title' => 'No bedtime phone',
        'description' => 'Stop doomscrolling',
        'hours_per_day' => 1.0,
    ]);
    $itemRecent->created_at = Carbon::parse('2024-01-05');
    $itemRecent->save();

    $itemEarlier = YaranaiItem::create([
        'title' => 'Pause license study',
        'description' => 'Suspend Takken study',
        'hours_per_day' => 2.0,
    ]);
    $itemEarlier->created_at = Carbon::parse('2023-12-25');
    $itemEarlier->save();

    $response = $this->getJson('/api/savings-summary');

    $response->assertOk();
    $response->assertJson([
        'hourly_rate' => 2000.0,
        'amount_saved_total' => 80000.0,
        'hours_saved' => [
            'this_month' => 26.0,
            'this_year' => 26.0,
            'all_time' => 40.0,
        ],
    ]);
});

it('returns 422 if no income setting exists when requesting summary', function () {
    $response = $this->getJson('/api/savings-summary');

    $response->assertStatus(422);
    $response->assertJson([
        'message' => '時給が登録されていません。先に収入設定を登録してください。',
    ]);
});

it('returns 422 if no yaranai items exist when requesting summary', function () {
    IncomeSetting::create([
        'income_type' => 'hourly',
        'amount' => 2500,
        'hourly_rate' => 2500,
    ]);

    $response = $this->getJson('/api/savings-summary');

    $response->assertStatus(422);
    $response->assertJson([
        'message' => 'やらないことが登録されていないため、節約時間を計算できません。',
    ]);
});
