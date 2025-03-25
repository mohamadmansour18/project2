<?php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::namespace('Dashboard')->group(function(){
    //Route::get(/getAll/Category/{category_id?});
    //Route::post();

    Route::controller(Controller::class)->group(function (){
        //Route::get('/auth' , 'logIn');
    });

    Route::middleware(['throttle:mansour'])->group(function () {
        Route::get('/man' , function (){
            return response()->json('hello world');
        });
    });
});



Route::fallback(function (){
    return response()->json([
        'message' => 'This route not found in APIs !' ,
        'status' => 404
    ] , 404);
});


//Route cash
