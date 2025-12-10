<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores income setting and returns hourly rate for annual input', function () {
    $response = $this->postJson('/api/income-settings', [
        'income_type' => 'annual',
        'amount' => 6000000,
    ]);

    $response->assertCreated();
    $response->assertJson([
        'hourly_rate' => 3125.0,
    ]);

    $this->assertDatabaseHas('income_settings', [
        'income_type' => 'annual',
        'amount' => 6000000,
        'hourly_rate' => 3125.00,
    ]);
});
