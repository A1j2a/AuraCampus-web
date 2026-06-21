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
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\StudentLeaveRequest;
use App\Models\SchoolEvent;
use App\Models\Notification;
use App\Models\PtcBooking;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ParentApiController extends Controller
{
    use ApiResponse;

    // ─────────────────────────────────────────────────────────────
    // Helper: verify child belongs to this parent
    // ─────────────────────────────────────────────────────────────
    private function verifyChildAccess(User $student): bool
    {
        return auth()->user()->students()->where('student_id', $student->id)->exists();
    }

    // ─────────────────────────────────────────────────────────────
    // 3.1 Children list
    // GET /api/parent/children
    // ─────────────────────────────────────────────────────────────
    public function children(): JsonResponse
    {
        $children = auth()->user()->students()->with('studentDetail.class')->get();

        return $this->successResponse(
            $children->map(function ($child) {
                $detail = $child->studentDetail;
                return [
                    'id'         => (string) $child->id,
                    'name'       => $child->name,
                    'classLabel' => $detail?->class ? 'Class ' . $detail->class->name . $detail->class->section : null,
                    'roll'       => $detail?->roll_number ? 'Roll No. ' . $detail->roll_number : null,
                    'avatarUrl'  => $child->profile_image,
                ];
            })
        );
    }

    // ─────────────────────────────────────────────────────────────
    // 3.2 Student Dashboard
    // GET /api/student/dashboard?studentId=child_01
    // ─────────────────────────────────────────────────────────────
    public function studentDashboard(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $detail  = $student->studentDetail;
        $class   = $detail?->class;
        $classId = $detail?->class_id;

        // Attendance %
        $totalDays   = Attendance::where('student_id', $student->id)->count();
        $presentDays = Attendance::where('student_id', $student->id)
            ->whereIn('status', ['present', 'late'])->count();
        $attendancePct = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 100;

        // Pending homework count
        $pendingHWCount = Homework::where('class_id', $classId)
            ->where('status', 'published')
            ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $student->id)
                ->whereIn('status', ['submitted', 'approved']))
            ->count();

        // Upcoming exams
        $upcomingExamsCount = \App\Models\ExamSchedule::where('class_id', $classId)
            ->whereHas('exam', fn($q) => $q->where('status', 'upcoming'))
            ->count();

        // Latest result
        $latestMark = Mark::with('examSchedule')
            ->where('student_id', $student->id)
            ->latest()
            ->first();
        $latestResultPercent = $latestMark
            ? round(($latestMark->marks_obtained / $latestMark->examSchedule->max_marks) * 100) . '%'
            : 'N/A';

        // Pending homework list (top 3)
        $pendingHomework = Homework::with(['subject', 'class'])
            ->where('class_id', $classId)
            ->where('status', 'published')
            ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $student->id)
                ->whereIn('status', ['submitted', 'approved']))
            ->latest()
            ->take(3)
            ->get();

        $subjectColors = ['#5D36DB', '#4A90D9', '#F5A623', '#7ED321'];
        $subjectBgs    = ['#EBE6FF', '#E6F0FF', '#FFF3E0', '#E8F5E9'];

        // Recent results
        $recentMarks = Mark::with(['examSchedule.exam', 'examSchedule.subject'])
            ->where('student_id', $student->id)
            ->latest()
            ->take(3)
            ->get();

        // Upcoming events
        $upcomingEvents = SchoolEvent::where('school_id', $student->school_id)
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->take(3)
            ->get();

        // Recent announcements
        $notices = Notice::where('school_id', $student->school_id)
            ->latest('published_at')->take(3)->get();

        $recentAnnouncements = $notices->map(fn($n) => [
            'id'    => 'ann_' . $n->id,
            'title' => $n->title,
            'body'  => $n->content,
            'tag'   => strtoupper($n->type === 'general' ? 'info' : $n->type),
            'date'  => $n->published_at?->format('d M Y') ?? $n->created_at->format('d M Y'),
        ]);

        return $this->successResponse([
            'activeChild' => [
                'id'         => (string) $student->id,
                'name'       => $student->name,
                'classLabel' => $class ? 'Class ' . $class->name . $class->section : null,
                'roll'       => $detail?->roll_number ? 'Roll No. ' . $detail->roll_number : null,
                'avatarUrl'  => $student->profile_image,
            ],
            'stats' => [
                'attendancePercentage'  => $attendancePct,
                'pendingHomeworkCount'  => $pendingHWCount,
                'upcomingExamsCount'    => $upcomingExamsCount,
                'latestResultPercent'   => $latestResultPercent,
                'behaviorAlertsCount'   => 0,
            ],
            'pendingHomework' => $pendingHomework->map(function ($hw, $i) use ($subjectColors, $subjectBgs) {
                $due = $hw->due_date;
                $dueLabel = $due
                    ? ($due->isToday() ? 'Due Today' : ($due->isTomorrow() ? 'Due Tomorrow' : 'Due ' . $due->format('d M Y')))
                    : null;
                return [
                    'id'          => (string) $hw->id,
                    'subject'     => strtoupper($hw->subject->name),
                    'subjectColor'=> $subjectColors[$i % count($subjectColors)],
                    'subjectBg'   => $subjectBgs[$i % count($subjectBgs)],
                    'title'       => $hw->title,
                    'desc'        => $hw->description,
                    'due'         => $dueLabel,
                    'dueColor'    => ($due && $due->isPast()) ? '#BA1A1A' : '#555555',
                ];
            }),
            'recentResults' => $recentMarks->map(function ($mark) {
                $pct = round(($mark->marks_obtained / $mark->examSchedule->max_marks) * 100);
                return [
                    'id'       => (string) $mark->id,
                    'title'    => $mark->examSchedule->exam->name . ': ' . $mark->examSchedule->subject->name,
                    'score'    => $mark->marks_obtained . '/' . $mark->examSchedule->max_marks,
                    'grade'    => $mark->grade,
                    'tag'      => $pct >= 90 ? 'Top 5%' : ($pct >= 75 ? 'Top 25%' : null),
                    'tagColor' => '#5D36DB',
                    'tagBg'    => '#EBE6FF',
                ];
            }),
            'upcomingEvents' => $upcomingEvents->map(fn($ev) => [
                'id'    => (string) $ev->id,
                'day'   => $ev->event_date->format('d'),
                'month' => strtoupper($ev->event_date->format('M')),
                'title' => $ev->title,
                'meta'  => $ev->event_time ? $ev->event_time . ($ev->organizer ? ' • ' . $ev->organizer : '') : null,
            ]),
            'recentAnnouncements' => $recentAnnouncements,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 3.3 Student Homework Feed
    // GET /api/student/homework?studentId=X&status=All
    // ─────────────────────────────────────────────────────────────
    public function studentHomework(Request $request, User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $classId = $student->studentDetail?->class_id;
        $status  = $request->query('status', 'All');

        $homeworks = Homework::with(['subject', 'teacher'])
            ->where('class_id', $classId)
            ->where('status', 'published')
            ->latest()
            ->get();

        $result = $homeworks->map(function ($hw) use ($student) {
            $submission = HomeworkSubmission::where('homework_id', $hw->id)
                ->where('student_id', $student->id)
                ->first();

            $hwStatus = 'Pending';
            if ($submission) {
                $hwStatus = match($submission->status) {
                    'submitted'           => 'Submitted',
                    'approved'            => 'Reviewed',
                    'revision_requested'  => 'Revision',
                    'late'                => 'Late',
                    default               => 'Pending',
                };
            } elseif ($hw->due_date && $hw->due_date->isPast()) {
                $hwStatus = 'Overdue';
            }

            return [
                'id'            => (string) $hw->id,
                'subject'       => strtoupper($hw->subject->name),
                'title'         => $hw->title,
                'status'        => $hwStatus,
                'dueDate'       => $hw->due_date?->format('d M Y'),
                'maxMarks'      => $hw->max_marks,
                'attachments'   => $hw->attachments ?? [],
                'submittedDate' => $submission?->submitted_at?->format('M d'),
                'grade'         => $submission?->grade,
                'feedback'      => $submission?->feedback,
                'senders'       => [$hw->teacher?->profile_image],
            ];
        });

        // Filter by status if not All
        if ($status !== 'All') {
            $result = $result->filter(fn($hw) => strtolower($hw['status']) === strtolower($status))->values();
        }

        return $this->successResponse($result);
    }

    // POST /api/student/homework/{homework}/submit
    public function submitHomework(Request $request, User $student, Homework $homework): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $data = $request->validate([
            'reply_note' => 'nullable|string',
            'status'     => 'nullable|in:submitted,draft',
            'files'      => 'nullable|array',
            'files.*.name' => 'required|string',
            'files.*.size' => 'nullable|string',
            'files.*.url'  => 'required|string',
        ]);

        $isLate   = $homework->due_date && now()->gt($homework->due_date);
        $hwStatus = ($data['status'] ?? 'submitted') === 'draft'
            ? 'pending'
            : ($isLate ? 'late' : 'submitted');

        $submission = HomeworkSubmission::updateOrCreate(
            ['homework_id' => $homework->id, 'student_id' => $student->id],
            [
                'reply_note'   => $data['reply_note'] ?? null,
                'files'        => $data['files'] ?? null,
                'status'       => $hwStatus,
                'submitted_at' => $hwStatus !== 'pending' ? now() : null,
            ]
        );

        return $this->successResponse($submission, 'Homework submitted successfully.');
    }

    // ─────────────────────────────────────────────────────────────
    // 3.4 Results & Performance
    // GET /api/student/results?studentId=X
    // ─────────────────────────────────────────────────────────────
    public function studentResults(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $marks = Mark::with(['examSchedule.exam', 'examSchedule.subject', 'examSchedule.class'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        // Check honor roll: avg >= 85%
        $avgPct = $marks->isNotEmpty()
            ? $marks->avg(fn($m) => ($m->marks_obtained / $m->examSchedule->max_marks) * 100)
            : 0;

        $results = $marks->map(function ($mark) use ($student) {
            $sched = $mark->examSchedule;
            $pct   = round(($mark->marks_obtained / $sched->max_marks) * 100);

            // Class rank: count how many students scored higher
            $rank = Mark::where('exam_schedule_id', $sched->id)
                ->where('marks_obtained', '>', $mark->marks_obtained)
                ->count() + 1;

            // Class average %
            $classAvgRaw = Mark::where('exam_schedule_id', $sched->id)->avg('marks_obtained');
            $classAvgPct = $classAvgRaw ? round(($classAvgRaw / $sched->max_marks) * 100) : 0;

            return [
                'id'           => (string) $mark->id,
                'examTitle'    => $sched->exam->name,
                'subject'      => $sched->subject->name,
                'date'         => $sched->exam_date ? Carbon::parse($sched->exam_date)->format('d M Y') : null,
                'marksObtained'=> $mark->marks_obtained,
                'maxMarks'     => $sched->max_marks,
                'percentage'   => $pct . '%',
                'grade'        => $mark->grade,
                'classRank'    => '#' . $rank,
                'classAverage' => $classAvgPct . '%',
                'isPublished'  => true,
            ];
        });

        return $this->successResponse([
            'honorRollCandidate' => $avgPct >= 85,
            'averagePercentage'  => round($avgPct, 1) . '%',
            'results'            => $results,
        ]);
    }

    // POST /api/student/results/ptc/schedule
    public function schedulePtc(Request $request, User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $data = $request->validate([
            'term'      => 'required|string',
            'ptc_date'  => 'required|date',
            'time_slot' => 'required|string',
        ]);

        $booking = PtcBooking::create([
            'school_id'  => $student->school_id,
            'student_id' => $student->id,
            'parent_id'  => auth()->id(),
            'term'       => $data['term'],
            'ptc_date'   => $data['ptc_date'],
            'time_slot'  => $data['time_slot'],
            'status'     => 'booked',
        ]);

        return $this->successResponse([
            'booking_id' => $booking->id,
            'ptc_date'   => $booking->ptc_date->format('d M Y'),
            'time_slot'  => $booking->time_slot,
            'status'     => $booking->status,
        ], 'PTC scheduled successfully.', 201);
    }

    // ─────────────────────────────────────────────────────────────
    // 3.5 Leave Requests
    // GET /api/student/leave?studentId=X
    // ─────────────────────────────────────────────────────────────
    public function getLeaveRequests(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $requests = StudentLeaveRequest::where('parent_id', auth()->id())
            ->whereHas('students', fn($q) => $q->where('users.id', $student->id))
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse(
            $requests->map(fn($req) => [
                'id'          => (string) $req->id,
                'type'        => $req->reason,
                'from'        => $req->from_date->format('d M'),
                'to'          => $req->to_date->format('d M'),
                'fromDate'    => $req->from_date->toISOString(),
                'toDate'      => $req->to_date->toISOString(),
                'days'        => $req->leave_days,
                'notes'       => $req->description,
                'attachments' => $req->attachments ?? [],
                'status'      => $req->status,
                'adminRemarks'=> $req->admin_remarks,
            ])
        );
    }

    // POST /api/student/leave
    public function submitLeaveRequest(Request $request, User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $data = $request->validate([
            'reason'               => 'required|string',
            'description'          => 'nullable|string',
            'from_date'            => 'required|date',
            'to_date'              => 'required|date|after_or_equal:from_date',
            'status'               => 'nullable|in:pending,draft',
            'attachments'          => 'nullable|array',
            'attachments.*.name'   => 'required|string',
            'attachments.*.url'    => 'required|string',
        ]);

        $leaveRequest = StudentLeaveRequest::create([
            'school_id'   => $student->school_id,
            'parent_id'   => auth()->id(),
            'reason'      => $data['reason'],
            'description' => $data['description'] ?? null,
            'attachments' => $data['attachments'] ?? null,
            'from_date'   => $data['from_date'],
            'to_date'     => $data['to_date'],
            'status'      => $data['status'] ?? 'pending',
        ]);

        $leaveRequest->students()->attach($student->id);

        return $this->successResponse([
            'id'     => $leaveRequest->id,
            'status' => $leaveRequest->status,
            'days'   => $leaveRequest->leave_days,
        ], 'Leave request submitted.', 201);
    }

    // ─────────────────────────────────────────────────────────────
    // 3.6 Events Calendar
    // GET /api/student/events?studentId=X
    // ─────────────────────────────────────────────────────────────
    public function studentEvents(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $events = SchoolEvent::where('school_id', $student->school_id)
            ->orderBy('event_date')
            ->get();

        return $this->successResponse(
            $events->map(fn($ev) => [
                'id'                 => (string) $ev->id,
                'type'               => strtoupper($ev->type),
                'title'              => $ev->title,
                'description'        => $ev->description,
                'date'               => $ev->event_date->toDateString(),
                'time'               => $ev->event_time ?? 'All Day',
                'organizer'          => $ev->organizer,
                'organizerAvatarUrl' => $ev->organizer_avatar_url,
                'bannerImageUrl'     => $ev->banner_image_url,
            ])
        );
    }

    // ─────────────────────────────────────────────────────────────
    // 3.7 Notifications
    // GET /api/student/notifications?studentId=X
    // ─────────────────────────────────────────────────────────────
    public function getNotifications(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $notifications = Notification::where('user_id', $student->id)
            ->latest()
            ->get();

        $categoryColors = [
            'academic' => ['color' => '#5D36DB', 'bgLight' => '#EBE6FF'],
            'alerts'   => ['color' => '#EF4444', 'bgLight' => '#FEE2E2'],
            'fees'     => ['color' => '#F5A623', 'bgLight' => '#FFF3E0'],
            'general'  => ['color' => '#4A90D9', 'bgLight' => '#E6F0FF'],
        ];

        return $this->successResponse(
            $notifications->map(function ($n) use ($categoryColors) {
                $category = $n->type ?? 'general';
                $style    = $categoryColors[$category] ?? $categoryColors['general'];
                $diffMins = now()->diffInMinutes($n->created_at);
                $time = $diffMins < 60
                    ? $diffMins . 'm ago'
                    : ($diffMins < 1440 ? floor($diffMins / 60) . 'h ago' : 'Earlier');

                return [
                    'id'       => (string) $n->id,
                    'category' => $category,
                    'title'    => $n->title,
                    'body'     => $n->body,
                    'time'     => $time,
                    'isUnread' => is_null($n->read_at),
                    'color'    => $style['color'],
                    'bgLight'  => $style['bgLight'],
                ];
            })
        );
    }

    // PUT /api/student/notifications/read
    public function markNotificationsRead(Request $request, User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $data = $request->validate([
            'notification_ids'   => 'nullable|array',
            'notification_ids.*' => 'integer',
        ]);

        $query = Notification::where('user_id', $student->id)->whereNull('read_at');

        if (!empty($data['notification_ids'])) {
            $query->whereIn('id', $data['notification_ids']);
        }

        $query->update(['read_at' => now()]);

        return $this->successResponse(null, 'Notifications marked as read.');
    }

    // DELETE /api/student/notifications/{notificationId}
    public function deleteNotification(User $student, int $notificationId): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $notification = Notification::where('user_id', $student->id)
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->delete();

        return $this->successResponse(null, 'Notification deleted.');
    }

    // ─────────────────────────────────────────────────────────────
    // Existing APIs (kept intact, response format updated)
    // ─────────────────────────────────────────────────────────────
    public function studentAttendance(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $records = Attendance::where('student_id', $student->id)->orderByDesc('date')->get();

        return $this->successResponse(
            $records->map(fn($rec) => [
                'id'      => $rec->id,
                'date'    => $rec->date->toDateString(),
                'status'  => $rec->status,
                'remarks' => $rec->remarks,
            ])
        );
    }

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

        $dayMap = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        $result = [];

        foreach ($dayMap as $num => $label) {
            $daySlots = $slots->where('day_of_week', $num)->values();
            $result[$label] = $daySlots->map(fn($slot) => [
                'id'           => $slot->id,
                'period_number'=> $slot->period_number,
                'start_time'   => $slot->start_time,
                'end_time'     => $slot->end_time,
                'room_number'  => $slot->room_number,
                'subject'      => ['name' => $slot->subject->name, 'code' => $slot->subject->code],
                'teacher'      => $slot->teacher ? ['name' => $slot->teacher->name] : null,
            ])->values();
        }

        return $this->successResponse($result);
    }

    public function studentExams(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $marks = Mark::with(['examSchedule.exam', 'examSchedule.subject'])
            ->where('student_id', $student->id)
            ->get();

        return $this->successResponse(
            $marks->map(fn($mark) => [
                'id'             => $mark->id,
                'marks_obtained' => $mark->marks_obtained,
                'grade'          => $mark->grade,
                'remarks'        => $mark->remarks,
                'exam'           => [
                    'id'   => $mark->examSchedule->exam->id,
                    'name' => $mark->examSchedule->exam->name,
                    'type' => $mark->examSchedule->exam->type,
                ],
                'subject'        => [
                    'name' => $mark->examSchedule->subject->name,
                    'code' => $mark->examSchedule->subject->code,
                ],
                'max_marks'      => $mark->examSchedule->max_marks,
                'passing_marks'  => $mark->examSchedule->passing_marks,
            ])
        );
    }

    public function studentFees(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $detail  = $student->studentDetail;
        $classId = $detail?->class_id;

        $session = AcademicSession::where('school_id', $student->school_id)
            ->where('is_active', true)->first();

        $structures = $session
            ? FeeStructure::where('academic_session_id', $session->id)->get()
            : collect();

        $payments      = FeePayment::where('student_id', $student->id)->get();
        $totalPaid     = $payments->sum('amount_paid');
        $totalApplicable = 0;
        $bills         = [];

        foreach ($structures as $struct) {
            $classes = is_array($struct->applicable_classes) ? $struct->applicable_classes : [];
            if (empty($classes) || in_array($classId, $classes)) {
                $totalApplicable += $struct->amount;
                $structPayment    = $payments->where('fee_structure_id', $struct->id)->first();
                $bills[] = [
                    'structure_id' => $struct->id,
                    'name'         => $struct->name,
                    'amount'       => $struct->amount,
                    'frequency'    => $struct->frequency,
                    'status'       => $structPayment
                        ? ($structPayment->amount_paid >= $struct->amount ? 'paid' : 'partial')
                        : 'unpaid',
                    'amount_paid'  => $structPayment ? $structPayment->amount_paid : 0.00,
                ];
            }
        }

        return $this->successResponse([
            'dues_summary' => [
                'total_applicable' => $totalApplicable,
                'total_paid'       => $totalPaid,
                'outstanding_dues' => max(0, $totalApplicable - $totalPaid),
            ],
            'bills'    => $bills,
            'payments' => $payments->map(fn($pay) => [
                'id'             => $pay->id,
                'amount_paid'    => $pay->amount_paid,
                'payment_date'   => $pay->payment_date,
                'payment_method' => $pay->payment_method,
                'receipt_number' => $pay->receipt_number,
                'status'         => $pay->status,
                'remarks'        => $pay->remarks,
            ]),
        ]);
    }

    public function getAnnouncements(User $student): JsonResponse
    {
        if (!$this->verifyChildAccess($student)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $notices = Notice::where('school_id', $student->school_id)
            ->latest('published_at')
            ->get();

        return $this->successResponse(
            $notices->map(fn($n) => [
                'id'          => 'ann_' . $n->id,
                'title'       => $n->title,
                'body'        => $n->content,
                'tag'         => strtoupper($n->type === 'general' ? 'info' : $n->type),
                'date'        => $n->published_at?->format('d M Y') ?? $n->created_at->format('d M Y'),
                'publishedAt' => $n->published_at?->format('d M Y h:i A') ?? $n->created_at->format('d M Y h:i A'),
            ])
        );
    }
}
