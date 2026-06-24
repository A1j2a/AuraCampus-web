<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\StudentDetail;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\TimetableSlot;
use App\Models\SyllabusChapter;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherApiController extends Controller
{
    use ApiResponse;

    // ─────────────────────────────────────────────────────────────
    // 2.1 Teacher Home Dashboard
    // GET /api/teacher/dashboard
    // ─────────────────────────────────────────────────────────────
    public function dashboard(): JsonResponse
    {
        $teacherId = auth()->id();
        $dayOfWeek = now()->dayOfWeek === 0 ? 7 : now()->dayOfWeek;

        // My class IDs (via class_subject pivot)
        $classIds = DB::table('class_subject')
            ->where('teacher_id', $teacherId)
            ->pluck('class_id')
            ->unique();

        $myClassesCount  = $classIds->count();
        $studentsCount   = StudentDetail::whereIn('class_id', $classIds)->count();
        $pendingMarksCount = ExamSchedule::whereIn('class_id', $classIds)
            ->whereHas('exam', fn($q) => $q->where('status', '!=', 'completed'))
            ->count();
        $pendingHWCount = Homework::where('teacher_id', $teacherId)
            ->where('status', 'published')
            ->withCount(['submissions as pending_count' => fn($q) => $q->where('status', 'submitted')])
            ->get()->sum('pending_count');

        // Today's classes from timetable
        $todaysSlots = TimetableSlot::with(['class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('period_number')
            ->get();

        $colors = ['#6C63FF', '#4A90D9', '#F5A623', '#7ED321', '#D0021B'];
        $todaysClasses = $todaysSlots->map(function ($slot, $i) use ($colors) {
            return [
                'id'      => 'cls_' . $slot->id,
                'time'    => date('h:i', strtotime($slot->start_time)),
                'period'  => date('A', strtotime($slot->start_time)),
                'subject' => $slot->class->name . $slot->class->section,
                'topic1'  => $slot->subject->name,
                'topic2'  => '',
                'color'   => $colors[$i % count($colors)],
            ];
        });

        // Pending tasks: pending homework reviews + pending marks entry
        $pendingTasks = [];
        $hwWithPending = Homework::where('teacher_id', $teacherId)
            ->where('status', 'published')
            ->whereHas('submissions', fn($q) => $q->where('status', 'submitted'))
            ->withCount(['submissions as submitted_count' => fn($q) => $q->where('status', 'submitted')])
            ->get()
            ->take(3);

        foreach ($hwWithPending as $hw) {
            $pendingTasks[] = [
                'id'       => 'task_hw_' . $hw->id,
                'title'    => 'Review Homework',
                'subtitle' => ($hw->class->name ?? '') . ' • ' . $hw->title,
                'urgent'   => true,
                'tag'      => 'URGENT',
                'cta'      => 'Mark Done',
                'hwId'     => (string) $hw->id,
            ];
        }

        $examsNeedingMarks = ExamSchedule::with(['exam', 'subject', 'class'])
            ->whereIn('class_id', $classIds)
            ->whereHas('exam', fn($q) => $q->where('status', 'ongoing'))
            ->take(2)->get();

        foreach ($examsNeedingMarks as $sched) {
            $total   = StudentDetail::where('class_id', $sched->class_id)->count();
            $entered = Mark::where('exam_schedule_id', $sched->id)->count();
            $progress = $total > 0 ? round(($entered / $total) * 100) : 0;
            $pendingTasks[] = [
                'id'       => 'task_marks_' . $sched->id,
                'title'    => 'Enter Marks',
                'subtitle' => $sched->exam->name . ' • ' . $sched->subject->name,
                'urgent'   => false,
                'progress' => $progress,
                'examId'   => 'ex_' . $sched->exam_id,
            ];
        }

        // Recent announcements
        $notices = Notice::where('school_id', auth()->user()->school_id)
            ->latest('published_at')->take(3)->get();

        $recentAnnouncements = $notices->map(fn($n) => [
            'id'    => 'ann_' . $n->id,
            'title' => $n->title,
            'body'  => $n->content,
        ]);

        return $this->successResponse([
            'stats' => [
                'myClassesCount'    => $myClassesCount,
                'studentsCount'     => $studentsCount,
                'pendingHWCount'    => (int) $pendingHWCount,
                'pendingMarksCount' => $pendingMarksCount,
            ],
            'todaysClasses'       => $todaysClasses,
            'pendingTasks'        => $pendingTasks,
            'recentAnnouncements' => $recentAnnouncements,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 2.2 Teacher Schedule (Timetable grouped by day)
    // GET /api/teacher/schedule
    // ─────────────────────────────────────────────────────────────
    public function schedule(): JsonResponse
    {
        $slots = TimetableSlot::with(['class', 'subject'])
            ->where('teacher_id', auth()->id())
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();

        $dayMap = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        $result = [];

        foreach ($dayMap as $num => $label) {
            $daySlots = $slots->where('day_of_week', $num)->values();
            $periods  = $daySlots->map(function ($slot) {
                $startMin = $this->timeToMinutes($slot->start_time);
                $endMin   = $this->timeToMinutes($slot->end_time);
                return [
                    'id'           => 'p_' . $slot->id,
                    'type'         => 'class',
                    'startTime'    => date('H:i', strtotime($slot->start_time)),
                    'endTime'      => date('H:i', strtotime($slot->end_time)),
                    'subject'      => $slot->subject->name,
                    'class'        => $slot->class->name . ' ' . $slot->class->section,
                    'room'         => $slot->room_number ? 'ROOM ' . $slot->room_number : null,
                    'periodNo'     => $slot->period_number,
                    'startMinutes' => $startMin,
                    'endMinutes'   => $endMin,
                ];
            })->values();

            $result[$label] = [
                'periods' => $periods,
                'isLeave' => $periods->isEmpty(),
            ];
        }

        return $this->successResponse($result);
    }

    // ─────────────────────────────────────────────────────────────
    // 2.3 Classes list
    // GET /api/teacher/classes
    // ─────────────────────────────────────────────────────────────
    public function classes(): JsonResponse
    {
        $classes = SchoolClass::whereHas('subjects', function ($q) {
            $q->where('class_subject.teacher_id', auth()->id());
        })->with(['subjects' => function ($q) {
            $q->where('class_subject.teacher_id', auth()->id());
        }])->withCount('students')->get();

        return $this->successResponse(
            $classes->map(fn($cls) => [
                'id'           => $cls->id,
                'name'         => $cls->name,
                'section'      => $cls->section,
                'room_number'  => $cls->room_number,
                'studentCount' => $cls->students_count,
                'subjects'     => $cls->subjects->map(fn($sub) => [
                    'id'   => $sub->id,
                    'name' => $sub->name,
                    'code' => $sub->code,
                ]),
            ])
        );
    }

    // ─────────────────────────────────────────────────────────────
    // 2.3b Class Overview (single class detail)
    // GET /api/teacher/classes/{class}
    // ─────────────────────────────────────────────────────────────
    public function classDetail(SchoolClass $class): JsonResponse
    {
        $students = StudentDetail::where('class_id', $class->id)
            ->with('user')
            ->get();

        $subjects = $class->subjects()
            ->where('class_subject.teacher_id', auth()->id())
            ->get();

        return $this->successResponse([
            'id'          => $class->id,
            'name'        => $class->name,
            'section'     => $class->section,
            'room_number' => $class->room_number,
            'subjects'    => $subjects->map(fn($sub) => [
                'id'   => $sub->id,
                'name' => $sub->name,
                'code' => $sub->code,
            ]),
            'students' => $students->map(fn($s) => [
                'student_id'       => $s->user_id,
                'name'             => $s->user->name,
                'roll_number'      => $s->roll_number,
                'admission_number' => $s->admission_number,
                'gender'           => $s->gender,
                'avatar_url'       => $s->user->profile_image,
            ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 2.3c Students list for a class
    // GET /api/teacher/classes/{class}/students
    // ─────────────────────────────────────────────────────────────
    public function students(SchoolClass $class): JsonResponse
    {
        $students = StudentDetail::where('class_id', $class->id)->with('user')->get();

        return $this->successResponse(
            $students->map(fn($s) => [
                'student_id'       => $s->user_id,
                'name'             => $s->user->name,
                'email'            => $s->user->email,
                'roll_number'      => $s->roll_number,
                'admission_number' => $s->admission_number,
                'gender'           => $s->gender,
                'avatar_url'       => $s->user->profile_image,
            ])
        );
    }

    // ─────────────────────────────────────────────────────────────
    // 2.5 Student Diagnostic Profile
    // GET /api/teacher/students/{studentId}
    // ─────────────────────────────────────────────────────────────
    public function studentProfile(string $studentId): JsonResponse
    {
        if (!ctype_digit($studentId) || (int)$studentId <= 0) {
            return $this->errorResponse('Invalid student ID.', 422);
        }

        $detail = StudentDetail::with(['user', 'class'])->where('user_id', (int)$studentId)->first();

        if (!$detail) {
            return $this->errorResponse('Student not found.', 404);
        }

        $user = $detail->user;

        // Subject-wise marks
        $marks = Mark::with('examSchedule.subject')
            ->where('student_id', $studentId)
            ->get();

        $subjectScores = $marks->groupBy('examSchedule.subject.name')->map(function ($group, $name) {
            $avg = round($group->avg(fn($m) => ($m->marks_obtained / $m->examSchedule->max_marks) * 100), 1);
            return ['name' => $name, 'score' => $avg];
        })->values();

        $colors = ['#6C63FF', '#4A90D9', '#F5A623', '#7ED321', '#D0021B'];
        $subjectScores = $subjectScores->map(function ($sub, $i) use ($colors) {
            $sub['color'] = $colors[$i % count($colors)];
            return $sub;
        });

        // Homework stats
        $hwCompleted = HomeworkSubmission::where('student_id', $studentId)
            ->whereIn('status', ['approved', 'submitted'])->count();
        $hwPending = HomeworkSubmission::where('student_id', $studentId)
            ->where('status', 'pending')->count();

        // Overall score
        $totalPct = $marks->isNotEmpty()
            ? round($marks->avg(fn($m) => ($m->marks_obtained / $m->examSchedule->max_marks) * 100), 1)
            : 0;

        return $this->successResponse([
            'student' => [
                'id'            => '#' . $detail->admission_number,
                'name'          => $user->name,
                'photoUrl'      => $user->profile_image,
                'academicScore' => $totalPct,
                'className'     => $detail->class ? $detail->class->name . ' ' . $detail->class->section : null,
                'homework'      => ['completed' => $hwCompleted, 'pending' => $hwPending],
                'subjects'      => $subjectScores,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 2.3 Syllabus – GET list
    // GET /api/teacher/syllabus?class_id=1&subject_id=2
    // ─────────────────────────────────────────────────────────────
    public function getSyllabus(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $chapters = SyllabusChapter::where('teacher_id', auth()->id())
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->orderBy('chapter_no')
            ->get();

        $total    = $chapters->count();
        $finished = $chapters->where('status', 'completed')->count();

        return $this->successResponse([
            'progressPercent'  => $total > 0 ? round(($finished / $total) * 100) : 0,
            'finishedChapters' => $finished,
            'totalChapters'    => $total,
            'chapters'         => $chapters->map(fn($ch) => [
                'id'          => $ch->id,
                'title'       => $ch->title,
                'chapter_no'  => $ch->chapter_no,
                'description' => $ch->description,
                'status'      => $ch->status,
                'priority'    => $ch->priority,
                'updatedDate' => $ch->updated_at?->format('d M Y'),
            ]),
        ]);
    }

    // POST /api/teacher/syllabus
    public function storeSyllabus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'subject_id'    => 'required|exists:subjects,id',
            'title'         => 'required|string',
            'chapter_no'    => 'nullable|string',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:not_started,in_progress,completed',
            'priority'      => 'nullable|in:low,medium,high',
            'deadline_date' => 'nullable|date',
        ]);

        $chapter = SyllabusChapter::create(array_merge($data, [
            'teacher_id' => auth()->id(),
            'school_id'  => auth()->user()->school_id,
        ]));

        return $this->successResponse($chapter, 'Chapter created successfully.', 201);
    }

    // PUT /api/teacher/syllabus/{chapter}
    public function updateSyllabus(Request $request, SyllabusChapter $chapter): JsonResponse
    {
        if ($chapter->teacher_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        $data = $request->validate([
            'title'         => 'sometimes|string',
            'chapter_no'    => 'nullable|string',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:not_started,in_progress,completed',
            'priority'      => 'nullable|in:low,medium,high',
            'deadline_date' => 'nullable|date',
        ]);

        $chapter->update($data);

        return $this->successResponse($chapter, 'Chapter updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────
    // 2.6 Exams
    // GET /api/teacher/exams
    // ─────────────────────────────────────────────────────────────
    public function getExams(): JsonResponse
    {
        $teacherId = auth()->id();

        // Classes where this teacher is the class teacher
        $classTeacherClassIds = SchoolClass::where('teacher_id', $teacherId)->pluck('id')->toArray();

        // Classes and subjects where this teacher is the subject teacher
        $subjectTeacherAssignments = DB::table('class_subject')
            ->where('teacher_id', $teacherId)
            ->get();

        if (empty($classTeacherClassIds) && $subjectTeacherAssignments->isEmpty()) {
            return $this->successResponse([]);
        }

        $schedules = ExamSchedule::with(['exam', 'class', 'subject'])
            ->where(function ($query) use ($classTeacherClassIds, $subjectTeacherAssignments) {
                // If class teacher, get all schedules for those classes
                if (!empty($classTeacherClassIds)) {
                    $query->whereIn('class_id', $classTeacherClassIds);
                }

                // Plus, get schedules for classes and subjects they teach
                foreach ($subjectTeacherAssignments as $assignment) {
                    $query->orWhere(function ($q) use ($assignment) {
                        $q->where('class_id', $assignment->class_id)
                          ->where('subject_id', $assignment->subject_id);
                    });
                }
            })
            ->latest()
            ->get();

        $today = now()->toDateString();
        foreach ($schedules as $sched) {
            $exam = $sched->exam;
            if ($exam) {
                $computedStatus = 'upcoming';
                if ($today > $exam->end_date) {
                    $computedStatus = 'completed';
                } elseif ($today >= $exam->start_date && $today <= $exam->end_date) {
                    $computedStatus = 'ongoing';
                }
                if ($exam->status !== $computedStatus) {
                    $exam->status = $computedStatus;
                    $exam->save();
                }
            }
        }

        return $this->successResponse(
            $schedules->map(fn($sched) => [
                'id'           => $sched->id,
                'examId'       => 'ex_' . $sched->exam_id,
                'className'    => $sched->class->name . ' ' . $sched->class->section,
                'subject'      => $sched->subject->name,
                'exam'         => $sched->exam->name,
                'academicYear' => $sched->exam->academicSession?->name ?? '',
                'totalMarks'   => $sched->max_marks,
                'passingMarks' => $sched->passing_marks,
                'examDate'     => $sched->exam_date,
                'status'       => $sched->exam->status,
                'publishedAt'  => $sched->exam->updated_at?->format('d M Y'),
            ])
        );
    }

    // GET /api/teacher/exams/schedule/{schedule}/marks
    public function getMarks(ExamSchedule $schedule): JsonResponse
    {
        $teacherId = auth()->id();
        $isClassTeacher = SchoolClass::where('id', $schedule->class_id)
            ->where('teacher_id', $teacherId)
            ->exists();
        $isSubjectTeacher = DB::table('class_subject')
            ->where('class_id', $schedule->class_id)
            ->where('subject_id', $schedule->subject_id)
            ->where('teacher_id', $teacherId)
            ->exists();

        if (!$isClassTeacher && !$isSubjectTeacher) {
            return $this->errorResponse('You do not have permission to enter marks for this exam schedule.', 403);
        }

        $students = StudentDetail::where('class_id', $schedule->class_id)->with('user')->get();
        $records  = Mark::where('exam_schedule_id', $schedule->id)->get()->keyBy('student_id');

        return $this->successResponse([
            'schedule_id'   => $schedule->id,
            'max_marks'     => $schedule->max_marks,
            'passing_marks' => $schedule->passing_marks,
            'students'      => $students->map(function ($s) use ($records) {
                $rec = $records->get($s->user_id);
                return [
                    'student_id'     => $s->user_id,
                    'name'           => $s->user->name,
                    'roll_number'    => $s->roll_number,
                    'marks_obtained' => $rec?->marks_obtained,
                    'grade'          => $rec?->grade,
                    'remarks'        => $rec?->remarks,
                ];
            }),
        ]);
    }

    // POST /api/teacher/exams/schedule/{schedule}/marks
    public function storeMarks(Request $request, ExamSchedule $schedule): JsonResponse
    {
        $teacherId = auth()->id();
        $isClassTeacher = SchoolClass::where('id', $schedule->class_id)
            ->where('teacher_id', $teacherId)
            ->exists();
        $isSubjectTeacher = DB::table('class_subject')
            ->where('class_id', $schedule->class_id)
            ->where('subject_id', $schedule->subject_id)
            ->where('teacher_id', $teacherId)
            ->exists();

        if (!$isClassTeacher && !$isSubjectTeacher) {
            return $this->errorResponse('You do not have permission to enter marks for this exam schedule.', 403);
        }

        $request->validate([
            'marks'                    => 'required|array',
            'marks.*.student_id'       => 'required|exists:users,id',
            'marks.*.marks_obtained'   => 'required|numeric|min:0|max:' . $schedule->max_marks,
            'marks.*.remarks'          => 'nullable|string',
        ]);

        if (\Carbon\Carbon::parse($schedule->exam_date)->isFuture()) {
            return $this->errorResponse('Cannot enter marks for an upcoming exam.', 422);
        }

        $schoolId = auth()->user()->school_id;

        foreach ($request->marks as $entry) {
            $studentId = (int) $entry['student_id'];

            // Verify student belongs to this class
            $exists = StudentDetail::where('user_id', $studentId)
                ->where('class_id', $schedule->class_id)
                ->exists();

            if (!$exists) continue;

            $grade = $this->calculateGrade($entry['marks_obtained'], $schedule->max_marks);

            Mark::updateOrCreate(
                [
                    'exam_schedule_id' => $schedule->id,
                    'student_id'       => $studentId,
                ],
                [
                    'school_id'      => $schoolId,
                    'marks_obtained' => $entry['marks_obtained'],
                    'grade'          => $grade,
                    'remarks'        => $entry['remarks'] ?? null,
                    'entered_by'     => auth()->id(),
                ]
            );
        }

        // Update exam status to completed
        $schedule->exam()->update(['status' => 'completed']);

        return $this->successResponse(null, 'Marks recorded successfully.');
    }

    // ─────────────────────────────────────────────────────────────
    // 2.4 Homework
    // GET /api/teacher/homework
    // ─────────────────────────────────────────────────────────────
    public function getHomework(): JsonResponse
    {
        $homeworks = Homework::with(['class', 'subject'])
            ->where('teacher_id', auth()->id())
            ->withCount('submissions')
            ->latest()
            ->get();

        return $this->successResponse(
            $homeworks->map(fn($hw) => [
                'id'            => $hw->id,
                'className'     => $hw->class->name . ' ' . $hw->class->section,
                'subject'       => $hw->subject->name,
                'title'         => $hw->title,
                'description'   => $hw->description,
                'dueDate'       => $hw->due_date?->format('d M Y'),
                'priority'      => $hw->priority,
                'status'        => $hw->status,
                'maxMarks'      => $hw->max_marks,
                'publishedAt'   => $hw->published_at?->format('d M Y'),
                'savedAt'       => $hw->created_at?->format('h:i A'),
                'attachments'   => $hw->attachments ?? [],
                'submissions'   => $hw->submissions_count,
                'notifyParents' => (bool) $hw->notify_parents,
            ])
        );
    }

    // POST /api/teacher/homework
    public function storeHomework(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_id'       => 'required|exists:classes,id',
            'subject_id'     => 'required|exists:subjects,id',
            'title'          => 'required|string',
            'description'    => 'nullable|string',
            'due_date'       => 'nullable|date',
            'priority'       => 'nullable|in:low,medium,high',
            'status'         => 'nullable|in:draft,published',
            'max_marks'      => 'nullable|integer|min:1',
            'attachments'    => 'nullable|array',
            'notify_parents' => 'nullable|boolean',
        ]);

        $data['teacher_id']   = auth()->id();
        $data['school_id']    = auth()->user()->school_id;
        $data['published_at'] = ($data['status'] ?? 'draft') === 'published' ? now() : null;

        $hw = Homework::create($data);

        return $this->successResponse($hw->load(['class', 'subject']), 'Homework created.', 201);
    }

    // PUT /api/teacher/homework/{homework}
    public function updateHomework(Request $request, Homework $homework): JsonResponse
    {
        if ($homework->teacher_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        $data = $request->validate([
            'title'          => 'sometimes|string',
            'description'    => 'nullable|string',
            'due_date'       => 'nullable|date',
            'priority'       => 'nullable|in:low,medium,high',
            'status'         => 'nullable|in:draft,published',
            'max_marks'      => 'nullable|integer|min:1',
            'attachments'    => 'nullable|array',
            'notify_parents' => 'nullable|boolean',
        ]);

        if (isset($data['status']) && $data['status'] === 'published' && !$homework->published_at) {
            $data['published_at'] = now();
        }

        $homework->update($data);

        return $this->successResponse($homework->fresh()->load(['class', 'subject']), 'Homework updated.');
    }

    // GET /api/teacher/homework/{homework}/submissions
    public function getSubmissions(Homework $homework): JsonResponse
    {
        if ($homework->teacher_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        $submissions = HomeworkSubmission::with('student')
            ->where('homework_id', $homework->id)
            ->get();

        $totalStudents = StudentDetail::where('class_id', $homework->class_id)->count();

        return $this->successResponse([
            'hwId'      => $homework->id,
            'hwTitle'   => $homework->title,
            'subject'   => $homework->subject->name,
            'className' => $homework->class->name . ' ' . $homework->class->section,
            'dueDate'   => $homework->due_date?->format('d M Y'),
            'stats'     => [
                'submitted' => $submissions->whereIn('status', ['submitted', 'approved'])->count(),
                'approved'  => $submissions->where('status', 'approved')->count(),
                'pending'   => $totalStudents - $submissions->whereIn('status', ['submitted', 'approved', 'late'])->count(),
                'late'      => $submissions->where('status', 'late')->count(),
                'total'     => $totalStudents,
            ],
            'submissions' => $submissions->map(fn($sub) => [
                'studentId'   => $sub->student_id,
                'studentName' => $sub->student->name,
                'avatarUrl'   => $sub->student->profile_image,
                'status'      => $sub->status,
                'submittedAt' => $sub->submitted_at?->format('d M Y, h:i A'),
                'files'       => $sub->files ?? [],
                'notes'       => $sub->reply_note,
                'grade'       => $sub->grade,
                'feedback'    => $sub->feedback,
            ]),
        ]);
    }

    // POST /api/teacher/homework/{homework}/submissions/{studentId}/grade
    public function gradeSubmission(Request $request, Homework $homework, string $studentId): JsonResponse
    {
        if (!ctype_digit($studentId) || (int)$studentId <= 0) {
            return $this->errorResponse('Invalid student ID.', 422);
        }

        if ($homework->teacher_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        $data = $request->validate([
            'status'   => 'required|in:approved,revision_requested',
            'grade'    => 'nullable|string',
            'feedback' => 'nullable|string',
        ]);

        $submission = HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', (int)$studentId)
            ->firstOrFail();

        $submission->update([
            'status'     => $data['status'],
            'grade'      => $data['grade'] ?? null,
            'feedback'   => $data['feedback'] ?? null,
            'graded_at'  => now(),
            'graded_by'  => auth()->id(),
        ]);

        return $this->successResponse(null, 'Submission graded successfully.');
    }

    // ─────────────────────────────────────────────────────────────
    // Attendance (existing, kept intact)
    // ─────────────────────────────────────────────────────────────
    public function getAttendance(Request $request, SchoolClass $class): JsonResponse
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $date = $request->date ?? now()->format('Y-m-d');

        $students = StudentDetail::where('class_id', $class->id)->with('user')->get();
        $records  = Attendance::where('class_id', $class->id)->where('date', $date)->get()->keyBy('student_id');

        return $this->successResponse([
            'date'       => $date,
            'attendance' => $students->map(function ($s) use ($records) {
                $rec = $records->get($s->user_id);
                return [
                    'student_id'  => $s->user_id,
                    'name'        => $s->user->name,
                    'roll_number' => $s->roll_number,
                    'status'      => $rec?->status,
                    'remarks'     => $rec?->remarks,
                ];
            }),
        ]);
    }

    public function storeAttendance(Request $request, SchoolClass $class): JsonResponse
    {
        $request->validate([
            'date'         => 'required|date_format:Y-m-d',
            'attendance'   => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,excused',
        ]);

        foreach ($request->attendance as $studentId => $status) {
            Attendance::updateOrCreate(
                ['school_id' => auth()->user()->school_id, 'class_id' => $class->id, 'student_id' => $studentId, 'date' => $request->date],
                ['status' => $status, 'marked_by' => auth()->id()]
            );
        }

        return $this->successResponse(null, 'Attendance marked successfully.');
    }

    // ─────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────
    private function timeToMinutes(string $time): int
    {
        [$h, $m] = explode(':', date('H:i', strtotime($time)));
        return (int)$h * 60 + (int)$m;
    }

    private function calculateGrade(float $obtained, int $max): string
    {
        $pct = ($obtained / $max) * 100;
        if ($pct >= 90) return 'A+';
        if ($pct >= 80) return 'A';
        if ($pct >= 70) return 'B+';
        if ($pct >= 60) return 'B';
        if ($pct >= 50) return 'C+';
        if ($pct >= 40) return 'C';
        if ($pct >= 33) return 'D';
        return 'F';
    }

    // ─────────────────────────────────────────────────────────────
    // Teacher Notifications
    // ─────────────────────────────────────────────────────────────
    public function getNotifications(): JsonResponse
    {
        $notifications = \App\Models\Notification::where('user_id', auth()->id())
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

    public function markNotificationsRead(Request $request): JsonResponse
    {
        $request->validate([
            'notification_ids' => 'nullable|array',
            'notification_ids.*' => 'integer',
        ]);

        $query = \App\Models\Notification::where('user_id', auth()->id());
        
        if ($request->has('notification_ids')) {
            $query->whereIn('id', $request->notification_ids);
        }

        $query->update(['read_at' => now()]);

        return $this->successResponse(null, 'Notifications marked as read.');
    }

    public function deleteNotification($notificationId): JsonResponse
    {
        $notification = \App\Models\Notification::where('user_id', auth()->id())
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return $this->errorResponse('Notification not found.', 404);
        }

        $notification->delete();

        return $this->successResponse(null, 'Notification deleted.');
    }

    public function storeExam(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'       => 'required|exists:classes,id',
            'subject_id'     => 'required|exists:subjects,id',
            'name'           => 'required|string|max:255',
            'type'           => 'required|string',
            'exam_date'      => 'required|date',
            'start_time'     => 'required|string',
            'end_time'       => 'required|string',
            'max_marks'      => 'required|integer|min:1',
            'passing_marks'  => 'required|integer|min:1',
        ]);

        $schoolId = auth()->user()->school_id;

        // Get active academic session
        $session = \App\Models\AcademicSession::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        // 1. Create the Exam
        $exam = Exam::create([
            'school_id'           => $schoolId,
            'academic_session_id' => $session?->id,
            'name'                => $request->name,
            'type'                => $request->type,
            'start_date'          => $request->exam_date,
            'end_date'            => $request->exam_date,
            'status'              => 'upcoming',
        ]);

        // 2. Create the ExamSchedule
        $schedule = ExamSchedule::create([
            'exam_id'       => $exam->id,
            'class_id'      => $request->class_id,
            'subject_id'    => $request->subject_id,
            'exam_date'     => $request->exam_date,
            'start_time'    => \Illuminate\Support\Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time'      => \Illuminate\Support\Carbon::parse($request->end_time)->format('H:i:s'),
            'max_marks'     => $request->max_marks,
            'passing_marks' => $request->passing_marks,
        ]);

        return $this->successResponse([
            'id'           => $schedule->id,
            'examId'       => 'ex_' . $exam->id,
            'className'    => $schedule->class->name . ' ' . $schedule->class->section,
            'subject'      => $schedule->subject->name,
            'exam'         => $exam->name,
            'academicYear' => $session?->name ?? '',
            'totalMarks'   => $schedule->max_marks,
            'passingMarks' => $schedule->passing_marks,
            'examDate'     => $schedule->exam_date,
            'status'       => $exam->status,
            'publishedAt'  => $exam->updated_at?->format('d M Y'),
        ], 'Exam created successfully.', 201);
    }
}
