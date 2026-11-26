@if(!empty($isPolos) && $isPolos)
    <div style="height: 64px;"></div>
@elseif(!empty($sig) && !empty($qr))
    <div style="margin: 6px auto;">
        <img src="{{ $qr }}" alt="QR TTD" style="width: 64px; height: 64px; margin: 0 auto; display: block; border: 1px solid #cbd5e1; padding: 2px; background: #fff;">
        <div style="font-size: 7.5pt; color: #166534; font-weight: bold;">Ditandatangani Elektronik</div>
        <div style="font-size: 7pt; color: #475569;">Tgl: {{ $sig->tanggal_ttd->format('d/m/Y') }}</div>
    </div>
@else
    <div style="height: 64px; display: flex; align-items: center; justify-content: center; border: 1px dashed #cbd5e1; margin: 6px 12px; color: #94a3b8; font-size: 8pt; font-style: italic;">
        [ Belum Ditandatangani ]
    </div>
@endif
<div style="font-weight: bold; text-decoration: underline;">{{ $nama }}</div>
<div style="font-size: 9pt;">NIP: {{ $nip ?: '-' }}</div>
