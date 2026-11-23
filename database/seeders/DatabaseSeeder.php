<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            ['Admin SI-GYM', 'admin@sigym.test', 'admin'],
            ['Owner SI-GYM', 'owner@sigym.test', 'owner'],
            ['Member SI-GYM', 'member@sigym.test', 'member'],
            ['Pendaftar SI-GYM', 'pendaftar@sigym.test', 'pendaftar'],
        ] as [$name, $email, $role]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => $role,
            ]);
        }

        $legacyPackagePrices = [
            'Premium' => 750000,
            'Regular' => 400000,
        ];

        foreach ([
            ['Annual', 'Annual', 'Akses gym selama 12 bulan.', 12, 1400000, 'images/packages/annual.jpg'],
            ['Basic', 'Basic', 'Akses gym selama 1 bulan.', 1, 150000, 'images/packages/basic.jpg'],
            ['Paket 1 Bulan', 'Basic', 'Paket membership untuk latihan selama 1 bulan.', 1, 150000, 'images/packages/basic-month.jpg'],
            ['Paket 12 Bulan', 'Annual', 'Paket membership untuk latihan selama 12 bulan.', 12, 1400000, 'images/packages/annual-month.jpg'],
            ['Paket 3 Bulan', 'Regular', 'Paket membership untuk latihan selama 3 bulan.', 3, 400000, 'images/packages/three-month.jpg'],
            ['Paket 6 Bulan', 'Premium', 'Paket membership untuk latihan selama 6 bulan.', 6, 750000, 'images/packages/six-month.jpg'],
            ['Premium', 'Premium', 'Akses gym selama 6 bulan.', 6, 1000000, 'images/packages/premium.jpg'],
            ['Regular', 'Regular', 'Akses gym selama 3 bulan.', 3, 300000, 'images/packages/regular.jpg'],
        ] as [$name, $category, $description, $duration, $price, $imagePath]) {
            $package = Package::firstOrCreate(['name' => $name], [
                'category' => $category,
                'description' => $description,
                'duration' => $duration,
                'price' => $price,
                'image_path' => $imagePath,
                'is_active' => true,
            ]);

            if (! $package->category || ! $package->image_path || ! $package->description) {
                $package->forceFill([
                    'category' => $package->category ?: $category,
                    'image_path' => $package->image_path ?: $imagePath,
                    'description' => $package->description ?: $description,
                ])->save();
            }

            if (isset($legacyPackagePrices[$name]) && (float) $package->price === (float) $legacyPackagePrices[$name]) {
                $package->update(['price' => $price]);
            }
        }

        $memberUser = User::where('email', 'member@sigym.test')->first();
        Member::updateOrCreate(['user_id' => $memberUser->id], [
            'member_code' => 'MBR-SEED-0001',
            'phone' => '081200000001',
        ]);

        $member = Member::where('user_id', $memberUser->id)->first();
        $package = Package::where('name', 'Regular')->first();
        Membership::updateOrCreate(
            ['member_id' => $member->id, 'package_id' => $package->id],
            [
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonths(2),
                'duration' => $package->duration,
                'status' => 'Aktif',
            ],
        );
    }
}
