<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\School;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherDetail;
use App\Models\StudentDetail;
use App\Models\Notice;
use App\Models\SubscriptionPlan;
use App\Models\SchoolSubscription;
use App\Models\SupportTicket;
use App\Models\TimetableSlot;
use App\Models\FeeStructure;
use App\Models\FeePayment;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Mark;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create standard roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $schoolAdminRole = Role::firstOrCreate(['name' => 'school-admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $parentRole = Role::firstOrCreate(['name' => 'parent']);

        // ── 1. Create Default Super Admin Account (No School) ──
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@auracampus.com'],
            [
                'name' => 'Alex Rivera',
                'password' => Hash::make('password'),
                'school_id' => null,
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // ── 2. Create Demo School: Greenwood Academy ──
        $school = School::firstOrCreate(
            ['slug' => 'greenwood-academy'],
            [
                'name' => 'Greenwood Academy',
                'email' => 'info@greenwood.com',
                'phone' => '+91-9876543210',
                'address' => '123 Education Lane, Knowledge City, MP 462001',
                'status' => 'active',
                'settings' => [
                    'timezone' => 'Asia/Kolkata',
                    'currency' => 'INR',
                    'grading_system' => 'percentage',
                ],
            ]
        );

        // ── 3. Create School Admin ──
        $schoolAdmin = User::firstOrCreate(
            ['email' => 'principal@greenwood.com'],
            [
                'name' => 'Principal James',
                'password' => Hash::make('password'),
                'school_id' => $school->id,
            ]
        );
        $schoolAdmin->school_id = $school->id;
        $schoolAdmin->save();
        $schoolAdmin->assignRole($schoolAdminRole);

        // ── 4. Create Active Academic Session ──
        $session = AcademicSession::firstOrCreate(
            ['school_id' => $school->id, 'name' => '2026-2027'],
            [
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
                'is_active' => true,
            ]
        );

        // ── 5. Create Classes & Sections ──
        $classesData = [
            ['name' => 'Class 9', 'section' => 'A'],
            ['name' => 'Class 9', 'section' => 'B'],
            ['name' => 'Class 9', 'section' => 'C'],
            ['name' => 'Class 10', 'section' => 'A'],
            ['name' => 'Class 10', 'section' => 'B'],
            ['name' => 'Class 11', 'section' => 'A'],
            ['name' => 'Class 11', 'section' => 'B'],
            ['name' => 'Class 12', 'section' => 'A'],
            ['name' => 'Class 12', 'section' => 'B'],
        ];

        $classes = [];
        foreach ($classesData as $cd) {
            $classes[] = SchoolClass::firstOrCreate(
                ['school_id' => $school->id, 'name' => $cd['name'], 'section' => $cd['section']],
                ['room_number' => 'Room ' . rand(100, 300)]
            );
        }

        // ── 6. Create Subjects ──
        $subjectsData = [
            ['name' => 'Mathematics', 'code' => 'MATH-101', 'type' => 'theory'],
            ['name' => 'Physics', 'code' => 'PHY-101', 'type' => 'both'],
            ['name' => 'Chemistry', 'code' => 'CHEM-101', 'type' => 'both'],
            ['name' => 'English Literature', 'code' => 'ENG-101', 'type' => 'theory'],
            ['name' => 'Hindi', 'code' => 'HIN-101', 'type' => 'theory'],
            ['name' => 'Computer Science', 'code' => 'CS-101', 'type' => 'practical'],
            ['name' => 'Biology', 'code' => 'BIO-101', 'type' => 'both'],
            ['name' => 'History', 'code' => 'HIST-101', 'type' => 'theory'],
        ];

        $subjects = [];
        foreach ($subjectsData as $sd) {
            $subjects[] = Subject::firstOrCreate(
                ['school_id' => $school->id, 'code' => $sd['code']],
                ['name' => $sd['name'], 'type' => $sd['type']]
            );
        }

        // ── 7. Create Demo Teachers ──
        $teachersData = [
            ['name' => 'Sarah Connor', 'email' => 'sarah.c@greenwood.com', 'emp' => 'EMP-001', 'designation' => 'HOD Mathematics', 'qualification' => 'M.Sc. Mathematics'],
            ['name' => 'Robert Downey', 'email' => 'robert.d@greenwood.com', 'emp' => 'EMP-002', 'designation' => 'HOD Physics', 'qualification' => 'M.Sc. Physics'],
            ['name' => 'Emma Watson', 'email' => 'emma.w@greenwood.com', 'emp' => 'EMP-003', 'designation' => 'Senior Chemistry Teacher', 'qualification' => 'M.Sc. Chemistry'],
            ['name' => 'James Miller', 'email' => 'james.m@greenwood.com', 'emp' => 'EMP-004', 'designation' => 'English Teacher', 'qualification' => 'M.A. English'],
            ['name' => 'Priya Sharma', 'email' => 'priya.s@greenwood.com', 'emp' => 'EMP-005', 'designation' => 'CS Teacher', 'qualification' => 'B.Tech CSE'],
        ];

        $teachers = [];
        foreach ($teachersData as $td) {
            $user = User::firstOrCreate(
                ['email' => $td['email']],
                [
                    'name' => $td['name'],
                    'password' => Hash::make('password'),
                    'school_id' => $school->id,
                ]
            );
            $user->school_id = $school->id;
            $user->save();
            $user->assignRole($teacherRole);

            TeacherDetail::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => $td['emp'],
                    'designation' => $td['designation'],
                    'qualification' => $td['qualification'],
                    'joining_date' => now()->subYears(rand(1, 8)),
                ]
            );
            $teachers[] = $user;
        }

        // Assign class teachers
        if (isset($classes[3])) { $classes[3]->update(['teacher_id' => $teachers[0]->id]); } // Class 10-A → Sarah
        if (isset($classes[7])) { $classes[7]->update(['teacher_id' => $teachers[1]->id]); } // Class 12-A → Robert
        if (isset($classes[2])) { $classes[2]->update(['teacher_id' => $teachers[2]->id]); } // Class 9-C → Emma

        // Attach subjects to classes with subject teachers
        foreach ($classes as $class) {
            $class->subjects()->syncWithoutDetaching([
                $subjects[0]->id => ['teacher_id' => $teachers[0]->id], // Math
                $subjects[3]->id => ['teacher_id' => $teachers[3]->id], // English
            ]);
        }

        // ── 8. Create Demo Students ──
        $studentNames = [
            'Aarav Patel', 'Ananya Sharma', 'Rohan Gupta', 'Isha Verma',
            'Arjun Singh', 'Meera Reddy', 'Karan Kumar', 'Diya Joshi',
            'Vivaan Nair', 'Saanvi Mishra', 'Aditya Rao', 'Kavya Das',
            'Reyansh Iyer', 'Pooja Thakur', 'Dhruv Malhotra',
        ];

        $studentUsers = [];
        foreach ($studentNames as $idx => $sName) {
            $email = Str::slug($sName, '.') . '@student.greenwood.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $sName,
                    'password' => Hash::make('password'),
                    'school_id' => $school->id,
                ]
            );
            $user->school_id = $school->id;
            $user->save();
            $user->assignRole($studentRole);

            // Distribute students across classes
            $classIdx = $idx % count($classes);
            StudentDetail::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $school->id,
                    'class_id' => $classes[$classIdx]->id,
                    'admission_number' => 'ADM-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                    'roll_number' => (string)($idx + 1),
                    'date_of_birth' => now()->subYears(rand(14, 18))->subDays(rand(0, 365)),
                    'gender' => $idx % 2 === 0 ? 'male' : 'female',
                ]
            );
            $studentUsers[] = $user;
        }

        // ── 9. Create Demo Parent ──
        $parentUser = User::firstOrCreate(
            ['email' => 'rajesh.patel@gmail.com'],
            [
                'name' => 'Rajesh Patel',
                'password' => Hash::make('password'),
                'school_id' => $school->id,
            ]
        );
        $parentUser->school_id = $school->id;
        $parentUser->save();
        $parentUser->assignRole($parentRole);

        // Link parent to first student (Aarav Patel)
        if (isset($studentUsers[0])) {
            \DB::table('parent_student')->updateOrInsert(
                ['parent_id' => $parentUser->id, 'student_id' => $studentUsers[0]->id],
                ['relationship' => 'Father', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // ── 10. Create Demo Notices ──
        $noticesData = [
            ['title' => 'Upcoming Q3 Term Exams', 'content' => 'Final schedules have been released for Class 6th to 12th. Exams start June 22.', 'type' => 'academic', 'published_at' => now()->subDay()],
            ['title' => 'Annual Sports Day Meet', 'content' => 'Registrations for Track & Field events are now open. Contact your class teacher for forms.', 'type' => 'event', 'published_at' => now()->subDays(3)],
            ['title' => 'Summer Holidays Announcement', 'content' => 'Greenwood Academy will remain closed from July 1 to July 15 for summer holidays.', 'type' => 'holiday', 'published_at' => now()->subWeek()],
        ];

        foreach ($noticesData as $nd) {
            Notice::firstOrCreate(
                ['school_id' => $school->id, 'title' => $nd['title']],
                ['content' => $nd['content'], 'type' => $nd['type'], 'published_at' => $nd['published_at']]
            );
        }

        // ── 11. Create Subscription Plans ──
        $basicPlan = SubscriptionPlan::firstOrCreate(
            ['name' => 'Basic Plan'],
            [
                'price' => 49.00,
                'max_students' => 200,
                'max_teachers' => 15,
                'features' => ['Core ERP', 'Basic Support', 'No Custom Domain'],
            ]
        );

        $premiumPlan = SubscriptionPlan::firstOrCreate(
            ['name' => 'Premium Plan'],
            [
                'price' => 149.00,
                'max_students' => 1000,
                'max_teachers' => 100,
                'features' => ['Core ERP', 'Priority Support', 'Custom Domain', 'Custom Roles'],
            ]
        );

        $enterprisePlan = SubscriptionPlan::firstOrCreate(
            ['name' => 'Enterprise Plan'],
            [
                'price' => 499.00,
                'max_students' => -1,
                'max_teachers' => -1,
                'features' => ['All Features Unlimited', 'Dedicated Support Account Manager', 'Custom Branding'],
            ]
        );

        // Assign Premium Plan to Greenwood Academy
        SchoolSubscription::firstOrCreate(
            ['school_id' => $school->id],
            [
                'subscription_plan_id' => $premiumPlan->id,
                'start_date' => now()->subMonths(3)->format('Y-m-d'),
                'end_date' => now()->addMonths(9)->format('Y-m-d'),
                'status' => 'active',
            ]
        );

        // ── 12. Create Support Tickets ──
        SupportTicket::firstOrCreate(
            ['school_id' => $school->id, 'subject' => 'Issue adding new teacher account'],
            [
                'user_id' => $schoolAdmin->id,
                'description' => "I am receiving a duplicate entry error when trying to add a teacher with an email that was previously deleted. Please assist.",
                'priority' => 'medium',
                'status' => 'open',
            ]
        );

        SupportTicket::firstOrCreate(
            ['school_id' => $school->id, 'subject' => 'Timetable Clash Warning Incorrectly Triggered'],
            [
                'user_id' => $schoolAdmin->id,
                'description' => "Our mathematics teacher Sarah Connor is flagged as having a clash on Monday Period 3, but looking at the slots, she is free. Please review.",
                'priority' => 'high',
                'status' => 'pending',
            ]
        );

        SupportTicket::firstOrCreate(
            ['school_id' => $school->id, 'subject' => 'How to export report cards to PDF?'],
            [
                'user_id' => $schoolAdmin->id,
                'description' => "Is there a bulk download feature for report cards? Parents are requesting digital copies of the final terms.",
                'priority' => 'low',
                'status' => 'closed',
            ]
        );

        // ── 13. Create Timetable Slots ──
        // Link all key subjects to Class 10-A ($classes[3]) and Class 9-A ($classes[0])
        $class10A = $classes[3];
        $class9A = $classes[0];

        $class10A->subjects()->syncWithoutDetaching([
            $subjects[0]->id => ['teacher_id' => $teachers[0]->id], // Math -> Sarah
            $subjects[1]->id => ['teacher_id' => $teachers[1]->id], // Physics -> Robert
            $subjects[2]->id => ['teacher_id' => $teachers[2]->id], // Chemistry -> Emma
            $subjects[3]->id => ['teacher_id' => $teachers[3]->id], // English -> James
            $subjects[5]->id => ['teacher_id' => $teachers[4]->id], // CS -> Priya
        ]);

        $class9A->subjects()->syncWithoutDetaching([
            $subjects[0]->id => ['teacher_id' => $teachers[0]->id], // Math -> Sarah
            $subjects[1]->id => ['teacher_id' => $teachers[1]->id], // Physics -> Robert
            $subjects[2]->id => ['teacher_id' => $teachers[2]->id], // Chemistry -> Emma
            $subjects[3]->id => ['teacher_id' => $teachers[3]->id], // English -> James
            $subjects[5]->id => ['teacher_id' => $teachers[4]->id], // CS -> Priya
        ]);

        $timetableData = [
            // Day 1 (Mon)
            ['day' => 1, 'period' => 1, 'subject' => $subjects[0], 'teacher' => $teachers[0], 'start' => '08:30:00', 'end' => '09:15:00', 'room' => 'Room 101'],
            ['day' => 1, 'period' => 2, 'subject' => $subjects[3], 'teacher' => $teachers[3], 'start' => '09:15:00', 'end' => '10:00:00', 'room' => 'Room 101'],
            ['day' => 1, 'period' => 3, 'subject' => $subjects[1], 'teacher' => $teachers[1], 'start' => '10:15:00', 'end' => '11:00:00', 'room' => 'Lab 1'],
            ['day' => 1, 'period' => 4, 'subject' => $subjects[2], 'teacher' => $teachers[2], 'start' => '11:00:00', 'end' => '11:45:00', 'room' => 'Lab 2'],

            // Day 2 (Tue)
            ['day' => 2, 'period' => 1, 'subject' => $subjects[1], 'teacher' => $teachers[1], 'start' => '08:30:00', 'end' => '09:15:00', 'room' => 'Lab 1'],
            ['day' => 2, 'period' => 2, 'subject' => $subjects[2], 'teacher' => $teachers[2], 'start' => '09:15:00', 'end' => '10:00:00', 'room' => 'Lab 2'],
            ['day' => 2, 'period' => 3, 'subject' => $subjects[0], 'teacher' => $teachers[0], 'start' => '10:15:00', 'end' => '11:00:00', 'room' => 'Room 101'],
            ['day' => 2, 'period' => 4, 'subject' => $subjects[5], 'teacher' => $teachers[4], 'start' => '11:00:00', 'end' => '11:45:00', 'room' => 'Computer Lab'],

            // Day 3 (Wed)
            ['day' => 3, 'period' => 1, 'subject' => $subjects[0], 'teacher' => $teachers[0], 'start' => '08:30:00', 'end' => '09:15:00', 'room' => 'Room 101'],
            ['day' => 3, 'period' => 2, 'subject' => $subjects[3], 'teacher' => $teachers[3], 'start' => '09:15:00', 'end' => '10:00:00', 'room' => 'Room 101'],
            ['day' => 3, 'period' => 3, 'subject' => $subjects[5], 'teacher' => $teachers[4], 'start' => '10:15:00', 'end' => '11:00:00', 'room' => 'Computer Lab'],
            ['day' => 3, 'period' => 4, 'subject' => $subjects[1], 'teacher' => $teachers[1], 'start' => '11:00:00', 'end' => '11:45:00', 'room' => 'Lab 1'],

            // Day 4 (Thu)
            ['day' => 4, 'period' => 1, 'subject' => $subjects[2], 'teacher' => $teachers[2], 'start' => '08:30:00', 'end' => '09:15:00', 'room' => 'Lab 2'],
            ['day' => 4, 'period' => 2, 'subject' => $subjects[3], 'teacher' => $teachers[3], 'start' => '09:15:00', 'end' => '10:00:00', 'room' => 'Room 101'],
            ['day' => 4, 'period' => 3, 'subject' => $subjects[0], 'teacher' => $teachers[0], 'start' => '10:15:00', 'end' => '11:00:00', 'room' => 'Room 101'],
            ['day' => 4, 'period' => 4, 'subject' => $subjects[1], 'teacher' => $teachers[1], 'start' => '11:00:00', 'end' => '11:45:00', 'room' => 'Lab 1'],

            // Day 5 (Fri)
            ['day' => 5, 'period' => 1, 'subject' => $subjects[0], 'teacher' => $teachers[0], 'start' => '08:30:00', 'end' => '09:15:00', 'room' => 'Room 101'],
            ['day' => 5, 'period' => 2, 'subject' => $subjects[5], 'teacher' => $teachers[4], 'start' => '09:15:00', 'end' => '10:00:00', 'room' => 'Computer Lab'],
            ['day' => 5, 'period' => 3, 'subject' => $subjects[3], 'teacher' => $teachers[3], 'start' => '10:15:00', 'end' => '11:00:00', 'room' => 'Room 101'],
            ['day' => 5, 'period' => 4, 'subject' => $subjects[2], 'teacher' => $teachers[2], 'start' => '11:00:00', 'end' => '11:45:00', 'room' => 'Lab 2'],
        ];

        foreach ($timetableData as $slot) {
            TimetableSlot::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'class_id' => $class10A->id,
                    'day_of_week' => $slot['day'],
                    'period_number' => $slot['period'],
                ],
                [
                    'subject_id' => $slot['subject']->id,
                    'teacher_id' => $slot['teacher']->id,
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                    'room_number' => $slot['room'],
                ]
            );
        }

        // ── 14. Create Fee Structures ──
        $tuitionFee = FeeStructure::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Tuition Fee'],
            [
                'academic_session_id' => $session->id,
                'amount' => 5000.00,
                'frequency' => 'monthly',
                'applicable_classes' => null, // all classes
            ]
        );

        $transportFee = FeeStructure::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Transport Fee'],
            [
                'academic_session_id' => $session->id,
                'amount' => 1200.00,
                'frequency' => 'monthly',
                'applicable_classes' => null,
            ]
        );

        $examFee = FeeStructure::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Exam Fee'],
            [
                'academic_session_id' => $session->id,
                'amount' => 800.00,
                'frequency' => 'quarterly',
                'applicable_classes' => null,
            ]
        );

        // ── 15. Create Fee Payments ──
        $paymentMethods = ['cash', 'upi', 'bank_transfer'];
        foreach ($studentUsers as $key => $student) {
            // Seed Tuition Fee payments for first 10 students
            if ($key >= 10) break;

            if ($key < 6) {
                $status = $key < 3 ? 'paid' : 'partial';
                $amountPaid = $status === 'paid' ? 5000.00 : 2500.00;

                FeePayment::firstOrCreate(
                    [
                        'school_id' => $school->id,
                        'student_id' => $student->id,
                        'fee_structure_id' => $tuitionFee->id,
                    ],
                    [
                        'amount_paid' => $amountPaid,
                        'payment_date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                        'payment_method' => $paymentMethods[$key % 3],
                        'receipt_number' => 'REC-2026-' . str_pad($key + 1, 4, '0', STR_PAD_LEFT),
                        'status' => $status,
                        'remarks' => $status === 'paid' ? 'Paid in full.' : 'First installment paid.',
                        'collected_by' => $schoolAdmin->id,
                    ]
                );
            }

            if ($key < 4) {
                FeePayment::firstOrCreate(
                    [
                        'school_id' => $school->id,
                        'student_id' => $student->id,
                        'fee_structure_id' => $transportFee->id,
                    ],
                    [
                        'amount_paid' => 1200.00,
                        'payment_date' => now()->subDays(rand(1, 15))->format('Y-m-d'),
                        'payment_method' => 'upi',
                        'receipt_number' => 'REC-2026-T' . str_pad($key + 1, 4, '0', STR_PAD_LEFT),
                        'status' => 'paid',
                        'remarks' => 'Monthly transport fee.',
                        'collected_by' => $schoolAdmin->id,
                    ]
                );
            }
        }

        // ── 16. Create Exams ──
        $midTerm = Exam::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Q1 Mid-Term Assessment'],
            [
                'academic_session_id' => $session->id,
                'type' => 'mid_term',
                'start_date' => now()->subMonths(2)->format('Y-m-d'),
                'end_date' => now()->subMonths(2)->addDays(5)->format('Y-m-d'),
                'status' => 'completed',
            ]
        );

        $finalTerm = Exam::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Final Semester Examination'],
            [
                'academic_session_id' => $session->id,
                'type' => 'final',
                'start_date' => now()->addMonths(2)->format('Y-m-d'),
                'end_date' => now()->addMonths(2)->addDays(7)->format('Y-m-d'),
                'status' => 'upcoming',
            ]
        );

        // ── 17. Create Exam Schedules ──
        $mathSchedule = ExamSchedule::firstOrCreate(
            [
                'exam_id' => $midTerm->id,
                'class_id' => $class10A->id,
                'subject_id' => $subjects[0]->id,
            ],
            [
                'exam_date' => now()->subMonths(2)->format('Y-m-d'),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'max_marks' => 100,
                'passing_marks' => 33,
            ]
        );

        $englishSchedule = ExamSchedule::firstOrCreate(
            [
                'exam_id' => $midTerm->id,
                'class_id' => $class10A->id,
                'subject_id' => $subjects[3]->id,
            ],
            [
                'exam_date' => now()->subMonths(2)->addDay()->format('Y-m-d'),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'max_marks' => 100,
                'passing_marks' => 33,
            ]
        );

        $finalMathSchedule = ExamSchedule::firstOrCreate(
            [
                'exam_id' => $finalTerm->id,
                'class_id' => $class10A->id,
                'subject_id' => $subjects[0]->id,
            ],
            [
                'exam_date' => now()->addMonths(2)->format('Y-m-d'),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'max_marks' => 100,
                'passing_marks' => 33,
            ]
        );

        // ── 18. Create Marks obtained ──
        // Get all student users enrolled in Class 10-A
        $class10AStudents = User::role('student')
            ->whereHas('studentDetail', function ($query) use ($class10A) {
                $query->where('class_id', $class10A->id);
            })
            ->get();

        foreach ($class10AStudents as $student) {
            $mathMarks = rand(35, 98);
            $mathGrade = $this->calculateGrade($mathMarks);

            Mark::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'exam_schedule_id' => $mathSchedule->id,
                    'student_id' => $student->id,
                ],
                [
                    'marks_obtained' => $mathMarks,
                    'grade' => $mathGrade,
                    'remarks' => $mathMarks > 85 ? 'Excellent performance!' : ($mathMarks > 60 ? 'Good work, keep it up.' : 'Needs improvement.'),
                    'entered_by' => $teachers[0]->id,
                ]
            );

            $englishMarks = rand(45, 95);
            $englishGrade = $this->calculateGrade($englishMarks);

            Mark::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'exam_schedule_id' => $englishSchedule->id,
                    'student_id' => $student->id,
                ],
                [
                    'marks_obtained' => $englishMarks,
                    'grade' => $englishGrade,
                    'remarks' => $englishMarks > 85 ? 'Great vocabulary and comprehension.' : 'Satisfactory result.',
                    'entered_by' => $teachers[3]->id,
                ]
            );
        }

        // ── 19. Create Notifications ──
        $notificationsData = [
            ['title' => 'Parent Linked Successfully', 'body' => 'Parent Rajesh Patel has been linked to student Aarav Patel.', 'type' => 'success'],
            ['title' => 'System Update Complete', 'body' => 'The AuraCampus platform has been updated to version 2.1.0 with Timetable Clash Detection.', 'type' => 'info'],
            ['title' => 'Notice Published', 'body' => 'Your notice "Upcoming Q3 Term Exams" has been published successfully.', 'type' => 'success'],
            ['title' => 'Low Attendance Warning', 'body' => 'Class 10-A has average attendance below 75% this week.', 'type' => 'warning'],
        ];

        foreach ($notificationsData as $noti) {
            Notification::create([
                'user_id' => $schoolAdmin->id,
                'title' => $noti['title'],
                'body' => $noti['body'],
                'type' => $noti['type'],
                'read_at' => rand(0, 1) ? now()->subMinutes(rand(10, 500)) : null,
            ]);
        }
    }

    /**
     * Calculate grade based on percentage marks obtained.
     */
    private function calculateGrade($percentage): string
    {
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

