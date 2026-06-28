<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Notice;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\StudentLeaveRequest;
use Illuminate\View\View;

class SchoolDashboardController extends Controller
{
    /**
     * Display the School Admin Dashboard.
     */
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;

        // Stats
        $totalStudents = User::where('school_id', $schoolId)->role('student')->count();
        $totalTeachers = User::where('school_id', $schoolId)->role('teacher')->count();
        $totalClasses  = SchoolClass::where('school_id', $schoolId)->count();
        $totalSubjects = Subject::where('school_id', $schoolId)->count();
        $totalParents  = User::where('school_id', $schoolId)->role('parent')->count();

        // Recent notices
        $notices = Notice::where('school_id', $schoolId)->latest('published_at')->take(3)->get();

        // Class roster with student counts
        $classes = SchoolClass::where('school_id', $schoolId)
                              ->with('teacher')
                              ->withCount('students')
                              ->orderBy('name')
                              ->orderBy('section')
                              ->get();

        // Student leave requests (pending first, then recent)
        $leaveRequests = StudentLeaveRequest::where('school_id', $schoolId)
            ->with(['parent', 'students', 'students.studentDetail.class'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 1 WHEN 'approved' THEN 2 WHEN 'rejected' THEN 3 ELSE 4 END")
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $pendingLeavesCount = StudentLeaveRequest::where('school_id', $schoolId)
            ->where('status', 'pending')
            ->count();

        // Calculate dynamic greeting based on school timezone
        $timezone = auth()->user()->school->settings['timezone'] ?? config('app.timezone', 'UTC');
        $hour = now($timezone)->hour;
        if ($hour < 12) {
            $greeting = 'Good Morning';
        } elseif ($hour < 17) {
            $greeting = 'Good Afternoon';
        } else {
            $greeting = 'Good Evening';
        }

        return view('school.dashboard.index', compact(
            'totalStudents', 'totalTeachers', 'totalClasses',
            'totalSubjects', 'totalParents', 'notices', 'classes',
            'leaveRequests', 'pendingLeavesCount', 'greeting'
        ));
    }
}
