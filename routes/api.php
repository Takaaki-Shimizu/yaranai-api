<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\YaranaiItemController;
use App\Http\Controllers\Api\IncomeSettingController;
use App\Http\Controllers\Api\DailySavingController;

Route::get('/yaranai-items', [YaranaiItemController::class, 'index']);
Route::post('/yaranai-items', [YaranaiItemController::class, 'store']);
Route::put('/yaranai-items/{yaranaiItem}', [YaranaiItemController::class, 'update']);
Route::delete('/yaranai-items/{yaranaiItem}', [YaranaiItemController::class, 'destroy']);
Route::post('/income-settings', [IncomeSettingController::class, 'store']);
Route::post('/daily-savings', [DailySavingController::class, 'store']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return ['message' => 'pong'];
});
