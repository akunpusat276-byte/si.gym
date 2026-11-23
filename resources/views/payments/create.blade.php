@extends('layouts.app')
@section('title', 'Catat Pembayaran')
@section('content')
<h2>Catat Pembayaran</h2><div class="card"><div class="card-body"><form method="POST" action="{{ route('payments.store') }}">@csrf
<div class="row"><div class="col-md-6 mb-3"><label>Pendaftar/Member</label><select class="form-select" name="user_id" required><option value="">Pilih</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div><div class="col-md-6 mb-3"><label>Paket</label><select class="form-select" name="package_id" required><option value="">Pilih</option>@foreach($packages as $package)<option value="{{ $package->id }}">{{ $package->name }}</option>@endforeach</select></div></div>
<div class="row"><div class="col-md-4 mb-3"><label>Jumlah</label><input class="form-control" type="number" name="amount" required></div><div class="col-md-4 mb-3"><label>Metode</label><select class="form-select" name="method"><option>Cash</option><option>Transfer</option><option>QRIS</option></select></div><div class="col-md-4 mb-3"><label>Status</label><input class="form-control" value="Menunggu Pembayaran" disabled><input type="hidden" name="status" value="Menunggu Pembayaran"></div></div>
<div class="mb-3"><label>Tanggal Pembayaran</label><input class="form-control" type="date" name="payment_date" value="{{ today()->format('Y-m-d') }}"></div><button class="btn btn-primary">Simpan</button></form></div></div>
@endsection
