<?php

use App\Models\YaranaiItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes a yaranai item', function () {
    $item = YaranaiItem::create([
        'title' => 'Delete me',
        'description' => 'Temporary item',
    ]);

    $response = $this->deleteJson("/api/yaranai-items/{$item->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('yaranai_items', ['id' => $item->id]);
});

it('updates a yaranai item', function () {
    $item = YaranaiItem::create([
        'title' => 'Original title',
        'description' => 'Old description',
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
