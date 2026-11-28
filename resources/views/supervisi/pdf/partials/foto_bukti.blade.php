@if($jadwal->fotoBuktis->isNotEmpty())
    <div class="page-break"></div>
    <div style="margin-top: 8px; margin-bottom: 12px;">
        <div style="font-weight: bold; font-size: 10pt; background: #e2e8f0; padding: 4px 8px; border: 1px solid #000; margin-bottom: 8px; page-break-after: avoid; break-after: avoid;">
            Lampiran Dokumentasi Foto Bukti Observasi ({{ $jadwal->fotoBuktis->count() }} Foto)
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
            @foreach($jadwal->fotoBuktis->values()->chunk(2) as $chunkIdx => $pair)
                <tr style="page-break-inside: avoid; break-inside: avoid;">
                    @foreach($pair as $colIdx => $foto)
                        @php
                            $fotoNo = ($chunkIdx * 2) + $colIdx + 1;
                        @endphp
                        <td style="width: 50%; border: 1px solid #000; padding: 6px; text-align: center; vertical-align: top;">
                            <img src="{{ $foto->toBase64DataUri() }}"
                                alt="Foto bukti {{ $fotoNo }}{{ $foto->keterangan ? ': ' . $foto->keterangan : '' }}"
                                style="max-width: 100%; height: 155px; object-fit: contain; display: block; margin: 0 auto 5px; border: 1px solid #cbd5e1; background: #ffffff;">
                            <div style="font-weight: bold; color: #0f172a;">
                                Foto {{ $fotoNo }}{{ $foto->keterangan ? ' — ' . $foto->keterangan : '' }}
                            </div>
                        </td>
                    @endforeach
                    @if($pair->count() === 1)
                        <td style="width: 50%; border: 1px solid #000; padding: 6px;"></td>
                    @endif
                </tr>
            @endforeach
        </table>
    </div>
@endif
