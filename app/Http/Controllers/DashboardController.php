<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        $previousMonthRevenue = Payment::where('status', 'Lunas')
            ->whereMonth('payment_date', today()->subMonth()->month)
            ->whereYear('payment_date', today()->subMonth()->year)
            ->sum('amount');
        $monthlyRevenueValue = Payment::where('status', 'Lunas')
            ->whereMonth('payment_date', today()->month)
            ->whereYear('payment_date', today()->year)
            ->sum('amount');
        $revenueGrowth = $previousMonthRevenue > 0
            ? round((($monthlyRevenueValue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
            : ($monthlyRevenueValue > 0 ? 100.0 : 0.0);

        $memberActivity = Member::query()
            ->selectRaw('DATE(created_at) as activity_date, COUNT(*) as total')
            ->whereBetween('created_at', [today()->subDays(6)->startOfDay(), today()->endOfDay()])
            ->groupBy('activity_date')
            ->pluck('total', 'activity_date');
        $memberActivityLabels = collect(range(6, 0))
            ->map(fn (int $daysAgo) => today()->subDays($daysAgo));

        return view('dashboard.admin', [
            'totalMembers' => Member::count(),
            'totalRegistrants' => Registration::whereNotIn('status', ['Ditolak'])->count(),
            'activeMembers' => Member::query()
                ->whereNotIn('status', ['Nonaktif', 'tidak aktif'])
                ->whereHas('memberships', fn ($query) => $query
                    ->whereDate('end_date', '>=', today())
                    ->whereDate('start_date', '<=', today()))
                ->count(),
            'expiredMembers' => Membership::where('end_date', '<', today())->count(),
            'totalPackages' => Package::count(),
            'totalTransactions' => Payment::count(),
            'totalRevenue' => Payment::where('status', 'Lunas')->sum('amount'),
            'monthlyRevenue' => $monthlyRevenueValue,
            'revenueGrowth' => $revenueGrowth,
            'pendingRegistrations' => Registration::whereIn('status', ['Menunggu', 'Menunggu Verifikasi'])->count(),
            'pendingPayments' => Payment::where('status', 'Menunggu Verifikasi')->count(),
            'expiringMemberships' => Membership::with(['member.user', 'package'])
                ->whereDate('start_date', '<=', today())
                ->whereBetween('end_date', [today(), today()->addDays(7)])
                ->latest('end_date')
                ->take(5)
                ->get(),
            'memberActivityLabels' => $memberActivityLabels->map(fn ($date) => $date->translatedFormat('D'))->all(),
            'memberActivityChart' => $memberActivityLabels
                ->map(fn ($date) => (int) ($memberActivity[$date->toDateString()] ?? 0))
                ->all(),
            'registrations' => Registration::with(['user', 'package'])->latest()->take(5)->get(),
            'payments' => Payment::with(['user', 'package'])->latest()->take(5)->get(),
            'recentMemberships' => Membership::with(['member.user', 'package'])->latest()->take(5)->get(),
        ]);
    }
}
