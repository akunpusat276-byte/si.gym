@extends('layouts.app')
@section('title', 'Login SI-GYM')
@section('content')
<style>
    body { background: #fff !important; }
    main { width: 100%; }
    .gym-login-page { min-height: 100vh; display: grid; grid-template-columns: 62% 38%; overflow: hidden; }
    .login-visual { position: relative; overflow: hidden; min-height: 100vh; color: white; background-image: linear-gradient(180deg, rgba(3,16,44,.08) 20%, rgba(3,16,44,.82) 100%), url("{{ asset('images/sigym-reference.png') }}"); background-size: 161.3% 100%; background-position: left center; }
    .gym-brand { position: absolute; top: 34px; left: 42px; z-index: 2; display: inline-flex; align-items: center; gap: 12px; color: white; text-decoration: none; font-size: 27px; font-weight: 800; letter-spacing: -1px; }
    .gym-brand-mark { width: 44px; height: 44px; display: grid; place-items: center; border-radius: 50%; border: 2px solid rgba(255,255,255,.95); font-size: 18px; font-style: italic; }
    .visual-copy { position: absolute; z-index: 2; left: 40px; right: 40px; bottom: 38px; max-width: 640px; }
    .visual-copy h1 { font-size: clamp(26px, 3vw, 42px); line-height: 1.15; letter-spacing: -1px; margin: 0 0 12px; font-weight: 700; }
    .visual-copy h1 strong { color: #2878ff; }
    .visual-copy p { margin: 0; max-width: 600px; color: rgba(255,255,255,.88); font-size: 16px; line-height: 1.5; }
    .login-center { min-height: 100vh; display: grid; place-items: center; position: relative; padding: 42px clamp(28px, 4vw, 62px); background: #fff; }
    .login-center::before { content: ""; position: absolute; left: -54px; top: 0; width: 108px; height: 100%; background: #fff; transform: skew(-7deg); transform-origin: center; }
    .login-card { width: min(420px, 100%); position: relative; z-index: 1; }
    .login-card h1 { color: #17244a; font-size: clamp(28px, 2.5vw, 38px); font-weight: 800; letter-spacing: -1px; text-align: center; margin-bottom: 8px; }
    .login-subtitle { text-align: center; color: #526078; margin-bottom: 30px; }
    .login-field { margin-bottom: 18px; }
    .login-field label { display: block; color: #1f2937; font-size: 13px; font-weight: 700; margin-bottom: 7px; }
    .input-shell { position: relative; }
    .input-shell svg { position: absolute; left: 17px; top: 50%; transform: translateY(-50%); width: 19px; height: 19px; color: #2878ff; pointer-events: none; }
    .login-field .form-control { height: 54px; border: 1px 
    solid #d1d7df; border-radius: 16px; box-shadow: none; color: #344054; padding-left: 48px; }
    .login-field .form-control::placeholder { color: #98a2b3; }
    .login-field .form-control:focus { border-color: #2878ff; box-shadow: 0 0 0 3px rgba(40,120,255,.10); }
    .password-wrap { position: relative; }
    .password-wrap .form-control { padding-right: 52px; }
    .password-toggle { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); border: 0; background: transparent; color: #667085; padding: 4px; display: grid; }
    .password-toggle svg { width: 20px; height: 20px; color: #667085; }
    .login-options { color: #667085; font-size: 13px; margin-bottom: 22px; }
    .login-options .form-check-input { width: 16px; height: 16px; margin-top: 2px; }
    .login-options .forgot-password-link { color: #2563eb; font-size: 12px; font-weight: 600; text-decoration: none; }
    .login-options .forgot-password-link:hover { color: #174bb5; text-decoration: underline; }
    .staff-login { height: 54px; border: 0; border-radius: 14px; background: linear-gradient(90deg, #2878ff, #0758c9); font-weight: 700; box-shadow: 0 8px 18px rgba(8,91,203,.20); }
    .staff-login:hover { background: linear-gradient(90deg, #176df2, #054db2); }
    .signup-now { display: flex; align-items: center; gap: 14px; text-align: center; margin-top: 25px; color: #667085; font-size: 13px; white-space: nowrap; }
    .signup-now::before, .signup-now::after { content: ""; height: 1px; background: #dde2e8; flex: 1; }
    .signup-now a { color: #0878ff; font-weight: 600; text-decoration: none; }
    @media(max-width: 900px) {
        .gym-login-page { grid-template-columns: 52% 48%; }
        .gym-brand, .visual-copy { left: 24px; right: 24px; }
    }
    @media(max-width: 680px) {
        .gym-login-page { display: block; }
        .login-visual { min-height: 210px; height: 210px; background-size: cover; background-position: left center; }
        .gym-brand { top: 20px; }
        .visual-copy { left: 20px; right: 20px; bottom: 18px; }
        .visual-copy h1 { font-size: 25px; }
        .visual-copy p, .visual-footer { display: none; }
        .login-center::before { display: none; }
        .login-center { min-height: calc(100vh - 210px); padding
        : 32px 22px 40px; }
        .login-card h1 { font-size: 27px; }
    }
</style>
<div class="gym-login-page">
    <section class="login-visual">
    </section>
    <section class="login-center" aria-label="Form login SI-GYM">
        <div class="login-card">
            <h1>Selamat Datang</h1>
            <p class="login-subtitle">Masuk untuk mengelola akun SI-GYM.</p>
            @if(session('status'))<div class="alert alert-success py-2">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="login-field">
                    <label for="email">Alamat Email</label>
                    <div class="input-shell">
                        <svg data-lucide="mail" aria-hidden="true"></svg>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="name@example.com" autocomplete="email" required autofocus>
                    </div>
                </div>
                <div class="login-field">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <svg data-lucide="lock-keyhole" aria-hidden="true" style="left:17px;top:50%;transform:translateY(-50%);position:absolute;width:19px;color:#2878ff"></svg>
                        <input id="password" type="password" name="password" class="form-control" placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Tampilkan password">
                            <svg data-lucide="eye" aria-hidden="true"></svg>
                        </button>
                    </div>
                </div>
                <div class="login-options d-flex justify-content-between align-items-center">
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Remember me</label></div>
                    <a class="forgot-password-link" href="{{ route('password.request') }}">Lupa Password?</a>
                </div>
                <button type="submit" class="staff-login btn btn-primary w-100">Login</button>
            </form>
            <div class="signup-now"><span>Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></span></div>
        </div>
    </section>
</div>
@push('scripts')
<script>
    document.getElementById('passwordToggle')?.addEventListener('click', function () {
        const password = document.getElementById('password');
        const showPassword = password.type === 'password';
        password.type = showPassword ? 'text' : 'password';
        this.setAttribute('aria-label', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
        this.innerHTML = showPassword ? '<svg data-lucide="eye-off" aria-hidden="true"></svg>' : '<svg data-lucide="eye" aria-hidden="true"></svg>';
        if (window.lucide) window.lucide.createIcons();
    });
</script>
@endpush
@endsection
