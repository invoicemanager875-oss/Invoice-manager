<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\FormOrder;
use App\Models\FormOrderTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaskHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'drafter', 'guard_name' => 'web']);
    }

    public function test_migration_rollback_safely_removes_only_drafter_role(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'superadmin']);
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'drafter']);

        $migration = require database_path('migrations/2026_09_16_163500_add_drafter_role_to_roles_table.php');
        $migration->down();

        $this->assertDatabaseMissing('roles', ['name' => 'drafter']);
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'superadmin']);
    }

    public function test_drafter_can_claim_available_task_successfully(): void
    {
        $brand = Brand::create(['name' => 'Brand Alpha', 'code' => 'BA']);
        $drafter = User::factory()->create();
        $drafter->assignRole('drafter');
        $drafter->brands()->attach($brand->id);

        $formOrder = FormOrder::create([
            'brand_id' => $brand->id,
            'created_by' => $drafter->id,
            'nomor' => 'FO-001',
            'nomor_urut' => 1,
            'tahun' => 2026,
            'bulan' => 9,
            'tanggal_order' => now(),
            'nama_klien' => 'Klien Alpha',
            'status' => 'draft',
        ]);

        $task = FormOrderTask::create([
            'form_order_id' => $formOrder->id,
            'name' => 'Gambar 3D View',
            'urutan' => 1,
            'assigned_to' => null,
            'is_done' => false,
        ]);

        $response = $this->actingAs($drafter)->patch(route('tasks.claim', $task));

        $response->assertSessionHas('success', 'Tugas berhasil diambil.');
        $this->assertDatabaseHas('form_order_tasks', [
            'id' => $task->id,
            'assigned_to' => $drafter->id,
        ]);
    }

    public function test_double_claim_prevents_overwrite_and_returns_error(): void
    {
        $brand = Brand::create(['name' => 'Brand Alpha', 'code' => 'BA']);

        $drafter1 = User::factory()->create(['name' => 'Drafter 1']);
        $drafter1->assignRole('drafter');
        $drafter1->brands()->attach($brand->id);

        $drafter2 = User::factory()->create(['name' => 'Drafter 2']);
        $drafter2->assignRole('drafter');
        $drafter2->brands()->attach($brand->id);

        $formOrder = FormOrder::create([
            'brand_id' => $brand->id,
            'created_by' => $drafter1->id,
            'nomor' => 'FO-002',
            'nomor_urut' => 2,
            'tahun' => 2026,
            'bulan' => 9,
            'tanggal_order' => now(),
            'nama_klien' => 'Klien Beta',
            'status' => 'draft',
        ]);

        $task = FormOrderTask::create([
            'form_order_id' => $formOrder->id,
            'name' => 'Desain Denah',
            'urutan' => 1,
            'assigned_to' => null,
            'is_done' => false,
        ]);

        // First drafter claims successfully
        $response1 = $this->actingAs($drafter1)->patch(route('tasks.claim', $task));
        $response1->assertSessionHas('success', 'Tugas berhasil diambil.');

        // Second drafter tries to claim the same task
        $response2 = $this->actingAs($drafter2)->patch(route('tasks.claim', $task));
        $response2->assertForbidden();

        $task->refresh();
        $this->assertSame($drafter1->id, $task->assigned_to);
    }

    public function test_concurrent_atomic_update_failure_returns_session_error(): void
    {
        $brand = Brand::create(['name' => 'Brand Alpha', 'code' => 'BA']);
        $drafter = User::factory()->create();
        $drafter->assignRole('drafter');
        $drafter->brands()->attach($brand->id);

        $formOrder = FormOrder::create([
            'brand_id' => $brand->id,
            'created_by' => $drafter->id,
            'nomor' => 'FO-003',
            'nomor_urut' => 3,
            'tahun' => 2026,
            'bulan' => 9,
            'tanggal_order' => now(),
            'nama_klien' => 'Klien Gamma',
            'status' => 'draft',
        ]);

        $task = FormOrderTask::create([
            'form_order_id' => $formOrder->id,
            'name' => 'MEP Plan',
            'urutan' => 1,
            'assigned_to' => null,
            'is_done' => false,
        ]);

        $otherUser = User::factory()->create();

        // Atomic check: when affected rows = 0
        FormOrderTask::whereKey($task->id)->update(['assigned_to' => $otherUser->id]);

        $affected = FormOrderTask::query()
            ->whereKey($task->id)
            ->whereNull('assigned_to')
            ->where('is_done', false)
            ->update(['assigned_to' => $drafter->id]);

        $this->assertSame(0, $affected);
    }

    public function test_atomic_toggle_completed_clears_active_form_order_focus(): void
    {
        $brand = Brand::create(['name' => 'Brand Alpha', 'code' => 'BA']);
        $drafter = User::factory()->create();
        $drafter->assignRole('drafter');
        $drafter->brands()->attach($brand->id);

        $formOrder = FormOrder::create([
            'brand_id' => $brand->id,
            'created_by' => $drafter->id,
            'nomor' => 'FO-004',
            'nomor_urut' => 4,
            'tahun' => 2026,
            'bulan' => 9,
            'tanggal_order' => now(),
            'nama_klien' => 'Klien Delta',
            'status' => 'draft',
        ]);

        $drafter->update(['active_form_order_id' => $formOrder->id]);

        $task = FormOrderTask::create([
            'form_order_id' => $formOrder->id,
            'name' => 'Final 3D Rendering',
            'urutan' => 1,
            'assigned_to' => $drafter->id,
            'is_done' => false,
        ]);

        $response = $this->actingAs($drafter)->patch(route('tasks.toggle', $task));

        $response->assertSessionHas('success', 'Tugas ditandai selesai.');

        $task->refresh();
        $drafter->refresh();

        $this->assertTrue($task->is_done);
        $this->assertNotNull($task->completed_at);
        $this->assertNull($drafter->active_form_order_id);
    }
}
