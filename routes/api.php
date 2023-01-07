<?php

use App\Http\Controllers\DisplayController;
use App\Http\Controllers\DisplayOption;
use App\Http\Controllers\DisplayPostController;
use App\Http\Controllers\DisplayPostsSyncController;
use App\Http\Controllers\DisplaysCodesController;
use App\Http\Controllers\DisplayUpdatesController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MediaDownloadController;
use App\Http\Controllers\MediaOption;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RaspberryController;
use App\Http\Controllers\RaspberryDisplayPostsController;
use App\Http\Controllers\RaspberryInstallerDownloadController;
use App\Http\Controllers\RecurrenceController;
use App\Http\Controllers\RecurrenceOption;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreDisplaysController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\IsNotRaspberry;
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

  Route::middleware(IsNotRaspberry::class)->group(function () {
    Route::get('/user', function (Request $request) {
      return new LoggedUserResource($request->user());
    });

    Route::apiResource('displays.posts', DisplayPostController::class)
      ->only('index');
    
   Route::get('displays/{display}/updates', DisplayUpdatesController::class)
      ->name('displays.updates');

    Route::apiResource('stores.displays', StoreDisplaysController::class)
      ->only('index');

    Route::get('medias/options', MediaOption::class)->name("medias.options");
    Route::get('displays/options', DisplayOption::class)->name("displays.options");
    Route::get('recurrences/options', RecurrenceOption::class)->name("recurrences.options");

    Route::patch('posts/{post}/description', [PostController::class, "description"])->name("posts.update.description");

    Route::apiResources([
      'users' => UserController::class,
      'displays' => DisplayController::class,
      'posts' => PostController::class,
      'medias' => MediaController::class,
      'raspberries' => RaspberryController::class,
      'recurrences' => RecurrenceController::class,
      'stores' => StoreController::class,
    ]);

    Route::apiResource('invitations', InvitationController::class,
      ['except' => ['update', 'show']]);
  });
  Route::get('display/{display}/posts/sync', DisplayPostsSyncController::class);
  Route::get("raspberry/display/posts", RaspberryDisplayPostsController::class)->name('raspberry.display.posts');
  Route::get('media/{filename}/download', MediaDownloadController::class)
    ->name('media.download');
  Route::get('raspberry/installer/download', RaspberryInstallerDownloadController::class)
    ->name('raspberry.installer.download');

  Route::get('/server-status', function (Request $request) {
    return response()->json(['status' => 'up']);
  });
});

Route::apiResource('displays-codes', DisplaysCodesController::class,
  ['only' => ['store']]);
Route::get('invitations/{token}', [InvitationController::class, 'show'])
  ->name('invitations.show');
Route::patch('invitations/{token}', [InvitationController::class, 'update'])
  ->name('invitations.update');

