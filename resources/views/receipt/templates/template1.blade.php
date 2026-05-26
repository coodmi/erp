@php
    $settings_data = \App\Models\Utility::settingsById($invoice->created_by ?? 1);
    $hex = ltrim($color, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $lum = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    $headerBg = $lum > 0.85 ? '#1e293b' : ('#' . $hex);
    $headerFg = '#ffffff';
    $accent   = '#' . $hex;
    $rgb      = "$r,$g,$b";
    $companyPhone = $settings['receipt_company_phone']   ?? ($settings['company_telephone']   ?? '');
    $companyEmail = $settings['receipt_company_email']   ?? ($settings['mail_from_address']   ?? '');
    $companyAddr  = $settings['receipt_company_address'] ?? ($settings['company_address']     ?? '');
    $sumSub  = $invoice->previewSubTotal  ?? 0;
    $sumPaid = $invoice->previewPaid      ?? 0;
    $sumDue  = $invoice->previewDue       ?? 0;
    $sumRef  = $invoice->previewRefund    ?? 0;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 13px;
    color: #0f172a;
    background: #f1f5f9;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.page {
    width: 740px;
    margin: 20px auto;
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,.12);
}

/* ── Top accent bar ── */
.topbar { height: 6px; background: {{ $accent }}; }

/* ── Header ── */
.header-table { width: 100%; background: {{ $headerBg }}; padding: 24px 32px; }
.header-logo { width: 50%; vertical-align: middle; }
.header-logo img { max-height: 54px; max-width: 180px; object-fit: contain; display: block; }
.header-title { width: 50%; vertical-align: middle; text-align: right; }
.header-title h1 { font-size: 22px; font-weight: 900; letter-spacing: 3px; text-transform: uppercase; color: {{ $headerFg }}; margin: 0; }
.header-title p { font-size: 11px; color: rgba(255,255,255,.75); margin-top: 4px; }

/* ── Meta strip ── */
.meta-strip { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 32px; }
.meta-strip table { width: 100%; }
.meta-strip td { padding: 0 16px 0 0; vertical-align: top; white-space: nowrap; }
.meta-lbl { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; display: block; margin-bottom: 2px; }
.meta-val { font-size: 13px; font-weight: 700; color: #0f172a; }

/* ── Body ── */
.body { padding: 24px 32px 28px; }

/* ── Address cards ── */
.addr-table { width: 100%; margin-bottom: 22px; }
.addr-card { width: 48%; vertical-align: top; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; }
.addr-spacer { width: 4%; }
.addr-tag { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: {{ $accent }}; margin-bottom: 6px; }
.addr-name { font-size: 13px; font-weight: 700; margin-bottom: 4px; }
.addr-info { font-size: 11.5px; color: #475569; line-height: 1.8; }

/* ── Section title ── */
.section-title { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 8px; }

/* ── Services table ── */
.services-wrap { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 18px; }
.services-tbl { width: 100%; border-collapse: collapse; }
.services-tbl thead tr { background: {{ $headerBg }}; }
.services-tbl thead th { padding: 10px 13px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: {{ $headerFg }}; text-align: left; }
.services-tbl thead th.right { text-align: right; }
.services-tbl tbody td { padding: 11px 13px; font-size: 12px; color: #475569; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
.services-tbl tbody tr:last-child td { border-bottom: 0; }
.services-tbl tbody td.bold { font-weight: 700; color: #0f172a; }
.services-tbl tbody td.right { text-align: right; font-weight: 700; color: #0f172a; }
.services-tbl .empty td { text-align: center; padding: 28px; color: #94a3b8; font-size: 12px; }

/* ── Totals ── */
.totals-outer { width: 100%; margin-bottom: 20px; }
.totals-box { width: 280px; float: right; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; }
.totals-box table { width: 100%; border-collapse: collapse; }
.tot-row td { padding: 6px 0; border-bottom: 1px dashed #e2e8f0; font-size: 12.5px; }
.tot-row:last-child td { border-bottom: 0; }
.tot-lbl { color: #64748b; font-weight: 600; }
.tot-val { text-align: right; font-weight: 800; color: #0f172a; }
.tot-paid .tot-val { color: #059669; }
.tot-due  .tot-val { color: #e11d48; }
.tot-ref  .tot-val { color: #d97706; }
.tot-grand td { background: {{ $headerBg }}; color: {{ $headerFg }}; font-size: 13px; font-weight: 800; padding: 9px 10px; border-radius: 8px; border-bottom: 0; }
.clearfix::after { content: ''; display: table; clear: both; }

/* ── Notice ── */
.notice {
    background: rgba({{ $rgb }},.08);
    border: 1px solid rgba({{ $rgb }},.2);
    border-radius: 8px;
    padding: 10px 14px;
    text-align: center;
    font-size: 11px;
    color: {{ $accent }};
    font-weight: 600;
    margin-bottom: 20px;
    clear: both;
}

/* ── Signatures ── */
.sig-table { width: 100%; margin-bottom: 8px; }
.sig-cell { width: 33.33%; text-align: center; vertical-align: bottom; padding: 0 8px; }
.sig-img { max-height: 60px; max-width: 140px; width: auto; display: block; margin: 0 auto 6px; object-fit: contain; }
.sig-line { height: 1px; background: #cbd5e1; margin: 0 12px 7px; }
.sig-label { font-size: 11px; color: #64748b; font-weight: 600; }

/* ── Footer ── */
.footer-table { width: 100%; background: {{ $headerBg }}; padding: 16px 32px; }
.footer-left { width: 50%; vertical-align: middle; }
.footer-left img { max-height: 36px; max-width: 140px; object-fit: contain; display: block; }
.footer-addr { font-size: 10px; color: rgba(255,255,255,.7); margin-top: 5px; }
.footer-right { width: 50%; vertical-align: middle; text-align: right; }
.footer-right p { font-size: 11px; color: rgba(255,255,255,.85); margin: 2px 0; }
.footer-right strong { color: #ffffff; }

/* ── Bottom bar ── */
.botbar { height: 6px; background: {{ $accent }}; }

/* ── Print ── */
@media print {
    body { background: #fff; }
    .page { box-shadow: none; margin: 0; border-radius: 0; width: 100%; }
}
</style>
</head>
<body>
<div class="page" id="boxes">

  {{-- Top accent --}}
  <div class="topbar"></div>

  {{-- Header --}}
  <table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
      <td class="header-logo">
        <img src="{{ $img }}" alt="Logo">
      </td>
      <td class="header-title">
        <h1>{{ __('Money Receipt') }}</h1>
        <p>{{ $displayName }}</p>
      </td>
    </tr>
  </table>

  {{-- Meta strip --}}
  <div class="meta-strip">
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td>
          <span class="meta-lbl">{{ __('Receipt No') }}</span>
          <span class="meta-val">{{ $receiptNo }}</span>
        </td>
        <td>
          <span class="meta-lbl">{{ __('Date') }}</span>
          <span class="meta-val">{{ date('d M Y') }}</span>
        </td>
        <td>
          <span class="meta-lbl">{{ __('Party') }}</span>
          <span class="meta-val">{{ $customer->billing_name ?? ('&lt;' . ucfirst($partyLabel) . ' Name&gt;') }}</span>
        </td>
        @if(!empty($customer->party_code))
        <td>
          <span class="meta-lbl">{{ __('ID') }}</span>
          <span class="meta-val">{{ $customer->party_code }}</span>
        </td>
        @endif
      </tr>
    </table>
  </div>

  {{-- Body --}}
  <div class="body">

    {{-- Address cards --}}
    <table class="addr-table" cellpadding="0" cellspacing="0">
      <tr>
        <td class="addr-card">
          <div class="addr-tag">{{ __('Received From') }}</div>
          <div class="addr-name">{{ $customer->billing_name ?? ('&lt;Customer Name&gt;') }}</div>
          <div class="addr-info">
            @if(!empty($customer->billing_phone)){{ __('Phone') }}: {{ $customer->billing_phone }}<br>@endif
            @if(!empty($customer->billing_address)){{ $customer->billing_address }}<br>@endif
            @if(!empty($customer->billing_country)){{ $customer->billing_country }}@endif
          </div>
        </td>
        <td class="addr-spacer"></td>
        <td class="addr-card" style="text-align:right;">
          <div class="addr-tag" style="text-align:right;">{{ __('Company') }}</div>
          <div class="addr-name">{{ $displayName }}</div>
          <div class="addr-info">
            @if($companyPhone){{ $companyPhone }}<br>@endif
            @if($companyEmail){{ $companyEmail }}<br>@endif
            @if($companyAddr){{ $companyAddr }}@endif
          </div>
        </td>
      </tr>
    </table>

    {{-- Services --}}
    <div class="section-title">{{ __('Services') }}</div>
    <div class="services-wrap">
      <table class="services-tbl" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <th style="width:32px;">#</th>
            <th>{{ __('Client / Service') }}</th>
            <th>{{ __('Visa Type') }}</th>
            <th>{{ __('Country') }}</th>
            <th class="right">{{ __('Amount') }}</th>
          </tr>
        </thead>
        <tbody>
          @if(!empty($invoice->itemData) && count($invoice->itemData) > 0)
            @foreach($invoice->itemData as $i => $item)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td class="bold">{{ $item->name }}</td>
              <td>{{ $item->visa_label ?? '—' }}</td>
              <td>{{ $item->country_name ?? '—' }}</td>
              <td class="right">{{ \App\Models\Utility::priceFormat($settings, ($item->price * $item->quantity) - ($item->discount ?? 0)) }}</td>
            </tr>
            @endforeach
          @else
            <tr class="empty"><td colspan="5">{{ __('No services found for this party.') }}</td></tr>
          @endif
        </tbody>
      </table>
    </div>

    {{-- Totals --}}
    <div class="totals-outer clearfix">
      <div class="totals-box">
        <table cellpadding="0" cellspacing="0">
          <tr class="tot-row">
            <td class="tot-lbl">{{ __('Subtotal') }}</td>
            <td class="tot-val">{{ \App\Models\Utility::priceFormat($settings, $sumSub) }}</td>
          </tr>
          <tr class="tot-row tot-paid">
            <td class="tot-lbl">{{ __('Paid') }}</td>
            <td class="tot-val">{{ \App\Models\Utility::priceFormat($settings, $sumPaid) }}</td>
          </tr>
          <tr class="tot-row tot-due">
            <td class="tot-lbl">{{ __('Due') }}</td>
            <td class="tot-val">{{ \App\Models\Utility::priceFormat($settings, $sumDue) }}</td>
          </tr>
          @if($sumRef > 0)
          <tr class="tot-row tot-ref">
            <td class="tot-lbl">{{ __('Refund') }}</td>
            <td class="tot-val">{{ \App\Models\Utility::priceFormat($settings, $sumRef) }}</td>
          </tr>
          @endif
          <tr class="tot-row tot-grand">
            <td class="tot-lbl" style="color:#fff;">{{ __('Total') }}</td>
            <td class="tot-val" style="color:#fff;">{{ \App\Models\Utility::priceFormat($settings, $sumSub) }}</td>
          </tr>
        </table>
      </div>
    </div>

    {{-- Notice --}}
    @if(!empty($noticeText))
    <div class="notice">{{ $noticeText }}</div>
    @endif

    {{-- Signatures --}}
    @php
        $sigCashier = $settings['signature_cashier'] ?? '';
        $sigManager = $settings['signature_manager'] ?? '';
        $sigMd      = $settings['signature_md']      ?? '';
    @endphp
    <table class="sig-table" cellpadding="0" cellspacing="0">
      <tr>
        <td class="sig-cell">
          @if(!empty($sigCashier))
            <img class="sig-img" src="{{ \App\Models\Utility::printFileUrl('signatures', $sigCashier) }}" alt="Cashier">
          @endif
          <div class="sig-line"></div>
          <div class="sig-label">{{ __('Cashier Signature') }}</div>
        </td>
        <td class="sig-cell">
          @if(!empty($sigManager))
            <img class="sig-img" src="{{ \App\Models\Utility::printFileUrl('signatures', $sigManager) }}" alt="Manager">
          @endif
          <div class="sig-line"></div>
          <div class="sig-label">{{ __('Manager Signature') }}</div>
        </td>
        <td class="sig-cell">
          @if(!empty($sigMd))
            <img class="sig-img" src="{{ \App\Models\Utility::printFileUrl('signatures', $sigMd) }}" alt="MD">
          @endif
          <div class="sig-line"></div>
          <div class="sig-label">{{ __('MD Signature & Seal') }}</div>
        </td>
      </tr>
    </table>

    @if(!empty($footerText))
    <p style="text-align:center;font-size:11px;color:#64748b;margin-top:12px;">{{ $footerText }}</p>
    @endif

  </div>{{-- /body --}}

  {{-- Footer --}}
  <table class="footer-table" cellpadding="0" cellspacing="0">
    <tr>
      <td class="footer-left">
        <img src="{{ $img }}" alt="Logo">
        @if($companyAddr)<div class="footer-addr">{{ $companyAddr }}</div>@endif
      </td>
      <td class="footer-right">
        <p><strong>{{ $displayName }}</strong></p>
        @if($companyPhone)<p>{{ $companyPhone }}</p>@endif
        @if($companyEmail)<p>{{ $companyEmail }}</p>@endif
      </td>
    </tr>
  </table>

  {{-- Bottom bar --}}
  <div class="botbar"></div>

</div>
</body>
</html>
