<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::orderBy('name')->get();

        return view('school.subjects.index', compact('subjects'));
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
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'type' => $request->type,
        ]);

        return redirect()->route('school.subjects')
                         ->with('success', $request->name . ' added to curriculum!');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('school.subjects')
                         ->with('success', 'Subject removed.');
    }
}
