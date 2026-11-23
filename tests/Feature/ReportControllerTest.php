<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_payment_reports_and_download_excel_compatible_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'member', 'name' => 'Budi Santoso']);
        $package = Package::create([
            'name' => 'Paket Basic',
            'duration' => 1,
            'price' => 150000,
        ]);
        Payment::create([
            'invoice_number' => 'INV-REPORT-PAID',
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => 150000,
            'payment_date' => today(),
            'method' => 'Transfer',
            'status' => 'Lunas',
        ]);
        Payment::create([
            'invoice_number' => 'INV-REPORT-PENDING',
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => 150000,
            'method' => 'Cash',
            'status' => 'Menunggu Pembayaran',
        ]);

        $this->actingAs($admin)
            ->get(route('reports.index', ['category' => 'revenue', 'search' => 'Budi']))
            ->assertOk()
            ->assertSee('INV-REPORT-PAID')
            ->assertDontSee('INV-REPORT-PENDING')
            ->assertSee('Rp 150.000');

        $this->get(route('reports.export', ['category' => 'revenue']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_owner_can_read_reports_but_cannot_mutate_them(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->get(route('owner.reports'))
            ->assertOk()
            ->assertSee('Laporan Gym');
    }
}
