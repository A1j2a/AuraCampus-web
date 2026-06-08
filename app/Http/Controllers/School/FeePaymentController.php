<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeePaymentController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->orderBy('section')->get();

        $selectedClassId = $request->query('class_id');
        $selectedStatus = $request->query('status');

        $session = auth()->user()->getActiveAcademicSession();
        $query = FeePayment::where('school_id', $schoolId)
            ->whereHas('feeStructure', function ($q) use ($session) {
                $q->where('academic_session_id', $session?->id);
            })
            ->with(['student.studentDetail.class', 'feeStructure'])
            ->latest();

        if ($selectedClassId) {
            $query->whereHas('student.studentDetail', function ($q) use ($selectedClassId) {
                $q->where('class_id', $selectedClassId);
            });
        }

        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        $payments = $query->get();

        return view('school.fees.payments', compact('payments', 'classes', 'selectedClassId', 'selectedStatus'));
    }

    public function create(): View
    {
        $schoolId = auth()->user()->school_id;

        // Fetch students and fee categories for dropdowns
        $students = User::where('school_id', $schoolId)
            ->role('student')
            ->with('studentDetail.class')
            ->orderBy('name')
            ->get();

        $session = auth()->user()->getActiveAcademicSession();
        $feeStructures = FeeStructure::where('school_id', $schoolId)
            ->where('academic_session_id', $session?->id)
            ->get();

        return view('school.fees.collect', compact('students', 'feeStructures'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,upi,bank_transfer,card',
            'remarks' => 'nullable|string|max:255',
        ]);

        $schoolId = auth()->user()->school_id;

        $student = User::where('school_id', $schoolId)->role('student')->findOrFail($request->student_id);
        $feeStructure = FeeStructure::where('school_id', $schoolId)->findOrFail($request->fee_structure_id);

        $amountPaid = floatval($request->amount_paid);
        $status = $amountPaid >= $feeStructure->amount ? 'paid' : 'partial';

        // Auto-generate receipt number
        $receiptNumber = 'REC-' . time() . rand(100, 999);

        $payment = FeePayment::create([
            'school_id' => $schoolId,
            'student_id' => $student->id,
            'fee_structure_id' => $feeStructure->id,
            'amount_paid' => $amountPaid,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'receipt_number' => $receiptNumber,
            'status' => $status,
            'remarks' => $request->remarks,
            'collected_by' => auth()->id(),
        ]);

        return redirect()->route('school.fees.payments')
            ->with('success', 'Fee collection recorded! Receipt: ' . $receiptNumber);
    }

    public function receipt(FeePayment $payment): View
    {
        $schoolId = auth()->user()->school_id;

        // Tenant Isolation
        if ($payment->school_id !== $schoolId) {
            abort(403, 'Unauthorized.');
        }

        $payment->load(['student.studentDetail.class', 'feeStructure', 'collector']);

        return view('school.fees.receipt', compact('payment'));
    }

    public function report(): View
    {
        $session = auth()->user()->getActiveAcademicSession();

        // Fetch metrics
        $payments = FeePayment::where('school_id', $schoolId)
            ->whereHas('feeStructure', function ($q) use ($session) {
                $q->where('academic_session_id', $session?->id);
            })
            ->get();

        $totalCollected = $payments->sum('amount_paid');
        $upiCollected = $payments->where('payment_method', 'upi')->sum('amount_paid');
        $cashCollected = $payments->where('payment_method', 'cash')->sum('amount_paid');
        $bankCollected = $payments->where('payment_method', 'bank_transfer')->sum('amount_paid');
        $cardCollected = $payments->where('payment_method', 'card')->sum('amount_paid');

        // Group by class
        $classCollections = [];
        $classes = SchoolClass::where('school_id', $schoolId)->get();
        foreach ($classes as $class) {
            $classPayments = FeePayment::where('school_id', $schoolId)
                ->whereHas('feeStructure', function ($q) use ($session) {
                    $q->where('academic_session_id', $session?->id);
                })
                ->whereHas('student.studentDetail', function ($q) use ($class) {
                    $q->where('class_id', $class->id);
                })
                ->sum('amount_paid');

            if ($classPayments > 0) {
                $classCollections[] = [
                    'name' => $class->name . ' - ' . $class->section,
                    'amount' => $classPayments
                ];
            }
        }

        return view('school.fees.report', compact(
            'totalCollected',
            'upiCollected',
            'cashCollected',
            'bankCollected',
            'cardCollected',
            'classCollections'
        ));
    }
}
