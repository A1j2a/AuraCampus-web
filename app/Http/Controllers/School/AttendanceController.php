<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\StudentDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();

        $selectedClassId = $request->query('class_id', $classes->first()?->id);
        $selectedDate = $request->query('date', now()->format('Y-m-d'));

        $students = collect();
        $attendanceMap = [];

        if ($selectedClassId) {
            $students = StudentDetail::where('class_id', $selectedClassId)
                                     ->with('user')
                                     ->orderBy('roll_number')
                                     ->get();

            $attendances = Attendance::where('class_id', $selectedClassId)
                                    ->where('date', $selectedDate)
                                    ->get()
                                    ->keyBy('student_id');

            $attendanceMap = $attendances->toArray();
        }

        return view('school.attendance.index', compact(
            'classes', 'students', 'attendanceMap',
            'selectedClassId', 'selectedDate'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,excused',
        ]);

        $schoolId = auth()->user()->school_id;

        foreach ($request->attendance as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'class_id' => $request->class_id,
                    'student_id' => $studentId,
                    'date' => $request->date,
                ],
                [
                    'status' => $status,
                    'marked_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('school.attendance', [
            'class_id' => $request->class_id,
            'date' => $request->date,
        ])->with('success', 'Attendance saved for ' . $request->date);
    }
}
