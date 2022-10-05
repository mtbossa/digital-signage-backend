<?php

use App\Http\Controllers\DisplayController;
use App\Http\Controllers\DisplayPostController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MediaDownloadController;
use App\Http\Controllers\MediaOption;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostDisplayOptions;
use App\Http\Controllers\PostRecurrenceOptions;
use App\Http\Controllers\RaspberryController;
use App\Http\Controllers\RaspberryDisplayPostsController;
use App\Http\Controllers\RaspberryInstallerDownloadController;
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

  Route::get("raspberry/display/posts", RaspberryDisplayPostsController::class)->name('raspberry.display.posts');
  Route::apiResource('displays.posts', DisplayPostController::class)
    ->only('index');
  Route::get('media/{filename}/download', MediaDownloadController::class)
    ->name('media.download');
  Route::get('raspberry/installer/download', RaspberryInstallerDownloadController::class)
    ->name('raspberry.installer.download');
  Route::apiResource('stores.displays', StoreDisplaysController::class)
    ->only('index');

  Route::get('medias/options', MediaOption::class)->name("medias.options");
  
  Route::apiResources([
    'users' => UserController::class,
    'displays' => DisplayController::class,
    'raspberries' => RaspberryController::class,
    'posts' => PostController::class,
    'medias' => MediaController::class,
    'recurrences' => RecurrenceController::class,
    'stores' => StoreController::class,
  ]);

  Route::get('posts/displays/options', PostDisplayOptions::class)->name("post.display.options");
  Route::get('posts/recurrences/options', PostRecurrenceOptions::class)->name("post.recurrence.options");

  Route::apiResource('invitations', InvitationController::class,
    ['except' => ['update', 'show']]);
});
Route::get('invitations/{token}', [InvitationController::class, 'show'])
    ->name('invitations.show');
Route::patch('invitations/{token}', [InvitationController::class, 'update'])
    ->name('invitations.update');

