<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentDetail;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        $students = User::where('school_id', auth()->user()->school_id)
                        ->role('student')
                        ->with(['studentDetail.class'])
                        ->latest()
                        ->get();

        $classes = SchoolClass::orderBy('name')->orderBy('section')->get();

        return view('school.students.index', compact('students', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'class_id' => 'required|exists:classes,id',
            'admission_number' => 'required|string|max:50',
            'roll_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'school_id' => auth()->user()->school_id,
        ]);
        $user->assignRole('student');

        StudentDetail::create([
            'user_id' => $user->id,
            'school_id' => auth()->user()->school_id,
            'class_id' => $request->class_id,
            'admission_number' => $request->admission_number,
            'roll_number' => $request->roll_number,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
        ]);

        return redirect()->route('school.students')
                         ->with('success', $request->name . ' has been registered!');
    }
}
