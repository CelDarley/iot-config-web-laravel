<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TopicController;

// Rotas de autenticação
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rotas de usuários (placeholder)
    Route::get('/users', function () {
        return view('users.index');
    })->name('users.index');

    // Rotas de tópicos MQTT
    Route::resource('topics', TopicController::class);
    Route::patch('topics/{id}/deactivate', [TopicController::class, 'deactivate'])->name('topics.deactivate');
    
    // Rotas de teste MQTT
    Route::post('/api/topics/test-connection', [TopicController::class, 'testConnection'])->name('topics.test-connection');
    Route::post('/api/topics/send-command', [TopicController::class, 'sendCommand'])->name('topics.send-command');

    // Rotas de dispositivos (placeholder)
    Route::get('/devices', function () {
        return view('devices.index');
    })->name('devices.index');
});
