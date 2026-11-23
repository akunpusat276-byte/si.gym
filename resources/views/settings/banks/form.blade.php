@extends('layouts.app')
@section('title', $bank ? 'Edit Bank' : 'Tambah Bank')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card app-card"><div class="card-body p-4">
        <h1 class="page-title mb-4">{{ $bank ? 'Edit Rekening Bank' : 'Tambah Rekening Bank' }}</h1>
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ $bank ? route('settings.banks.update', $bank) : route('settings.banks.store') }}">
            @csrf @if($bank) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-7"><label class="form-label">Nama Bank</label><input name="bank_name" class="form-control" value="{{ old('bank_name', $bank?->bank_name) }}" required></div>
                <div class="col-md-5"><label class="form-label">Kode Bank</label><input name="bank_code" class="form-control text-uppercase" value="{{ old('bank_code', $bank?->bank_code) }}" required placeholder="BCA"></div>
                <div class="col-md-7"><label class="form-label">Nomor Rekening</label><input name="account_number" class="form-control" value="{{ old('account_number', $bank?->account_number) }}" placeholder="Nomor rekening tujuan"></div>
                <div class="col-md-5"><label class="form-label">Nama Pemilik Rekening</label><input name="account_holder" class="form-control" value="{{ old('account_holder', $bank?->account_holder) }}"></div>
                <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="isActive" {{ old('is_active', $bank?->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="isActive">Bank aktif untuk pembayaran</label></div></div>
            </div>
            <div class="d-flex gap-2 mt-4"><button class="btn btn-primary">Simpan</button><a href="{{ route('settings.banks.index') }}" class="btn btn-outline-secondary">Batal</a></div>
        </form>
    </div></div>
</div></div>
@endsection
