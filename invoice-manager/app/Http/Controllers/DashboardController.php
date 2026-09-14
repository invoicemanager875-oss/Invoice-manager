<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\FormOrder;
use App\Models\FormOrderTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $isSuperAdmin = $user->hasRole('superadmin');

        // Brand milik user (superadmin melihat semua, admin biasa hanya yang ia buat sendiri)
        $brands = $isSuperAdmin ? Brand::all() : $user->ownedBrands;

        $draftersStatus = $this->draftersStatus($isSuperAdmin, $brands->pluck('id'));
        $stats = $this->projectStats($isSuperAdmin, $brands->pluck('id'));

        return view('dashboard', [
            'isAdmin'        => $isAdmin,
            'isSuperAdmin'   => $isSuperAdmin,
            'brandCount'     => $brands->count(),
            'stats'          => $stats,
            'notifications'  => [],
            'draftersStatus' => $draftersStatus,
        ]);
    }

    /**
     * Ringkasan proyek (Form Order) & tugas, mengikuti konsep kartu statistik
     * di referensi basyid_pm1 (Total Proyek, Sedang Berjalan, Mendekati
     * Deadline, Tugas Selesai). Klasifikasi berlangsung/mendekati/terlambat
     * dihitung lewat FormOrder::deadline_status (lihat app/Models/FormOrder.php).
     */
    private function projectStats(bool $isSuperAdmin, \Illuminate\Support\Collection $brandIds): array
    {
        $brandIds = $brandIds->all();

        if (! $isSuperAdmin && empty($brandIds)) {
            return [
                'total_proyek' => 0,
                'sedang_berjalan' => 0,
                'mendekati_deadline' => 0,
                'tugas_selesai' => 0,
                'total_tugas' => 0,
            ];
        }

        $scopeFormOrder = fn ($query) => $isSuperAdmin ? $query : $query->whereIn('brand_id', $brandIds);

        $formOrders = $scopeFormOrder(FormOrder::query())->get(['id', 'status', 'deadline']);

        $sedangBerjalan = $formOrders->filter(fn (FormOrder $fo) => $fo->deadline_status === 'berlangsung')->count();
        $mendekatiDeadline = $formOrders->filter(fn (FormOrder $fo) => in_array($fo->deadline_status, ['mendekati', 'terlambat'], true))->count();

        $scopeTask = fn ($query) => $query->whereHas('formOrder', fn ($fo) => $scopeFormOrder($fo));

        $totalTugas = $scopeTask(FormOrderTask::query())->count();
        $tugasSelesai = $scopeTask(FormOrderTask::query())->where('is_done', true)->count();

        return [
            'total_proyek' => $formOrders->count(),
            'sedang_berjalan' => $sedangBerjalan,
            'mendekati_deadline' => $mendekatiDeadline,
            'tugas_selesai' => $tugasSelesai,
            'total_tugas' => $totalTugas,
        ];
    }

    /**
     * Drafter yang terdaftar pada brand yang bisa dilihat user ini, beserta
     * tugas yang sedang mereka kerjakan (belum selesai, pada Form Order yang
     * belum selesai) untuk ditampilkan sebagai "Status Tim" di dashboard.
     */
    private function draftersStatus(bool $isSuperAdmin, \Illuminate\Support\Collection $brandIds): \Illuminate\Support\Collection
    {
        $brandIds = $brandIds->all();

        if (! $isSuperAdmin && empty($brandIds)) {
            return collect();
        }

        $drafters = User::role('drafter')
            ->when(! $isSuperAdmin, fn ($q) => $q->whereHas('brands', fn ($b) => $b->whereIn('brands.id', $brandIds)))
            ->with(['assignedTasks' => function ($q) use ($isSuperAdmin, $brandIds) {
                $q->where('is_done', false)
                    ->whereHas('formOrder', function ($fo) use ($isSuperAdmin, $brandIds) {
                        $fo->where('status', '!=', 'selesai');
                        if (! $isSuperAdmin) {
                            $fo->whereIn('brand_id', $brandIds);
                        }
                    })
                    ->with('formOrder.brand')
                    ->orderBy('urutan');
            }])
            ->orderBy('name')
            ->get();

        // Kelompokkan per jobdesk (3D/2D/Estimator/Engineering), meniru "Status Tim"
        // di referensi basyid_pm1. Drafter tanpa jobdesk masuk grup "Lainnya" di akhir.
        $groups = $drafters->groupBy(fn (User $drafter) => $drafter->jobdesk ?: 'Lainnya');

        return collect([...User::JOBDESKS, 'Lainnya'])
            ->filter(fn ($label) => $groups->has($label))
            ->mapWithKeys(fn ($label) => [$label => $groups->get($label)]);
    }
}
