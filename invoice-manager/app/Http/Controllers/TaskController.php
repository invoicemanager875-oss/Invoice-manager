<?php

namespace App\Http\Controllers;

use App\Models\FormOrder;
use App\Models\FormOrderTask;

class TaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $tasks = FormOrderTask::query()
            ->where('assigned_to', $user->id)
            ->with('formOrder.brand')
            ->orderBy('urutan')
            ->get();

        $pendingByFormOrder = $tasks->where('is_done', false)->groupBy('form_order_id');
        $done = $tasks->where('is_done', true)->sortByDesc('completed_at')->values();

        $brandIds = $user->brands()->pluck('brands.id');

        $available = FormOrderTask::query()
            ->whereNull('assigned_to')
            ->where('is_done', false)
            ->whereHas('formOrder', fn ($q) => $q->whereIn('brand_id', $brandIds)->where('status', '!=', 'selesai'))
            ->with('formOrder.brand')
            ->orderBy('urutan')
            ->get();

        return view('tasks.index', [
            'pendingByFormOrder' => $pendingByFormOrder,
            'done' => $done,
            'available' => $available,
            'activeFormOrderId' => $user->active_form_order_id,
        ]);
    }

    public function toggle(FormOrderTask $task)
    {
        $this->authorize('update', $task);

        $isDone = ! $task->is_done;

        $task->update([
            'is_done' => $isDone,
            'completed_at' => $isDone ? now() : null,
        ]);

        // Kalau ini tugas pending terakhir milik drafter di proyek yang sedang
        // ditandai "fokus", lepas otomatis penanda fokusnya karena tidak ada lagi yang dikerjakan.
        if ($isDone) {
            $user = auth()->user();

            if ($user->active_form_order_id === $task->form_order_id) {
                $stillHasPending = FormOrderTask::query()
                    ->where('form_order_id', $task->form_order_id)
                    ->where('assigned_to', $user->id)
                    ->where('is_done', false)
                    ->exists();

                if (! $stillHasPending) {
                    $user->forceFill(['active_form_order_id' => null])->save();
                }
            }
        }

        return back()->with('success', $isDone ? 'Tugas ditandai selesai.' : 'Tugas dibuka kembali.');
    }

    public function claim(FormOrderTask $task)
    {
        $this->authorize('claim', $task);

        $task->update(['assigned_to' => auth()->id()]);

        return back()->with('success', 'Tugas berhasil diambil.');
    }

    public function toggleActiveProject(FormOrder $form_order)
    {
        $user = auth()->user();

        $hasPendingTask = FormOrderTask::query()
            ->where('form_order_id', $form_order->id)
            ->where('assigned_to', $user->id)
            ->where('is_done', false)
            ->exists();

        abort_unless($hasPendingTask, 403);

        $isActive = $user->active_form_order_id === $form_order->id;

        $user->forceFill(['active_form_order_id' => $isActive ? null : $form_order->id])->save();

        return back()->with('success', $isActive ? 'Fokus proyek dinonaktifkan.' : 'Proyek ini ditandai sebagai fokus Anda saat ini.');
    }
}
