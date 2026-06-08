<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TimetableSlot;
use App\Models\FeeStructure;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\AcademicSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    // Reset database for tests to run cleanly
    use RefreshDatabase;

    private User $teacher;
    private User $parent;
    private User $student;
    private SchoolClass $class;
    private Subject $subject;
    private AcademicSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic roles
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        // Fetch seeded records for testing
        $this->teacher = User::role('teacher')->first();
        $this->parent = User::role('parent')->first();
        $this->student = User::role('student')->first();
        $this->class = SchoolClass::first();
        $this->subject = Subject::first();
        $this->session = AcademicSession::first();
    }

    /**
     * Test API authentication login.
     */
    public function test_auth_login_succeeds_with_correct_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'sarah.c@greenwood.com', // Seeded teacher
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'token',
                         'user' => [
                             'id',
                             'name',
                             'email',
                             'role',
                             'school',
                         ]
                     ]
                 ]);
    }

    /**
     * Test API authentication failure.
     */
    public function test_auth_login_fails_with_incorrect_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'sarah.c@greenwood.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Invalid credentials.',
                 ]);
    }

    /**
     * Test that standard non-allowed roles (like student) cannot log in to these portals.
     */
    public function test_auth_login_restricted_for_unallowed_roles(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'aarav.patel@student.greenwood.com', // Seeded student
            'password' => 'password',
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Access denied. You do not have permissions to access the mobile portals.',
                 ]);
    }

    /**
     * Test teacher dashboard endpoint.
     */
    public function test_teacher_dashboard_returns_correct_stats(): void
    {
        Sanctum::actingAs($this->teacher, ['*']);

        $response = $this->getJson('/api/teacher/dashboard');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'today_classes_count',
                         'total_students',
                         'notices',
                     ]
                 ]);
    }

    /**
     * Test teacher timetable endpoint.
     */
    public function test_teacher_timetable_retrieval(): void
    {
        Sanctum::actingAs($this->teacher, ['*']);

        $response = $this->getJson('/api/teacher/timetable');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         '*' => [
                             'id',
                             'day_of_week',
                             'period_number',
                             'start_time',
                             'end_time',
                             'room_number',
                             'class',
                             'subject',
                         ]
                     ]
                 ]);
    }

    /**
     * Test teacher saving attendance.
     */
    public function test_teacher_can_submit_attendance(): void
    {
        Sanctum::actingAs($this->teacher, ['*']);

        $response = $this->postJson("/api/teacher/classes/{$this->class->id}/attendance", [
            'date' => now()->format('Y-m-d'),
            'attendance' => [
                $this->student->id => 'present',
            ]
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Attendance marked successfully.',
                 ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'status' => 'present',
        ]);
    }

    /**
     * Test parent children directory list.
     */
    public function test_parent_can_list_children(): void
    {
        Sanctum::actingAs($this->parent, ['*']);

        $response = $this->getJson('/api/parent/children');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         '*' => [
                             'student_id',
                             'name',
                             'email',
                             'roll_number',
                             'admission_number',
                             'class',
                         ]
                     ]
                 ]);
    }

    /**
     * Test parent accessing dashboard stats of child.
     */
    public function test_parent_can_access_child_dashboard_stats(): void
    {
        Sanctum::actingAs($this->parent, ['*']);

        $response = $this->getJson("/api/parent/children/{$this->student->id}/dashboard");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'student',
                         'stats' => [
                             'attendance_percentage',
                             'total_paid',
                             'outstanding_dues',
                         ],
                         'notices',
                     ]
                 ]);
    }

    /**
     * Test tenant security isolation: a parent cannot access details of a non-linked student.
     */
    public function test_parent_cannot_access_unlinked_student_data(): void
    {
        // Create another student user who is not linked to this parent
        $anotherStudent = User::factory()->create([
            'school_id' => $this->parent->school_id,
        ]);
        $anotherStudent->assignRole('student');

        Sanctum::actingAs($this->parent, ['*']);

        $response = $this->getJson("/api/parent/children/{$anotherStudent->id}/dashboard");

        $response->assertStatus(403)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Access denied. This student is not linked to your account.',
                 ]);
    }
}
