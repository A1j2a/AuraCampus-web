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
use App\Http\Controllers\School\LeaveRequestController;
use App\Http\Controllers\School\HomeworkManagementController;
use App\Http\Controllers\School\CurriculumTrackerController;
use App\Http\Controllers\School\PtcManagementController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->middleware('guest');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('super-admin')) return redirect()->route('superadmin.dashboard');
    if ($user->hasRole('school-admin')) return redirect()->route('school.dashboard');
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Super Admin Route Group
Route::middleware(['auth', 'verified', 'role:super-admin'])
    ->prefix('super-admin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/schools', [SchoolController::class, 'index'])->name('schools');
        Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');
        Route::patch('/schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
        Route::get('/admins', [AdminController::class, 'index'])->name('admins');
        Route::patch('/admins/{admin}', [AdminController::class, 'update'])->name('admins.update');
        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions');
        Route::post('/subscriptions/plan', [SubscriptionController::class, 'storePlan'])->name('subscriptions.plan.store');
        Route::post('/subscriptions/school', [SubscriptionController::class, 'storeSchoolSubscription'])->name('subscriptions.school.store');
        Route::get('/support', [SupportController::class, 'index'])->name('support');
        Route::get('/support/{ticket}', [SupportController::class, 'show'])->name('support.show');
        Route::patch('/support/{ticket}', [SupportController::class, 'update'])->name('support.update');
        Route::post('/support/{ticket}/message', [SupportController::class, 'message'])->name('support.message');
        Route::get('/settings', fn() => view('superadmin.settings.index'))->name('settings');
        Route::post('/settings', fn() => redirect()->route('superadmin.settings')->with('success', 'Settings updated!'))->name('settings.store');
    });

// School Admin Route Group
Route::middleware(['auth', 'verified', 'role:school-admin'])
    ->prefix('school')
    ->name('school.')
    ->group(function () {
        Route::get('/dashboard', [SchoolDashboardController::class, 'index'])->name('dashboard');

        // Classes
        Route::get('/classes', [ClassController::class, 'index'])->name('classes');
        Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
        Route::patch('/classes/{class}', [ClassController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');

        // Subjects
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::patch('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::post('/subjects/assign', [SubjectController::class, 'assign'])->name('subjects.assign');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
        Route::delete('/classes/{class}/subjects/{subject}', [SubjectController::class, 'detachClassSubject'])->name('classes.subjects.detach');

        // Teachers
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::patch('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');

        // Students
        Route::get('/students', [StudentController::class, 'index'])->name('students');
        Route::patch('/students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::patch('/students/{student}/status', [StudentController::class, 'updateStatus'])->name('students.status');
        Route::patch('/students/{student}/transfer', [StudentController::class, 'transferClass'])->name('students.transfer');

        // Attendance
        // Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
        // Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

        // Parents
        Route::get('/parents', [ParentController::class, 'index'])->name('parents');
        Route::post('/parents', [ParentController::class, 'store'])->name('parents.store');
        Route::patch('/parents/{parent}', [ParentController::class, 'update'])->name('parents.update');
        Route::post('/parents/link', [ParentController::class, 'link'])->name('parents.link');

        // Notices
        Route::get('/notices', [NoticeController::class, 'index'])->name('notices');
        Route::post('/notices', [NoticeController::class, 'store'])->name('notices.store');
        Route::delete('/notices/{notice}', [NoticeController::class, 'destroy'])->name('notices.destroy');

        // Exams
        Route::get('/exams', [ExamController::class, 'index'])->name('exams');
        Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
        Route::post('/exams/{exam}/schedule', [ExamController::class, 'storeSchedule'])->name('exams.schedule.store');

        // Marks
        Route::get('/marks/schedule/{examSchedule}', [MarksController::class, 'index'])->name('marks.schedule');
        Route::post('/marks/schedule/store', [MarksController::class, 'store'])->name('marks.schedule.store');

        // Report Cards
        Route::get('/report-cards', [MarksController::class, 'reportCardsIndex'])->name('report-cards.index');
        Route::get('/report-cards/{student}/{exam}', [MarksController::class, 'reportCard'])->name('report-cards.show');

        // Timetable
        Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
        Route::post('/timetable', [TimetableController::class, 'store'])->name('timetable.store');
        Route::delete('/timetable/bulk-delete', [TimetableController::class, 'bulkDestroy'])->name('timetable.bulk-destroy');
        Route::delete('/timetable/{slot}', [TimetableController::class, 'destroy'])->name('timetable.destroy');
        Route::post('/timetable/periods', [TimetableController::class, 'updatePeriods'])->name('timetable.periods.update');

        // Fees
        // Route::get('/fees/structures', [FeeStructureController::class, 'index'])->name('fees.index');
        // Route::post('/fees/structures', [FeeStructureController::class, 'store'])->name('fees.store');
        // Route::get('/fees/payments', [FeePaymentController::class, 'index'])->name('fees.payments');
        // Route::get('/fees/collect', [FeePaymentController::class, 'create'])->name('fees.collect');
        // Route::post('/fees/payments', [FeePaymentController::class, 'store'])->name('fees.payments.store');
        // Route::get('/fees/receipt/{payment}', [FeePaymentController::class, 'receipt'])->name('fees.receipt');
        // Route::get('/fees/report', [FeePaymentController::class, 'report'])->name('fees.report');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // Search
        Route::get('/search', [SearchController::class, 'query'])->name('search');

        // Session Switcher
        Route::post('/session/select', function (Illuminate\Http\Request $request) {
            $request->validate(['academic_session_id' => 'required|exists:academic_sessions,id']);
            $session = \App\Models\AcademicSession::where('school_id', auth()->user()->school_id)->findOrFail($request->academic_session_id);
            session(['active_academic_session_id' => $session->id]);
            return back()->with('success', 'Session changed to ' . $session->name);
        })->name('session.select');

        // Support
        Route::get('/support', [SchoolSupportController::class, 'index'])->name('support.index');
        Route::post('/support', [SchoolSupportController::class, 'store'])->name('support.store');
        Route::get('/support/{ticket}', [SchoolSupportController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/message', [SchoolSupportController::class, 'message'])->name('support.message');

        // Leave Requests (from parents)
        Route::patch('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
        Route::patch('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');

        // Homework Management
        Route::get('/homework', [HomeworkManagementController::class, 'index'])->name('homework.index');
        Route::get('/homework/{homework}', [HomeworkManagementController::class, 'show'])->name('homework.show');
        Route::delete('/homework/{homework}', [HomeworkManagementController::class, 'destroy'])->name('homework.destroy');

        // Curriculum Tracker
        Route::get('/curriculum', [CurriculumTrackerController::class, 'index'])->name('curriculum.index');

        // PTC Bookings Management
        Route::get('/ptc', [PtcManagementController::class, 'index'])->name('ptc.index');
        Route::post('/ptc/{booking}/cancel', [PtcManagementController::class, 'cancel'])->name('ptc.cancel');
        Route::post('/ptc/{booking}/reschedule', [PtcManagementController::class, 'reschedule'])->name('ptc.reschedule');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/notifications/read-all', function () {
        \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'Notifications marked as read.');
    })->name('notifications.read-all');
});

require __DIR__.'/auth.php';
