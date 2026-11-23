<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'status' => ['nullable', Rule::in(['Aktif', 'Akan Berakhir', 'Kedaluwarsa', 'Menunggu Pembayaran'])],
        ]);
        $search = trim($validated['search'] ?? '');

        $query = Membership::with(['member.user', 'package'])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->whereHas('member', fn (Builder $memberQuery) => $memberQuery
                    ->where('member_code', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%")))
                    ->orWhereHas('package', fn (Builder $packageQuery) => $packageQuery->where('name', 'like', "%{$search}%"));
            }))
            ->when($validated['package_id'] ?? null, fn (Builder $query, int $packageId) => $query->where('package_id', $packageId))
            ->when(($validated['status'] ?? null) === 'Aktif', fn (Builder $query) => $query
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>', today()->addDays(7)))
            ->when(($validated['status'] ?? null) === 'Akan Berakhir', fn (Builder $query) => $query
                ->whereDate('start_date', '<=', today())
                ->whereBetween('end_date', [today(), today()->addDays(7)]))
            ->when(($validated['status'] ?? null) === 'Kedaluwarsa', fn (Builder $query) => $query
                ->whereDate('end_date', '<', today()))
            ->when(($validated['status'] ?? null) === 'Menunggu Pembayaran', fn (Builder $query) => $query
                ->whereNull('start_date'))
            ->latest();
        if ($request->user()->role === 'member') {
            $member = $request->user()->member;
            abort_unless($member, 404);
            $query->whereBelongsTo($member);
        }

        return view('memberships.index', [
            'memberships' => $query->paginate(15)->withQueryString(),
            'packages' => Package::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Request $request, Membership $membership): View
    {
        if (in_array($request->user()->role, ['member', 'pendaftar'], true)) {
            abort_unless($request->user()->member && $membership->member_id === $request->user()->member->id, 404);
        }

        return view('memberships.show', compact('membership'));
    }

    public function create(): View
    {
        return view('memberships.create', [
            'members' => Member::with('user')->orderBy('member_code')->get(),
            'packages' => Package::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'start_date' => ['required', 'date'],
        ]);
        $package = Package::findOrFail($data['package_id']);
        $start = now()->parse($data['start_date']);
        Membership::create([
            ...$data,
            'start_date' => $start,
            'end_date' => $start->copy()->addMonths((int) $package->duration),
            'duration' => $package->duration,
            'status' => 'Aktif',
        ]);

        return redirect()->route('memberships.index')->with('success', 'Membership berhasil ditambahkan.');
    }

    public function edit(Membership $membership): View
    {
        return view('memberships.edit', [
            'membership' => $membership->load(['member.user', 'package']),
            'members' => Member::with('user')->orderBy('member_code')->get(),
            'packages' => Package::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Membership $membership): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
        $membership->update([
            ...$data,
            'duration' => Package::findOrFail($data['package_id'])->duration,
        ]);
        $membership->update(['status' => $membership->calculated_status]);

        return redirect()->route('memberships.index')->with('success', 'Membership berhasil diperbarui.');
    }

    public function destroy(Membership $membership): RedirectResponse
    {
        $membership->delete();

        return back()->with('success', 'Membership berhasil dihapus.');
    }

    public function requestRenewal(Request $request, Membership $membership): RedirectResponse
    {
        $member = $request->user()->member;
        abort_unless($member && $membership->member_id === $member->id, 404);

        $payment = DB::transaction(function () use ($request, $membership, $member): Payment {
            $membership = Membership::query()->lockForUpdate()->findOrFail($membership->id);
            $package = Package::query()->whereKey($membership->package_id)->firstOrFail();
            $pendingPaymentExists = Payment::query()
                ->whereBelongsTo($request->user())
                ->where('package_id', $package->id)
                ->whereIn('status', ['Menunggu', 'Menunggu Pembayaran', 'Menunggu Verifikasi'])
                ->exists();
            abort_if($pendingPaymentExists, 422, 'Masih ada tagihan yang belum diselesaikan.');

            return Payment::create([
                'invoice_number' => 'INV-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'user_id' => $request->user()->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'method' => 'Transfer',
                'status' => 'Menunggu Pembayaran',
                'notes' => 'Perpanjangan membership '.$membership->id,
            ]);
        });

        return redirect()->route('member.payments.checkout', $payment)
            ->with('success', 'Tagihan perpanjangan berhasil dibuat.');
    }
}
