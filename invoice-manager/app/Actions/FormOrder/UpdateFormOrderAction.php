<?php

namespace App\Actions\FormOrder;

use App\Actions\Concerns\StoresImagesAsPng;
use App\Models\FormOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateFormOrderAction
{
    use StoresImagesAsPng;

    public function __construct(
        protected SyncFormOrderTasksAction $syncTasks,
    ) {
    }

    public function execute(FormOrder $formOrder, array $data): FormOrder
    {
        return DB::transaction(function () use ($formOrder, $data) {

            $formOrder->update([
                'invoice_id' => $data['invoice_id'] ?? null,
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
            ]);

            $this->removeImages($formOrder, $data['remove_image_ids'] ?? []);
            $this->updateExistingCaptions($formOrder, $data['existing_images'] ?? []);
            $this->addNewImages($formOrder, $data['images'] ?? []);

            $this->removeRevisions($formOrder, $data['remove_revision_ids'] ?? []);
            $this->addNewRevisions($formOrder, $data['revisions'] ?? []);

            if (config('features.drafter_tasks')) {
                $this->syncTasks->execute($formOrder, $data['lingkup_pekerjaan'] ?? [], $data['tugas_assignments'] ?? []);
            }

            return $formOrder->refresh();
        });
    }

    protected function removeImages(FormOrder $formOrder, array $removeIds): void
    {
        if (empty($removeIds)) {
            return;
        }

        $images = $formOrder->images()->whereIn('id', $removeIds)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
    }

    protected function updateExistingCaptions(FormOrder $formOrder, array $existingImages): void
    {
        foreach ($existingImages as $id => $data) {
            $formOrder->images()->where('id', $id)->update([
                'caption' => $data['caption'] ?? null,
                'size' => $data['size'] ?? 'sedang',
            ]);
        }
    }

    protected function addNewImages(FormOrder $formOrder, array $images): void
    {
        $nextUrutan = (int) $formOrder->images()->max('urutan') + 1;

        foreach ($images as $image) {
            if (empty($image['file'])) {
                continue;
            }

            $path = $this->storeImageAsPng($image['file'], 'form-orders/images');

            $formOrder->images()->create([
                'path' => $path,
                'caption' => $image['caption'] ?? null,
                'size' => $image['size'] ?? 'sedang',
                'urutan' => $nextUrutan++,
            ]);
        }
    }

    protected function removeRevisions(FormOrder $formOrder, array $removeIds): void
    {
        if (empty($removeIds)) {
            return;
        }

        $revisions = $formOrder->revisions()->whereIn('id', $removeIds)->get();

        foreach ($revisions as $revision) {
            if ($revision->path) {
                Storage::disk('public')->delete($revision->path);
            }

            $revision->delete();
        }
    }

    protected function addNewRevisions(FormOrder $formOrder, array $revisions): void
    {
        $nextUrutan = (int) $formOrder->revisions()->max('urutan') + 1;

        foreach ($revisions as $revision) {
            if (empty($revision['catatan']) && empty($revision['file'])) {
                continue;
            }

            $path = ! empty($revision['file']) ? $this->storeImageAsPng($revision['file'], 'form-orders/revisions') : null;

            $formOrder->revisions()->create([
                'catatan' => $revision['catatan'] ?? null,
                'path' => $path,
                'urutan' => $nextUrutan++,
            ]);
        }
    }
}
