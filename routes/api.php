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

    // ── Teacher Portal ──────────────────────────────────────────
    Route::middleware('role:teacher')->prefix('teacher')->group(function () {

        // Dashboard
        Route::get('/dashboard', [TeacherApiController::class, 'dashboard']);

        // Schedule (timetable grouped by day)
        Route::get('/schedule', [TeacherApiController::class, 'schedule']);

        // Classes
        Route::get('/classes', [TeacherApiController::class, 'classes']);
        Route::get('/classes/{class}', [TeacherApiController::class, 'classDetail']);
        Route::get('/classes/{class}/students', [TeacherApiController::class, 'students']);
        Route::get('/classes/{class}/attendance', [TeacherApiController::class, 'getAttendance']);
        Route::post('/classes/{class}/attendance', [TeacherApiController::class, 'storeAttendance']);

        // Student profile
        Route::get('/students/{studentId}', [TeacherApiController::class, 'studentProfile']);

        // Syllabus
        Route::get('/syllabus', [TeacherApiController::class, 'getSyllabus']);
        Route::post('/syllabus', [TeacherApiController::class, 'storeSyllabus']);
        Route::put('/syllabus/{chapter}', [TeacherApiController::class, 'updateSyllabus']);

        // Exams & Marks
        Route::get('/exams', [TeacherApiController::class, 'getExams']);
        Route::get('/exams/schedule/{schedule}/marks', [TeacherApiController::class, 'getMarks']);
        Route::post('/exams/schedule/{schedule}/marks', [TeacherApiController::class, 'storeMarks']);

        // Homework
        Route::get('/homework', [TeacherApiController::class, 'getHomework']);
        Route::post('/homework', [TeacherApiController::class, 'storeHomework']);
        Route::put('/homework/{homework}', [TeacherApiController::class, 'updateHomework']);
        Route::get('/homework/{homework}/submissions', [TeacherApiController::class, 'getSubmissions']);
        Route::post('/homework/{homework}/submissions/{studentId}/grade', [TeacherApiController::class, 'gradeSubmission']);
    });

    // ── Parent / Student Portal ─────────────────────────────────
    Route::middleware('role:parent')->prefix('parent')->group(function () {

        // 3.1 Children list
        Route::get('/children', [ParentApiController::class, 'children']);

        // 3.2 Student dashboard
        Route::get('/children/{student}/dashboard', [ParentApiController::class, 'studentDashboard']);

        // 3.3 Homework feed & submission
        Route::get('/children/{student}/homework', [ParentApiController::class, 'studentHomework']);
        Route::post('/children/{student}/homework/{homework}/submit', [ParentApiController::class, 'submitHomework']);

        // 3.4 Results & PTC
        Route::get('/children/{student}/results', [ParentApiController::class, 'studentResults']);
        Route::post('/children/{student}/results/ptc/schedule', [ParentApiController::class, 'schedulePtc']);

        // 3.5 Leave requests
        Route::get('/children/{student}/leave', [ParentApiController::class, 'getLeaveRequests']);
        Route::post('/children/{student}/leave', [ParentApiController::class, 'submitLeaveRequest']);

        // 3.6 Events calendar
        Route::get('/children/{student}/events', [ParentApiController::class, 'studentEvents']);

        // 3.7 Notifications
        Route::get('/children/{student}/notifications', [ParentApiController::class, 'getNotifications']);
        Route::put('/children/{student}/notifications/read', [ParentApiController::class, 'markNotificationsRead']);
        Route::delete('/children/{student}/notifications/{notificationId}', [ParentApiController::class, 'deleteNotification']);

        // Existing
        Route::get('/children/{student}/attendance', [ParentApiController::class, 'studentAttendance']);
        Route::get('/children/{student}/timetable', [ParentApiController::class, 'studentTimetable']);
        Route::get('/children/{student}/exams', [ParentApiController::class, 'studentExams']);
        Route::get('/children/{student}/fees', [ParentApiController::class, 'studentFees']);
    });
});

