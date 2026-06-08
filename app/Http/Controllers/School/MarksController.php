<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarksController extends Controller
{
    public function index(ExamSchedule $examSchedule): View
    {
        $schoolId = auth()->user()->school_id;

        // Tenant Isolation check
        $class = $examSchedule->class;
        if ($class->school_id !== $schoolId) {
            abort(403, 'Unauthorized.');
        }

        // Fetch students in this class
        $students = User::where('school_id', $schoolId)
            ->role('student')
            ->whereHas('studentDetail', function ($q) use ($class) {
                $q->where('class_id', $class->id);
            })
            ->with('studentDetail')
            ->orderBy('name')
            ->get();

        // Get entered marks
        $marks = Mark::where('exam_schedule_id', $examSchedule->id)
            ->get()
            ->keyBy('student_id');

        return view('school.marks.index', compact('examSchedule', 'students', 'marks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'exam_schedule_id' => 'required|exists:exam_schedules,id',
            'marks' => 'required|array',
            'marks.*.marks_obtained' => 'nullable|numeric|min:0',
            'marks.*.remarks' => 'nullable|string|max:255',
        ]);

        $schoolId = auth()->user()->school_id;
        $schedule = ExamSchedule::findOrFail($request->exam_schedule_id);

        // Tenant Isolation check
        if ($schedule->exam->school_id !== $schoolId) {
            abort(403, 'Unauthorized.');
        }

        $maxMarks = $schedule->max_marks;

        foreach ($request->marks as $studentId => $markData) {
            $obtained = $markData['marks_obtained'];

            if ($obtained === null || $obtained === '') {
                // If marks are empty, remove mark record if exists
                Mark::where('school_id', $schoolId)
                    ->where('exam_schedule_id', $schedule->id)
                    ->where('student_id', $studentId)
                    ->delete();
                continue;
            }

            $obtained = floatval($obtained);

            if ($obtained > $maxMarks) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['marks' => 'Obtained marks cannot exceed maximum marks (' . $maxMarks . ').']);
            }

            $percentage = ($obtained / $maxMarks) * 100;
            $grade = $this->calculateGrade($percentage);

            Mark::updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'exam_schedule_id' => $schedule->id,
                    'student_id' => $studentId,
                ],
                [
                    'marks_obtained' => $obtained,
                    'grade' => $grade,
                    'remarks' => $markData['remarks'] ?? null,
                    'entered_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('school.exams.show', $schedule->exam_id)
            ->with('success', 'Marks saved successfully!');
    }

    public function reportCardsIndex(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        $exams = Exam::where('school_id', $schoolId)->latest()->get();
        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->orderBy('section')->get();

        $selectedExamId = $request->query('exam_id');
        $selectedClassId = $request->query('class_id');

        $students = collect();
        if ($selectedExamId && $selectedClassId) {
            $students = User::where('school_id', $schoolId)
                ->role('student')
                ->whereHas('studentDetail', function ($q) use ($selectedClassId) {
                    $q->where('class_id', $selectedClassId);
                })
                ->with('studentDetail')
                ->orderBy('name')
                ->get();
        }

        return view('school.marks.report-cards-list', compact('exams', 'classes', 'students', 'selectedExamId', 'selectedClassId'));
    }

    public function reportCard(User $student, Exam $exam): View
    {
        $schoolId = auth()->user()->school_id;

        // Tenant Isolation
        if ($student->school_id !== $schoolId || $exam->school_id !== $schoolId) {
            abort(403, 'Unauthorized.');
        }

        // Fetch marks for this student and this exam
        $marks = Mark::where('student_id', $student->id)
            ->whereHas('examSchedule', function ($q) use ($exam) {
                $q->where('exam_id', $exam->id);
            })
            ->with(['examSchedule.subject', 'examSchedule.class'])
            ->get();

        $studentDetail = $student->studentDetail;
        $class = $studentDetail?->class;

        // Calculate totals
        $totalObtained = 0;
        $totalMax = 0;
        $passCount = 0;
        $failCount = 0;

        foreach ($marks as $mark) {
            $totalObtained += $mark->marks_obtained;
            $totalMax += $mark->examSchedule->max_marks;

            if ($mark->marks_obtained >= $mark->examSchedule->passing_marks) {
                $passCount++;
            } else {
                $failCount++;
            }
        }

        $overallPercentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;
        $overallGrade = $this->calculateGrade($overallPercentage);
        $resultStatus = $failCount === 0 && count($marks) > 0 ? 'PASS' : (count($marks) > 0 ? 'SUPPLEMENTARY' : 'N/A');

        return view('school.marks.report-card', compact(
            'student',
            'exam',
            'marks',
            'studentDetail',
            'class',
            'totalObtained',
            'totalMax',
            'overallPercentage',
            'overallGrade',
            'resultStatus'
        ));
    }

    private function calculateGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        if ($percentage >= 33) return 'D';
        return 'F';
    }
}
