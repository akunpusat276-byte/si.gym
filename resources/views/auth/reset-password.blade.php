@extends('layouts.app')
@section('title', 'Atur Ulang Password SI-GYM')
@section('content')
<style>
    body { background:#fff !important; }
    main { width:100%; }
    .password-recovery-page { min-height:100vh;display:grid;grid-template-columns:62% 38%;overflow:hidden; }
    .password-recovery-visual { position:relative;min-height:100vh;background-image:linear-gradient(180deg,rgba(3,16,44,.08) 20%,rgba(3,16,44,.82) 100%),url("{{ asset('images/sigym-reference.png') }}");background-size:161.3% 100%;background-position:left center; }
    .password-recovery-center { min-height:100vh;display:grid;place-items:center;position:relative;padding:42px clamp(28px,4vw,62px);background:#fff; }
    .password-recovery-center::before { content:"";position:absolute;left:-54px;top:0;width:108px;height:100%;background:#fff;transform:skew(-7deg); }
    .password-recovery-card { width:min(420px,100%);position:relative;z-index:1; }
    .password-recovery-card h1 { color:#17244a;font-size:clamp(27px,2.5vw,36px);font-weight:800;letter-spacing:-.8px;text-align:center;margin-bottom:8px; }
    .password-recovery-subtitle { color:#526078;text-align:center;line-height:1.6;margin-bottom:26px; }
    .password-recovery-field { margin-bottom:18px; }
    .password-recovery-field label { display:block;color:#1f2937;font-size:13px;font-weight:700;margin-bottom:7px; }
    .password-recovery-field .form-control { height:52px;border:1px solid #d1d7df;border-radius:14px;box-shadow:none; }
    .password-recovery-field .form-control:focus { border-color:#2878ff;box-shadow:0 0 0 3px rgba(40,120,255,.1); }
    .password-recovery-submit { height:52px;border:0;border-radius:13px;background:linear-gradient(90deg,#2878ff,#0758c9);font-weight:700;box-shadow:0 8px 18px rgba(8,91,203,.2); }
    .password-recovery-back { display:block;margin-top:22px;color:#2563eb;font-size:13px;font-weight:600;text-align:center;text-decoration:none; }
    @media(max-width:900px) { .password-recovery-page { grid-template-columns:52% 48%; } }
    @media(max-width:680px) {
        .password-recovery-page { display:block; }
        .password-recovery-visual { min-height:210px;height:210px;background-size:cover;background-position:left center; }
        .password-recovery-center { min-height:calc(100vh - 210px);padding:32px 22px 40px; }
        .password-recovery-center::before { display:none; }
    }
</style>
<div class="password-recovery-page">
    <section class="password-recovery-visual" aria-hidden="true"></section>
    <section class="password-recovery-center" aria-label="Atur ulang password SI-GYM">
        <div class="password-recovery-card">
            <h1>Password Baru</h1>
            <p class="password-recovery-subtitle">Buat password baru untuk akun SI-GYM Anda.</p>
            @if($errors->any())
                <div class="alert alert-danger py-2" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="password-recovery-field">
                    <label for="email">Alamat Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $email) }}" class="form-control" autocomplete="email" required autofocus>
                </div>
                <div class="password-recovery-field">
                    <label for="password">Password Baru</label>
                    <input id="password" type="password" name="password" class="form-control" autocomplete="new-password" minlength="8" required>
                </div>
                <div class="password-recovery-field">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" minlength="8" required>
                </div>
                <button type="submit" class="password-recovery-submit btn btn-primary w-100">Simpan Password Baru</button>
            </form>
            <a class="password-recovery-back" href="{{ route('login') }}">Kembali ke Login</a>
        </div>
    </section>
</div>
@endsection
