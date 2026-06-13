<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;

        $subjects = Subject::where('school_id', $schoolId)
            ->withCount('classes')
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::where('school_id', $schoolId)
            ->where('is_active', true)
            ->with(['subjects'])
            ->orderBy('name')
            ->orderBy('section')
            ->get();

        return view('school.subjects.index', compact('subjects', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'type' => 'required|in:theory,practical,both',
        ]);

        Subject::create([
            'school_id' => auth()->user()->school_id,
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'type'      => $request->type,
        ]);

        return redirect()->route('school.subjects')
            ->with('success', $request->name . ' added to curriculum!');
    }

    public function assign(Request $request): RedirectResponse
    {
        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'subject_ids'   => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $schoolId = auth()->user()->school_id;
        $class    = SchoolClass::where('school_id', $schoolId)->findOrFail($request->class_id);

        // Sync subjects to class (replace existing), teacher assigned later via timetable
        $class->subjects()->sync($request->subject_ids);

        return redirect()->route('school.subjects')
            ->with('success', 'Subjects assigned to ' . $class->name . ' - ' . $class->section . ' successfully! Assign teachers via Timetable.');
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'type' => 'required|in:theory,practical,both',
        ]);

        $subject->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'type' => $request->type,
        ]);

        return redirect()->route('school.subjects')->with('success', 'Subject updated successfully!');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('school.subjects')
            ->with('success', 'Subject removed.');
    }

    public function detachClassSubject(SchoolClass $class, Subject $subject): RedirectResponse
    {
        $class->subjects()->detach($subject->id);

        return redirect()->route('school.subjects')
            ->with('success', $subject->name . ' has been removed from class ' . $class->name . ' - ' . $class->section . '.');
    }
}
