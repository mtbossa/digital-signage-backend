<?php

use App\Http\Controllers\DisplayController;
use App\Http\Controllers\DisplayInstallerDownloadController;
use App\Http\Controllers\DisplayPostController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MediaDownloadController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostDisplayOptions;
use App\Http\Controllers\PostMediaOptions;
use App\Http\Controllers\RaspberryController;
use App\Http\Controllers\RecurrenceController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreDisplaysController;
use App\Http\Controllers\UserController;
use App\Http\Resources\LoggedUserResource;
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

  Route::get('/server-status', function (Request $request) {
    return response()->json(['status' => 'up']);
  });

  Route::get('/user', function (Request $request) {
    return new LoggedUserResource($request->user());
  });

  Route::apiResource('displays.posts', DisplayPostController::class)
    ->only('index');
  Route::get('media/{filename}/download', MediaDownloadController::class)
    ->name('media.download');
  Route::get('displays/{display}/installer/download', DisplayInstallerDownloadController::class)
    ->name('displays.installer.download');
  Route::apiResource('stores.displays', StoreDisplaysController::class)
    ->only('index');

  Route::apiResources([
    'users' => UserController::class,
    'displays' => DisplayController::class,
    'raspberries' => RaspberryController::class,
    'posts' => PostController::class,
    'medias' => MediaController::class,
    'recurrences' => RecurrenceController::class,
    'stores' => StoreController::class,
  ]);

  Route::get('posts/medias/options', PostMediaOptions::class)->name("post.media.options");
  Route::get('posts/displays/options', PostDisplayOptions::class)->name("post.display.options");

  Route::apiResource('invitations', InvitationController::class,
    ['except' => ['update', 'show']]);
});

Route::get('invitations/{token}', [InvitationController::class, 'show'])
    ->name('invitations.show');
Route::patch('invitations/{token}', [InvitationController::class, 'update'])
    ->name('invitations.update');

