@extends('layouts.app')
@section('title', 'Rekening Bank')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="page-title mb-1">Rekening Bank</h1><p class="text-muted mb-0">Kelola rekening tujuan pembayaran transfer.</p></div>
    <a href="{{ route('settings.banks.create') }}" class="btn btn-primary">Tambah Bank</a>
</div>
<div class="card admin-table-card bg-white"><div class="table-responsive"><table class="table align-middle">
    <thead><tr><th>Bank</th><th>Kode</th><th>Nomor Rekening</th><th>Pemilik</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
    <tbody>
    @forelse($banks as $bank)
        <tr>
            <td class="fw-semibold">{{ $bank->bank_name }}</td>
            <td>{{ $bank->bank_code }}</td>
            <td>{{ $bank->account_number ?: '-' }}</td>
            <td>{{ $bank->account_holder ?: '-' }}</td>
            <td><span class="badge {{ $bank->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $bank->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td class="text-end">
                <a href="{{ route('settings.banks.edit', $bank) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                <form action="{{ route('settings.banks.destroy', $bank) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bank ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" @disabled($bank->payments()->exists())>Hapus</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada rekening bank.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
{{ $banks->links() }}
@endsection
