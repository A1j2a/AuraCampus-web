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
use Illuminate\Support\Facades\DB;

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
        $teacherStats    = collect();
        $slots           = collect();

        // Load periods configuration early so it's available in all closures below
        $school  = auth()->user()->school;
        $periods = $school->settings['timetable_periods'] ?? [
            1 => ['name' => 'Period 1', 'start' => '08:00', 'end' => '08:45'],
            2 => ['name' => 'Period 2', 'start' => '08:45', 'end' => '09:30'],
            3 => ['name' => 'Period 3', 'start' => '09:30', 'end' => '10:15'],
            4 => ['name' => 'Period 4', 'start' => '10:30', 'end' => '11:15'],
            5 => ['name' => 'Period 5', 'start' => '11:15', 'end' => '12:00'],
            6 => ['name' => 'Period 6', 'start' => '12:00', 'end' => '12:45'],
        ];
        $periods = collect($periods)->mapWithKeys(function($p, $key) {
            return [(int)$key => [
                'name'  => $p['name']  ?? 'Period ' . $key,
                'start' => $p['start'] ?? '08:00',
                'end'   => $p['end']   ?? '08:45',
            ]];
        })->toArray();

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

            // Calculate Analytics & Teacher Workloads
            $assignedTeacherIds = $slots->pluck('teacher_id')->unique()->toArray();
            $teacherIdsToLoad = array_unique(array_merge(
                $classTeachers->pluck('id')->toArray(),
                $assignedTeacherIds
            ));

            $teachersForStats = User::whereIn('id', $teacherIdsToLoad)
                ->with('subjects')
                ->orderBy('name')
                ->get();

            $schoolWideSlots = TimetableSlot::where('school_id', $schoolId)->get();
            $schoolWideLoad = $schoolWideSlots->groupBy('teacher_id')->map->count();
            $classSlotsByTeacher = $slots->groupBy('teacher_id');

            // Group school-wide slots by teacher → period numbers assigned across ALL classes
            $schoolWidePeriodsByTeacher = $schoolWideSlots->groupBy('teacher_id')->map(function($tSlots) {
                return $tSlots->pluck('period_number')->unique()->toArray();
            });

            $teacherStats = $teachersForStats->map(function($teacher) use ($schoolWideLoad, $classSlotsByTeacher, $periods, $schoolWidePeriodsByTeacher) {
                $teacherClassSlots    = $classSlotsByTeacher->get($teacher->id, collect());
                $classPeriodsCount    = $teacherClassSlots->count();
                $classSubjects        = $teacherClassSlots->pluck('subject.name')->unique()->values();
                $allPeriodNums        = array_keys($periods);

                // School-wide: which period numbers is this teacher busy in (across ALL classes)?
                $busyPeriodNums       = $schoolWidePeriodsByTeacher->get($teacher->id, []);
                // Free = configured periods minus school-wide busy periods
                $freePeriodNums       = array_values(array_diff($allPeriodNums, $busyPeriodNums));

                $totalPeriodsCount    = $schoolWideLoad->get($teacher->id, 0);

                // Teacher's own subject names (from their profile, not slot assignments)
                $teacherSubjects      = $teacher->subjects->pluck('name')->toArray();

                return [
                    'id'                  => $teacher->id,
                    'name'                => $teacher->name,
                    'profile_image'       => $teacher->profile_image,
                    'teacher_subjects'    => $teacherSubjects,
                    'class_periods'       => $classPeriodsCount,
                    'class_subjects'      => $classSubjects,
                    'free_period_nums'    => $freePeriodNums,
                    'total_periods'       => $totalPeriodsCount,
                ];
            });
        }

        // Rooms defined school-wide (can be extended to DB later)
        $rooms = ['Room 101', 'Room 102', 'Room 103', 'Room 104', 'Room 105',
                  'Room 201', 'Room 202', 'Room 203', 'Lab 1', 'Lab 2',
                  'Computer Lab', 'Library', 'Art Room', 'Music Room'];

        $days = [
            1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
        ];

        return view('school.timetable.index', compact(
            'classes', 'selectedClassId', 'selectedClass',
            'slotsByDayAndPeriod', 'classSubjects', 'classTeachers', 'allTeachers',
            'rooms', 'days', 'periods', 'teacherStats', 'slots'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = auth()->user()->school;
        $schoolId = $school->id;
        $periods = $school->settings['timetable_periods'] ?? [
            1 => ['name' => 'Period 1', 'start' => '08:00', 'end' => '08:45'],
            2 => ['name' => 'Period 2', 'start' => '08:45', 'end' => '09:30'],
            3 => ['name' => 'Period 3', 'start' => '09:30', 'end' => '10:15'],
            4 => ['name' => 'Period 4', 'start' => '10:30', 'end' => '11:15'],
            5 => ['name' => 'Period 5', 'start' => '11:15', 'end' => '12:00'],
            6 => ['name' => 'Period 6', 'start' => '12:00', 'end' => '12:45'],
        ];
        $periodsKeys = array_keys($periods);

        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'subject_id'    => 'required|exists:subjects,id',
            'teacher_id'    => 'required|exists:users,id',
            'day_of_week'   => 'required|integer|between:1,6',
            'period_number' => 'required|integer|in:' . implode(',', $periodsKeys),
            'start_time'    => 'required',
            'end_time'      => 'required|after:start_time',
            'room_number'   => 'nullable|string|max:50',
        ]);

        $teacher = User::where('school_id', $schoolId)->role('teacher')->findOrFail($request->teacher_id);

        $days = [
            1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
        ];

        $isDaily = $request->has('is_daily');
        $daysToAllocate = $isDaily ? range(1, 6) : [(int) $request->day_of_week];

        // Validate clashes for all days
        foreach ($daysToAllocate as $day) {
            // 1. Teacher clash
            $teacherClash = TimetableSlot::where('day_of_week', $day)
                ->where('period_number', $request->period_number)
                ->where('teacher_id', $request->teacher_id)
                ->where('class_id', '!=', $request->class_id)
                ->with('class')
                ->first();

            if ($teacherClash) {
                $dayName = $days[$day] ?? 'this day';
                return back()->withInput()->withErrors([
                    'teacher_id' => '⚠️ Teacher Clash on ' . $dayName . '! ' . $teacher->name . ' is already assigned to Class ' .
                        $teacherClash->class->name . '-' . $teacherClash->class->section . ' on this day.'
                ]);
            }

            // 2. Same subject clash
            $subjectClash = TimetableSlot::where('class_id', $request->class_id)
                ->where('day_of_week', $day)
                ->where('subject_id', $request->subject_id)
                ->where('period_number', '!=', $request->period_number)
                ->first();

            if ($subjectClash) {
                $subject = Subject::find($request->subject_id);
                $dayName = $days[$day] ?? 'this day';
                return back()->withInput()->withErrors([
                    'subject_id' => '⚠️ Subject already scheduled on ' . $dayName . '! ' . $subject->name .
                        ' is already in Period ' . $subjectClash->period_number . '.'
                ]);
            }

            // 3. Room clash
            if ($request->room_number) {
                $roomClash = TimetableSlot::where('day_of_week', $day)
                    ->where('period_number', $request->period_number)
                    ->where('room_number', $request->room_number)
                    ->where('class_id', '!=', $request->class_id)
                    ->with('class')
                    ->first();

                if ($roomClash) {
                    $dayName = $days[$day] ?? 'this day';
                    return back()->withInput()->withErrors([
                        'room_number' => '⚠️ Room Clash on ' . $dayName . '! ' . $request->room_number .
                            ' is already booked by Class ' . $roomClash->class->name . '-' . $roomClash->class->section . ' during this period.'
                    ]);
                }
            }
        }

        // Save or update slots within transaction
        DB::transaction(function () use ($schoolId, $request, $daysToAllocate) {
            foreach ($daysToAllocate as $day) {
                TimetableSlot::updateOrCreate(
                    [
                        'school_id'     => $schoolId,
                        'class_id'      => $request->class_id,
                        'day_of_week'   => $day,
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
            }

            // Update teacher in class_subject pivot
            \DB::table('class_subject')
                ->where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->update(['teacher_id' => $request->teacher_id]);
        });

        return redirect()->route('school.timetable.index', ['class_id' => $request->class_id])
            ->with('success', 'Period allocated successfully' . ($isDaily ? ' for all days!' : '!'));
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

    public function updatePeriods(Request $request): RedirectResponse
    {
        $request->validate([
            'periods' => 'required|array|min:1|max:12',
            'periods.*.start' => 'required',
            'periods.*.end' => 'required|after:periods.*.start',
            'periods.*.name' => 'required|string|max:50',
        ]);

        $school = auth()->user()->school;
        $settings = $school->settings ?? [];
        $settings['timetable_periods'] = $request->periods;
        
        $school->update(['settings' => $settings]);

        return back()->with('success', 'Period timings updated successfully!');
    }
}
