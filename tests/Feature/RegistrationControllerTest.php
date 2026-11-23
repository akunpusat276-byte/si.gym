<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrant_can_create_a_registration_for_an_active_package(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = $this->createPackage();

        $response = $this->actingAs($registrant)->post(route('registrations.store'), [
            'package_id' => $package->id,
            'phone' => '08123456789',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01',
            'address' => 'Jl. Sehat No. 1',
            'notes' => 'Pendaftaran baru',
        ]);

        $response->assertRedirect(route('registrant.dashboard'));
        $this->assertDatabaseHas('registrations', [
            'user_id' => $registrant->id,
            'package_id' => $package->id,
            'status' => 'Menunggu Verifikasi',
        ]);
    }

    public function test_registrant_cannot_select_an_inactive_package(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = $this->createPackage(['is_active' => false]);

        $response = $this->actingAs($registrant)
            ->from(route('registrations.create'))
            ->post(route('registrations.store'), [
                'package_id' => $package->id,
                'phone' => '08123456789',
            ]);

        $response->assertRedirectBackWithErrors(['package_id']);
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_admin_can_create_a_registration_for_a_pendaftar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = $this->createPackage();

        $response = $this->actingAs($admin)->post(route('admin.registrations.store'), [
            'user_id' => $registrant->id,
            'package_id' => $package->id,
            'phone' => '08123456789',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01',
            'address' => 'Jl. Sehat No. 1',
            'notes' => 'Pendaftaran baru',
        ]);

        $response->assertRedirect(route('registrations.index'));
        $this->assertDatabaseHas('registrations', [
            'user_id' => $registrant->id,
            'package_id' => $package->id,
            'status' => 'Menunggu Verifikasi',
            'phone' => '08123456789',
        ]);
    }

    public function test_admin_create_form_only_lists_eligible_pendaftar_and_active_packages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $eligibleRegistrant = User::factory()->create(['role' => 'pendaftar', 'name' => 'Calon Baru']);
        $activeRegistrant = User::factory()->create(['role' => 'pendaftar', 'name' => 'Calon Aktif']);
        $this->createRegistration($activeRegistrant, $this->createPackage());
        User::factory()->create(['role' => 'member', 'name' => 'Sudah Member']);
        $activePackage = $this->createPackage(['name' => 'Paket Aktif']);
        $this->createPackage(['name' => 'Paket Nonaktif', 'is_active' => false]);

        $this->actingAs($admin)
            ->get(route('admin.registrations.create'))
            ->assertOk()
            ->assertSee('Calon Baru')
            ->assertDontSee('Calon Aktif')
            ->assertDontSee('Sudah Member')
            ->assertSee('Paket Aktif')
            ->assertDontSee('Paket Nonaktif')
            ->assertSee('value="'.$eligibleRegistrant->id.'"', false)
            ->assertSee('value="'.$activePackage->id.'"', false);
    }

    public function test_admin_cannot_create_a_registration_for_a_non_pendaftar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        $package = $this->createPackage();

        $response = $this->actingAs($admin)
            ->from(route('admin.registrations.create'))
            ->post(route('admin.registrations.store'), [
                'user_id' => $member->id,
                'package_id' => $package->id,
                'phone' => '08123456789',
            ]);

        $response->assertRedirectBackWithErrors(['user_id']);
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_admin_cannot_create_a_second_active_registration_for_a_pendaftar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = $this->createPackage();
        $this->createRegistration($registrant, $package);

        $response = $this->actingAs($admin)->post(route('admin.registrations.store'), [
            'user_id' => $registrant->id,
            'package_id' => $package->id,
            'phone' => '08123456789',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_non_admin_cannot_create_a_registration_through_admin_routes(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = $this->createPackage();

        $this->actingAs($registrant)
            ->post(route('admin.registrations.store'), [
                'user_id' => $registrant->id,
                'package_id' => $package->id,
                'phone' => '08123456789',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_registrant_cannot_create_another_registration_while_one_is_pending_payment(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = $this->createPackage();
        $this->createRegistration($registrant, $package, ['status' => 'Menunggu Pembayaran']);
        $anotherPackage = $this->createPackage(['name' => 'Paket Tahunan']);

        $response = $this->actingAs($registrant)->post(route('registrations.store'), [
            'package_id' => $anotherPackage->id,
            'phone' => '08123456789',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_registrant_cannot_view_update_or_cancel_another_users_registration(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $otherRegistrant = User::factory()->create(['role' => 'pendaftar']);
        $registration = $this->createRegistration($otherRegistrant, $this->createPackage());

        $this->actingAs($registrant)
            ->get(route('registrations.show.me', $registration))
            ->assertNotFound();

        $this->put(route('registrations.update.me', $registration), [
            'package_id' => $registration->package_id,
            'phone' => '08999999999',
        ])->assertNotFound();

        $this->delete(route('registrations.cancel', $registration))->assertNotFound();
        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'phone' => $registration->phone,
        ]);
    }

    public function test_registrant_can_update_their_pending_registration_and_view_their_history(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $registration = $this->createRegistration($registrant, $this->createPackage());

        $this->actingAs($registrant)
            ->put(route('registrations.update.me', $registration), [
                'package_id' => $registration->package_id,
                'phone' => '08999999999',
                'gender' => 'Perempuan',
            ])
            ->assertRedirect(route('registrations.status.me'));

        $this->get(route('registrations.status.me'))
            ->assertOk()
            ->assertSee($registration->registration_number)
            ->assertSee('Riwayat Pendaftaran');

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'phone' => '08999999999',
            'gender' => 'Perempuan',
        ]);
    }

    public function test_registrant_dashboard_lists_active_membership_packages_with_detail_links(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $activePackage = $this->createPackage([
            'name' => 'Paket Basic Aktif',
            'description' => 'Akses gym bulanan',
        ]);
        $this->createPackage(['name' => 'Paket Nonaktif', 'is_active' => false]);

        $this->actingAs($registrant)
            ->get(route('registrant.dashboard'))
            ->assertOk()
            ->assertSee('Selamat Datang,')
            ->assertSee('Paket Membership')
            ->assertSee('Paket Basic Aktif')
            ->assertSee('Akses gym bulanan')
            ->assertSee('1 Bulan')
            ->assertSee('Rp 150.000')
            ->assertSee(route('registrant.packages.show', $activePackage))
            ->assertSee(route('registrant.dashboard').'#informasi-gym')
            ->assertDontSee('Paket Nonaktif');
    }

    public function test_registrant_can_view_active_package_details_and_select_it_for_registration(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = $this->createPackage([
            'name' => 'Paket Basic',
            'description' => 'Akses gym bulanan',
            'facilities' => 'Area kardio dan beban',
        ]);

        $this->actingAs($registrant)
            ->get(route('registrant.packages.show', $package))
            ->assertOk()
            ->assertSee('Detail Paket Membership')
            ->assertSee('Paket Basic')
            ->assertSee('Area kardio dan beban')
            ->assertSee(route('registrations.create', ['package' => $package->id]));

        $this->get(route('registrations.create', ['package' => $package->id]))
            ->assertOk()
            ->assertSee('Paket Basic telah dipilih dari katalog.')
            ->assertSee('value="'.$package->id.'" selected', false);
    }

    public function test_registrant_cannot_view_an_inactive_package_detail(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = $this->createPackage(['is_active' => false]);

        $this->actingAs($registrant)
            ->get(route('registrant.packages.show', $package))
            ->assertNotFound();

        $this->get(route('registrations.create', ['package' => $package->id]))
            ->assertNotFound();
    }

    public function test_registrant_without_a_registration_can_start_from_the_dashboard(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $this->createPackage(['name' => 'Paket Basic']);

        $this->actingAs($registrant)
            ->get(route('registrant.dashboard'))
            ->assertOk()
            ->assertSee('Paket Basic')
            ->assertSee('Detail')
            ->assertSee(route('registrant.packages.show', Package::where('name', 'Paket Basic')->firstOrFail()));
    }

    public function test_registrant_cannot_edit_registration_after_approval(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $registration = $this->createRegistration(
            $registrant,
            $this->createPackage(),
            ['status' => 'Disetujui'],
        );

        $this->actingAs($registrant)
            ->get(route('registrations.edit.me', $registration))
            ->assertUnprocessable();

        $this->put(route('registrations.update.me', $registration), [
            'package_id' => $registration->package_id,
            'phone' => '08999999999',
        ])->assertUnprocessable();

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'phone' => $registration->phone,
            'status' => 'Disetujui',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPackage(array $attributes = []): Package
    {
        return Package::create([
            'name' => 'Paket Bulanan',
            'duration' => 1,
            'price' => 150000,
            'is_active' => true,
            ...$attributes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createRegistration(User $user, Package $package, array $attributes = []): Registration
    {
        return Registration::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'registration_number' => 'REG-'.$user->id.'-'.$package->id,
            'phone' => '08123456789',
            'status' => 'Menunggu',
            ...$attributes,
        ]);
    }
}
