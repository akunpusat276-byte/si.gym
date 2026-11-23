@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<style>
.admin-metric,.admin-panel { border:0;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,.07); }
.admin-metric { color:#fff;overflow:hidden; }
.admin-metric .metric-label,.admin-metric .metric-note { color:rgba(255,255,255,.82); }
.admin-metric .metric-icon { width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:rgba(255,255,255,.18); }
.admin-metric .metric-value { font-size:clamp(20px,2.1vw,26px);font-weight:750;line-height:1.15; }
.admin-tabs { display:flex;gap:4px;border-bottom:1px solid #e8edf4;overflow-x:auto; }
.admin-tab { border:0;background:transparent;color:#64748b;padding:10px 12px;font-size:12px;font-weight:650;white-space:nowrap;border-bottom:2px solid transparent; }
.admin-tab.active { color:#2563eb;border-color:#2563eb; }
.admin-panel-content { display:none; }
.admin-panel-content.active { display:block; }
.admin-compact-table th { color:#718096;font-size:11px;white-space:nowrap; }
.admin-compact-table td { font-size:12px;vertical-align:middle; }
.admin-chart-wrap { height:252px;position:relative; }
@media (max-width:575.98px) { .admin-compact-table th,.admin-compact-table td { padding:.55rem .45rem; }.admin-chart-wrap { height:210px; } }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title mb-1">Dashboard Admin</h1>
        <p class="text-muted mb-0">Kelola aktivitas SI-GYM dan pantau perkembangan member.</p>
    </div>
    <a href="{{ route('payments.create') }}" class="btn btn-primary px-3">
        <i data-lucide="plus" class="me-1"></i>Pembayaran Baru
    </a>
</div>

<div class="row g-3">
    @foreach([
        ['Total Member', number_format($totalMembers), 'users', '#0879e8', 'Data member gym'],
        ['Pendaftar', number_format($totalRegistrants), 'user-round-plus', '#19b89c', 'Akun calon member'],
        ['Member Aktif', number_format($activeMembers), 'circle-check', '#7954d8', 'Membership berjalan'],
        ['Total Pendapatan', 'Rp '.number_format($totalRevenue, 0, ',', '.'), 'wallet', '#f7941d', 'Seluruh pembayaran lunas'],
    ] as [$label, $value, $icon, $color, $note])
        <div class="col-sm-6 col-xl-3">
            <section class="card admin-metric h-100" style="background:{{ $color }}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="metric-label small mb-2">{{ $label }}</div>
                            <div class="metric-value text-truncate">{{ $value }}</div>
                            <div class="metric-note small mt-1">{{ $note }}</div>
                        </div>
                        <span class="metric-icon flex-shrink-0"><i data-lucide="{{ $icon }}"></i></span>
                    </div>
                </div>
            </section>
        </div>
    @endforeach
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <a href="{{ route('registrations.index', ['status' => 'Menunggu Verifikasi']) }}" class="card admin-panel text-decoration-none text-reset h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div><h2 class="h6 fw-bold mb-1">Notifikasi Pendaftaran</h2><p class="small text-muted mb-0">Menunggu verifikasi Admin</p></div>
                <span class="badge rounded-pill text-bg-warning fs-6">{{ number_format($pendingRegistrations) }}</span>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="{{ route('payments.index', ['status' => 'Menunggu Verifikasi']) }}" class="card admin-panel text-decoration-none text-reset h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div><h2 class="h6 fw-bold mb-1">Notifikasi Pembayaran</h2><p class="small text-muted mb-0">Bukti transfer menunggu verifikasi</p></div>
                <span class="badge rounded-pill text-bg-danger fs-6">{{ number_format($pendingPayments) }}</span>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-xl-8">
        <section class="card admin-panel h-100">
            <div class="card-header bg-white border-0 px-3 pt-3">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <h2 class="h6 fw-bold mb-1">Data Terbaru</h2>
                        <p class="text-muted small mb-0">Pendaftaran, pembayaran, dan membership terkini.</p>
                    </div>
                    <a href="{{ route('registrations.index') }}" class="small text-decoration-none text-nowrap">Lihat Semua <i data-lucide="arrow-up-right" style="width:14px"></i></a>
                </div>
                <div class="admin-tabs mt-3" role="tablist" aria-label="Kategori data terbaru">
                    <button class="admin-tab active" type="button" role="tab" aria-selected="true" data-panel="admin-registrations">Pendaftaran</button>
                    <button class="admin-tab" type="button" role="tab" aria-selected="false" data-panel="admin-payments">Pembayaran</button>
                    <button class="admin-tab" type="button" role="tab" aria-selected="false" data-panel="admin-memberships">Membership</button>
                </div>
            </div>
            <div class="card-body p-3">
                <div id="admin-registrations" class="admin-panel-content active" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table admin-compact-table align-middle mb-0">
                            <thead><tr><th>Pendaftar</th><th>Tanggal</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                            <tbody>
                                @forelse($registrations as $registration)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $registration->user?->name ?? '-' }}</span><small class="d-block text-muted">{{ $registration->package?->name ?? '-' }}</small></td>
                                        <td>{{ $registration->created_at?->format('d-m-Y') ?? '-' }}</td>
                                        <td><span class="badge {{ $registration->status === 'Disetujui' ? 'text-bg-success' : ($registration->status === 'Ditolak' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ $registration->status }}</span></td>
                                        <td class="text-end"><a href="{{ route('registrations.show', $registration) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pendaftaran.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="admin-payments" class="admin-panel-content" role="tabpanel" hidden>
                    <div class="table-responsive">
                        <table class="table admin-compact-table align-middle mb-0">
                            <thead><tr><th>Transaksi</th><th>Tanggal</th><th>Jumlah</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $payment->user?->name ?? $payment->invoice_number }}</span><small class="d-block text-muted">{{ $payment->invoice_number }}</small></td>
                                        <td>{{ $payment->payment_date?->format('d-m-Y') ?? '-' }}</td>
                                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                        <td><span class="badge {{ $payment->status === 'Lunas' ? 'text-bg-success' : ($payment->status === 'Ditolak' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ $payment->status }}</span></td>
                                        <td class="text-end"><a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pembayaran.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="admin-memberships" class="admin-panel-content" role="tabpanel" hidden>
                    <div class="table-responsive">
                        <table class="table admin-compact-table align-middle mb-0">
                            <thead><tr><th>Member</th><th>Paket</th><th>Berakhir</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                            <tbody>
                                @forelse($recentMemberships as $membership)
                                    <tr>
                                        <td class="fw-semibold">{{ $membership->member?->user?->name ?? '-' }}</td>
                                        <td>{{ $membership->package?->name ?? '-' }}</td>
                                        <td>{{ $membership->end_date?->format('d-m-Y') ?? '-' }}</td>
                                        <td><span class="badge {{ $membership->calculated_status === 'Aktif' ? 'text-bg-success' : 'text-bg-warning' }}">{{ $membership->calculated_status }}</span></td>
                                        <td class="text-end"><a href="{{ route('memberships.show', $membership) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada membership.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="card admin-panel h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div><h2 class="h6 fw-bold mb-1">Statistik Member</h2><p class="text-muted small mb-0">Pendaftaran member baru</p></div>
                    <span class="badge text-bg-light border">7 Hari Terakhir</span>
                </div>
                <div class="admin-chart-wrap mt-3"><canvas id="memberActivityChart" aria-label="Grafik member baru tujuh hari terakhir"></canvas></div>
            </div>
        </section>
    </div>
</div>

<section class="card admin-panel mt-3">
    <div class="card-header bg-white border-0 px-3 pt-3">
        <h2 class="h6 fw-bold mb-1">Membership Akan Berakhir</h2>
        <p class="small text-muted mb-0">Member dengan masa aktif berakhir dalam tujuh hari.</p>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table admin-compact-table align-middle mb-0">
                <thead><tr><th>Member</th><th>Paket</th><th>Tanggal Berakhir</th><th>Sisa</th></tr></thead>
                <tbody>
                    @forelse($expiringMemberships as $membership)
                        <tr>
                            <td>{{ $membership->member?->user?->name ?? '-' }}</td>
                            <td>{{ $membership->package?->name ?? '-' }}</td>
                            <td>{{ $membership->end_date?->format('d-m-Y') ?? '-' }}</td>
                            <td><span class="badge text-bg-warning">{{ $membership->remaining_days }} hari</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada membership yang segera berakhir.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const activityCanvas = document.getElementById('memberActivityChart');
if (activityCanvas) {
    new Chart(activityCanvas, {
        type: 'line',
        data: {
            labels: @json($memberActivityLabels),
            datasets: [{
                data: @json($memberActivityChart),
                borderColor: '#1768c4',
                backgroundColor: 'rgba(23,104,196,.12)',
                fill: true,
                tension: .35,
                pointRadius: 3,
                pointBackgroundColor: '#1768c4',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf2f7' } },
                x: { grid: { display: false } },
            },
        },
    });
}

document.querySelectorAll('.admin-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.admin-tab').forEach((item) => {
            item.classList.remove('active');
            item.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.admin-panel-content').forEach((panel) => {
            panel.classList.remove('active');
            panel.hidden = true;
        });
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');
        const panel = document.getElementById(tab.dataset.panel);
        panel.hidden = false;
        panel.classList.add('active');
    });
});
</script>
@endpush
@endsection
