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
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherApiController extends Controller
{
    use ApiResponse;

    /**
     * Teacher Dashboard summary.
     */
    public function dashboard(): JsonResponse
    {
        $teacherId = auth()->id();
        $schoolId = auth()->user()->school_id;

        // Today's Day of Week (1 = Mon, ..., 5 = Fri, 6 = Sat, 7 = Sun)
        $dayOfWeek = now()->dayOfWeek === 0 ? 7 : now()->dayOfWeek;

        // Today's classes count
        $todayClassesCount = TimetableSlot::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->count();

        // Notices
        $notices = Notice::latest('published_at')->take(3)->get();

        // Total students taught
        $classIds = DB::table('class_subject')
            ->where('teacher_id', $teacherId)
            ->pluck('class_id')
            ->unique();

        $totalStudents = StudentDetail::whereIn('class_id', $classIds)->count();

        return $this->successResponse([
            'today_classes_count' => $todayClassesCount,
            'total_students' => $totalStudents,
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
     * Get Teacher Timetable.
     */
    public function timetable(): JsonResponse
    {
        $slots = TimetableSlot::with(['class', 'subject'])
            ->where('teacher_id', auth()->id())
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
                    'class' => [
                        'id' => $slot->class->id,
                        'name' => $slot->class->name,
                        'section' => $slot->class->section,
                    ],
                    'subject' => [
                        'id' => $slot->subject->id,
                        'name' => $slot->subject->name,
                        'code' => $slot->subject->code,
                    ]
                ];
            })
        );
    }

    /**
     * Get classes and subjects taught by teacher.
     */
    public function classes(): JsonResponse
    {
        $classes = SchoolClass::whereHas('subjects', function ($query) {
            $query->where('class_subject.teacher_id', auth()->id());
        })->with(['subjects' => function ($query) {
            $query->where('class_subject.teacher_id', auth()->id());
        }])->get();

        return $this->successResponse(
            $classes->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'section' => $class->section,
                    'room_number' => $class->room_number,
                    'subjects' => $class->subjects->map(function ($sub) {
                        return [
                            'id' => $sub->id,
                            'name' => $sub->name,
                            'code' => $sub->code,
                        ];
                    })
                ];
            })
        );
    }

    /**
     * Get students list for a class.
     */
    public function students(SchoolClass $class): JsonResponse
    {
        $students = StudentDetail::where('class_id', $class->id)
            ->with('user')
            ->get();

        return $this->successResponse(
            $students->map(function ($student) {
                return [
                    'student_id' => $student->user_id,
                    'name' => $student->user->name,
                    'email' => $student->user->email,
                    'roll_number' => $student->roll_number,
                    'admission_number' => $student->admission_number,
                    'gender' => $student->gender,
                ];
            })
        );
    }

    /**
     * Get attendance roster for a class on a date.
     */
    public function getAttendance(Request $request, SchoolClass $class): JsonResponse
    {
        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        $date = $request->date ?? now()->format('Y-m-d');
        
        $students = StudentDetail::where('class_id', $class->id)
            ->with('user')
            ->get();

        $records = Attendance::where('class_id', $class->id)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        return $this->successResponse([
            'date' => $date,
            'attendance' => $students->map(function ($student) use ($records) {
                $record = $records->get($student->user_id);
                return [
                    'student_id' => $student->user_id,
                    'name' => $student->user->name,
                    'roll_number' => $student->roll_number,
                    'status' => $record ? $record->status : null,
                    'remarks' => $record ? $record->remarks : null,
                ];
            })
        ]);
    }

    /**
     * Save/Update attendance records.
     */
    public function storeAttendance(Request $request, SchoolClass $class): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,excused',
        ]);

        foreach ($request->attendance as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'school_id' => auth()->user()->school_id,
                    'class_id' => $class->id,
                    'student_id' => $studentId,
                    'date' => $request->date,
                ],
                [
                    'status' => $status,
                    'marked_by' => auth()->id(),
                ]
            );
        }

        return $this->successResponse(null, 'Attendance marked successfully.');
    }

    /**
     * Get all exam listings.
     */
    public function getExams(): JsonResponse
    {
        $exams = Exam::with(['schedules' => function ($query) {
            $query->with(['class', 'subject']);
        }])->latest()->get();

        return $this->successResponse(
            $exams->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'type' => $exam->type,
                    'start_date' => $exam->start_date,
                    'end_date' => $exam->end_date,
                    'status' => $exam->status,
                    'schedules' => $exam->schedules->map(function ($schedule) {
                        return [
                            'id' => $schedule->id,
                            'exam_date' => $schedule->exam_date,
                            'start_time' => $schedule->start_time,
                            'end_time' => $schedule->end_time,
                            'max_marks' => $schedule->max_marks,
                            'passing_marks' => $schedule->passing_marks,
                            'class' => [
                                'id' => $schedule->class->id,
                                'name' => $schedule->class->name,
                                'section' => $schedule->class->section,
                            ],
                            'subject' => [
                                'id' => $schedule->subject->id,
                                'name' => $schedule->subject->name,
                                'code' => $schedule->subject->code,
                            ]
                        ];
                    })
                ];
            })
        );
    }

    /**
     * Get marks details for a schedule.
     */
    public function getMarks(ExamSchedule $schedule): JsonResponse
    {
        $students = StudentDetail::where('class_id', $schedule->class_id)
            ->with('user')
            ->get();

        $records = Mark::where('exam_schedule_id', $schedule->id)
            ->get()
            ->keyBy('student_id');

        return $this->successResponse([
            'schedule_id' => $schedule->id,
            'max_marks' => $schedule->max_marks,
            'passing_marks' => $schedule->passing_marks,
            'students' => $students->map(function ($student) use ($records) {
                $record = $records->get($student->user_id);
                return [
                    'student_id' => $student->user_id,
                    'name' => $student->user->name,
                    'roll_number' => $student->roll_number,
                    'marks_obtained' => $record ? $record->marks_obtained : null,
                    'grade' => $record ? $record->grade : null,
                    'remarks' => $record ? $record->remarks : null,
                ];
            })
        ]);
    }

    /**
     * Save/Update student marks.
     */
    public function storeMarks(Request $request, ExamSchedule $schedule): JsonResponse
    {
        $request->validate([
            'marks' => 'required|array',
            'marks.*.marks_obtained' => 'required|numeric|min:0|max:' . $schedule->max_marks,
            'marks.*.remarks' => 'nullable|string',
        ]);

        foreach ($request->marks as $studentId => $marksData) {
            $obtained = $marksData['marks_obtained'];
            $grade = $this->calculateGrade($obtained, $schedule->max_marks);

            Mark::updateOrCreate(
                [
                    'school_id' => auth()->user()->school_id,
                    'exam_schedule_id' => $schedule->id,
                    'student_id' => $studentId,
                ],
                [
                    'marks_obtained' => $obtained,
                    'grade' => $grade,
                    'remarks' => $marksData['remarks'] ?? null,
                    'entered_by' => auth()->id(),
                ]
            );
        }

        return $this->successResponse(null, 'Marks recorded successfully.');
    }

    /**
     * Calculate grade from percentage.
     */
    private function calculateGrade($obtained, $max): string
    {
        $percentage = ($obtained / $max) * 100;
        
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        if ($percentage >= 33) return 'D';
        return 'F';
    }
}
