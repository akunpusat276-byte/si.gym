<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_recent_workflow_data_and_seven_day_member_stats(): void
    {
        $this->travelTo('2026-11-10 10:00:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $registrant = User::factory()->create(['role' => 'pendaftar', 'name' => 'Calon Member Baru']);
        $memberUser = User::factory()->create(['role' => 'member', 'name' => 'Budi Santoso']);
        Member::create([
            'user_id' => $memberUser->id,
            'member_code' => 'GYM-00123',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $package = Package::create([
            'name' => 'Paket Basic',
            'duration' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);
        Registration::create([
            'user_id' => $registrant->id,
            'package_id' => $package->id,
            'registration_number' => 'REG-20261110-001',
            'phone' => '08123456789',
            'status' => 'Menunggu Verifikasi',
        ]);
        Payment::create([
            'invoice_number' => 'INV-ADMIN-001',
            'user_id' => $memberUser->id,
            'package_id' => $package->id,
            'amount' => 150000,
            'payment_date' => today(),
            'method' => 'Transfer Bank',
            'status' => 'Lunas',
        ]);
        Payment::create([
            'invoice_number' => 'INV-ADMIN-OLD',
            'user_id' => $memberUser->id,
            'package_id' => $package->id,
            'amount' => 500000,
            'payment_date' => today()->subMonth(),
            'method' => 'Transfer',
            'status' => 'Lunas',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Statistik Member')
            ->assertSee('7 Hari Terakhir')
            ->assertSee('Data Terbaru')
            ->assertSee('Calon Member Baru')
            ->assertSee('INV-ADMIN-001')
            ->assertSee('Rp 650.000')
            ->assertSee('Notifikasi Pendaftaran')
            ->assertSee('Notifikasi Pembayaran')
            ->assertSee('Membership Akan Berakhir')
            ->assertSee('Membership');
    }
}
