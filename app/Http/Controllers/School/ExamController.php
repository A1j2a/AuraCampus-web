<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;

        $session = auth()->user()->getActiveAcademicSession();
        $exams = Exam::where('school_id', $schoolId)
            ->where('academic_session_id', $session?->id)
            ->withCount('schedules')
            ->latest()
            ->get();

        return view('school.exams.index', compact('exams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:unit_test,mid_term,final,practical',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $schoolId = auth()->user()->school_id;

        // Get active academic session
        $session = auth()->user()->getActiveAcademicSession();

        Exam::create([
            'school_id' => $schoolId,
            'academic_session_id' => $session->id,
            'name' => $request->name,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'upcoming',
        ]);

        return redirect()->route('school.exams')
            ->with('success', 'Exam created successfully!');
    }

    public function show(Exam $exam): View
    {
        // Ensure tenant isolation
        if ($exam->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized.');
        }

        $schoolId = auth()->user()->school_id;

        // Fetch schedules with relationships
        $schedules = ExamSchedule::where('exam_id', $exam->id)
            ->with(['class', 'subject'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        // Fetch classes and subjects for scheduling dropdowns
        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->orderBy('section')->get();
        $subjects = Subject::where('school_id', $schoolId)->orderBy('name')->get();

        return view('school.exams.show', compact('exam', 'schedules', 'classes', 'subjects'));
    }

    public function storeSchedule(Request $request, Exam $exam): RedirectResponse
    {
        // Ensure tenant isolation
        if ($exam->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'max_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:1|lte:max_marks',
        ]);

        ExamSchedule::create([
            'exam_id' => $exam->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'exam_date' => $request->exam_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'max_marks' => $request->max_marks,
            'passing_marks' => $request->passing_marks,
        ]);

        return redirect()->route('school.exams.show', $exam)
            ->with('success', 'Subject scheduled successfully!');
    }
}
