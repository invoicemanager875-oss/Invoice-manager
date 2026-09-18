@php
    $forPdf = $forPdf ?? true;
    $brand = $contract->brand;
    $src = fn ($path) => $forPdf
        ? 'file://'.str_replace('\\', '/', public_path('storage/'.$path))
        : \Illuminate\Support\Facades\Storage::url($path);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK - {{ $contract->nomor }}</title>
    <style>
        * { box-sizing: border-box; }
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .brand-name {
            font-size: 15pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .brand-meta {
            font-size: 8.5pt;
            color: #64748b;
            line-height: 1.3;
            margin-top: 3px;
        }
        .doc-title-box {
            text-align: center;
            margin-bottom: 18px;
        }
        .doc-title {
            font-size: 12.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: underline;
        }
        .doc-nomor {
            font-size: 9.5pt;
            color: #334155;
            font-family: 'Courier New', Courier, monospace;
            margin-top: 3px;
            font-weight: bold;
        }
        .lead-text {
            font-size: 9.5pt;
            text-align: justify;
            margin-bottom: 12px;
        }
        .party-box {
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }
        .party-box td {
            padding: 5px 8px;
            font-size: 9pt;
            vertical-align: top;
        }
        .party-title {
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            font-size: 9pt;
            background-color: #e2e8f0;
            padding: 4px 8px !important;
            border-bottom: 1px solid #cbd5e1;
        }
        .section-heading {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 8.5pt;
            font-weight: bold;
            color: #334155;
            text-align: left;
            text-transform: uppercase;
        }
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .narasi-box {
            font-size: 9pt;
            text-align: justify;
            line-height: 1.5;
            margin-top: 10px;
            margin-bottom: 16px;
            white-space: pre-wrap;
            font-family: inherit;
        }
        .rekening-box {
            border: 1px solid #94a3b8;
            background-color: #f8fafc;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 8.5pt;
        }
        .sign-table {
            width: 100%;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sign-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 10px;
        }
        .materai-box {
            width: 110px;
            height: 60px;
            border: 1px dashed #94a3b8;
            margin: 12px auto;
            color: #94a3b8;
            font-size: 7.5pt;
            line-height: 60px;
            text-align: center;
        }
        .sign-space {
            height: 72px;
        }
        .sign-name {
            font-weight: bold;
            font-size: 9.5pt;
            text-decoration: underline;
            color: #0f172a;
        }
        .sign-role {
            font-size: 8.5pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    {{-- KOP BRAND --}}
    <table class="header-table">
        <tr>
            @if ($brand && $brand->logo_path && file_exists(public_path('storage/'.$brand->logo_path)))
                <td style="width: 75px; padding-right: 12px;">
                    <img src="{{ $src($brand->logo_path) }}" style="max-height: 60px; max-width: 75px; object-fit: contain;">
                </td>
            @endif
            <td>
                <div class="brand-name">{{ $brand->name ?? $contract->pihak_pertama_perusahaan }}</div>
                <div class="brand-meta">
                    @if ($brand && $brand->address) {{ $brand->address }} <br> @else {{ $contract->pihak_pertama_alamat }} <br> @endif
                    Telepon: {{ $brand->phone ?? $contract->pihak_pertama_telepon }}
                    @if ($brand && $brand->email) | Email: {{ $brand->email }} @endif
                </div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <div style="font-size: 8pt; color: #64748b;">Dokumen Legalitas Kerja</div>
                <div style="font-size: 8.5pt; font-weight: bold; color: #0f172a;">{{ $contract->status === 'final' ? 'LEGAL & TERIKAT' : 'DRAF PERJANJIAN' }}</div>
            </td>
        </tr>
    </table>

    {{-- JUDUL DOKUMEN --}}
    <div class="doc-title-box">
        <div class="doc-title">{{ $contract->judul_kontrak }}</div>
        <div class="doc-nomor">Nomor: {{ $contract->nomor }}</div>
    </div>

    {{-- KOMPARISI PEMBUKAAN --}}
    <div class="lead-text">
        Pada hari ini, tanggal <strong>{{ $contract->tanggal_kontrak?->translatedFormat('d F Y') ?? date('d F Y') }}</strong>, bertempat di kantor {{ $contract->pihak_pertama_perusahaan }}, telah disepakati dan ditandatangani Perjanjian Kerja Pelaksanaan Proyek oleh dan antara pihak-pihak sebagai berikut:
    </div>

    {{-- TABEL IDENTITAS PARA PIHAK --}}
    <table class="party-box">
        <tr>
            <td colspan="3" class="party-title">I. PIHAK PERTAMA (PENYEDIA JASA)</td>
        </tr>
        <tr>
            <td style="width: 25%;">Nama Perusahaan</td>
            <td style="width: 2%;">:</td>
            <td style="width: 73%;"><strong>{{ $contract->pihak_pertama_perusahaan }}</strong></td>
        </tr>
        <tr>
            <td>Nama Penandatangan</td>
            <td>:</td>
            <td>{{ $contract->pihak_pertama_nama }} (Jabatan: {{ $contract->pihak_pertama_jabatan }})</td>
        </tr>
        <tr>
            <td>Alamat Domisili</td>
            <td>:</td>
            <td>{{ $contract->pihak_pertama_alamat }}</td>
        </tr>
        <tr>
            <td>Nomor Telepon</td>
            <td>:</td>
            <td>{{ $contract->pihak_pertama_telepon ?: '-' }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-style: italic; color: #475569; font-size: 8.5pt; padding-top: 0;">
                Dalam hal ini bertindak untuk dan atas nama {{ $contract->pihak_pertama_perusahaan }}, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.
            </td>
        </tr>
    </table>

    <table class="party-box">
        <tr>
            <td colspan="3" class="party-title">II. PIHAK KEDUA (PENGGUNA JASA / KLIEN)</td>
        </tr>
        <tr>
            <td style="width: 25%;">Nama Lengkap</td>
            <td style="width: 2%;">:</td>
            <td style="width: 73%;"><strong>{{ $contract->pihak_kedua_nama }}</strong> @if($contract->pihak_kedua_perusahaan) ({{ $contract->pihak_kedua_perusahaan }}) @endif</td>
        </tr>
        <tr>
            <td>No. KTP / NIK / NPWP</td>
            <td>:</td>
            <td>{{ $contract->pihak_kedua_identitas ?: '-' }}</td>
        </tr>
        <tr>
            <td>Alamat Klien</td>
            <td>:</td>
            <td>{{ $contract->pihak_kedua_alamat }}</td>
        </tr>
        <tr>
            <td>Kontak / Telepon</td>
            <td>:</td>
            <td>{{ $contract->pihak_kedua_telepon }} @if($contract->pihak_kedua_email) | Email: {{ $contract->pihak_kedua_email }} @endif</td>
        </tr>
        <tr>
            <td colspan="3" style="font-style: italic; color: #475569; font-size: 8.5pt; padding-top: 0;">
                Dalam hal ini bertindak sebagai Pemilik Proyek / Pengguna Jasa, selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong>.
            </td>
        </tr>
    </table>

    <div class="lead-text">
        PIHAK PERTAMA dan PIHAK KEDUA secara bersama-sama disebut sebagai <strong>PARA PIHAK</strong>. PARA PIHAK sepakat untuk saling mengikatkan diri dalam Perjanjian Kerja ini dengan ketentuan dan syarat-syarat sebagaimana tercantum dalam pasal-pasal berikut:
    </div>

    {{-- TABEL RINCIAN SCOPES --}}
    @if ($contract->scopes->isNotEmpty())
        <div class="section-heading">Lampiran Pasal 1: Rincian Paket Lingkup Pekerjaan</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No</th>
                    <th style="width: 35%;">Nama Paket Pekerjaan</th>
                    <th style="width: 40%;">Detail & Rincian Deliverable</th>
                    <th style="width: 20%; text-align: right;">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contract->scopes as $idx => $scope)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $scope->nama_paket }}</strong></td>
                        <td>{{ $scope->deskripsi ?: '-' }}</td>
                        <td class="text-right">Rp {{ number_format($scope->nominal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #f8fafc; font-weight: bold;">
                    <td colspan="3" class="text-right">TOTAL NILAI KONTRAK:</td>
                    <td class="text-right">Rp {{ number_format($contract->nilai_kontrak, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        <div style="font-size: 8.5pt; font-style: italic; color: #475569; margin-bottom: 10px;">
            Terbilang: "{{ $contract->nilai_terbilang }}"
        </div>
    @endif

    {{-- TABEL RINCIAN TERMIN --}}
    @if ($contract->terms->isNotEmpty())
        <div class="section-heading">Lampiran Pasal 3: Jadwal & Skema Termin Pembayaran</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No</th>
                    <th style="width: 25%;">Nama Termin</th>
                    <th style="width: 12%; text-align: center;">Bobot (%)</th>
                    <th style="width: 20%; text-align: right;">Nominal (Rp)</th>
                    <th style="width: 38%;">Syarat / Milestone Pencairan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contract->terms as $idx => $term)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $term->judul }}</strong></td>
                        <td class="text-center">{{ $term->persentase }}%</td>
                        <td class="text-right">Rp {{ number_format($term->nominal, 0, ',', '.') }}</td>
                        <td>{{ $term->syarat_pencairan ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- REKENING PEMBAYARAN RESMI --}}
    @php
        $rekeningList = $brand->rekening_config ?? [];
    @endphp
    @if (! empty($rekeningList))
        <div class="rekening-box">
            <strong>Rekening Resmi Pembayaran PIHAK PERTAMA:</strong><br>
            @foreach ($rekeningList as $rek)
                - Bank {{ $rek['bank'] ?? '-' }} | No. Rek: <strong>{{ $rek['nomor'] ?? '-' }}</strong> | A/N: {{ $rek['atas_nama'] ?? '-' }}<br>
            @endforeach
        </div>
    @endif

    {{-- BATANG TUBUH KLAUSUL LENGKAP --}}
    @if ($contract->narasi)
        <div class="section-heading">Batang Tubuh Klausul & Ketentuan Perjanjian</div>
        <div class="narasi-box">{{ $contract->narasi }}</div>
    @endif

    {{-- BLOK TANDA TANGAN BILATERAL --}}
    <table class="sign-table">
        <tr>
            <td>
                <div style="font-weight: bold; text-transform: uppercase; font-size: 8.5pt;">PIHAK PERTAMA</div>
                <div style="font-size: 8.5pt; color: #475569;">{{ $contract->pihak_pertama_perusahaan }}</div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ $contract->pihak_pertama_nama }}</div>
                <div class="sign-role">{{ $contract->pihak_pertama_jabatan }}</div>
            </td>
            <td>
                <div style="font-weight: bold; text-transform: uppercase; font-size: 8.5pt;">PIHAK KEDUA</div>
                <div style="font-size: 8.5pt; color: #475569;">{{ $contract->pihak_kedua_perusahaan ?: 'Pengguna Jasa' }}</div>
                <div class="materai-box">MATERAI 10.000</div>
                <div class="sign-name">{{ $contract->pihak_kedua_nama }}</div>
                <div class="sign-role">Klien / Pemilik Proyek</div>
            </td>
        </tr>
    </table>

</body>
</html>
