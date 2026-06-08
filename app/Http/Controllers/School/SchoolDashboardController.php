<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Notice;
use App\Models\Attendance;
use App\Models\Subject;
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
        $totalClasses = SchoolClass::count();
        $totalSubjects = Subject::count();

        // Today's attendance rate
        $todayAttendance = Attendance::where('date', today())->get();
        $attendanceRate = $todayAttendance->count() > 0
            ? round(($todayAttendance->where('status', 'present')->count() / $todayAttendance->count()) * 100, 1)
            : 0;

        // Recent notices
        $notices = Notice::latest('published_at')->take(3)->get();

        // Class roster with student counts
        $classes = SchoolClass::with('teacher')
                              ->withCount('students')
                              ->orderBy('name')
                              ->orderBy('section')
                              ->get();

        return view('school.dashboard.index', compact(
            'totalStudents', 'totalTeachers', 'totalClasses',
            'totalSubjects', 'attendanceRate', 'notices', 'classes'
        ));
    }
}
