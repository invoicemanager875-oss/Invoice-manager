@php
    $kop = $invoice->kop_config ?? [];
    $theme = $invoice->theme;
    $forPdf = $forPdf ?? false;
    $variant = $variant ?? 'invoice'; // 'invoice' | 'sph'
    $src = fn ($path) => str_starts_with($path, 'data:')
        ? $path
        : ($forPdf
            ? 'file://'.str_replace('\\', '/', public_path('storage/'.$path))
            : \Illuminate\Support\Facades\Storage::url($path));
@endphp

@if (! empty($kop['custom_image_path']))
    <div>
        <img src="{{ $src($kop['custom_image_path']) }}" alt="Kop surat" style="width:100%;max-height:160px;object-fit:contain;display:block">
    </div>
@else
    {{-- Tabel (bukan flex/grid) supaya kop tetap tampil sejajar kiri-kanan baik di browser
         maupun saat dirender ke PDF oleh dompdf, yang tidak mendukung flexbox/grid. --}}
    <table style="width:100%;border-collapse:collapse;background-color:{{ $theme['hdr'] }}">
        <tr>
            <td style="padding:20px 24px;vertical-align:middle">
                <table style="border-collapse:collapse">
                    <tr>
                        @if (! empty($kop['logo_path']))
                            <td style="vertical-align:middle;padding-right:12px">
                                <img src="{{ $src($kop['logo_path']) }}" alt="{{ $kop['name'] ?? '' }}" style="height:48px;width:48px;object-fit:contain;border-radius:6px">
                            </td>
                        @endif
                        <td style="vertical-align:middle">
                            <div style="font-weight:700;color:#ffffff;font-size:18px;line-height:1.3">{{ $kop['name'] ?? $invoice->brand->name }}</div>
                            <div style="font-size:11px;color:rgba(255,255,255,.7)">{{ $kop['address'] ?? '' }}</div>
                            @if ($variant === 'invoice')
                                <div style="font-size:11px;color:rgba(255,255,255,.7)">{{ $kop['phone'] ?? '' }} {{ ! empty($kop['email']) ? '· '.$kop['email'] : '' }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td style="padding:20px 24px;vertical-align:middle;text-align:right">
                @if ($variant === 'sph')
                    <div style="font-size:11px;color:rgba(255,255,255,.8)">{{ $kop['phone'] ?? '' }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.7)">{{ $kop['email'] ?? '' }}</div>
                @else
                    <div style="font-size:22px;font-weight:800;letter-spacing:3px;color:{{ $theme['acc'] }}">INVOICE</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.8)">{{ $invoice->nomor }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.7)">Tgl: {{ $invoice->tanggal->format('d M Y') }}{{ $invoice->jatuh_tempo ? ' · Jth Tempo: '.$invoice->jatuh_tempo->format('d M Y') : '' }}</div>
                @endif
            </td>
        </tr>
    </table>
@endif
