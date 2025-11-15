<?php

use App\Http\Controllers\TeamMemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('team-members')->group(function () {
    Route::get('/', [TeamMemberController::class, 'apiIndex']);
    Route::get('/{id}', [TeamMemberController::class, 'apiShow']);
    Route::post('/', [TeamMemberController::class, 'apiStore']);
    Route::put('/{id}', [TeamMemberController::class, 'apiUpdate']);
    Route::delete('/{id}', [TeamMemberController::class, 'apiDestroy']);
});

