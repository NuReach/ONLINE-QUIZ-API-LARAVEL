<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\Authentication;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\SubmitExamController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {

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

    Route::controller(ExamController::class)->group(function () {
        Route::get('/exams/{id}', 'getOneExam');
        Route::get('/exams', 'getAllExam');
        Route::post('/exams/create', 'createExam');
        Route::put('/exams/update/{id}', 'updateExam');
        Route::delete('/exams/delete/{id}', 'deleteExam');
    }); 

});



Route::controller(SubmitExamController::class)->group(function () {
    Route::post('/submitExam/create', 'createSubmitExam');
});

Route::controller(ResultController::class)->group(function () {
    Route::get('/getResult', 'getResult');
    Route::get('/getResult/studentScore/{id}', 'getResultStudentScore');
});





Route::get('/helllo', function () {
    return response()->json("hello", 200);
});