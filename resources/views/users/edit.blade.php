@extends('layouts.app')
@section('title', 'Edit Pengguna')
@section('content')
<h2>Edit Pengguna</h2>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('users.update', $user) }}">
@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="name" value="{{ old('name', $user->name) }}" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required></div>
<div class="col-md-6"><label class="form-label">Password Baru</label><input type="password" class="form-control" name="password"><small class="text-muted">Kosongkan jika tidak diubah.</small></div>
<div class="col-md-6"><label class="form-label">Konfirmasi Password Baru</label><input type="password" class="form-control" name="password_confirmation"></div>
<div class="col-md-6"><label class="form-label">Role</label><select class="form-select" name="role" required>@foreach(['admin'=>'Admin','owner'=>'Pemilik','member'=>'Member','pendaftar'=>'Pendaftar'] as $value=>$label)<option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Telepon</label><input class="form-control" name="phone" value="{{ old('phone', $user->phone) }}"></div>
<div class="col-md-6"><label class="form-label">Jenis Kelamin</label><select class="form-select" name="gender"><option value="">Pilih</option><option @selected(old('gender', $user->gender)==='Laki-laki')>Laki-laki</option><option @selected(old('gender', $user->gender)==='Perempuan')>Perempuan</option></select></div>
<div class="col-md-6"><label class="form-label">Tanggal Lahir</label><input type="date" class="form-control" name="birth_date" value="{{ old('birth_date', $user->birth_date?->toDateString()) }}"></div>
<div class="col-md-12"><label class="form-label">Alamat</label><textarea class="form-control" name="address">{{ old('address', $user->address) }}</textarea></div>
</div>
<div class="mt-4"><button class="btn btn-primary">Simpan</button><a href="{{ route('users.index') }}" class="btn btn-light">Batal</a></div>
</form>
</div></div>
@endsection
