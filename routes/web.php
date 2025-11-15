<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TeamMemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Login Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Dashboard Routes (Protected)
Route::middleware('auth')->prefix('admin/team-members')->name('admin.team-members.')->group(function () {
    Route::get('/', [TeamMemberController::class, 'index'])->name('index');
    Route::get('/create', [TeamMemberController::class, 'create'])->name('create');
    Route::post('/', [TeamMemberController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [TeamMemberController::class, 'edit'])->name('edit');
    Route::post('/{id}', [TeamMemberController::class, 'update'])->name('update');
    Route::delete('/{id}', [TeamMemberController::class, 'destroy'])->name('destroy');
});
