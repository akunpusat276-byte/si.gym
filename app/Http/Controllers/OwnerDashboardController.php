<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->validate([
            'period' => ['nullable', Rule::in(['7d', '30d', '6m', '12m'])],
        ])['period'] ?? '6m';
        $daily = in_array($period, ['7d', '30d'], true);
        $pointCount = match ($period) {
            '7d' => 7,
            '30d' => 30,
            '12m' => 12,
            default => 6,
        };
        $points = collect(range($pointCount - 1, 0))->map(fn (int $offset) => $daily
            ? today()->subDays($offset)
            : today()->startOfMonth()->subMonths($offset));
        $periodStart = $daily ? $points->first()->copy()->startOfDay() : $points->first()->copy()->startOfMonth();
        $periodEnd = today()->endOfDay();
        $revenueBucket = $daily ? 'DATE(payment_date)' : 'SUBSTR(payment_date, 1, 7)';
        $createdBucket = $daily ? 'DATE(created_at)' : 'SUBSTR(created_at, 1, 7)';
        $dateKey = fn ($date): string => $daily ? $date->toDateString() : $date->format('Y-m');
        $labels = $points->map(fn ($date) => $daily
            ? $date->translatedFormat('d M')
            : $date->translatedFormat('M y'));
        $periodStartDate = $periodStart->toDateString();
        $periodEndDate = today()->toDateString();
        $revenueByPeriod = Payment::query()
            ->where('status', 'Lunas')
            ->whereBetween('payment_date', [$periodStartDate, $periodEndDate])
            ->selectRaw("$revenueBucket as period, SUM(amount) as total")
            ->groupByRaw($revenueBucket)
            ->pluck('total', 'period');
        $membersByPeriod = Member::query()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("$createdBucket as period, COUNT(*) as total")
            ->groupByRaw($createdBucket)
            ->pluck('total', 'period');
        $registrationsByPeriod = Registration::query()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw("$createdBucket as period, COUNT(*) as total")
            ->groupByRaw($createdBucket)
            ->pluck('total', 'period');
        $paymentMethodCounts = Payment::query()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw('method, COUNT(*) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        $activeMembers = Member::query()
            ->whereNotIn('status', ['Nonaktif', 'tidak aktif'])
            ->whereHas('memberships', fn (Builder $query) => $query
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today()));
        $stableActiveMembers = (clone $activeMembers)->whereHas('memberships', fn (Builder $query) => $query
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>', today()->addDays(7)))->count();
        $expiringMembers = (clone $activeMembers)->whereHas('memberships', fn (Builder $query) => $query
            ->whereDate('start_date', '<=', today())
            ->whereBetween('end_date', [today(), today()->addDays(7)]))->count();
        $expiredMembers = Member::query()
            ->whereNotIn('status', ['Nonaktif', 'tidak aktif'])
            ->whereHas('memberships', fn (Builder $query) => $query->whereDate('end_date', '<', today()))
            ->whereDoesntHave('memberships', fn (Builder $query) => $query
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today()))
            ->count();

        $members = Member::query()->get(['id', 'birth_date', 'gender']);
        $ageGroups = collect(['< 18' => 0, '18 - 24' => 0, '25 - 34' => 0, '35+' => 0]);
        foreach ($members as $member) {
            if (! $member->birth_date) {
                continue;
            }
            $age = $member->birth_date->age;
            $key = match (true) {
                $age < 18 => '< 18',
                $age <= 24 => '18 - 24',
                $age <= 34 => '25 - 34',
                default => '35+',
            };
            $ageGroups[$key]++;
        }

        return view('dashboard.owner', [
            'totalMembers' => $members->count(),
            'totalRegistrants' => User::where('role', 'pendaftar')->count(),
            'activeMembers' => $stableActiveMembers + $expiringMembers,
            'stableActiveMembers' => $stableActiveMembers,
            'expiringMembers' => $expiringMembers,
            'expiredMembers' => $expiredMembers,
            'totalPackages' => Package::count(),
            'totalTransactions' => Payment::count(),
            'totalRevenue' => Payment::where('status', 'Lunas')
                ->whereNotNull('payment_date')
                ->whereMonth('payment_date', today()->month)
                ->whereYear('payment_date', today()->year)
                ->sum('amount'),
            'chartLabels' => $labels->values(),
            'revenueSeries' => $points->map(fn ($date) => (float) ($revenueByPeriod[$dateKey($date)] ?? 0))->values(),
            'memberGrowthSeries' => $points->map(fn ($date) => (int) ($membersByPeriod[$dateKey($date)] ?? 0))->values(),
            'registrationGrowthSeries' => $points->map(fn ($date) => (int) ($registrationsByPeriod[$dateKey($date)] ?? 0))->values(),
            'paymentMethodCounts' => $paymentMethodCounts,
            'period' => $period,
            'periodOptions' => ['7d' => '7 Hari Terakhir', '30d' => '30 Hari Terakhir', '6m' => '6 Bulan Terakhir', '12m' => '12 Bulan Terakhir'],
            'ageGroups' => $ageGroups,
            'genderDistribution' => [
                'Laki-laki' => $members->where('gender', 'Laki-laki')->count(),
                'Perempuan' => $members->where('gender', 'Perempuan')->count(),
                'Belum diisi' => $members->whereNotIn('gender', ['Laki-laki', 'Perempuan'])->count(),
            ],
            'recentMembers' => Member::with('user')->latest()->take(5)->get(),
            'recentRegistrants' => Registration::with(['user', 'package'])->latest()->take(5)->get(),
            'recentPayments' => Payment::with(['user', 'package'])->latest()->take(5)->get(),
        ]);
    }
}
