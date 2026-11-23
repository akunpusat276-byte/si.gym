@extends('layouts.app')
@section('title', 'Pengguna')
@section('content')
<style>
.management-filter,.account-card{border:0;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.06)}
.account-avatar{width:48px;height:48px;border-radius:14px;background:#e8f1ff;color:#2563eb;display:grid;place-items:center;font-weight:800;font-size:18px}
.account-card{position:relative;overflow:hidden;transition:.18s ease}
.account-card::before{content:'';position:absolute;inset:0 0 auto;height:4px;background:#2563eb}
.account-card.role-owner::before{background:#7954d8}
.account-card.role-member::before{background:#19a98b}
.account-card.role-pendaftar::before{background:#f7941d}
.account-card:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(15,23,42,.09)}
</style>
<div class="page-header">
    <div><h1 class="page-title mb-1">Kelola Pengguna</h1><p class="text-muted mb-0">Kelola akun Admin, Pemilik Gym, Member, dan Pendaftar.</p></div>
    <div class="page-header-actions">
        <a href="{{ route('users.export') }}" class="btn btn-primary"><i data-lucide="download" class="me-2"></i>Export CSV</a>
        <a href="{{ route('users.create') }}" class="btn btn-success px-3"><i data-lucide="plus" class="me-1"></i>Tambah Baru</a>
    </div>
</div>
<div class="card crud-toolbar bg-white mb-3"><div class="card-body py-3">
    <form method="GET" action="{{ route('users.index') }}" class="d-flex flex-wrap gap-2">
        <div class="input-group" style="max-width:320px"><span class="input-group-text bg-white border-end-0"><i data-lucide="search" style="width:17px"></i></span><input class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Cari pengguna..."></div>
        <select name="role" class="form-select" style="width:auto"><option value="">Semua Role</option>@foreach(['admin' => 'Admin/Pengelola Gym', 'owner' => 'Pemilik Gym', 'member' => 'Member', 'pendaftar' => 'Pendaftar'] as $value => $label)<option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
        <select name="status" class="form-select" style="width:auto" aria-label="Filter status akun"><option value="">Semua Status</option>@foreach(['Aktif', 'Nonaktif'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
        <button class="btn btn-primary px-3">Cari</button>
        @if(request()->filled('search') || request()->filled('role') || request()->filled('status'))<a href="{{ route('users.index') }}" class="btn btn-light border">Reset</a>@endif
    </form>
</div></div>
<div class="row g-3">
@forelse($users as $user)
<div class="col-md-6 col-xl-4"><div class="card account-card role-{{ $user->role }} h-100"><div class="card-body p-4">
<div class="d-flex align-items-start justify-content-between"><div class="account-avatar">{{ strtoupper(substr($user->name,0,1)) }}</div><span class="badge rounded-pill {{ ($user->status ?? 'Aktif') === 'Aktif' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user->status ?? 'Aktif' }}</span></div>
<h5 class="fw-bold mt-3 mb-1">{{ $user->name }}</h5><div class="text-muted small text-truncate">{{ $user->email }}</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top mt-3 pt-3"><span class="badge text-bg-light border text-uppercase">{{ ['admin' => 'Admin / Pengelola', 'owner' => 'Pemilik Gym', 'member' => 'Member', 'pendaftar' => 'Pendaftar'][$user->role] ?? $user->role }}</span><div class="d-flex gap-1">
<a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary" aria-label="Detail {{ $user->name }}" title="Detail"><i data-lucide="eye" style="width:15px"></i></a>
@unless(auth()->id() === $user->id)<form method="POST" action="{{ route('users.status', $user) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" title="{{ $user->status === 'Nonaktif' ? 'Aktifkan akun' : 'Nonaktifkan akun' }}"><i data-lucide="{{ $user->status === 'Nonaktif' ? 'circle-check' : 'circle-minus' }}" style="width:15px"></i></button></form>@endunless
<a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i data-lucide="pencil" style="width:15px"></i></a>
@unless(auth()->id() === $user->id)<form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i data-lucide="trash-2" style="width:15px"></i></button></form>@endunless
</div></div>
</div></div></div>
@empty<div class="col-12"><div class="card management-filter"><div class="card-body text-center text-muted py-5">Belum ada pengguna.</div></div></div>@endforelse
</div><div class="mt-3">{{ $users->links() }}</div>
@endsection
