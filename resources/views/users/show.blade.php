@extends('layouts.app')
@section('title', 'Detail Pengguna')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">Detail Pengguna</h1>
        <p class="text-muted mb-0">Informasi akun dan aktivitas {{ $user->name }}.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Kembali</a>
        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary"><i data-lucide="pencil" class="me-1"></i>Edit Pengguna</a>
    </div>
</div>
<div class="card app-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1">{{ $user->name }}</h2>
                <div class="text-muted">{{ $user->email }}</div>
            </div>
            <div class="d-flex gap-2">
                <span class="badge text-bg-light border text-uppercase">{{ $user->role }}</span>
                <span class="badge text-bg-{{ $user->status === 'Aktif' ? 'success' : 'secondary' }}">{{ $user->status }}</span>
            </div>
        </div>
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted fw-normal">ID</dt><dd class="col-sm-9">{{ $user->id }}</dd>
            <dt class="col-sm-3 text-muted fw-normal">Nomor Telepon</dt><dd class="col-sm-9">{{ $user->phone ?: '-' }}</dd>
            <dt class="col-sm-3 text-muted fw-normal">Alamat</dt><dd class="col-sm-9">{{ $user->address ?: '-' }}</dd>
            <dt class="col-sm-3 text-muted fw-normal">Jenis Kelamin</dt><dd class="col-sm-9">{{ $user->gender ?: '-' }}</dd>
            <dt class="col-sm-3 text-muted fw-normal">Tanggal Lahir</dt><dd class="col-sm-9">{{ $user->birth_date?->format('d M Y') ?: '-' }}</dd>
            <dt class="col-sm-3 text-muted fw-normal">Tanggal Dibuat</dt><dd class="col-sm-9">{{ $user->created_at?->format('d M Y H:i') ?: '-' }}</dd>
            @if($user->member)
                <dt class="col-sm-3 text-muted fw-normal">Kode Member</dt><dd class="col-sm-9">{{ $user->member->member_code }}</dd>
            @endif
        </dl>
    </div>
</div>
@if($user->registrations->isNotEmpty())
    <section class="card app-card mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3">Pendaftaran</h2>
            @foreach($user->registrations as $registration)
                <div class="d-flex flex-wrap justify-content-between gap-2 border-top py-2">
                    <span>{{ $registration->registration_number }} · {{ $registration->package?->name ?? '-' }}</span>
                    <span>{{ $registration->status }}</span>
                </div>
            @endforeach
        </div>
    </section>
@endif
@if($user->payments->isNotEmpty())
    <section class="card app-card">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3">Pembayaran Terbaru</h2>
            @foreach($user->payments->sortByDesc('created_at')->take(5) as $payment)
                <div class="d-flex flex-wrap justify-content-between gap-2 border-top py-2">
                    <span>{{ $payment->invoice_number }} · {{ $payment->package?->name ?? '-' }}</span>
                    <span>Rp {{ number_format($payment->amount, 0, ',', '.') }} · {{ $payment->status }}</span>
                </div>
            @endforeach
        </div>
    </section>
@endif
@endsection
