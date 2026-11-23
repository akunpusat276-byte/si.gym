@extends('layouts.app')
@section('title', 'Detail Pendaftaran')
@section('content')
<h2>Detail Pendaftaran</h2><div class="card"><div class="card-body">
<p><strong>Nomor:</strong> {{ $registration->registration_number }}</p>
<p><strong>Pendaftar:</strong> {{ $registration->user->name }}</p>
<p><strong>Email:</strong> {{ $registration->user->email }}</p>
<p><strong>Telepon:</strong> {{ $registration->phone }}</p>
<p><strong>Alamat:</strong> {{ $registration->address ?: '-' }}</p>
<p><strong>Paket:</strong> {{ $registration->package->name }} · {{ $registration->package->duration }} bulan · Rp {{ number_format($registration->package->price, 0, ',', '.') }}</p>
<p><strong>Tanggal Pendaftaran:</strong> {{ $registration->created_at?->translatedFormat('d F Y') ?? '-' }}</p>
<p><strong>Status:</strong> {{ $registration->status }}</p>
@if($registration->admin_note)<p><strong>Catatan Admin:</strong> {{ $registration->admin_note }}</p>@endif
@if($registration->payments->isNotEmpty())
    <h3 class="h6 fw-bold mt-4">Tagihan Pembayaran</h3>
    @foreach($registration->payments as $payment)
        <div class="d-flex flex-wrap justify-content-between gap-2 border-top py-2">
            <span>{{ $payment->invoice_number }} · {{ $payment->package?->name ?? '-' }} · Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
            <span>{{ $payment->status }}</span>
        </div>
    @endforeach
@endif
@if(auth()->user()->role === 'admin')
<div class="d-flex gap-2 mb-3"><a href="{{ route('registrations.edit', $registration) }}" class="btn btn-outline-primary">Edit Pendaftaran</a><form method="POST" action="{{ route('registrations.destroy', $registration) }}" onsubmit="return confirm('Hapus pendaftaran ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Hapus</button></form></div>
@if(in_array($registration->status, ['Menunggu', 'Menunggu Verifikasi'], true))
<form method="POST" action="{{ route('registrations.status', $registration) }}" class="mt-3">@csrf @method('PATCH')
<div class="row"><div class="col-md-4"><select class="form-select" name="status"><option value="Disetujui">Setujui &amp; Buat Tagihan</option><option value="Ditolak">Tolak Pendaftaran</option></select></div><div class="col-md-6"><input class="form-control" name="admin_note" value="{{ $registration->admin_note }}" placeholder="Catatan admin"></div><div class="col-md-2"><button class="btn btn-primary">Simpan</button></div></div>
</form>
@elseif($registration->status === 'Menunggu Pembayaran')
<p class="text-muted mt-3 mb-0">Pendaftaran disetujui. Tagihan pembayaran telah dibuat dan menunggu pembayaran.</p>
@endif
@endif
@if(auth()->user()->role === 'pendaftar')
<div class="d-flex gap-2 mt-3">
@if(in_array($registration->status, ['Menunggu', 'Menunggu Verifikasi'], true) && $registration->payments_count === 0)
<a href="{{ route('registrations.edit.me', $registration) }}" class="btn btn-primary">Ubah Pendaftaran</a>
@endif
@if(in_array($registration->status, ['Menunggu', 'Menunggu Verifikasi'], true) && $registration->payments_count === 0)
<form method="POST" action="{{ route('registrations.cancel', $registration) }}" onsubmit="return confirm('Batalkan pendaftaran ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Batalkan</button></form>
@endif
</div>
@endif
</div></div>
@endsection
