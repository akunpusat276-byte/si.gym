<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'membership_status' => ['nullable', Rule::in(['Aktif', 'Akan Berakhir', 'Expired', 'Nonaktif'])],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
        ]);
        $search = trim($validated['search'] ?? '');

        $members = Member::with(['user', 'memberships.package'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('member_code', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when(($validated['package_id'] ?? null) !== null, fn (Builder $query) => $query->whereHas(
                'memberships',
                fn (Builder $membershipQuery) => $membershipQuery->where('package_id', $validated['package_id']),
            ))
            ->when(($validated['membership_status'] ?? null) === 'Nonaktif', fn (Builder $query) => $query
                ->whereIn('status', ['Nonaktif', 'tidak aktif']))
            ->when(($validated['membership_status'] ?? null) === 'Aktif', fn (Builder $query) => $query
                ->whereNotIn('status', ['Nonaktif', 'tidak aktif'])
                ->whereHas('memberships', fn (Builder $membershipQuery) => $membershipQuery
                    ->whereDate('start_date', '<=', today())
                    ->whereDate('end_date', '>', today()->addDays(7))))
            ->when(($validated['membership_status'] ?? null) === 'Akan Berakhir', fn (Builder $query) => $query
                ->whereNotIn('status', ['Nonaktif', 'tidak aktif'])
                ->whereHas('memberships', fn (Builder $membershipQuery) => $membershipQuery
                    ->whereDate('start_date', '<=', today())
                    ->whereBetween('end_date', [today(), today()->addDays(7)])))
            ->when(($validated['membership_status'] ?? null) === 'Expired', fn (Builder $query) => $query
                ->whereHas('memberships', fn (Builder $membershipQuery) => $membershipQuery
                    ->whereDate('end_date', '<', today())))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('members.index', [
            'members' => $members,
            'packages' => Package::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('members.create', [
            'users' => User::where('role', 'member')->whereDoesntHave('member')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'member'), Rule::unique('members', 'user_id')],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data): void {
            $member = Member::create([
                ...$data,
                'member_code' => 'MBR-'.now()->format('Ymd').'-'.str_pad((string) $data['user_id'], 6, '0', STR_PAD_LEFT),
            ]);
            User::whereKey($data['user_id'])->update(['role' => 'member']);
        });

        return redirect()->route('members.index')->with('success', 'Member berhasil ditambahkan.');
    }

    public function show(Member $member): View
    {
        $member->load(['user', 'memberships.package']);

        return view('members.show', compact('member'));
    }

    public function edit(Member $member): View
    {
        $member->load('user');

        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],
        ]);
        $member->update($data);

        return redirect()->route('members.index')->with('success', 'Data member berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        if ($member->memberships()->exists()) {
            return redirect()->route('members.index')
                ->with('error', 'Member yang memiliki membership tidak dapat dihapus.');
        }

        DB::transaction(function () use ($member) {
            $member->user()->update(['role' => 'pendaftar']);
            $member->delete();
        });

        return redirect()->route('members.index')->with('success', 'Data member berhasil dihapus.');
    }

    public function toggleStatus(Member $member): RedirectResponse
    {
        $isInactive = in_array(strtolower((string) $member->status), ['nonaktif', 'tidak aktif'], true);
        $member->update(['status' => $isInactive ? 'Aktif' : 'Nonaktif']);

        return back()->with('success', 'Status member berhasil diperbarui.');
    }
}
