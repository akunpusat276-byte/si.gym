@extends('layouts.app')
@section('title', $package->name)
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">{{ $package->name }}</h1>
        <p class="text-muted mb-0">{{ auth()->user()->role === 'owner' ? 'Informasi paket membership (read-only).' : 'Detail paket membership.' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route(auth()->user()->role === 'owner' ? 'owner.packages' : 'packages.index') }}" class="btn btn-outline-secondary">Kembali</a>
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('packages.edit', $package) }}" class="btn btn-primary"><i data-lucide="pencil" class="me-1"></i>Edit Paket</a>
        @endif
    </div>
</div>
<div class="card app-card">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h2 class="h4 fw-bold">{{ $package->name }}</h2>
                <p class="text-muted mb-0">{{ $package->description ?: 'Tidak ada deskripsi paket.' }}</p>
            </div>
            <span class="badge text-bg-{{ $package->is_active ? 'success' : 'secondary' }}">{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</span>
        </div>
        @if($package->imageUrl())
            <img src="{{ $package->imageUrl() }}" alt="Foto paket {{ $package->name }}" class="img-fluid rounded-3 mb-4" style="max-height:300px;object-fit:cover">
        @endif
        <div class="row g-3">
            <div class="col-sm-6">
                <div class="small text-muted">Kategori</div>
                <div class="fw-semibold">{{ $package->category ?: 'Tanpa kategori' }}</div>
            </div>
            <div class="col-sm-6">
                <div class="small text-muted">Durasi</div>
                <div class="fw-semibold">{{ $package->duration }} bulan</div>
            </div>
            <div class="col-sm-6">
                <div class="small text-muted">Harga</div>
                <div class="fw-semibold">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
            </div>
            @if($package->facilities)
                <div class="col-12">
                    <div class="small text-muted">Fasilitas</div>
                    <div class="fw-semibold">{{ $package->facilities }}</div>
                </div>
            @endif
            <div class="col-sm-6">
                <div class="small text-muted">Tanggal dibuat</div>
                <div class="fw-semibold">{{ $package->created_at?->format('d M Y') ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
