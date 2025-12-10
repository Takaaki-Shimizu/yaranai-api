<?php

use App\Models\YaranaiItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('creates a yaranai item with AI-estimated hours per day', function () {
    config([
        'ai.openai.api_key' => 'test-key',
        'ai.openai.model' => 'gpt-4o-mini',
        'ai.openai.api_url' => 'https://api.openai.test/v1/chat/completions',
    ]);

    Http::fake([
        'https://api.openai.test/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'hours_per_day' => 1.0,
                            'reason' => 'Short form videos typically consume about one hour before bed.',
                        ]),
                    ],
                ],
            ],
        ], 200),
    ]);

    $response = $this->postJson('/api/yaranai-items', [
        'title' => 'Avoid phone before bed',
        'description' => 'Stop watching short-form videos before sleep.',
    ]);

    $response->assertCreated();
    $response->assertJson([
        'title' => 'Avoid phone before bed',
        'hours_per_day' => 1.0,
    ]);

    $this->assertDatabaseHas('yaranai_items', [
        'title' => 'Avoid phone before bed',
        'description' => 'Stop watching short-form videos before sleep.',
        'hours_per_day' => 1.0,
    ]);
});

it('deletes a yaranai item', function () {
    $item = YaranaiItem::create([
        'title' => 'Delete me',
        'description' => 'Temporary item',
        'hours_per_day' => 1.0,
    ]);

    $response = $this->deleteJson("/api/yaranai-items/{$item->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('yaranai_items', ['id' => $item->id]);
});

it('updates a yaranai item', function () {
    $item = YaranaiItem::create([
        'title' => 'Original title',
        'description' => 'Old description',
        'hours_per_day' => 0.5,
    ]);

    $response = $this->putJson("/api/yaranai-items/{$item->id}", [
        'title' => 'Updated title',
        'description' => 'New description',
    ]);

    $response->assertOk();
    $response->assertJson([
        'id' => $item->id,
        'title' => 'Updated title',
        'description' => 'New description',
    ]);

    $this->assertDatabaseHas('yaranai_items', [
        'id' => $item->id,
        'title' => 'Updated title',
        'description' => 'New description',
    ]);
});
