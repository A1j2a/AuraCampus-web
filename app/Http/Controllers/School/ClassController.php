<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassController extends Controller
{
    /**
     * Display a listing of classes for the school.
     */
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;

        $classes = SchoolClass::where('school_id', $schoolId)
                              ->with('teacher')
                              ->withCount('students')
                              ->orderBy('name')
                              ->orderBy('section')
                              ->get();

        $teachers = \App\Models\User::where('school_id', $schoolId)
                                    ->role('teacher')
                                    ->orderBy('name')
                                    ->get();

        return view('school.classes.index', compact('classes', 'teachers'));
    }

    /**
     * Store a newly created class.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'section'     => 'required|string|max:10',
            'room_number' => 'nullable|string|max:50',
            'teacher_id'  => 'nullable|exists:users,id',
        ]);

        $schoolId = auth()->user()->school_id;

        // Check if duplicate class/section exists in the same school
        $duplicate = SchoolClass::where('school_id', $schoolId)
            ->where('name', $request->name)
            ->where('section', strtoupper($request->section))
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['name' => 'This class and section already exists.'])->withInput();
        }

        SchoolClass::create([
            'school_id'   => $schoolId,
            'name'        => $request->name,
            'section'     => strtoupper($request->section),
            'room_number' => $request->room_number,
            'teacher_id'  => $request->teacher_id ?: null,
        ]);

        return redirect()->route('school.classes')
                         ->with('success', $request->name . ' - Section ' . strtoupper($request->section) . ' created!');
    }

    /**
     * Update the specified class.
     */
    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'section'     => 'required|string|max:10',
            'room_number' => 'nullable|string|max:50',
            'capacity'    => 'nullable|integer|min:1',
            'teacher_id'  => 'nullable|exists:users,id',
        ]);

        $schoolId = auth()->user()->school_id;

        // Check if duplicate class/section exists in the same school
        $duplicate = SchoolClass::where('school_id', $schoolId)
            ->where('name', $request->name)
            ->where('section', strtoupper($request->section))
            ->where('id', '!=', $class->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['name' => 'This class and section already exists.'])->withInput();
        }

        $class->update([
            'name'        => $request->name,
            'section'     => strtoupper($request->section),
            'room_number' => $request->room_number,
            'capacity'    => $request->capacity,
            'teacher_id'  => $request->teacher_id ?: null,
        ]);

        return redirect()->route('school.classes')->with('success', $request->name . ' - ' . strtoupper($request->section) . ' updated!');
    }

    /**
     * Remove the specified class.
     */
    public function destroy(SchoolClass $class): RedirectResponse
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($class) {
            // 1. Get student user IDs assigned to this class
            $studentUserIds = \App\Models\StudentDetail::where('class_id', $class->id)->pluck('user_id')->toArray();

            // 2. Detach students from parents (parent_student table)
            \Illuminate\Support\Facades\DB::table('parent_student')->whereIn('student_id', $studentUserIds)->delete();

            // 3. Delete student user credentials
            \App\Models\UserCredential::whereIn('user_id', $studentUserIds)->delete();

            // 4. Delete student details
            \App\Models\StudentDetail::where('class_id', $class->id)->delete();

            // 5. Delete student users
            \App\Models\User::whereIn('id', $studentUserIds)->delete();

            // 6. Delete timetable slots
            \App\Models\TimetableSlot::where('class_id', $class->id)->delete();

            // 7. Delete teacher assignments
            \App\Models\TeacherClassSection::where('class_id', $class->id)->delete();

            // 8. Delete attendances
            \App\Models\Attendance::where('class_id', $class->id)->delete();

            // 9. Delete exam schedules (and marks)
            $examScheduleIds = \App\Models\ExamSchedule::where('class_id', $class->id)->pluck('id')->toArray();
            \Illuminate\Support\Facades\DB::table('marks')->whereIn('exam_schedule_id', $examScheduleIds)->delete();
            \App\Models\ExamSchedule::where('class_id', $class->id)->delete();

            // 10. Detach subjects from class
            $class->subjects()->detach();

            // 11. Delete the class itself
            $class->delete();
        });

        return redirect()->route('school.classes')->with('success', 'Class and all associated records deleted successfully.');
    }
}
