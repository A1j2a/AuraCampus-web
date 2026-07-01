<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\StudentDetail;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class HomeworkManagementController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        // Fetch filter options
        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->orderBy('section')->get();
        $subjects = Subject::where('school_id', $schoolId)->orderBy('name')->get();
        $teachers = User::where('school_id', $schoolId)->role('teacher')->orderBy('name')->get();

        // Get filtered homework list
        $homeworkQuery = Homework::where('school_id', $schoolId)
            ->with(['class', 'subject', 'teacher'])
            ->withCount('submissions');

        if ($request->filled('class_id')) {
            $homeworkQuery->where('class_id', $request->class_id);
        }
        if ($request->filled('subject_id')) {
            $homeworkQuery->where('subject_id', $request->subject_id);
        }
        if ($request->filled('teacher_id')) {
            $homeworkQuery->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('status')) {
            $homeworkQuery->where('status', $request->status);
        }

        $homeworks = $homeworkQuery->latest()->paginate(15)->withQueryString();

        // Count class students to calculate completion rate
        $classStudentCounts = StudentDetail::where('school_id', $schoolId)
            ->groupBy('class_id')
            ->selectRaw('class_id, count(*) as count')
            ->pluck('count', 'class_id')
            ->toArray();

        return view('school.homework.index', compact('homeworks', 'classes', 'subjects', 'teachers', 'classStudentCounts'));
    }

    public function show(Homework $homework): View
    {
        if ($homework->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        // Fetch student details in the class
        $students = User::where('school_id', $homework->school_id)
            ->whereHas('studentDetail', function ($query) use ($homework) {
                $query->where('class_id', $homework->class_id);
            })
            ->with(['studentDetail'])
            ->get();

        // Fetch submissions for this homework keyed by student_id
        $submissions = \App\Models\HomeworkSubmission::where('homework_id', $homework->id)
            ->get()
            ->keyBy('student_id');

        return view('school.homework.show', compact('homework', 'students', 'submissions'));
    }

    public function destroy(Homework $homework): RedirectResponse
    {
        if ($homework->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        // Delete submissions first
        $homework->submissions()->delete();
        $homework->delete();

        return redirect()->route('school.homework.index')->with('success', 'Homework record deleted successfully.');
    }
}
