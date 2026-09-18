@php
    $sph = $invoice->sph_config ?? [];
    $sphSign = $invoice->sign_config ?? [];
    $sphHasSign = ! empty($sphSign['ttd_path']) || ! empty($sphSign['stempel_path']) || ! empty($sphSign['materai_path']);
    $sphForPdf = $forPdf ?? false;
    $sphSrc = fn ($path) => str_starts_with($path, 'data:')
        ? $path
        : ($sphForPdf
            ? 'file://'.str_replace('\\', '/', public_path('storage/'.$path))
            : \Illuminate\Support\Facades\Storage::url($path));
@endphp

@if (! empty($sph['aktif']))
    <div class="kop-wrap">
        @include('invoices._partials.kop', ['variant' => 'sph'])
    </div>

    <div class="sph-page" style="padding: 32px; max-width: 760px; margin: 0 auto; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 10px 10px">
        <div style="margin-bottom: 20px">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                <div>
                    <div style="font-size:11px;color:#718096;font-weight:700;text-transform:uppercase">No. Ref</div>
                    <div style="font-size:13px;font-weight:600;color:#1a365d">{{ str_replace('INV/', 'SPH/', $invoice->nomor) }}</div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:11px;color:#718096;font-weight:700;text-transform:uppercase">Tanggal</div>
                    <div style="font-size:13px;color:#2d3748">{{ $sph['tempat_tanggal'] ?? $invoice->tanggal->format('d M Y') }}</div>
                </div>
            </div>
            <div style="background:#f0f7ff;border-left:3px solid #1a365d;border-radius:0 8px 8px 0;padding:10px 16px;margin-top:8px">
                <span style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase">Perihal: </span>
                <span style="font-size:13px;font-weight:700;color:#1a365d">{{ $sph['perihal'] ?? 'Penawaran Harga' }}</span>
            </div>
        </div>

        <div style="font-size:13px;color:#2d3748;line-height:1.8;white-space:pre-wrap;margin-bottom:24px">{{ $sph['narasi'] ?? '' }}</div>

        <div style="display:flex;justify-content:flex-end;margin-top:24px">
            <div style="text-align:center;min-width:200px">
                <div style="font-size:12px;color:#718096;margin-bottom:8px">Hormat Kami,</div>
                @if ($sphHasSign)
                    {{-- Logo/stempel digeser ke kiri (bukan 50% pas tengah) supaya ada ruang
                         kosong di kanan untuk tanda tangan, dan tanda tangan dikunci ke sisi
                         kanan kotak (right:0) supaya tidak meluber keluar section. --}}
                    <div style="position:relative;width:200px;height:100px;margin:0 auto">
                        @if (! empty($sphSign['stempel_path']))
                            <img src="{{ $sphSrc($sphSign['stempel_path']) }}" style="position:absolute;top:50%;left:38%;margin-top:-50px;margin-left:-50px;opacity:.55;height:100px;width:100px;object-fit:contain">
                        @endif
                        @if (! empty($sphSign['materai_path']))
                            <img src="{{ $sphSrc($sphSign['materai_path']) }}" style="position:absolute;top:50%;left:38%;margin-top:-28px;margin-left:-28px;height:56px;width:56px;object-fit:contain">
                        @endif
                        @if (! empty($sphSign['ttd_path']))
                            <div style="position:absolute;top:50%;right:0;margin-top:-32px">
                                <img src="{{ $sphSrc($sphSign['ttd_path']) }}" style="height:64px;max-width:110px;object-fit:contain">
                            </div>
                        @endif
                    </div>
                @else
                    <div style="height:52px"></div>
                @endif
                <div style="border-top:1.5px solid #2d3748;padding-top:6px;margin-top:4px">
                    <div style="font-size:13px;font-weight:700;color:#1a365d">{{ $sph['pengirim'] ?? $invoice->brand->name }}</div>
                </div>
            </div>
        </div>
    </div>

    <div style="page-break-after: always"></div>
@endif
