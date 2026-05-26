@php
    $printSettings = $settings ?? \App\Models\Utility::settings();
    $sigGridClass = $sigGridClass ?? 'rc-sigs';
    $sigBoxClass  = $sigBoxClass ?? 'rc-sig';
    $sigLineClass = $sigLineClass ?? 'rc-sig-line';
    $sigImgClass  = $sigImgClass ?? 'rc-sig-img';
    $signatureSlots = [
        ['key' => 'signature_cashier', 'label' => __('Cashier Signature')],
        ['key' => 'signature_manager', 'label' => __('Manager Signature')],
        ['key' => 'signature_md', 'label' => __('MD Signature & Seal')],
    ];
@endphp
<div class="{{ $sigGridClass }}">
    @foreach($signatureSlots as $slot)
        @php
            $sigFile = $printSettings[$slot['key']] ?? '';
            $sigUrl  = !empty($sigFile) ? \App\Models\Utility::printFileUrl('signatures', $sigFile) : '';
        @endphp
        <div class="{{ $sigBoxClass }}">
            @if(!empty($sigUrl))
                <img src="{{ $sigUrl }}" alt="{{ $slot['label'] }}" class="{{ $sigImgClass }}">
            @else
                <div class="{{ $sigLineClass }}"></div>
            @endif
            <p>{{ $slot['label'] }}</p>
        </div>
    @endforeach
</div>
