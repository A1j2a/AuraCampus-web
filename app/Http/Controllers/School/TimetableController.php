<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TimetableSlot;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId        = auth()->user()->school_id;
        $selectedClassId = $request->query('class_id');
        $selectedClass   = null;
        $slotsByDayAndPeriod = [];
        $classSubjects   = collect();
        $classTeachers   = collect();
        $allTeachers     = collect();

        $classes = SchoolClass::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('section')
            ->get();

        if ($selectedClassId) {
            $selectedClass = SchoolClass::where('school_id', $schoolId)->findOrFail($selectedClassId);

            // Only subjects assigned to this class
            $classSubjects = $selectedClass->subjects()->orderBy('name')->get();

            // Only teachers assigned to this class
            $classTeachers = User::where('school_id', $schoolId)
                ->role('teacher')
                ->whereHas('teacherClassSections', fn($q) => $q->where('class_id', $selectedClassId))
                ->orderBy('name')
                ->get();

            // All teachers with subjects for dynamic scheduling filters
            $allTeachers = User::where('school_id', $schoolId)
                ->role('teacher')
                ->with('subjects')
                ->orderBy('name')
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'subject_ids' => $t->subjects->pluck('id')->toArray()
                ]);

            $slots = TimetableSlot::where('class_id', $selectedClassId)
                ->with(['subject', 'teacher'])
                ->get();

            foreach ($slots as $slot) {
                $slotsByDayAndPeriod[$slot->day_of_week][$slot->period_number] = $slot;
            }
        }

        // Rooms defined school-wide (can be extended to DB later)
        $rooms = ['Room 101', 'Room 102', 'Room 103', 'Room 104', 'Room 105',
                  'Room 201', 'Room 202', 'Room 203', 'Lab 1', 'Lab 2',
                  'Computer Lab', 'Library', 'Art Room', 'Music Room'];

        $days = [
            1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
        ];

        $periods = [
            1 => ['name' => 'Period 1', 'start' => '08:00', 'end' => '08:45'],
            2 => ['name' => 'Period 2', 'start' => '08:45', 'end' => '09:30'],
            3 => ['name' => 'Period 3', 'start' => '09:30', 'end' => '10:15'],
            4 => ['name' => 'Period 4', 'start' => '10:30', 'end' => '11:15'],
            5 => ['name' => 'Period 5', 'start' => '11:15', 'end' => '12:00'],
            6 => ['name' => 'Period 6', 'start' => '12:00', 'end' => '12:45'],
        ];

        return view('school.timetable.index', compact(
            'classes', 'selectedClassId', 'selectedClass',
            'slotsByDayAndPeriod', 'classSubjects', 'classTeachers', 'allTeachers',
            'rooms', 'days', 'periods'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = auth()->user()->school_id;

        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'subject_id'    => 'required|exists:subjects,id',
            'teacher_id'    => 'required|exists:users,id',
            'day_of_week'   => 'required|integer|between:1,6',
            'period_number' => 'required|integer|between:1,6',
            'start_time'    => 'required',
            'end_time'      => 'required|after:start_time',
            'room_number'   => 'nullable|string|max:50',
        ]);

        $teacher = User::where('school_id', $schoolId)->role('teacher')->findOrFail($request->teacher_id);

        // 1. Teacher clash — same teacher same day same period different class
        $teacherClash = TimetableSlot::where('day_of_week', $request->day_of_week)
            ->where('period_number', $request->period_number)
            ->where('teacher_id', $request->teacher_id)
            ->where('class_id', '!=', $request->class_id)
            ->with('class')
            ->first();

        if ($teacherClash) {
            return back()->withInput()->withErrors([
                'teacher_id' => '⚠️ Teacher Clash! ' . $teacher->name . ' is already assigned to Class ' .
                    $teacherClash->class->name . '-' . $teacherClash->class->section . ' on this day & period.'
            ]);
        }

        // 2. Same subject already assigned on same day in this class
        $subjectClash = TimetableSlot::where('class_id', $request->class_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('subject_id', $request->subject_id)
            ->where('period_number', '!=', $request->period_number)
            ->first();

        if ($subjectClash) {
            $subject = Subject::find($request->subject_id);
            return back()->withInput()->withErrors([
                'subject_id' => '⚠️ Subject already scheduled! ' . $subject->name .
                    ' is already in Period ' . $subjectClash->period_number . ' on this day.'
            ]);
        }

        // 3. Room clash — same room same day same period different class
        if ($request->room_number) {
            $roomClash = TimetableSlot::where('day_of_week', $request->day_of_week)
                ->where('period_number', $request->period_number)
                ->where('room_number', $request->room_number)
                ->where('class_id', '!=', $request->class_id)
                ->with('class')
                ->first();

            if ($roomClash) {
                return back()->withInput()->withErrors([
                    'room_number' => '⚠️ Room Clash! ' . $request->room_number .
                        ' is already booked by Class ' . $roomClash->class->name . '-' . $roomClash->class->section . ' during this period.'
                ]);
            }
        }

        // Save or update slot
        TimetableSlot::updateOrCreate(
            [
                'school_id'     => $schoolId,
                'class_id'      => $request->class_id,
                'day_of_week'   => $request->day_of_week,
                'period_number' => $request->period_number,
            ],
            [
                'subject_id'  => $request->subject_id,
                'teacher_id'  => $request->teacher_id,
                'start_time'  => $request->start_time,
                'end_time'    => $request->end_time,
                'room_number' => $request->room_number,
            ]
        );

        // Update teacher in class_subject pivot
        \DB::table('class_subject')
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->update(['teacher_id' => $request->teacher_id]);

        return redirect()->route('school.timetable.index', ['class_id' => $request->class_id])
            ->with('success', 'Period allocated successfully!');
    }

    public function destroy(TimetableSlot $slot): RedirectResponse
    {
        if ($slot->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $classId = $slot->class_id;
        $slot->delete();

        return redirect()->route('school.timetable.index', ['class_id' => $classId])
            ->with('success', 'Period slot removed successfully.');
    }
}
