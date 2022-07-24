<?php

use App\Events\Post\ShouldEndPost;
use App\Events\Post\ShouldStartPost;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MediaDownloadController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RaspberryController;
use App\Http\Controllers\RaspberryPostController;
use App\Http\Controllers\RecurrenceController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use App\Models\Post;
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

Route::get('/event/{post}/{eventName}',
    function (Post $post, string $eventName) {
        if ($eventName === 'start') {
            event(new ShouldStartPost($post));
        } else {
            if ($eventName === 'end') {
                event(new ShouldEndPost($post));
            }
        }
    });

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/server-status', function (Request $request) {
        return response()->json(['status' => 'up']);
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('raspberry.posts', RaspberryPostController::class)
        ->only('index');
    Route::get('media/{filename}/download', MediaDownloadController::class)
        ->name('media.download');

    Route::apiResources([
        'users'       => UserController::class,
        'displays'    => DisplayController::class,
        'raspberries' => RaspberryController::class,
        'posts'       => PostController::class,
        'medias'      => MediaController::class,
        'recurrences' => RecurrenceController::class,
        'stores'      => StoreController::class,
    ]);

    Route::apiResource('invitations', InvitationController::class,
        ['except' => ['update', 'show']]);
});

Route::get('invitations/{token}', [InvitationController::class, 'show'])
    ->name('invitations.show');
Route::patch('invitations/{token}', [InvitationController::class, 'update'])
    ->name('invitations.update');

