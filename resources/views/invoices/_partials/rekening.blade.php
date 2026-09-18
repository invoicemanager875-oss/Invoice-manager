@if (! empty($invoice->rekening_config))
    <div>
        <h3 style="font-size:13px;font-weight:700;color:#1a365d;margin:0 0 8px">Rekening Pembayaran</h3>
        <div style="font-size:13px;color:#334155">
            @foreach ($invoice->rekening_config as $rek)
                <div style="margin-bottom:4px"><span style="color:#64748b">{{ $rek['bank'] ?? '' }}</span> — <strong>{{ $rek['norek'] ?? '' }}</strong> a/n {{ $rek['nama'] ?? '' }}</div>
            @endforeach
        </div>
    </div>
@endif
