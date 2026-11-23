@extends('layouts.app')
@section('title', 'Paket Membership')
@section('content')
<style> 
.registrant-catalog { --catalog-ink:#172033;--catalog-blue:#123c78; }
.registrant-hero { min-height:126px;border-radius:16px;overflow:hidden;background-image:linear-gradient(90deg,rgba(7,22,47,.95),rgba(7,22,47,.66) 49%,rgba(7,22,47,.16)),url("{{ asset('images/sigym-reference.png') }}");background-size:100% 100%,200% auto;background-position:center,left 43%;background-repeat:no-repeat;color:#fff;padding:22px 26px;display:flex;align-items:center;box-shadow:0 10px 24px rgba(15,23,42,.13); }
.registrant-hero h1 { color:#fff;font-size:clamp(24px,3vw,34px);line-height:1.15;letter-spacing:-.7px; }
.registrant-hero h1 span { color:#3b82f6; }
.registrant-hero p { color:rgba(255,255,255,.82);font-size:14px; }
.package-grid-card { height:100%;overflow:hidden;border:0;border-radius:15px;background:#fff;box-shadow:0 6px 20px rgba(17,45,91,.12);transition:transform .18s ease,box-shadow .18s ease; }
.package-grid-card:hover { transform:translateY(-3px);box-shadow:0 12px 26px rgba(17,45,91,.17); }
.package-grid-image-wrap { height:108px;position:relative;overflow:hidden;background:#dbe5f1; }
.package-grid-image { width:100%;height:100%;object-fit:cover; }
.package-duration { position:absolute;right:10px;top:9px;display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:5px 10px;color:#fff;font-size:11px;font-weight:700;box-shadow:0 3px 8px rgba(15,23,42,.16); }
.package-duration svg { width:13px;height:13px; }
.package-duration.tone-blue { background:#2878e8; }
.package-duration.tone-green { background:#21c56b; }
.package-duration.tone-purple { background:#8253ed; }
.package-duration.tone-orange { background:#f49b12; }
.package-grid-card .card-body { padding:13px 15px 14px; }
.package-grid-title { color:var(--catalog-ink);font-size:15px;font-weight:750;margin:0 0 3px; }
.package-grid-description { color:#667085;font-size:11px;line-height:1.4;margin:0;min-height:16px; }
.package-grid-price { color:#123c9b;font-size:16px;font-weight:800;letter-spacing:-.2px;white-space:nowrap; }
.package-detail-link { min-width:85px;height:34px;border:0;border-radius:10px;background:linear-gradient(135deg,#153f7e,#071f4b);color:#fff;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;box-shadow:0 3px 8px rgba(13,44,91,.18); }
.package-detail-link:hover { color:#fff;background:#1f5aad; }
.package-detail-link svg { width:14px;height:14px; }
.package-empty { min-height:180px;border:1px dashed #cbd5e1;border-radius:14px;background:#fff; }
.gym-info-panel { padding:clamp(18px,3vw,30px);border:1px solid #dce6f3;border-radius:18px;background:linear-gradient(145deg,#f8fbff,#eef4fb); }
.gym-info-heading { display:flex;align-items:flex-start;gap:14px;margin-bottom:20px; }
.gym-info-heading-icon { width:46px;height:46px;flex:0 0 46px;display:grid;place-items:center;border-radius:14px;background:#123c78;color:#fff;box-shadow:0 5px 14px rgba(18,60,120,.2); }
.gym-info-heading-icon svg { width:23px;height:23px; }
.gym-info-heading h2 { color:#172033;font-size:clamp(19px,2.4vw,24px);font-weight:750;margin:0 0 4px; }
.gym-info-heading p { color:#64748b;font-size:13px;margin:0; }
.gym-info-card { height:100%;border:1px solid #e6edf5;border-radius:14px;background:#fff;box-shadow:0 5px 18px rgba(15,23,42,.055); }
.gym-info-card-body { display:flex;align-items:flex-start;gap:13px;padding:18px; }
.gym-info-icon { width:40px;height:40px;flex:0 0 40px;display:grid;place-items:center;border-radius:12px;background:#eaf2ff;color:#1d4f91; }
.gym-info-icon svg { width:19px;height:19px; }
.gym-info-label { color:#64748b;font-size:12px;font-weight:600;margin-bottom:5px; }
.gym-info-value { color:#172033;font-weight:650;line-height:1.55;overflow-wrap:anywhere; }
.gym-info-value a { color:#123c78;text-decoration:none; }
.gym-info-value a:hover { text-decoration:underline; }
.gym-info-facilities { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px 16px;padding:0;margin:9px 0 0;list-style:none; }
.gym-info-facilities li { display:flex;align-items:flex-start;gap:8px;color:#344054;font-size:13px;line-height:1.45;overflow-wrap:anywhere; }
.gym-info-facilities svg { width:16px;height:16px;flex:0 0 16px;margin-top:1px;color:#2563eb; }
@media(max-width:767.98px) {
    .registrant-hero { min-height:116px;padding:18px;background-size:100% 100%,200% auto;background-position:center,left 43%; }
    .package-grid-image-wrap { height:130px; }
    .package-grid-card .card-body { padding:13px; }
}
@media(max-width:575.98px) {
    .gym-info-panel { padding:16px; }
    .gym-info-card-body { padding:15px; }
    .gym-info-facilities { grid-template-columns:1fr; }
}
</style>

<div class="registrant-catalog">
    <section class="registrant-hero mb-4">
        <div>
            <h1 class="fw-bold mb-1">Selamat Datang, <span>Pendaftar!</span></h1>
            <p class="mb-0">Mulai perjalanan sehat Anda bersama SI-GYM.</p>
        </div>
    </section>

    <div id="paket-membership" class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">Paket Membership</h2>
            <p class="text-muted small mb-0">Pilih paket latihan yang sesuai dengan kebutuhan Anda.</p>
        </div>
    </div>

    <div class="row g-3">
        @php
            $fallbackPackageImages = [
                'images/packages/annual.jpg',
                'images/packages/basic.jpg',
                'images/packages/basic-month.jpg',
                'images/packages/annual-month.jpg',
                'images/packages/three-month.jpg',
                'images/packages/six-month.jpg',
                'images/packages/premium.jpg',
                'images/packages/regular.jpg',
            ];
        @endphp
        @forelse($packages as $package)
            @php
                $durationTones = ['tone-blue', 'tone-green', 'tone-purple', 'tone-orange'];
                $durationTone = $durationTones[$loop->index % count($durationTones)];
            @endphp
            <div class="col-sm-6 col-xl-4">
                <article class="card package-grid-card">
                    <div class="package-grid-image-wrap">
                        @if($package->imageUrl())
                            <img class="package-grid-image" src="{{ $package->imageUrl() }}" alt="Foto paket {{ $package->name }}">
                        @else
                            <img class="package-grid-image" src="{{ asset($fallbackPackageImages[$loop->index % count($fallbackPackageImages)]) }}" alt="Foto gym untuk paket {{ $package->name }}">
                        @endif
                        <span class="package-duration {{ $durationTone }}"><i data-lucide="calendar-days"></i>{{ $package->duration }} Bulan</span>
                    </div>
                    <div class="card-body">
                        <h3 class="package-grid-title">{{ $package->name }}</h3>
                        <p class="package-grid-description">{{ $package->description ?: 'Paket latihan SI-GYM' }}</p>
                        <div class="d-flex justify-content-between align-items-end gap-2 mt-2">
                            <div class="package-grid-price">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                            <a href="{{ route('registrant.packages.show', $package) }}" class="package-detail-link">
                                Detail <i data-lucide="arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="package-empty d-flex flex-column align-items-center justify-content-center text-center p-4">
                    <i data-lucide="package-open" class="text-secondary mb-2" style="width:28px;height:28px"></i>
                    <h3 class="h6 fw-bold mb-1">Paket Belum Tersedia</h3>
                    <p class="text-muted small mb-0">Silakan kembali lagi nanti atau hubungi Admin SI-GYM.</p>
                </div>
            </div>
        @endforelse
    </div>
    <section class="gym-info-panel mt-4" id="informasi-gym" aria-labelledby="gym-info-title">
        <div class="gym-info-heading">
            <span class="gym-info-heading-icon"><i data-lucide="building-2"></i></span>
            <div>
                <h2 id="gym-info-title">Informasi Gym</h2>
                <p>Kenali fasilitas dan informasi SI-GYM sebelum memulai latihan Anda.</p>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-sm-6 col-xl-4">
                <article class="card gym-info-card">
                    <div class="gym-info-card-body">
                        <span class="gym-info-icon"><i data-lucide="dumbbell"></i></span>
                        <div class="min-w-0"><div class="gym-info-label">Nama Gym</div><div class="gym-info-value">{{ $gymInfo['gym_name'] ?? 'SI-GYM' }}</div></div>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-4">
                <article class="card gym-info-card">
                    <div class="gym-info-card-body">
                        <span class="gym-info-icon"><i data-lucide="phone"></i></span>
                        <div class="min-w-0">
                            <div class="gym-info-label">Nomor Kontak</div>
                            <div class="gym-info-value">
                                @if($gymInfo['gym_phone'] ?? false)
                                    <a href="tel:{{ $gymPhoneLink }}">{{ $gymInfo['gym_phone'] }}</a>
                                @else
                                    Hubungi Admin SI-GYM
                                @endif
                            </div>
                            @if(!empty($gymInfo['gym_email']))
                                <div class="gym-info-value small mt-1"><a href="mailto:{{ $gymInfo['gym_email'] }}">{{ $gymInfo['gym_email'] }}</a></div>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-4">
                <article class="card gym-info-card">
                    <div class="gym-info-card-body">
                        <span class="gym-info-icon"><i data-lucide="clock-3"></i></span>
                        <div class="min-w-0"><div class="gym-info-label">Jam Operasional</div><div class="gym-info-value">{{ $gymInfo['gym_hours'] ?? 'Hubungi Admin untuk informasi jam operasional.' }}</div></div>
                    </div>
                </article>
            </div>
            <div class="col-md-6">
                <article class="card gym-info-card">
                    <div class="gym-info-card-body">
                        <span class="gym-info-icon"><i data-lucide="map-pin"></i></span>
                        <div class="min-w-0"><div class="gym-info-label">Alamat Gym</div><div class="gym-info-value">{{ $gymInfo['gym_address'] ?? 'Hubungi Admin untuk informasi alamat.' }}</div></div>
                    </div>
                </article>
            </div>
            <div class="col-md-6">
                <article class="card gym-info-card">
                    <div class="gym-info-card-body">
                        <span class="gym-info-icon"><i data-lucide="activity"></i></span>
                        <div class="min-w-0 flex-grow-1">
                            <div class="gym-info-label">Fasilitas Gym</div>
                            @if(count($gymFacilities))
                                <ul class="gym-info-facilities">
                                    @foreach($gymFacilities as $facility)
                                        <li><i data-lucide="circle-check"></i><span>{{ $facility }}</span></li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="gym-info-value">Informasi fasilitas akan diperbarui oleh Admin.</div>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
