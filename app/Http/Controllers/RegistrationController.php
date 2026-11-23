<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['Menunggu', 'Menunggu Verifikasi', 'Disetujui', 'Ditolak', 'Menunggu Pembayaran', 'Pembayaran Berhasil', 'Selesai'])],
        ]);
        $search = trim($validated['search'] ?? '');

        $registrations = Registration::with(['user', 'package'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('registration_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"))
                        ->orWhereHas('package', fn (Builder $packageQuery) => $packageQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($validated['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('registrations.index', compact('registrations'));
    }

    public function create(Request $request): View
    {
        $validated = $request->validate([
            'package' => ['sometimes', 'integer', 'exists:packages,id'],
        ]);
        $packages = Package::where('is_active', true)->orderBy('name')->get();
        $selectedPackage = isset($validated['package'])
            ? $packages->firstWhere('id', $validated['package'])
            : null;

        abort_if(isset($validated['package']) && ! $selectedPackage, 404);

        return view('registrations.create', [
            'packages' => $packages,
            'selectedPackage' => $selectedPackage,
            'user' => $request->user(),
        ]);
    }

    public function adminCreate(): View
    {
        $users = User::where('role', 'pendaftar')
            ->whereDoesntHave('registrations', fn (Builder $query) => $query->whereIn('status', ['Menunggu', 'Menunggu Verifikasi', 'Disetujui', 'Menunggu Pembayaran']))
            ->orderBy('name')
            ->get();

        return view('registrations.admin-create', [
            'packages' => Package::where('is_active', true)->orderBy('name')->get(),
            'users' => $users,
        ]);
    }

    public function adminStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'pendaftar')],
            'package_id' => ['required', Rule::exists('packages', 'id')->where('is_active', true)],
            'phone' => ['required', 'string', 'max:30'],
            'gender' => ['nullable', 'in:Laki-laki,Perempuan'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data): void {
            $user = User::whereKey($data['user_id'])->lockForUpdate()->firstOrFail();
            abort_unless($user->role === 'pendaftar', 422, 'Akun yang dipilih bukan pendaftar.');
            abort_if(
                $user->registrations()->whereIn('status', ['Menunggu', 'Menunggu Verifikasi', 'Disetujui', 'Menunggu Pembayaran'])->exists(),
                422,
                'Pendaftar masih memiliki pendaftaran aktif.',
            );

            $sequence = Registration::whereDate('created_at', today())->count() + 1;
            $user->registrations()->create([
                ...$data,
                'registration_number' => 'REG-'.now()->format('Ymd').'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'status' => 'Menunggu Verifikasi',
            ]);
        });

        return redirect()->route('registrations.index')->with('success', 'Pendaftaran calon member berhasil ditambahkan.');
    }

    public function status(Request $request): View
    {
        $registrations = Registration::with('package')
            ->withCount('payments')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('registrations.index', compact('registrations'));
    }

    public function applicantShow(Request $request, Registration $registration): View
    {
        abort_unless($registration->user_id === $request->user()->id, 404);

        return view('registrations.show', ['registration' => $registration->load(['user', 'package'])->loadCount('payments')]);
    }

    public function applicantEdit(Request $request, Registration $registration): View
    {
        abort_unless($registration->user_id === $request->user()->id, 404);
        abort_unless(
            in_array($registration->status, ['Menunggu', 'Menunggu Verifikasi'], true)
            && ! $registration->payments()->exists(),
            422,
            'Pendaftaran yang sudah diproses tidak dapat diubah.',
        );

        return view('registrations.edit', [
            'registration' => $registration->load(['user', 'package']),
            'packages' => Package::where('is_active', true)->get(),
            'applicantMode' => true,
        ]);
    }

    public function applicantUpdate(Request $request, Registration $registration): RedirectResponse
    {
        abort_unless($registration->user_id === $request->user()->id, 404);
        abort_unless(
            in_array($registration->status, ['Menunggu', 'Menunggu Verifikasi'], true)
            && ! $registration->payments()->exists(),
            422,
            'Pendaftaran yang sudah diproses tidak dapat diubah.',
        );

        $data = $request->validate([
            'package_id' => ['required', Rule::exists('packages', 'id')->where('is_active', true)],
            'phone' => ['required', 'string', 'max:30'],
            'gender' => ['nullable', 'in:Laki-laki,Perempuan'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
        $registration->update($data);

        return redirect()->route('registrations.status.me')->with('success', 'Pendaftaran berhasil diperbarui.');
    }

    public function cancel(Request $request, Registration $registration): RedirectResponse
    {
        abort_unless($registration->user_id === $request->user()->id, 404);
        abort_unless(in_array($registration->status, ['Menunggu', 'Menunggu Verifikasi'], true), 422, 'Pendaftaran yang sudah diproses tidak dapat dibatalkan.');
        abort_if($registration->payments()->exists(), 422, 'Pendaftaran yang memiliki pembayaran tidak dapat dibatalkan.');

        $registration->delete();

        return redirect()->route('registrations.status.me')->with('success', 'Pendaftaran berhasil dibatalkan.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['required', Rule::exists('packages', 'id')->where('is_active', true)],
            'phone' => ['required', 'string', 'max:30'],
            'gender' => ['nullable', 'in:Laki-laki,Perempuan'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
        $existing = Registration::where('user_id', $request->user()->id)
            ->whereIn('status', ['Menunggu', 'Menunggu Verifikasi', 'Disetujui', 'Menunggu Pembayaran'])
            ->exists();
        abort_if($existing, 422, 'Anda masih memiliki pendaftaran aktif.');
        $sequence = Registration::whereDate('created_at', today())->count() + 1;
        Registration::create([
            ...$data,
            'user_id' => $request->user()->id,
            'registration_number' => 'REG-'.now()->format('Ymd').'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            'status' => 'Menunggu Verifikasi',
        ]);

        return redirect()->route('registrant.dashboard')->with('success', 'Pendaftaran berhasil dikirim.');
    }

    public function show(Registration $registration): View
    {
        $registration->load(['user', 'package', 'payments.package']);

        return view('registrations.show', compact('registration'));
    }

    public function edit(Registration $registration): View
    {
        return view('registrations.edit', [
            'registration' => $registration->load(['user', 'package']),
            'packages' => Package::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, Registration $registration): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'phone' => ['required', 'string', 'max:30'],
            'gender' => ['nullable', 'in:Laki-laki,Perempuan'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
        $registration->update($data);

        return redirect()->route('registrations.show', $registration)->with('success', 'Pendaftaran berhasil diperbarui.');
    }

    public function destroy(Registration $registration): RedirectResponse
    {
        abort_if($registration->payments()->exists(), 422, 'Pendaftaran yang memiliki pembayaran tidak dapat dihapus.');
        $registration->delete();

        return redirect()->route('registrations.index')->with('success', 'Pendaftaran berhasil dihapus.');
    }

    public function updateStatus(Request $request, Registration $registration): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:Menunggu,Menunggu Verifikasi,Disetujui,Ditolak'],
            'admin_note' => ['nullable', 'string'],
        ]);
        DB::transaction(function () use ($registration, $data): void {
            $registration = Registration::query()->lockForUpdate()->findOrFail($registration->id);
            abort_unless(
                in_array($registration->status, ['Menunggu', 'Menunggu Verifikasi'], true),
                422,
                'Pendaftaran yang sudah diproses tidak dapat diverifikasi ulang.',
            );

            if ($data['status'] === 'Disetujui') {
                $registration->update([
                    'status' => 'Menunggu Pembayaran',
                    'admin_note' => $data['admin_note'] ?? null,
                    'verified_at' => now(),
                ]);
                $registration->user()->update([
                    'phone' => $registration->phone,
                    'gender' => $registration->gender,
                    'birth_date' => $registration->birth_date,
                    'address' => $registration->address,
                ]);
                Payment::create([
                    'invoice_number' => 'INV-'.now()->format('YmdHis').'-'.random_int(100, 999),
                    'user_id' => $registration->user_id,
                    'registration_id' => $registration->id,
                    'package_id' => $registration->package_id,
                    'amount' => $registration->package->price,
                    'method' => 'Transfer',
                    'status' => 'Menunggu Pembayaran',
                ]);

                return;
            }

            $registration->update([
                'status' => $data['status'],
                'admin_note' => $data['admin_note'] ?? null,
                'verified_at' => now(),
            ]);
        });

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }
}
