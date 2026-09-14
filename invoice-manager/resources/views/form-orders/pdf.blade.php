@php
    $forPdf = $forPdf ?? false;
    $brand = $formOrder->brand;
    $src = fn ($path) => $forPdf
        ? 'file://'.str_replace('\\', '/', public_path('storage/'.$path))
        : \Illuminate\Support\Facades\Storage::url($path);
    $paketItems = collect(config('invoice_packages'))->pluck('items', 'label');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $formOrder->nomor }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #1a1a1a; margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; }
        img { max-width: 100%; }
        .page { max-width: 760px; margin: 0 auto; padding: 24px; }
        th { background: #f7fafc; padding: 8px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #4a5568; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        td { padding: 8px 12px; border-bottom: 1px solid #f0f4f8; font-size: 13px; }
        /* Kop dicetak position:fixed supaya otomatis terulang di setiap halaman
           (mis. saat Lingkup Pekerjaan meluber ke halaman berikutnya). Ruang di
           atas setiap halaman disediakan lewat margin-top pada @page. */
        .pdf-kop { position: fixed; top: -35mm; left: -12mm; right: -12mm; }
        @page { size: A4; margin: 35mm 12mm 12mm 12mm; }
    </style>
</head>
<body>

    @include('form-orders.partials.pdf-header')

    <div class="page">
        <table style="margin-bottom:20px">
            <tr><td style="width:35%;color:#718096">Nama Klien</td><td>: <strong>{{ $formOrder->nama_klien }}</strong></td></tr>
            <tr><td style="color:#718096">Lokasi Project</td><td>: {{ $formOrder->lokasi_project ?: '-' }}</td></tr>
            <tr><td style="color:#718096">Jenis Pekerjaan</td><td>: {{ $formOrder->jenis_pekerjaan ?: '-' }}</td></tr>
            <tr><td style="color:#718096">Ukuran Bangunan</td><td>: {{ $formOrder->ukuran_bangunan ?: '-' }}</td></tr>
            <tr><td style="color:#718096">Arah Mata Angin</td><td>: {{ $formOrder->arah_mata_angin ?: '-' }}</td></tr>
            @if ($formOrder->share_location)
                <tr><td style="color:#718096">Share Location</td><td>: {{ $formOrder->share_location }}</td></tr>
            @endif
        </table>

        @if (! empty($formOrder->lingkup_pekerjaan))
            <div style="font-size:13px;font-weight:700;color:#1a365d;margin-bottom:8px">Lingkup Pekerjaan</div>
            <table style="margin-bottom:20px">
                <thead><tr><th style="width:40px;text-align:center">No</th><th>Item Pekerjaan</th></tr></thead>
                <tbody>
                    @foreach ($formOrder->lingkup_pekerjaan as $i => $item)
                        <tr>
                            <td style="text-align:center;color:#718096;vertical-align:top">{{ $i + 1 }}</td>
                            <td>
                                {{ $item }}
                                @if ($paketItems->has($item))
                                    <ul style="margin:6px 0 0;padding-left:16px;color:#4a5568;font-size:11px;line-height:1.6">
                                        @foreach ($paketItems[$item] as $fitur)
                                            <li>{{ $fitur }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if ($formOrder->catatan_klien || $formOrder->images->isNotEmpty())
        <div style="page-break-before:always">
            <div class="page">
                @if ($formOrder->catatan_klien)
                    <div style="font-size:13px;font-weight:700;color:#1a365d;margin-bottom:8px">Catatan dari Klien</div>
                    <div style="background:#f7fafc;border-left:3px solid #c9a227;border-radius:0 8px 8px 0;padding:10px 14px;font-size:12px;white-space:pre-wrap;margin-bottom:20px">{{ $formOrder->catatan_klien }}</div>
                @endif

                @if ($formOrder->images->isNotEmpty())
                    <div style="font-size:13px;font-weight:700;color:#1a365d;margin-bottom:8px">Lampiran Gambar</div>
                    @foreach ($formOrder->images as $img)
                        <div style="margin-bottom:16px">
                            <img src="{{ $src($img->path) }}" style="max-width:{{ $img->pdf_max_width }}px;max-height:520px;object-fit:contain;border:1px solid #e2e8f0;border-radius:6px;display:block">
                            @if ($img->caption)
                                <div style="font-size:11px;color:#4a5568;margin-top:4px">{{ $img->caption }}</div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endif

    <div class="page">
        @if ($formOrder->revisions->isNotEmpty())
            <div style="font-size:13px;font-weight:700;color:#1a365d;margin-bottom:8px;margin-top:20px">Revisi</div>
            <table style="margin-bottom:20px">
                @foreach ($formOrder->revisions as $rev)
                    <tr>
                        <td style="vertical-align:top;padding:8px 12px;border-bottom:1px solid #f0f4f8">
                            @if ($rev->path)
                                <img src="{{ $src($rev->path) }}" style="width:90px;height:90px;object-fit:cover;border:1px solid #e2e8f0;border-radius:6px;display:block">
                            @endif
                        </td>
                        <td style="vertical-align:top;padding:8px 12px;border-bottom:1px solid #f0f4f8">
                            <div style="font-size:10px;color:#a0aec0;margin-bottom:3px">{{ $rev->created_at->format('d M Y, H:i') }}</div>
                            <div style="font-size:12px;white-space:pre-wrap">{{ $rev->catatan ?: '-' }}</div>
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif

        <div style="margin-top:24px;padding-top:10px;border-top:1px solid #e2e8f0;text-align:center;font-size:10px;color:#a0aec0">
            {{ $brand->phone }} {{ $brand->email ? '· '.$brand->email : '' }} {{ $brand->website ? '· '.$brand->website : '' }}
        </div>
    </div>

</body>
</html>
