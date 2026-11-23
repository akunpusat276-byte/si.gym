@extends('layouts.app')
@section('title', 'Data Member')
@section('content')
<style>
.member-directory-card{height:100%;border:0;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.06);transition:transform .18s ease,box-shadow .18s ease}
.member-directory-card:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(15,23,42,.09)}
.member-directory-avatar{width:48px;height:48px;border-radius:14px;background:#e8f1ff;color:#2563eb;display:grid;place-items:center;font-size:18px;font-weight:800;overflow:hidden}
.member-directory-avatar img{width:100%;height:100%;object-fit:cover}
.member-directory-code{font-family:monospace;color:#2563eb;background:#eff6ff;border-radius:7px;padding:5px 8px;font-size:12px}
</style>
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">{{ auth()->user()->role === 'owner' ? 'Data Member Gym' : 'Kelola Member' }}</h1>
        <p class="text-muted mb-0">{{ auth()->user()->role === 'owner' ? 'Pantau profil dan identitas member secara read-only.' : 'Kelola profil dan identitas member gym.' }}</p>
    </div>
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('members.create') }}" class="btn btn-primary px-3">
            <i data-lucide="plus" class="me-1"></i>Tambah Member
        </a>
    @endif
</div>

@if(session('error'))
    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
@endif

<div class="card crud-toolbar bg-white mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route(auth()->user()->role === 'owner' ? 'owner.members' : 'members.index') }}" class="d-flex flex-wrap gap-2">
            <div class="input-group" style="max-width: 320px">
                <span class="input-group-text bg-white border-end-0"><i data-lucide="search" style="width: 17px"></i></span>
                <input class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, atau email member..." aria-label="Cari member">
            </div>
            <select class="form-select" name="membership_status" style="max-width:190px" aria-label="Filter status membership">
                <option value="">Semua Status Membership</option>
                @foreach(['Aktif', 'Akan Berakhir', 'Expired', 'Nonaktif'] as $status)
                    <option value="{{ $status }}" @selected(request('membership_status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <select class="form-select" name="package_id" style="max-width:190px" aria-label="Filter paket membership">
                <option value="">Semua Paket</option>
                @foreach($packages as $package)
                    <option value="{{ $package->id }}" @selected((string) request('package_id') === (string) $package->id)>{{ $package->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary px-3" type="submit">Cari</button>
            @if(request()->filled('search') || request()->filled('membership_status') || request()->filled('package_id'))
                <a href="{{ route(auth()->user()->role === 'owner' ? 'owner.members' : 'members.index') }}" class="btn btn-light border">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="row g-3">
    @forelse($members as $member)
        <div class="col-md-6 col-xl-4">
            <article class="card member-directory-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="member-directory-avatar">
                            @if($member->user->avatar)
                                <img src="{{ asset('storage/'.$member->user->avatar) }}" alt="Foto {{ $member->user->name }}">
                            @else
                                {{ strtoupper(substr($member->user->name, 0, 1)) }}
                            @endif
                        </div>
                        @php
                            $latestMembership = $member->memberships->sortByDesc('end_date')->first();
                            $membershipStatus = in_array($member->status, ['Nonaktif', 'tidak aktif'], true)
                                ? 'Nonaktif'
                                : ($latestMembership?->calculated_status ?? 'Belum Berlangganan');
                            $membershipStatus = $membershipStatus === 'Kedaluwarsa' ? 'Expired' : $membershipStatus;
                            $memberIsActive = ! in_array(strtolower((string) $member->status), ['nonaktif', 'tidak aktif'], true);
                            $membershipBadge = match ($membershipStatus) {
                                'Aktif' => 'success',
                                'Akan Berakhir' => 'warning',
                                'Kedaluwarsa' => 'secondary',
                                'Nonaktif' => 'dark',
                                default => 'light',
                            };
                        @endphp
                        <span class="badge rounded-pill text-bg-{{ $membershipBadge }}">{{ $membershipStatus }}</span>
                    </div>
                    <h2 class="h6 fw-bold mt-3 mb-1">{{ $member->user->name }}</h2>
                    <div class="text-muted small text-truncate">{{ $member->user->email }}</div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top mt-3 pt-3">
                        <span class="member-directory-code">{{ $member->member_code }}</span>
                        <span class="small text-muted">{{ $member->phone ?: 'Telepon belum diisi' }}</span>
                    </div>
                    @if($latestMembership)
                        <div class="small text-muted mt-2">{{ $latestMembership->package?->name ?? '-' }} · Berakhir {{ $latestMembership->end_date?->format('d M Y') ?? '-' }}</div>
                    @endif
                    <div class="d-flex gap-2 mt-3">
                        <a class="btn btn-primary flex-grow-1" href="{{ auth()->user()->role === 'owner' ? route('owner.member.show', $member) : route('members.show', $member) }}">
                            <i data-lucide="eye" style="width:15px"></i> Detail
                        </a>
                        @if(auth()->user()->role === 'admin')
                            <a class="btn btn-outline-primary" href="{{ route('members.edit', $member) }}" aria-label="Edit {{ $member->user->name }}" title="Edit"><i data-lucide="pencil" style="width:15px"></i></a>
                            <form method="POST" action="{{ route('members.status', $member) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-outline-secondary" type="submit" aria-label="{{ $memberIsActive ? 'Nonaktifkan' : 'Aktifkan' }} {{ $member->user->name }}" title="{{ $memberIsActive ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i data-lucide="{{ $memberIsActive ? 'user-round-x' : 'user-round-check' }}" style="width:15px"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Hapus data member ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger" type="submit" aria-label="Hapus {{ $member->user->name }}" title="Hapus"><i data-lucide="trash-2" style="width:15px"></i></button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        </div>
    @empty
        <div class="col-12"><div class="card app-card"><div class="card-body text-center text-muted py-5">{{ request()->filled('search') ? 'Tidak ada member yang cocok dengan pencarian.' : 'Belum ada data member.' }}</div></div></div>
    @endforelse
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <span class="small text-muted">Total {{ $members->total() }} member</span>
    {{ $members->links() }}
</div>
@endsection
