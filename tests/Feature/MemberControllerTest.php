<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_member_with_an_automatically_generated_unique_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($admin)->post(route('members.store'), [
            'user_id' => $user->id,
            'phone' => '08123456789',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01',
            'address' => 'Jl. Sehat No. 1',
        ]);

        $response->assertRedirect(route('members.index'));
        $member = Member::where('user_id', $user->id)->firstOrFail();
        $this->assertMatchesRegularExpression('/^MBR-\d{8}-\d+$/', $member->member_code);
        $this->assertSame(1, Member::where('member_code', $member->member_code)->count());
        $this->assertSame('08123456789', $member->phone);
        $this->assertSame('member', $user->fresh()->role);
    }

    public function test_admin_cannot_register_a_non_member_user_as_a_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($admin)
            ->from(route('members.create'))
            ->post(route('members.store'), [
                'user_id' => $owner->id,
            ]);

        $response->assertRedirectBackWithErrors(['user_id']);
        $this->assertDatabaseMissing('members', ['user_id' => $owner->id]);
        $this->assertSame('owner', $owner->fresh()->role);
    }

    public function test_admin_can_search_members_and_open_their_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'member', 'name' => 'Budi Santoso']);
        $member = $this->createMember($user, ['member_code' => 'GYM-00123']);
        User::factory()->create(['role' => 'member', 'name' => 'Siti Nurhaliza']);

        $this->actingAs($admin)
            ->get(route('members.index', ['search' => 'GYM-00123']))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Siti Nurhaliza');

        $this->get(route('members.show', $member))
            ->assertOk()
            ->assertSee('GYM-00123')
            ->assertSee('Budi Santoso');
    }

    public function test_admin_can_update_member_profile_without_changing_the_generated_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $memberUser = User::factory()->create(['role' => 'member']);
        $member = $this->createMember($memberUser);
        $memberCode = $member->member_code;

        $response = $this->actingAs($admin)->put(route('members.update', $member), [
            'phone' => '08987654321',
            'gender' => 'Perempuan',
            'birth_date' => '1998-06-15',
            'address' => 'Jl. Baru No. 5',
        ]);

        $response->assertRedirect(route('members.index'));
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'member_code' => $memberCode,
            'phone' => '08987654321',
            'gender' => 'Perempuan',
            'address' => 'Jl. Baru No. 5',
        ]);
    }

    public function test_admin_can_open_edit_form_with_existing_member_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = $this->createMember(
            User::factory()->create(['role' => 'member']),
            [
                'gender' => 'Perempuan',
                'birth_date' => '1998-06-15',
            ],
        );

        $this->actingAs($admin)
            ->get(route('members.edit', $member))
            ->assertOk()
            ->assertSee('Edit Member')
            ->assertSee('Perempuan')
            ->assertSee('1998-06-15');
    }

    public function test_admin_cannot_delete_a_member_with_membership(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $memberUser = User::factory()->create(['role' => 'member']);
        $member = $this->createMember($memberUser);
        $package = Package::create([
            'name' => 'Paket Bulanan',
            'duration' => 1,
            'price' => 150000,
        ]);
        Membership::create([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'duration' => 1,
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($admin)->delete(route('members.destroy', $member));

        $response->assertRedirect(route('members.index'))
            ->assertSessionHas('error', 'Member yang memiliki membership tidak dapat dihapus.');
        $this->assertDatabaseHas('members', ['id' => $member->id]);
        $this->assertSame('member', $memberUser->fresh()->role);
    }

    public function test_admin_can_delete_member_without_memberships_and_restore_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $memberUser = User::factory()->create(['role' => 'member']);
        $member = $this->createMember($memberUser);

        $response = $this->actingAs($admin)->delete(route('members.destroy', $member));

        $response->assertRedirect(route('members.index'));
        $this->assertDatabaseMissing('members', ['id' => $member->id]);
        $this->assertSame('pendaftar', $memberUser->fresh()->role);
    }

    public function test_admin_can_toggle_member_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = $this->createMember(User::factory()->create(['role' => 'member']));

        $response = $this->actingAs($admin)->patch(route('members.status', $member));

        $response->assertRedirect();
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'status' => 'Nonaktif',
        ]);
    }

    public function test_owner_can_read_member_details_but_cannot_mutate_members(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $member = $this->createMember(User::factory()->create(['role' => 'member']));

        $this->actingAs($owner)
            ->get(route('owner.members'))
            ->assertOk()
            ->assertSee('read-only')
            ->assertDontSee('Tambah Member');

        $this->delete(route('members.destroy', $member))->assertForbidden();
        $this->assertDatabaseHas('members', ['id' => $member->id]);
    }

    public function test_member_search_rejects_non_string_input(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('members.index', ['search' => ['invalid']]))
            ->assertSessionHasErrors(['search']);
    }

    public function test_admin_can_filter_members_by_membership_status_and_package(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $package = Package::create([
            'name' => 'Paket Basic',
            'duration' => 1,
            'price' => 150000,
        ]);
        $activeUser = User::factory()->create(['role' => 'member', 'name' => 'Member Aktif']);
        $expiredUser = User::factory()->create(['role' => 'member', 'name' => 'Member Expired']);
        $activeMember = $this->createMember($activeUser);
        $expiredMember = $this->createMember($expiredUser);
        Membership::create([
            'member_id' => $activeMember->id,
            'package_id' => $package->id,
            'start_date' => today()->subDays(10),
            'end_date' => today()->addDays(20),
            'duration' => 1,
            'status' => 'Aktif',
        ]);
        Membership::create([
            'member_id' => $expiredMember->id,
            'package_id' => $package->id,
            'start_date' => today()->subDays(40),
            'end_date' => today()->subDays(10),
            'duration' => 1,
            'status' => 'Kedaluwarsa',
        ]);

        $this->actingAs($admin)
            ->get(route('members.index', ['membership_status' => 'Aktif', 'package_id' => $package->id]))
            ->assertOk()
            ->assertSee('Member Aktif')
            ->assertDontSee('Member Expired');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createMember(User $user, array $attributes = []): Member
    {
        return Member::create([
            'user_id' => $user->id,
            'member_code' => 'MBR-'.$user->id,
            'phone' => null,
            'gender' => null,
            'birth_date' => null,
            'address' => null,
            ...$attributes,
        ]);
    }
}
