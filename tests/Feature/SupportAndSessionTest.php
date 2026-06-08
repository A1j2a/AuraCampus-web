<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\FeeStructure;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportAndSessionTest extends TestCase
{
    use RefreshDatabase;

    private User $schoolAdmin;
    private User $superAdmin;
    private School $school;
    private AcademicSession $session2026;
    private AcademicSession $session2025;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & basic demo structures
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        $this->schoolAdmin = User::role('school-admin')->first();
        $this->superAdmin = User::role('super-admin')->first();
        $this->school = School::where('slug', 'greenwood-academy')->first();

        // Retrieve seeded 2026 session
        $this->session2026 = AcademicSession::where('school_id', $this->school->id)->where('name', '2026-2027')->first();

        // Create a past 2025 session for toggle testing
        $this->session2025 = AcademicSession::create([
            'school_id' => $this->school->id,
            'name' => '2025-2026',
            'start_date' => '2025-04-01',
            'end_date' => '2026-03-31',
            'is_active' => false,
        ]);
    }

    /**
     * Test Academic Session switcher saves active session to the user session.
     */
    public function test_session_select_updates_active_academic_session(): void
    {
        $response = $this->actingAs($this->schoolAdmin)
            ->post('/school/session/select', [
                'academic_session_id' => $this->session2025->id,
            ]);

        $response->assertSessionHas('active_academic_session_id', $this->session2025->id);
        
        // Verify user model helper returns the correct active session
        $this->assertEquals($this->session2025->id, $this->schoolAdmin->getActiveAcademicSession()->id);
    }

    /**
     * Test active session filters exams list.
     */
    public function test_exams_list_is_filtered_by_active_session(): void
    {
        // 2026 exam
        $exam2026 = Exam::where('school_id', $this->school->id)->where('academic_session_id', $this->session2026->id)->first();
        
        // Create a 2025 exam
        $exam2025 = Exam::create([
            'school_id' => $this->school->id,
            'academic_session_id' => $this->session2025->id,
            'name' => 'Past 2025 Midterms',
            'type' => 'mid_term',
            'start_date' => '2025-09-01',
            'end_date' => '2025-09-05',
            'status' => 'completed',
        ]);

        // Scenario 1: Active session is 2026-2027
        $response = $this->actingAs($this->schoolAdmin)
            ->withSession(['active_academic_session_id' => $this->session2026->id])
            ->get('/school/exams');

        $response->assertStatus(200);
        $response->assertSee($exam2026->name);
        $response->assertDontSee($exam2025->name);

        // Scenario 2: Active session toggled to 2025-2026
        $response = $this->actingAs($this->schoolAdmin)
            ->withSession(['active_academic_session_id' => $this->session2025->id])
            ->get('/school/exams');

        $response->assertStatus(200);
        $response->assertSee($exam2025->name);
        $response->assertDontSee($exam2026->name);
    }

    /**
     * Test school admin support tickets CRUD and thread view.
     */
    public function test_school_admin_can_manage_tickets_and_send_messages(): void
    {
        // Create ticket
        $response = $this->actingAs($this->schoolAdmin)
            ->post('/school/support', [
                'subject' => 'Database Sync Delay',
                'description' => 'Our student roster takes 5 minutes to show updates.',
                'priority' => 'medium',
            ]);

        $ticket = SupportTicket::where('subject', 'Database Sync Delay')->first();
        $this->assertNotNull($ticket);

        $response->assertRedirect(route('school.support.show', $ticket));

        // Send chat message
        $response = $this->actingAs($this->schoolAdmin)
            ->post("/school/support/{$ticket->id}/message", [
                'message' => 'Also, we checked that this affects class 10-A only.',
            ]);

        $response->assertRedirect(route('school.support.show', $ticket));

        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $this->schoolAdmin->id,
            'message' => 'Also, we checked that this affects class 10-A only.',
        ]);
    }

    /**
     * Test ticket reopen triggers on new client message.
     */
    public function test_ticket_reopens_on_client_reply_if_closed(): void
    {
        $ticket = SupportTicket::create([
            'school_id' => $this->school->id,
            'user_id' => $this->schoolAdmin->id,
            'subject' => 'Printer config help',
            'description' => 'We need custom paper layout guidance.',
            'priority' => 'low',
            'status' => 'closed',
        ]);

        $this->actingAs($this->schoolAdmin)
            ->post("/school/support/{$ticket->id}/message", [
                'message' => 'Actually, I need help with letterhead printing too.',
            ]);

        $this->assertEquals('open', $ticket->fresh()->status);
    }

    /**
     * Test Super Admin support desk replies and status updates.
     */
    public function test_super_admin_can_reply_and_resolve_tickets(): void
    {
        $ticket = SupportTicket::create([
            'school_id' => $this->school->id,
            'user_id' => $this->schoolAdmin->id,
            'subject' => 'API Timeout Error',
            'description' => 'Sanctum responses are taking 10s.',
            'priority' => 'high',
            'status' => 'open',
        ]);

        // Super Admin replies to ticket
        $response = $this->actingAs($this->superAdmin)
            ->post("/super-admin/support/{$ticket->id}/message", [
                'message' => 'We have optimized the database queries. Please check now.',
            ]);

        $response->assertRedirect(route('superadmin.support.show', $ticket));

        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $this->superAdmin->id,
            'message' => 'We have optimized the database queries. Please check now.',
        ]);

        // Super Admin replies change ticket status to pending
        $this->assertEquals('pending', $ticket->fresh()->status);

        // Super Admin closes the ticket
        $this->actingAs($this->superAdmin)
            ->patch("/super-admin/support/{$ticket->id}", [
                'status' => 'closed',
            ]);

        $this->assertEquals('closed', $ticket->fresh()->status);
    }

    /**
     * Test tenant security isolation for support tickets.
     */
    public function test_tenant_isolation_boundary_on_support_tickets(): void
    {
        // Create a different school and a different school admin
        $otherSchool = School::create(['name' => 'Blue Valley School', 'slug' => 'blue-valley']);
        $otherAdmin = User::factory()->create(['school_id' => $otherSchool->id]);
        $otherAdmin->assignRole('school-admin');

        // School Admin 1 creates a ticket
        $ticket = SupportTicket::create([
            'school_id' => $this->school->id,
            'user_id' => $this->schoolAdmin->id,
            'subject' => 'School 1 Private Issue',
            'description' => 'Confidential details.',
            'priority' => 'low',
        ]);

        // School Admin 2 tries to view ticket (should block)
        $response = $this->actingAs($otherAdmin)
            ->get("/school/support/{$ticket->id}");
        $response->assertStatus(403);

        // School Admin 2 tries to message on ticket (should block)
        $response = $this->actingAs($otherAdmin)
            ->post("/school/support/{$ticket->id}/message", [
                'message' => 'I am writing in an unauthorized ticket.',
            ]);
        $response->assertStatus(403);
    }
}
