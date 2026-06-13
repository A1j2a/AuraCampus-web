<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SchoolController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\SuperAdmin\SupportController;
use App\Http\Controllers\School\SchoolDashboardController;
use App\Http\Controllers\School\ClassController;
use App\Http\Controllers\School\SubjectController;
use App\Http\Controllers\School\TeacherController;
use App\Http\Controllers\School\StudentController;
use App\Http\Controllers\School\AttendanceController;
use App\Http\Controllers\School\ParentController;
use App\Http\Controllers\School\NoticeController;
use App\Http\Controllers\School\SettingsController;
use App\Http\Controllers\School\ExamController;
use App\Http\Controllers\School\MarksController;
use App\Http\Controllers\School\TimetableController;
use App\Http\Controllers\School\FeeStructureController;
use App\Http\Controllers\School\FeePaymentController;
use App\Http\Controllers\School\SearchController;
use App\Http\Controllers\School\SupportController as SchoolSupportController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])
    ->middleware('guest');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('super-admin')) {
        return redirect()->route('superadmin.dashboard');
    }
    if ($user->hasRole('school-admin')) {
        return redirect()->route('school.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Super Admin Route Group
Route::middleware(['auth', 'verified', 'role:super-admin'])
    ->prefix('super-admin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        // Schools Management
        Route::get('/schools', [SchoolController::class, 'index'])->name('schools');
        Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');

        // Admins Directory
        Route::get('/admins', [AdminController::class, 'index'])->name('admins');

        // Subscriptions Management
        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions');
        Route::post('/subscriptions/plan', [SubscriptionController::class, 'storePlan'])->name('subscriptions.plan.store');
        Route::post('/subscriptions/school', [SubscriptionController::class, 'storeSchoolSubscription'])->name('subscriptions.school.store');

        // Support Desk
        Route::get('/support', [SupportController::class, 'index'])->name('support');
        Route::get('/support/{ticket}', [SupportController::class, 'show'])->name('support.show');
        Route::patch('/support/{ticket}', [SupportController::class, 'update'])->name('support.update');
        Route::post('/support/{ticket}/message', [SupportController::class, 'message'])->name('support.message');

        Route::get('/settings', function () {
            return view('superadmin.settings.index');
        })->name('settings');
        Route::post('/settings', function () {
            return redirect()->route('superadmin.settings')->with('success', 'System configurations updated successfully!');
        })->name('settings.store');
    });

// School Admin Route Group
Route::middleware(['auth', 'verified', 'role:school-admin'])
    ->prefix('school')
    ->name('school.')
    ->group(function () {
        Route::get('/dashboard', [SchoolDashboardController::class, 'index'])->name('dashboard');

        // Classes & Sections
        Route::get('/classes', [ClassController::class, 'index'])->name('classes');
        Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
        Route::delete('/classes/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');

        // Subjects
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

        // Teachers
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');

        // Students
        Route::get('/students', [StudentController::class, 'index'])->name('students');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');

        // Attendance
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

        // Parents Management
        Route::get('/parents', [ParentController::class, 'index'])->name('parents');
        Route::post('/parents', [ParentController::class, 'store'])->name('parents.store');
        Route::post('/parents/link', [ParentController::class, 'link'])->name('parents.link');

        // Notices Management
        Route::get('/notices', [NoticeController::class, 'index'])->name('notices');
        Route::post('/notices', [NoticeController::class, 'store'])->name('notices.store');
        Route::delete('/notices/{notice}', [NoticeController::class, 'destroy'])->name('notices.destroy');

        // Exams & Grading Management
        Route::get('/exams', [ExamController::class, 'index'])->name('exams');
        Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
        Route::post('/exams/{exam}/schedule', [ExamController::class, 'storeSchedule'])->name('exams.schedule.store');

        // Marks Entry
        Route::get('/marks/schedule/{examSchedule}', [MarksController::class, 'index'])->name('marks.schedule');
        Route::post('/marks/schedule/store', [MarksController::class, 'store'])->name('marks.schedule.store');

        // Report Cards
        Route::get('/report-cards', [MarksController::class, 'reportCardsIndex'])->name('report-cards.index');
        Route::get('/report-cards/{student}/{exam}', [MarksController::class, 'reportCard'])->name('report-cards.show');

        // Timetable Management
        Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
        Route::post('/timetable', [TimetableController::class, 'store'])->name('timetable.store');
        Route::delete('/timetable/{slot}', [TimetableController::class, 'destroy'])->name('timetable.destroy');

        // Fee Management
        Route::get('/fees/structures', [FeeStructureController::class, 'index'])->name('fees.index');
        Route::post('/fees/structures', [FeeStructureController::class, 'store'])->name('fees.store');
        Route::get('/fees/payments', [FeePaymentController::class, 'index'])->name('fees.payments');
        Route::get('/fees/collect', [FeePaymentController::class, 'create'])->name('fees.collect');
        Route::post('/fees/payments', [FeePaymentController::class, 'store'])->name('fees.payments.store');
        Route::get('/fees/receipt/{payment}', [FeePaymentController::class, 'receipt'])->name('fees.receipt');
        Route::get('/fees/report', [FeePaymentController::class, 'report'])->name('fees.report');

        // Settings Management
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // Global Search API
        Route::get('/search', [SearchController::class, 'query'])->name('search');

        // Academic Session Switcher
        Route::post('/session/select', function (Illuminate\Http\Request $request) {
            $request->validate([
                'academic_session_id' => 'required|exists:academic_sessions,id',
            ]);

            $session = \App\Models\AcademicSession::where('school_id', auth()->user()->school_id)
                ->findOrFail($request->academic_session_id);

            session(['active_academic_session_id' => $session->id]);

            return back()->with('success', 'Academic session changed to ' . $session->name);
        })->name('session.select');

        // Help Desk Support
        Route::get('/support', [SchoolSupportController::class, 'index'])->name('support.index');
        Route::post('/support', [SchoolSupportController::class, 'store'])->name('support.store');
        Route::get('/support/{ticket}', [SchoolSupportController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/message', [SchoolSupportController::class, 'message'])->name('support.message');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::post('/notifications/read-all', function () {
        \App\Models\Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return back()->with('success', 'Notifications marked as read.');
    })->name('notifications.read-all');
});

require __DIR__.'/auth.php';
