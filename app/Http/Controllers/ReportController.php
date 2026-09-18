<?php

namespace App\Http\Controllers;

use App\Models\FormOrderTask;
use App\Models\Invoice;
use App\Models\User;
use App\Services\RekapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index(Request $request, RekapService $rekap)
    {
        $this->authorize('viewAny', Invoice::class);

        $user = auth()->user();

        $validTabs = ['bulanan', 'tahunan', 'brand', 'leads'];

        if (config('features.drafter_tasks')) {
            $validTabs[] = 'kpi';
        }

        $tab = in_array($request->query('tab'), $validTabs, true)
            ? $request->query('tab')
            : 'bulanan';

        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $data = match ($tab) {
            'tahunan' => [
                'yearlyTotals' => $rekap->yearlyTotals($user),
            ],
            'brand' => [
                'revenueByBrand' => $rekap->revenueByBrand($user),
                'leadsByBrand' => $rekap->leadsByBrand($user),
            ],
            'leads' => [
                'leadsByStatus' => $rekap->leadsByStatus($user),
                'leadsBySumber' => $rekap->leadsBySumber($user),
                'leadsMonthlyTrend' => $rekap->leadsMonthlyTrend($user, $year),
            ],
            'kpi' => [
                'drafterKpi' => $this->drafterKpi($user, $year, $month),
            ],
            default => [
                'monthlyRevenue' => $rekap->monthlyRevenue($user, $year),
                'monthlyLeadsDeals' => $rekap->monthlyLeadsDeals($user, $year),
            ],
        };

        return view('reports.index', compact('tab', 'year', 'month', 'data'));
    }

    public function kpiPdf(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $user = auth()->user();
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $drafterKpi = $this->drafterKpi($user, $year, $month);

        $filename = "KPI-Drafter-{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.pdf';

        return Pdf::loadView('reports.kpi-pdf', [
            'drafterKpi' => $drafterKpi,
            'year' => $year,
            'month' => $month,
        ])->stream($filename);
    }

    /**
     * KPI bulanan per drafter: dari tugas yang mereka selesaikan pada bulan
     * tersebut, berapa yang tepat waktu vs terlambat terhadap deadline Form
     * Order-nya. Tugas pada Form Order tanpa deadline dihitung terpisah
     * (tidak masuk basis persentase tepat waktu). Meniru "KPI bulanan" di
     * referensi basyid_pm1.
     */
    private function drafterKpi(User $user, int $year, int $month): Collection
    {
        $isSuperAdmin = $user->hasRole('superadmin');

        $tasks = FormOrderTask::query()
            ->whereNotNull('assigned_to')
            ->where('is_done', true)
            ->whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month)
            ->when(
                ! $isSuperAdmin,
                fn ($query) => $query->whereHas('formOrder', fn ($fo) => $fo->whereIn('brand_id', $user->ownedBrands()->pluck('id')))
            )
            ->with(['assignee', 'formOrder'])
            ->get();

        return $tasks->groupBy('assigned_to')
            ->map(function (Collection $group) {
                $withDeadline = $group->filter(fn (FormOrderTask $t) => $t->formOrder && $t->formOrder->deadline);

                $tepatWaktu = $withDeadline->filter(
                    fn (FormOrderTask $t) => $t->completed_at->copy()->startOfDay()->lte($t->formOrder->deadline->copy()->startOfDay())
                )->count();

                $terlambat = $withDeadline->count() - $tepatWaktu;

                return [
                    'drafter' => $group->first()->assignee,
                    'total_selesai' => $group->count(),
                    'tepat_waktu' => $tepatWaktu,
                    'terlambat' => $terlambat,
                    'tanpa_deadline' => $group->count() - $withDeadline->count(),
                    'persen_tepat_waktu' => $withDeadline->count() > 0
                        ? (int) round($tepatWaktu / $withDeadline->count() * 100)
                        : null,
                ];
            })
            ->sortByDesc('total_selesai')
            ->values();
    }
}
