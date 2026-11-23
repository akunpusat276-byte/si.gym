@extends('layouts.app')
@section('title', 'Detail Paket '.$package->name)
@section('content')
<style>
.registrant-package-detail { border:0;border-radius:16px;overflow:hidden;box-shadow:0 8px 26px rgba(15,23,42,.09); }
.registrant-package-detail-image { width:100%;height:clamp(210px,32vw,360px);object-fit:cover; }
.registrant-package-price { color:#123c9b;font-size:26px;font-weight:800; }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title mb-1">Detail Paket Membership</h1>
        <p class="text-muted mb-0">Tinjau manfaat dan harga sebelum mendaftar.</p>
    </div>
    <a href="{{ route('registrant.dashboard') }}#paket-membership" class="btn btn-outline-secondary">Kembali ke Paket</a>
</div>

<article class="card registrant-package-detail">
    <img
        class="registrant-package-detail-image"
        src="{{ $package->imageUrl() ?: asset('images/packages/regular.jpg') }}"
        alt="Foto paket {{ $package->name }}"
    >
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="badge rounded-pill text-bg-primary mb-2">{{ $package->duration }} Bulan</span>
                <h2 class="h3 fw-bold mb-2">{{ $package->name }}</h2>
                <p class="text-muted mb-0">{{ $package->description ?: 'Paket latihan SI-GYM' }}</p>
            </div>
            <div class="registrant-package-price">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
        </div>

        @if($package->facilities)
            <div class="border-top mt-4 pt-4">
                <h3 class="h6 fw-bold">Fasilitas Paket</h3>
                <p class="mb-0 text-muted" style="white-space:pre-line">{{ $package->facilities }}</p>
            </div>
        @endif

        <div class="d-flex flex-wrap align-items-center gap-2 border-top mt-4 pt-4">
            @if($hasActiveRegistration)
                <a href="{{ route('registrations.status.me') }}" class="btn btn-primary px-4">Lihat Pendaftaran Saya</a>
                <span class="small text-muted">Selesaikan atau batalkan pendaftaran aktif sebelum memilih paket lain.</span>
            @else
                <a href="{{ route('registrations.create', ['package' => $package->id]) }}" class="btn btn-primary px-4">Pilih Paket Ini</a>
            @endif
            <a href="{{ route('registrant.dashboard') }}" class="btn btn-light border px-4">Kembali</a>
        </div>
    </div>
</article>
@endsection
