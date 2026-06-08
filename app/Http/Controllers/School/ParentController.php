<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ParentController extends Controller
{
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;

        // Get all parent-role users in the school with their linked students
        $parents = User::where('school_id', $schoolId)
            ->role('parent')
            ->with(['students' => function ($query) use ($schoolId) {
                // Ensure students belong to the same school
                $query->where('school_id', $schoolId);
            }])
            ->latest()
            ->get();

        // Get all student-role users in the school for linking options
        $students = User::where('school_id', $schoolId)
            ->role('student')
            ->with('studentDetail.class')
            ->orderBy('name')
            ->get();

        return view('school.parents.index', compact('parents', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'student_id' => 'nullable|exists:users,id',
            'relationship' => 'nullable|required_with:student_id|string|max:50',
        ]);

        $schoolId = auth()->user()->school_id;

        $parent = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'school_id' => $schoolId,
        ]);
        $parent->assignRole('parent');

        // Link to student if selected
        if ($request->student_id) {
            $student = User::where('school_id', $schoolId)->role('student')->findOrFail($request->student_id);
            $parent->students()->attach($student->id, [
                'relationship' => $request->relationship,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('school.parents')
            ->with('success', 'Parent account for ' . $parent->name . ' has been created successfully!');
    }

    public function link(Request $request): RedirectResponse
    {
        $request->validate([
            'parent_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:users,id',
            'relationship' => 'required|string|max:50',
        ]);

        $schoolId = auth()->user()->school_id;

        // Ensure both users exist in this school and have correct roles
        $parent = User::where('school_id', $schoolId)->role('parent')->findOrFail($request->parent_id);
        $student = User::where('school_id', $schoolId)->role('student')->findOrFail($request->student_id);

        // Link student to parent
        $parent->students()->syncWithoutDetaching([
            $student->id => [
                'relationship' => $request->relationship,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        return redirect()->route('school.parents')
            ->with('success', 'Student linked to ' . $parent->name . ' successfully!');
    }
}
