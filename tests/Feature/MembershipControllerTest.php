<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_sees_only_their_own_memberships_and_cannot_open_another_membership(): void
    {
        $memberUser = User::factory()->create(['role' => 'member', 'name' => 'Budi Member']);
        $otherMemberUser = User::factory()->create(['role' => 'member', 'name' => 'Siti Member']);
        $package = $this->createPackage();
        $member = $this->createMember($memberUser);
        $otherMember = $this->createMember($otherMemberUser);
        $membership = $this->createMembership($member, $package);
        $otherMembership = $this->createMembership($otherMember, $package);

        $this->actingAs($memberUser)
            ->get(route('member.memberships'))
            ->assertOk()
            ->assertSee('Budi Member')
            ->assertDontSee('Siti Member');

        $this->get(route('member.memberships.show', $otherMembership))->assertNotFound();
        $this->get(route('member.memberships.show', $membership))->assertOk();
    }

    public function test_member_can_request_a_renewal_without_activating_membership_before_payment(): void
    {
        $memberUser = User::factory()->create(['role' => 'member']);
        $package = $this->createPackage();
        $member = $this->createMember($memberUser);
        $membership = $this->createMembership($member, $package);

        $this->actingAs($memberUser)
            ->post(route('member.memberships.renewal', $membership))
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'user_id' => $memberUser->id,
            'package_id' => $package->id,
            'status' => 'Menunggu Pembayaran',
        ]);
        $this->assertSame(1, Membership::where('member_id', $member->id)->count());
        $this->assertSame(1, Payment::where('user_id', $memberUser->id)->count());
    }

    private function createPackage(): Package
    {
        return Package::create([
            'name' => 'Paket Basic',
            'duration' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);
    }

    private function createMember(User $user): Member
    {
        return Member::create([
            'user_id' => $user->id,
            'member_code' => 'MBR-'.$user->id,
        ]);
    }

    private function createMembership(Member $member, Package $package): Membership
    {
        return Membership::create([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'duration' => 1,
            'status' => 'Aktif',
        ]);
    }
}
