<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\YaranaiItem;
use Illuminate\Http\Request;

class YaranaiItemController extends Controller
{
    // GET /api/yaranai-items
    public function index(Request $request)
    {
        // 認証導入までは user_id 無しで全件返す
        $items = YaranaiItem::orderBy('id')->get();

        return response()->json($items);
    }

    // POST /api/yaranai-items （今後のために雛形だけ）
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $item = YaranaiItem::create($validated);

        return response()->json($item, 201);
    }
}
