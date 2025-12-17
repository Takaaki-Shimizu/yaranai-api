<?php

use App\Models\IncomeSetting;
use App\Models\YaranaiItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calculates and stores daily saving for the specified date', function () {
    IncomeSetting::create([
        'income_type' => 'hourly',
        'amount' => 2500,
        'hourly_rate' => 2500,
    ]);

    YaranaiItem::create([
        'title' => 'No short videos',
        'description' => 'Stop doomscrolling before bed',
        'hours_per_day' => 1.5,
    ]);

    YaranaiItem::create([
        'title' => 'No study',
        'description' => 'Skip real estate license study',
        'hours_per_day' => 2.5,
    ]);

    $response = $this->postJson('/api/daily-savings', [
        'date' => '2024-12-01',
    ]);

    $response->assertCreated();
    $response->assertJson([
        'date' => '2024-12-01',
        'hourly_rate' => 2500.0,
        'hours_saved' => 4.0,
        'amount_saved' => 10000.0,
    ]);

    $this->assertDatabaseHas('daily_savings', [
        'date' => '2024-12-01 00:00:00',
        'hourly_rate' => 2500.00,
        'hours_saved' => 4.00,
        'amount_saved' => 10000.00,
    ]);
});

it('returns 422 when no income setting exists', function () {
    $response = $this->postJson('/api/daily-savings');

    $response->assertStatus(422);
    $response->assertJson([
        'message' => '時給が登録されていません。先に収入設定を登録してください。',
    ]);
});

it('returns 422 when no yaranai items exist', function () {
    IncomeSetting::create([
        'income_type' => 'hourly',
        'amount' => 1500,
        'hourly_rate' => 1500,
    ]);

    $response = $this->postJson('/api/daily-savings');

    $response->assertStatus(422);
    $response->assertJson([
        'message' => 'やらないことが登録されていないため、節約時間を計算できません。',
    ]);
});

it('previews daily savings using latest income setting and yaranai items', function () {
    IncomeSetting::create([
        'income_type' => 'hourly',
        'amount' => 2000,
        'hourly_rate' => 2000,
    ]);

    YaranaiItem::create([
        'title' => 'Cut social media',
        'description' => 'Reduce doomscrolling',
        'hours_per_day' => 1.5,
    ]);

    YaranaiItem::create([
        'title' => 'Skip errands',
        'description' => 'Outsource chores',
        'hours_per_day' => 0.5,
    ]);

    $response = $this->getJson('/api/daily-savings/preview');

    $response->assertOk();
    $response->assertJson([
        'hourly_rate' => 2000.0,
        'hours_saved_per_day' => 2.0,
        'amount_saved_per_day' => 4000.0,
    ]);
});

it('returns 404 when preview is requested without an income setting', function () {
    $response = $this->getJson('/api/daily-savings/preview');

    $response->assertNotFound();
    $response->assertJson([
        'message' => '時給が登録されていません。先に収入設定を登録してください。',
    ]);
});

it('returns 422 when preview is requested without yaranai items', function () {
    IncomeSetting::create([
        'income_type' => 'hourly',
        'amount' => 1800,
        'hourly_rate' => 1800,
    ]);

    $response = $this->getJson('/api/daily-savings/preview');

    $response->assertStatus(422);
    $response->assertJson([
        'message' => 'やらないことが登録されていないため、節約時間を計算できません。',
    ]);
});
