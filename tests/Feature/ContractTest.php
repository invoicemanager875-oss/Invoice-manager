<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Contract;
use App\Models\ContractCounter;
use App\Models\Invoice;
use App\Models\User;
use App\Services\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'drafter', 'guard_name' => 'web']);
    }

    public function test_skenario_1_penomoran_spk_otomatis_dan_berurutan(): void
    {
        $brandA = Brand::create([
            'name' => 'Studio Arsitek A',
            'code' => 'SAA',
            'ttd_nama' => 'Budi Santoso',
            'ttd_jabatan' => 'Principal Architect',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->brands()->attach($brandA->id);

        $year = (int) date('Y');

        // Check counter is empty or 0 initially
        $initialCounter = ContractCounter::where('brand_id', $brandA->id)->where('tahun', $year)->first();
        $this->assertNull($initialCounter);

        // Create 1st SPK for Brand A
        $response1 = $this->actingAs($admin)->post(route('contracts.store'), [
            'brand_id' => $brandA->id,
            'judul_kontrak' => 'Surat Perjanjian Kerja Desain Villa',
            'tanggal_kontrak' => date('Y-m-d'),
            'pihak_pertama_perusahaan' => $brandA->name,
            'pihak_pertama_nama' => 'Budi Santoso',
            'pihak_pertama_jabatan' => 'Principal Architect',
            'pihak_pertama_alamat' => 'Jl. Merdeka No. 10 Jakarta',
            'pihak_pertama_telepon' => '08123456789',
            'pihak_kedua_nama' => 'Pak Andi',
            'pihak_kedua_alamat' => 'Jl. Sudirman No. 5 Jakarta',
            'pihak_kedua_telepon' => '08987654321',
            'nilai_kontrak' => 50000000,
            'durasi_hari' => 45,
            'tanggal_mulai' => date('Y-m-d'),
            'tanggal_selesai' => date('Y-m-d', strtotime('+44 days')),
            'narasi' => 'Draf narasi awal.',
            'scopes' => [
                ['nama_paket' => 'Gambar Kerja', 'deskripsi' => 'RAB, Denah', 'nominal' => 50000000],
            ],
            'terms' => [
                ['judul' => 'Termin 1', 'persentase' => 100, 'nominal' => 50000000, 'syarat_pencairan' => 'Kontrak'],
            ],
        ]);

        $contract1 = Contract::first();
        $this->assertNotNull($contract1);
        $expectedNomor1 = sprintf('SPK/%d/001', $year);
        $this->assertSame($expectedNomor1, $contract1->nomor);
        $response1->assertRedirect(route('contracts.show', $contract1));

        $counter1 = ContractCounter::where('brand_id', $brandA->id)->where('tahun', $year)->first();
        $this->assertNotNull($counter1);
        $this->assertSame(1, $counter1->last_number);

        // Create 2nd SPK for Brand A
        $this->actingAs($admin)->post(route('contracts.store'), [
            'brand_id' => $brandA->id,
            'judul_kontrak' => 'Surat Perjanjian Kerja Interior Kafe',
            'tanggal_kontrak' => date('Y-m-d'),
            'pihak_pertama_perusahaan' => $brandA->name,
            'pihak_pertama_nama' => 'Budi Santoso',
            'pihak_pertama_jabatan' => 'Principal Architect',
            'pihak_pertama_alamat' => 'Jl. Merdeka No. 10 Jakarta',
            'pihak_pertama_telepon' => '08123456789',
            'pihak_kedua_nama' => 'Ibu Citra',
            'pihak_kedua_alamat' => 'Jl. Gatot Subroto No. 2',
            'pihak_kedua_telepon' => '08111222333',
            'nilai_kontrak' => 30000000,
            'durasi_hari' => 30,
            'tanggal_mulai' => date('Y-m-d'),
            'tanggal_selesai' => date('Y-m-d', strtotime('+29 days')),
            'scopes' => [
                ['nama_paket' => 'Interior Kafe', 'nominal' => 30000000],
            ],
            'terms' => [
                ['judul' => 'Termin 1', 'persentase' => 100, 'nominal' => 30000000],
            ],
        ]);

        $contract2 = Contract::where('nomor', sprintf('SPK/%d/002', $year))->first();
        $this->assertNotNull($contract2);

        $counter2 = ContractCounter::where('brand_id', $brandA->id)->where('tahun', $year)->first();
        $this->assertSame(2, $counter2->last_number);
    }

    public function test_skenario_2_auto_populate_dari_invoice(): void
    {
        $brand = Brand::create(['name' => 'Studio Kreatif', 'code' => 'SK']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->brands()->attach($brand->id);

        $invoice = Invoice::create([
            'brand_id' => $brand->id,
            'created_by' => $admin->id,
            'nomor' => 'INV/2026/0010',
            'nomor_urut' => 10,
            'tahun' => 2026,
            'bulan' => 10,
            'klien' => 'PT Sejahtera',
            'alamat' => 'Kawasan Industri Cikarang Blok A',
            'phone' => '0218900000',
            'email' => 'finance@sejahtera.com',
            'status' => 'menunggu',
            'tanggal' => date('Y-m-d'),
            'jatuh_tempo' => date('Y-m-d', strtotime('+14 days')),
            'kop_config' => ['nama' => 'Studio Kreatif'],
        ]);

        $invoice->items()->create([
            'deskripsi' => 'Paket Perencanaan Gedung Kantor',
            'volume' => 1,
            'satuan' => 'Paket',
            'harga_satuan' => 50000000,
            'jumlah' => 50000000,
        ]);

        $invoice->terms()->createMany([
            ['label' => 'Termin I (DP)', 'persen' => 50, 'nominal' => 25000000, 'catatan' => 'DP Awal'],
            ['label' => 'Termin II (Pelunasan)', 'persen' => 50, 'nominal' => 25000000, 'catatan' => 'Serah Terima'],
        ]);

        // Recalculate invoice total
        $invoice->subtotal = 50000000;
        $invoice->total = 50000000;
        $invoice->save();

        $contractService = app(ContractService::class);
        $populated = $contractService->autoPopulateFromInvoice($invoice->fresh(['items', 'terms', 'brand']));

        $this->assertSame('PT Sejahtera', $populated['pihak_kedua_nama']);
        $this->assertSame(50000000.0, (float) $populated['nilai_kontrak']);
        $this->assertSame('Kawasan Industri Cikarang Blok A', $populated['pihak_kedua_alamat']);
        $this->assertCount(1, $populated['scopes']);
        $this->assertSame('Paket Perencanaan Gedung Kantor', $populated['scopes'][0]['nama_paket']);
        $this->assertCount(2, $populated['terms']);
        $this->assertSame('Termin I (DP)', $populated['terms'][0]['judul']);
        $this->assertSame(25000000.0, (float) $populated['terms'][0]['nominal']);

        // Check via HTTP create page with ?invoice_id=
        $response = $this->actingAs($admin)->get(route('contracts.create', ['invoice_id' => $invoice->id]));
        $response->assertOk();
        $response->assertSee('PT Sejahtera');
        $response->assertSee('50000000');
    }

    public function test_skenario_3_auto_generate_narasi_pasal(): void
    {
        $brand = Brand::create([
            'name' => 'Studio Mega Arsitektur',
            'code' => 'SMA',
            'address' => 'Gedung Wisma Lt. 3',
            'phone' => '021778899',
            'rekening_config' => [
                ['bank' => 'BCA', 'nomor' => '7788990011', 'atas_nama' => 'PT Mega Arsitektur'],
            ],
        ]);

        $contractService = app(ContractService::class);
        $narasi = $contractService->generateAutoNarasi([
            'brand_name' => $brand->name,
            'pihak_pertama_perusahaan' => $brand->name,
            'pihak_pertama_alamat' => $brand->address,
            'pihak_pertama_telepon' => $brand->phone,
            'pihak_kedua_nama' => 'Bapak Surya Handoko',
            'pihak_kedua_alamat' => 'Jl. Mawar Indah No. 12',
            'pihak_kedua_telepon' => '081234567890',
            'nilai_kontrak' => 25000000,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-30',
            'scopes' => [
                ['nama_paket' => 'Desain Interior 3D', 'deskripsi' => 'Living room & kitchen', 'nominal' => 25000000],
            ],
            'terms' => [
                ['judul' => 'Termin I (DP 50%)', 'persentase' => 50, 'nominal' => 12500000, 'syarat_pencairan' => 'Saat penandatanganan'],
                ['judul' => 'Termin II (Pelunasan 50%)', 'persentase' => 50, 'nominal' => 12500000, 'syarat_pencairan' => 'Setelah render final'],
            ],
            'rekening_config' => $brand->rekening_config,
        ]);

        // Verifikasi terbilang 25 juta rupiah
        $this->assertStringContainsString('Dua Puluh Lima Juta Rupiah', $narasi);

        // Verifikasi rekening resmi brand tercantum
        $this->assertStringContainsString('BCA', $narasi);
        $this->assertStringContainsString('7788990011', $narasi);
        $this->assertStringContainsString('PT Mega Arsitektur', $narasi);

        // Verifikasi klausul pasal-pasal standar SPK
        $this->assertStringContainsString('PASAL 1', $narasi);
        $this->assertStringContainsString('LINGKUP PEKERJAAN', $narasi);
        $this->assertStringContainsString('PASAL 2', $narasi);
        $this->assertStringContainsString('PASAL 3', $narasi);
        $this->assertStringContainsString('NILAI KONTRAK', $narasi);
        $this->assertStringContainsString('PASAL 4', $narasi);
        $this->assertStringContainsString('HAK DAN KEWAJIBAN', $narasi);
        $this->assertStringContainsString('PASAL 5', $narasi);
        $this->assertStringContainsString('PASAL 6', $narasi);
    }

    public function test_skenario_4_finalisasi_mengunci_edit_dan_hapus(): void
    {
        $brand = Brand::create(['name' => 'Studio Mandiri', 'code' => 'SM']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->brands()->attach($brand->id);

        $contract = Contract::create([
            'brand_id' => $brand->id,
            'created_by' => $admin->id,
            'nomor' => 'SPK/2026/001',
            'nomor_urut' => 1,
            'tahun' => 2026,
            'judul_kontrak' => 'Kontrak Desain Rumah',
            'tanggal_kontrak' => '2026-10-01',
            'pihak_pertama_perusahaan' => 'Studio Mandiri',
            'pihak_pertama_nama' => 'Arsitek Mandiri',
            'pihak_pertama_jabatan' => 'Direktur',
            'pihak_pertama_alamat' => 'Jl. Mandiri No. 1',
            'pihak_kedua_nama' => 'Pak Joko',
            'pihak_kedua_alamat' => 'Jl. Joko No. 2',
            'pihak_kedua_telepon' => '081299887766',
            'nilai_kontrak' => 10000000,
            'nilai_terbilang' => 'Sepuluh Juta Rupiah',
            'durasi_hari' => 14,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-14',
            'status' => 'draft',
        ]);

        // Finalize the contract
        $responseFinalize = $this->actingAs($admin)->patch(route('contracts.finalize', $contract));
        $responseFinalize->assertRedirect(route('contracts.show', $contract));

        $contract->refresh();
        $this->assertSame('final', $contract->status);

        // Attempt to update final contract should result in 403 Forbidden
        $responseUpdate = $this->actingAs($admin)->put(route('contracts.update', $contract), [
            'brand_id' => $brand->id,
            'judul_kontrak' => 'Modifikasi Judul Yang Terlarang',
            'tanggal_kontrak' => '2026-10-01',
            'pihak_pertama_perusahaan' => 'Studio Mandiri',
            'pihak_pertama_nama' => 'Arsitek Mandiri',
            'pihak_pertama_jabatan' => 'Direktur',
            'pihak_pertama_alamat' => 'Jl. Mandiri No. 1',
            'pihak_kedua_nama' => 'Pak Joko Modif',
            'pihak_kedua_alamat' => 'Jl. Joko No. 2',
            'pihak_kedua_telepon' => '081299887766',
            'nilai_kontrak' => 15000000,
            'durasi_hari' => 14,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-14',
            'scopes' => [
                ['nama_paket' => 'Modif', 'nominal' => 15000000],
            ],
            'terms' => [
                ['judul' => 'Termin 1', 'persentase' => 100, 'nominal' => 15000000],
            ],
        ]);
        $responseUpdate->assertForbidden();

        // Attempt to delete final contract should result in 403 Forbidden
        $responseDelete = $this->actingAs($admin)->delete(route('contracts.destroy', $contract));
        $responseDelete->assertForbidden();

        // Ensure database record remains untouched
        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'nomor' => 'SPK/2026/001',
            'judul_kontrak' => 'Kontrak Desain Rumah',
            'status' => 'final',
        ]);
    }

    public function test_skenario_5_export_pdf_berkop_brand(): void
    {
        $brand = Brand::create([
            'name' => 'PT Graha Estetika',
            'code' => 'GE',
            'address' => 'Jl. Senopati No. 45 Jakarta Selatan',
            'phone' => '021556677',
            'email' => 'legal@grahaestetika.com',
            'rekening_config' => [
                ['bank' => 'Bank Mandiri', 'nomor' => '137000998877', 'atas_nama' => 'PT Graha Estetika'],
            ],
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->brands()->attach($brand->id);

        $contract = Contract::create([
            'brand_id' => $brand->id,
            'created_by' => $admin->id,
            'nomor' => 'SPK/2026/005',
            'nomor_urut' => 5,
            'tahun' => 2026,
            'judul_kontrak' => 'Perjanjian Kerja Pengawasan Berkala',
            'tanggal_kontrak' => '2026-10-01',
            'pihak_pertama_perusahaan' => $brand->name,
            'pihak_pertama_nama' => 'Ir. Hendra Wijaya',
            'pihak_pertama_jabatan' => 'Direktur Utama',
            'pihak_pertama_alamat' => $brand->address,
            'pihak_pertama_telepon' => $brand->phone,
            'pihak_kedua_nama' => 'PT Investama Properti',
            'pihak_kedua_alamat' => 'Jl. Sudirman Plaza Kav. 20',
            'pihak_kedua_telepon' => '02199887766',
            'nilai_kontrak' => 75000000,
            'nilai_terbilang' => 'Tujuh Puluh Lima Juta Rupiah',
            'durasi_hari' => 60,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-29',
            'status' => 'final',
            'narasi' => "SURAT PERJANJIAN KERJA (SPK)\nNomor: SPK/2026/005\n\nPASAL 1 — LINGKUP PEKERJAAN\nPengawasan berkala struktur gedung.",
        ]);

        $contract->scopes()->create([
            'nama_paket' => 'Pengawasan Struktur',
            'deskripsi' => 'Kunjungan berkala 2x seminggu',
            'nominal' => 75000000,
        ]);

        $contract->terms()->create([
            'judul' => 'Termin 1 (Pelunasan)',
            'persentase' => 100,
            'nominal' => 75000000,
            'syarat_pencairan' => 'Setelah laporan akhir disetujui',
        ]);

        $response = $this->actingAs($admin)->get(route('contracts.pdf', $contract));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));

        // Validate that response body starts with PDF binary header %PDF-
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_skenario_authorization_drafter_cannot_finalize_or_delete(): void
    {
        $brand = Brand::create(['name' => 'Studio Drafter Test', 'code' => 'SDT']);
        $drafter = User::factory()->create();
        $drafter->assignRole('drafter');
        $drafter->brands()->attach($brand->id);

        $contract = Contract::create([
            'brand_id' => $brand->id,
            'created_by' => $drafter->id,
            'nomor' => 'SPK/2026/009',
            'nomor_urut' => 9,
            'tahun' => 2026,
            'judul_kontrak' => 'Drafter Project',
            'tanggal_kontrak' => '2026-10-01',
            'pihak_pertama_perusahaan' => $brand->name,
            'pihak_pertama_nama' => 'Pimpinan',
            'pihak_pertama_jabatan' => 'Lead',
            'pihak_pertama_alamat' => 'Alamat',
            'pihak_kedua_nama' => 'Klien',
            'pihak_kedua_alamat' => 'Alamat Klien',
            'pihak_kedua_telepon' => '0812345',
            'nilai_kontrak' => 10000000,
            'nilai_terbilang' => 'Sepuluh Juta Rupiah',
            'durasi_hari' => 14,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-14',
            'status' => 'draft',
        ]);

        // Drafter cannot finalize
        $resFinalize = $this->actingAs($drafter)->patch(route('contracts.finalize', $contract));
        $resFinalize->assertForbidden();

        // Drafter cannot delete
        $resDelete = $this->actingAs($drafter)->delete(route('contracts.destroy', $contract));
        $resDelete->assertForbidden();
    }

    public function test_brand_isolation_cannot_view_or_update_other_brand_contracts(): void
    {
        $brandA = Brand::create(['name' => 'Brand Alpha', 'code' => 'BA']);
        $brandB = Brand::create(['name' => 'Brand Beta', 'code' => 'BB']);

        $userA = User::factory()->create();
        $userA->assignRole('admin');
        $userA->brands()->attach($brandA->id);

        $userB = User::factory()->create();
        $userB->assignRole('admin');
        $userB->brands()->attach($brandB->id);

        $contractA = Contract::create([
            'brand_id' => $brandA->id,
            'created_by' => $userA->id,
            'nomor' => 'SPK/2026/011',
            'nomor_urut' => 11,
            'tahun' => 2026,
            'judul_kontrak' => 'Alpha Contract',
            'tanggal_kontrak' => '2026-10-01',
            'pihak_pertama_perusahaan' => $brandA->name,
            'pihak_pertama_nama' => 'Pimpinan A',
            'pihak_pertama_jabatan' => 'Lead',
            'pihak_pertama_alamat' => 'Alamat A',
            'pihak_kedua_nama' => 'Klien A',
            'pihak_kedua_alamat' => 'Alamat Klien A',
            'pihak_kedua_telepon' => '0812345',
            'nilai_kontrak' => 20000000,
            'nilai_terbilang' => 'Dua Puluh Juta Rupiah',
            'durasi_hari' => 14,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-14',
            'status' => 'draft',
        ]);

        // User B cannot view Alpha contract
        $resShow = $this->actingAs($userB)->get(route('contracts.show', $contractA));
        $resShow->assertForbidden();

        // User B cannot edit Alpha contract
        $resEdit = $this->actingAs($userB)->get(route('contracts.edit', $contractA));
        $resEdit->assertForbidden();
    }

    public function test_auto_narasi_ajax_endpoint(): void
    {
        $brand = Brand::create(['name' => 'Studio Ajax', 'code' => 'SA']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->brands()->attach($brand->id);

        $response = $this->actingAs($admin)->postJson(route('contracts.autoNarasi'), [
            'brand_name' => 'Studio Ajax',
            'pihak_pertama_perusahaan' => 'Studio Ajax',
            'pihak_kedua_nama' => 'Bapak Klien Ajax',
            'nilai_kontrak' => 15000000,
            'scopes' => [
                ['nama_paket' => 'Visualisasi 3D', 'deskripsi' => 'Exterior render', 'nominal' => 15000000],
            ],
            'terms' => [
                ['judul' => 'Termin 1', 'persentase' => 100, 'nominal' => 15000000, 'syarat_pencairan' => 'Serah terima'],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['narasi']);
        $this->assertStringContainsString('Lima Belas Juta Rupiah', $response->json('narasi'));
    }
}
