<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles and permissions for each test
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_super_admin_redirects_to_super_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('superadmin.dashboard', absolute: false));
    }

    public function test_school_admin_redirects_to_school_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('school-admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('school.dashboard', absolute: false));
    }

    public function test_super_admin_can_access_super_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('superadmin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_school_admin_cannot_access_super_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('school-admin');

        $response = $this->actingAs($user)->get(route('superadmin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_school_admin_can_access_school_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('school-admin');

        $response = $this->actingAs($user)->get(route('school.dashboard'));

        $response->assertStatus(200);
    }

    public function test_super_admin_cannot_access_school_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('school.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guests_cannot_access_dashboards(): void
    {
        $response = $this->get(route('superadmin.dashboard'));
        $response->assertRedirect('/login');

        $response = $this->get(route('school.dashboard'));
        $response->assertRedirect('/login');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
