<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TeacherDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = User::where('school_id', auth()->user()->school_id)
                        ->role('teacher')
                        ->with('teacherDetail')
                        ->latest()
                        ->get();

        return view('school.teachers.index', compact('teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'employee_id' => 'required|string|max:50',
            'designation' => 'required|string|max:255',
            'qualification' => 'required|string|max:255',
            'joining_date' => 'nullable|date',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'school_id' => auth()->user()->school_id,
        ]);
        $user->assignRole('teacher');

        TeacherDetail::create([
            'user_id' => $user->id,
            'employee_id' => $request->employee_id,
            'designation' => $request->designation,
            'qualification' => $request->qualification,
            'joining_date' => $request->joining_date,
        ]);

        return redirect()->route('school.teachers')
                         ->with('success', $request->name . ' has been onboarded!');
    }
}
