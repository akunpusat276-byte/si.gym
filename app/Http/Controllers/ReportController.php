<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const CATEGORIES = [
        'payments' => 'Pembayaran',
        'revenue' => 'Pendapatan',
        'members' => 'Member',
        'active-members' => 'Member Aktif',
        'registrations' => 'Pendaftaran',
        'memberships' => 'Membership',
        'expired-memberships' => 'Membership Expired',
    ];

    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $category = $filters['category'] ?? 'payments';
        $records = $this->reportQuery($category, $filters)->paginate(25)->withQueryString();
        $rows = $records->through(fn ($record): array => $this->reportRow($category, $record));

        return view('reports.index', [
            'categories' => self::CATEGORIES,
            'category' => $category,
            'filters' => $filters,
            'rows' => $rows,
            'totalAmount' => in_array($category, ['payments', 'revenue'], true)
                ? (clone $this->reportQuery($category, $filters))->sum('amount')
                : null,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $category = $filters['category'] ?? 'payments';
        $filename = 'laporan-si-gym-'.$category.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($category, $filters): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['ID', 'Tanggal', 'Nama', 'Detail', 'Nominal (Rp)', 'Status']);

            foreach ($this->reportQuery($category, $filters)->lazy(500) as $record) {
                fputcsv($stream, array_values($this->reportRow($category, $record)));
            }

            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{category?: string, search?: string, from?: string, to?: string, month?: int, year?: int}
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'category' => ['nullable', Rule::in(array_keys(self::CATEGORIES))],
            'search' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function reportQuery(string $category, array $filters): Builder
    {
        $query = match ($category) {
            'payments', 'revenue' => Payment::query()
                ->with(['user', 'package'])
                ->when($category === 'revenue', fn (Builder $query) => $query->where('status', 'Lunas')),
            'registrations' => Registration::query()->with(['user', 'package']),
            'members', 'active-members' => Member::query()->with(['user', 'memberships.package'])
                ->when($category === 'active-members', fn (Builder $query) => $query
                    ->whereNotIn('status', ['Nonaktif', 'tidak aktif'])
                    ->whereHas('memberships', fn (Builder $membershipQuery) => $membershipQuery
                        ->whereDate('start_date', '<=', today())
                        ->whereDate('end_date', '>=', today()))),
            'memberships', 'expired-memberships' => Membership::query()
                ->with(['member.user', 'package', 'payment'])
                ->when($category === 'expired-memberships', fn (Builder $query) => $query->whereDate('end_date', '<', today())),
            default => throw new \InvalidArgumentException('Kategori laporan tidak valid.'),
        };
        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function (Builder $query) use ($category, $search): void {
                match ($category) {
                    'payments', 'revenue' => $query
                        ->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('package', fn (Builder $packageQuery) => $packageQuery->where('name', 'like', "%{$search}%")),
                    'registrations' => $query
                        ->where('registration_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('package', fn (Builder $packageQuery) => $packageQuery->where('name', 'like', "%{$search}%")),
                    'members', 'active-members' => $query
                        ->where('member_code', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")),
                    default => $query
                        ->whereHas('member', fn (Builder $memberQuery) => $memberQuery
                            ->where('member_code', 'like', "%{$search}%")
                            ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%")))
                        ->orWhereHas('package', fn (Builder $packageQuery) => $packageQuery->where('name', 'like', "%{$search}%")),
                };
            });
        }

        $dateColumn = match ($category) {
            'payments', 'revenue' => 'payment_date',
            'memberships', 'expired-memberships' => 'start_date',
            default => 'created_at',
        };

        return $query
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate($dateColumn, '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate($dateColumn, '<=', $to))
            ->when($filters['month'] ?? null, fn (Builder $query, int $month) => $query->whereMonth($dateColumn, $month))
            ->when($filters['year'] ?? null, fn (Builder $query, int $year) => $query->whereYear($dateColumn, $year))
            ->latest($dateColumn);
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string|int|float, 5: string}
     */
    private function reportRow(string $category, object $record): array
    {
        return match ($category) {
            'payments', 'revenue' => [
                $record->invoice_number,
                $record->payment_date?->format('Y-m-d') ?? $record->created_at?->format('Y-m-d') ?? '',
                $record->user?->name ?? '',
                $record->package?->name ?? '',
                $record->amount,
                $record->status,
            ],
            'registrations' => [
                $record->registration_number,
                $record->created_at?->format('Y-m-d') ?? '',
                $record->user?->name ?? '',
                $record->package?->name ?? '',
                '',
                $record->status,
            ],
            'members', 'active-members' => [
                $record->member_code,
                $record->created_at?->format('Y-m-d') ?? '',
                $record->user?->name ?? '',
                $record->user?->email ?? '',
                '',
                $record->status ?? '',
            ],
            default => [
                (string) $record->id,
                $record->start_date?->format('Y-m-d') ?? '',
                $record->member?->user?->name ?? '',
                $record->package?->name ?? '',
                $record->payment?->amount ?? '',
                $record->calculated_status,
            ],
        };
    }
}
