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
