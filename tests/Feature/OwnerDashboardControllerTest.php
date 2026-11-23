<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_reports_monthly_revenue_and_non_overlapping_membership_statuses(): void
    {
        $this->travelTo('2026-11-10 10:00:00');
        $owner = User::factory()->create(['role' => 'owner']);
        $package = Package::create([
            'name' => 'Paket Basic',
            'duration' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);
        $activeMemberUser = User::factory()->create(['role' => 'member', 'name' => 'Member Aktif']);
        $expiringMemberUser = User::factory()->create(['role' => 'member', 'name' => 'Member Akan Berakhir']);
        $expiredMemberUser = User::factory()->create(['role' => 'member', 'name' => 'Member Kedaluwarsa']);
        $activeMember = Member::create(['user_id' => $activeMemberUser->id, 'member_code' => 'GYM-001']);
        $expiringMember = Member::create(['user_id' => $expiringMemberUser->id, 'member_code' => 'GYM-002']);
        $expiredMember = Member::create(['user_id' => $expiredMemberUser->id, 'member_code' => 'GYM-003']);

        Membership::create([
            'member_id' => $activeMember->id,
            'package_id' => $package->id,
            'start_date' => today()->subDays(10),
            'end_date' => today()->addDays(30),
            'duration' => 1,
            'status' => 'Aktif',
        ]);
        Membership::create([
            'member_id' => $expiringMember->id,
            'package_id' => $package->id,
            'start_date' => today()->subDays(20),
            'end_date' => today()->addDays(5),
            'duration' => 1,
            'status' => 'Aktif',
        ]);
        Membership::create([
            'member_id' => $expiredMember->id,
            'package_id' => $package->id,
            'start_date' => today()->subDays(60),
            'end_date' => today()->subDay(),
            'duration' => 1,
            'status' => 'Kedaluwarsa',
        ]);
        Payment::create([
            'invoice_number' => 'INV-CURRENT-MONTH',
            'user_id' => $activeMemberUser->id,
            'package_id' => $package->id,
            'amount' => 123456,
            'payment_date' => today(),
            'method' => 'Transfer Bank',
            'status' => 'Lunas',
        ]);
        Payment::create([
            'invoice_number' => 'INV-PREVIOUS-MONTH',
            'user_id' => $activeMemberUser->id,
            'package_id' => $package->id,
            'amount' => 500000,
            'payment_date' => today()->subMonth(),
            'method' => 'Transfer Bank',
            'status' => 'Lunas',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('READ ONLY')
            ->assertSee('Grafik Pendapatan')
            ->assertSee('Rp 123.456')
            ->assertSee('Status Member')
            ->assertSee('Akan Berakhir')
            ->assertSee('data: [1, 1, 1]', false);
    }

    public function test_owner_dashboard_filters_database_backed_charts_by_the_selected_period(): void
    {
        $this->travelTo('2026-11-10 10:00:00');
        $owner = User::factory()->create(['role' => 'owner']);
        $member = User::factory()->create(['role' => 'member']);
        $package = Package::create([
            'name' => 'Paket Basic',
            'duration' => 1,
            'price' => 150000,
        ]);
        Member::create([
            'user_id' => $member->id,
            'member_code' => 'GYM-7DAY',
            'created_at' => today()->subDays(2),
        ]);
        Payment::create([
            'invoice_number' => 'INV-7DAY',
            'user_id' => $member->id,
            'package_id' => $package->id,
            'amount' => 150000,
            'payment_date' => today()->subDays(2),
            'method' => 'Transfer',
            'status' => 'Lunas',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.dashboard', ['period' => '7d']))
            ->assertOk()
            ->assertSee('7 Hari Terakhir')
            ->assertSee('INV-7DAY')
            ->assertSee('Pertumbuhan Member & Pendaftaran')
            ->assertSee('Metode Pembayaran');
    }
}
