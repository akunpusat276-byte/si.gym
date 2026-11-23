<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_users_by_role_and_account_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $activeMember = User::factory()->create([
            'role' => 'member',
            'status' => 'Aktif',
            'name' => 'Member Aktif',
        ]);
        User::factory()->create([
            'role' => 'member',
            'status' => 'Nonaktif',
            'name' => 'Member Nonaktif',
        ]);
        User::factory()->create(['role' => 'pendaftar', 'name' => 'Calon Member']);

        $this->actingAs($admin)
            ->get(route('users.index', ['role' => 'member', 'status' => 'Aktif']))
            ->assertOk()
            ->assertSee('Member Aktif')
            ->assertDontSee('Member Nonaktif')
            ->assertDontSee('Calon Member');

        $this->assertSame('Aktif', $activeMember->fresh()->status);
    }

    public function test_admin_can_open_a_user_detail_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'role' => 'member',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'phone' => '08123456789',
            'address' => 'Jl. Sehat No. 1',
        ]);

        $this->actingAs($admin)
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('budi@example.test')
            ->assertSee('08123456789')
            ->assertSee('Jl. Sehat No. 1');
    }
}
