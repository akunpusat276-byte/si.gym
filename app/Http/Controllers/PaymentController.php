<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:Menunggu,Menunggu Pembayaran,Menunggu Verifikasi,Lunas,Ditolak,Dibatalkan'],
            'method' => ['nullable', 'in:Cash,Transfer,QRIS'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        $search = trim($validated['search'] ?? '');

        $query = Payment::with(['user', 'package'])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('package', fn (Builder $packageQuery) => $packageQuery->where('name', 'like', "%{$search}%"));
            }))
            ->when($validated['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($validated['method'] ?? null, fn (Builder $query, string $method) => $query->where('method', $method))
            ->when($validated['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($validated['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->when($validated['month'] ?? null, fn (Builder $query, int $month) => $query->whereMonth('created_at', $month))
            ->when($validated['year'] ?? null, fn (Builder $query, int $year) => $query->whereYear('created_at', $year))
            ->latest();
        if (in_array($request->user()->role, ['member', 'pendaftar'], true)) {
            $query->whereBelongsTo($request->user());
        }

        return view('payments.index', ['payments' => $query->paginate(15)->withQueryString()]);
    }

    public function create(): View
    {
        return view('payments.create', [
            'users' => User::whereIn('role', ['pendaftar', 'member'])->get(),
            'registrations' => Registration::with(['user', 'package'])->where('status', 'Disetujui')->get(),
            'packages' => Package::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'registration_id' => ['nullable', 'exists:registrations,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'method' => ['required', 'in:Cash,Transfer,QRIS'],
            'status' => ['required', 'in:Menunggu,Menunggu Pembayaran,Menunggu Verifikasi,Ditolak,Dibatalkan'],
            'notes' => ['nullable', 'string'],
        ]);
        $package = Package::findOrFail($data['package_id']);
        abort_unless((float) $data['amount'] === (float) $package->price, 422, 'Nominal pembayaran tidak sesuai harga paket.');
        if ($data['registration_id']) {
            $registration = Registration::findOrFail($data['registration_id']);
            abort_unless($registration->user_id === $data['user_id'], 422, 'Pendaftaran tidak sesuai dengan pengguna.');
            abort_unless($registration->package_id === $data['package_id'], 422, 'Paket pembayaran tidak sesuai dengan pendaftaran.');
            abort_unless($registration->status === 'Disetujui', 422, 'Pendaftaran belum disetujui.');
        }
        DB::transaction(function () use ($data) {
            $payment = Payment::create([
                ...$data,
                'invoice_number' => 'INV-'.now()->format('YmdHis').'-'.random_int(100, 999),
            ]);
        });

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil disimpan.');
    }

    public function show(Request $request, Payment $payment): View
    {
        abort_unless(
            in_array($request->user()->role, ['admin', 'owner'], true)
            || $payment->user_id === $request->user()->id,
            404,
        );

        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment): View
    {
        abort_if($payment->status === 'Lunas', 422, 'Pembayaran lunas tidak dapat diubah.');

        return view('payments.edit', [
            'payment' => $payment->load(['user', 'package', 'registration']),
            'users' => User::whereIn('role', ['pendaftar', 'member'])->orderBy('name')->get(),
            'registrations' => Registration::with(['user', 'package'])->where('status', 'Disetujui')->get(),
            'packages' => Package::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        abort_if($payment->status === 'Lunas', 422, 'Pembayaran lunas tidak dapat diubah.');
        $isStatusOnly = $request->has('status') && ! $request->has('user_id');
        $data = $request->validate($isStatusOnly ? [
            'status' => ['required', 'in:Menunggu,Menunggu Pembayaran,Menunggu Verifikasi,Ditolak,Dibatalkan'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ] : [
            'user_id' => ['required', 'exists:users,id'],
            'registration_id' => ['nullable', 'exists:registrations,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'method' => ['required', 'in:Cash,Transfer,QRIS'],
            'status' => ['required', 'in:Menunggu,Menunggu Pembayaran,Menunggu Verifikasi,Ditolak,Dibatalkan'],
            'notes' => ['nullable', 'string'],
        ]);
        abort_if(($data['status'] ?? null) === 'Lunas', 422, 'Pembayaran hanya dapat dilunasi melalui proses verifikasi.');
        if (! $isStatusOnly) {
            $package = Package::findOrFail($data['package_id']);
            abort_unless((float) $data['amount'] === (float) $package->price, 422, 'Nominal pembayaran tidak sesuai harga paket.');
        }
        DB::transaction(function () use ($payment, $data) {
            $payment->update($data);
        });

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        abort_if($payment->membership()->exists(), 422, 'Pembayaran yang sudah mengaktifkan membership tidak dapat dihapus.');
        $payment->delete();

        return back()->with('success', 'Pembayaran berhasil dihapus.');
    }

    private function activatePaidPayment(Payment $payment): void
    {
        $user = $payment->user()->lockForUpdate()->firstOrFail();
        $member = Member::firstOrCreate(
            ['user_id' => $user->id],
            [
                'member_code' => 'MBR-'.now()->format('Ymd').'-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                'phone' => $user->phone,
                'gender' => $user->gender,
                'birth_date' => $user->birth_date,
                'address' => $user->address,
            ]
        );
        if ($payment->registration) {
            $member->update([
                'phone' => $payment->registration->phone,
                'gender' => $payment->registration->gender,
                'birth_date' => $payment->registration->birth_date,
                'address' => $payment->registration->address,
            ]);
        }

        $latest = Membership::where('member_id', $member->id)->where('end_date', '>=', today())->latest('end_date')->first();
        $start = $latest
            ? $latest->end_date->copy()->addDay()
            : Carbon::parse($payment->payment_date ?: today());

        $membership = Membership::create([
            'member_id' => $member->id,
            'package_id' => $payment->package_id,
            'payment_id' => $payment->id,
            'start_date' => $start,
            'end_date' => $start->copy()->addMonths($payment->package->duration),
            'duration' => $payment->package->duration,
            'status' => 'Aktif',
        ]);
        $membership->update(['status' => $membership->calculated_status]);

        if ($payment->registration_id) {
            $payment->registration()->update(['status' => 'Selesai']);
        }
        if ($user->role === 'pendaftar') {
            $user->update(['role' => 'member']);
        }
    }

    public function checkout(Request $request, Payment $payment): View
    {
        $this->authorizePayment($request, $payment);

        return view('payments.checkout', [
            'payment' => $payment->load(['user', 'package', 'registration']),
            'banks' => Bank::where('is_active', true)
                ->whereNotNull('account_number')
                ->whereNotNull('account_holder')
                ->orderBy('bank_name')
                ->get(),
        ]);
    }

    public function selectBank(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorizePayment($request, $payment);
        abort_if($payment->status === 'Lunas', 422, 'Pembayaran yang sudah lunas tidak dapat diubah.');
        abort_if($payment->status === 'Menunggu Verifikasi', 422, 'Pembayaran yang sedang diverifikasi tidak dapat diubah.');

        $data = $request->validate([
            'bank_id' => ['required', 'exists:banks,id'],
        ]);
        $bank = Bank::where('id', $data['bank_id'])
            ->where('is_active', true)
            ->whereNotNull('account_number')
            ->whereNotNull('account_holder')
            ->firstOrFail();

        $payment->update([
            'method' => 'Transfer',
            'bank_id' => $bank->id,
            'bank_name' => $bank->bank_name,
            'bank_account_number' => $bank->account_number,
            'bank_account_holder' => $bank->account_holder,
        ]);

        return redirect()->route($this->roleRoutePrefix($request).'payments.upload-proof', $payment);
    }

    public function selectCash(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorizePayment($request, $payment);
        abort_unless(
            in_array($payment->status, ['Menunggu', 'Menunggu Pembayaran', 'Ditolak'], true),
            422,
            'Metode pembayaran sudah tidak dapat diubah.',
        );
        if ($payment->payment_proof) {
            Storage::disk('local')->delete($payment->payment_proof);
        }
        $payment->update([
            'method' => 'Cash',
            'status' => 'Menunggu Pembayaran',
            'bank_id' => null,
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_holder' => null,
            'payment_proof' => null,
            'sender_name' => null,
            'transfer_date' => null,
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
        ]);

        return redirect()->route($this->roleRoutePrefix($request).'payments')
            ->with('success', 'Pilih pembayaran tunai dan selesaikan di meja Admin Gym.');
    }

    public function uploadProof(Request $request, Payment $payment): View
    {
        $this->authorizePayment($request, $payment);

        return view('payments.upload-proof', [
            'payment' => $payment->load(['package', 'bank']),
        ]);
    }

    public function submitProof(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorizePayment($request, $payment);
        abort_if($payment->status === 'Lunas', 422, 'Pembayaran yang sudah lunas tidak dapat diperbarui.');
        abort_if(! $payment->bank_id || ! $payment->bank_account_number, 422, 'Pilih bank tujuan terlebih dahulu.');

        $data = $request->validate([
            'sender_name' => ['required', 'string', 'max:100'],
            'transfer_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);
        $path = $request->file('proof')->store('payment-proofs', 'local');

        $payment->update([
            'sender_name' => $data['sender_name'],
            'transfer_date' => $data['transfer_date'],
            'notes' => $data['notes'] ?? null,
            'payment_proof' => $path,
            'status' => 'Menunggu Verifikasi',
            'payment_date' => $data['transfer_date'],
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
        ]);

        return redirect()->route($this->roleRoutePrefix($request).'payments')->with('success', 'Bukti transfer berhasil dikirim dan menunggu verifikasi Admin.');
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($request, $payment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            abort_if($payment->status === 'Lunas', 422, 'Pembayaran sudah diproses.');
            abort_unless(
                $payment->status === 'Menunggu Verifikasi'
                || ($payment->method === 'Cash' && in_array($payment->status, ['Menunggu', 'Menunggu Pembayaran'], true)),
                422,
                'Pembayaran belum siap untuk diverifikasi.',
            );
            abort_if($payment->method !== 'Cash' && ! $payment->payment_proof, 422, 'Bukti transfer belum tersedia.');
            abort_unless((float) $payment->amount === (float) $payment->package->price, 422, 'Nominal pembayaran tidak sesuai invoice.');
            $payment->update([
                'status' => 'Lunas',
                'payment_date' => $payment->payment_date ?? today(),
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);
            $this->activatePaidPayment($payment);
        });

        return back()->with('success', 'Pembayaran berhasil diverifikasi dan lunas.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        abort_if($payment->status === 'Lunas', 422, 'Pembayaran yang sudah lunas tidak dapat ditolak.');
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);
        $payment->update([
            'status' => 'Ditolak',
            'rejection_reason' => $data['rejection_reason'],
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Pembayaran ditolak.');
    }

    public function proof(Request $request, Payment $payment)
    {
        abort_unless(
            in_array($request->user()->role, ['admin', 'owner'], true)
            || $payment->user_id === $request->user()->id,
            404,
        );
        abort_unless($payment->payment_proof && Storage::disk('local')->exists($payment->payment_proof), 404);

        return Storage::disk('local')->response($payment->payment_proof);
    }

    private function authorizePayment(Request $request, Payment $payment): void
    {
        abort_unless($payment->user_id === $request->user()->id, 404);
    }

    private function roleRoutePrefix(Request $request): string
    {
        return $request->user()->role === 'member' ? 'member.' : 'registrant.';
    }
}
