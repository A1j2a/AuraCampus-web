<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\TimetableSlot;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->orderBy('section')->get();
        $selectedClassId = $request->query('class_id');

        $slotsByDayAndPeriod = [];
        $selectedClass = null;

        if ($selectedClassId) {
            $selectedClass = SchoolClass::where('school_id', $schoolId)->findOrFail($selectedClassId);
            
            // Get all slots for this class
            $slots = TimetableSlot::where('class_id', $selectedClassId)
                ->with(['subject', 'teacher'])
                ->get();

            foreach ($slots as $slot) {
                $slotsByDayAndPeriod[$slot->day_of_week][$slot->period_number] = $slot;
            }
        }

        // Fetch subjects and teachers for slot allocation modal
        $subjects = Subject::where('school_id', $schoolId)->orderBy('name')->get();
        $teachers = User::where('school_id', $schoolId)->role('teacher')->orderBy('name')->get();

        // Standard days map
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        // Standard periods (1 to 6)
        $periods = [
            1 => ['name' => 'Period 1', 'start' => '08:00', 'end' => '08:45'],
            2 => ['name' => 'Period 2', 'start' => '08:45', 'end' => '09:30'],
            3 => ['name' => 'Period 3', 'start' => '09:30', 'end' => '10:15'],
            4 => ['name' => 'Period 4', 'start' => '10:30', 'end' => '11:15'],
            5 => ['name' => 'Period 5', 'start' => '11:15', 'end' => '12:00'],
            6 => ['name' => 'Period 6', 'start' => '12:00', 'end' => '12:45'],
        ];

        return view('school.timetable.index', compact(
            'classes',
            'selectedClassId',
            'selectedClass',
            'slotsByDayAndPeriod',
            'subjects',
            'teachers',
            'days',
            'periods'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = auth()->user()->school_id;

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'day_of_week' => 'required|integer|between:1,6',
            'period_number' => 'required|integer|between:1,6',
            'start_time' => 'required',
            'end_time' => 'required',
            'room_number' => 'nullable|string|max:50',
        ]);

        // Ensure teacher belongs to the same school
        $teacher = User::where('school_id', $schoolId)->role('teacher')->findOrFail($request->teacher_id);
        
        // Clash detection: Is the teacher already scheduled for another class in the same day and period?
        $clash = TimetableSlot::where('day_of_week', $request->day_of_week)
            ->where('period_number', $request->period_number)
            ->where('teacher_id', $request->teacher_id)
            ->where('class_id', '!=', $request->class_id)
            ->with('class')
            ->first();

        if ($clash) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['teacher_id' => 'Clash Detected! ' . $teacher->name . ' is already scheduled in Class ' . $clash->class->name . '-' . $clash->class->section . ' during this period.']);
        }

        // Save or update slot
        TimetableSlot::updateOrCreate(
            [
                'school_id' => $schoolId,
                'class_id' => $request->class_id,
                'day_of_week' => $request->day_of_week,
                'period_number' => $request->period_number,
            ],
            [
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'room_number' => $request->room_number,
            ]
        );

        return redirect()->route('school.timetable.index', ['class_id' => $request->class_id])
            ->with('success', 'Timetable slot allocated successfully!');
    }

    public function destroy(TimetableSlot $slot): RedirectResponse
    {
        $schoolId = auth()->user()->school_id;

        if ($slot->school_id !== $schoolId) {
            abort(403, 'Unauthorized.');
        }

        $classId = $slot->class_id;
        $slot->delete();

        return redirect()->route('school.timetable.index', ['class_id' => $classId])
            ->with('success', 'Period slot unassigned successfully.');
    }
}
