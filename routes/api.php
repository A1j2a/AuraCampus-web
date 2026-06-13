<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Teacher\TeacherApiController;
use App\Http\Controllers\Api\Parent\ParentApiController;
use Illuminate\Support\Facades\Route;

// Public login endpoint
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated API endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Teacher Portal endpoints (role: teacher)
    Route::middleware('role:teacher')
        ->prefix('teacher')
        ->group(function () {
            Route::get('/dashboard', [TeacherApiController::class, 'dashboard']);
            Route::get('/timetable', [TeacherApiController::class, 'timetable']);
            Route::get('/classes', [TeacherApiController::class, 'classes']);
            Route::get('/classes/{class}/students', [TeacherApiController::class, 'students']);
            Route::get('/classes/{class}/attendance', [TeacherApiController::class, 'getAttendance']);
            Route::post('/classes/{class}/attendance', [TeacherApiController::class, 'storeAttendance']);
            Route::get('/exams', [TeacherApiController::class, 'getExams']);
            Route::get('/exams/schedule/{schedule}/marks', [TeacherApiController::class, 'getMarks']);
            Route::post('/exams/schedule/{schedule}/marks', [TeacherApiController::class, 'storeMarks']);
        });

    // Parent Portal endpoints (role: parent)
    Route::middleware('role:parent')
        ->prefix('parent')
        ->group(function () {
            Route::get('/children', [ParentApiController::class, 'children']);
            Route::get('/children/{student}/dashboard', [ParentApiController::class, 'studentDashboard']);
            Route::get('/children/{student}/attendance', [ParentApiController::class, 'studentAttendance']);
            Route::get('/children/{student}/timetable', [ParentApiController::class, 'studentTimetable']);
            Route::get('/children/{student}/exams', [ParentApiController::class, 'studentExams']);
            Route::get('/children/{student}/fees', [ParentApiController::class, 'studentFees']);
        });
});

