@props([
    'government_name' => null,
    'institution_name' => null,
    'school_name' => null,
    'address' => null,
    'postal_code' => null,
    'phone' => null,
    'email' => null,
    'website' => null,
    'border_style' => 'double',
    'is_active' => true,
    'logo_left_url' => null,
    'logo_right_url' => null,
    'logo_left_base64' => null,
    'logo_right_base64' => null,
    'contact_lines' => [],
    'useBase64' => false,
])

@php
    // Jika props tidak diisi manual, ambil default dari LetterheadService
    if (empty($school_name) && empty($government_name)) {
        $data = app(\App\Services\LetterheadService::class)->getData();
        $government_name = $government_name ?? $data['government_name'];
        $institution_name = $institution_name ?? $data['institution_name'];
        $school_name = $school_name ?? $data['school_name'];
        $address = $address ?? $data['address'];
        $postal_code = $postal_code ?? $data['postal_code'];
        $phone = $phone ?? $data['phone'];
        $email = $email ?? $data['email'];
        $website = $website ?? $data['website'];
        $border_style = $border_style ?? $data['border_style'];
        $is_active = $is_active ?? $data['is_active'];
        $logo_left_url = $logo_left_url ?? $data['logo_left_url'];
        $logo_right_url = $logo_right_url ?? $data['logo_right_url'];
        $logo_left_base64 = $logo_left_base64 ?? $data['logo_left_base64'];
        $logo_right_base64 = $logo_right_base64 ?? $data['logo_right_base64'];
        $contact_lines = !empty($contact_lines) ? $contact_lines : $data['contact_lines'];
    }

    $logoLeftSrc = $useBase64 
        ? ($logo_left_base64 ?: $logo_left_url) 
        : ($logo_left_url ?: $logo_left_base64);

    $logoRightSrc = $useBase64 
        ? ($logo_right_base64 ?: $logo_right_url) 
        : ($logo_right_url ?: $logo_right_base64);

    $hasLogoLeft = !empty($logoLeftSrc);
    $hasLogoRight = !empty($logoRightSrc);
@endphp

@if($is_active)
<div class="kop-surat-container" style="width: 100%; font-family: 'Times New Roman', Times, serif; color: #000; margin-bottom: 16px;">
    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
        <tr>
            {{-- Kolom Logo Kiri --}}
            @if($hasLogoLeft)
                <td style="width: 90px; vertical-align: middle; text-align: center; padding: 0 10px 0 0;">
                    <img src="{{ $logoLeftSrc }}" alt="Logo Kiri" style="width: 75px; height: 75px; object-fit: contain; display: block; margin: 0 auto;">
                </td>
            @elseif($hasLogoRight)
                {{-- Balancer jika hanya ada logo kanan agar teks tetap di tengah --}}
                <td style="width: 90px; padding: 0;"></td>
            @endif

            {{-- Kolom Teks Tengah --}}
            <td style="vertical-align: middle; text-align: center; padding: 0 4px; line-height: 1.25;">
                @if(!empty($government_name))
                    <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">
                        {{ $government_name }}
                    </div>
                @endif

                @if(!empty($institution_name))
                    <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin: 1px 0 0 0;">
                        {{ $institution_name }}
                    </div>
                @endif

                @if(!empty($school_name))
                    <div style="font-size: 14pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; margin: 3px 0 0 0;">
                        {{ $school_name }}
                    </div>
                @endif

                @if(!empty($contact_lines))
                    <div style="margin-top: 4px; line-height: 1.3;">
                        @foreach($contact_lines as $line)
                            <div style="font-size: 9pt; font-weight: normal; color: #222; margin: 0;">
                                {{ $line }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </td>

            {{-- Kolom Logo Kanan --}}
            @if($hasLogoRight)
                <td style="width: 90px; vertical-align: middle; text-align: center; padding: 0 0 0 10px;">
                    <img src="{{ $logoRightSrc }}" alt="Logo Kanan" style="width: 75px; height: 75px; object-fit: contain; display: block; margin: 0 auto;">
                </td>
            @elseif($hasLogoLeft)
                {{-- Balancer jika hanya ada logo kiri agar teks tetap simetris di tengah --}}
                <td style="width: 90px; padding: 0;"></td>
            @endif
        </tr>
    </table>

    {{-- Garis Pembatas Kop Surat --}}
    @if($border_style === 'double')
        <div style="border-top: 2.5px solid #000; margin-top: 8px;"></div>
        <div style="border-top: 1px solid #000; margin-top: 2px;"></div>
    @elseif($border_style === 'single')
        <div style="border-top: 2px solid #000; margin-top: 8px;"></div>
    @endif
</div>
@endif
