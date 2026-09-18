@if ($invoice->terms->isNotEmpty())
    @php
        $showPct = $invoice->termin_show_pct !== false;

        $paidTerms = $invoice->terms->filter(fn ($t) => $t->is_lunas)->values();
        $lastPaidIndex = -1;
        foreach ($invoice->terms as $i => $t) {
            if ($t->is_lunas) {
                $lastPaidIndex = $i;
            }
        }
        $nextUnpaid = $lastPaidIndex >= 0
            ? $invoice->terms->slice($lastPaidIndex + 1)->first(fn ($t) => ! $t->is_lunas)
            : null;

        $joinLabelsId = function ($items) {
            $labels = $items->pluck('label')->all();
            if (count($labels) === 1) {
                return $labels[0];
            }
            $last = array_pop($labels);
            return implode(', ', $labels).' dan '.$last;
        };
    @endphp

    <div>
        <h3 class="text-sm font-semibold text-navy-600 mb-2">Termin Pembayaran</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <th class="text-left px-5 py-2.5">Label</th>
                    @if ($showPct)
                        <th class="text-right px-5 py-2.5">Persen</th>
                    @endif
                    <th class="text-right px-5 py-2.5">Nominal</th>
                    <th class="text-left px-5 py-2.5">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($invoice->terms as $term)
                    <tr class="{{ $term->is_lunas ? 'bg-emerald-50/50' : '' }}">
                        <td class="px-5 py-3 {{ $term->is_lunas ? 'text-slate-400 line-through' : '' }}">
                            {{ $term->is_lunas ? '✓ ' : '' }}{{ $term->label }}
                            @if ($term->catatan)
                                <div class="text-[11px] text-slate-400 no-underline whitespace-pre-line">{{ $term->catatan }}</div>
                            @endif
                            @if ($term->is_lunas)
                                <div class="text-[11px] italic text-emerald-600 no-underline">
                                    {{ $term->tanggal_lunas ? 'Dibayar: '.$term->tanggal_lunas->format('d M Y') : 'Sudah Dibayar' }}
                                </div>
                            @endif
                        </td>
                        @if ($showPct)
                            <td class="px-5 py-3 text-right {{ $term->is_lunas ? 'text-slate-400 line-through' : '' }}">{{ rtrim(rtrim($term->persen, '0'), '.') }}%</td>
                        @endif
                        <td class="px-5 py-3 text-right font-medium {{ $term->is_lunas ? 'text-slate-400 line-through' : '' }}">Rp {{ number_format($term->nominal, 0, ',', '.') }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $term->is_lunas ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $term->is_lunas ? 'Lunas' : 'Menunggu' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($paidTerms->isNotEmpty())
            <div class="mt-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-700 italic leading-relaxed">
                @if ($nextUnpaid)
                    ✓ Terima kasih telah melakukan pembayaran {{ $joinLabelsId($paidTerms) }}, untuk selanjutnya bisa dibantu untuk melakukan pembayaran {{ $nextUnpaid->label }} dengan total <strong>Rp {{ number_format($nextUnpaid->nominal, 0, ',', '.') }}</strong>.
                @else
                    ✓ Terima kasih, seluruh pembayaran termin sudah lunas.
                @endif
            </div>
        @endif
    </div>
@endif
