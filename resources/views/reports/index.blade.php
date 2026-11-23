@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
<style>
.report-card{border:0;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.06)}
@media print{.no-print,.sidebar,.topbar{display:none!important}.main-content{margin:0!important;padding:0!important}.report-card{box-shadow:none;border:1px solid #ddd}.table{font-size:11px}}
</style>
<div class="page-header no-print">
    <div>
        <h1 class="page-title mb-1">Laporan Gym</h1>
        <p class="text-muted mb-0">{{ auth()->user()->role === 'owner' ? 'Laporan operasional read-only SI-GYM.' : 'Laporan operasional berdasarkan data SI-GYM.' }}</p>
    </div>
</div>
<section class="card report-card mb-3 no-print">
    <div class="card-body">
        <form method="GET" action="{{ route(auth()->user()->role === 'owner' ? 'owner.reports' : 'reports.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-6 col-lg-2">
                <label for="category" class="form-label">Kategori</label>
                <select id="category" name="category" class="form-select">
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="search" class="form-label">Pencarian</label>
                <input id="search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Nama atau kode">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="from" class="form-label">Dari Tanggal</label>
                <input id="from" type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="to" class="form-label">Sampai Tanggal</label>
                <input id="to" type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-sm-6 col-lg-1">
                <label for="month" class="form-label">Bulan</label>
                <select id="month" name="month" class="form-select">
                    <option value="">Semua</option>
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" @selected((int) ($filters['month'] ?? 0) === $month)>{{ $month }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-lg-1">
                <label for="year" class="form-label">Tahun</label>
                <input id="year" type="number" name="year" min="2000" max="2100" class="form-control" value="{{ $filters['year'] ?? '' }}">
            </div>
            <div class="col-lg-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit">Filter</button>
                <a class="btn btn-light border" href="{{ route(auth()->user()->role === 'owner' ? 'owner.reports' : 'reports.index') }}">Reset</a>
            </div>
        </form>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <a class="btn btn-outline-success" href="{{ route(auth()->user()->role === 'owner' ? 'owner.reports.export' : 'reports.export', request()->query()) }}"><i data-lucide="file-spreadsheet" class="me-1"></i>Unduh CSV (Excel)</a>
            <button type="button" class="btn btn-outline-primary" onclick="window.print()"><i data-lucide="printer" class="me-1"></i>Cetak / Simpan PDF</button>
        </div>
    </div>
</section>
<section class="card report-card">
    <div class="card-header bg-white border-0 px-4 pt-4 d-flex flex-wrap justify-content-between gap-2">
        <div><h2 class="h5 fw-bold mb-1">Laporan {{ $categories[$category] }}</h2><p class="small text-muted mb-0">{{ number_format($rows->total()) }} data ditemukan</p></div>
        @if($totalAmount !== null)
            <div class="fw-bold">Total: Rp {{ number_format((float) $totalAmount, 0, ',', '.') }}</div>
        @endif
    </div>
    <div class="card-body px-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Nomor / Kode</th><th>Tanggal</th><th>Nama</th><th>Detail</th><th>Nominal</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row[0] }}</td>
                            <td>{{ $row[1] ?: '-' }}</td>
                            <td>{{ $row[2] ?: '-' }}</td>
                            <td>{{ $row[3] ?: '-' }}</td>
                            <td>{{ $row[4] === '' ? '-' : 'Rp '.number_format((float) $row[4], 0, ',', '.') }}</td>
                            <td><span class="badge text-bg-light border">{{ $row[5] ?: '-' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Tidak ada data untuk filter yang dipilih.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 no-print">{{ $rows->links() }}</div>
    </div>
</section>
@endsection
