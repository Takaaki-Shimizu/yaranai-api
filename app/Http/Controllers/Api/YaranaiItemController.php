<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\YaranaiItem;
use App\Services\Ai\DailyHourEstimator;
use Illuminate\Http\Request;
use RuntimeException;

class YaranaiItemController extends Controller
{
    public function __construct(
        private DailyHourEstimator $hourEstimator
    ) {
    }

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

        try {
            $hoursPerDay = $this->hourEstimator->estimateHoursPerDay(
                $validated['title'],
                $validated['description'] ?? ''
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => 'AIの予測に失敗しました: ' . $exception->getMessage(),
            ], 503);
        }

        $item = YaranaiItem::create([
            ...$validated,
            'hours_per_day' => $hoursPerDay,
        ]);

        return response()->json($item, 201);
    }

    // PUT /api/yaranai-items/{yaranaiItem}
    public function update(Request $request, YaranaiItem $yaranaiItem)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $yaranaiItem->update($validated);

        return response()->json($yaranaiItem->refresh());
    }

    // DELETE /api/yaranai-items/{yaranaiItem}
    public function destroy(YaranaiItem $yaranaiItem)
    {
        $yaranaiItem->delete();

        return response()->noContent();
    }
}
