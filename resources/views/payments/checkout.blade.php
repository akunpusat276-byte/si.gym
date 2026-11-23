@extends('layouts.app')
@section('title', 'Pembayaran Transfer Bank')
@section('content')
@php
    $prefix = auth()->user()->role === 'member' ? 'member.' : 'registrant.';
@endphp
<style>
.checkout-shell{max-width:1180px;margin:0 auto}.checkout-header{background:#fff;border:1px solid #e7eaf0;border-radius:18px;padding:22px 24px;box-shadow:0 10px 30px rgba(15,23,42,.04)}
.checkout-progress{display:flex;align-items:center;gap:12px;color:#98a2b3;font-size:13px;font-weight:600}.checkout-progress .step{width:28px;height:28px;border-radius:50%;display:grid;place-items:center;border:1px solid #d0d5dd;background:#fff}.checkout-progress .active{color:#fff;border-color:#2563eb;background:#2563eb}.checkout-progress .line{height:1px;flex:1;background:#e4e7ec}
.checkout-card{border:0;border-radius:18px;box-shadow:0 12px 35px rgba(15,23,42,.06)}.checkout-card .card-body{padding:28px}
.method-card,.bank-option{border:1px solid #e4e7ec;border-radius:14px;cursor:pointer;transition:.2s ease;background:#fff}.method-card.selected,.bank-option:has(input:checked){border-color:#2563eb!important;background:#f5f9ff;box-shadow:0 0 0 3px rgba(37,99,235,.08)}
.bank-logo{width:52px;height:52px;display:grid;place-items:center;border-radius:12px;background:linear-gradient(145deg,#eef4ff,#fff);color:#1d4ed8;font-weight:800}
.account-panel{border-radius:16px;padding:22px;color:#fff;background:linear-gradient(135deg,#172033,#2563a9)}
.account-number{font-size:25px;font-weight:700;letter-spacing:1px}.copy-button{border:1px solid rgba(255,255,255,.25);color:#fff;background:rgba(255,255,255,.12)}
.summary-card{border:0;border-radius:18px;background:#172033;color:#fff;box-shadow:0 16px 40px rgba(15,23,42,.16)}.summary-card small{color:#98a2b3}.summary-divider{border-color:rgba(255,255,255,.12)!important}
.checkout-main-button{height:50px;border-radius:10px;font-weight:700}
@media(max-width:768px){.checkout-card .card-body{padding:20px}.checkout-progress .label{display:none}.account-number{font-size:21px}}
</style>
<div class="checkout-shell">
    <div class="checkout-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><a href="{{ route($prefix.'payments') }}" class="text-decoration-none text-secondary small">&larr; Pembayaran</a><h1 class="page-title mt-2 mb-0">Selesaikan Pembayaran</h1></div>
        <div class="checkout-progress flex-grow-1 justify-content-end" style="max-width:390px">
            <span class="step active">1</span><span class="label">Metode</span><span class="line"></span><span class="step">2</span><span class="label">Transfer</span>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card checkout-card">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="fw-bold fs-5 mb-1">Metode Pembayaran</div>
                        <div class="text-secondary small">Pilih rekening bank tujuan untuk menyelesaikan transaksi.</div>
                    </div>
                    <label class="method-card selected d-flex align-items-center gap-3 p-3 mb-4">
                        <span class="bank-logo"><i data-lucide="landmark"></i></span>
                        <span class="flex-grow-1"><strong>Transfer Bank</strong><div class="small text-secondary">Verifikasi otomatis oleh Admin setelah bukti diterima</div></span>
                        <span class="badge text-bg-primary">Dipilih</span>
                    </label>
                    @if($banks->isEmpty())
                        <div class="alert alert-warning">Informasi rekening belum tersedia. Silakan hubungi Admin Gym.</div>
                    @else
                        <form method="POST" action="{{ route($prefix.'payments.bank', $payment) }}">
                            @csrf
                            <div class="d-flex justify-content-between align-items-end mb-3"><div><div class="fw-bold">Bank Tujuan</div><div class="small text-secondary">Pilih satu bank</div></div></div>
                            <div class="row g-3 mb-4">
                                @foreach($banks as $bank)
                                <div class="col-md-6">
                                    <label class="bank-option card h-100 p-3" for="bank-{{ $bank->id }}">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bank-logo">{{ strtoupper(substr($bank->bank_name, 0, 1)) }}</div>
                                            <div class="flex-grow-1"><strong>{{ $bank->bank_name }}</strong><div class="small text-secondary">{{ $bank->bank_code }}</div></div>
                                            <input class="form-check-input" type="radio" name="bank_id" id="bank-{{ $bank->id }}" value="{{ $bank->id }}" data-account="{{ $bank->account_number }}" data-holder="{{ $bank->account_holder }}" required {{ $payment->bank_id === $bank->id ? 'checked' : '' }}>
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <div class="account-panel" id="bankDetails">
                                <div class="d-flex justify-content-between gap-2 mb-4"><div><div class="small text-white-50">REKENING TUJUAN</div><div class="fw-bold fs-5" id="selectedBank">{{ $payment->bank_name ?: 'Belum ada bank dipilih' }}</div></div><i data-lucide="shield-check"></i></div>
                                <div class="text-white-50 small mb-1">Nomor Rekening</div>
                                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                    <span class="account-number" id="selectedAccount">{{ $payment->bank_account_number ?: '-' }}</span>
                                    <button type="button" class="btn btn-sm copy-button px-3" id="copyAccount" @disabled(!$payment->bank_account_number)>Salin nomor</button>
                                </div>
                                <div class="small">Atas nama <strong id="selectedHolder">{{ $payment->bank_account_holder ?: '-' }}</strong></div>
                                <div class="summary-divider border-top mt-4 pt-3 d-flex justify-content-between align-items-center"><span>Total transfer</span><strong class="fs-5">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></div>
                                <div class="small mt-2" id="copyMessage" aria-live="polite"></div>
                            </div>
                            <button class="btn btn-primary checkout-main-button w-100 mt-4" id="continueButton" disabled>Lanjut Upload Bukti Transfer</button>
                        </form>
                    @endif
                    <div class="border-top mt-4 pt-4">
                        <div class="fw-bold mb-1">Pembayaran Tunai</div>
                        <p class="small text-secondary">Pilih opsi ini jika Anda akan membayar langsung di meja Admin Gym. Status lunas akan diberikan setelah Admin menerima pembayaran.</p>
                        <form method="POST" action="{{ route($prefix.'payments.cash', $payment) }}" onsubmit="return confirm('Konfirmasi pembayaran tunai di Gym?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100">Pilih Pembayaran Tunai</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card summary-card"><div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="fs-5 mb-0">Ringkasan</h2><span class="badge text-bg-light text-dark">Invoice</span></div>
                <small>Nomor Invoice</small><div class="fw-semibold mb-3">{{ $payment->invoice_number }}</div>
                <small>Paket Membership</small><div class="fw-semibold">{{ $payment->package->name }}</div><div class="text-white-50 small mb-3">{{ $payment->package->duration }} bulan akses</div>
                <small>Nama Pengguna</small><div class="fw-semibold mb-4">{{ $payment->user->name }}</div>
                <div class="summary-divider border-top pt-4"><div class="d-flex justify-content-between text-white-50"><span>Status</span><span>{{ $payment->status }}</span></div><div class="d-flex justify-content-between align-items-end mt-3"><span>Total</span><strong class="fs-3">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></div></div>
            </div></div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.querySelectorAll('input[name="bank_id"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        const option = this.closest('.bank-option');
        document.getElementById('selectedBank').textContent = option.querySelector('strong').textContent;
        document.getElementById('selectedAccount').textContent = this.dataset.account;
        document.getElementById('selectedHolder').textContent = this.dataset.holder;
        document.getElementById('continueButton').disabled = false;
        document.getElementById('copyAccount').disabled = false;
    });
});
const selected = document.querySelector('input[name="bank_id"]:checked');
if (selected) selected.dispatchEvent(new Event('change'));

document.getElementById('copyAccount')?.addEventListener('click', async function () {
    const account = document.getElementById('selectedAccount').textContent;
    try {
        await navigator.clipboard.writeText(account);
        document.getElementById('copyMessage').textContent = 'Nomor rekening berhasil disalin.';
    } catch (error) {
        document.getElementById('copyMessage').textContent = 'Nomor rekening gagal disalin. Salin manual dari layar.';
    }
});
</script>
@endpush
@endsection
