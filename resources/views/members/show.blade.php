@extends('layouts.app')
@section('title', 'Detail Member')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">Detail Member</h1>
        <p class="text-muted mb-0">Informasi profil dan membership {{ $member->user->name }}.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route(auth()->user()->role === 'owner' ? 'owner.members' : 'members.index') }}" class="btn btn-outline-secondary">Kembali</a>
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('members.edit', $member) }}" class="btn btn-primary">
                <i data-lucide="pencil" class="me-1"></i>Edit Member
            </a>
        @endif
    </div>
</div>

<div class="card app-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1">{{ $member->user->name }}</h2>
                <div class="text-muted">{{ $member->user->email }}</div>
            </div>
            <span class="badge text-bg-{{ $member->status === 'Aktif' ? 'success' : 'secondary' }}">{{ $member->status }}</span>
        </div>
        <div class="row g-3">
            <div class="col-sm-6 col-lg-4">
                <div class="small text-muted">Kode Member</div>
                <div class="fw-semibold"><span class="member-code">{{ $member->member_code }}</span></div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="small text-muted">Telepon</div>
                <div class="fw-semibold">{{ $member->phone ?: '-' }}</div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="small text-muted">Jenis Kelamin</div>
                <div class="fw-semibold">{{ $member->gender ?: '-' }}</div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="small text-muted">Tanggal Lahir</div>
                <div class="fw-semibold">{{ $member->birth_date?->format('d M Y') ?: '-' }}</div>
            </div>
            <div class="col-12">
                <div class="small text-muted">Alamat</div>
                <div class="fw-semibold">{{ $member->address ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 fw-bold mb-0">Riwayat Membership</h2>
    <span class="small text-muted">{{ $member->memberships->count() }} membership</span>
</div>
<div class="card admin-table-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th scope="col">Paket</th>
                    <th scope="col">Mulai</th>
                    <th scope="col">Berakhir</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($member->memberships as $membership)
                    <tr>
                        <td>{{ $membership->package?->name ?? '-' }}</td>
                        <td>{{ $membership->start_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $membership->end_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $membership->calculated_status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat membership.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
