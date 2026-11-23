<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrantDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $gymInfo = Setting::query()->pluck('value', 'key');
        $gymFacilities = preg_split('/[\r\n,]+/', $gymInfo['gym_facilities'] ?? '', -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $gymFacilities = array_values(array_filter(
            array_map(fn (string $facility): string => trim(ltrim($facility, "- \t")), $gymFacilities),
            fn (string $facility): bool => $facility !== '',
        ));

        return view('dashboard.registrant', [
            'packages' => Package::query()->where('is_active', true)->orderBy('name')->get(),
            'gymInfo' => $gymInfo,
            'gymFacilities' => $gymFacilities,
            'gymPhoneLink' => preg_replace('/[^0-9+]/', '', $gymInfo['gym_phone'] ?? ''),
        ]);
    }

    public function showPackage(Request $request, Package $package): View
    {
        abort_unless($package->is_active, 404);

        return view('dashboard.registrant-package', [
            'package' => $package,
            'hasActiveRegistration' => $request->user()->registrations()
                ->whereIn('status', ['Menunggu', 'Menunggu Verifikasi', 'Disetujui', 'Menunggu Pembayaran'])
                ->exists(),
        ]);
    }
}
