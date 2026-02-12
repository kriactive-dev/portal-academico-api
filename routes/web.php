<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([ 'message' => 'Welcome to the API 1.0',
                                'version' => '1.0', 
                                'status' => 'success'
    
    ]);
});
