<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\StudentDetail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;

        $query = User::where('school_id', $schoolId)
            ->role('student')
            ->with(['studentDetail.class', 'students', 'credential'])
            ->latest();

        // Filters
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhereHas('studentDetail', fn($q) => $q->where('admission_number', 'like', "%$search%"));
            });
        }

        if (request('class_id')) {
            $query->whereHas('studentDetail', fn($q) => $q->where('class_id', request('class_id')));
        }

        if (request('status')) {
            $query->whereHas('studentDetail', fn($q) => $q->where('status', request('status')));
        }

        $students = $query->get();

        $classes = SchoolClass::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('section')
            ->get();

        return view('school.students.index', compact('students', 'classes'));
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'admission_number' => 'required|string|max:50',
            'roll_number'      => 'nullable|string|max:20',
            'dob'              => 'nullable|date',
            'gender'           => 'nullable|in:male,female,other',
            'blood_group'      => 'nullable|string|max:5',
            'class_id'         => 'required|exists:classes,id',
        ]);

        $student->update(['name' => $request->name]);

        $student->studentDetail()->updateOrCreate(
            ['user_id' => $student->id],
            [
                'school_id'        => $student->school_id,
                'admission_number' => $request->admission_number,
                'roll_number'      => $request->roll_number,
                'date_of_birth'    => $request->dob,
                'gender'           => $request->gender,
                'blood_group'      => $request->blood_group,
                'class_id'         => $request->class_id,
            ]
        );

        return redirect()->route('school.students')->with('success', $student->name . ' updated successfully!');
    }

    public function updateStatus(Request $request, User $student): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:active,inactive,transferred,graduated',
        ]);

        $student->studentDetail()->update(['status' => $request->status]);

        return redirect()->route('school.students')
            ->with('success', $student->name . ' status updated to ' . $request->status . '.');
    }

    public function transferClass(Request $request, User $student): RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $student->studentDetail()->update([
            'class_id' => $request->class_id,
            'status'   => 'active',
        ]);

        return redirect()->route('school.students')
            ->with('success', $student->name . ' transferred successfully!');
    }
}
