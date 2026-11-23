@extends('layouts.app')
@section('title', 'Edit Pendaftaran')
@section('content')
@php
    $applicantMode = isset($applicantMode) && $applicantMode;
@endphp
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">Edit Pendaftaran</h1>
        <p class="text-muted mb-0">Perbarui data pendaftaran {{ $registration->registration_number }}.</p>
    </div>
    <a href="{{ $applicantMode ? route('registrations.status.me') : route('registrations.show', $registration) }}" class="btn btn-outline-secondary">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-1">Periksa kembali data yang dimasukkan:</div>
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card app-card">
    <div class="card-body p-4">
        <form method="POST" action="{{ $applicantMode ? route('registrations.update.me', $registration) : route('registrations.update', $registration) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Pendaftar</label>
                    <input class="form-control" value="{{ $registration->user->name }}" disabled>
                </div>
                <div class="col-md-6">
                    <label for="package_id" class="form-label">Paket <span class="text-danger">*</span></label>
                    <select id="package_id" name="package_id" class="form-select @error('package_id') is-invalid @enderror" required>
                        <option value="">Pilih Paket</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" @selected(old('package_id', $registration->package_id) == $package->id)>{{ $package->name }} — {{ $package->duration }} bulan — Rp {{ number_format($package->price, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Telepon <span class="text-danger">*</span></label>
                    <input id="phone" name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $registration->phone) }}" required maxlength="30">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="gender" class="form-label">Jenis Kelamin</label>
                    <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="">Pilih</option>
                        <option value="Laki-laki" @selected(old('gender', $registration->gender) === 'Laki-laki')>Laki-laki</option>
                        <option value="Perempuan" @selected(old('gender', $registration->gender) === 'Perempuan')>Perempuan</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="birth_date" class="form-label">Tanggal Lahir</label>
                    <input id="birth_date" type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', $registration->birth_date?->format('Y-m-d')) }}" max="{{ today()->toDateString() }}">
                    @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $registration->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $registration->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ $applicantMode ? route('registrations.status.me') : route('registrations.show', $registration) }}" class="btn btn-light border">Batal</a>
                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
