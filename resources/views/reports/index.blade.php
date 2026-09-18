@push('head-scripts')
    @vite(['resources/js/charts.js'])
@endpush

@php
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    $tabs = [
        'bulanan' => 'Bulanan',
        'tahunan' => 'Tahunan',
        'brand' => 'Per Brand',
        'leads' => 'Leads',
    ];

    if (config('features.drafter_tasks')) {
        $tabs['kpi'] = 'KPI Drafter';
    }
    $navy = '#1a365d';
    $gold = '#c9a227';
    $slate300 = '#cbd5e1';
    $slate400 = '#94a3b8';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-navy-600">Rekap &amp; Grafik</h2>
    </x-slot>

    <div class="flex items-center gap-1 border-b border-slate-200 mb-6">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('reports.index', ['tab' => $key, 'year' => $year]) }}"
               class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition
                      {{ $tab === $key ? 'border-navy-600 text-navy-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if (in_array($tab, ['bulanan', 'leads', 'kpi']))
        <form method="GET" class="mb-6 flex items-center gap-2 flex-wrap">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if ($tab === 'kpi')
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Bulan</label>
                <select name="month" onchange="this.form.submit()"
                        class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach ($months as $i => $m)
                        <option value="{{ $i + 1 }}" @selected(($i + 1) == $month)>{{ $m }}</option>
                    @endforeach
                </select>
            @endif
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Tahun</label>
            <select name="year" onchange="this.form.submit()"
                    class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                @foreach (range(now()->year, now()->year - 4) as $y)
                    <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    @endif

    @if ($tab === 'bulanan')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-navy-600 mb-4">Pendapatan Bulanan ({{ $year }})</h3>
                <div x-data="{}" x-init="new Chart($refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: @json($months),
                        datasets: [{ label: 'Pendapatan', data: @json($data['monthlyRevenue']), backgroundColor: '{{ $navy }}' }]
                    },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                })">
                    <canvas x-ref="canvas" height="220"></canvas>
                </div>
                <table class="w-full text-xs mt-4">
                    <thead><tr class="text-slate-400"><th class="text-left py-1">Bulan</th><th class="text-right py-1">Pendapatan</th></tr></thead>
                    <tbody>
                        @foreach ($months as $i => $m)
                            <tr class="border-t border-slate-100">
                                <td class="py-1">{{ $m }}</td>
                                <td class="py-1 text-right">Rp {{ number_format($data['monthlyRevenue'][$i], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-navy-600 mb-4">Deal Leads Bulanan ({{ $year }})</h3>
                <div x-data="{}" x-init="new Chart($refs.canvas, {
                    type: 'line',
                    data: {
                        labels: @json($months),
                        datasets: [{ label: 'Deal', data: @json($data['monthlyLeadsDeals']), borderColor: '{{ $gold }}', backgroundColor: '{{ $gold }}', tension: 0.3 }]
                    },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                })">
                    <canvas x-ref="canvas" height="220"></canvas>
                </div>
                <table class="w-full text-xs mt-4">
                    <thead><tr class="text-slate-400"><th class="text-left py-1">Bulan</th><th class="text-right py-1">Deal</th></tr></thead>
                    <tbody>
                        @foreach ($months as $i => $m)
                            <tr class="border-t border-slate-100">
                                <td class="py-1">{{ $m }}</td>
                                <td class="py-1 text-right">{{ $data['monthlyLeadsDeals'][$i] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tab === 'tahunan')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-navy-600 mb-4">Pendapatan per Tahun</h3>
                <div x-data="{}" x-init="new Chart($refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: @json($data['yearlyTotals']->pluck('year')),
                        datasets: [{ label: 'Pendapatan', data: @json($data['yearlyTotals']->pluck('revenue')), backgroundColor: '{{ $navy }}' }]
                    },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                })">
                    <canvas x-ref="canvas" height="220"></canvas>
                </div>
                <table class="w-full text-xs mt-4">
                    <thead><tr class="text-slate-400"><th class="text-left py-1">Tahun</th><th class="text-right py-1">Pendapatan</th><th class="text-right py-1">Invoice Lunas</th></tr></thead>
                    <tbody>
                        @foreach ($data['yearlyTotals'] as $row)
                            <tr class="border-t border-slate-100">
                                <td class="py-1">{{ $row['year'] }}</td>
                                <td class="py-1 text-right">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                                <td class="py-1 text-right">{{ $row['invoice_count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-navy-600 mb-4">Deal per Tahun</h3>
                <div x-data="{}" x-init="new Chart($refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: @json($data['yearlyTotals']->pluck('year')),
                        datasets: [{ label: 'Deal', data: @json($data['yearlyTotals']->pluck('deal_count')), backgroundColor: '{{ $gold }}' }]
                    },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                })">
                    <canvas x-ref="canvas" height="220"></canvas>
                </div>
                <table class="w-full text-xs mt-4">
                    <thead><tr class="text-slate-400"><th class="text-left py-1">Tahun</th><th class="text-right py-1">Leads</th><th class="text-right py-1">Deal</th></tr></thead>
                    <tbody>
                        @foreach ($data['yearlyTotals'] as $row)
                            <tr class="border-t border-slate-100">
                                <td class="py-1">{{ $row['year'] }}</td>
                                <td class="py-1 text-right">{{ $row['lead_count'] }}</td>
                                <td class="py-1 text-right">{{ $row['deal_count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tab === 'brand')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-navy-600 mb-4">Pendapatan per Brand</h3>
                <div x-data="{}" x-init="new Chart($refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: @json($data['revenueByBrand']->pluck('brand')),
                        datasets: [{ label: 'Pendapatan', data: @json($data['revenueByBrand']->pluck('total')), backgroundColor: '{{ $navy }}' }]
                    },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                })">
                    <canvas x-ref="canvas" height="220"></canvas>
                </div>
                <table class="w-full text-xs mt-4">
                    <thead><tr class="text-slate-400"><th class="text-left py-1">Brand</th><th class="text-right py-1">Pendapatan</th></tr></thead>
                    <tbody>
                        @forelse ($data['revenueByBrand'] as $row)
                            <tr class="border-t border-slate-100">
                                <td class="py-1">{{ $row['brand'] }}</td>
                                <td class="py-1 text-right">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-3 text-center text-slate-400">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-navy-600 mb-4">Leads per Brand</h3>
                <div x-data="{}" x-init="new Chart($refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: @json($data['leadsByBrand']->pluck('brand')),
                        datasets: [{ label: 'Leads', data: @json($data['leadsByBrand']->pluck('total')), backgroundColor: '{{ $gold }}' }]
                    },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                })">
                    <canvas x-ref="canvas" height="220"></canvas>
                </div>
                <table class="w-full text-xs mt-4">
                    <thead><tr class="text-slate-400"><th class="text-left py-1">Brand</th><th class="text-right py-1">Leads</th></tr></thead>
                    <tbody>
                        @forelse ($data['leadsByBrand'] as $row)
                            <tr class="border-t border-slate-100">
                                <td class="py-1">{{ $row['brand'] }}</td>
                                <td class="py-1 text-right">{{ $row['total'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-3 text-center text-slate-400">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tab === 'leads')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-navy-600 mb-4">Funnel Status Leads</h3>
                <div x-data="{}" x-init="new Chart($refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: @json($data['leadsByStatus']->pluck('label')),
                        datasets: [{
                            label: 'Leads',
                            data: @json($data['leadsByStatus']->pluck('total')),
                            backgroundColor: @json($data['leadsByStatus']->pluck('status'))
                                .map(s => s === 'deal' ? '{{ $gold }}' : '{{ $slate300 }}')
                        }]
                    },
                    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
                })">
                    <canvas x-ref="canvas" height="220"></canvas>
                </div>
                <table class="w-full text-xs mt-4">
                    <thead><tr class="text-slate-400"><th class="text-left py-1">Status</th><th class="text-right py-1">Jumlah</th></tr></thead>
                    <tbody>
                        @foreach ($data['leadsByStatus'] as $row)
                            <tr class="border-t border-slate-100">
                                <td class="py-1">{{ $row['label'] }}</td>
                                <td class="py-1 text-right">{{ $row['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-navy-600 mb-4">Leads per Sumber</h3>
                <div x-data="{}" x-init="new Chart($refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: @json($data['leadsBySumber']->pluck('label')),
                        datasets: [{ label: 'Leads', data: @json($data['leadsBySumber']->pluck('total')), backgroundColor: '{{ $navy }}' }]
                    },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                })">
                    <canvas x-ref="canvas" height="220"></canvas>
                </div>
                <table class="w-full text-xs mt-4">
                    <thead><tr class="text-slate-400"><th class="text-left py-1">Sumber</th><th class="text-right py-1">Jumlah</th></tr></thead>
                    <tbody>
                        @foreach ($data['leadsBySumber'] as $row)
                            <tr class="border-t border-slate-100">
                                <td class="py-1">{{ $row['label'] }}</td>
                                <td class="py-1 text-right">{{ $row['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-navy-600 mb-4">Tren Leads Bulanan ({{ $year }})</h3>
            <div x-data="{}" x-init="new Chart($refs.canvas, {
                type: 'line',
                data: {
                    labels: @json($months),
                    datasets: [{ label: 'Leads', data: @json($data['leadsMonthlyTrend']), borderColor: '{{ $gold }}', backgroundColor: '{{ $gold }}', tension: 0.3 }]
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            })">
                <canvas x-ref="canvas" height="140"></canvas>
            </div>
            <table class="w-full text-xs mt-4">
                <thead><tr class="text-slate-400"><th class="text-left py-1">Bulan</th><th class="text-right py-1">Leads</th></tr></thead>
                <tbody>
                    @foreach ($months as $i => $m)
                        <tr class="border-t border-slate-100">
                            <td class="py-1">{{ $m }}</td>
                            <td class="py-1 text-right">{{ $data['leadsMonthlyTrend'][$i] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($tab === 'kpi')
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-navy-600">KPI Drafter &mdash; {{ $months[$month - 1] }} {{ $year }}</h3>
                <a href="{{ route('reports.kpi.pdf', ['year' => $year, 'month' => $month]) }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                    <x-icon name="document-list" class="w-4 h-4" />
                    Cetak PDF
                </a>
            </div>

            <table class="w-full text-xs">
                <thead>
                    <tr class="text-slate-400">
                        <th class="text-left py-2">Drafter</th>
                        <th class="text-right py-2">Total Selesai</th>
                        <th class="text-right py-2">Tepat Waktu</th>
                        <th class="text-right py-2">Terlambat</th>
                        <th class="text-right py-2">Tanpa Deadline</th>
                        <th class="text-right py-2">% Tepat Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data['drafterKpi'] as $row)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-semibold text-slate-700">{{ $row['drafter']->name ?? '-' }}</td>
                            <td class="py-2 text-right">{{ $row['total_selesai'] }}</td>
                            <td class="py-2 text-right text-emerald-600">{{ $row['tepat_waktu'] }}</td>
                            <td class="py-2 text-right text-red-600">{{ $row['terlambat'] }}</td>
                            <td class="py-2 text-right text-slate-400">{{ $row['tanpa_deadline'] }}</td>
                            <td class="py-2 text-right font-semibold">
                                {{ $row['persen_tepat_waktu'] !== null ? $row['persen_tepat_waktu'].'%' : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-slate-400">Belum ada tugas selesai pada bulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>
