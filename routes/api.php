<?php

use App\Http\Controllers\DisplayController;
use App\Http\Controllers\DisplayPostController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RaspberryController;
use App\Http\Controllers\RecurrenceController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
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


Route::middleware('auth:sanctum')->group(function () {

  Route::get('/user', function (Request $request) {
    return $request->user();
  });

  Route::apiResource('displays.posts', DisplayPostController::class)->only('index');

  Route::apiResources([
    'users' => UserController::class,
    'displays' => DisplayController::class, 'raspberries' => RaspberryController::class,
    'posts' => PostController::class,
    'medias' => MediaController::class, 'recurrences' => RecurrenceController::class,
    'stores' => StoreController::class,
  ]);

  Route::apiResource('invitations', InvitationController::class, ['except' => ['update']]);
});

Route::patch('invitations/{token}', [InvitationController::class, 'update'])->name('invitations.update');

