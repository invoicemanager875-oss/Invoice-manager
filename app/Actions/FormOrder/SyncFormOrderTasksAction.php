<?php

namespace App\Actions\FormOrder;

use App\Models\FormOrder;

class SyncFormOrderTasksAction
{
    /**
     * Selaraskan checklist tugas (FormOrderTask) dengan item lingkup pekerjaan
     * saat ini. Task yang namanya masih ada di lingkup pekerjaan dipertahankan
     * (assignee-nya diperbarui bila berubah); task yang namanya sudah hilang
     * dihapus kecuali sudah ditandai selesai (dipertahankan sebagai histori).
     */
    public function execute(FormOrder $formOrder, array $lingkupPekerjaan, array $tugasAssignments = []): void
    {
        $lingkupPekerjaan = array_values($lingkupPekerjaan);
        $existingByName = $formOrder->tasks()->get()->keyBy('name');
        $keepNames = [];

        foreach ($lingkupPekerjaan as $index => $name) {
            $keepNames[] = $name;
            $assignedTo = $tugasAssignments[$index] ?? null;

            $task = $existingByName->get($name);

            if ($task) {
                $task->update([
                    'assigned_to' => $assignedTo ?: null,
                    'urutan' => $index + 1,
                ]);

                continue;
            }

            $formOrder->tasks()->create([
                'name' => $name,
                'assigned_to' => $assignedTo ?: null,
                'urutan' => $index + 1,
                'is_done' => false,
            ]);
        }

        $formOrder->tasks()
            ->whereNotIn('name', $keepNames)
            ->where('is_done', false)
            ->delete();
    }
}
