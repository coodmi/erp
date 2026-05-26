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
    $accent = '#' . $hex;
    $rgb = "$r,$g,$b";
    $companyPhone = $settings['receipt_company_phone'] ?? ($settings['company_telephone'] ?? '');
    $companyEmail = $settings['receipt_company_email'] ?? ($settings['mail_from_address'] ?? '');
    $companyAddr  = $settings['receipt_company_address'] ?? ($settings['company_address'] ?? '');
    $sumSub  = $invoice->previewSubTotal ?? 0;
    $sumPaid = $invoice->previewPaid ?? 0;
    $sumDue  = $invoice->previewDue ?? 0;
    $sumRef  = $invoice->previewRefund ?? 0;
@endphp
<!DOCTYPE html>
<html lang="en" dir="{{ ($settings_data['SITE_RTL'] ?? '') == 'on' ? 'rtl' : '' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f1f5f9;color:#0f172a;font-size:13px;line-height:1.6;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.rc-wrap{max-width:760px;margin:24px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 32px rgba(15,23,42,.13)}
.rc-topbar{height:6px;background:{{ $accent }}}
.rc-header{background:{{ $headerBg }};padding:28px 36px;display:flex;align-items:center;justify-content:space-between;gap:20px}
.rc-logo{max-height:56px;max-width:200px;object-fit:contain;filter:brightness(0) invert(1)}
.rc-title{text-align:right}
.rc-title h1{font-size:1.65rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:{{ $headerFg }};margin:0}
.rc-title p{font-size:.8rem;color:rgba(255,255,255,.75);margin-top:4px}
.rc-meta{background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 36px;display:flex;flex-wrap:wrap;gap:24px 32px}
.rc-meta-item .lbl{font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:2px}
.rc-meta-item .val{font-size:.86rem;font-weight:700;color:#0f172a}
.rc-body{padding:28px 36px 32px}
.rc-addr-grid{display:flex;justify-content:space-between;align-items:flex-start;gap:24px;margin-bottom:24px;flex-wrap:wrap}
.rc-addr-card{flex:1;min-width:200px;max-width:48%;background:rgba({{ $rgb }},.06);border:1px solid rgba({{ $rgb }},.14);border-radius:12px;padding:16px 18px}
.rc-addr-tag{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:{{ $accent }};margin-bottom:8px}
.rc-addr-name{font-size:13px;font-weight:700;margin-bottom:4px}
.rc-addr-info{font-size:12px;color:#475569;line-height:1.75}
.rc-section-title{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:#94a3b8;margin-bottom:10px}
.rc-tbl-wrap{border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;margin-bottom:20px}
.rc-tbl{width:100%;border-collapse:collapse}
.rc-tbl thead tr{background:{{ $headerBg }}}
.rc-tbl thead th{padding:11px 14px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:{{ $headerFg }};text-align:left}
.rc-tbl thead th:last-child{text-align:right}
.rc-tbl tbody td{padding:12px 14px;font-size:12px;color:#475569;border-bottom:1px solid #f1f5f9;vertical-align:top}
.rc-tbl tbody tr:last-child td{border-bottom:0}
.rc-tbl tbody td:first-child{font-weight:600;color:#0f172a}
.rc-tbl tbody td:last-child{text-align:right;font-weight:700;color:#0f172a}
.rc-empty td{text-align:center;padding:32px;color:#94a3b8;font-size:12.5px}
.rc-totals-wrap{display:flex;justify-content:flex-end;margin-bottom:22px}
.rc-totals{min-width:280px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px}
.rc-tot-row{display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px dashed #e2e8f0;font-size:12.5px}
.rc-tot-row:last-child{border-bottom:0}
.rc-tot-row .tl{color:#64748b;font-weight:600}
.rc-tot-row .tv{font-weight:800;color:#0f172a}
.rc-tot-row.paid .tv{color:#059669}
.rc-tot-row.due .tv{color:#e11d48}
.rc-tot-row.ref .tv{color:#d97706}
.rc-tot-row.grand{margin-top:6px;padding:10px 12px;background:{{ $headerBg }};border-radius:10px;border-bottom:none}
.rc-tot-row.grand .tl,.rc-tot-row.grand .tv{color:{{ $headerFg }};font-size:14px;font-weight:800}
.rc-notice{background:rgba({{ $rgb }},.08);border:1px solid rgba({{ $rgb }},.2);border-radius:10px;padding:12px 16px;text-align:center;font-size:11.5px;color:{{ $accent }};font-weight:600;margin-bottom:22px}
.rc-sigs{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:8px}
.rc-sig{text-align:center}
.rc-sig-line{height:1px;background:#cbd5e1;margin:0 16px 8px}
.rc-sig p{font-size:11px;color:#64748b;font-weight:600}
.rc-footer{background:{{ $headerBg }};padding:18px 36px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.rc-footer img{height:36px;filter:brightness(0) invert(1)}
.rc-footer-info{text-align:right}
.rc-footer-info p{color:rgba(255,255,255,.85);font-size:11px;margin:2px 0}
.rc-botbar{height:6px;background:{{ $accent }}}
@media(max-width:620px){
  .rc-header,.rc-body,.rc-meta,.rc-footer{padding-left:20px;padding-right:20px}
  .rc-addr-card{max-width:100%}
  .rc-sigs{grid-template-columns:1fr}
  .rc-title{text-align:left}
}
@media print{body{background:#fff}.rc-wrap{box-shadow:none;margin:0;border-radius:0}}
</style>
</head>
<body>
<div class="rc-wrap">
  <div class="rc-topbar"></div>

  <div class="rc-header">
    <img class="rc-logo" src="{{ $img }}" alt="Logo">
    <div class="rc-title">
      <h1>{{ __('Money Receipt') }}</h1>
      <p>{{ $displayName }}</p>
    </div>
  </div>

  <div class="rc-meta">
    <div class="rc-meta-item"><span class="lbl">{{ __('Receipt No') }}</span><span class="val">{{ $receiptNo }}</span></div>
    <div class="rc-meta-item"><span class="lbl">{{ __('Date') }}</span><span class="val">{{ date('d M Y') }}</span></div>
    <div class="rc-meta-item"><span class="lbl">{{ __('Party') }}</span><span class="val">{{ $customer->billing_name ?? '—' }}</span></div>
    @if(!empty($customer->party_code))
    <div class="rc-meta-item"><span class="lbl">{{ __('ID') }}</span><span class="val">{{ $customer->party_code }}</span></div>
    @endif
  </div>

  <div class="rc-body">
    <div class="rc-addr-grid">
      <div class="rc-addr-card">
        <div class="rc-addr-tag">{{ __('Received From') }}</div>
        <div class="rc-addr-name">{{ $customer->billing_name ?? '—' }}</div>
        <div class="rc-addr-info">
          @if(!empty($customer->billing_phone)){{ __('Phone') }}: {{ $customer->billing_phone }}<br>@endif
          @if(!empty($customer->billing_address)){{ $customer->billing_address }}<br>@endif
          @if(!empty($customer->billing_country)){{ $customer->billing_country }}@endif
        </div>
      </div>
      <div class="rc-addr-card" style="margin-left:auto;text-align:right">
        <div class="rc-addr-tag" style="justify-content:flex-end">{{ __('Company') }}</div>
        <div class="rc-addr-name">{{ $displayName }}</div>
        <div class="rc-addr-info">
          @if($companyPhone){{ $companyPhone }}<br>@endif
          @if($companyEmail){{ $companyEmail }}<br>@endif
          @if($companyAddr){{ $companyAddr }}@endif
        </div>
      </div>
    </div>

    <p class="rc-section-title">{{ __('Services') }}</p>
    <div class="rc-tbl-wrap">
      <table class="rc-tbl">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Client / Service') }}</th>
            <th>{{ __('Visa Type') }}</th>
            <th>{{ __('Country') }}</th>
            <th>{{ __('Amount') }}</th>
          </tr>
        </thead>
        <tbody>
          @if(!empty($invoice->itemData) && count($invoice->itemData) > 0)
            @foreach($invoice->itemData as $i => $item)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $item->name }}</td>
              <td>{{ $item->visa_label ?? '—' }}</td>
              <td>{{ $item->country_name ?? '—' }}</td>
              <td>{{ \App\Models\Utility::priceFormat($settings, ($item->price * $item->quantity) - ($item->discount ?? 0)) }}</td>
            </tr>
            @endforeach
          @else
            <tr class="rc-empty"><td colspan="5">{{ __('No services found for this party.') }}</td></tr>
          @endif
        </tbody>
      </table>
    </div>

    <div class="rc-totals-wrap">
      <div class="rc-totals">
        <div class="rc-tot-row"><span class="tl">{{ __('Subtotal') }}</span><span class="tv">{{ Utility::priceFormat($settings, $sumSub) }}</span></div>
        <div class="rc-tot-row paid"><span class="tl">{{ __('Paid') }}</span><span class="tv">{{ Utility::priceFormat($settings, $sumPaid) }}</span></div>
        <div class="rc-tot-row due"><span class="tl">{{ __('Due') }}</span><span class="tv">{{ Utility::priceFormat($settings, $sumDue) }}</span></div>
        @if($sumRef > 0)
        <div class="rc-tot-row ref"><span class="tl">{{ __('Refund') }}</span><span class="tv">{{ Utility::priceFormat($settings, $sumRef) }}</span></div>
        @endif
        <div class="rc-tot-row grand"><span class="tl">{{ __('Total') }}</span><span class="tv">{{ Utility::priceFormat($settings, $sumSub) }}</span></div>
      </div>
    </div>

    @if(!empty($noticeText))
    <div class="rc-notice">{{ $noticeText }}</div>
    @endif

    <div class="rc-sigs">
      <div class="rc-sig"><div class="rc-sig-line"></div><p>{{ __('Cashier Signature') }}</p></div>
      <div class="rc-sig"><div class="rc-sig-line"></div><p>{{ __('Manager Signature') }}</p></div>
      <div class="rc-sig"><div class="rc-sig-line"></div><p>{{ __('MD Signature & Seal') }}</p></div>
    </div>

    @if(!empty($footerText))
    <p style="text-align:center;font-size:11px;color:#64748b;margin-top:12px;">{{ $footerText }}</p>
    @endif
  </div>

  <div class="rc-footer">
    <div>
      <img src="{{ $img }}" alt="Logo">
      @if($companyAddr)<p style="margin-top:6px;">{{ $companyAddr }}</p>@endif
    </div>
    <div class="rc-footer-info">
      <p><strong>{{ $displayName }}</strong></p>
      @if($companyPhone)<p>{{ $companyPhone }}</p>@endif
      @if($companyEmail)<p>{{ $companyEmail }}</p>@endif
    </div>
  </div>
  <div class="rc-botbar"></div>
</div>
</body>
</html>
