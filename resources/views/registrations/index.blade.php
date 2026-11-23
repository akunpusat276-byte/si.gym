@extends('layouts.app')
@section('title', 'Pendaftaran Calon Member')
@section('content')
@if(auth()->user()->role === 'pendaftar')
@php
    $registration = $registrations->first();
@endphp
<style>
.applicant-page-banner { height:104px;border-radius:4px;background:linear-gradient(90deg,rgba(15,23,42,.96),rgba(15,23,42,.38)),url("{{ asset('images/gym-login.jpg') }}") center 42%/cover;color:white;display:flex;align-items:center;padding:16px 28px;box-shadow:0 3px 12px rgba(15,23,42,.12); overflow:hidden; }
.applicant-page-banner h2 { font-size:25px;font-weight:750;line-height:1.02;margin:0; }
.applicant-page-banner h2:last-of-type { font-size:25px; }
.applicant-page-banner small { display:block;margin-top:5px;color:#fff !important;font-size:12px; }
.applicant-process-card,.applicant-info-card { border:0;border-radius:4px;box-shadow:0 3px 14px rgba(15,23,42,.08); }
.applicant-process-line { display:grid;grid-template-columns:repeat(5,1fr);position:relative;margin-top:16px; }
.applicant-process-line::before { content:'';position:absolute;left:10%;right:10%;top:16px;height:3px;background:#e2e8f0; }
.applicant-process-line.progress-1::before,.applicant-process-line.progress-2::before { background:linear-gradient(90deg,#34d399 0 20%,#e2e8f0 20%); }
.applicant-process-line.progress-3::before { background:linear-gradient(90deg,#34d399 0 30%,#2563eb 30% 50%,#e2e8f0 50%); }
.applicant-process-line.progress-4::before { background:linear-gradient(90deg,#34d399 0 50%,#2563eb 50% 70%,#e2e8f0 70%); }
.applicant-process-line.progress-5::before { background:linear-gradient(90deg,#34d399 0 70%,#2563eb 70% 90%,#e2e8f0 90%); }
.applicant-process-step { position:relative;text-align:center;z-index:1; }
.applicant-process-dot { width:34px;height:34px;border-radius:50%;margin:0 auto 8px;background:white;border:3px solid white;box-shadow:0 0 0 1px #cbd5e1;display:grid;place-items:center;color:#94a3b8;font-weight:700; }
.applicant-process-step.done .applicant-process-dot { background:#34d399;box-shadow:0 0 0 1px #34d399;color:white; }
.applicant-process-step.current .applicant-process-dot { background:#2563eb;box-shadow:0 0 0 1px #2563eb;color:white; }
.applicant-process-step small { display:block;color:#475569;font-size:12px;line-height:1.2;font-weight:600; }
.applicant-package-photo { width:104px;height:64px;object-fit:cover;border-radius:5px;flex:none; }
.applicant-package-layout { display:flex;gap:12px;align-items:flex-start; }
.applicant-info-card .btn-primary { min-height:38px;border-radius:5px;display:grid;place-items:center;font-size:13px;font-weight:600; }
.applicant-package-layout + .btn-primary { margin-top:14px; }
.applicant-package-layout + .btn-primary:disabled { margin-top:14px; }
.applicant-history-card { height:100%;border:1px solid #e8edf4;border-radius:10px;box-shadow:0 3px 12px rgba(15,23,42,.045); }
.applicant-history-number { color:#1d4ed8;background:#eff6ff;border-radius:6px;padding:5px 8px;font-family:monospace;font-size:12px;font-weight:700; }
.applicant-history-card .btn { min-height:36px;border-radius:5px;font-size:12px;font-weight:600; }
.applicant-history-actions form { flex:1; }
.applicant-history-actions form .btn { width:100%; }
.applicant-progress-line { position:absolute;left:10%;right:10%;top:16px;height:3px;overflow:hidden;background:#e2e8f0; }
.applicant-progress-line span { display:block;height:100%;width:40%;background:#34d399; }
@media (max-width:768px) { .applicant-page-banner { padding:18px; }.applicant-process-line { overflow-x:auto;grid-template-columns:repeat(5,86px);padding-bottom:4px; }.applicant-process-line::before { width:344px;right:auto;left:43px; } }
</style>
<div class="applicant-page-banner mb-3">
    <div><h2>Selamat Datang,</h2><h2>Calon Member SI-GYM</h2><small>Daftarkan diri Anda sekarang untuk memulai hidup sehat.</small></div>
</div>
@if($registration)
@php
    $currentStep = match ($registration->status) {
        'Disetujui' => 4,
        'Menunggu Pembayaran' => 5,
        'Ditolak' => 3,
        default => 3,
    };
@endphp
<div class="card applicant-process-card mb-3">
    <div class="card-body px-4 py-3">
        <h6 class="fw-bold mb-0">Proses Pendaftaran Anda</h6>
        <div class="applicant-process-line progress-{{ $currentStep }}">
            @foreach([['Akun<br>Dibuat',1],['Formulir<br>Dikirim',2],['Menunggu<br>Verifikasi',3],['Disetujui',4],['Menunggu<br>Pembayaran',5]] as [$label,$number])
            <div class="applicant-process-step {{ $number < $currentStep ? 'done' : ($number === $currentStep ? 'current' : '') }}">
                <div class="applicant-process-dot">
                    @if($number < $currentStep)<i data-lucide="check" style="width:18px"></i>@else{{ $number }}@endif
                </div>
                <small>{!! $label !!}</small>
            </div>
            @endforeach
        </div>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card applicant-info-card h-100"><div class="card-body p-4 d-flex flex-column">
            <h6 class="fw-bold mb-3">Informasi Pendaftaran</h6>
            <dl class="row mb-3 small">
                <dt class="col-sm-4 text-muted fw-normal">Nomor Pendaftaran</dt><dd class="col-sm-8 fw-semibold">{{ $registration->registration_number }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Tanggal Daftar</dt><dd class="col-sm-8">{{ optional($registration->created_at)->translatedFormat('d F Y') }}</dd>
            </dl>
            <a href="{{ route('registrations.show.me', $registration) }}" class="btn btn-primary w-100 mt-auto">Lihat Detail</a>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card applicant-info-card h-100"><div class="card-body p-4 d-flex flex-column">
            <h6 class="fw-bold mb-3">Paket yang Dipilih</h6>
            <div class="applicant-package-layout">
                <img class="applicant-package-photo" src="{{ asset('images/gym-login.jpg') }}" alt="Paket membership">
                <div><strong>{{ $registration->package->name }}</strong><div class="text-muted small mt-1">Durasi: {{ $registration->package->duration }} Bulan</div><div class="mt-2">Harga: Rp {{ number_format($registration->package->price,0,',','.') }}</div></div>
            </div>
            @if(in_array($registration->status, ['Menunggu', 'Menunggu Verifikasi'], true) && $registration->payments_count === 0)
            <a href="{{ route('registrations.edit.me', $registration) }}" class="btn btn-primary w-100 mt-auto">Ubah Paket</a>
            @else
            <button class="btn btn-primary w-100 mt-auto" disabled>Ubah Paket</button>
            @endif
        </div></div>
    </div>
</div>
@else
<div class="card applicant-info-card"><div class="card-body p-5 text-center"><h5 class="fw-bold">Belum Ada Pendaftaran</h5><p class="text-muted">Pilih paket dan lengkapi formulir untuk memulai proses keanggotaan.</p><a href="{{ route('registrations.create') }}" class="btn btn-primary">Mulai Pendaftaran</a></div></div>
@endif
<section class="mt-4" aria-labelledby="registration-history-title">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h5 id="registration-history-title" class="fw-bold mb-1">Riwayat Pendaftaran</h5>
            <p class="text-muted small mb-0">Lihat dan kelola pengajuan pendaftaran Anda.</p>
        </div>
        @if($registrations->isEmpty())
            <a href="{{ route('registrations.create') }}" class="btn btn-primary btn-sm">Mulai Pendaftaran</a>
        @endif
    </div>
    <div class="row g-3">
        @forelse($registrations as $item)
            @php
                $itemCanBeChanged = in_array($item->status, ['Menunggu', 'Menunggu Verifikasi'], true)
                    && $item->payments_count === 0;
                $statusClass = match ($item->status) {
                    'Disetujui' => 'text-bg-success',
                    'Ditolak' => 'text-bg-danger',
                    default => 'text-bg-warning',
                };
            @endphp
            <div class="col-md-6 col-xl-4">
                <article class="card applicant-history-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <span class="applicant-history-number">{{ $item->registration_number }}</span>
                            <span class="badge {{ $statusClass }}">{{ $item->status }}</span>
                        </div>
                        <div class="d-flex justify-content-between gap-3 mt-3">
                            <div>
                                <div class="text-muted small">Paket Membership</div>
                                <div class="fw-semibold">{{ $item->package?->name ?? '-' }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Tanggal Daftar</div>
                                <div class="fw-medium">{{ $item->created_at?->translatedFormat('d M Y') ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="applicant-history-actions d-flex gap-2 mt-3">
                            <a href="{{ route('registrations.show.me', $item) }}" class="btn btn-primary flex-fill">Lihat Detail</a>
                            @if($itemCanBeChanged)
                                <a href="{{ route('registrations.edit.me', $item) }}" class="btn btn-outline-primary flex-fill">Ubah</a>
                                <form method="POST" action="{{ route('registrations.cancel', $item) }}" onsubmit="return confirm('Batalkan pendaftaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger" type="submit">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="card applicant-info-card">
                    <div class="card-body text-center py-4 text-muted">Belum ada riwayat pendaftaran.</div>
                </div>
            </div>
        @endforelse
    </div>
    <div class="mt-3">{{ $registrations->links() }}</div>
</section>
@else
<div class="mb-4">
    <h1 class="page-title mb-1">{{ auth()->user()->role === 'admin' ? 'Kelola Pendaftaran Calon Member' : 'Data Pendaftar Gym' }}</h1>
    <p class="text-muted mb-0">{{ auth()->user()->role === 'admin' ? 'Verifikasi, ubah, dan kelola pendaftaran calon member.' : 'Pantau perkembangan calon member secara read-only.' }}</p>
</div>
<div class="card crud-toolbar bg-white mb-3"><div class="card-body py-3"><form method="GET" class="d-flex flex-wrap gap-2"><div class="input-group" style="max-width:320px"><span class="input-group-text bg-white border-end-0"><i data-lucide="search" style="width:17px"></i></span><input class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Cari nama, nomor, email, atau telepon..."></div><select name="status" class="form-select" style="width:auto"><option value="">Semua Status</option>@foreach(['Menunggu Verifikasi', 'Disetujui', 'Ditolak', 'Menunggu Pembayaran', 'Pembayaran Berhasil', 'Selesai'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select><button class="btn btn-primary">Filter</button></form></div></div>
<style>.registration-management-card{border:0;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.055);transition:.2s}.registration-management-card:hover{transform:translateY(-3px)}.registration-number{font-family:monospace;color:#2563eb;background:#eef4ff;border-radius:8px;padding:5px 8px;font-size:12px}</style>
<div class="row g-3">
@forelse($registrations as $registration)
<div class="col-md-6 col-xl-4"><div class="card registration-management-card h-100"><div class="card-body p-4">
<div class="d-flex justify-content-between gap-2"><div class="registration-number">{{ $registration->registration_number }}</div><span class="badge {{ $registration->status === 'Disetujui' ? 'text-bg-success' : ($registration->status === 'Ditolak' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ $registration->status }}</span></div>
<div class="d-flex align-items-center gap-3 mt-3"><div class="rounded-circle bg-primary-subtle text-primary fw-bold d-grid place-items-center" style="width:44px;height:44px">{{ strtoupper(substr($registration->user?->name ?? 'P',0,1)) }}</div><div><h6 class="fw-bold mb-1">{{ $registration->user?->name ?? '-' }}</h6><div class="text-muted small">{{ $registration->created_at->format('d M Y') }}</div></div></div>
<div class="border-top mt-3 pt-3"><div class="text-muted small">Paket</div><div class="fw-semibold">{{ $registration->package?->name ?? '-' }}</div></div>
<div class="d-flex gap-2 mt-3">@if(auth()->user()->role === 'owner')<a class="btn btn-primary flex-grow-1" href="{{ route('owner.registration.show', $registration) }}"><i data-lucide="eye" style="width:15px"></i> Detail</a>@else<a class="btn btn-primary flex-grow-1" href="{{ route('registrations.show', $registration) }}"><i data-lucide="eye" style="width:15px"></i> Detail</a><a class="btn btn-outline-primary" href="{{ route('registrations.edit', $registration) }}"><i data-lucide="pencil" style="width:15px"></i></a>@endif</div>
</div></div></div>
@empty<div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">Belum ada pendaftaran.</div></div></div>@endforelse
</div><div class="mt-3">{{ $registrations->links() }}</div>
@endif
@endsection
