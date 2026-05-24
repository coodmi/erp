@php
    $settings_data = \App\Models\Utility::settingsById($bill->created_by);
    $accentHex = ltrim($color, '#');
    $r = hexdec(substr($accentHex,0,2));
    $g = hexdec(substr($accentHex,2,2));
    $b = hexdec(substr($accentHex,4,2));
    $lum = (0.299*$r + 0.587*$g + 0.114*$b) / 255;
    $onAccent    = ($lum > 0.55) ? '#1e293b' : '#ffffff';
    $accentFull  = '#' . $accentHex;
    $accentLight = 'rgba(' . $r . ',' . $g . ',' . $b . ',0.08)';
    $accentMid   = 'rgba(' . $r . ',' . $g . ',' . $b . ',0.18)';
@endphp
<!DOCTYPE html>
<html lang="en" dir="{{ $settings_data['SITE_RTL'] == 'on' ? 'rtl' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent:       {{ $accentFull }};
            --on-accent:    {{ $onAccent }};
            --accent-light: {{ $accentLight }};
            --accent-mid:   {{ $accentMid }};
            --text-dark:    #0f172a;
            --text-mid:     #475569;
            --text-light:   #94a3b8;
            --border:       #e2e8f0;
            --bg:           #f8fafc;
            --white:        #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-dark);
            font-size: 13px;
            line-height: 1.6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .doc-wrap {
            max-width: 780px;
            margin: 24px auto;
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 32px rgba(15,23,42,.10);
        }

        /* ── Header ── */
        .doc-header { background: var(--accent); color: var(--on-accent); padding: 36px 40px 28px; }
        .doc-header-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; }
        .doc-logo { max-width: 160px; max-height: 60px; object-fit: contain; }
        .doc-title { font-size: 36px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; opacity: .95; text-align: right; }
        .doc-header-bottom { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 24px; gap: 20px; }
        .doc-from p { font-size: 12.5px; opacity: .88; line-height: 1.7; }
        .doc-from strong { font-size: 14px; font-weight: 700; display: block; margin-bottom: 4px; }
        .doc-meta-table { border-collapse: collapse; min-width: 220px; }
        .doc-meta-table td { padding: 3px 0; font-size: 12.5px; opacity: .88; }
        .doc-meta-table td:first-child { padding-right: 16px; white-space: nowrap; font-weight: 500; }
        .doc-meta-table td:last-child  { text-align: right; font-weight: 600; }
        .doc-qr { background: rgba(255,255,255,.15); border-radius: 10px; padding: 8px; width: 80px; height: 80px; flex-shrink: 0; }
        .doc-qr img { width: 100%; height: 100%; }

        /* ── Body ── */
        .doc-body { padding: 32px 40px; }

        .doc-addresses { display: flex; gap: 20px; margin-bottom: 28px; }
        .doc-address-box { flex: 1; background: var(--accent-light); border: 1px solid var(--accent-mid); border-radius: 12px; padding: 16px 18px; }
        .doc-address-box .addr-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: var(--accent); margin-bottom: 8px; }
        .doc-address-box p { font-size: 12.5px; color: var(--text-mid); line-height: 1.7; }
        .doc-address-box strong { font-size: 13.5px; font-weight: 700; color: var(--text-dark); display: block; margin-bottom: 3px; }

        /* ── Items Table ── */
        .doc-table { width: 100%; border-collapse: collapse; }
        .doc-table thead tr { background: var(--accent); color: var(--on-accent); }
        .doc-table thead th { padding: 11px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; white-space: nowrap; }
        .doc-table thead th:first-child { border-radius: 8px 0 0 0; }
        .doc-table thead th:last-child  { border-radius: 0 8px 0 0; text-align: right; }
        .doc-table tbody tr { border-bottom: 1px solid var(--border); }
        .doc-table tbody tr:hover { background: var(--accent-light); }
        .doc-table tbody td { padding: 11px 14px; font-size: 12.5px; color: var(--text-mid); vertical-align: top; }
        .doc-table tbody td:first-child { color: var(--text-dark); font-weight: 500; }
        .doc-table tbody td:last-child  { text-align: right; font-weight: 600; color: var(--text-dark); }
        .doc-table .item-desc td { padding-top: 0; padding-bottom: 10px; font-size: 11.5px; color: var(--text-light); font-style: italic; border-bottom: none; }
        .doc-table tfoot tr:first-child td { border-top: 2px solid var(--accent); padding-top: 10px; }
        .doc-table tfoot td { padding: 8px 14px; font-size: 12.5px; font-weight: 600; color: var(--text-mid); }
        .doc-table tfoot td:last-child { text-align: right; color: var(--text-dark); }

        /* ── Totals ── */
        .doc-totals-wrap { display: flex; justify-content: flex-end; border-top: 1px solid var(--border); }
        .doc-totals { width: 280px; padding: 20px 0 0; }
        .doc-totals-row { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; font-size: 12.5px; color: var(--text-mid); border-bottom: 1px dashed var(--border); }
        .doc-totals-row:last-child { border-bottom: none; }
        .doc-totals-row span:last-child { font-weight: 600; color: var(--text-dark); }
        .doc-totals-row.grand { margin-top: 8px; padding: 10px 14px; background: var(--accent); color: var(--on-accent); border-radius: 10px; font-size: 14px; font-weight: 800; border-bottom: none; }
        .doc-totals-row.grand span:last-child { color: var(--on-accent); font-weight: 800; }
        .doc-totals-row.due { padding: 8px 14px; background: var(--accent-light); border: 1px solid var(--accent-mid); border-radius: 8px; margin-top: 6px; font-weight: 700; color: var(--accent); border-bottom: none; }
        .doc-totals-row.due span:last-child { color: var(--accent); }

        /* ── Footer ── */
        .doc-footer { margin-top: 32px; padding: 20px 24px; background: var(--accent-light); border: 1px solid var(--accent-mid); border-radius: 12px; font-size: 12px; color: var(--text-mid); }
        .doc-footer strong { display: block; font-size: 13px; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }

        /* ── RTL ── */
        html[dir="rtl"] .doc-title { text-align: left; }
        html[dir="rtl"] .doc-meta-table td:last-child { text-align: left; }
        html[dir="rtl"] .doc-table thead th:first-child { border-radius: 0 8px 0 0; }
        html[dir="rtl"] .doc-table thead th:last-child  { border-radius: 8px 0 0 0; text-align: left; }
        html[dir="rtl"] .doc-table tbody td:last-child  { text-align: left; }
        html[dir="rtl"] .doc-table tfoot td:last-child  { text-align: left; }
        html[dir="rtl"] .doc-totals { margin-left: 0; margin-right: auto; }

        @media print {
            body { background: white; }
            .doc-wrap { box-shadow: none; margin: 0; border-radius: 0; }
        }
    </style>
    @if($settings_data['SITE_RTL'] == 'on')
        <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
    @endif
</head>
<body>
<div class="doc-wrap" id="boxes">

    {{-- ── Header ── --}}
    <div class="doc-header">
        <div class="doc-header-top">
            <img class="doc-logo" src="{{ $img }}" alt="Logo">
            <div class="doc-title">{{ __('BILL') }}</div>
        </div>
        <div class="doc-header-bottom">
            <div class="doc-from">
                <strong>@if($settings['company_name']){{ $settings['company_name'] }}@endif</strong>
                <p>
                    @if($settings['mail_from_address']){{ $settings['mail_from_address'] }}<br>@endif
                    @if($settings['company_address']){{ $settings['company_address'] }}@endif
                    @if($settings['company_city']), {{ $settings['company_city'] }}@endif
                    @if($settings['company_state']) {{ $settings['company_state'] }}@endif
                    @if($settings['company_zipcode']) – {{ $settings['company_zipcode'] }}@endif
                    @if($settings['company_country'])<br>{{ $settings['company_country'] }}@endif
                    @if($settings['company_telephone'])<br>{{ $settings['company_telephone'] }}@endif
                    @if(!empty($settings['registration_number']))<br>{{ __('Reg No') }}: {{ $settings['registration_number'] }}@endif
                    @if($settings['vat_gst_number_switch'] == 'on' && !empty($settings['tax_type']) && !empty($settings['vat_number']))<br>{{ $settings['tax_type'] }} {{ __('No') }}: {{ $settings['vat_number'] }}@endif
                </p>
            </div>
            <div style="display:flex;align-items:flex-end;gap:16px;">
                <table class="doc-meta-table">
                    <tr>
                        <td>{{ __('Bill No') }}:</td>
                        <td>{{ Utility::billNumberFormat($settings, $bill->bill_id) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Bill Date') }}:</td>
                        <td>{{ Utility::dateFormat($settings, $bill->issue_date) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Due Date') }}:</td>
                        <td>{{ Utility::dateFormat($settings, $bill->due_date) }}</td>
                    </tr>
                    @if(!empty($customFields) && count($bill->customField) > 0)
                        @foreach($customFields as $field)
                            <tr>
                                <td>{{ $field->name }}:</td>
                                <td>{{ !empty($bill->customField) ? $bill->customField[$field->id] : '-' }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
                <div class="doc-qr">
                    {!! DNS2D::getBarcodeHTML(route('bill.link.copy', \Crypt::encrypt($bill->bill_id)), "QRCODE", 2, 2) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- ── Body ── --}}
    <div class="doc-body">

        {{-- Addresses --}}
        <div class="doc-addresses">
            <div class="doc-address-box">
                <div class="addr-label">{{ __('Bill To') }}</div>
                @if(!empty($vendor->billing_name))
                    <strong>{{ $vendor->billing_name }}</strong>
                    <p>
                        @if($vendor->billing_address){{ $vendor->billing_address }}<br>@endif
                        @if($vendor->billing_city){{ $vendor->billing_city }}@endif
                        @if($vendor->billing_state), {{ $vendor->billing_state }}@endif
                        @if($vendor->billing_zip) {{ $vendor->billing_zip }}@endif
                        @if($vendor->billing_country)<br>{{ $vendor->billing_country }}@endif
                        @if($vendor->billing_phone)<br>{{ $vendor->billing_phone }}@endif
                    </p>
                @else
                    <p>—</p>
                @endif
            </div>
            @if($settings['shipping_display'] == 'on')
            <div class="doc-address-box">
                <div class="addr-label">{{ __('Ship To') }}</div>
                @if(!empty($vendor->shipping_name))
                    <strong>{{ $vendor->shipping_name }}</strong>
                    <p>
                        @if($vendor->shipping_address){{ $vendor->shipping_address }}<br>@endif
                        @if($vendor->shipping_city){{ $vendor->shipping_city }}@endif
                        @if($vendor->shipping_state), {{ $vendor->shipping_state }}@endif
                        @if($vendor->shipping_zip) {{ $vendor->shipping_zip }}@endif
                        @if($vendor->shipping_country)<br>{{ $vendor->shipping_country }}@endif
                        @if($vendor->shipping_phone)<br>{{ $vendor->shipping_phone }}@endif
                    </p>
                @else
                    <p>—</p>
                @endif
            </div>
            @endif
        </div>

        {{-- Items Table --}}
        <table class="doc-table">
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th>{{ __('Qty') }}</th>
                    <th>{{ __('Rate') }}</th>
                    <th>{{ __('Discount') }}</th>
                    <th>{{ __('Tax') }}</th>
                    <th>{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($bill->itemData) && count($bill->itemData) > 0)
                    @foreach($bill->itemData as $item)
                        @php
                            $unitName = App\Models\ProductServiceUnit::find($item->unit);
                            $itemtax  = 0;
                        @endphp
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->quantity }}{{ $unitName ? ' ('.$unitName->name.')' : '' }}</td>
                            <td>{{ Utility::priceFormat($settings, $item->price) }}</td>
                            <td>{{ $item->discount != 0 ? Utility::priceFormat($settings, $item->discount) : '—' }}</td>
                            <td>
                                @if(!empty($item->itemTax))
                                    @foreach($item->itemTax as $taxes)
                                        @php $itemtax += $taxes['tax_price']; @endphp
                                        <div>{{ $taxes['name'] }} ({{ $taxes['rate'] }}) {{ $taxes['price'] }}</div>
                                    @endforeach
                                @else —
                                @endif
                            </td>
                            <td>{{ Utility::priceFormat($settings, $item->price * $item->quantity - $item->discount + $itemtax) }}</td>
                        </tr>
                        @if(!empty($item->description))
                            <tr class="item-desc">
                                <td colspan="6">{{ $item->description }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td>{{ __('Totals') }}</td>
                    <td>{{ $bill->totalQuantity }}</td>
                    <td>{{ Utility::priceFormat($settings, $bill->totalRate) }}</td>
                    <td>{{ Utility::priceFormat($settings, $bill->totalDiscount) }}</td>
                    <td>{{ Utility::priceFormat($settings, $bill->totalTaxPrice) }}</td>
                    <td>{{ Utility::priceFormat($settings, $bill->getSubTotal()) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Totals Summary --}}
        <div class="doc-totals-wrap">
            <div class="doc-totals">
                <div class="doc-totals-row">
                    <span>{{ __('Subtotal') }}</span>
                    <span>{{ Utility::priceFormat($settings, $bill->getSubTotal()) }}</span>
                </div>
                @if($bill->getTotalDiscount())
                <div class="doc-totals-row">
                    <span>{{ __('Discount') }}</span>
                    <span>– {{ Utility::priceFormat($settings, $bill->getTotalDiscount()) }}</span>
                </div>
                @endif
                @if(!empty($bill->taxesData))
                    @foreach($bill->taxesData as $taxName => $taxPrice)
                    <div class="doc-totals-row">
                        <span>{{ $taxName }}</span>
                        <span>{{ Utility::priceFormat($settings, $taxPrice) }}</span>
                    </div>
                    @endforeach
                @endif
                <div class="doc-totals-row grand">
                    <span>{{ __('Total') }}</span>
                    <span>{{ Utility::priceFormat($settings, $bill->getSubTotal() - $bill->getTotalDiscount() + $bill->getTotalTax()) }}</span>
                </div>
                <div class="doc-totals-row">
                    <span>{{ __('Paid') }}</span>
                    <span>{{ Utility::priceFormat($settings, ($bill->getTotal() - $bill->getDue()) - $bill->billTotalDebitNote()) }}</span>
                </div>
                @if($bill->billTotalDebitNote())
                <div class="doc-totals-row">
                    <span>{{ __('Debit Note') }}</span>
                    <span>{{ Utility::priceFormat($settings, $bill->billTotalDebitNote()) }}</span>
                </div>
                @endif
                <div class="doc-totals-row due">
                    <span>{{ __('Amount Due') }}</span>
                    <span>{{ Utility::priceFormat($settings, $bill->getDue()) }}</span>
                </div>
            </div>
        </div>

        {{-- Footer Notes --}}
        @if($settings['footer_title'] || $settings['footer_notes'])
        <div class="doc-footer">
            @if($settings['footer_title'])<strong>{{ $settings['footer_title'] }}</strong>@endif
            {!! $settings['footer_notes'] !!}
        </div>
        @endif

    </div>
</div>

@if(!isset($preview))
    @include('bill.script')
@endif
</body>
</html>
