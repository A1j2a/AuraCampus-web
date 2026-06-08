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
        $classes = SchoolClass::with('teacher')
                              ->withCount('students')
                              ->orderBy('name')
                              ->orderBy('section')
                              ->get();

        return view('school.classes.index', compact('classes'));
    }

    /**
     * Store a newly created class.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'section' => 'required|string|max:10',
            'room_number' => 'nullable|string|max:50',
        ]);

        SchoolClass::create([
            'school_id' => auth()->user()->school_id,
            'name' => $request->name,
            'section' => strtoupper($request->section),
            'room_number' => $request->room_number,
        ]);

        return redirect()->route('school.classes')
                         ->with('success', $request->name . ' - Section ' . strtoupper($request->section) . ' created!');
    }

    /**
     * Remove the specified class.
     */
    public function destroy(SchoolClass $class): RedirectResponse
    {
        $class->delete();

        return redirect()->route('school.classes')
                         ->with('success', 'Class removed successfully.');
    }
}
