@extends('layouts.app')
@section('title', 'Profil Saya')
@section('content')
@php
    $role = auth()->user()->role;
@endphp
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">Profil Saya</h1>
        <p class="text-muted mb-0">Kelola informasi pribadi dan keamanan akun Anda.</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-1">Periksa kembali data yang dimasukkan:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card app-card h-100">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Data Profil</h2>
                <form method="POST" enctype="multipart/form-data" action="{{ route(match($role) { 'member' => 'member.profile.update', 'owner' => 'owner.profile.update', default => 'registrant.profile.update' }) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Foto Profil</label>
                        <input id="avatar" class="form-control @error('avatar') is-invalid @enderror" type="file" name="avatar" accept="image/*">
                        @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', auth()->user()->name) }}" required maxlength="255">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()->email) }}" required maxlength="255">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Telepon</label>
                        <input id="phone" class="form-control @error('phone') is-invalid @enderror" name="phone" type="tel" value="{{ old('phone', auth()->user()->phone) }}" maxlength="30">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Jenis Kelamin</label>
                            <select id="gender" class="form-select @error('gender') is-invalid @enderror" name="gender">
                                <option value="">Pilih</option>
                                <option value="Laki-laki" @selected(old('gender', auth()->user()->gender) === 'Laki-laki')>Laki-laki</option>
                                <option value="Perempuan" @selected(old('gender', auth()->user()->gender) === 'Perempuan')>Perempuan</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="birth_date" class="form-label">Tanggal Lahir</label>
                            <input id="birth_date" type="date" class="form-control @error('birth_date') is-invalid @enderror" name="birth_date" value="{{ old('birth_date', auth()->user()->birth_date?->format('Y-m-d')) }}" max="{{ today()->toDateString() }}">
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea id="address" class="form-control @error('address') is-invalid @enderror" name="address" rows="3">{{ old('address', auth()->user()->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary" type="submit">Simpan Profil</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5" id="ubah-password">
        <div class="card app-card">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Ubah Password</h2>
                <form method="POST" action="{{ route(match($role) { 'member' => 'member.password.update', 'owner' => 'owner.password.update', default => 'registrant.password.update' }) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required autocomplete="current-password">
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-outline-primary" type="submit">Ubah Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
