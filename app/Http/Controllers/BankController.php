<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('settings.banks.index', ['banks' => Bank::orderBy('bank_name')->paginate(15)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('settings.banks.form', ['bank' => null]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Bank::create($this->validateBank($request));

        return redirect()->route('settings.banks.index')->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bank $bank): View
    {
        return view('settings.banks.form', compact('bank'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bank $bank): RedirectResponse
    {
        $bank->update($this->validateBank($request, $bank));

        return redirect()->route('settings.banks.index')->with('success', 'Rekening bank berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank): RedirectResponse
    {
        abort_if($bank->payments()->exists(), 422, 'Rekening yang sudah digunakan pada pembayaran tidak dapat dihapus.');
        $bank->delete();

        return back()->with('success', 'Rekening bank berhasil dihapus.');
    }

    private function validateBank(Request $request, ?Bank $bank = null): array
    {
        return $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_code' => ['required', 'string', 'max:20', Rule::unique('banks', 'bank_code')->ignore($bank)],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
