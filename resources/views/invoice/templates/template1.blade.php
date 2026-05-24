@php
    $settings_data = \App\Models\Utility::settingsById($invoice->created_by);
    $hex = ltrim($color, '#');
    if(strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
    $lum = (0.299*$r + 0.587*$g + 0.114*$b)/255;
    $onAccent = $lum > 0.6 ? '#1e293b' : '#ffffff';
    $accent   = '#'.$hex;
    $rgb      = "$r,$g,$b";
    // fallback: if white/near-white, use dark slate for header
    $isLight  = $lum > 0.85;
    $headerBg = $isLight ? '#1e293b' : $accent;
    $headerFg = '#ffffff';
@endphp
<!DOCTYPE html>
<html lang="en" dir="{{ $settings_data['SITE_RTL']=='on'?'rtl':'' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --accent:{{ $accent }};
  --accent-rgb:{{ $rgb }};
  --hbg:{{ $headerBg }};
  --hfg:{{ $headerFg }};
  --a10:rgba({{ $rgb }},.08);
  --a20:rgba({{ $rgb }},.16);
  --text:#0f172a;--t2:#475569;--t3:#94a3b8;
  --bdr:#e2e8f0;--bg:#f1f5f9;--white:#fff;
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:13px;line-height:1.6;-webkit-print-color-adjust:exact;print-color-adjust:exact}

.inv-wrap{max-width:760px;margin:24px auto;background:var(--white);border-radius:16px;overflow:hidden;box-shadow:0 4px 32px rgba(15,23,42,.13)}

/* ── accent top bar ── */
.inv-topbar{height:6px;background:var(--accent)}

/* ── header ── */
.inv-header{background:var(--hbg);padding:32px 36px 28px;position:relative;overflow:hidden}
.inv-header::after{content:'';position:absolute;right:-40px;top:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.05);pointer-events:none}

/* header layout: left = logo+qr, right = title+meta */
.inv-header-inner{display:flex;justify-content:space-between;align-items:stretch;gap:20px}

/* LEFT: logo stacked above qr */
.inv-head-left{display:flex;flex-direction:column;align-items:flex-start;gap:14px;flex-shrink:0}
.inv-logo{max-height:72px;max-width:200px;object-fit:contain;display:block}
.inv-qr-box{width:52px;height:52px;border-radius:8px;padding:4px;flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden}
.inv-qr-box img,.inv-qr-box svg,.inv-qr-box table{width:100%!important;height:100%!important;max-width:44px;max-height:44px}
/* white bg = dark qr, colored bg = white qr via invert */
.inv-qr-box.qr-invert{background:rgba(255,255,255,.15);filter:invert(1) brightness(2)}
.inv-qr-box.qr-normal{background:rgba(255,255,255,.95)}
.inv-company-name{font-size:13.5px;font-weight:700;color:var(--hfg);margin-bottom:4px}
.inv-company-detail{font-size:11px;color:rgba(255,255,255,.7);line-height:1.8}

/* RIGHT: title + meta */
.inv-head-right{display:flex;flex-direction:column;align-items:flex-end;justify-content:space-between;gap:16px}
.inv-doc-title{font-size:26px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;color:var(--hfg);opacity:.95;line-height:1;text-align:right}
.inv-meta{border-collapse:collapse}
.inv-meta td{padding:2px 0;font-size:11px;color:rgba(255,255,255,.8)}
.inv-meta td:first-child{padding-right:14px;white-space:nowrap;font-weight:500}
.inv-meta td:last-child{text-align:right;font-weight:700;color:var(--hfg)}

/* ── body ── */
.inv-body{padding:28px 36px 36px}

/* address row */
.inv-addr-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px}
.inv-addr-card{background:var(--a10);border:1px solid var(--a20);border-radius:12px;padding:16px 18px}
.inv-addr-tag{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);margin-bottom:8px;display:flex;align-items:center;gap:5px}
.inv-addr-tag::before{content:'';width:14px;height:2px;background:var(--accent);border-radius:2px;flex-shrink:0}
.inv-addr-name{font-size:13.5px;font-weight:700;color:var(--text);margin-bottom:3px}
.inv-addr-info{font-size:12px;color:var(--t2);line-height:1.75}

/* status */
.inv-status-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:30px;font-size:11px;font-weight:700;background:var(--a10);color:var(--accent);border:1px solid var(--a20);margin-bottom:20px}
.inv-status-dot{width:6px;height:6px;border-radius:50%;background:var(--accent);flex-shrink:0}

/* table */
.inv-tbl-wrap{border-radius:12px;overflow:hidden;border:1px solid var(--bdr);margin-bottom:0}
.inv-tbl{width:100%;border-collapse:collapse}
.inv-tbl thead tr{background:var(--hbg)}
.inv-tbl thead th{padding:11px 14px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:var(--hfg);text-align:left;white-space:nowrap}
.inv-tbl thead th:last-child{text-align:right}
.inv-tbl tbody tr{border-bottom:1px solid var(--bdr);transition:background .1s}
.inv-tbl tbody tr:last-child{border-bottom:none}
.inv-tbl tbody tr:hover{background:var(--a10)}
.inv-tbl tbody td{padding:12px 14px;font-size:12.5px;color:var(--t2);vertical-align:top}
.inv-tbl tbody td:first-child{color:var(--text);font-weight:600}
.inv-tbl tbody td:last-child{text-align:right;font-weight:700;color:var(--text)}
.inv-tbl .desc-row td{padding-top:0;padding-bottom:10px;font-size:11.5px;color:var(--t3);font-style:italic;border-bottom:none}
.inv-tbl tfoot tr:first-child td{border-top:2px solid var(--accent);padding-top:11px;font-weight:700;color:var(--t2)}
.inv-tbl tfoot td{padding:7px 14px;font-size:12.5px}
.inv-tbl tfoot td:last-child{text-align:right;font-weight:700;color:var(--text)}

/* totals */
.inv-totals-wrap{display:flex;justify-content:flex-end;border-top:1px solid var(--bdr)}
.inv-totals{width:290px;padding:20px 0 0}
.inv-tot-row{display:flex;justify-content:space-between;padding:5px 0;font-size:12.5px;color:var(--t2);border-bottom:1px dashed var(--bdr)}
.inv-tot-row:last-child{border-bottom:none}
.inv-tot-row span:last-child{font-weight:600;color:var(--text)}
.inv-tot-row.grand{margin-top:10px;padding:11px 15px;background:var(--hbg);color:var(--hfg);border-radius:11px;font-size:14.5px;font-weight:800;border-bottom:none}
.inv-tot-row.grand span:last-child{color:var(--hfg);font-weight:800}
.inv-tot-row.due{padding:8px 13px;background:var(--a10);border:1px solid var(--a20);border-radius:9px;margin-top:7px;font-weight:700;color:var(--accent);border-bottom:none}
.inv-tot-row.due span:last-child{color:var(--accent)}

/* footer */
.inv-footer{margin-top:28px;padding:18px 22px;background:var(--a10);border:1px solid var(--a20);border-radius:12px}
.inv-footer-title{font-size:13px;font-weight:700;color:var(--text);margin-bottom:4px}
.inv-footer-notes{font-size:12px;color:var(--t2);line-height:1.7}

/* bottom bar */
.inv-botbar{height:6px;background:var(--accent)}

/* RTL */
html[dir="rtl"] .inv-doc-title{text-align:left}
html[dir="rtl"] .inv-meta td:last-child{text-align:left}
html[dir="rtl"] .inv-tbl thead th:last-child{text-align:left}
html[dir="rtl"] .inv-tbl tbody td:last-child{text-align:left}
html[dir="rtl"] .inv-tbl tfoot td:last-child{text-align:left}
html[dir="rtl"] .inv-totals{margin-left:0;margin-right:auto}

@media(max-width:620px){
  .inv-header,.inv-body{padding:20px}
  .inv-header-inner{flex-direction:column}
  .inv-head-right{align-items:flex-start}
  .inv-doc-title{text-align:left;font-size:28px}
  .inv-addr-grid{grid-template-columns:1fr}
  .inv-totals{width:100%}
}
@media print{
  body{background:#fff}
  .inv-wrap{box-shadow:none;margin:0;border-radius:0}
}
</style>
@if($settings_data['SITE_RTL']=='on')
<link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
@endif
</head>
<body>
<div class="inv-wrap" id="boxes">
  <div class="inv-topbar"></div>

  {{-- HEADER --}}
  <div class="inv-header">
    <div class="inv-header-inner">

      {{-- LEFT: logo → qr (stacked) + company --}}
      <div class="inv-head-left">
        <img class="inv-logo" src="{{ $img }}" alt="Logo">
        <div class="inv-qr-box {{ $isLight ? 'qr-normal' : 'qr-invert' }}">
          {!! DNS2D::getBarcodeHTML(route('invoice.link.copy',\Crypt::encrypt($invoice->invoice_id)),"QRCODE",2,2) !!}
        </div>
        <div>
          <div class="inv-company-name">{{ $settings['company_name'] ?? '' }}</div>
          <div class="inv-company-detail">
            @if(!empty($settings['mail_from_address'])){{ $settings['mail_from_address'] }}<br>@endif
            @if(!empty($settings['company_address'])){{ $settings['company_address'] }}@endif
            @if(!empty($settings['company_city'])), {{ $settings['company_city'] }}@endif
            @if(!empty($settings['company_state'])) {{ $settings['company_state'] }}@endif
            @if(!empty($settings['company_zipcode'])) {{ $settings['company_zipcode'] }}@endif
            @if(!empty($settings['company_country']))<br>{{ $settings['company_country'] }}@endif
            @if(!empty($settings['company_telephone']))<br>{{ $settings['company_telephone'] }}@endif
            @if(!empty($settings['registration_number']))<br>{{ __('Reg') }}: {{ $settings['registration_number'] }}@endif
            @if(!empty($settings['vat_gst_number_switch']) && $settings['vat_gst_number_switch']=='on' && !empty($settings['vat_number']))<br>{{ $settings['tax_type'] ?? 'VAT' }}: {{ $settings['vat_number'] }}@endif
          </div>
        </div>
      </div>

      {{-- RIGHT: title + meta --}}
      <div class="inv-head-right">
        <div class="inv-doc-title">{{ __('Invoice') }}</div>
        <table class="inv-meta">
          <tr><td>{{ __('Invoice No') }}</td><td>{{ Utility::invoiceNumberFormat($settings,$invoice->invoice_id) }}</td></tr>
          <tr><td>{{ __('Issue Date') }}</td><td>{{ Utility::dateFormat($settings,$invoice->issue_date) }}</td></tr>
          <tr><td>{{ __('Due Date') }}</td><td>{{ Utility::dateFormat($settings,$invoice->due_date) }}</td></tr>
          @if(!empty($customFields) && count($invoice->customField)>0)
            @foreach($customFields as $field)
              <tr><td>{{ $field->name }}</td><td>{{ $invoice->customField[$field->id] ?? '-' }}</td></tr>
            @endforeach
          @endif
        </table>
      </div>

    </div>
  </div>

  {{-- BODY --}}
  <div class="inv-body">

    {{-- Addresses --}}
    <div class="inv-addr-grid">
      <div class="inv-addr-card">
        <div class="inv-addr-tag">{{ __('Bill To') }}</div>
        @if(!empty($customer->billing_name))
          <div class="inv-addr-name">{{ $customer->billing_name }}</div>
          <div class="inv-addr-info">
            @if($customer->billing_address){{ $customer->billing_address }}<br>@endif
            @if($customer->billing_city){{ $customer->billing_city }}@endif
            @if($customer->billing_state), {{ $customer->billing_state }}@endif
            @if($customer->billing_zip) {{ $customer->billing_zip }}@endif
            @if($customer->billing_country)<br>{{ $customer->billing_country }}@endif
            @if($customer->billing_phone)<br>{{ $customer->billing_phone }}@endif
          </div>
        @else<div class="inv-addr-info">—</div>@endif
      </div>
      @if($settings['shipping_display']=='on')
      <div class="inv-addr-card">
        <div class="inv-addr-tag">{{ __('Ship To') }}</div>
        @if(!empty($customer->shipping_name))
          <div class="inv-addr-name">{{ $customer->shipping_name }}</div>
          <div class="inv-addr-info">
            @if($customer->shipping_address){{ $customer->shipping_address }}<br>@endif
            @if($customer->shipping_city){{ $customer->shipping_city }}@endif
            @if($customer->shipping_state), {{ $customer->shipping_state }}@endif
            @if($customer->shipping_zip) {{ $customer->shipping_zip }}@endif
            @if($customer->shipping_country)<br>{{ $customer->shipping_country }}@endif
            @if($customer->shipping_phone)<br>{{ $customer->shipping_phone }}@endif
          </div>
        @else<div class="inv-addr-info">—</div>@endif
      </div>
      @endif
    </div>

    {{-- Status --}}
    @php $statusLabels = \App\Models\Invoice::$statues; @endphp
    <div class="inv-status-pill">
      <span class="inv-status-dot"></span>
      {{ __($statusLabels[$invoice->status] ?? 'Draft') }}
    </div>

    {{-- Items --}}
    <div class="inv-tbl-wrap">
      <table class="inv-tbl">
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
          @if(isset($invoice->itemData) && count($invoice->itemData)>0)
            @foreach($invoice->itemData as $item)
              @php $unit=App\Models\ProductServiceUnit::find($item->unit); $itax=0; @endphp
              <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->quantity }}{{ $unit?' ('.$unit->name.')':'' }}</td>
                <td>{{ Utility::priceFormat($settings,$item->price) }}</td>
                <td>{{ $item->discount!=0?Utility::priceFormat($settings,$item->discount):'—' }}</td>
                <td>
                  @if(!empty($item->itemTax))
                    @foreach($item->itemTax as $t)
                      @php $itax+=$t['tax_price']; @endphp
                      <div>{{ $t['name'] }} ({{ $t['rate'] }}) {{ $t['price'] }}</div>
                    @endforeach
                  @else —
                  @endif
                </td>
                <td>{{ Utility::priceFormat($settings,$item->price*$item->quantity-$item->discount+$itax) }}</td>
              </tr>
              @if(!empty($item->description))
                <tr class="desc-row"><td colspan="6">{{ $item->description }}</td></tr>
              @endif
            @endforeach
          @endif
        </tbody>
        <tfoot>
          <tr>
            <td>{{ __('Totals') }}</td>
            <td>{{ $invoice->totalQuantity }}</td>
            <td>{{ Utility::priceFormat($settings,$invoice->totalRate) }}</td>
            <td>{{ Utility::priceFormat($settings,$invoice->totalDiscount) }}</td>
            <td>{{ Utility::priceFormat($settings,$invoice->totalTaxPrice) }}</td>
            <td>{{ Utility::priceFormat($settings,$invoice->getSubTotal()) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    {{-- Totals --}}
    <div class="inv-totals-wrap">
      <div class="inv-totals">
        <div class="inv-tot-row"><span>{{ __('Subtotal') }}</span><span>{{ Utility::priceFormat($settings,$invoice->getSubTotal()) }}</span></div>
        @if($invoice->getTotalDiscount())
        <div class="inv-tot-row"><span>{{ __('Discount') }}</span><span>– {{ Utility::priceFormat($settings,$invoice->getTotalDiscount()) }}</span></div>
        @endif
        @if(!empty($invoice->taxesData))
          @foreach($invoice->taxesData as $tn=>$tp)
          <div class="inv-tot-row"><span>{{ $tn }}</span><span>{{ Utility::priceFormat($settings,$tp) }}</span></div>
          @endforeach
        @endif
        <div class="inv-tot-row grand">
          <span>{{ __('Total') }}</span>
          <span>{{ Utility::priceFormat($settings,$invoice->getSubTotal()-$invoice->getTotalDiscount()+$invoice->getTotalTax()) }}</span>
        </div>
        <div class="inv-tot-row"><span>{{ __('Paid') }}</span><span>{{ Utility::priceFormat($settings,($invoice->getTotal()-$invoice->getDue())-$invoice->invoiceTotalCreditNote()) }}</span></div>
        @if($invoice->invoiceTotalCreditNote())
        <div class="inv-tot-row"><span>{{ __('Credit Note') }}</span><span>{{ Utility::priceFormat($settings,$invoice->invoiceTotalCreditNote()) }}</span></div>
        @endif
        <div class="inv-tot-row due"><span>{{ __('Amount Due') }}</span><span>{{ Utility::priceFormat($settings,$invoice->getDue()) }}</span></div>
      </div>
    </div>

    {{-- Footer --}}
    @if(!empty($settings['footer_title']) || !empty($settings['footer_notes']))
    <div class="inv-footer">
      @if(!empty($settings['footer_title']))<div class="inv-footer-title">{{ $settings['footer_title'] }}</div>@endif
      @if(!empty($settings['footer_notes']))<div class="inv-footer-notes">{!! $settings['footer_notes'] !!}</div>@endif
    </div>
    @endif

  </div>
  <div class="inv-botbar"></div>
</div>

@if(!isset($preview))
  @include('invoice.script')
@endif
</body>
</html>
