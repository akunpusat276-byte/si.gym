@extends('layouts.app')
@section('title', 'Upload Bukti Transfer')
@section('content')
@php
    $prefix = auth()->user()->role === 'member' ? 'member.' : 'registrant.';
@endphp
<style>.upload-shell{max-width:980px;margin:auto}.upload-card{border:0;border-radius:18px;box-shadow:0 12px 35px rgba(15,23,42,.06)}.upload-card .card-body{padding:28px}.transfer-summary{border-radius:14px;background:#f5f9ff;border:1px solid #dbeafe}.upload-drop{border:2px dashed #cbd5e1;border-radius:14px;background:#fafcff;padding:28px;text-align:center}.upload-drop:hover{border-color:#2563eb;background:#f7faff}.submit-proof{height:50px;border-radius:10px;font-weight:700}@media(max-width:768px){.upload-card .card-body{padding:20px}}</style>
<div class="upload-shell">
    <a href="{{ route($prefix.'payments') }}" class="text-decoration-none text-secondary small">&larr; Pembayaran</a>
    <div class="d-flex align-items-center justify-content-between mb-4 mt-2"><div><h1 class="page-title mb-1">Konfirmasi Transfer</h1><div class="text-secondary">Unggah bukti pembayaran untuk proses verifikasi Admin.</div></div></div>
    <div class="row g-4">
    <div class="col-lg-8">
        <div class="card upload-card"><div class="card-body">
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @if(! $payment->bank_id)
                <div class="alert alert-warning">Pilih bank tujuan terlebih dahulu.</div>
                <a class="btn btn-primary" href="{{ route($prefix.'payments.checkout', $payment) }}">Pilih Bank</a>
            @else
                <div class="transfer-summary p-4 mb-4">
                    <div class="text-primary small fw-bold">TRANSFER KE</div>
                    <div class="d-flex justify-content-between gap-3 flex-wrap mt-2"><div><strong class="fs-5">{{ $payment->bank_name }}</strong><div class="font-monospace fs-5 fw-semibold mt-1">{{ $payment->bank_account_number }}</div><div class="text-secondary">{{ $payment->bank_account_holder }}</div></div><div class="text-end"><small class="text-secondary">Jumlah transfer</small><div class="fs-4 fw-bold text-primary">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div></div></div>
                </div>
                <form method="POST" action="{{ route($prefix.'payments.submit-proof', $payment) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nomor Invoice</label><input class="form-control" value="{{ $payment->invoice_number }}" readonly></div>
                        <div class="col-md-6"><label class="form-label">Tanggal Transfer</label><input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required></div>
                        <div class="col-md-6"><label class="form-label">Nama Pengirim</label><input name="sender_name" class="form-control" value="{{ old('sender_name', auth()->user()->name) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Bukti Transfer</label><div class="upload-drop"><i data-lucide="upload-cloud" class="text-primary mb-2"></i><input type="file" name="proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required></div></div>
                        <div class="col-12"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea><div class="form-text">JPG, JPEG, PNG, atau PDF. Maksimal 2 MB.</div></div>
                    </div>
                    <button class="btn btn-primary submit-proof w-100 mt-4">Kirim untuk Verifikasi Admin</button>
                </form>
            @endif
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card upload-card"><div class="card-body">
            <h2 class="fs-6 fw-bold mb-3">Langkah Verifikasi</h2>
            <div class="d-flex gap-3 mb-3"><span class="badge rounded-pill text-bg-primary">1</span><div><strong>Lakukan transfer</strong><div class="small text-secondary">Gunakan rekening tujuan yang tertera.</div></div></div>
            <div class="d-flex gap-3 mb-3"><span class="badge rounded-pill text-bg-primary">2</span><div><strong>Unggah bukti</strong><div class="small text-secondary">Foto atau PDF bukti transaksi.</div></div></div>
            <div class="d-flex gap-3"><span class="badge rounded-pill text-bg-primary">3</span><div><strong>Tunggu konfirmasi</strong><div class="small text-secondary">Admin akan memverifikasi pembayaran.</div></div></div>
        </div></div>
    </div>
</div>
</div>
@endsection
