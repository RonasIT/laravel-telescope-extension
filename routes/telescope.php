<?php

use Illuminate\Support\Facades\Route;
use RonasIT\TelescopeExtension\Http\Controllers\RequestsController;

Route::group([
    'prefix' => config('telescope.path'),
    'domain' => config('telescope.domain'),
    'middleware' => 'telescope',
], function () {
    Route::post('telescope-api/requests', [RequestsController::class, 'index']);
});

