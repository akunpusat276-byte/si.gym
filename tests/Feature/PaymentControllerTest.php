<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_sees_only_their_own_payments_and_cannot_open_another_payment(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $otherMember = User::factory()->create(['role' => 'member']);
        $package = $this->createPackage();
        $ownPayment = $this->createPayment($member, $package, 'INV-MEMBER-001');
        $otherPayment = $this->createPayment($otherMember, $package, 'INV-OTHER-001');

        $this->actingAs($member)
            ->get(route('member.payments'))
            ->assertOk()
            ->assertSee('INV-MEMBER-001')
            ->assertDontSee('INV-OTHER-001');

        $this->get(route('member.payments.show', $otherPayment))->assertNotFound();
        $this->get(route('member.payments.show', $ownPayment))->assertOk();
    }

    public function test_owner_can_read_all_payments_but_cannot_update_them(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $member = User::factory()->create(['role' => 'member']);
        $payment = $this->createPayment($member, $this->createPackage(), 'INV-OWNER-001');

        $this->actingAs($owner)
            ->get(route('owner.payments'))
            ->assertOk()
            ->assertSee('INV-OWNER-001')
            ->assertSee(route('owner.payment.show', $payment))
            ->assertDontSee(route('payments.edit', $payment));

        $this->put(route('payments.update', $payment), [
            'status' => 'Lunas',
        ])->assertForbidden();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Menunggu',
        ]);
    }

    public function test_payment_index_filters_by_status_and_method(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        $package = $this->createPackage();
        $transfer = $this->createPayment($member, $package, 'INV-FILTER-TRANSFER');
        $transfer->update(['method' => 'Transfer', 'status' => 'Menunggu Verifikasi']);
        $this->createPayment($member, $package, 'INV-FILTER-CASH');

        $this->actingAs($admin)
            ->get(route('payments.index', ['status' => 'Menunggu Verifikasi', 'method' => 'Transfer']))
            ->assertOk()
            ->assertSee('INV-FILTER-TRANSFER')
            ->assertDontSee('INV-FILTER-CASH');
    }

    public function test_admin_cannot_mark_a_payment_paid_without_using_verification(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $this->createPayment(User::factory()->create(['role' => 'member']), $this->createPackage(), 'INV-NO-BYPASS');

        $this->actingAs($admin)
            ->put(route('payments.update', $payment), ['status' => 'Lunas'])
            ->assertSessionHasErrors(['status']);

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'Menunggu']);
    }

    public function test_registrant_can_choose_cash_and_admin_verification_records_the_payment_date(): void
    {
        Storage::fake('local');
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = $this->createPackage();
        $payment = $this->createPayment($registrant, $package, 'INV-CASH-001');
        Storage::disk('local')->put('payment-proofs/rejected.jpg', 'proof');
        $payment->update([
            'method' => 'Transfer',
            'status' => 'Ditolak',
            'payment_proof' => 'payment-proofs/rejected.jpg',
            'bank_name' => 'Bank Contoh',
        ]);

        $this->actingAs($registrant)
            ->get(route('registrant.payments.checkout', $payment))
            ->assertOk()
            ->assertSee('Pilih Pembayaran Tunai');

        $this->post(route('registrant.payments.cash', $payment))
            ->assertRedirect(route('registrant.payments'));

        $payment->refresh();
        $this->assertSame('Cash', $payment->method);
        $this->assertSame('Menunggu Pembayaran', $payment->status);
        $this->assertNull($payment->bank_name);
        $this->assertNull($payment->payment_proof);
        Storage::disk('local')->assertMissing('payment-proofs/rejected.jpg');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->patch(route('payments.verify', $payment))
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Lunas',
            'verified_by' => $admin->id,
        ]);
        $this->assertSame(today()->toDateString(), $payment->fresh()->payment_date->toDateString());
        $this->assertDatabaseHas('memberships', ['payment_id' => $payment->id]);
    }

    public function test_payment_method_cannot_be_changed_while_transfer_is_under_verification(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $payment = $this->createPayment($registrant, $this->createPackage(), 'INV-VERIFY-001');
        $payment->update(['method' => 'Transfer', 'status' => 'Menunggu Verifikasi']);

        $this->actingAs($registrant)
            ->post(route('registrant.payments.cash', $payment))
            ->assertUnprocessable();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'method' => 'Transfer',
            'status' => 'Menunggu Verifikasi',
        ]);
    }

    public function test_registrant_cannot_change_bank_while_transfer_is_under_verification(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $payment = $this->createPayment($registrant, $this->createPackage(), 'INV-VERIFY-BANK');
        $payment->update(['method' => 'Transfer', 'status' => 'Menunggu Verifikasi']);

        $this->actingAs($registrant)
            ->post(route('registrant.payments.bank', $payment), [])
            ->assertUnprocessable();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'method' => 'Transfer',
            'status' => 'Menunggu Verifikasi',
        ]);
    }

    public function test_admin_approval_creates_a_pending_invoice_and_verified_payment_activates_membership(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = $this->createPackage();
        $registration = Registration::create([
            'user_id' => $registrant->id,
            'package_id' => $package->id,
            'registration_number' => 'REG-20261110-001',
            'phone' => '08123456789',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01',
            'address' => 'Jl. Sehat No. 1',
            'status' => 'Menunggu Verifikasi',
        ]);

        $this->actingAs($admin)
            ->patch(route('registrations.status', $registration), [
                'status' => 'Disetujui',
                'admin_note' => 'Data terverifikasi',
            ])
            ->assertRedirect();

        $registration->refresh();
        $payment = Payment::where('registration_id', $registration->id)->firstOrFail();
        $this->assertSame('Menunggu Pembayaran', $registration->status);
        $this->assertSame('Menunggu Pembayaran', $payment->status);
        $this->assertSame('150000.00', $payment->amount);

        Storage::disk('local')->put('payment-proofs/bukti.jpg', 'bukti');
        $payment->update([
            'status' => 'Menunggu Verifikasi',
            'payment_proof' => 'payment-proofs/bukti.jpg',
        ]);

        $this->patch(route('payments.verify', $payment))
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Lunas',
            'verified_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'status' => 'Selesai',
        ]);
        $this->assertSame('member', $registrant->fresh()->role);
        $member = Member::where('user_id', $registrant->id)->firstOrFail();
        $this->assertMatchesRegularExpression('/^MBR-\d{8}-\d+$/', $member->member_code);
        $membership = Membership::where('payment_id', $payment->id)->firstOrFail();
        $this->assertSame($member->id, $membership->member_id);
        $this->assertSame($package->id, $membership->package_id);
        $this->assertSame(today()->toDateString(), $membership->start_date->toDateString());
        $this->assertSame(today()->addMonth()->toDateString(), $membership->end_date->toDateString());
        $this->assertSame(1, Membership::where('payment_id', $payment->id)->count());
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

    private function createPayment(User $user, Package $package, string $invoice): Payment
    {
        return Payment::create([
            'invoice_number' => $invoice,
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => $package->price,
            'method' => 'Cash',
            'status' => 'Menunggu',
        ]);
    }
}
