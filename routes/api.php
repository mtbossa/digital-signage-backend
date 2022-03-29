<?php

use App\Http\Controllers\DisplayController;
use App\Http\Controllers\RaspberryController;
use \App\Http\Controllers\PostController;
use \App\Http\Controllers\MediaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::apiResources([
  'displays' => DisplayController::class, 'raspberries' => RaspberryController::class, 'posts' => PostController::class,
  'medias' => MediaController::class
]);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});
