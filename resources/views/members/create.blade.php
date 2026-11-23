@extends('layouts.app')
@section('title', 'Tambah Member')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">Tambah Member</h1>
        <p class="text-muted mb-0">Lengkapi identitas pengguna untuk membuat data member.</p>
    </div>
    <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">Kembali</a>
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
        <form method="POST" action="{{ route('members.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="user_id" class="form-label">Pengguna <span class="text-danger">*</span></label>
                    <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                        <option value="">Pilih pengguna</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} — {{ $user->email }}</option>
                        @endforeach
                    </select>
                    @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($users->isEmpty())
                        <div class="form-text">Tidak ada pengguna yang tersedia untuk dijadikan member.</div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kode Member</label>
                    <input class="form-control" value="Dibuat otomatis setelah member disimpan" disabled>
                    <div class="form-text">Kode unik dibuat oleh sistem dan tidak dapat diubah.</div>
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Telepon</label>
                    <input id="phone" name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" maxlength="30">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="gender" class="form-label">Jenis Kelamin</label>
                    <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="">Pilih</option>
                        <option value="Laki-laki" @selected(old('gender') === 'Laki-laki')>Laki-laki</option>
                        <option value="Perempuan" @selected(old('gender') === 'Perempuan')>Perempuan</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="birth_date" class="form-label">Tanggal Lahir</label>
                    <input id="birth_date" type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date') }}" max="{{ today()->toDateString() }}">
                    @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address') }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('members.index') }}" class="btn btn-light border">Batal</a>
                <button class="btn btn-primary" type="submit" @disabled($users->isEmpty())>Simpan Member</button>
            </div>
        </form>
    </div>
</div>
@endsection
