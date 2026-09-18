<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-navy-600">{{ $jadwal->nama_proyek }}</h2>
                    @if ($jadwal->status === 'published')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Published
                        </span>
                    @elseif ($jadwal->status === 'archived')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            Archived
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            Draft
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 mt-0.5">
                    {{ $jadwal->brand->name ?? 'Semua Brand' }} &middot;
                    {{ $jadwal->lokasi ?? 'Lokasi belum ditentukan' }} &middot;
                    {{ $jadwal->durasi_hari }} Hari Kalender
                </p>
            </div>

            <div class="flex items-center gap-2">
                @can('update', $jadwal)
                    @if ($jadwal->status !== 'published')
                        <form action="{{ route('jadwal-perencanaan.publish', $jadwal) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm"
                                    @if (! $jadwal->is_bobot_valid) disabled title="Total bobot harus 100.00%" class="opacity-50 cursor-not-allowed" @endif>
                                <x-icon name="check-circle" class="w-4 h-4" />
                                Publikasikan Jadwal
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('jadwal-perencanaan.edit', $jadwal) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 transition shadow-sm">
                        <x-icon name="pencil" class="w-4 h-4" />
                        Edit Jadwal
                    </a>
                @endcan

                @can('exportPdf', $jadwal)
                    <a href="{{ route('jadwal-perencanaan.pdf', $jadwal) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold bg-navy-600 hover:bg-navy-700 text-white transition shadow-sm">
                        <x-icon name="arrow-down-tray" class="w-4 h-4" />
                        Export PDF
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    {{-- Interactive 4-Tab Container --}}
    <div x-data="{ activeTab: 'umum' }" class="space-y-6">

        {{-- Tab Navigation Bar --}}
        <div class="border-b border-slate-200 bg-white rounded-t-xl px-4 flex gap-6 text-sm font-semibold">
            <button @click="activeTab = 'umum'"
                    :class="activeTab === 'umum' ? 'border-navy-600 text-navy-600 border-b-2 py-3.5' : 'text-slate-500 hover:text-slate-700 py-3.5'"
                    class="transition flex items-center gap-2">
                <span>⚙️</span> Data Umum & Ringkasan
            </button>
            <button @click="activeTab = 'distribusi'"
                    :class="activeTab === 'distribusi' ? 'border-navy-600 text-navy-600 border-b-2 py-3.5' : 'text-slate-500 hover:text-slate-700 py-3.5'"
                    class="transition flex items-center gap-2">
                <span>📊</span> Matriks Distribusi Harian (Gantt)
            </button>
            <button @click="activeTab = 'kurvas'"
                    :class="activeTab === 'kurvas' ? 'border-navy-600 text-navy-600 border-b-2 py-3.5' : 'text-slate-500 hover:text-slate-700 py-3.5'"
                    class="transition flex items-center gap-2">
                <span>📈</span> Grafik Kurva S
            </button>
            <button @click="activeTab = 'ekspor'"
                    :class="activeTab === 'ekspor' ? 'border-navy-600 text-navy-600 border-b-2 py-3.5' : 'text-slate-500 hover:text-slate-700 py-3.5'"
                    class="transition flex items-center gap-2">
                <span>🖨</span> Cetak & Dokumen Legal
            </button>
        </div>

        {{-- TAB 1: DATA UMUM --}}
        <div x-show="activeTab === 'umum'" class="space-y-6">
            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-semibold uppercase text-slate-400">Durasi Pelaksanaan</span>
                    <div class="text-2xl font-black text-navy-700 mt-1">{{ $jadwal->durasi_hari }} <span class="text-sm font-semibold text-slate-500">Hari</span></div>
                    <span class="text-xs text-slate-400 mt-1 block">
                        {{ $jadwal->tanggal_mulai ? $jadwal->tanggal_mulai->format('d M Y') : '-' }} s/d {{ $jadwal->tanggal_selesai ? $jadwal->tanggal_selesai->format('d M Y') : '-' }}
                    </span>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-semibold uppercase text-slate-400">Total Bobot Proyek</span>
                    <div class="text-2xl font-black mt-1 {{ $jadwal->is_bobot_valid ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ number_format($jadwal->total_bobot, 2) }}%
                    </div>
                    <span class="text-xs font-bold mt-1 block {{ $jadwal->is_bobot_valid ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $jadwal->is_bobot_valid ? '✓ Tepat 100.00% (Valid)' : '⚠ Belum 100% (Draft)' }}
                    </span>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-semibold uppercase text-slate-400">Jumlah Tahapan</span>
                    <div class="text-2xl font-black text-navy-700 mt-1">{{ $jadwal->items->count() }} <span class="text-sm font-semibold text-slate-500">Item</span></div>
                    <span class="text-xs text-slate-400 mt-1 block">Rangkaian pekerjaan terdistribusi</span>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-semibold uppercase text-slate-400">Referensi RAB / Invoice</span>
                    @if ($jadwal->invoice)
                        <div class="text-lg font-black text-navy-700 mt-1 truncate">Rp {{ number_format($jadwal->invoice->total, 0, ',', '.') }}</div>
                        <span class="text-xs text-slate-500 mt-1 block truncate">Inv: {{ $jadwal->invoice->nomor }}</span>
                    @else
                        <div class="text-lg font-semibold text-slate-400 mt-1">Tidak terhubung</div>
                        <span class="text-xs text-slate-400 mt-1 block">Standalone Schedule</span>
                    @endif
                </div>
            </div>

            {{-- Parameter Details & Item List --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-navy-700 uppercase tracking-wide">Rincian Tahapan Pekerjaan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 border-b border-slate-200">
                                <th class="text-center w-12 px-4 py-2.5">No</th>
                                <th class="text-left px-4 py-2.5">Nama Tahapan Pekerjaan</th>
                                <th class="text-center w-32 px-4 py-2.5">Bobot</th>
                                <th class="text-center w-32 px-4 py-2.5">Rentang Hari</th>
                                <th class="text-center w-28 px-4 py-2.5">Durasi</th>
                                <th class="text-center w-36 px-4 py-2.5">Rata-rata/Hari</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($jadwal->items as $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="text-center font-bold text-slate-400 py-3">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $item->nama_item }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-navy-700">{{ number_format($item->bobot, 2) }}%</td>
                                    <td class="px-4 py-3 text-center text-xs font-semibold text-slate-600">
                                        H{{ $item->hari_mulai }} &mdash; H{{ $item->hari_selesai }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $item->durasi }} Hari</td>
                                    <td class="px-4 py-3 text-center text-xs text-slate-500">
                                        {{ $item->durasi > 0 ? number_format($item->bobot / $item->durasi, 2) : 0 }}% / hari
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-slate-400 text-sm">Belum ada item tahapan pekerjaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                            <tr>
                                <td colspan="2" class="text-right px-4 py-3 text-slate-700 uppercase text-xs">Total Bobot:</td>
                                <td class="px-4 py-3 text-center text-sm {{ $jadwal->is_bobot_valid ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ number_format($jadwal->total_bobot, 2) }}%
                                </td>
                                <td colspan="3" class="px-4 py-3 text-xs {{ $jadwal->is_bobot_valid ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $jadwal->is_bobot_valid ? '✓ Persentase tepat 100.00%' : '⚠ Harus tepat 100.00% sebelum dipublikasikan' }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: DISTRIBUSI HARIAN (GANTT MATRIX) --}}
        <div x-show="activeTab === 'distribusi'" class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between flex-wrap gap-2 text-xs text-slate-600">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-rose-600 inline-block"></span> <strong class="text-rose-700">P (Friday)</strong>: Progress Cutoff</span>
                    <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-[#a9c9e8] inline-block"></span> Rencana Pekerjaan Aktif</span>
                </div>
                <div class="text-slate-500">
                    Durasi: <strong>{{ $kurvaSData['durasi_hari'] }} Hari</strong> &middot; Evaluasi berkala setiap hari Jumat
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] border-collapse">
                        <thead>
                            {{-- Header Month Grouping --}}
                            <tr class="bg-navy-900 text-white uppercase tracking-wider text-center text-[10px]">
                                <th class="sticky left-0 bg-navy-900 z-10 px-3 py-1.5 text-left w-10 border-r border-navy-800">No</th>
                                <th class="sticky left-10 bg-navy-900 z-10 px-3 py-1.5 text-left min-w-[200px] border-r border-navy-800">Uraian Pekerjaan</th>
                                <th class="sticky left-[240px] bg-navy-900 z-10 px-2 py-1.5 text-center w-16 border-r border-navy-800">Bobot</th>
                                @foreach ($kurvaSData['monthsGroup'] as $mKey => $mInfo)
                                    <th colspan="{{ $mInfo['count'] }}" class="border-r border-navy-800 py-1.5 text-gold-400 font-bold">
                                        {{ $mInfo['label'] }}
                                    </th>
                                @endforeach
                            </tr>

                            {{-- Header Days Row --}}
                            <tr class="bg-slate-100 text-slate-600 border-b border-slate-200 text-center font-bold">
                                <th class="sticky left-0 bg-slate-100 z-10 px-2 py-2 border-r border-slate-200">#</th>
                                <th class="sticky left-10 bg-slate-100 z-10 px-3 py-2 text-left border-r border-slate-200">Tahapan</th>
                                <th class="sticky left-[240px] bg-slate-100 z-10 px-2 py-2 border-r border-slate-200">%</th>
                                @foreach ($kurvaSData['days'] as $day)
                                    <th class="px-1 py-1.5 min-w-[32px] border-r border-slate-200 {{ $day['is_jumat'] ? 'bg-rose-600 text-white' : '' }}">
                                        <div>{{ $day['day_num'] }}</div>
                                        <div class="text-[9px] font-normal opacity-80">{{ $day['is_jumat'] ? 'P' : 'H'.$day['hari_ke'] }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @php
                                $colors = ['#a9c9e8', '#f5dfa0', '#e8a898', '#cfc4e8'];
                            @endphp
                            @foreach ($jadwal->items as $idx => $item)
                                @php
                                    $rowColor = $colors[$idx % count($colors)];
                                @endphp
                                <tr class="hover:bg-slate-50/60">
                                    <td class="sticky left-0 bg-white z-10 px-2 py-2 text-center font-semibold text-slate-400 border-r border-slate-100">{{ $loop->iteration }}</td>
                                    <td class="sticky left-10 bg-white z-10 px-3 py-2 font-semibold text-slate-800 border-r border-slate-100 truncate max-w-[200px]" title="{{ $item->nama_item }}">
                                        {{ $item->nama_item }}
                                    </td>
                                    <td class="sticky left-[240px] bg-white z-10 px-2 py-2 text-center font-bold text-navy-700 border-r border-slate-100">
                                        {{ number_format($item->bobot, 2) }}%
                                    </td>
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
                                        <td class="px-1 py-2 text-center border-r border-slate-100 {{ $day['is_jumat'] ? 'bg-rose-50/40' : '' }}"
                                            style="{{ $isActive ? 'background-color: ' . $rowColor . ';' : '' }}">
                                            @if ($isActive && $val > 0)
                                                <span class="text-[10px] font-bold text-slate-800">{{ number_format($val, 2) }}</span>
                                            @else
                                                <span class="text-slate-300">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot class="font-bold border-t-2 border-slate-300">
                            {{-- Daily Plan Row --}}
                            <tr class="bg-slate-100 text-slate-700">
                                <td colspan="2" class="sticky left-0 bg-slate-100 z-10 px-3 py-2 text-right border-r border-slate-200 uppercase text-[10px]">
                                    Rencana Harian (%)
                                </td>
                                <td class="sticky left-[240px] bg-slate-100 z-10 px-2 py-2 text-center border-r border-slate-200">
                                    {{ number_format($jadwal->total_bobot, 2) }}%
                                </td>
                                @foreach ($kurvaSData['days'] as $day)
                                    <td class="px-1 py-2 text-center border-r border-slate-200 {{ $day['is_jumat'] ? 'bg-rose-100 text-rose-800' : '' }}">
                                        {{ $day['bobot_rencana'] > 0 ? number_format($day['bobot_rencana'], 2) : '-' }}
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Cumulative Plan Row --}}
                            <tr class="bg-navy-50 text-navy-800">
                                <td colspan="2" class="sticky left-0 bg-navy-50 z-10 px-3 py-2 text-right border-r border-navy-100 uppercase text-[10px]">
                                    Kumulatif Rencana (%)
                                </td>
                                <td class="sticky left-[240px] bg-navy-50 z-10 px-2 py-2 text-center border-r border-navy-100">
                                    100.00%
                                </td>
                                @foreach ($kurvaSData['days'] as $day)
                                    <td class="px-1 py-2 text-center border-r border-navy-100 {{ $day['is_jumat'] ? 'bg-rose-200 text-rose-900 font-extrabold' : '' }}">
                                        {{ number_format($day['kumulatif_rencana'], 2) }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 3: GRAFIK KURVA S --}}
        <div x-show="activeTab === 'kurvas'" class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h3 class="text-base font-bold text-navy-700">Visualisasi Kurva S (Dual-Axis Chart)</h3>
                        <p class="text-xs text-slate-500">Batang Biru = Bobot Rencana Harian (Sumbu Kiri) &middot; Garis Merah = Kumulatif Rencana % (Sumbu Kanan 0-100%)</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#4299e1] rounded inline-block"></span> Rencana Harian (%)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-[#e53e3e] rounded-full inline-block"></span> Kumulatif Kurva S (%)</span>
                    </div>
                </div>

                {{-- Responsive SVG Dual-Axis Chart --}}
                @php
                    $chartDays = array_values($kurvaSData['days']);
                    $count = count($chartDays);
                    $w = max(800, $count * 36);
                    $h = 320;
                    $padLeft = 50;
                    $padRight = 50;
                    $padTop = 20;
                    $padBottom = 40;
                    $plotW = $w - $padLeft - $padRight;
                    $plotH = $h - $padTop - $padBottom;

                    $maxDaily = 1.0;
                    foreach ($chartDays as $cd) {
                        if ($cd['bobot_rencana'] > $maxDaily) $maxDaily = $cd['bobot_rencana'];
                    }
                    $maxDaily = ceil($maxDaily * 1.2);

                    $polyPoints = [];
                    $barWidth = max(6, ($plotW / max(1, $count)) - 8);
                @endphp

                <div class="overflow-x-auto py-2">
                    <svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full h-auto min-w-[750px] bg-slate-50/50 rounded-lg border border-slate-100">
                        {{-- Grid lines & Y right axis labels (0%, 25%, 50%, 75%, 100%) --}}
                        @for ($pct = 0; $pct <= 100; $pct += 25)
                            @php
                                $yPos = $padTop + $plotH - ($pct / 100 * $plotH);
                            @endphp
                            <line x1="{{ $padLeft }}" y1="{{ $yPos }}" x2="{{ $w - $padRight }}" y2="{{ $yPos }}" stroke="#e2e8f0" stroke-width="1" stroke-dasharray="4,4" />
                            <text x="{{ $w - $padRight + 8 }}" y="{{ $yPos + 4 }}" font-size="10" font-weight="600" fill="#718096">{{ $pct }}%</text>
                            <text x="{{ $padLeft - 8 }}" y="{{ $yPos + 4 }}" font-size="10" font-weight="600" text-anchor="end" fill="#718096">
                                {{ number_format(($pct / 100) * $maxDaily, 1) }}%
                            </text>
                        @endfor

                        {{-- Draw Daily Bars (Blue) --}}
                        @foreach ($chartDays as $i => $cd)
                            @php
                                $x = $padLeft + ($i * ($plotW / max(1, $count))) + 4;
                                $barH = ($cd['bobot_rencana'] / max(1, $maxDaily)) * $plotH;
                                $yBar = $padTop + $plotH - $barH;

                                // Coordinate for polyline cumulative (Right axis 0-100%)
                                $lineX = $x + ($barWidth / 2);
                                $lineY = $padTop + $plotH - ($cd['kumulatif_rencana'] / 100 * $plotH);
                                $polyPoints[] = "{$lineX},{$lineY}";
                            @endphp

                            <rect x="{{ $x }}" y="{{ $yBar }}" width="{{ $barWidth }}" height="{{ $barH }}"
                                  fill="#4299e1" rx="2" opacity="0.85">
                                <title>H{{ $cd['hari_ke'] }} ({{ $cd['tanggal_display'] }}): Rencana {{ $cd['bobot_rencana'] }}%</title>
                            </rect>

                            {{-- Friday Highlight marker on X-axis --}}
                            @if ($cd['is_jumat'])
                                <rect x="{{ $x - 2 }}" y="{{ $padTop + $plotH + 2 }}" width="{{ $barWidth + 4 }}" height="16" fill="#e53e3e" rx="3" />
                                <text x="{{ $lineX }}" y="{{ $padTop + $plotH + 13 }}" font-size="9" font-weight="bold" fill="#ffffff" text-anchor="middle">P</text>
                            @else
                                <text x="{{ $lineX }}" y="{{ $padTop + $plotH + 13 }}" font-size="9" font-weight="500" fill="#718096" text-anchor="middle">
                                    {{ $cd['hari_ke'] }}
                                </text>
                            @endif
                        @endforeach

                        {{-- Polyline S-Curve (Red) --}}
                        <polyline points="{{ implode(' ', $polyPoints) }}" fill="none" stroke="#e53e3e" stroke-width="2.5" stroke-linejoin="round" />

                        {{-- Circle Dots for points --}}
                        @foreach ($chartDays as $i => $cd)
                            @php
                                $x = $padLeft + ($i * ($plotW / max(1, $count))) + 4;
                                $lineX = $x + ($barWidth / 2);
                                $lineY = $padTop + $plotH - ($cd['kumulatif_rencana'] / 100 * $plotH);
                            @endphp
                            <circle cx="{{ $lineX }}" cy="{{ $lineY }}" r="{{ $cd['is_jumat'] ? 4.5 : 3 }}"
                                    fill="{{ $cd['is_jumat'] ? '#e53e3e' : '#ffffff' }}"
                                    stroke="#e53e3e" stroke-width="2">
                                <title>H{{ $cd['hari_ke'] }} Kumulatif: {{ $cd['kumulatif_rencana'] }}%</title>
                            </circle>
                        @endforeach
                    </svg>
                </div>
            </div>

            {{-- Summary Table --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-5 py-3 border-b border-slate-100 font-bold text-sm text-navy-700">
                    Tabel Kumulatif Rencana Kurva S
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 border-b border-slate-200">
                                <th class="px-4 py-2 text-left">Hari Ke</th>
                                <th class="px-4 py-2 text-left">Tanggal Kalender</th>
                                <th class="px-4 py-2 text-center">Status Hari</th>
                                <th class="px-4 py-2 text-right">Bobot Rencana Harian</th>
                                <th class="px-4 py-2 text-right">Kumulatif Rencana</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($kurvaSData['days'] as $day)
                                <tr class="hover:bg-slate-50/50 {{ $day['is_jumat'] ? 'bg-rose-50/30' : '' }}">
                                    <td class="px-4 py-2 font-bold text-slate-700">H-{{ $day['hari_ke'] }}</td>
                                    <td class="px-4 py-2 text-slate-600">{{ $day['tanggal_formatted'] }}</td>
                                    <td class="px-4 py-2 text-center">
                                        @if ($day['is_jumat'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white">
                                                (P) Friday Cutoff
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right font-medium text-slate-700">
                                        {{ number_format($day['bobot_rencana'], 2) }}%
                                    </td>
                                    <td class="px-4 py-2 text-right font-bold {{ $day['is_jumat'] ? 'text-rose-700' : 'text-navy-700' }}">
                                        {{ number_format($day['kumulatif_rencana'], 2) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 4: CETAK & DOKUMEN LEGAL --}}
        <div x-show="activeTab === 'ekspor'" class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3 class="text-base font-bold text-navy-700">Cetak Dokumen Jadwal & Kurva S</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Dokumen resmi ber-Kop Brand, siap cetak atau diunduh sebagai PDF format A4 Landscape.</p>
                </div>

                @can('exportPdf', $jadwal)
                    <a href="{{ route('jadwal-perencanaan.pdf', $jadwal) }}" target="_blank"
                       class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-800 font-bold text-sm px-5 py-2.5 rounded-lg transition shadow">
                        <x-icon name="arrow-down-tray" class="w-4 h-4" />
                        Download PDF Resmi (A4 Landscape)
                    </a>
                @endcan
            </div>

            {{-- Document Preview Box --}}
            <div class="bg-slate-100 p-6 rounded-xl border border-slate-200 flex justify-center">
                <div class="bg-white p-8 rounded-lg shadow-md max-w-4xl w-full border border-slate-300 text-xs space-y-4">
                    {{-- Kop preview --}}
                    <div class="flex items-center justify-between border-b-2 border-black pb-4">
                        <div>
                            <div class="text-base font-black uppercase text-black tracking-wide">{{ $jadwal->brand->name ?? 'BRAND' }}</div>
                            <div class="text-[10px] text-slate-600">{{ $jadwal->brand->address ?? 'Alamat Resmi Brand' }}</div>
                            <div class="text-[10px] text-slate-600">{{ $jadwal->brand->phone ?? '' }} &middot; {{ $jadwal->brand->email ?? '' }}</div>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2.5 py-1 bg-rose-600 text-white font-bold text-[10px] rounded">
                                [P] Friday (Progress)
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-[11px] py-2">
                        <div><strong>Nama Project:</strong> {{ $jadwal->nama_proyek }}</div>
                        <div><strong>Location:</strong> {{ $jadwal->lokasi ?? '-' }}</div>
                        <div><strong>Durasi Proyek:</strong> {{ $jadwal->durasi_hari }} Hari Kalender</div>
                        <div><strong>Status:</strong> {{ strtoupper($jadwal->status) }}</div>
                    </div>

                    <div class="p-4 bg-slate-50 rounded border border-slate-200 text-center text-slate-500 text-xs">
                        Pratinjau lengkap Gantt chart dan Kurva S akan dicetak dalam tata letak A4 Landscape.
                        Klik tombol di atas untuk membuka dokumen PDF resmi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
