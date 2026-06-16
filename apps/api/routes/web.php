<?php

use Illuminate\Support\Facades\Route;

Route::any('/{any}', function () {
    return response()->json(['message' => 'Not found.'], 404);
})->where('any', '.*');
