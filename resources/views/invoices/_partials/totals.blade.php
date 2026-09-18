{{-- Tabel (bukan flex) supaya baris subtotal/total tetap rata kiri-kanan saat dirender ke PDF oleh dompdf. --}}
<table style="width:280px;max-width:100%;margin-left:auto;border-collapse:collapse;font-size:13px">
    <tr>
        <td style="padding:3px 0;color:#64748b">Subtotal</td>
        <td style="padding:3px 0;text-align:right;font-weight:600;color:#334155">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td style="padding:3px 0;color:#64748b">Diskon</td>
        <td style="padding:3px 0;text-align:right;color:#334155">{{ rtrim(rtrim($invoice->diskon_persen, '0'), '.') }}%</td>
    </tr>
    <tr>
        <td style="padding:3px 0;color:#64748b">PPN</td>
        <td style="padding:3px 0;text-align:right;color:#334155">{{ rtrim(rtrim($invoice->ppn_persen, '0'), '.') }}%</td>
    </tr>
    <tr>
        <td style="padding:8px 0 3px;border-top:1px solid #e2e8f0;font-size:15px;font-weight:800;color:#1a365d">Total</td>
        <td style="padding:8px 0 3px;border-top:1px solid #e2e8f0;text-align:right;font-size:15px;font-weight:800;color:#1a365d">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="2" style="padding:4px 0 0;text-align:right;font-size:11px;font-style:italic;color:#64748b">
            Terbilang: {{ \App\Support\Terbilang::rupiah($invoice->total) }}
        </td>
    </tr>
</table>
