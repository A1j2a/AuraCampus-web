<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SyllabusChapter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumTrackerController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        // Fetch all classes for filter dropdown
        $classes = SchoolClass::where('school_id', $schoolId)
            ->orderBy('name')
            ->orderBy('section')
            ->get();

        $selectedClassId = $request->query('class_id') ?: ($classes->first()?->id ?? null);
        $selectedClass = $selectedClassId ? SchoolClass::where('school_id', $schoolId)->find($selectedClassId) : null;

        // Fetch subjects associated with selected class, or all subjects as fallback
        if ($selectedClass) {
            $subjects = $selectedClass->subjects()->orderBy('name')->get();
            if ($subjects->isEmpty()) {
                $subjects = Subject::where('school_id', $schoolId)->orderBy('name')->get();
            }
        } else {
            $subjects = Subject::where('school_id', $schoolId)->orderBy('name')->get();
        }

        $selectedSubjectId = $request->query('subject_id') ?: ($subjects->first()?->id ?? null);

        // Fetch chapters for selected class and subject
        $chapters = collect();
        $stats = [
            'total' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'not_started' => 0,
            'completed_pct' => 0,
            'in_progress_pct' => 0,
            'not_started_pct' => 0,
        ];

        if ($selectedClassId && $selectedSubjectId) {
            $chapters = SyllabusChapter::where('school_id', $schoolId)
                ->where('class_id', $selectedClassId)
                ->where('subject_id', $selectedSubjectId)
                ->with('teacher')
                ->orderBy('chapter_no')
                ->get();

            $stats['total'] = $chapters->count();
            if ($stats['total'] > 0) {
                $stats['completed'] = $chapters->where('status', 'completed')->count();
                $stats['in_progress'] = $chapters->where('status', 'in_progress')->count();
                $stats['not_started'] = $chapters->where('status', 'not_started')->count();

                $stats['completed_pct'] = round(($stats['completed'] / $stats['total']) * 100);
                $stats['in_progress_pct'] = round(($stats['in_progress'] / $stats['total']) * 100);
                $stats['not_started_pct'] = round(($stats['not_started'] / $stats['total']) * 100);
            }
        }

        return view('school.curriculum.index', compact(
            'classes',
            'subjects',
            'selectedClassId',
            'selectedSubjectId',
            'chapters',
            'stats'
        ));
    }
}
