@php
    $settings_data = \App\Models\Utility::settingsById($invoice->created_by);
    $hex = ltrim($color, '#');
    if(strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
    $lum = (0.299*$r + 0.587*$g + 0.114*$b)/255;
    $onAccent  = $lum > 0.55 ? '#1e293b' : '#ffffff';
    $accent    = '#'.$hex;
    $accentRgb = "$r,$g,$b";
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
  --accent-rgb:{{ $accentRgb }};
  --on-accent:{{ $onAccent }};
  --accent-10:rgba({{ $accentRgb }},.08);
  --accent-20:rgba({{ $accentRgb }},.15);
  --accent-40:rgba({{ $accentRgb }},.35);
  --text:#0f172a;--text-2:#475569;--text-3:#94a3b8;
  --border:#e2e8f0;--bg:#f8fafc;--white:#fff;
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:13px;line-height:1.6;-webkit-print-color-adjust:exact;print-color-adjust:exact}

/* ── wrapper ── */
.inv{max-width:800px;margin:20px auto;background:var(--white);border-radius:20px;overflow:hidden;box-shadow:0 8px 40px rgba(15,23,42,.12)}

/* ── header ── */
.inv-head{background:var(--accent);color:var(--on-accent);padding:36px 40px 32px;position:relative;overflow:hidden}
.inv-head::before{content:'';position:absolute;top:-60px;right:-60px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.06)}
.inv-head::after{content:'';position:absolute;bottom:-80px;left:30%;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,.04)}
.inv-head-top{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;position:relative;z-index:1}
.inv-logo{max-width:160px;max-height:56px;object-fit:contain}
.inv-title{font-size:42px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;opacity:.95;text-align:right;line-height:1}
.inv-head-bottom{display:flex;justify-content:space-between;align-items:flex-end;margin-top:28px;gap:20px;position:relative;z-index:1}
.inv-from-name{font-size:15px;font-weight:700;margin-bottom:6px;opacity:.95}
.inv-from-detail{font-size:12px;opacity:.78;line-height:1.8}
.inv-meta{display:flex;gap:20px;align-items:flex-end}
.inv-meta-table{border-collapse:collapse;min-width:200px}
.inv-meta-table td{padding:3px 0;font-size:12.5px;opacity:.85}
.inv-meta-table td:first-child{padding-right:18px;white-space:nowrap;font-weight:500}
.inv-meta-table td:last-child{text-align:right;font-weight:700}
.inv-qr{background:rgba(255,255,255,.18);border-radius:12px;padding:8px;width:76px;height:76px;flex-shrink:0;backdrop-filter:blur(4px)}
.inv-qr img{width:100%;height:100%}

/* ── body ── */
.inv-body{padding:32px 40px 40px}

/* ── address cards ── */
.inv-addr-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:28px}
.inv-addr{background:var(--accent-10);border:1px solid var(--accent-20);border-radius:14px;padding:18px 20px}
.inv-addr-label{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);margin-bottom:10px;display:flex;align-items:center;gap:6px}
.inv-addr-label::before{content:'';width:16px;height:2px;background:var(--accent);border-radius:2px;display:inline-block}
.inv-addr-name{font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px}
.inv-addr-detail{font-size:12px;color:var(--text-2);line-height:1.75}

/* ── status badge ── */
.inv-status{display:inline-flex;align-items:center;gap:7px;padding:6px 16px;border-radius:30px;font-size:11.5px;font-weight:700;background:var(--accent-10);color:var(--accent);border:1px solid var(--accent-20);margin-bottom:24px}
.inv-status-dot{width:7px;height:7px;border-radius:50%;background:var(--accent)}

/* ── items table ── */
.inv-table-wrap{border-radius:14px;overflow:hidden;border:1px solid var(--border);margin-bottom:0}
.inv-table{width:100%;border-collapse:collapse}
.inv-table thead tr{background:var(--accent)}
.inv-table thead th{padding:12px 16px;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:var(--on-accent);text-align:left;white-space:nowrap}
.inv-table thead th:last-child{text-align:right}
.inv-table tbody tr{border-bottom:1px solid var(--border);transition:background .12s}
.inv-table tbody tr:last-child{border-bottom:none}
.inv-table tbody tr:hover{background:var(--accent-10)}
.inv-table tbody td{padding:13px 16px;font-size:12.5px;color:var(--text-2);vertical-align:top}
.inv-table tbody td:first-child{color:var(--text);font-weight:600}
.inv-table tbody td:last-child{text-align:right;font-weight:700;color:var(--text)}
.inv-table .item-desc td{padding-top:0;padding-bottom:10px;font-size:11.5px;color:var(--text-3);font-style:italic;border-bottom:none}
.inv-table tfoot tr:first-child td{border-top:2px solid var(--accent);padding-top:12px;font-weight:700;color:var(--text-2)}
.inv-table tfoot td{padding:8px 16px;font-size:12.5px}
.inv-table tfoot td:last-child{text-align:right;font-weight:700;color:var(--text)}

/* ── totals ── */
.inv-bottom{display:flex;justify-content:flex-end;margin-top:0;border-top:1px solid var(--border)}
.inv-totals{width:300px;padding:22px 0 0}
.inv-total-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:12.5px;color:var(--text-2);border-bottom:1px dashed var(--border)}
.inv-total-row:last-child{border-bottom:none}
.inv-total-row span:last-child{font-weight:600;color:var(--text)}
.inv-total-row.grand{margin-top:10px;padding:12px 16px;background:var(--accent);color:var(--on-accent);border-radius:12px;font-size:15px;font-weight:800;border-bottom:none}
.inv-total-row.grand span:last-child{color:var(--on-accent);font-weight:800}
.inv-total-row.due{padding:9px 14px;background:var(--accent-10);border:1px solid var(--accent-20);border-radius:10px;margin-top:8px;font-weight:700;color:var(--accent);border-bottom:none}
.inv-total-row.due span:last-child{color:var(--accent)}

/* ── footer ── */
.inv-footer{margin-top:32px;padding:20px 24px;background:var(--accent-10);border:1px solid var(--accent-20);border-radius:14px}
.inv-footer-title{font-size:13px;font-weight:700;color:var(--text);margin-bottom:5px}
.inv-footer-notes{font-size:12px;color:var(--text-2);line-height:1.7}

/* ── watermark strip ── */
.inv-strip{height:5px;background:linear-gradient(90deg,var(--accent),rgba({{ $accentRgb }},.3),var(--accent))}

/* ── RTL ── */
html[dir="rtl"] .inv-title{text-align:left}
html[dir="rtl"] .inv-meta-table td:last-child{text-align:left}
html[dir="rtl"] .inv-table thead th:last-child{text-align:left}
html[dir="rtl"] .inv-table tbody td:last-child{text-align:left}
html[dir="rtl"] .inv-table tfoot td:last-child{text-align:left}
html[dir="rtl"] .inv-totals{margin-left:0;margin-right:auto}

@media(max-width:600px){
  .inv-head{padding:24px 20px 20px}
  .inv-body{padding:20px}
  .inv-addr-row{grid-template-columns:1fr}
  .inv-head-bottom{flex-direction:column;align-items:flex-start}
  .inv-title{font-size:28px}
  .inv-totals{width:100%}
}
@media print{
  body{background:#fff}
  .inv{box-shadow:none;margin:0;border-radius:0}
}
</style>
@if($settings_data['SITE_RTL']=='on')
<link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
@endif
</head>
<body>
<div class="inv" id="boxes">
  <div class="inv-strip"></div>

  {{-- ── HEADER ── --}}
  <div class="inv-head">
    <div class="inv-head-top">
      <img class="inv-logo" src="{{ $img }}" alt="Logo">
      <div class="inv-title">{{ __('Invoice') }}</div>
    </div>
    <div class="inv-head-bottom">
      <div>
        <div class="inv-from-name">@if($settings['company_name']){{ $settings['company_name'] }}@endif</div>
        <div class="inv-from-detail">
          @if($settings['mail_from_address']){{ $settings['mail_from_address'] }}<br>@endif
          @if($settings['company_address']){{ $settings['company_address'] }}@endif
          @if($settings['company_city']), {{ $settings['company_city'] }}@endif
          @if($settings['company_state']) {{ $settings['company_state'] }}@endif
          @if($settings['company_zipcode']) – {{ $settings['company_zipcode'] }}@endif
          @if($settings['company_country'])<br>{{ $settings['company_country'] }}@endif
          @if($settings['company_telephone'])<br>{{ $settings['company_telephone'] }}@endif
          @if(!empty($settings['registration_number']))<br>{{ __('Reg') }}: {{ $settings['registration_number'] }}@endif
          @if($settings['vat_gst_number_switch']=='on' && !empty($settings['tax_type']) && !empty($settings['vat_number']))<br>{{ $settings['tax_type'] }}: {{ $settings['vat_number'] }}@endif
        </div>
      </div>
      <div class="inv-meta">
        <table class="inv-meta-table">
          <tr><td>{{ __('Invoice No') }}</td><td>{{ Utility::invoiceNumberFormat($settings,$invoice->invoice_id) }}</td></tr>
          <tr><td>{{ __('Issue Date') }}</td><td>{{ Utility::dateFormat($settings,$invoice->issue_date) }}</td></tr>
          <tr><td>{{ __('Due Date') }}</td><td>{{ Utility::dateFormat($settings,$invoice->due_date) }}</td></tr>
          @if(!empty($customFields) && count($invoice->customField)>0)
            @foreach($customFields as $field)
              <tr><td>{{ $field->name }}</td><td>{{ !empty($invoice->customField)?$invoice->customField[$field->id]:'-' }}</td></tr>
            @endforeach
          @endif
        </table>
        <div class="inv-qr">
          {!! DNS2D::getBarcodeHTML(route('invoice.link.copy',\Crypt::encrypt($invoice->invoice_id)),"QRCODE",2,2) !!}
        </div>
      </div>
    </div>
  </div>

  {{-- ── BODY ── --}}
  <div class="inv-body">

    {{-- Addresses --}}
    <div class="inv-addr-row">
      <div class="inv-addr">
        <div class="inv-addr-label">{{ __('Bill To') }}</div>
        @if(!empty($customer->billing_name))
          <div class="inv-addr-name">{{ $customer->billing_name }}</div>
          <div class="inv-addr-detail">
            @if($customer->billing_address){{ $customer->billing_address }}<br>@endif
            @if($customer->billing_city){{ $customer->billing_city }}@endif
            @if($customer->billing_state), {{ $customer->billing_state }}@endif
            @if($customer->billing_zip) {{ $customer->billing_zip }}@endif
            @if($customer->billing_country)<br>{{ $customer->billing_country }}@endif
            @if($customer->billing_phone)<br>{{ $customer->billing_phone }}@endif
          </div>
        @else<div class="inv-addr-detail">—</div>@endif
      </div>
      @if($settings['shipping_display']=='on')
      <div class="inv-addr">
        <div class="inv-addr-label">{{ __('Ship To') }}</div>
        @if(!empty($customer->shipping_name))
          <div class="inv-addr-name">{{ $customer->shipping_name }}</div>
          <div class="inv-addr-detail">
            @if($customer->shipping_address){{ $customer->shipping_address }}<br>@endif
            @if($customer->shipping_city){{ $customer->shipping_city }}@endif
            @if($customer->shipping_state), {{ $customer->shipping_state }}@endif
            @if($customer->shipping_zip) {{ $customer->shipping_zip }}@endif
            @if($customer->shipping_country)<br>{{ $customer->shipping_country }}@endif
            @if($customer->shipping_phone)<br>{{ $customer->shipping_phone }}@endif
          </div>
        @else<div class="inv-addr-detail">—</div>@endif
      </div>
      @endif
    </div>

    {{-- Status --}}
    @php $statusLabels = \App\Models\Invoice::$statues; @endphp
    <div class="inv-status">
      <span class="inv-status-dot"></span>
      {{ __('Status') }}: {{ __($statusLabels[$invoice->status] ?? 'Draft') }}
    </div>

    {{-- Items Table --}}
    <div class="inv-table-wrap">
      <table class="inv-table">
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
              @php $unitName=App\Models\ProductServiceUnit::find($item->unit); $itemtax=0; @endphp
              <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->quantity }}{{ $unitName?' ('.$unitName->name.')':'' }}</td>
                <td>{{ Utility::priceFormat($settings,$item->price) }}</td>
                <td>{{ $item->discount!=0?Utility::priceFormat($settings,$item->discount):'—' }}</td>
                <td>
                  @if(!empty($item->itemTax))
                    @foreach($item->itemTax as $taxes)
                      @php $itemtax+=$taxes['tax_price']; @endphp
                      <div>{{ $taxes['name'] }} ({{ $taxes['rate'] }}) {{ $taxes['price'] }}</div>
                    @endforeach
                  @else —
                  @endif
                </td>
                <td>{{ Utility::priceFormat($settings,$item->price*$item->quantity-$item->discount+$itemtax) }}</td>
              </tr>
              @if(!empty($item->description))
                <tr class="item-desc"><td colspan="6">{{ $item->description }}</td></tr>
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
    <div class="inv-bottom">
      <div class="inv-totals">
        <div class="inv-total-row"><span>{{ __('Subtotal') }}</span><span>{{ Utility::priceFormat($settings,$invoice->getSubTotal()) }}</span></div>
        @if($invoice->getTotalDiscount())
        <div class="inv-total-row"><span>{{ __('Discount') }}</span><span>– {{ Utility::priceFormat($settings,$invoice->getTotalDiscount()) }}</span></div>
        @endif
        @if(!empty($invoice->taxesData))
          @foreach($invoice->taxesData as $taxName=>$taxPrice)
          <div class="inv-total-row"><span>{{ $taxName }}</span><span>{{ Utility::priceFormat($settings,$taxPrice) }}</span></div>
          @endforeach
        @endif
        <div class="inv-total-row grand"><span>{{ __('Total') }}</span><span>{{ Utility::priceFormat($settings,$invoice->getSubTotal()-$invoice->getTotalDiscount()+$invoice->getTotalTax()) }}</span></div>
        <div class="inv-total-row"><span>{{ __('Paid') }}</span><span>{{ Utility::priceFormat($settings,($invoice->getTotal()-$invoice->getDue())-$invoice->invoiceTotalCreditNote()) }}</span></div>
        @if($invoice->invoiceTotalCreditNote())
        <div class="inv-total-row"><span>{{ __('Credit Note') }}</span><span>{{ Utility::priceFormat($settings,$invoice->invoiceTotalCreditNote()) }}</span></div>
        @endif
        <div class="inv-total-row due"><span>{{ __('Amount Due') }}</span><span>{{ Utility::priceFormat($settings,$invoice->getDue()) }}</span></div>
      </div>
    </div>

    {{-- Footer --}}
    @if(!empty($settings['footer_title']) || !empty($settings['footer_notes']))
    <div class="inv-footer">
      @if(!empty($settings['footer_title']))<div class="inv-footer-title">{{ $settings['footer_title'] }}</div>@endif
      @if(!empty($settings['footer_notes']))<div class="inv-footer-notes">{!! $settings['footer_notes'] !!}</div>@endif
    </div>
    @endif

  </div>{{-- /inv-body --}}
  <div class="inv-strip"></div>
</div>

@if(!isset($preview))
  @include('invoice.script')
@endif
</body>
</html>
