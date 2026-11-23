<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PackageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_read_package_list_and_details_without_mutation_controls(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $package = Package::create([
            'name' => 'Paket Premium',
            'description' => 'Akses penuh ke gym',
            'duration' => 3,
            'price' => 450000,
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.packages'))
            ->assertOk()
            ->assertSee(route('owner.packages.show', $package))
            ->assertDontSee(route('packages.edit', $package));

        $this->get(route('owner.packages.show', $package))
            ->assertOk()
            ->assertSee('Paket Premium')
            ->assertSee('read-only')
            ->assertDontSee(route('packages.edit', $package));

        $this->put(route('packages.update', $package), [
            'name' => 'Paket Diubah',
            'duration' => 6,
            'price' => 600000,
        ])->assertForbidden();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'name' => 'Paket Premium',
            'duration' => 3,
        ]);
    }

    public function test_package_search_filters_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Package::create([
            'name' => 'Paket Premium',
            'duration' => 3,
            'price' => 450000,
            'is_active' => true,
        ]);
        Package::create([
            'name' => 'Paket Basic',
            'duration' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('packages.index', ['search' => 'Premium']))
            ->assertOk()
            ->assertSee('Paket Premium')
            ->assertDontSee('Paket Basic');
    }

    public function test_package_search_duration_and_status_filters_are_combined(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Package::create([
            'name' => 'Paket Basic',
            'description' => 'Premium equipment access',
            'category' => 'Basic',
            'duration' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);
        Package::create([
            'name' => 'Paket Premium',
            'category' => 'Premium',
            'duration' => 6,
            'price' => 1000000,
            'is_active' => true,
        ]);
        Package::create([
            'name' => 'Paket Premium Nonaktif',
            'category' => 'Premium',
            'duration' => 1,
            'price' => 900000,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('packages.index', ['search' => 'Premium', 'duration' => 1, 'status' => 'active']))
            ->assertOk()
            ->assertSee('Paket Basic')
            ->assertDontSee('Paket Premium')
            ->assertSee('Apakah Anda yakin ingin menghapus paket membership ini?')
            ->assertSee('Ya, Hapus');
    }

    public function test_admin_can_create_a_package_with_category_and_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('packages.store'), [
                'name' => 'Paket VIP',
                'category' => 'Premium',
                'description' => 'Akses seluruh fasilitas gym',
                'facilities' => 'Area kardio dan beban',
                'duration' => 6,
                'price' => 1200000,
                'is_active' => '1',
            ])
            ->assertRedirect(route('packages.index'));

        $this->assertDatabaseHas('packages', [
            'name' => 'Paket VIP',
            'category' => 'Premium',
            'duration' => 6,
            'price' => 1200000,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_upload_and_replace_a_package_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $package = Package::create([
            'name' => 'Paket Basic',
            'duration' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('packages.update', $package), [
                'name' => 'Paket Basic',
                'category' => 'Basic',
                'duration' => 1,
                'price' => 150000,
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('basic.jpg'),
            ])
            ->assertRedirect(route('packages.index'));

        $package->refresh();
        $firstImage = $package->image_path;
        $this->assertNotNull($firstImage);
        Storage::disk('public')->assertExists($firstImage);

        $this->put(route('packages.update', $package), [
            'name' => 'Paket Basic',
            'category' => 'Basic',
            'duration' => 1,
            'price' => 150000,
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('basic-new.jpg'),
        ])->assertRedirect(route('packages.index'));

        $package->refresh();
        $this->assertNotSame($firstImage, $package->image_path);
        Storage::disk('public')->assertMissing($firstImage);
        Storage::disk('public')->assertExists($package->image_path);
    }

    public function test_admin_can_update_package_details_without_replacing_its_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $imagePath = UploadedFile::fake()->image('existing.jpg')->store('packages', 'public');
        $package = Package::create([
            'name' => 'Paket Basic',
            'category' => 'Basic',
            'duration' => 1,
            'price' => 150000,
            'is_active' => true,
            'image_path' => $imagePath,
        ]);

        $this->actingAs($admin)
            ->put(route('packages.update', $package), [
                'name' => 'Paket Tahunan',
                'category' => 'Annual',
                'description' => 'Akses gym selama satu tahun',
                'duration' => 12,
                'price' => 1400000,
                'is_active' => '1',
            ])
            ->assertRedirect(route('packages.index'));

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'name' => 'Paket Tahunan',
            'category' => 'Annual',
            'duration' => 12,
            'price' => 1400000,
        ]);
        Storage::disk('public')->assertExists($imagePath);
        $this->assertSame($imagePath, $package->refresh()->image_path);
    }

    public function test_admin_cannot_delete_a_package_used_by_a_registration(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $package = Package::create([
            'name' => 'Paket Terdaftar',
            'category' => 'Regular',
            'duration' => 3,
            'price' => 400000,
            'is_active' => true,
        ]);
        Registration::create([
            'user_id' => $registrant->id,
            'package_id' => $package->id,
            'registration_number' => 'REG-PAKET-'.$registrant->id,
            'phone' => '08123456789',
            'status' => 'Menunggu Verifikasi',
        ]);

        $this->actingAs($admin)
            ->from(route('packages.index'))
            ->delete(route('packages.destroy', $package))
            ->assertRedirect(route('packages.index'))
            ->assertSessionHas('error', 'Paket yang sudah digunakan tidak dapat dihapus. Nonaktifkan paket sebagai gantinya.');

        $this->assertModelExists($package);
    }

    public function test_seeded_packages_have_distinct_local_photos(): void
    {
        Package::create([
            'name' => 'Premium',
            'category' => 'Premium',
            'duration' => 6,
            'price' => 750000,
            'is_active' => true,
        ]);
        Package::create([
            'name' => 'Regular',
            'category' => 'Regular',
            'duration' => 3,
            'price' => 400000,
            'is_active' => true,
        ]);

        $this->seed();
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $packages = Package::query()->orderBy('name')->get();
        $imagePaths = $packages->pluck('image_path');

        $this->assertCount(8, $packages);
        $this->assertCount(8, $imagePaths->unique());
        $this->assertDatabaseHas('packages', ['name' => 'Premium', 'price' => 1000000]);
        $this->assertDatabaseHas('packages', ['name' => 'Regular', 'price' => 300000]);

        foreach ($imagePaths as $imagePath) {
            $this->assertFileExists(public_path($imagePath));
        }

        $response = $this->actingAs($registrant)->get(route('registrant.dashboard'))->assertOk();

        foreach ($packages as $package) {
            $response->assertSee($package->imageUrl(), false);
        }
    }

    public function test_admin_gym_settings_appear_on_registrant_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        Package::create([
            'name' => 'Paket Starter',
            'description' => 'Latihan dasar',
            'duration' => 1,
            'price' => 175000,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('settings.update'), [
                'gym_name' => 'SI-GYM Pusat',
                'gym_address' => 'Jl. Sehat No. 8',
                'gym_phone' => '021123456',
                'gym_email' => 'info@gym.id',
                'gym_hours' => 'Senin-Jumat 06.00-22.00',
                'gym_facilities' => "Treadmill\nSepeda statis\nRuang ganti",
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key' => 'gym_address',
            'value' => 'Jl. Sehat No. 8',
        ]);

        $this->actingAs($registrant)
            ->get(route('registrant.dashboard'))
            ->assertOk()
            ->assertSee('SI-GYM Pusat')
            ->assertSee('Jl. Sehat No. 8')
            ->assertSee('Senin-Jumat 06.00-22.00')
            ->assertSee('Nomor Kontak')
            ->assertSee('021123456')
            ->assertSee('Treadmill')
            ->assertSee('Sepeda statis')
            ->assertSee('Ruang ganti')
            ->assertSee('info@gym.id')
            ->assertSee('Paket Starter')
            ->assertSee('Latihan dasar')
            ->assertSee('Rp 175.000');
    }

    public function test_registrant_dashboard_displays_database_defaults_for_gym_information(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);

        $this->actingAs($registrant)
            ->get(route('registrant.dashboard'))
            ->assertOk()
            ->assertSee('Informasi Gym')
            ->assertSee('SI-GYM')
            ->assertSee('081329170566')
            ->assertSee("Jl KH Subahan Ma'kmun, Ds Luwungragi, Brebes")
            ->assertSee('08.00 – 22.00 WIB')
            ->assertSee('Alat fitness dan angkat beban')
            ->assertSee('Treadmill')
            ->assertSee('Sepeda statis')
            ->assertSee('Area latihan kardio')
            ->assertSee('Area angkat beban')
            ->assertSee('Ruang ganti')
            ->assertSee('data-lucide="dumbbell"', false)
            ->assertSee('data-lucide="map-pin"', false)
            ->assertSee('data-lucide="phone"', false)
            ->assertSee('data-lucide="clock-3"', false)
            ->assertSee('data-lucide="activity"', false);
    }

    public function test_owner_can_read_gym_settings_but_cannot_edit_them(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        Setting::updateOrCreate(['key' => 'gym_name'], ['value' => 'SI-GYM Pusat']);

        $this->actingAs($owner)
            ->get(route('owner.settings'))
            ->assertOk()
            ->assertSee('SI-GYM Pusat')
            ->assertSee('disabled', false)
            ->assertDontSee(route('settings.update'));

        $this->put(route('owner.settings'), [
            'gym_name' => 'Nama Diubah',
        ])->assertForbidden();

        $this->assertDatabaseHas('settings', [
            'key' => 'gym_name',
            'value' => 'SI-GYM Pusat',
        ]);
    }
}
