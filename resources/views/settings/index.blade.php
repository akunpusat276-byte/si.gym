@extends('layouts.app')
@section('title', 'Pengaturan Gym')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">Pengaturan Gym</h1>
        <p class="text-muted mb-0">{{ auth()->user()->role === 'owner' ? 'Informasi gym (read-only).' : 'Kelola informasi gym dan rekening pembayaran.' }}</p>
    </div>
</div>
@php
    $values = $settings->pluck('value', 'key');
@endphp
@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-1">Pengaturan belum dapat disimpan:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="card app-card">
    <div class="card-body p-4">
        @if(auth()->user()->role === 'admin')
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')
        @endif
        <div class="mb-3">
            <label for="gym_name" class="form-label">Nama Gym</label>
            <input id="gym_name" class="form-control" name="gym_name" value="{{ old('gym_name', $values['gym_name'] ?? 'SI-GYM') }}" required @disabled(auth()->user()->role === 'owner')>
        </div>
        <div class="mb-3">
            <label for="gym_address" class="form-label">Alamat</label>
            <textarea id="gym_address" class="form-control" name="gym_address" rows="3" @disabled(auth()->user()->role === 'owner')>{{ old('gym_address', $values['gym_address'] ?? '') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="gym_phone" class="form-label">Nomor Telepon</label>
            <input id="gym_phone" class="form-control" type="tel" name="gym_phone" value="{{ old('gym_phone', $values['gym_phone'] ?? '') }}" @disabled(auth()->user()->role === 'owner')>
        </div>
        <div class="mb-3">
            <label for="gym_email" class="form-label">Email</label>
            <input id="gym_email" type="email" class="form-control" name="gym_email" value="{{ old('gym_email', $values['gym_email'] ?? '') }}" @disabled(auth()->user()->role === 'owner')>
        </div>
        <div class="mb-3">
            <label for="gym_hours" class="form-label">Jam Operasional</label>
            <textarea id="gym_hours" class="form-control" name="gym_hours" rows="2" @disabled(auth()->user()->role === 'owner')>{{ old('gym_hours', $values['gym_hours'] ?? '') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="gym_facilities" class="form-label">Fasilitas Gym</label>
            <textarea id="gym_facilities" class="form-control" name="gym_facilities" rows="7" aria-describedby="gym-facilities-help" @disabled(auth()->user()->role === 'owner')>{{ old('gym_facilities', $values['gym_facilities'] ?? '') }}</textarea>
            <div id="gym-facilities-help" class="form-text">Masukkan satu fasilitas per baris agar tampil rapi pada dashboard pendaftar.</div>
        </div>
        @if(auth()->user()->role === 'admin')
            <button class="btn btn-primary" type="submit">Simpan Pengaturan</button>
            </form>
        @endif
    </div>
</div>
@if(auth()->user()->role === 'admin')
    <div class="mt-3"><a href="{{ route('settings.banks.index') }}" class="btn btn-outline-primary">Kelola Rekening Bank</a></div>
@endif
@endsection
