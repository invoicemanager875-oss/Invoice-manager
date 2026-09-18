<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->nomor }}</title>
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #fff;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        table { border-collapse: collapse; width: 100%; }
        img { max-width: 100%; }
        .page { max-width: 760px; margin: 0 auto; padding: 24px; }
        .no-print { display: block; }
        .kop-wrap { max-width: 760px; margin: 0 auto; }
        .rekening-sign-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px; page-break-inside: avoid; break-inside: avoid; }
        .sign-section { page-break-inside: avoid; break-inside: avoid; margin-top: 24px; }
        .totals-block { page-break-inside: avoid; break-inside: avoid; }
        /* Termin, catatan, dan rekening/TTD digabung jadi satu grup: kalau sisa
           halaman saat ini tidak cukup untuk semuanya, seluruh grup pindah bersama
           ke halaman baru, bukan terpisah-pisah antar section. */
        .tail-section { page-break-inside: avoid; break-inside: avoid; }
        thead { display: table-header-group; }
        /* Baris item dibiarkan pecah ke halaman berikutnya kalau memang tidak muat
           (deskripsi paket yang panjang), supaya sisa halaman terpakai penuh dulu
           dan tidak menyisakan area kosong besar sebelum pindah halaman. */
        th { background: #f7fafc; padding: 8px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #4a5568; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        td { padding: 8px 12px; border-bottom: 1px solid #f0f4f8; font-size: 13px; }
        @page { size: A4; margin: 12mm; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:right;padding:16px 24px;background:#f7fafc;border-bottom:1px solid #e2e8f0">
        <button onclick="window.print()" style="background:#1a365d;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:700;cursor:pointer">
            Cetak
        </button>
    </div>

    @include('invoices._partials.sph')

    <div class="page">
        @include('invoices._partials.items-table', ['repeatHeader' => true])

        <div class="totals-block" style="margin-top:16px">
            @include('invoices._partials.totals')
        </div>

        <div class="tail-section">
            @if ($invoice->terms->isNotEmpty())
                <div style="margin-top:24px">
                    @include('invoices._partials.termin')
                </div>
            @endif

            @if ($invoice->catatan)
                <div style="margin-top:24px;padding:12px;background:#f7fafc;border-left:3px solid {{ $invoice->theme['acc'] }};border-radius:0 8px 8px 0">
                    <div style="font-size:11px;font-weight:700;color:#718096;margin-bottom:4px">CATATAN</div>
                    <div style="font-size:12px;color:#4a5568;white-space:pre-wrap">{{ $invoice->catatan }}</div>
                </div>
            @endif

            <div class="rekening-sign-grid">
                <div>@include('invoices._partials.rekening')</div>
                <div class="sign-section">@include('invoices._partials.sign')</div>
            </div>

            <div style="margin-top:32px;padding-top:12px;border-top:1px solid #e2e8f0;text-align:center;font-size:10px;color:#a0aec0">
                {{ $invoice->brand->phone }} {{ $invoice->brand->email ? '· '.$invoice->brand->email : '' }} {{ $invoice->brand->website ? '· '.$invoice->brand->website : '' }}
            </div>
        </div>
    </div>

    @if (request('auto'))
        <script>window.print();</script>
    @endif

</body>
</html>
