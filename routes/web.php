<?php

use Illuminate\Support\Facades\Route;
use RonasIT\TelescopeExtension\Http\Controllers\RequestsController;

Route::post('/telescope/telescope-api/requests', [RequestsController::class, 'index']);
