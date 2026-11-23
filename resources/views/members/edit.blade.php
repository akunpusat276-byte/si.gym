@extends('layouts.app')
@section('title', 'Edit Member')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">Edit Member</h1>
        <p class="text-muted mb-0">Perbarui informasi member {{ $member->user->name }}.</p>
    </div>
    <a href="{{ route('members.show', $member) }}" class="btn btn-outline-secondary">Kembali ke Detail</a>
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

<div class="card app-card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('members.update', $member) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Pengguna</label>
                    <input class="form-control" value="{{ $member->user->name }}" disabled>
                    <div class="form-text">Nama akun dikelola melalui data pengguna.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kode Member</label>
                    <input class="form-control" value="{{ $member->member_code }}" disabled>
                    <div class="form-text">Kode unik dibuat otomatis dan tidak dapat diubah.</div>
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Telepon</label>
                    <input id="phone" name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $member->phone) }}" maxlength="30">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="gender" class="form-label">Jenis Kelamin</label>
                    <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="">Pilih</option>
                        <option value="Laki-laki" @selected(old('gender', $member->gender) === 'Laki-laki')>Laki-laki</option>
                        <option value="Perempuan" @selected(old('gender', $member->gender) === 'Perempuan')>Perempuan</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="birth_date" class="form-label">Tanggal Lahir</label>
                    <input id="birth_date" type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', $member->birth_date?->format('Y-m-d')) }}" max="{{ today()->toDateString() }}">
                    @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $member->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('members.show', $member) }}" class="btn btn-light border">Batal</a>
                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
