<?php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Notice;
use App\Models\TimetableSlot;
use App\Models\Mark;
use App\Models\FeeStructure;
use App\Models\FeePayment;
use App\Models\AcademicSession;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentApiController extends Controller
{
    use ApiResponse;

    /**
     * Helper to verify if the student is indeed linked to the logged-in parent.
     */
    private function verifyChildAccess(User $student): bool
    {
        return auth()->user()->students()->where('student_id', $student->id)->exists();
    }

    /**
     * Get children linked to the parent.
     */
    public function children(): JsonResponse
    {
        $children = auth()->user()->students()->with('studentDetail.class')->get();

        return $this->successResponse(
            $children->map(function ($child) {
                $detail = $child->studentDetail;
                return [
                    'student_id' => $child->id,
                    'name' => $child->name,
                    'email' => $child->email,
                    'roll_number' => $detail?->roll_number,
                    'admission_number' => $detail?->admission_number,
                    'class' => $detail?->class ? [
                        'id' => $detail->class->id,
                        'name' => $detail->class->name,
                        'section' => $detail->class->section,
                    ] : null,
                ];
            })
        );
    }

    /**
     * Selected child's dashboard stats.
     */
    public function studentDashboard(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied. This student is not linked to your account.', 403);
        }

        $detail = $student->studentDetail;
        $class = $detail?->class;

        // Calculate attendance rate
        $totalDays = Attendance::where('student_id', $student->id)->count();
        $presentDays = Attendance::where('student_id', $student->id)
            ->whereIn('status', ['present', 'late'])
            ->count();
        $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 100;

        // Notices
        $notices = Notice::latest('published_at')->take(3)->get();

        // Calculate outstanding dues
        $session = AcademicSession::where('school_id', $student->school_id)
            ->where('is_active', true)
            ->first();

        $structures = $session 
            ? FeeStructure::where('academic_session_id', $session->id)->get() 
            : collect();

        $payments = FeePayment::where('student_id', $student->id)->get();
        $totalPaid = $payments->sum('amount_paid');

        $totalApplicable = 0;
        $classId = $detail?->class_id;
        foreach ($structures as $struct) {
            $classes = is_string($struct->applicable_classes) 
                ? json_decode($struct->applicable_classes, true) 
                : $struct->applicable_classes;

            if (empty($classes) || in_array($classId, $classes)) {
                $totalApplicable += $struct->amount;
            }
        }
        $totalDues = max(0, $totalApplicable - $totalPaid);

        return $this->successResponse([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'class_name' => $class ? $class->name . '-' . $class->section : 'N/A',
                'class_teacher' => $class?->teacher ? $class->teacher->name : 'N/A',
            ],
            'stats' => [
                'attendance_percentage' => $attendancePercentage,
                'total_paid' => $totalPaid,
                'outstanding_dues' => $totalDues,
            ],
            'notices' => $notices->map(function ($notice) {
                return [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'content' => $notice->content,
                    'type' => $notice->type,
                    'published_at' => $notice->published_at,
                ];
            }),
        ]);
    }

    /**
     * Selected child's attendance list.
     */
    public function studentAttendance(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $records = Attendance::where('student_id', $student->id)
            ->orderByDesc('date')
            ->get();

        return $this->successResponse(
            $records->map(function ($rec) {
                return [
                    'id' => $rec->id,
                    'date' => $rec->date,
                    'status' => $rec->status,
                    'remarks' => $rec->remarks,
                ];
            })
        );
    }

    /**
     * Selected child's timetable.
     */
    public function studentTimetable(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $classId = $student->studentDetail?->class_id;

        if (!$classId) {
            return $this->errorResponse('Student is not assigned to any class.', 400);
        }

        $slots = TimetableSlot::with(['subject', 'teacher'])
            ->where('class_id', $classId)
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();

        return $this->successResponse(
            $slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'day_of_week' => $slot->day_of_week,
                    'period_number' => $slot->period_number,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'room_number' => $slot->room_number,
                    'subject' => [
                        'name' => $slot->subject->name,
                        'code' => $slot->subject->code,
                    ],
                    'teacher' => $slot->teacher ? [
                        'name' => $slot->teacher->name,
                    ] : null,
                ];
            })
        );
    }

    /**
     * Selected child's exams and marks.
     */
    public function studentExams(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $marks = Mark::with(['examSchedule.exam', 'examSchedule.subject'])
            ->where('student_id', $student->id)
            ->get();

        return $this->successResponse(
            $marks->map(function ($mark) {
                $sched = $mark->examSchedule;
                return [
                    'id' => $mark->id,
                    'marks_obtained' => $mark->marks_obtained,
                    'grade' => $mark->grade,
                    'remarks' => $mark->remarks,
                    'exam' => [
                        'id' => $sched->exam->id,
                        'name' => $sched->exam->name,
                        'type' => $sched->exam->type,
                    ],
                    'subject' => [
                        'name' => $sched->subject->name,
                        'code' => $sched->subject->code,
                    ],
                    'max_marks' => $sched->max_marks,
                    'passing_marks' => $sched->passing_marks,
                ];
            })
        );
    }

    /**
     * Selected child's fees and payments log.
     */
    public function studentFees(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $detail = $student->studentDetail;
        $session = AcademicSession::where('school_id', $student->school_id)
            ->where('is_active', true)
            ->first();

        $structures = $session 
            ? FeeStructure::where('academic_session_id', $session->id)->get() 
            : collect();

        $payments = FeePayment::where('student_id', $student->id)->get();
        $totalPaid = $payments->sum('amount_paid');

        // Map applicable bills
        $classId = $detail?->class_id;
        $bills = [];
        $totalApplicable = 0;

        foreach ($structures as $struct) {
            $classes = is_string($struct->applicable_classes) 
                ? json_decode($struct->applicable_classes, true) 
                : $struct->applicable_classes;

            if (empty($classes) || in_array($classId, $classes)) {
                $totalApplicable += $struct->amount;
                $structPayment = $payments->where('fee_structure_id', $struct->id)->first();
                
                $bills[] = [
                    'structure_id' => $struct->id,
                    'name' => $struct->name,
                    'amount' => $struct->amount,
                    'frequency' => $struct->frequency,
                    'status' => $structPayment 
                        ? ($structPayment->amount_paid >= $struct->amount ? 'paid' : 'partial') 
                        : 'unpaid',
                    'amount_paid' => $structPayment ? $structPayment->amount_paid : 0.00,
                ];
            }
        }

        $totalDues = max(0, $totalApplicable - $totalPaid);

        return $this->successResponse([
            'dues_summary' => [
                'total_applicable' => $totalApplicable,
                'total_paid' => $totalPaid,
                'outstanding_dues' => $totalDues,
            ],
            'bills' => $bills,
            'payments' => $payments->map(function ($pay) {
                return [
                    'id' => $pay->id,
                    'amount_paid' => $pay->amount_paid,
                    'payment_date' => $pay->payment_date,
                    'payment_method' => $pay->payment_method,
                    'receipt_number' => $pay->receipt_number,
                    'status' => $pay->status,
                    'remarks' => $pay->remarks,
                ];
            })
        ]);
    }
}
