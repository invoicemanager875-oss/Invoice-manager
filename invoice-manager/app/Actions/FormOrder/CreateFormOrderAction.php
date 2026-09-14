<?php

namespace App\Actions\FormOrder;

use App\Actions\Concerns\StoresImagesAsPng;
use App\Models\Brand;
use App\Models\FormOrder;
use App\Services\FormOrderNumberService;
use Illuminate\Support\Facades\DB;

class CreateFormOrderAction
{
    use StoresImagesAsPng;

    public function __construct(
        protected FormOrderNumberService $numberService,
        protected SyncFormOrderTasksAction $syncTasks,
    ) {
    }

    public function execute(array $data, int $createdBy): FormOrder
    {
        return DB::transaction(function () use ($data, $createdBy) {

            $brand = Brand::findOrFail($data['brand_id']);

            $number = $this->numberService->generate($brand->id);

            $formOrder = FormOrder::create([
                'brand_id' => $brand->id,
                'created_by' => $createdBy,
                'invoice_id' => $data['invoice_id'] ?? null,
                'nomor' => $number['nomor'],
                'nomor_urut' => $number['nomor_urut'],
                'tahun' => $number['tahun'],
                'bulan' => $number['bulan'],
                'tanggal_order' => $data['tanggal_order'],
                'deadline' => $data['deadline'] ?? null,
                'nama_klien' => $data['nama_klien'],
                'lokasi_project' => $data['lokasi_project'] ?? null,
                'jenis_pekerjaan' => $data['jenis_pekerjaan'] ?? null,
                'ukuran_bangunan' => $data['ukuran_bangunan'] ?? null,
                'arah_mata_angin' => $data['arah_mata_angin'] ?? null,
                'share_location' => $data['share_location'] ?? null,
                'lingkup_pekerjaan' => array_values(array_filter($data['lingkup_pekerjaan'] ?? [])),
                'catatan_klien' => $data['catatan_klien'] ?? null,
                'status' => 'draft',
            ]);

            foreach ($data['images'] ?? [] as $index => $image) {
                if (empty($image['file'])) {
                    continue;
                }

                $path = $this->storeImageAsPng($image['file'], 'form-orders/images');

                $formOrder->images()->create([
                    'path' => $path,
                    'caption' => $image['caption'] ?? null,
                    'size' => $image['size'] ?? 'sedang',
                    'urutan' => $index + 1,
                ]);
            }

            foreach ($data['revisions'] ?? [] as $index => $revision) {
                if (empty($revision['catatan']) && empty($revision['file'])) {
                    continue;
                }

                $formOrder->revisions()->create([
                    'catatan' => $revision['catatan'] ?? null,
                    'path' => ! empty($revision['file']) ? $this->storeImageAsPng($revision['file'], 'form-orders/revisions') : null,
                    'urutan' => $index + 1,
                ]);
            }

            if (config('features.drafter_tasks')) {
                $this->syncTasks->execute($formOrder, $data['lingkup_pekerjaan'] ?? [], $data['tugas_assignments'] ?? []);
            }

            return $formOrder;
        });
    }
}
