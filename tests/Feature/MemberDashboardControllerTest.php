<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_dashboard_shows_latest_membership_and_only_the_five_latest_payments(): void
    {
        $this->travelTo('2026-11-10 10:00:00');
        $user = User::factory()->create(['role' => 'member', 'name' => 'Budi Santoso']);
        $otherUser = User::factory()->create(['role' => 'member', 'name' => 'Member Lain']);
        $member = Member::create([
            'user_id' => $user->id,
            'member_code' => 'GYM-000123',
        ]);
        $currentPackage = $this->createPackage('Premium');
        $oldPackage = $this->createPackage('Basic');
        $this->createMembership($member, $oldPackage, now()->subMonths(3), now()->subMonths(2));
        $this->createMembership($member, $currentPackage, now()->subMonth(), now()->addDays(24));

        foreach (range(0, 5) as $index) {
            $this->createPayment($user, $currentPackage, 'INV-MEMBER-'.$index, now()->subDays($index));
        }
        $this->createPayment($otherUser, $currentPackage, 'INV-OTHER', now()->addDay(), 'QRIS');

        $response = $this->actingAs($user)->get(route('member.dashboard'));

        $response->assertOk()
            ->assertSee('Selamat Datang, Budi Santoso!')
            ->assertSee('GYM-000123')
            ->assertSee('Premium')
            ->assertSee('10-11-2026')
            ->assertSee('06-11-2026')
            ->assertDontSee('Basic')
            ->assertDontSee('05-11-2026')
            ->assertDontSee('QRIS');
    }

    private function createPackage(string $name): Package
    {
        return Package::create([
            'name' => $name,
            'duration' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);
    }

    private function createMembership(
        Member $member,
        Package $package,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
    ): Membership {
        return Membership::create([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration' => 1,
            'status' => 'Aktif',
        ]);
    }

    private function createPayment(
        User $user,
        Package $package,
        string $invoice,
        CarbonInterface $paymentDate,
        string $method = 'Transfer Bank',
    ): Payment {
        return Payment::create([
            'invoice_number' => $invoice,
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => $package->price,
            'payment_date' => $paymentDate,
            'method' => $method,
            'status' => 'Lunas',
        ]);
    }
}
