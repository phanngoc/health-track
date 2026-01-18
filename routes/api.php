<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\KnowledgeController;
use App\Http\Controllers\Api\TimelineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Check-in routes
    Route::apiResource('checkins', CheckInController::class);
    Route::post('checkins/moment', [CheckInController::class, 'storeMoment']);

    // Timeline routes
    Route::get('timeline', [TimelineController::class, 'index']);

    // Alert routes
    Route::get('alerts', [AlertController::class, 'index']);
    Route::get('alerts/unacknowledged-count', [AlertController::class, 'unacknowledgedCount']);
    Route::post('alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge']);

    // Knowledge routes
    Route::get('knowledge', [KnowledgeController::class, 'index']);
    Route::get('knowledge/{symptomCode}', [KnowledgeController::class, 'show']);
});
