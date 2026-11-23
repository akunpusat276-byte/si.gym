@extends('layouts.app')
@section('title', 'Registrasi SI-GYM')
@section('content')
<style>
    body { background: #fff !important; }
    main { width: 100%; }
    .gym-auth-page { min-height: 100vh; display: grid; grid-template-columns: 58% 42%; }
    .auth-visual { position: relative; overflow: hidden; min-height: 100vh; color: white; background-image: linear-gradient(180deg, rgba(3,16,44,.08) 20%, rgba(3,16,44,.82) 100%), url("{{ asset('images/sigym-reference.png') }}"); background-size: 161.3% 100%; background-position: left center; }
    .auth-center { min-height: 100vh; display: grid; place-items: center; padding: 42px clamp(28px, 5vw, 78px); background: #fff; }
    .auth-card { width: min(430px, 100%); }
    .auth-card h1 { color: #344054; font-size: 30px; font-weight: 700; letter-spacing: -.8px; text-align: center; margin-bottom: 8px; }
    .auth-subtitle { text-align: center; color: #667085; margin-bottom: 28px; }
    .auth-field { margin-bottom: 16px; }
    .auth-field label { display: block; color: #667085; font-size: 13px; margin-bottom: 7px; }
    .auth-field .form-control { height: 48px; border: 1px solid #d0d5dd; border-radius: 7px; box-shadow: none; color: #344054; }
    .auth-field .form-control::placeholder { color: #98a2b3; }
    .auth-field .form-control:focus { border-color: #6b8eb8; box-shadow: 0 0 0 3px rgba(107,142,184,.12); }
    .auth-submit { height: 48px; border: 0; border-radius: 7px; background: #607d9f; font-weight: 600; margin-top: 8px; box-shadow: 0 5px 14px rgba(96,125,159,.18); }
    .auth-submit:hover { background: #526f90; }
    .auth-link { text-align: center; margin-top: 24px; color: #667085; font-size: 13px; }
    .auth-link a { color: #2563eb; font-weight: 600; text-decoration: none; }
    @media(max-width: 900px) {
        .gym-auth-page { grid-template-columns: 48% 52%; }
    }
    @media(max-width: 680px) {
        .gym-auth-page { display: block; }
        .auth-visual { min-height: 190px; height: 190px; background-size: cover; background-position: left center; }
        .auth-center { min-height: calc(100vh - 190px); padding: 30px 22px 40px; }
        .auth-card h1 { font-size: 27px; }
    }
</style>
<div class="gym-auth-page">
    <section class="auth-visual">
    </section>
    <main class="auth-center">
        <div class="auth-card">
            <h1>Buat Akun Baru</h1>
            <p class="auth-subtitle">Daftar sebagai calon member SI-GYM.</p>
            @if($errors->any())
                <div class="alert alert-danger py-2">
                    {{ $errors->first() }}
                </div>
            @endif
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="auth-field">
                    <label for="name">Nama Lengkap</label>
                    <input id="name" class="form-control" name="name" value="{{ old('name') }}" placeholder="Nama lengkap Anda" autocomplete="name" required>
                </div>
                <div class="auth-field">
                    <label for="email">Alamat Email</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" autocomplete="email" required>
                </div>
                <div class="auth-field">
                    <label for="password">Password</label>
                    <input id="password" class="form-control" type="password" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                </div>
                <div class="auth-field">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" placeholder="Ulangi password" autocomplete="new-password" required>
                </div>
                <button class="auth-submit btn btn-primary w-100" type="submit">Daftar Sekarang</button>
            </form>
            <div class="auth-link">Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></div>
        </div>
    </main>
</div>
@endsection
