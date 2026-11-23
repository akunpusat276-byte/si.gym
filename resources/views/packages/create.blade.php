@extends('layouts.app')
@section('title', 'Tambah Paket')
@section('content')
<div class="page-header">
    <div><h1 class="page-title mb-1">Tambah Paket Membership</h1><p class="text-muted mb-0">Lengkapi informasi paket yang akan ditampilkan kepada pendaftar.</p></div>
    <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">Kembali</a>
</div>
<div class="card app-card"><div class="card-body p-4">
    <form method="POST" enctype="multipart/form-data" action="{{ route('packages.store') }}">
        @csrf
        @include('packages._form', ['package' => null])
        <div class="d-flex gap-2"><button class="btn btn-primary">Simpan Paket</button><a href="{{ route('packages.index') }}" class="btn btn-light border">Batal</a></div>
    </form>
</div></div>
@endsection
