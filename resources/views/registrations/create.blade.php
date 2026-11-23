@extends('layouts.app')
@section('title', 'Formulir Pendaftaran')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">Formulir Pendaftaran Member</h1>
        <p class="text-muted mb-0">Lengkapi data diri dan pilih paket gym yang sesuai.</p>
    </div>
    <a href="{{ route('registrant.dashboard') }}" class="btn btn-outline-secondary">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-1">Periksa kembali data yang dimasukkan:</div>
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card app-card">
    <div class="card-body p-4">
        @if($packages->isEmpty())
            <div class="alert alert-info mb-0">Belum ada paket aktif yang dapat dipilih. Silakan coba kembali nanti.</div>
        @else
            <form method="POST" action="{{ route('registrations.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama</label>
                        <input class="form-control" value="{{ $user->name }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" value="{{ $user->email }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                        <input id="phone" class="form-control @error('phone') is-invalid @enderror" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required maxlength="30">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="package_id" class="form-label">Paket <span class="text-danger">*</span></label>
                        <select id="package_id" class="form-select @error('package_id') is-invalid @enderror" name="package_id" required>
                            <option value="">Pilih Paket</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" @selected(old('package_id', $selectedPackage?->id) == $package->id)>{{ $package->name }} — {{ $package->duration }} bulan — Rp {{ number_format($package->price, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                        @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($selectedPackage)
                            <div class="form-text">Paket {{ $selectedPackage->name }} telah dipilih dari katalog.</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="gender" class="form-label">Jenis Kelamin</label>
                        <select id="gender" class="form-select @error('gender') is-invalid @enderror" name="gender">
                            <option value="">Pilih</option>
                            <option value="Laki-laki" @selected(old('gender', $user->gender) === 'Laki-laki')>Laki-laki</option>
                            <option value="Perempuan" @selected(old('gender', $user->gender) === 'Perempuan')>Perempuan</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="birth_date" class="form-label">Tanggal Lahir</label>
                        <input id="birth_date" class="form-control @error('birth_date') is-invalid @enderror" type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}" max="{{ today()->toDateString() }}">
                        @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea id="address" class="form-control @error('address') is-invalid @enderror" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('registrant.dashboard') }}" class="btn btn-light border">Batal</a>
                    <button class="btn btn-primary" type="submit">Kirim Pendaftaran</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
