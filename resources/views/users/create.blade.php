@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('content')
<h2>Tambah Pengguna</h2>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('users.store') }}">
@csrf
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email') }}" required></div>
<div class="col-md-6"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
<div class="col-md-6"><label class="form-label">Konfirmasi Password</label><input type="password" class="form-control" name="password_confirmation" required></div>
<div class="col-md-6"><label class="form-label">Role</label><select class="form-select" name="role" required><option value="">Pilih role</option>@foreach(['admin'=>'Admin','owner'=>'Pemilik','member'=>'Member','pendaftar'=>'Pendaftar'] as $value=>$label)<option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Telepon</label><input class="form-control" name="phone" value="{{ old('phone') }}"></div>
<div class="col-md-6"><label class="form-label">Jenis Kelamin</label><select class="form-select" name="gender"><option value="">Pilih</option><option @selected(old('gender')==='Laki-laki')>Laki-laki</option><option @selected(old('gender')==='Perempuan')>Perempuan</option></select></div>
<div class="col-md-6"><label class="form-label">Tanggal Lahir</label><input type="date" class="form-control" name="birth_date" value="{{ old('birth_date') }}"></div>
<div class="col-md-12"><label class="form-label">Alamat</label><textarea class="form-control" name="address">{{ old('address') }}</textarea></div>
</div>
<div class="mt-4"><button class="btn btn-primary">Simpan</button><a href="{{ route('users.index') }}" class="btn btn-light">Batal</a></div>
</form>
</div></div>
@endsection
