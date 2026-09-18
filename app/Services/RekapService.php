<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RekapService
{
    public function monthlyRevenue(User $user, int $year): array
    {
        $rows = $this->invoiceQuery($user)
            ->lunas()
            ->whereYear('tanggal', $year)
            ->get(['tanggal', 'total']);

        $byMonth = $rows->groupBy(fn ($row) => $row->tanggal->month)
            ->map(fn ($group) => (float) $group->sum('total'));

        return collect(range(1, 12))
            ->map(fn ($m) => (float) ($byMonth[$m] ?? 0))
            ->all();
    }

    public function monthlyLeadsDeals(User $user, int $year): array
    {
        $rows = $this->leadQuery($user)
            ->deal()
            ->whereYear('tanggal', $year)
            ->get(['tanggal']);

        $byMonth = $rows->groupBy(fn ($row) => $row->tanggal->month)
            ->map(fn ($group) => $group->count());

        return collect(range(1, 12))
            ->map(fn ($m) => (int) ($byMonth[$m] ?? 0))
            ->all();
    }

    public function leadsMonthlyTrend(User $user, int $year): array
    {
        $rows = $this->leadQuery($user)
            ->whereYear('tanggal', $year)
            ->get(['tanggal']);

        $byMonth = $rows->groupBy(fn ($row) => $row->tanggal->month)
            ->map(fn ($group) => $group->count());

        return collect(range(1, 12))
            ->map(fn ($m) => (int) ($byMonth[$m] ?? 0))
            ->all();
    }

    public function yearlyTotals(User $user): Collection
    {
        $invoiceRows = $this->invoiceQuery($user)
            ->lunas()
            ->get(['tanggal', 'total']);

        $invoiceYears = $invoiceRows
            ->groupBy(fn ($row) => $row->tanggal->year)
            ->map(fn ($group) => [
                'revenue' => (float) $group->sum('total'),
                'invoice_count' => $group->count(),
            ]);

        $leadRows = $this->leadQuery($user)->get(['tanggal', 'status']);

        $leadYears = $leadRows
            ->groupBy(fn ($row) => $row->tanggal->year)
            ->map(fn ($group) => [
                'lead_count' => $group->count(),
                'deal_count' => $group->where('status', 'deal')->count(),
            ]);

        $years = $invoiceYears->keys()
            ->merge($leadYears->keys())
            ->unique()
            ->sort()
            ->values();

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return $years->map(fn ($year) => [
            'year' => (int) $year,
            'revenue' => (float) ($invoiceYears[$year]['revenue'] ?? 0),
            'invoice_count' => (int) ($invoiceYears[$year]['invoice_count'] ?? 0),
            'lead_count' => (int) ($leadYears[$year]['lead_count'] ?? 0),
            'deal_count' => (int) ($leadYears[$year]['deal_count'] ?? 0),
        ])->values();
    }

    public function revenueByBrand(User $user): Collection
    {
        return $this->invoiceQuery($user)
            ->lunas()
            ->with('brand:id,name')
            ->selectRaw('brand_id, SUM(total) as total')
            ->groupBy('brand_id')
            ->get()
            ->map(fn ($row) => [
                'brand' => $row->brand->name ?? '-',
                'total' => (float) $row->total,
            ]);
    }

    public function leadsByBrand(User $user): Collection
    {
        return $this->leadQuery($user)
            ->with('brand:id,name')
            ->selectRaw('brand_id, COUNT(*) as total')
            ->groupBy('brand_id')
            ->get()
            ->map(fn ($row) => [
                'brand' => $row->brand->name ?? '-',
                'total' => (int) $row->total,
            ]);
    }

    public function leadsByStatus(User $user): Collection
    {
        $counts = $this->leadQuery($user)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(Lead::STATUSES)
            ->map(fn ($label, $status) => [
                'status' => $status,
                'label' => $label,
                'total' => (int) ($counts[$status] ?? 0),
            ])
            ->values();
    }

    public function leadsBySumber(User $user): Collection
    {
        $counts = $this->leadQuery($user)
            ->selectRaw('sumber, COUNT(*) as total')
            ->groupBy('sumber')
            ->pluck('total', 'sumber');

        return collect(Lead::SUMBERS)
            ->map(fn ($label, $sumber) => [
                'sumber' => $sumber,
                'label' => $label,
                'total' => (int) ($counts[$sumber] ?? 0),
            ])
            ->values();
    }

    private function invoiceQuery(User $user): Builder
    {
        return Invoice::query()
            ->when(
                ! $user->hasRole('superadmin'),
                fn ($query) => $query->whereIn('brand_id', $user->ownedBrands()->pluck('id'))
            );
    }

    private function leadQuery(User $user): Builder
    {
        return Lead::query()
            ->when(
                ! $user->hasRole('superadmin'),
                fn ($query) => $query->whereIn('brand_id', $user->ownedBrands()->pluck('id'))
            );
    }
}
