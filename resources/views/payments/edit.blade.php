@extends('layouts.app')
@section('title', 'Edit Pembayaran')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="page-title mb-0">Edit Pembayaran</h2>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Kembali</a>
</div>
<div class="card app-card"><div class="card-body p-4">
<form method="POST" action="{{ route('payments.update', $payment) }}">
@csrf @method('PUT')
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Pengguna</label><select name="user_id" class="form-select" required>@foreach($users as $user)<option value="{{ $user->id }}" @selected($payment->user_id == $user->id)>{{ $user->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Paket</label><select name="package_id" class="form-select" required>@foreach($packages as $package)<option value="{{ $package->id }}" @selected($payment->package_id == $package->id) data-price="{{ $package->price }}">{{ $package->name }} - Rp {{ number_format($package->price, 0, ',', '.') }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">Nominal</label><input type="number" name="amount" value="{{ old('amount', $payment->amount) }}" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Metode</label><select name="method" class="form-select">@foreach(['Cash','Transfer','QRIS'] as $method)<option @selected($payment->method === $method)>{{ $method }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(['Menunggu','Menunggu Pembayaran','Menunggu Verifikasi','Ditolak','Dibatalkan'] as $status)<option @selected($payment->status === $status)>{{ $status }}</option>@endforeach</select><div class="form-text">Status Lunas hanya dapat diberikan melalui verifikasi pembayaran.</div></div>
    <div class="col-md-4"><label class="form-label">Tanggal Pembayaran</label><input type="date" name="payment_date" value="{{ old('payment_date', optional($payment->payment_date)->toDateString()) }}" class="form-control"></div>
    <div class="col-md-8"><label class="form-label">Catatan</label><input name="notes" value="{{ old('notes', $payment->notes) }}" class="form-control"></div>
</div>
<button class="btn btn-primary mt-4">Simpan Perubahan</button>
</form>
</div></div>
@endsection
