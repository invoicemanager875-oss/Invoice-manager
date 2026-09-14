@php
    $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KPI Drafter {{ $monthNames[$month - 1] }} {{ $year }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #1a1a1a; margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; }
        .page { max-width: 760px; margin: 0 auto; padding: 24px; }
        h1 { font-size: 18px; margin: 0 0 2px; color: #1a365d; }
        p.subtitle { font-size: 12px; color: #718096; margin: 0 0 20px; }
        th { background: #f7fafc; padding: 8px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #4a5568; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        td { padding: 8px 12px; border-bottom: 1px solid #f0f4f8; font-size: 12px; }
        .text-right { text-align: right; }
        .ontime { color: #059669; }
        .late { color: #dc2626; }
        .muted { color: #94a3b8; }
        @page { size: A4; margin: 16mm 12mm; }
    </style>
</head>
<body>
    <div class="page">
        <h1>KPI Drafter</h1>
        <p class="subtitle">Periode {{ $monthNames[$month - 1] }} {{ $year }} &middot; Dicetak {{ now()->translatedFormat('d M Y H:i') }}</p>

        <table>
            <thead>
                <tr>
                    <th>Drafter</th>
                    <th class="text-right">Total Selesai</th>
                    <th class="text-right">Tepat Waktu</th>
                    <th class="text-right">Terlambat</th>
                    <th class="text-right">Tanpa Deadline</th>
                    <th class="text-right">% Tepat Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($drafterKpi as $row)
                    <tr>
                        <td><strong>{{ $row['drafter']->name ?? '-' }}</strong></td>
                        <td class="text-right">{{ $row['total_selesai'] }}</td>
                        <td class="text-right ontime">{{ $row['tepat_waktu'] }}</td>
                        <td class="text-right late">{{ $row['terlambat'] }}</td>
                        <td class="text-right muted">{{ $row['tanpa_deadline'] }}</td>
                        <td class="text-right"><strong>{{ $row['persen_tepat_waktu'] !== null ? $row['persen_tepat_waktu'].'%' : '-' }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-right muted" style="text-align:center">Belum ada tugas selesai pada bulan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
