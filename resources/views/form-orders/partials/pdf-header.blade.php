    {{--
        Tabel (bukan flex) supaya kop tetap tampil sejajar kiri-kanan saat dirender ke PDF oleh
        dompdf, yang tidak mendukung flexbox. Dibungkus div "pdf-kop" (position:fixed, lihat
        pdf.blade.php) supaya kop otomatis terulang di setiap halaman, termasuk saat Lingkup
        Pekerjaan meluber ke halaman berikutnya.
    --}}
    <div class="{{ $forPdf ? 'pdf-kop' : '' }}">
    <table style="width:100%;border-collapse:collapse;background-color:{{ $brand->color_header ?? '#1a365d' }}">
        <tr>
            <td style="padding:18px 24px;vertical-align:middle">
                <table style="border-collapse:collapse">
                    <tr>
                        @if ($brand->logo_path)
                            <td style="vertical-align:middle;padding-right:12px">
                                <img src="{{ $src($brand->logo_path) }}" style="height:44px;max-width:130px;object-fit:contain">
                            </td>
                        @endif
                        <td style="vertical-align:middle">
                            <div style="font-size:18px;font-weight:800;color:#fff">{{ $brand->name }}</div>
                            <div style="font-size:11px;color:rgba(255,255,255,.7)">{{ $brand->address }}</div>
                            <div style="font-size:11px;color:rgba(255,255,255,.7)">{{ $brand->phone }} {{ $brand->email ? '· '.$brand->email : '' }}</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="padding:18px 24px;vertical-align:middle;text-align:right">
                <div style="font-size:22px;font-weight:800;color:{{ $brand->color_accent ?? '#c9a227' }};letter-spacing:2px">FORM ORDER</div>
                <div style="font-size:11px;color:rgba(255,255,255,.8)">{{ $formOrder->nomor }}</div>
                <div style="font-size:11px;color:rgba(255,255,255,.7)">{{ $formOrder->tanggal_order->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
    </div>
