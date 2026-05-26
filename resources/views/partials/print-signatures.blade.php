@php
    $sigDir = \App\Models\Utility::get_file('signatures/');
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
            $sigFile = $settings[$slot['key']] ?? \App\Models\Utility::getValByName($slot['key']);
        @endphp
        <div class="{{ $sigBoxClass }}">
            @if(!empty($sigFile))
                <img src="{{ $sigDir . $sigFile }}" alt="{{ $slot['label'] }}" class="{{ $sigImgClass }}">
            @else
                <div class="{{ $sigLineClass }}"></div>
            @endif
            <p>{{ $slot['label'] }}</p>
        </div>
    @endforeach
</div>
