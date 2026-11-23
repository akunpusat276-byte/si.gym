<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberDashboardController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegistrantDashboardController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route(match (auth()->user()->role) {
        'admin' => 'admin.dashboard',
        'owner' => 'owner.dashboard',
        'member' => 'member.dashboard',
        default => 'registrant.dashboard',
    })
    : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.status');
    Route::resource('registrations', RegistrationController::class)->except(['create', 'store']);
    Route::patch('registrations/{registration}/status', [RegistrationController::class, 'updateStatus'])->name('registrations.status');
    Route::resource('members', MemberController::class);
    Route::patch('members/{member}/status', [MemberController::class, 'toggleStatus'])->name('members.status');
    Route::resource('packages', PackageController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('memberships', MembershipController::class);
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::resource('settings/banks', BankController::class)->names('settings.banks')->parameters(['banks' => 'bank'])->except(['show']);
    Route::patch('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::patch('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::get('payments/{payment}/proof', [PaymentController::class, 'proof'])->name('payments.proof');
});

Route::middleware(['auth', 'owner'])->prefix('owner')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('owner.dashboard');
    Route::get('/profile', fn () => view('members.profile'))->name('owner.profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('owner.profile.update');
    Route::put('/password', [AuthController::class, 'updatePassword'])->name('owner.password.update');
    Route::match(['POST', 'PUT', 'PATCH', 'DELETE'], '/{path}', fn () => abort(
        403,
        'Pemilik hanya memiliki akses baca dan tidak dapat mengubah data.'
    ))->where('path', '.*');
    Route::get('/registrations', [RegistrationController::class, 'index'])->name('owner.registrations');
    Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('owner.registration.show');
    Route::get('/members', [MemberController::class, 'index'])->name('owner.members');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('owner.member.show');
    Route::get('/packages', [PackageController::class, 'index'])->name('owner.packages');
    Route::get('/packages/{package}', [PackageController::class, 'show'])->name('owner.packages.show');
    Route::get('/payments', [PaymentController::class, 'index'])->name('owner.payments');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('owner.payment.show');
    Route::get('/payments/{payment}/proof', [PaymentController::class, 'proof'])->name('owner.payments.proof');
    Route::get('/memberships', [MembershipController::class, 'index'])->name('owner.memberships');
    Route::get('/memberships/{membership}', [MembershipController::class, 'show'])->name('owner.membership.show');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('owner.reports.export');
    Route::get('/reports', [ReportController::class, 'index'])->name('owner.reports');
    Route::get('/settings', [SettingController::class, 'index'])->name('owner.settings');
});

Route::middleware(['auth', 'member'])->prefix('member')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('member.dashboard');
    Route::get('/profile', fn () => view('members.profile'))->name('member.profile');
    Route::get('/payments', [PaymentController::class, 'index'])->name('member.payments');
    Route::get('/memberships', [MembershipController::class, 'index'])->name('member.memberships');
    Route::get('/memberships/{membership}', [MembershipController::class, 'show'])->name('member.memberships.show');
    Route::post('/memberships/{membership}/renewal', [MembershipController::class, 'requestRenewal'])->name('member.memberships.renewal');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('member.profile.update');
    Route::put('/password', [AuthController::class, 'updatePassword'])->name('member.password.update');
    Route::get('/payments/{payment}/checkout', [PaymentController::class, 'checkout'])->name('member.payments.checkout');
    Route::post('/payments/{payment}/bank', [PaymentController::class, 'selectBank'])->name('member.payments.bank');
    Route::post('/payments/{payment}/cash', [PaymentController::class, 'selectCash'])->name('member.payments.cash');
    Route::get('/payments/{payment}/upload-proof', [PaymentController::class, 'uploadProof'])->name('member.payments.upload-proof');
    Route::post('/payments/{payment}/upload-proof', [PaymentController::class, 'submitProof'])->name('member.payments.submit-proof');
    Route::get('/payments/{payment}/proof', [PaymentController::class, 'proof'])->name('member.payments.proof');
    Route::get('/payments/{payment}/detail', [PaymentController::class, 'show'])->name('member.payments.show');
});

Route::middleware(['auth', 'registrant'])->prefix('pendaftar')->group(function () {
    Route::get('/dashboard', [RegistrantDashboardController::class, 'index'])->name('registrant.dashboard');
    Route::get('/packages/{package}', [RegistrantDashboardController::class, 'showPackage'])->name('registrant.packages.show');
    Route::get('/profile', fn () => view('members.profile'))->name('registrant.profile');
    Route::get('/registration/create', [RegistrationController::class, 'create'])->name('registrations.create');
    Route::post('/registration', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::get('/registration/status', [RegistrationController::class, 'status'])->name('registrations.status.me');
    Route::get('/registration/{registration}', [RegistrationController::class, 'applicantShow'])->name('registrations.show.me');
    Route::get('/registration/{registration}/edit', [RegistrationController::class, 'applicantEdit'])->name('registrations.edit.me');
    Route::put('/registration/{registration}', [RegistrationController::class, 'applicantUpdate'])->name('registrations.update.me');
    Route::delete('/registration/{registration}', [RegistrationController::class, 'cancel'])->name('registrations.cancel');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('registrant.profile.update');
    Route::put('/password', [AuthController::class, 'updatePassword'])->name('registrant.password.update');
    Route::get('/payments', [PaymentController::class, 'index'])->name('registrant.payments');
    Route::get('/payments/{payment}/checkout', [PaymentController::class, 'checkout'])->name('registrant.payments.checkout');
    Route::post('/payments/{payment}/bank', [PaymentController::class, 'selectBank'])->name('registrant.payments.bank');
    Route::post('/payments/{payment}/cash', [PaymentController::class, 'selectCash'])->name('registrant.payments.cash');
    Route::get('/payments/{payment}/upload-proof', [PaymentController::class, 'uploadProof'])->name('registrant.payments.upload-proof');
    Route::post('/payments/{payment}/upload-proof', [PaymentController::class, 'submitProof'])->name('registrant.payments.submit-proof');
    Route::get('/payments/{payment}/proof', [PaymentController::class, 'proof'])->name('registrant.payments.proof');
    Route::get('/payments/{payment}/detail', [PaymentController::class, 'show'])->name('registrant.payments.show');
});
