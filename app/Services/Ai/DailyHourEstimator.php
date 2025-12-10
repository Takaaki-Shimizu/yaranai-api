<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DailyHourEstimator
{
    public function estimateHoursPerDay(string $title, ?string $description = null): float
    {
        $apiKey = config('ai.openai.api_key');
        $model = config('ai.openai.model', 'gpt-4o-mini');
        $apiUrl = config('ai.openai.api_url', 'https://api.openai.com/v1/chat/completions');

        if (empty($apiKey)) {
            throw new RuntimeException('OpenAI APIキーが設定されていません。');
        }

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'あなたは生活改善コーチです。ユーザーが「やらない」と決めた行動から、1日あたり何時間節約できるかを推定してください。回答はJSONで返し、hours_per_dayキーには0〜24の数値（小数可）を入れてください。説明はreasonキーに短くまとめてください。説明の例：「ショート動画を寝る前に見ると平均60分かかるので1.0時間」。特定の資格勉強（例:宅建）は一般的な学習時間（宅建: 6ヶ月=180日×1日2時間=360時間など）を参考にし、日割りで平均値を出してください。'
                ],
                [
                    'role' => 'user',
                    'content' => $this->buildPrompt($title, $description)
                ],
            ],
            'temperature' => 0.2,
            'max_tokens' => 400,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($apiUrl, $payload);

        if (! $response->successful()) {
            Log::error('OpenAI API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('AI推定APIの呼び出しに失敗しました。');
        }

        $content = $response->json('choices.0.message.content');

        $hours = $this->extractHours($content);

        if ($hours === null) {
            throw new RuntimeException('AIレスポンスから時間を抽出できませんでした。');
        }

        return $hours;
    }

    private function buildPrompt(string $title, ?string $description): string
    {
        $lines = [
            "やらないこと: {$title}",
        ];

        if (! empty($description)) {
            $lines[] = "説明: {$description}";
        } else {
            $lines[] = '説明: 追加の説明はありません。';
        }

        $lines[] = '1日あたりに浮く現実的な時間（hours_per_day）と、その根拠（reason）を考えてください。hours_per_dayは0.1刻み程度でも構いません。';
        $lines[] = 'JSONで{"hours_per_day": number, "reason": "text"}の形のみで回答してください。';

        return implode("\n", $lines);
    }

    private function extractHours(?string $content): ?float
    {
        if (empty($content)) {
            return null;
        }

        $decoded = json_decode($content, true);

        if (is_array($decoded) && isset($decoded['hours_per_day'])) {
            return $this->normalizeHours((float) $decoded['hours_per_day']);
        }

        if (is_numeric($content)) {
            return $this->normalizeHours((float) $content);
        }

        if (preg_match('/([0-9]+(?:\.[0-9]+)?)\s*(?:時間|hours?)/i', $content, $matches)) {
            return $this->normalizeHours((float) $matches[1]);
        }

        return null;
    }

    private function normalizeHours(float $hours): float
    {
        $hours = max(min($hours, 24), 0);

        return round($hours, 2);
    }
}
