<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index', ['settings' => Setting::orderBy('key')->get()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gym_name' => ['required', 'string', 'max:255'],
            'gym_address' => ['nullable', 'string', 'max:2000'],
            'gym_phone' => ['nullable', 'string', 'max:50'],
            'gym_email' => ['nullable', 'email', 'max:255'],
            'gym_hours' => ['nullable', 'string', 'max:500'],
            'gym_facilities' => ['nullable', 'string', 'max:2000'],
        ]);
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
