<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SessionAuthController;

Route::post('/session-login', [SessionAuthController::class, 'login']);

