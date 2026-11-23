@extends('layouts.app')
@section('title', 'Ubah Paket')
@section('content')
<div class="page-header">
    <div><h1 class="page-title mb-1">Edit Paket Membership</h1><p class="text-muted mb-0">{{ $package->name }}</p></div>
    <a href="{{ route('packages.show', $package) }}" class="btn btn-outline-secondary">Kembali</a>
</div>
<div class="card app-card"><div class="card-body p-4">
    <form method="POST" enctype="multipart/form-data" action="{{ route('packages.update', $package) }}">
        @csrf
        @method('PUT')
        @include('packages._form', ['package' => $package])
        <div class="d-flex gap-2"><button class="btn btn-primary">Simpan Perubahan</button><a href="{{ route('packages.index') }}" class="btn btn-light border">Batal</a></div>
    </form>
</div></div>
@endsection
