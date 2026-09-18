@php
    $brand = $jadwal->brand;
    $logoSrc = null;
    if ($brand && $brand->logo_path) {
        $logoSrc = 'file://'.str_replace('\\', '/', public_path('storage/'.$brand->logo_path));
    }
    $colors = ['#a9c9e8', '#f5dfa0', '#e8a898', '#cfc4e8'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Perencanaan - {{ $jadwal->nama_proyek }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 8mm 8mm 8mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            font-size: 9px;
            line-height: 1.2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .kop-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .kop-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .monogram {
            width: 50px;
            height: 50px;
            background-color: #1a365d;
            color: #d69e2e;
            font-size: 20px;
            font-weight: 900;
            text-align: center;
            line-height: 50px;
            border-radius: 6px;
        }
        .brand-title {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            color: #1a365d;
            letter-spacing: 0.5px;
        }
        .brand-sub {
            font-size: 9px;
            color: #4a5568;
            margin-top: 2px;
        }
        .divider {
            border-bottom: 2.5px solid #1a1a1a;
            margin: 6px 0 8px 0;
        }
        .info-table td {
            font-size: 9px;
            padding: 2px 4px;
            border: none;
        }
        .badge-friday {
            background-color: #e53e3e;
            color: #ffffff;
            font-weight: bold;
            font-size: 8px;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
        }
        .gantt-table {
            width: 100%;
            border: 1px solid #cbd5e0;
            margin-top: 6px;
        }
        .gantt-table th, .gantt-table td {
            border: 1px solid #cbd5e0;
            padding: 3px 2px;
            text-align: center;
            font-size: 8px;
        }
        .gantt-table th.th-month {
            background-color: #1a1a1a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            padding: 4px 2px;
        }
        .gantt-table th.th-day {
            background-color: #f7fafc;
            color: #2d3748;
            font-size: 7.5px;
        }
        .gantt-table th.th-friday, .gantt-table td.td-friday {
            background-color: #e53e3e !important;
            color: #ffffff !important;
            font-weight: bold;
        }
        .td-friday-soft {
            background-color: #fff5f5;
        }
        .item-title {
            text-align: left !important;
            font-weight: 600;
            padding-left: 5px !important;
            color: #2d3748;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .legal-footer {
            margin-top: 8px;
            font-size: 8px;
            color: #718096;
            text-align: right;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    {{-- 1. Header Kop Brand --}}
    <table class="kop-table">
        <tr>
            <td style="width: 55px;">
                @if ($logoSrc)
                    <img src="{{ $logoSrc }}" class="kop-logo" alt="Logo">
                @else
                    <div class="monogram">
                        {{ strtoupper(substr($brand->name ?? 'BG', 0, 2)) }}
                    </div>
                @endif
            </td>
            <td style="padding-left: 10px;">
                <div class="brand-title">{{ $brand->name ?? 'BASYID GROUP' }}</div>
                <div class="brand-sub">
                    {{ $brand->address ?? 'Professional Architecture, Interior & Planning Services' }}
                    @if ($brand->phone) &middot; Telp: {{ $brand->phone }} @endif
                    @if ($brand->email) &middot; Email: {{ $brand->email }} @endif
                </div>
            </td>
            <td style="text-align: right;">
                <div style="font-size: 13px; font-weight: bold; color: #1a365d;">TIMELINE PROYEK & KURVA S</div>
                <div style="font-size: 8px; color: #718096;">Tanggal Cetak: {{ date('d F Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- 2. Garis Pembatas --}}
    <div class="divider"></div>

    {{-- 3. Info Project Timeline --}}
    <table class="info-table" style="margin-bottom: 4px;">
        <tr>
            <td style="width: 14%; font-weight: bold; color: #4a5568;">NAMA PROJECT</td>
            <td style="width: 36%;">: <strong>{{ $jadwal->nama_proyek }}</strong></td>
            <td style="width: 14%; font-weight: bold; color: #4a5568;">DURASI KERJA</td>
            <td style="width: 36%;">: {{ $jadwal->durasi_hari }} Hari Kalender ({{ $jadwal->tanggal_mulai ? $jadwal->tanggal_mulai->format('d/m/Y') : '-' }} s/d {{ $jadwal->tanggal_selesai ? $jadwal->tanggal_selesai->format('d/m/Y') : '-' }})</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #4a5568;">LOCATION</td>
            <td>: {{ $jadwal->lokasi ?? '-' }}</td>
            <td style="font-weight: bold; color: #4a5568;">PROGRESS CUTOFF</td>
            <td>: <span class="badge-friday">[P] Friday (Progress Checkpoint)</span></td>
        </tr>
    </table>

    {{-- 4 & 5. Tabel Kalender Dua Baris & Batang Progres Gantt --}}
    <table class="gantt-table">
        <thead>
            {{-- Header Row 1: Months Grouping --}}
            <tr>
                <th rowspan="2" style="width: 18px;" class="th-month">No</th>
                <th rowspan="2" style="width: 140px;" class="th-month">Uraian Pekerjaan</th>
                <th rowspan="2" style="width: 35px;" class="th-month">Bobot</th>
                @foreach ($kurvaSData['monthsGroup'] as $mInfo)
                    <th colspan="{{ $mInfo['count'] }}" class="th-month" style="letter-spacing: 0.5px;">
                        {{ $mInfo['label'] }}
                    </th>
                @endforeach
            </tr>

            {{-- Header Row 2: Day Numbers with Friday (P) Highlight --}}
            <tr>
                @foreach ($kurvaSData['days'] as $day)
                    <th class="th-day {{ $day['is_jumat'] ? 'th-friday' : '' }}" style="width: 14px;">
                        <div>{{ $day['day_num'] }}</div>
                        <div style="font-size: 6.5px; opacity: 0.9;">{{ $day['is_jumat'] ? 'P' : 'H'.$day['hari_ke'] }}</div>
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach ($jadwal->items as $idx => $item)
                @php
                    $rowColor = $colors[$idx % count($colors)];
                @endphp
                <tr>
                    <td style="color: #718096; font-weight: bold;">{{ $loop->iteration }}</td>
                    <td class="item-title" title="{{ $item->nama_item }}">{{ $item->nama_item }}</td>
                    <td style="font-weight: bold; color: #1a365d;">{{ number_format($item->bobot, 2) }}%</td>
                    @foreach ($kurvaSData['days'] as $day)
                        @php
                            $h = $day['hari_ke'];
                            $isActive = ($h >= $item->hari_mulai && $h <= $item->hari_selesai);
                            $val = 0;
                            if ($item->distribusi && isset($item->distribusi[(string) $h])) {
                                $val = (float) $item->distribusi[(string) $h];
                            } elseif ($isActive && $item->durasi > 0) {
                                $val = $item->bobot / $item->durasi;
                            }
                        @endphp
                        <td style="{{ $isActive ? 'background-color: ' . $rowColor . '; font-weight: bold;' : ($day['is_jumat'] ? 'background-color: #fff5f5;' : '') }}">
                            @if ($isActive && $val > 0)
                                {{ number_format($val, 2) }}
                            @else
                                &nbsp;
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            {{-- Daily Plan Row --}}
            <tr style="background-color: #edf2f7; font-weight: bold;">
                <td colspan="2" style="text-align: right; font-weight: bold; font-size: 7.5px;">RENCANA HARIAN (%)</td>
                <td style="font-weight: bold;">{{ number_format($jadwal->total_bobot, 2) }}%</td>
                @foreach ($kurvaSData['days'] as $day)
                    <td style="{{ $day['is_jumat'] ? 'background-color: #feb2b2; color: #9b2c2c;' : '' }}">
                        {{ $day['bobot_rencana'] > 0 ? number_format($day['bobot_rencana'], 2) : '-' }}
                    </td>
                @endforeach
            </tr>

            {{-- Cumulative Plan Row --}}
            <tr style="background-color: #ebf8ff; font-weight: bold; color: #2b6cb0;">
                <td colspan="2" style="text-align: right; font-weight: bold; font-size: 7.5px;">KUMULATIF RENCANA (%)</td>
                <td style="font-weight: bold;">100.00%</td>
                @foreach ($kurvaSData['days'] as $day)
                    <td style="{{ $day['is_jumat'] ? 'background-color: #e53e3e; color: #ffffff; font-weight: bold;' : '' }}">
                        {{ number_format($day['kumulatif_rencana'], 2) }}
                    </td>
                @endforeach
            </tr>
        </tfoot>
    </table>

    {{-- 6. Legal Footer --}}
    <div class="legal-footer">
        Dokumen perencanaan ini dibuat secara otomatis oleh sistem Invoice & Project Manager &copy; {{ date('Y') }} {{ $brand->name ?? 'Basyid Group' }}. Seluruh hak cipta dilindungi.
    </div>

</body>
</html>
