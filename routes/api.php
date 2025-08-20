<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\AuthController;

// Auth
Route::post('/login', [AuthController::class, 'login']);

// Email
Route::get('/emails/inbox', [EmailController::class, 'inbox']);
Route::get('/emails/sent', [EmailController::class, 'sent']);
Route::post('/emails/send', [EmailController::class, 'send']);
