@php
    $settings_data = \App\Models\Utility::settingsById($invoice->created_by ?? 1);
    $hex = ltrim((string) ($color ?? '1e293b'), '#');
    if (!preg_match('/^[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $hex)) {
        $hex = '1e293b';
    }
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
<html lang="en" dir="{{ ($settings_data['SITE_RTL'] ?? '') === 'on' ? 'rtl' : '' }}">
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
.inv-header{background:var(--hbg);padding:28px 36px;position:relative;overflow:hidden}
.inv-header::after{content:'';position:absolute;right:-40px;top:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.05);pointer-events:none}
.inv-header-inner{display:flex;justify-content:space-between;align-items:center;gap:20px}
.inv-head-left{display:flex;flex-direction:column;align-items:flex-start;gap:12px;flex-shrink:0}
.inv-logo{max-height:64px;max-width:200px;object-fit:contain;display:block}
.inv-qr-box{width:52px;height:52px;border-radius:8px;padding:4px;flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden}
.inv-qr-box img,.inv-qr-box svg,.inv-qr-box table{width:100%!important;height:100%!important;max-width:44px;max-height:44px}
.inv-qr-box.qr-invert{background:rgba(255,255,255,.15);filter:invert(1) brightness(2)}
.inv-qr-box.qr-normal{background:rgba(255,255,255,.95)}
.inv-head-right{text-align:right}
.inv-doc-title{font-size:32px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;color:var(--hfg);line-height:1}

/* ── meta strip (below header) ── */
.inv-meta-strip{background:#f8fafc;border-bottom:2px solid var(--bdr);padding:0}
.inv-meta-strip table{width:100%;border-collapse:collapse}
.inv-meta-strip td{padding:13px 20px;border-right:1px solid var(--bdr);vertical-align:top;white-space:nowrap}
.inv-meta-strip td:last-child{border-right:0}
.inv-meta-lbl{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;color:#94a3b8;display:block;margin-bottom:3px}
.inv-meta-val{font-size:13px;font-weight:700;color:#0f172a}

/* ── body ── */
.inv-body{padding:28px 36px 36px}

/* address row */
.inv-addr-row{display:flex;justify-content:space-between;align-items:stretch;gap:20px;margin-bottom:28px}
.inv-addr-block{flex:1 1 0;min-width:0;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 18px}
.inv-addr-to{text-align:right}
.inv-addr-tag{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;color:#64748b;margin-bottom:8px;display:block}
.inv-addr-name{font-size:14px;font-weight:800;color:var(--text);margin-bottom:5px}
.inv-addr-info{font-size:12px;color:var(--t2);line-height:1.8}

/* status */
.inv-status-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:30px;font-size:11px;font-weight:700;background:var(--a10);color:var(--accent);border:1px solid var(--a20);margin-bottom:20px}
.inv-status-dot{width:6px;height:6px;border-radius:50%;background:var(--accent);flex-shrink:0}

/* table */
.inv-tbl-wrap{border-radius:12px;overflow:hidden;border:1px solid var(--bdr);margin-bottom:0}
.inv-tbl{width:100%;border-collapse:collapse}
.inv-tbl thead tr{background:var(--hbg)}
.inv-tbl thead th{padding:11px 14px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:var(--hfg);text-align:left;white-space:nowrap}
.inv-tbl thead th:last-child{text-align:right}
.inv-tbl tbody tr{border-bottom:1px solid var(--bdr)}
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
html[dir="rtl"] .inv-addr-to{margin-left:0;margin-right:auto;text-align:left}
html[dir="rtl"] .inv-addr-to .inv-addr-tag{justify-content:flex-start}
html[dir="rtl"] .inv-tbl thead th:last-child{text-align:left}
html[dir="rtl"] .inv-tbl tbody td:last-child{text-align:left}
html[dir="rtl"] .inv-tbl tfoot td:last-child{text-align:left}
html[dir="rtl"] .inv-totals{margin-left:0;margin-right:auto}

@media(max-width:620px){
  .inv-header,.inv-body{padding:20px}
  .inv-header-inner{flex-direction:column}
  .inv-doc-title{font-size:28px}
  .inv-addr-row{flex-direction:column;gap:20px}
  .inv-addr-block{max-width:100%}
  .inv-addr-to{margin-left:0;text-align:left}
  .inv-addr-to .inv-addr-tag{justify-content:flex-start}
  .inv-totals{width:100%}
  .inv-meta-strip td{display:block;border-right:0;border-bottom:1px solid var(--bdr)}
}
@media print{
  body{background:#fff}
  .inv-wrap{box-shadow:none;margin:0;border-radius:0}
}
</style>
@if(($settings_data['SITE_RTL'] ?? '') === 'on')
<link rel="stylesheet" href="{{ asset('css/bootstrap-rtl.css') }}">
@endif
</head>
<body>
<div class="inv-wrap" id="boxes">
  <div class="inv-topbar"></div>

  {{-- HEADER: logo left, INVOICE title right --}}
  <div class="inv-header">
    <div class="inv-header-inner">
      <div class="inv-head-left">
        <img class="inv-logo" src="{{ $img }}" alt="Logo">
        @if(empty($preview))
        <div class="inv-qr-box {{ $isLight ? 'qr-normal' : 'qr-invert' }}">
          @php
            $qrHtml = '';
            try {
                $raw = DNS2D::getBarcodeHTML(route('invoice.link.copy', \Crypt::encrypt($invoice->invoice_id)), 'QRCODE', 2, 2);
                if (is_string($raw) && mb_check_encoding($raw, 'UTF-8')) { $qrHtml = $raw; }
            } catch (\Throwable $e) {}
          @endphp
          {!! $qrHtml !!}
        </div>
        @endif
      </div>
      <div class="inv-head-right">
        <div class="inv-doc-title">{{ __('Invoice') }}</div>
      </div>
    </div>
  </div>

  {{-- META STRIP: Invoice No | Issue Date | Due Date in one line --}}
  <div class="inv-meta-strip">
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td>
          <span class="inv-meta-lbl">{{ __('Invoice No') }}</span>
          <span class="inv-meta-val">{{ Utility::invoiceNumberFormat($settings, $invoice->invoice_id) }}</span>
        </td>
        <td>
          <span class="inv-meta-lbl">{{ __('Issue Date') }}</span>
          <span class="inv-meta-val">{{ Utility::dateFormat($settings, $invoice->issue_date) }}</span>
        </td>
        <td>
          <span class="inv-meta-lbl">{{ __('Due Date') }}</span>
          <span class="inv-meta-val">{{ Utility::dateFormat($settings, $invoice->due_date) }}</span>
        </td>
        @if(!empty($customFields) && count($invoice->customField) > 0)
          @foreach($customFields as $field)
          <td>
            <span class="inv-meta-lbl">{{ $field->name }}</span>
            <span class="inv-meta-val">{{ $invoice->customField[$field->id] ?? '-' }}</span>
          </td>
          @endforeach
        @endif
      </tr>
    </table>
  </div>

  {{-- BODY --}}
  <div class="inv-body">

    {{-- Addresses: Bill To left (TO), company right (FROM) --}}
    <div class="inv-addr-row">
      <div class="inv-addr-block inv-addr-from">
        <div class="inv-addr-tag">{{ __('Bill To') }}</div>
        @if(!empty($customer->billing_name))
          <div class="inv-addr-name">{{ $customer->billing_name }}</div>
          <div class="inv-addr-info">
            @if(!empty($customer->billing_address ?? '')){{ $customer->billing_address }}<br>@endif
            @if(!empty($customer->billing_city ?? '')){{ $customer->billing_city }}@endif
            @if(!empty($customer->billing_state ?? '')), {{ $customer->billing_state }}@endif
            @if(!empty($customer->billing_zip ?? '')) {{ $customer->billing_zip }}@endif
            @if(!empty($customer->billing_country ?? ''))<br>{{ $customer->billing_country }}@endif
            @if(!empty($customer->billing_phone ?? ''))<br>{{ $customer->billing_phone }}@endif
          </div>
        @else<div class="inv-addr-info">—</div>@endif
      </div>
      <div class="inv-addr-block inv-addr-to">
        <div class="inv-addr-tag">{{ __('From') }}</div>
        <div class="inv-addr-name">{{ $settings['company_name'] ?? '' }}</div>
        <div class="inv-addr-info">
          @if(!empty($settings['mail_from_address'])){{ $settings['mail_from_address'] }}<br>@endif
          @if(!empty($settings['company_address'])){{ $settings['company_address'] }}<br>@endif
          @if(!empty($settings['company_city'])){{ $settings['company_city'] }}@endif
          @if(!empty($settings['company_state'])), {{ $settings['company_state'] }}@endif
          @if(!empty($settings['company_zipcode'])) {{ $settings['company_zipcode'] }}@endif
          @if(!empty($settings['company_country']))<br>{{ $settings['company_country'] }}@endif
          @if(!empty($settings['company_telephone']))<br>{{ $settings['company_telephone'] }}@endif
        </div>
      </div>
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
              @php
                $itax = 0;
                $unit = (!empty($preview) || empty($item->unit)) ? null : App\Models\ProductServiceUnit::find($item->unit);
              @endphp
              <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->quantity }}{{ $unit ? ' ('.$unit->name.')' : '' }}</td>
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
          @elseif(!empty($preview))
            <tr>
              <td colspan="6" style="text-align:center;padding:28px 14px;color:var(--t3);font-size:12.5px;">
                {{ __('No services found for this party. Add clients under this agent to see line items here.') }}
              </td>
            </tr>
          @endif
        </tbody>
        <tfoot>
          @php
            if (!empty($preview)) {
              $footSubTotal = $invoice->previewSubTotal ?? $invoice->totalRate ?? 0;
              $footAmount   = $invoice->previewGrandTotal ?? 0;
              $footDiscount = $invoice->totalDiscount ?? 0;
            } else {
              $footSubTotal = $invoice->getSubTotal();
              $footAmount   = $invoice->getSubTotal();
              $footDiscount = $invoice->getTotalDiscount();
            }
          @endphp
          <tr>
            <td>{{ __('Totals') }}</td>
            <td>{{ $invoice->totalQuantity }}</td>
            <td>{{ Utility::priceFormat($settings,$invoice->totalRate) }}</td>
            <td>{{ Utility::priceFormat($settings,$footDiscount) }}</td>
            <td>{{ Utility::priceFormat($settings,$invoice->totalTaxPrice) }}</td>
            <td>{{ Utility::priceFormat($settings,$footAmount) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    {{-- Totals --}}
    @php
      if (!empty($preview)) {
        $sumSubTotal  = $invoice->previewSubTotal ?? 0;
        $sumDiscount  = $invoice->totalDiscount ?? 0;
        $sumGrand     = $invoice->previewGrandTotal ?? 0;
        $sumPaid      = $invoice->previewPaid ?? 0;
        $sumDue       = $invoice->previewDue ?? max(0, $sumGrand - $sumPaid);
        $sumCredit    = 0;
      } else {
        $sumSubTotal  = $invoice->getSubTotal();
        $sumDiscount  = $invoice->getTotalDiscount();
        $sumGrand     = $invoice->getSubTotal() - $invoice->getTotalDiscount() + $invoice->getTotalTax();
        $sumPaid      = ($invoice->getTotal() - $invoice->getDue()) - $invoice->invoiceTotalCreditNote();
        $sumDue       = $invoice->getDue();
        $sumCredit    = $invoice->invoiceTotalCreditNote();
      }
    @endphp
    <div class="inv-totals-wrap">
      <div class="inv-totals">
        <div class="inv-tot-row"><span>{{ __('Subtotal') }}</span><span>{{ Utility::priceFormat($settings,$sumSubTotal) }}</span></div>
        @if($sumDiscount)
        <div class="inv-tot-row"><span>{{ __('Discount') }}</span><span>– {{ Utility::priceFormat($settings,$sumDiscount) }}</span></div>
        @endif
        @if(!empty($invoice->taxesData))
          @foreach($invoice->taxesData as $tn=>$tp)
          <div class="inv-tot-row"><span>{{ $tn }}</span><span>{{ Utility::priceFormat($settings,$tp) }}</span></div>
          @endforeach
        @endif
        <div class="inv-tot-row grand">
          <span>{{ __('Total') }}</span>
          <span>{{ Utility::priceFormat($settings,$sumGrand) }}</span>
        </div>
        <div class="inv-tot-row"><span>{{ __('Paid') }}</span><span>{{ Utility::priceFormat($settings,$sumPaid) }}</span></div>
        @if($sumCredit)
        <div class="inv-tot-row"><span>{{ __('Credit Note') }}</span><span>{{ Utility::priceFormat($settings,$sumCredit) }}</span></div>
        @endif
        <div class="inv-tot-row due"><span>{{ __('Amount Due') }}</span><span>{{ Utility::priceFormat($settings,$sumDue) }}</span></div>
      </div>
    </div>

    {{-- Footer --}}
    @if(!empty($settings['footer_title']) || !empty($settings['footer_notes']))
    <div class="inv-footer">
      @if(!empty($settings['footer_title']))<div class="inv-footer-title">{{ $settings['footer_title'] }}</div>@endif
      @if(!empty($settings['footer_notes']))
        <div class="inv-footer-notes">@if(!empty($preview)){{ strip_tags($settings['footer_notes']) }}@else{!! $settings['footer_notes'] !!}@endif</div>
      @endif
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
