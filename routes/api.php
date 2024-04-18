<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Authentication;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\Api\SubmitExamController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user(), 200);
});

Route::middleware(['auth:sanctum','teacher'])->group(function () {

    Route::controller(CourseController::class)->group(function () {
        
        Route::get('/courses/{id}', 'getOneCourse');
        Route::get('/courses', 'getAllCourse');
        Route::get('/users/courses', 'getAllCourseBelongToUser');
        Route::get('/users/courses/search/{search}/{sortBy}/{sortDir}', 'searchCourse');
        Route::post('/courses/create', 'createCourse');
        Route::put('/courses/update/{id}', 'updateCourse');
        Route::delete('/courses/delete/{id}', 'deleteCourse');
    }); 

    Route::controller(QuestionController::class)->group(function () {
        Route::get('/questions/{id}', 'getOneQuestion');
        Route::get('/questions', 'getAllQuestion');
        Route::get('/users/questions/search/{search}/{sortBy}/{sortDir}', 'searchQuestions');
        Route::post('/questions/create', 'createQuestion');
        Route::put('/questions/update/{id}', 'updateQuestion');
        Route::delete('/questions/delete/{id}', 'deleteQuestion');
    }); 

    Route::controller(ExamController::class)->group(function () {
        Route::get('/exams/{id}', 'getOneExam');
        Route::get('/users/exams', 'getAllExam');
        Route::get('/users/exams/search/{search}/{sortBy}/{sortDir}', 'searchExams');
        Route::post('/exams/create', 'createExam');
        Route::put('/exams/update/{id}', 'updateExam');
        Route::delete('/exams/delete/{id}', 'deleteExam');
    }); 

    Route::controller(ResultController::class)->group(function () {
        Route::get('/get/user/result/{user_id}/{exam_id}','getUserResult');
        Route::get('/getResult', 'getResult');
        Route::get('/getResult/studentScore/{id}', 'getResultStudentScore');
    });

    Route::controller(SubmitExamController::class)->group(function () {
        Route::post('/submitExam/create', 'createSubmitExam');
    });
});

Route::middleware(['auth:sanctum'])->group(function () {


    Route::controller(ResultController::class)->group(function () {
        Route::get('/get/user/result/{user_id}/{exam_id}','getUserResult');
        Route::get('/getResult', 'getResult');
        Route::get('/getResult/studentScore/{id}', 'getResultStudentScore');
    });

    Route::controller(SubmitExamController::class)->group(function () {
        Route::post('/submitExam/create', 'createSubmitExam');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/teacher/dashboard', 'getDashboardDetail');
    });

    
    Route::controller(AuthController::class)->group(function () {
        Route::post('/update/user/{id}', 'updateUser');
    });

});


Route::get('/helllo', function () {
    return response()->json("hello", 200);
});