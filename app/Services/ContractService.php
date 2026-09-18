<?php

namespace App\Services;

use App\Models\Invoice;
use App\Support\Terbilang;
use Carbon\Carbon;

class ContractService
{
    public function autoPopulateFromInvoice(Invoice $invoice): array
    {
        $invoice->load(['brand', 'items', 'terms']);
        $brand = $invoice->brand;

        $nilaiKontrak = (float) $invoice->total;
        $nilaiTerbilang = Terbilang::make($nilaiKontrak);

        $scopes = $invoice->items->map(function ($item, $index) {
            return [
                'urutan' => $index + 1,
                'nama_paket' => $item->deskripsi ?? 'Item Pekerjaan '.($index + 1),
                'deskripsi' => $item->deskripsi ?? null,
                'nominal' => (float) ($item->jumlah ?? $item->total ?? 0),
            ];
        })->values()->toArray();

        $terms = $invoice->terms->map(function ($term, $index) {
            return [
                'termin_ke' => $index + 1,
                'judul' => $term->label ?? $term->judul ?? 'Termin '.($index + 1),
                'persentase' => (float) ($term->persen ?? $term->persentase ?? 0),
                'nominal' => (float) $term->nominal,
                'syarat_pencairan' => $term->syarat_pencairan ?? $term->catatan ?? 'Sesuai kesepakatan',
            ];
        })->values()->toArray();

        $tanggalKontrak = now()->toDateString();
        $tanggalMulai = now()->toDateString();
        $durasiHari = 30;
        $tanggalSelesai = now()->addDays($durasiHari - 1)->toDateString();

        $klienNama = $invoice->klien ?? $invoice->klien_nama ?? '';
        $klienAlamat = $invoice->alamat ?? $invoice->klien_alamat ?? '';
        $klienTelepon = $invoice->phone ?? $invoice->klien_telepon ?? '';
        $klienEmail = $invoice->email ?? $invoice->klien_email ?? '';

        $dataForNarasi = [
            'tanggal_kontrak' => $tanggalKontrak,
            'pihak_pertama_perusahaan' => $brand->name ?? '',
            'pihak_pertama_alamat' => $brand->address ?? '',
            'pihak_pertama_telepon' => $brand->phone ?? '',
            'pihak_kedua_nama' => $klienNama,
            'pihak_kedua_alamat' => $klienAlamat,
            'pihak_kedua_telepon' => $klienTelepon,
            'scopes' => $scopes,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'nilai_kontrak' => $nilaiKontrak,
            'nilai_terbilang' => $nilaiTerbilang,
            'terms' => $terms,
            'rekening_config' => $brand->rekening_config ?? [],
            'catatan' => $invoice->catatan ?? '',
        ];

        $narasi = $this->generateAutoNarasi($dataForNarasi);

        return [
            'brand_id' => $brand->id,
            'invoice_id' => $invoice->id,
            'judul_kontrak' => 'Surat Perjanjian Kerja',
            'tanggal_kontrak' => $tanggalKontrak,
            'pihak_pertama_nama' => $brand->ttd_nama ?? 'Pimpinan Brand',
            'pihak_pertama_jabatan' => $brand->ttd_jabatan ?? 'Direktur / Pimpinan',
            'pihak_pertama_perusahaan' => $brand->name ?? '',
            'pihak_pertama_alamat' => $brand->address ?? '',
            'pihak_pertama_telepon' => $brand->phone ?? '',
            'pihak_kedua_nama' => $klienNama,
            'pihak_kedua_identitas' => '',
            'pihak_kedua_perusahaan' => '',
            'pihak_kedua_alamat' => $klienAlamat,
            'pihak_kedua_telepon' => $klienTelepon,
            'pihak_kedua_email' => $klienEmail,
            'nilai_kontrak' => $nilaiKontrak,
            'nilai_terbilang' => $nilaiTerbilang,
            'durasi_hari' => $durasiHari,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'narasi' => $narasi,
            'rekening_config' => $brand->rekening_config ?? [],
            'scopes' => $scopes,
            'terms' => $terms,
        ];
    }

    public function generateAutoNarasi(array $data): string
    {
        $tglKontrakStr = isset($data['tanggal_kontrak']) ? Carbon::parse($data['tanggal_kontrak'])->translatedFormat('l, d F Y') : date('d F Y');
        $tglMulaiStr = isset($data['tanggal_mulai']) ? Carbon::parse($data['tanggal_mulai'])->translatedFormat('d F Y') : '-';
        $tglSelesaiStr = isset($data['tanggal_selesai']) ? Carbon::parse($data['tanggal_selesai'])->translatedFormat('d F Y') : '-';

        $p1Perusahaan = $data['pihak_pertama_perusahaan'] ?? ($data['brand_name'] ?? 'Penyedia Jasa');
        $p1Alamat = $data['pihak_pertama_alamat'] ?? '-';
        $p1Telepon = $data['pihak_pertama_telepon'] ?? '-';

        $p2Nama = $data['pihak_kedua_nama'] ?? ($data['nama_klien'] ?? 'Pengguna Jasa');
        $p2Alamat = $data['pihak_kedua_alamat'] ?? ($data['alamat_klien'] ?? '-');
        $p2Telepon = $data['pihak_kedua_telepon'] ?? ($data['telp_klien'] ?? '-');

        $nilaiRp = number_format((float) ($data['nilai_kontrak'] ?? 0), 0, ',', '.');
        $nilaiTerbilang = $data['nilai_terbilang'] ?? Terbilang::make((float) ($data['nilai_kontrak'] ?? 0));

        // Format Scopes
        $scopesText = '';
        if (! empty($data['scopes']) && is_array($data['scopes'])) {
            foreach ($data['scopes'] as $idx => $scope) {
                $num = $idx + 1;
                $nama = $scope['nama_paket'] ?? $scope['nama_item'] ?? 'Pekerjaan';
                $scopesText .= "{$num}. {$nama}\n";
                if (! empty($scope['deskripsi'])) {
                    $scopesText .= "   - {$scope['deskripsi']}\n";
                }
            }
        } else {
            $scopesText = "1. Pekerjaan sesuai rincian penawaran yang disepakati.\n";
        }
        $scopesText = rtrim($scopesText);

        // Format Terms
        $termsText = '';
        if (! empty($data['terms']) && is_array($data['terms'])) {
            foreach ($data['terms'] as $idx => $term) {
                $num = $idx + 1;
                $judul = $term['judul'] ?? "Termin {$num}";
                $pct = number_format((float) ($term['persentase'] ?? 0), 0);
                $nom = number_format((float) ($term['nominal'] ?? 0), 0, ',', '.');
                $syarat = ! empty($term['syarat_pencairan']) ? " (syarat: {$term['syarat_pencairan']})" : '';
                $termsText .= "{$num}. {$judul}: {$pct}% = Rp {$nom}{$syarat}\n";
            }
        } else {
            $termsText = "1. Pembayaran penuh: Rp {$nilaiRp}\n";
        }
        $termsText = rtrim($termsText);

        // Format Rekening Bank
        $bankText = '';
        if (! empty($data['rekening_config']) && is_array($data['rekening_config'])) {
            foreach ($data['rekening_config'] as $idx => $rek) {
                $num = $idx + 1;
                $bank = $rek['bank'] ?? 'Bank';
                $noRek = $rek['nomor'] ?? ($rek['no_rekening'] ?? '-');
                $atasNama = $rek['atas_nama'] ?? ($rek['nama'] ?? $p1Perusahaan);
                $bankText .= "{$num}. {$bank} — {$noRek} a.n. {$atasNama}\n";
            }
        } else {
            $bankText = "1. Rekening resmi yang ditunjuk oleh Pihak Pertama.\n";
        }
        $bankText = rtrim($bankText);

        $catatanKhusus = ! empty($data['catatan']) ? "\n\nKETENTUAN KHUSUS\n{$data['catatan']}" : '';

        return <<<TEXT
SURAT PERJANJIAN KERJA

Pada hari ini, {$tglKontrakStr}, kami yang bertanda tangan di bawah ini:

PIHAK PERTAMA
Nama/Perusahaan : {$p1Perusahaan}
Alamat          : {$p1Alamat}
Telepon         : {$p1Telepon}

PIHAK KEDUA
Nama/Perusahaan : {$p2Nama}
Alamat          : {$p2Alamat}
Telepon         : {$p2Telepon}

Kedua belah pihak telah sepakat mengadakan Perjanjian Kerja dengan ketentuan:

PASAL 1 — LINGKUP PEKERJAAN
Pihak Pertama sepakat melaksanakan pekerjaan:
{$scopesText}

PASAL 2 — WAKTU PELAKSANAAN
Pekerjaan dimulai {$tglMulaiStr} dan diselesaikan paling lambat {$tglSelesaiStr}.

PASAL 3 — NILAI KONTRAK DAN PEMBAYARAN
Nilai kontrak sebesar Rp {$nilaiRp} ({$nilaiTerbilang}).
Pembayaran dilakukan dengan termin sebagai berikut:
{$termsText}

PASAL 4 — HAK DAN KEWAJIBAN
1. Pihak Pertama berkewajiban menyelesaikan pekerjaan sesuai spesifikasi dan tepat waktu.
2. Pihak Kedua berkewajiban melakukan pembayaran sesuai jadwal yang disepakati.
3. Pihak Pertama berhak menerima pembayaran sesuai nilai kontrak.
4. Pihak Kedua berhak mendapatkan hasil pekerjaan sesuai lingkup yang disepakati.

PASAL 5 — REKENING PEMBAYARAN
Seluruh pembayaran oleh Pihak Kedua wajib ditransfer ke rekening berikut atas nama Pihak Pertama:
{$bankText}

PASAL 6 — PENYELESAIAN PERSELISIHAN
Perselisihan diselesaikan secara musyawarah mufakat. Apabila tidak tercapai, diselesaikan melalui jalur hukum yang berlaku.{$catatanKhusus}

Demikian Surat Perjanjian Kerja ini dibuat pada tanggal {$tglKontrakStr} dalam keadaan sadar dan tanpa paksaan.
TEXT;
    }
}
