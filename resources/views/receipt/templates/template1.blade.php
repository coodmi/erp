@php
    $settings_data = \App\Models\Utility::settingsById($invoice->created_by ?? 1);
    $hex = ltrim($color, '#');
    if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
    $r = hexdec(substr($hex,0,2)); $g = hexdec(substr($hex,2,2)); $b = hexdec(substr($hex,4,2));
    $lum = (0.299*$r + 0.587*$g + 0.114*$b) / 255;
    $headerBg = $lum > 0.85 ? '#1e293b' : ('#'.$hex);
    $headerFg = '#ffffff';
    $accent   = '#'.$hex;
    $rgb      = "$r,$g,$b";

    // Company info — try receipt-specific first, then fall back to general settings
    $companyName  = !empty($settings['receipt_company_name'])    ? $settings['receipt_company_name']    : ($settings['company_name']      ?? '');
    $companyPhone = !empty($settings['receipt_company_phone'])   ? $settings['receipt_company_phone']   : ($settings['company_telephone'] ?? '');
    $companyEmail = !empty($settings['receipt_company_email'])   ? $settings['receipt_company_email']   : ($settings['mail_from_address'] ?? '');
    $companyAddr  = !empty($settings['receipt_company_address']) ? $settings['receipt_company_address'] : ($settings['company_address']   ?? '');
    $companyCity  = $settings['company_city']    ?? '';
    $companyState = $settings['company_state']   ?? '';
    $companyZip   = $settings['company_zipcode'] ?? '';
    $companyCountry = $settings['company_country'] ?? '';
    $companyFullAddr = trim(implode(', ', array_filter([$companyAddr, $companyCity, $companyState, $companyZip, $companyCountry])));

    $sumSub  = $invoice->previewSubTotal  ?? 0;
    $sumPaid = $invoice->previewPaid      ?? 0;
    $sumDue  = $invoice->previewDue       ?? 0;
    $sumRef  = $invoice->previewRefund    ?? 0;

    $sigCashier = $settings['signature_cashier'] ?? '';
    $sigManager = $settings['signature_manager'] ?? '';
    $sigMd      = $settings['signature_md']      ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#0f172a;background:#f1f5f9;-webkit-print-color-adjust:exact;print-color-adjust:exact}

/* ── Page wrapper ── */
.page{width:760px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 6px 32px rgba(0,0,0,.13)}

/* ── Accent bars ── */
.bar-top{height:5px;background:{{ $accent }}}
.bar-bot{height:5px;background:{{ $accent }}}

/* ── Header ── */
.hdr{background:{{ $headerBg }};padding:0}
.hdr table{width:100%;border-collapse:collapse}
.hdr td{padding:22px 32px;vertical-align:middle}
.hdr-logo img{max-height:52px;max-width:180px;object-fit:contain;display:block}
.hdr-title{text-align:right}
.hdr-title h1{font-size:24px;font-weight:900;letter-spacing:3px;text-transform:uppercase;color:#fff;margin:0;line-height:1}
.hdr-title .sub{font-size:11px;color:rgba(255,255,255,.7);margin-top:5px}

/* ── Meta strip ── */
.meta{background:#f8fafc;border-bottom:2px solid #e2e8f0;padding:0}
.meta table{width:100%;border-collapse:collapse}
.meta td{padding:12px 20px;border-right:1px solid #e2e8f0;vertical-align:top}
.meta td:last-child{border-right:0}
.meta .lbl{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;color:#94a3b8;display:block;margin-bottom:3px}
.meta .val{font-size:13px;font-weight:700;color:#0f172a}

/* ── Body ── */
.body{padding:24px 32px 28px}

/* ── Bill-to / Company cards ── */
.cards table{width:100%;border-collapse:collapse;margin-bottom:22px}
.card-cell{width:48%;vertical-align:top;padding:16px 18px;border-radius:10px}
.card-from{background:#f8fafc;border:1px solid #e2e8f0}
.card-company{background:#f8fafc;border:1px solid #e2e8f0;text-align:right}
.card-spacer{width:4%}
.card-tag{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;margin-bottom:7px;color:#64748b}
.card-name{font-size:14px;font-weight:800;color:#0f172a;margin-bottom:5px}
.card-info{font-size:11.5px;color:#475569;line-height:1.85}
.card-info span{display:block}

/* ── Section label ── */
.sec-label{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#94a3b8;margin-bottom:8px}

/* ── Services table ── */
.tbl-wrap{border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:20px}
.svc-tbl{width:100%;border-collapse:collapse}
.svc-tbl thead tr{background:{{ $headerBg }}}
.svc-tbl thead th{padding:10px 14px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#fff;text-align:left}
.svc-tbl thead th.r{text-align:right}
.svc-tbl tbody td{padding:11px 14px;font-size:12.5px;color:#475569;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.svc-tbl tbody tr:last-child td{border-bottom:0}
.svc-tbl tbody td.bold{font-weight:700;color:#0f172a}
.svc-tbl tbody td.r{text-align:right;font-weight:700;color:#0f172a}
.svc-tbl .empty td{text-align:center;padding:28px;color:#94a3b8;font-size:12px}

/* ── Totals ── */
.totals-wrap{width:100%;margin-bottom:20px}
.totals-box{width:280px;float:right;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden}
.tot-tbl{width:100%;border-collapse:collapse}
.tot-tbl tr td{padding:8px 16px;font-size:12.5px;border-bottom:1px solid #f1f5f9}
.tot-tbl tr:last-child td{border-bottom:0}
.tot-lbl{color:#64748b;font-weight:600}
.tot-val{text-align:right;font-weight:800;color:#0f172a}
.tot-paid .tot-val{color:#059669}
.tot-due  .tot-val{color:#e11d48}
.tot-ref  .tot-val{color:#d97706}
.tot-grand{background:{{ $headerBg }}}
.tot-grand .tot-lbl,.tot-grand .tot-val{color:#fff;font-size:13.5px;font-weight:900;border-bottom:0}
.clearfix::after{content:'';display:table;clear:both}

/* ── Notice ── */
.notice{clear:both;background:rgba({{ $rgb }},.07);border:1px solid rgba({{ $rgb }},.2);border-radius:8px;padding:10px 16px;text-align:center;font-size:11px;color:{{ $accent }};font-weight:700;margin-bottom:22px}

/* ── Signatures ── */
.sig-tbl{width:100%;border-collapse:collapse;margin-bottom:8px}
.sig-cell{width:33.33%;text-align:center;padding:0 10px;vertical-align:bottom}
.sig-img{max-height:58px;max-width:130px;width:auto;display:block;margin:0 auto 6px;object-fit:contain}
.sig-line{height:1px;background:#cbd5e1;margin:0 10px 7px}
.sig-lbl{font-size:11px;color:#64748b;font-weight:600}

/* ── Footer ── */
.ftr{background:{{ $headerBg }};padding:0}
.ftr table{width:100%;border-collapse:collapse}
.ftr td{padding:16px 32px;vertical-align:middle}
.ftr-logo img{max-height:34px;max-width:130px;object-fit:contain;display:block}
.ftr-addr{font-size:10px;color:rgba(255,255,255,.65);margin-top:5px}
.ftr-info{text-align:right}
.ftr-info p{font-size:11px;color:rgba(255,255,255,.8);margin:2px 0}
.ftr-info strong{color:#fff;font-size:12px}

@media print{
  body{background:#fff}
  .page{box-shadow:none;margin:0;border-radius:0;width:100%}
}
</style>
</head>
<body>
<div class="page" id="boxes">

  <div class="bar-top"></div>

  {{-- ── Header ── --}}
  <div class="hdr">
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td class="hdr-logo"><img src="{{ $img }}" alt="Logo"></td>
        <td class="hdr-title">
          <h1>Money Receipt</h1>
          <div class="sub">{{ $companyName }}</div>
        </td>
      </tr>
    </table>
  </div>

  {{-- ── Meta strip ── --}}
  <div class="meta">
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td>
          <span class="lbl">Receipt No</span>
          <span class="val">{{ $receiptNo }}</span>
        </td>
        <td>
          <span class="lbl">Date</span>
          <span class="val">{{ date('d M Y') }}</span>
        </td>
        <td>
          <span class="lbl">Party</span>
          <span class="val">{{ $customer->billing_name ?? '—' }}</span>
        </td>
        @if(!empty($customer->party_code))
        <td>
          <span class="lbl">ID</span>
          <span class="val">{{ $customer->party_code }}</span>
        </td>
        @endif
      </tr>
    </table>
  </div>

  {{-- ── Body ── --}}
  <div class="body">

    {{-- Bill-to & Company cards --}}
    <div class="cards">
      <table cellpadding="0" cellspacing="0">
        <tr>
          {{-- Bill To --}}
          <td class="card-cell card-from">
            <div class="card-tag">Bill To</div>
            <div class="card-name">{{ $customer->billing_name ?? '—' }}</div>
            <div class="card-info">
              @if(!empty($customer->billing_phone) && !str_starts_with($customer->billing_phone,'<'))
                <span>📞 {{ $customer->billing_phone }}</span>
              @endif
              @if(!empty($customer->billing_address) && !str_starts_with($customer->billing_address,'<'))
                <span>{{ $customer->billing_address }}</span>
              @endif
              @php
                $loc = trim(implode(', ', array_filter([
                  $customer->billing_city    ?? '',
                  $customer->billing_state   ?? '',
                  $customer->billing_country ?? '',
                ])));
              @endphp
              @if($loc && !str_starts_with($loc,'<'))<span>{{ $loc }}</span>@endif
            </div>
          </td>

          <td class="card-spacer"></td>

          {{-- Company --}}
          <td class="card-cell card-company">
            <div class="card-tag" style="text-align:right">From</div>
            <div class="card-name">{{ $companyName }}</div>
            <div class="card-info">
              @if($companyPhone)<span>📞 {{ $companyPhone }}</span>@endif
              @if($companyEmail)<span>✉ {{ $companyEmail }}</span>@endif
              @if($companyFullAddr)<span>{{ $companyFullAddr }}</span>@endif
            </div>
          </td>
        </tr>
      </table>
    </div>

    {{-- Services --}}
    <div class="sec-label">Services</div>
    <div class="tbl-wrap">
      <table class="svc-tbl" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <th style="width:32px">#</th>
            <th>Client / Service</th>
            <th>Visa Type</th>
            <th>Country</th>
            <th class="r">Amount</th>
          </tr>
        </thead>
        <tbody>
          @if(!empty($invoice->itemData) && count($invoice->itemData) > 0)
            @foreach($invoice->itemData as $i => $item)
            <tr>
              <td>{{ $i+1 }}</td>
              <td class="bold">{{ $item->name }}</td>
              <td>{{ $item->visa_label ?? '—' }}</td>
              <td>{{ $item->country_name ?? '—' }}</td>
              <td class="r">{{ \App\Models\Utility::priceFormat($settings, ($item->price * $item->quantity) - ($item->discount ?? 0)) }}</td>
            </tr>
            @endforeach
          @else
            <tr class="empty"><td colspan="5">No services found for this party.</td></tr>
          @endif
        </tbody>
      </table>
    </div>

    {{-- Totals --}}
    <div class="totals-wrap clearfix">
      <div class="totals-box">
        <table class="tot-tbl" cellpadding="0" cellspacing="0">
          <tr><td class="tot-lbl">Subtotal</td><td class="tot-val">{{ \App\Models\Utility::priceFormat($settings,$sumSub) }}</td></tr>
          <tr class="tot-paid"><td class="tot-lbl">Paid</td><td class="tot-val">{{ \App\Models\Utility::priceFormat($settings,$sumPaid) }}</td></tr>
          <tr class="tot-due"><td class="tot-lbl">Due</td><td class="tot-val">{{ \App\Models\Utility::priceFormat($settings,$sumDue) }}</td></tr>
          @if($sumRef > 0)
          <tr class="tot-ref"><td class="tot-lbl">Refund</td><td class="tot-val">{{ \App\Models\Utility::priceFormat($settings,$sumRef) }}</td></tr>
          @endif
          <tr class="tot-grand"><td class="tot-lbl">Total</td><td class="tot-val">{{ \App\Models\Utility::priceFormat($settings,$sumSub) }}</td></tr>
        </table>
      </div>
    </div>

    {{-- Notice --}}
    @if(!empty($noticeText))
    <div class="notice">{{ $noticeText }}</div>
    @endif

    {{-- Signatures --}}
    <table class="sig-tbl" cellpadding="0" cellspacing="0">
      <tr>
        <td class="sig-cell">
          @if(!empty($sigCashier))
            <img class="sig-img" src="{{ \App\Models\Utility::printFileUrl('signatures',$sigCashier) }}" alt="">
          @else
            <div style="height:50px"></div>
          @endif
          <div class="sig-line"></div>
          <div class="sig-lbl">Cashier Signature</div>
        </td>
        <td class="sig-cell">
          @if(!empty($sigManager))
            <img class="sig-img" src="{{ \App\Models\Utility::printFileUrl('signatures',$sigManager) }}" alt="">
          @else
            <div style="height:50px"></div>
          @endif
          <div class="sig-line"></div>
          <div class="sig-lbl">Manager Signature</div>
        </td>
        <td class="sig-cell">
          @if(!empty($sigMd))
            <img class="sig-img" src="{{ \App\Models\Utility::printFileUrl('signatures',$sigMd) }}" alt="">
          @else
            <div style="height:50px"></div>
          @endif
          <div class="sig-line"></div>
          <div class="sig-lbl">MD Signature &amp; Seal</div>
        </td>
      </tr>
    </table>

    @if(!empty($footerText))
    <p style="text-align:center;font-size:11px;color:#64748b;margin-top:14px">{{ $footerText }}</p>
    @endif

  </div>{{-- /body --}}

  {{-- ── Footer ── --}}
  <div class="ftr">
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td class="ftr-logo">
          <img src="{{ $img }}" alt="Logo">
          @if($companyFullAddr)<div class="ftr-addr">{{ $companyFullAddr }}</div>@endif
        </td>
        <td class="ftr-info">
          <p><strong>{{ $companyName }}</strong></p>
          @if($companyPhone)<p>{{ $companyPhone }}</p>@endif
          @if($companyEmail)<p>{{ $companyEmail }}</p>@endif
        </td>
      </tr>
    </table>
  </div>

  <div class="bar-bot"></div>

</div>
</body>
</html>
