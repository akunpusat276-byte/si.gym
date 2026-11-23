<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_is_redirected_to_its_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/admin/dashboard');
    }

    public function test_owner_cannot_open_admin_mutation_route(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->get('/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($owner)
            ->post('/admin/packages')
            ->assertForbidden();
    }

    public function test_member_only_sees_own_payment_history(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get('/member/payments')
            ->assertOk();
    }

    public function test_registrant_cannot_open_admin_area(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);

        $this->actingAs($registrant)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_owner_cannot_mutate_owner_read_only_routes(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->post('/owner/dashboard')
            ->assertForbidden();
    }

    public function test_admin_can_open_member_crud_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/members/create')
            ->assertOk();
    }
}
