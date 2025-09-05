<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;


    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::patch('/tickets/{id}', [TicketController::class, 'update']);
    Route::post('tickets/{id}/classify', [TicketController::class, 'classify'])->middleware('throttle:30,1');

    Route::get('/stats', [DashboardController::class, 'index']);


