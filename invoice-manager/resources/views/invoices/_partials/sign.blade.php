@php
    $sign = $invoice->sign_config ?? [];
    $hasSign = ! empty($sign['ttd_path']) || ! empty($sign['stempel_path']) || ! empty($sign['materai_path']);
    $forPdf = $forPdf ?? false;
    $hideQris = $hideQris ?? false;
    $src = fn ($path) => str_starts_with($path, 'data:')
        ? $path
        : ($forPdf
            ? 'file://'.str_replace('\\', '/', public_path('storage/'.$path))
            : \Illuminate\Support\Facades\Storage::url($path));
@endphp

@if ($invoice->qris_path && ! $hideQris)
    <div style="text-align:center">
        <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">Scan QRIS untuk pembayaran</div>
        <img src="{{ $src($invoice->qris_path) }}" alt="QRIS" style="margin:0 auto;height:160px;width:160px;object-fit:contain;border-radius:8px;border:1px solid #e2e8f0;background:#fff;padding:8px">
    </div>
@endif

@if ($hasSign)
    <table style="width:100%;border-collapse:collapse">
        <tr>
            <td style="text-align:right">
                <div style="display:inline-block;text-align:center;min-width:200px">
                    <div style="font-size:11px;color:#64748b;margin-bottom:8px">TTD</div>
                    {{-- Logo/stempel digeser ke kiri (bukan 50% pas tengah) supaya ada ruang
                         kosong di kanan untuk tanda tangan, dan tanda tangan dikunci ke sisi
                         kanan kotak (right:0) supaya tidak meluber keluar section. --}}
                    <div style="position:relative;height:100px">
                        @if (! empty($sign['stempel_path']))
                            <img src="{{ $src($sign['stempel_path']) }}" style="position:absolute;top:50%;left:38%;margin-top:-50px;margin-left:-50px;opacity:.55;height:100px;width:100px;object-fit:contain">
                        @endif
                        @if (! empty($sign['materai_path']))
                            <img src="{{ $src($sign['materai_path']) }}" style="position:absolute;top:50%;left:38%;margin-top:-28px;margin-left:-28px;height:56px;width:56px;object-fit:contain">
                        @endif
                        @if (! empty($sign['ttd_path']))
                            <div style="position:absolute;top:50%;right:0;margin-top:-32px">
                                <img src="{{ $src($sign['ttd_path']) }}" style="height:64px;max-width:110px;object-fit:contain">
                            </div>
                        @endif
                    </div>
                    <div style="border-top:1px solid #334155;padding-top:4px;margin-top:4px;font-size:13px;font-weight:700">{{ $sign['ttd_nama'] ?? $invoice->brand->name }}</div>
                    @if (! empty($sign['ttd_jabatan']))
                        <div style="font-size:11px;color:#64748b">{{ $sign['ttd_jabatan'] }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
@endif
