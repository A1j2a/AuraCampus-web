<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\AcademicSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeStructureController extends Controller
{
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;

        $session = auth()->user()->getActiveAcademicSession();
        $feeStructures = FeeStructure::where('school_id', $schoolId)
            ->where('academic_session_id', $session?->id)
            ->latest()
            ->get();
        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->orderBy('section')->get();

        return view('school.fees.index', compact('feeStructures', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:one_time,monthly,quarterly,annually',
            'applicable_classes' => 'nullable|array',
            'applicable_classes.*' => 'exists:classes,id',
        ]);

        $schoolId = auth()->user()->school_id;

        $session = auth()->user()->getActiveAcademicSession();

        FeeStructure::create([
            'school_id' => $schoolId,
            'academic_session_id' => $session ? $session->id : 1,
            'name' => $request->name,
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'applicable_classes' => $request->applicable_classes,
        ]);

        return redirect()->route('school.fees.index')
            ->with('success', 'Fee structure category added successfully!');
    }
}
