@extends('layouts.app')
@section('title', 'Pembayaran')
@section('content')
<style>
.payment-card{height:100%;border:0;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.06);transition:transform .18s ease,box-shadow .18s ease}
.payment-card:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(15,23,42,.09)}
.payment-card-icon{width:42px;height:42px;border-radius:13px;background:#e8f1ff;color:#2563eb;display:grid;place-items:center}
.payment-amount{font-size:20px;font-weight:800;color:#172033}
.payment-invoice{font-family:monospace;font-size:12px;color:#2563eb}
.payment-card .btn{min-height:36px}
</style>
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">{{ auth()->user()->role === 'admin' ? 'Kelola Pembayaran' : (auth()->user()->role === 'owner' ? 'Data Pembayaran' : (auth()->user()->role === 'member' ? 'Riwayat Pembayaran' : 'Pembayaran Saya')) }}</h1>
        <p class="text-muted mb-0">{{ auth()->user()->role === 'admin' ? 'Verifikasi transaksi dan kelola seluruh pembayaran member.' : (auth()->user()->role === 'owner' ? 'Pantau seluruh transaksi pembayaran sebagai data read-only.' : 'Lihat status dan lanjutkan proses pembayaran Anda.') }}</p>
    </div>
    @if(auth()->user()->role === 'admin')<a class="btn btn-primary px-3" href="{{ route('payments.create') }}"><i data-lucide="plus" class="me-1"></i>Tambah Pembayaran</a>@endif
</div>
<div class="card crud-toolbar bg-white mb-3"><div class="card-body py-3"><form method="GET" action="{{ route(match(auth()->user()->role) { 'owner' => 'owner.payments', 'member' => 'member.payments', 'pendaftar' => 'registrant.payments', default => 'payments.index' }) }}" class="d-flex flex-wrap gap-2">
<div class="input-group" style="max-width:280px"><span class="input-group-text bg-white border-end-0"><i data-lucide="search" style="width:17px"></i></span><input class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Cari invoice, nama, atau paket..." aria-label="Cari pembayaran"></div>
<select name="status" class="form-select" style="width:auto" aria-label="Filter status pembayaran"><option value="">Semua Status</option>@foreach(['Menunggu Pembayaran','Menunggu Verifikasi','Lunas','Ditolak','Dibatalkan'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
<select name="method" class="form-select" style="width:auto" aria-label="Filter metode pembayaran"><option value="">Semua Metode</option>@foreach(['Transfer','Cash','QRIS'] as $method)<option value="{{ $method }}" @selected(request('method') === $method)>{{ $method }}</option>@endforeach</select>
<input type="date" name="from" class="form-control" style="width:auto" value="{{ request('from') }}" aria-label="Tanggal mulai">
<input type="date" name="to" class="form-control" style="width:auto" value="{{ request('to') }}" aria-label="Tanggal akhir">
<button class="btn btn-primary" type="submit">Filter</button>
@if(collect(['search','status','method','from','to','month','year'])->contains(fn ($key) => request()->filled($key)))<a class="btn btn-light border" href="{{ route(match(auth()->user()->role) { 'owner' => 'owner.payments', 'member' => 'member.payments', 'pendaftar' => 'registrant.payments', default => 'payments.index' }) }}">Reset</a>@endif
</form></div></div>
<div class="row g-3">
@forelse($payments as $index => $payment)
<div class="col-md-6 col-xl-4"><article class="card payment-card"><div class="card-body p-4">
<div class="d-flex justify-content-between align-items-start gap-2"><div class="d-flex align-items-center gap-2"><span class="payment-card-icon"><i data-lucide="receipt"></i></span><div><div class="text-muted small">Invoice</div><div class="payment-invoice fw-bold">{{ $payment->invoice_number }}</div></div></div><span class="badge text-bg-{{ $payment->status === 'Lunas' ? 'success' : ($payment->status === 'Ditolak' ? 'danger' : 'warning') }}">{{ $payment->status }}</span></div>
<div class="border-top mt-3 pt-3"><div class="fw-semibold">{{ $payment->user->name }}</div><div class="text-muted small">{{ $payment->package->name }} · {{ optional($payment->payment_date)->format('d M Y') ?? 'Tanggal belum tersedia' }}</div></div>
<div class="payment-amount mt-3 mb-3">Rp {{ number_format($payment->amount,0,',','.') }}</div>
<div class="d-flex gap-2">
        @if(in_array(auth()->user()->role, ['member', 'pendaftar'], true))
            @php
                $prefix = auth()->user()->role === 'member' ? 'member.' : 'registrant.';
            @endphp
            @if($payment->status !== 'Lunas')
                <a class="btn btn-sm btn-outline-primary" href="{{ route($prefix.'payments.checkout', $payment) }}">Bayar</a>
            @endif
            <a class="btn btn-primary flex-grow-1" href="{{ route($prefix.'payments.show', $payment) }}"><i data-lucide="eye" style="width:15px"></i> Detail</a>
        @elseif(auth()->user()->role === 'owner')
            <a class="btn btn-primary flex-grow-1" href="{{ route('owner.payment.show', $payment) }}"><i data-lucide="eye" style="width:15px"></i> Detail</a>
        @else
            <a class="btn btn-primary flex-grow-1" href="{{ route('payments.show', $payment) }}"><i data-lucide="eye" style="width:15px"></i> Detail</a>
        @endif
        @if(auth()->user()->role === 'admin')
            <a class="btn btn-outline-primary" href="{{ route('payments.edit', $payment) }}"><i data-lucide="pencil" style="width:15px"></i></a>
            @if(! $payment->membership)
                <form method="POST" action="{{ route('payments.destroy', $payment) }}" class="d-inline ms-1" onsubmit="return confirm('Hapus pembayaran ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger"><i data-lucide="trash-2" style="width:15px"></i></button>
                </form>
            @endif
        @endif
</div>
</div></article></div>
@empty
<div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">{{ request()->filled('search') ? 'Pembayaran tidak ditemukan.' : 'Belum ada data pembayaran.' }}</div></div></div>
@endforelse
</div><div class="mt-3">{{ $payments->links() }}</div>
@endsection
