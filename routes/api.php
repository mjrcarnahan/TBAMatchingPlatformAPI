<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MaestroController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ProfileController;

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

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::group(['middleware' => ['cors']], function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('user/verify/{token}',  [AuthController::class, 'verifyUser']);

    Route::post('user/send-password-reset', [UserController::class,'sendEmail']);
    Route::post('user/password-reset', [UserController::class,'changePassword']);

    Route::get('sexes', [MaestroController::class, 'indexSex']);
    Route::get('maritals', [MaestroController::class, 'indexMarital']);

    Route::group(['middleware' => ['jwt.auth']], function () {

        Route::post('register/spouse', [UserController::class, 'spouse']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::group(['prefix' => 'profiles', 'middleware' => ['user.auth']], function () {
            Route::get('', [ProfileController::class,'index']);
            Route::get('{profile}', [ProfileController::class,'show']);
        });

        Route::group(['prefix' => 'profile', 'middleware' => ['user.auth']], function () {
            Route::get('', [ProfileController::class, 'showMe']);
            Route::put('update', [ProfileController::class, 'update']);
            Route::post('agree', [ProfileController::class, 'agree']);
            Route::post('picture', [ProfileController::class, 'picture']);
            Route::post('credit-report', [ProfileController::class, 'creditReport']);
            Route::post('obgyn-letter', [ProfileController::class, 'obgynLetter']);
        });

        Route::group(['prefix' => 'user'], function () {
            Route::get('me', [AuthController::class, 'me']);
        });

        Route::group(['middleware' => ['user.auth']], function () {
            Route::get('questions', [QuestionController::class, 'index']);
            Route::post('answers', [QuestionController::class, 'storeAnswer']);
            Route::get('get-answers', [QuestionController::class, 'profileAnswers']);
        });

    });

});

//rutas admin
Route::group(['middleware' => ['cors','jwt.auth','role:3']], function () {
    Route::prefix('admin')->group(__DIR__ . '/api/admin.php');
});
