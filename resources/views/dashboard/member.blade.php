@extends('layouts.app')
@section('title', 'Dashboard Member')
@section('content')
<style>
.member-dashboard { --member-blue: #1768c4; --member-ink: #172033; }
.member-welcome { min-height:168px;border-radius:16px;background-image:linear-gradient(90deg,rgba(9,24,49,.96),rgba(9,24,49,.62) 58%,rgba(9,24,49,.18)),url("{{ asset('images/sigym-reference.png') }}");background-size:100% 100%,175% auto;background-position:center,left 43%;background-repeat:no-repeat;color:#fff;padding:26px 30px;display:flex;align-items:center;box-shadow:0 10px 24px rgba(15,23,42,.12); }
.member-welcome h1 { font-size:clamp(23px,3vw,30px);letter-spacing:-.7px; }
.member-dashboard-card { border:0;border-radius:15px;box-shadow:0 5px 20px rgba(15,23,42,.065); }
.member-overview { min-height:174px; }
.member-avatar { width:82px;height:82px;flex:0 0 82px;border-radius:50%;overflow:hidden;display:grid;place-items:center;background:linear-gradient(145deg,#e8f1ff,#cbdaf0);color:#6882a5; }
.member-avatar img { width:100%;height:100%;object-fit:cover; }
.member-avatar i { width:42px;height:42px; }
.member-details { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 24px; }
.member-detail { border-left:1px solid #e6ebf2;padding-left:16px;min-width:0; }
.member-detail small { display:block;color:#718096;font-size:11px;margin-bottom:3px; }
.member-detail strong { display:block;color:var(--member-ink);font-size:13px;overflow-wrap:anywhere; }
.member-time-card { min-height:174px;background:linear-gradient(145deg,#f0f7ff,#e2efff); }
.member-time-icon { width:44px;height:44px;border-radius:12px;background:white;color:var(--member-blue);display:grid;place-items:center; }
.member-time-icon i { width:23px;height:23px; }
.member-time-card .progress { height:8px;background:#c8d9ed; }
.member-time-card .progress-bar { background:var(--member-blue); }
.member-dashboard-card h2 { color:var(--member-ink);font-size:16px;font-weight:700; }
.member-dashboard-table thead th { color:#718096;font-size:11px;white-space:nowrap;border-bottom-color:#e8edf4; }
.member-dashboard-table tbody td { font-size:12px;white-space:nowrap;border-color:#edf1f6; }
.member-promo { min-height:100%;position:relative;overflow:hidden;border-radius:15px;background-image:linear-gradient(90deg,rgba(5,16,36,.88),rgba(5,16,36,.14)),url("{{ asset('images/sigym-reference.png') }}");background-size:100% 100%,auto 220%;background-position:center,16% 48%;background-repeat:no-repeat;color:white; }
.member-promo h2 { color:white;max-width:180px;font-size:23px;line-height:1.15; }
.member-promo .promo-line { width:48px;height:4px;border-radius:4px;background:#2583f7; }
@media (max-width: 767.98px) {
    .member-welcome { min-height:145px;padding:22px;background-size:100% 100%,auto 380%;background-position:center,18% 48%; }
    .member-overview { align-items:flex-start !important;flex-direction:column; }
    .member-details { width:100%;gap:14px 8px; }
    .member-detail { padding-left:10px; }
    .member-promo { min-height:210px; }
}
</style>
@php
    $membershipStatus = $membership?->calculated_status ?? 'Belum Aktif';
    $membershipStatusColor = match ($membershipStatus) {
        'Aktif' => 'success',
        'Akan Berakhir' => 'warning',
        'Kedaluwarsa' => 'secondary',
        default => 'primary',
    };
    $remainingDays = $membership?->remaining_days ?? 0;
    $totalMembershipDays = $membership?->start_date && $membership->end_date
        ? max($membership->start_date->diffInDays($membership->end_date), 1)
        : 1;
    $membershipProgress = $membership?->start_date && $membership->end_date
        ? min(100, max(0, $remainingDays / $totalMembershipDays * 100))
        : 0;
@endphp
<div class="member-dashboard">
    <section class="member-welcome mb-4">
        <div>
            <small class="text-white-50">Dashboard Member</small>
            <h1 class="fw-bold mb-1">Selamat Datang, {{ auth()->user()->name }}!</h1>
            <p class="mb-0 text-white-50">Tetap semangat berlatih dan jaga kesehatan tubuh Anda.</p>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <section class="card member-dashboard-card member-overview h-100">
                <div class="card-body p-4 d-flex align-items-center gap-4">
                    <div class="member-avatar">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="Foto {{ auth()->user()->name }}">
                        @else
                            <i data-lucide="user-round"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="mb-3"><span class="badge rounded-pill text-bg-{{ $membershipStatusColor }}">{{ $membershipStatus === 'Aktif' ? 'Member Aktif' : $membershipStatus }}</span></div>
                        <div class="member-details">
                            <div class="member-detail"><small>Kode Member</small><strong>{{ $member->member_code }}</strong></div>
                            <div class="member-detail"><small>Paket</small><strong>{{ $membership?->package?->name ?? 'Belum ada paket' }}</strong></div>
                            <div class="member-detail"><small>Mulai</small><strong>{{ $membership?->start_date?->format('d-m-Y') ?? '-' }}</strong></div>
                            <div class="member-detail"><small>Berakhir</small><strong>{{ $membership?->end_date?->format('d-m-Y') ?? '-' }}</strong></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="card member-dashboard-card member-time-card h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="member-time-icon"><i data-lucide="calendar-days"></i></span>
                        <div><small class="text-muted d-block">Sisa Masa Berlaku</small><strong class="fs-4 text-dark">{{ $membership ? $remainingDays.' Hari' : 'Tidak aktif' }}</strong></div>
                    </div>
                    <div class="progress" role="progressbar" aria-label="Sisa masa membership" aria-valuenow="{{ (int) round($membershipProgress) }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: {{ $membershipProgress }}%"></div>
                    </div>
                    @if(! $membership)<small class="text-muted mt-2">Pilih paket untuk memulai membership Anda.</small>@endif
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <section class="card member-dashboard-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="mb-0">Riwayat Pembayaran Terakhir</h2>
                        <a href="{{ route('member.payments') }}" class="small text-decoration-none">Lihat Semua <i data-lucide="arrow-right" style="width:14px"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table member-dashboard-table align-middle mb-0">
                            <thead><tr><th>Tanggal</th><th>Metode</th><th>Jumlah</th><th>Status</th></tr></thead>
                            <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date?->format('d-m-Y') ?? '-' }}</td>
                                    <td>{{ $payment->method }}</td>
                                    <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    <td><span class="badge rounded-pill text-bg-{{ $payment->status === 'Lunas' ? 'success' : ($payment->status === 'Ditolak' ? 'danger' : 'warning') }}">{{ $payment->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pembayaran.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="member-promo h-100 d-flex flex-column justify-content-end p-4">
                <h2 class="fw-bold">Train Hard<br>Be Stronger</h2>
                <span class="promo-line mb-3"></span>
                <a href="{{ route('member.memberships') }}" class="btn btn-primary btn-sm align-self-start">Lihat Membership <i data-lucide="arrow-right" style="width:14px"></i></a>
            </section>
        </div>
    </div>
</div>
@endsection
