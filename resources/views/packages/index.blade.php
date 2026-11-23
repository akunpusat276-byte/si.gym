@extends('layouts.app')
@section('title', 'Paket Membership')
@section('content')
@php
    $isAdmin = auth()->user()->role === 'admin';
@endphp
<div class="page-header">
    <div>
        <h1 class="page-title mb-1">{{ $isAdmin ? 'Paket Membership' : 'Data Paket Membership' }}</h1>
        <p class="text-muted mb-0">{{ $isAdmin ? 'Kelola paket, harga, durasi, dan status membership.' : 'Pantau paket, harga, dan durasi membership secara read-only.' }}</p>
    </div>
    @if($isAdmin)
        <a class="btn btn-primary px-3" href="{{ route('packages.create') }}"><i data-lucide="plus" class="me-1"></i>Tambah Paket</a>
    @endif
</div>
<div class="card crud-toolbar bg-white mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route($isAdmin ? 'packages.index' : 'owner.packages') }}" class="d-flex flex-wrap gap-2 align-items-center">
            <div class="input-group" style="max-width:320px">
                <span class="input-group-text bg-white border-end-0"><i data-lucide="search" style="width:17px"></i></span>
                <input class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Cari paket..." aria-label="Cari paket">
            </div>
            <select class="form-select" name="duration" aria-label="Filter durasi" style="max-width:180px">
                <option value="">Semua durasi</option>
                @foreach($durations as $duration)
                    <option value="{{ $duration }}" @selected((string) request('duration') === (string) $duration)>{{ $duration }} bulan</option>
                @endforeach
            </select>
            <select class="form-select" name="status" aria-label="Filter status" style="max-width:180px">
                <option value="">Semua status</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
            </select>
            <button class="btn btn-primary" type="submit">Terapkan</button>
            @if(request()->hasAny(['search', 'duration', 'status']))
                <a class="btn btn-light border" href="{{ route($isAdmin ? 'packages.index' : 'owner.packages') }}">Reset</a>
            @endif
        </form>
    </div>
</div>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
<div class="card admin-table-card bg-white">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama Paket</th>
                    <th>Durasi</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $package)
                    <tr>
                        <td>{{ ($packages->firstItem() ?? 1) + $loop->index }}</td>
                        <td>
                            @if($package->imageUrl())
                                <img src="{{ $package->imageUrl() }}" alt="Foto paket {{ $package->name }}" class="rounded-3" style="width:54px;height:44px;object-fit:cover">
                            @else
                                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-light text-primary" style="width:54px;height:44px"><i data-lucide="dumbbell"></i></span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $package->name }}</div>
                            <div class="small text-muted">{{ $package->category ?: 'Tanpa kategori' }}</div>
                        </td>
                        <td>{{ $package->duration }} bulan</td>
                        <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                        <td><span class="badge rounded-pill {{ $package->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <a class="btn btn-sm btn-outline-secondary crud-action" href="{{ route($isAdmin ? 'packages.show' : 'owner.packages.show', $package) }}" aria-label="Detail paket {{ $package->name }}" title="Detail"><i data-lucide="eye"></i></a>
                                @if($isAdmin)
                                    <a class="btn btn-sm btn-outline-primary crud-action" href="{{ route('packages.edit', $package) }}" aria-label="Edit paket {{ $package->name }}" title="Edit"><i data-lucide="pencil"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-danger crud-action" data-bs-toggle="modal" data-bs-target="#delete-package-{{ $package->id }}" aria-label="Hapus paket {{ $package->name }}" title="Hapus"><i data-lucide="trash-2"></i></button>
                                @endif
                            </div>
                            @if($isAdmin)
                                <div class="modal fade" id="delete-package-{{ $package->id }}" tabindex="-1" aria-labelledby="delete-package-label-{{ $package->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h2 class="modal-title fs-5" id="delete-package-label-{{ $package->id }}">Hapus Paket Membership</h2>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>
                                            <div class="modal-body">Apakah Anda yakin ingin menghapus paket membership ini?<div class="fw-semibold mt-2">{{ $package->name }}</div></div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                                <form method="POST" action="{{ route('packages.destroy', $package) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">{{ request()->filled('search') || request()->filled('duration') || request()->filled('status') ? 'Paket tidak ditemukan.' : 'Belum ada paket.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
    <span class="small text-muted">Total {{ $packages->total() }} paket</span>
    {{ $packages->links() }}
</div>
@endsection
