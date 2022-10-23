<?php


use App\Http\Controllers\Api\V1\Auth\NewPasswordController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\User\SubscriberController;
use App\Http\Controllers\Api\V1\User\TopicController;
use App\Http\Controllers\Api\V1\User\PublisherController;

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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


//real user application Apis
Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1'], function () {
    //Authentication Routes
    Route::post('auth/register', [RegisteredUserController::class, 'store'])->middleware('guest');
    Route::post('auth/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
    Route::post('auth/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
    Route::post('auth/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('guest');
    Route::post('auth/reset-password', [NewPasswordController::class, 'store'])->middleware('guest');

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        //Subscribers Routes
        Route::post('subscriber/create', [SubscriberController::class, 'addSubscriber']);
        Route::get('subscriber/list_all', [SubscriberController::class, 'getAllSubscribers']);
        Route::get('subscriber/{slug}', [SubscriberController::class, 'getSubscriber']);
        Route::post('subscriber/deactivate', [SubscriberController::class, 'deactivateSubscriber']);
        Route::post('subscriber/activate', [SubscriberController::class, 'activateSubscriber']);

        //Topics Routes
        Route::get('topic/list_all', [TopicController::class, 'getTopics']);
        Route::post('topic/create', [TopicController::class, 'addTopic']);

        //Publishers Routes
        Route::post('publisher/create', [PublisherController::class, 'createMessage']);
        Route::get('publisher/list_all_messages', [PublisherController::class, 'getAllMessages']);
        Route::get('publisher/message/{slug}', [PublisherController::class, 'getMessage']);

    });

        Route::fallback(function () {
        return response()->json(['message' => 'Page Not Found'], 404);
    });
});
