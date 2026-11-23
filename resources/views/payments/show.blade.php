@extends('layouts.app')
@section('title', 'Detail Pembayaran')
@section('content')
<h2>Detail Pembayaran</h2><div class="card"><div class="card-body"><p><strong>Invoice:</strong> {{ $payment->invoice_number }}</p><p><strong>Pengguna:</strong> {{ $payment->user->name }}</p><p><strong>Jumlah:</strong> Rp {{ number_format($payment->amount,0,',','.') }}</p><p><strong>Status:</strong> {{ $payment->status }}</p>
<p><strong>Bank:</strong> {{ $payment->bank_name ?: '-' }}</p><p><strong>Pengirim:</strong> {{ $payment->sender_name ?: '-' }}</p><p><strong>Tanggal Transfer:</strong> {{ $payment->transfer_date?->format('d/m/Y') ?: '-' }}</p>
@if($payment->payment_proof)<a href="{{ auth()->user()->role === 'admin' ? route('payments.proof', $payment) : (auth()->user()->role === 'owner' ? route('owner.payments.proof', $payment) : route((auth()->user()->role === 'member' ? 'member.' : 'registrant.').'payments.proof', $payment)) }}" target="_blank" class="btn btn-sm btn-outline-primary mb-3">Lihat Bukti Transfer</a>@endif
@if(auth()->user()->role === 'admin' && ($payment->status === 'Menunggu Verifikasi' || ($payment->status === 'Menunggu Pembayaran' && $payment->method === 'Cash')))
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('payments.verify', $payment) }}">@csrf @method('PATCH')<button class="btn btn-success">Verifikasi Lunas</button></form>
        <form method="POST" action="{{ route('payments.reject', $payment) }}" class="d-flex gap-2">@csrf @method('PATCH')<input class="form-control" name="rejection_reason" placeholder="Alasan penolakan" required><button class="btn btn-danger">Tolak</button></form>
    </div>
@endif
@if(auth()->user()->role === 'admin' && $payment->status !== 'Lunas')<form method="POST" action="{{ route('payments.update', $payment) }}" class="row mt-3">@csrf @method('PUT')<div class="col-md-4"><select class="form-select" name="status">@foreach(['Menunggu Pembayaran','Menunggu Verifikasi','Ditolak','Dibatalkan'] as $status)<option @selected($payment->status === $status)>{{ $status }}</option>@endforeach</select></div><div class="col-md-3"><input class="form-control" type="date" name="payment_date" value="{{ $payment->payment_date?->format('Y-m-d') }}"></div><div class="col-md-2"><button class="btn btn-primary">Perbarui</button></div></form>@endif
</div></div>
@endsection
