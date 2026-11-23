@extends('layouts.app')
@section('title', 'Tambah Membership')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="page-title mb-0">Tambah Membership</h2>
    <a href="{{ route('memberships.index') }}" class="btn btn-outline-secondary">Kembali</a>
</div>
<div class="card app-card"><div class="card-body p-4">
<form method="POST" action="{{ route('memberships.store') }}">
@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Member</label>
        <select name="member_id" class="form-select" required>
            <option value="">Pilih member</option>
            @foreach($members as $member)<option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>{{ $member->member_code }} - {{ $member->user->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Paket</label>
        <select name="package_id" class="form-select" required>
            <option value="">Pilih paket</option>
            @foreach($packages as $package)<option value="{{ $package->id }}" @selected(old('package_id') == $package->id)>{{ $package->name }} ({{ $package->duration }} bulan)</option>@endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Tanggal Mulai</label>
        <input type="date" name="start_date" value="{{ old('start_date', today()->toDateString()) }}" class="form-control" required>
    </div>
</div>
<button class="btn btn-primary mt-4">Simpan Membership</button>
</form>
</div></div>
@endsection
