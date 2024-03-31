<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\Authentication;
use App\Http\Controllers\Api\QuestionController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum','admin'])->group(function () {

    Route::controller(CourseController::class)->group(function () {
        Route::get('/courses/{id}', 'getOneCourse');
        Route::get('/courses', 'getAllCourse');
        Route::post('/courses/create', 'createCourse');
        Route::put('/courses/update/{id}', 'updateCourse');
        Route::delete('/courses/delete/{id}', 'deleteCourse');
    }); 

    Route::controller(QuestionController::class)->group(function () {
        Route::get('/questions/{id}', 'getOneQuestion');
        Route::get('/questions', 'getAllQuestion');
        Route::post('/questions/create', 'createQuestion');
        Route::put('/questions/update/{id}', 'updateQuestion');
        Route::delete('/questions/delete/{id}', 'deleteQuestion');
    }); 

    

});




Route::get('/helllo', function () {
    return response()->json("hello", 200);
});