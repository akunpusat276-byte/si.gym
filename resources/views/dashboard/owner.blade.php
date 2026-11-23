@extends('layouts.app')
@section('title', 'Dashboard Pemilik')
@section('content')
<style>
.owner-stat,.owner-chart-card{border:0;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,.07)}
.owner-stat{color:#fff;overflow:hidden}
.owner-stat small,.owner-stat .owner-value-note{color:rgba(255,255,255,.82)!important}
.owner-icon{width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:rgba(255,255,255,.18);color:#fff}
.owner-value{font-size:clamp(20px,2.1vw,26px);font-weight:750;line-height:1.15}
.owner-chart-wrap{height:250px;position:relative}
.read-only-pill{color:#c2410c;background:#fff7ed;border:1px solid #fed7aa;border-radius:999px;padding:7px 12px;font-weight:700;font-size:12px}
</style>
<div class="page-header">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><h1 class="page-title mb-0">Dashboard Pemilik</h1><span class="read-only-pill"><i data-lucide="lock-keyhole" style="width:14px"></i> READ ONLY</span></div>
        <p class="text-muted mb-0">Pemantauan performa dan pertumbuhan bisnis gym.</p>
    </div>
    <form method="GET" action="{{ route('owner.dashboard') }}" class="d-flex gap-2">
        <label for="period" class="visually-hidden">Periode grafik</label>
        <select id="period" name="period" class="form-select" onchange="this.form.submit()">
            @foreach($periodOptions as $value => $label)
                <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-white border bg-white" type="submit"><i data-lucide="filter" class="me-1"></i>Filter</button>
    </form>
</div>
<div class="row g-3">
    @foreach([['Total Member',$totalMembers,'users','#0879e8'],['Member Aktif',$activeMembers,'circle-check','#19a98b'],['Akan Berakhir',$expiringMembers,'calendar-clock','#7954d8'],['Pendapatan Bulanan','Rp '.number_format($totalRevenue,0,',','.'),'wallet','#12a8ad']] as [$label,$value,$icon,$color])
        <div class="col-sm-6 col-xl-3"><section class="card owner-stat h-100" style="background:{{ $color }}"><div class="card-body p-3"><div class="d-flex justify-content-between align-items-center gap-2"><div><small>{{ $label }}</small><div class="owner-value mt-2">{{ $value }}</div><small class="owner-value-note">Ringkasan terkini</small></div><div class="owner-icon"><i data-lucide="{{ $icon }}"></i></div></div></div></section></div>
    @endforeach
</div>
<div class="row g-3 mt-2">
    <div class="col-xl-8"><section class="card owner-chart-card h-100"><div class="card-body p-4"><div><h2 class="h5 fw-bold">Grafik Pendapatan</h2><p class="text-muted small">Pembayaran lunas · {{ $periodOptions[$period] }}</p></div><div class="owner-chart-wrap"><canvas id="ownerRevenueChart" aria-label="Grafik pendapatan sesuai periode"></canvas></div></div></section></div>
    <div class="col-xl-4"><section class="card owner-chart-card h-100"><div class="card-body p-4"><h2 class="h5 fw-bold">Status Member</h2><p class="text-muted small">Aktif, akan berakhir, dan expired</p><div class="owner-chart-wrap"><canvas id="statusChart" aria-label="Diagram status member"></canvas></div></div></section></div>
</div>
<div class="row g-3 mt-2">
    <div class="col-xl-8"><section class="card owner-chart-card h-100"><div class="card-body p-4"><h2 class="h5 fw-bold">Pertumbuhan Member &amp; Pendaftaran</h2><p class="text-muted small">{{ $periodOptions[$period] }}</p><div class="owner-chart-wrap"><canvas id="growthChart" aria-label="Grafik pertumbuhan member dan pendaftaran"></canvas></div></div></section></div>
    <div class="col-xl-4"><section class="card owner-chart-card h-100"><div class="card-body p-4"><h2 class="h5 fw-bold">Metode Pembayaran</h2><p class="text-muted small">Transaksi dalam {{ $periodOptions[$period] }}</p><div class="owner-chart-wrap"><canvas id="paymentMethodChart" aria-label="Diagram metode pembayaran"></canvas></div></div></section></div>
</div>
<section class="card owner-chart-card mt-3">
    <div class="card-header bg-white border-0 px-4 pt-4 d-flex justify-content-between"><h2 class="h5 fw-bold mb-0">Laporan Terbaru</h2><a href="{{ route('owner.reports') }}" class="btn btn-sm btn-outline-primary"><i data-lucide="download" style="width:14px"></i> Download</a></div>
    <div class="card-body px-4"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Laporan</th><th>Tanggal</th><th>Nilai</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($recentPayments as $payment)
            <tr><td class="fw-semibold">{{ $payment->invoice_number }}</td><td>{{ optional($payment->payment_date)->format('d M Y') ?? '-' }}</td><td>Rp {{ number_format($payment->amount,0,',','.') }}</td><td><span class="badge {{ $payment->status === 'Lunas' ? 'text-bg-success' : 'text-bg-warning' }}">{{ $payment->status }}</span></td><td class="text-end"><a href="{{ route('owner.payment.show', $payment) }}" class="btn btn-sm btn-outline-primary">Detail</a></td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada laporan.</td></tr>
        @endforelse
    </tbody></table></div></div>
</section>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const ownerLabels = @json($chartLabels);
new Chart(document.getElementById('ownerRevenueChart'), {
    type: 'bar',
    data: { labels: ownerLabels, datasets: [{ label: 'Pendapatan', data: @json($revenueSeries), backgroundColor: '#1768c4', borderRadius: 6, maxBarThickness: 42 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#edf1f7' } }, x: { grid: { display: false } } } },
});
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: { labels: ['Aktif', 'Akan Berakhir', 'Expired'], datasets: [{ data: [{{ $stableActiveMembers }}, {{ $expiringMembers }}, {{ $expiredMembers }}], backgroundColor: ['#1768c4', '#f59e0b', '#ef4444'], borderWidth: 0 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom' } } },
});
new Chart(document.getElementById('growthChart'), {
    type: 'line',
    data: { labels: ownerLabels, datasets: [
        { label: 'Member Baru', data: @json($memberGrowthSeries), borderColor: '#1768c4', backgroundColor: 'rgba(23,104,196,.12)', tension: .35 },
        { label: 'Pendaftaran', data: @json($registrationGrowthSeries), borderColor: '#19a98b', backgroundColor: 'rgba(25,169,139,.10)', tension: .35 },
    ] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
});
new Chart(document.getElementById('paymentMethodChart'), {
    type: 'doughnut',
    data: { labels: @json($paymentMethodCounts->keys()->values()), datasets: [{ data: @json($paymentMethodCounts->values()), backgroundColor: ['#1768c4', '#19a98b', '#f59e0b', '#7954d8'], borderWidth: 0 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom' } } },
});
</script>
@endpush
@endsection
