@extends('layouts.app')
@section('title', 'Membership')
@section('content')
<style>
.membership-card{height:100%;border:0;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.06);transition:transform .18s ease,box-shadow .18s ease}
.membership-card:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(15,23,42,.09)}
.membership-ring{height:7px;background:#e9eef5;border-radius:99px;overflow:hidden}
.membership-ring div{height:100%;background:#2563eb;border-radius:99px}
.membership-plan-icon{width:42px;height:42px;border-radius:13px;background:#e8f1ff;color:#2563eb;display:grid;place-items:center}
.membership-dates{font-size:12px}
</style>
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">{{ auth()->user()->role === 'admin' ? 'Kelola Membership Member' : (auth()->user()->role === 'owner' ? 'Data Membership Gym' : 'Membership Saya') }}</h1>
        <p class="text-muted mb-0">{{ auth()->user()->role === 'admin' ? 'Kelola masa aktif, paket, dan status membership member.' : (auth()->user()->role === 'owner' ? 'Pantau membership gym secara read-only.' : 'Informasi paket dan masa aktif membership Anda.') }}</p>
    </div>
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('memberships.create') }}" class="btn btn-primary"><i data-lucide="plus" class="me-1"></i>Tambah Membership</a>
    @endif
</div>
<div class="card crud-toolbar bg-white mb-3"><div class="card-body py-3"><form method="GET" action="{{ route(match(auth()->user()->role) { 'owner' => 'owner.memberships', 'member' => 'member.memberships', default => 'memberships.index' }) }}" class="d-flex flex-wrap gap-2">
<div class="input-group" style="max-width:300px"><span class="input-group-text bg-white border-end-0"><i data-lucide="search" style="width:17px"></i></span><input class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Cari member, kode, atau paket..." aria-label="Cari membership"></div>
<select name="package_id" class="form-select" style="max-width:190px" aria-label="Filter paket"><option value="">Semua Paket</option>@foreach($packages as $package)<option value="{{ $package->id }}" @selected((string) request('package_id') === (string) $package->id)>{{ $package->name }}</option>@endforeach</select>
<select name="status" class="form-select" style="max-width:200px" aria-label="Filter status membership"><option value="">Semua Status</option>@foreach(['Aktif','Akan Berakhir','Kedaluwarsa','Menunggu Pembayaran'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
<button class="btn btn-primary" type="submit">Filter</button>@if(request()->filled('search') || request()->filled('status') || request()->filled('package_id'))<a href="{{ route(match(auth()->user()->role) { 'owner' => 'owner.memberships', 'member' => 'member.memberships', default => 'memberships.index' }) }}" class="btn btn-light border">Reset</a>@endif</form></div></div>
<div class="row g-3">
@forelse($memberships as $membership)
    @php
        $totalDays = $membership->start_date && $membership->end_date
            ? max($membership->start_date->diffInDays($membership->end_date), 1)
            : 1;
        $remainingDays = $membership->remaining_days;
        $progress = $membership->start_date && $membership->end_date
            ? min(100, max(0, $remainingDays / $totalDays * 100))
            : 0;
        $statusColor = match ($membership->calculated_status) {
            'Aktif' => 'success',
            'Akan Berakhir' => 'warning',
            'Kedaluwarsa' => 'secondary',
            default => 'info',
        };
    @endphp
    <div class="col-md-6 col-xl-4">
        <article class="card membership-card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div class="d-flex align-items-center gap-2"><span class="membership-plan-icon"><i data-lucide="badge-check"></i></span><div><div class="text-muted small">Member</div><h2 class="h6 fw-bold mb-0">{{ $membership->member->user->name }}</h2></div></div>
                    <span class="badge text-bg-{{ $statusColor }}">{{ $membership->calculated_status }}</span>
                </div>
                <div class="border-top mt-3 pt-3">
                    <div class="fw-semibold">{{ $membership->package->name }}</div>
                    <div class="d-flex justify-content-between gap-2 text-muted membership-dates mt-2"><span>Mulai {{ $membership->start_date?->format('d M Y') ?? '-' }}</span><span>Sampai {{ $membership->end_date?->format('d M Y') ?? '-' }}</span></div>
                    <div class="membership-ring mt-2" role="progressbar" aria-label="Sisa masa membership" aria-valuenow="{{ (int) round($progress) }}" aria-valuemin="0" aria-valuemax="100"><div style="width:{{ $progress }}%"></div></div>
                    <small class="text-muted d-block mt-2">{{ $remainingDays }} hari tersisa</small>
                </div>
                <div class="d-flex gap-2 mt-3">
                    @if(auth()->user()->role === 'owner')
                        <a class="btn btn-primary flex-grow-1" href="{{ route('owner.membership.show',$membership) }}">Detail</a>
                    @elseif(auth()->user()->role === 'admin')
                        <a class="btn btn-primary flex-grow-1" href="{{ route('memberships.show',$membership) }}">Detail</a>
                        <a class="btn btn-outline-primary" href="{{ route('memberships.edit',$membership) }}" aria-label="Edit membership"><i data-lucide="pencil" style="width:15px"></i></a>
                        <form method="POST" action="{{ route('memberships.destroy',$membership) }}" onsubmit="return confirm('Hapus membership ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit" aria-label="Hapus membership"><i data-lucide="trash-2" style="width:15px"></i></button></form>
                    @elseif(auth()->user()->role === 'member')
                        <a class="btn btn-primary flex-grow-1" href="{{ route('member.memberships.show',$membership) }}">Detail</a>
                        @if($membership->calculated_status !== 'Menunggu Pembayaran')
                            <form method="POST" action="{{ route('member.memberships.renewal', $membership) }}" onsubmit="return confirm('Buat tagihan perpanjangan untuk paket ini?')">@csrf<button class="btn btn-outline-primary" type="submit">Perpanjang</button></form>
                        @endif
                    @endif
                </div>
            </div>
        </article>
    </div>
@empty
    <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">{{ request()->filled('search') ? 'Membership tidak ditemukan.' : 'Belum ada membership.' }}</div></div></div>
@endforelse
</div><div class="mt-3">{{ $memberships->links() }}</div>
@endsection
