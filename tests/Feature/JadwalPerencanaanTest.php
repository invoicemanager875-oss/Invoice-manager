<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\JadwalItem;
use App\Models\JadwalPerencanaan;
use App\Models\User;
use App\Services\JadwalCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JadwalPerencanaanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'drafter', 'guard_name' => 'web']);
    }

    public function test_skenario_1_pembuatan_jadwal_baru_dan_hitung_tanggal_selesai_otomatis(): void
    {
        $brand = Brand::create(['name' => 'Basyid Studio', 'code' => 'BS']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->brands()->attach($brand->id);

        $response = $this->actingAs($admin)->post(route('jadwal-perencanaan.store'), [
            'brand_id' => $brand->id,
            'nama_proyek' => 'Pembangunan Villa Ubud',
            'lokasi' => 'Gianyar, Bali',
            'tanggal_mulai' => '2026-10-01',
            'durasi_hari' => 30,
        ]);

        $jadwal = JadwalPerencanaan::first();

        $this->assertNotNull($jadwal);
        $this->assertSame('Pembangunan Villa Ubud', $jadwal->nama_proyek);
        $this->assertSame('Gianyar, Bali', $jadwal->lokasi);
        $this->assertSame(30, $jadwal->durasi_hari);
        $this->assertSame('2026-10-01', $jadwal->tanggal_mulai->format('Y-m-d'));
        $this->assertSame('2026-10-30', $jadwal->tanggal_selesai->format('Y-m-d'));
        $this->assertSame('draft', $jadwal->status);
        $response->assertRedirect(route('jadwal-perencanaan.show', $jadwal));
    }

    public function test_skenario_2_tambah_item_pekerjaan_dan_distribusi_bobot(): void
    {
        $brand = Brand::create(['name' => 'Basyid Studio', 'code' => 'BS']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->brands()->attach($brand->id);

        $this->actingAs($admin)->post(route('jadwal-perencanaan.store'), [
            'brand_id' => $brand->id,
            'nama_proyek' => 'Pembangunan Villa Ubud',
            'lokasi' => 'Gianyar, Bali',
            'tanggal_mulai' => '2026-10-01',
            'durasi_hari' => 30,
            'items' => [
                [
                    'nama_item' => 'Pekerjaan Pondasi & Struktur',
                    'bobot' => 25.00,
                    'hari_mulai' => 1,
                    'hari_selesai' => 10,
                ],
            ],
        ]);

        $item = JadwalItem::first();
        $this->assertNotNull($item);
        $this->assertSame('Pekerjaan Pondasi & Struktur', $item->nama_item);
        $this->assertSame('25.00', (string) $item->bobot);
        $this->assertSame(10, $item->durasi);

        // Daily distribution check (25.00 / 10 = 2.50 per day)
        $this->assertIsArray($item->distribusi);
        $this->assertEquals(2.5, $item->distribusi['1']);
        $this->assertEquals(2.5, $item->distribusi['10']);
    }

    public function test_skenario_3_validasi_total_bobot_kurva_s_tepat_100_persen(): void
    {
        $brand = Brand::create(['name' => 'Basyid Studio', 'code' => 'BS']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->brands()->attach($brand->id);

        $jadwal = JadwalPerencanaan::create([
            'brand_id' => $brand->id,
            'created_by' => $admin->id,
            'nama_proyek' => 'Renovasi Kantor',
            'durasi_hari' => 20,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-20',
            'status' => 'draft',
        ]);

        // Total 75% (40% + 35%)
        JadwalItem::create([
            'jadwal_perencanaan_id' => $jadwal->id,
            'urutan' => 1,
            'nama_item' => 'Item A',
            'bobot' => 40.00,
            'hari_mulai' => 1,
            'hari_selesai' => 10,
            'durasi' => 10,
        ]);
        JadwalItem::create([
            'jadwal_perencanaan_id' => $jadwal->id,
            'urutan' => 2,
            'nama_item' => 'Item B',
            'bobot' => 35.00,
            'hari_mulai' => 11,
            'hari_selesai' => 20,
            'durasi' => 10,
        ]);

        // Try publish with 75%
        $response = $this->actingAs($admin)->patch(route('jadwal-perencanaan.publish', $jadwal));
        $response->assertSessionHas('error', 'Total bobot item pekerjaan adalah 75.00%, harus tepat 100.00%.');

        $jadwal->refresh();
        $this->assertSame('draft', $jadwal->status);

        // Add 25% to make exactly 100%
        JadwalItem::create([
            'jadwal_perencanaan_id' => $jadwal->id,
            'urutan' => 3,
            'nama_item' => 'Item C',
            'bobot' => 25.00,
            'hari_mulai' => 15,
            'hari_selesai' => 20,
            'durasi' => 6,
        ]);

        $responseSuccess = $this->actingAs($admin)->patch(route('jadwal-perencanaan.publish', $jadwal));
        $responseSuccess->assertSessionHas('success', 'Jadwal berhasil dipublikasikan.');

        $jadwal->refresh();
        $this->assertSame('published', $jadwal->status);
    }

    public function test_skenario_4_penandaan_kolom_jumat_dan_kumulatif_kurva_s(): void
    {
        $brand = Brand::create(['name' => 'Basyid Studio', 'code' => 'BS']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->brands()->attach($brand->id);

        // 2026-10-01 is Thursday
        $jadwal = JadwalPerencanaan::create([
            'brand_id' => $brand->id,
            'created_by' => $admin->id,
            'nama_proyek' => 'Villa Canggu',
            'durasi_hari' => 10,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-10',
            'status' => 'draft',
        ]);

        JadwalItem::create([
            'jadwal_perencanaan_id' => $jadwal->id,
            'urutan' => 1,
            'nama_item' => 'Semua Pekerjaan',
            'bobot' => 100.00,
            'hari_mulai' => 1,
            'hari_selesai' => 10,
            'durasi' => 10,
            'distribusi' => array_fill_keys(range(1, 10), 10.0),
        ]);

        $calc = new JadwalCalculationService;
        $data = $calc->calculateKurvaS($jadwal);

        $this->assertArrayHasKey(2, $data['days']);
        $day2 = $data['days'][2];

        // 2026-10-02 is Friday
        $this->assertTrue($day2['is_jumat']);
        $this->assertTrue($day2['is_progress_cutoff']);
        $this->assertEquals(20.00, $day2['kumulatif_rencana']);
    }

    public function test_skenario_5_ekspor_pdf_gantt_chart_ber_kop_brand(): void
    {
        $brand = Brand::create(['name' => 'Basyid Studio', 'code' => 'BS']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->brands()->attach($brand->id);

        $jadwal = JadwalPerencanaan::create([
            'brand_id' => $brand->id,
            'created_by' => $admin->id,
            'nama_proyek' => 'Villa Sanur',
            'lokasi' => 'Sanur, Bali',
            'durasi_hari' => 10,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-10',
            'status' => 'published',
        ]);

        JadwalItem::create([
            'jadwal_perencanaan_id' => $jadwal->id,
            'urutan' => 1,
            'nama_item' => 'Pekerjaan Utama',
            'bobot' => 100.00,
            'hari_mulai' => 1,
            'hari_selesai' => 10,
            'durasi' => 10,
            'distribusi' => array_fill_keys(range(1, 10), 10.0),
        ]);

        $response = $this->actingAs($admin)->get(route('jadwal-perencanaan.pdf', $jadwal));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
