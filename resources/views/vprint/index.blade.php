@extends('layouts.admin')

@section('page-title'){{ __('Print Receipt') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Print Receipt') }}</li>
@endsection

@php
    $agents   = DB::table('agents')->pluck('agent_name', 'id');
    $logo     = \App\Models\Utility::get_file('uploads/logo/');
    $settings = \App\Models\Utility::settings();

    $company_logo  = \App\Models\Utility::getValByName('company_logo_dark');
    $receipt_logo  = \App\Models\Utility::getValByName('receipt_logo');
    $invoice_logo  = \App\Models\Utility::getValByName('invoice_logo');
    if (!empty($receipt_logo)) {
        $logoSrc = \App\Models\Utility::printFileUrl('receipt_logo', $receipt_logo);
    } elseif (!empty($invoice_logo)) {
        $logoSrc = \App\Models\Utility::printFileUrl('invoice_logo', $invoice_logo);
    } else {
        $logoSrc = $logo . '/' . ($company_logo ?: 'logo-dark.png');
    }

    $inv_id = 'INV-' . strtoupper(\Str::random(8));
@endphp

@push('css-page')
<style>
/* ── Layout ── */
.vp-wrap { display:grid; grid-template-columns:340px 1fr; gap:24px; align-items:start; }
@media(max-width:1100px){ .vp-wrap{ grid-template-columns:1fr; } }

/* ── Form Panel ── */
.vp-panel { background:#fff; border:1px solid #e2e8f0; border-radius:20px; box-shadow:0 14px 35px rgba(15,23,42,.06); overflow:hidden; position:sticky; top:80px; }
.vp-panel-section { padding:18px 20px; border-bottom:1px solid #f1f5f9; }
.vp-panel-section:last-child { border-bottom:0; }
.vp-section-title { font-size:.7rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:14px; display:flex; align-items:center; gap:7px; }
.vp-section-title i { font-size:.9rem; }
.vp-label { font-size:.72rem; font-weight:700; color:#334155; margin-bottom:5px; display:block; }
.vp-input { width:100%; min-height:40px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:.84rem; padding:8px 12px; background:#f8fafc; color:#0f172a; transition:all .18s; outline:none; margin-bottom:10px; }
.vp-input:focus { border-color:#2563eb; background:#fff; box-shadow:0 0 0 3px rgba(37,99,235,.08); }
.vp-input:last-child { margin-bottom:0; }
.vp-select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:30px; }
.vp-print-btn { width:100%; min-height:46px; border-radius:12px; font-weight:800; font-size:.9rem; border:0; cursor:pointer; background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; box-shadow:0 10px 22px rgba(37,99,235,.25); display:flex; align-items:center; justify-content:center; gap:8px; transition:all .2s; margin:16px 20px; width:calc(100% - 40px); }
.vp-print-btn:hover { filter:brightness(1.06); transform:translateY(-1px); }
.vp-refund-row { display:flex; align-items:center; gap:8px; margin-bottom:10px; }
.vp-refund-row label { font-size:.82rem; color:#334155; font-weight:600; cursor:pointer; }

/* ── Preview Card ── */
.vp-preview-wrap { background:#f1f5f9; border-radius:20px; padding:20px; }
.vp-preview-toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px; }
.vp-preview-label { font-size:.78rem; font-weight:700; color:#64748b; display:flex; align-items:center; gap:6px; }
.vp-preview-dots { display:flex; gap:5px; }
.vp-dot { width:10px; height:10px; border-radius:50%; }
.vp-preview-actions { display:flex; gap:8px; }
.vp-action-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:10px; font-size:.8rem; font-weight:700; border:0; cursor:pointer; transition:all .18s; }
.vp-action-btn.print { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.22); }
.vp-action-btn.secondary { background:#fff; color:#475569; border:1.5px solid #e2e8f0; }
.vp-action-btn:hover { filter:brightness(1.05); transform:translateY(-1px); }

/* ── Receipt ── */
.vp-receipt { background:#fff; border-radius:16px; box-shadow:0 20px 60px rgba(15,23,42,.1); overflow:hidden; max-width:860px; margin:0 auto; }
.vp-receipt-header { background:linear-gradient(135deg,#1e3a8a,#1d4ed8,#2563eb); padding:24px 32px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
.vp-receipt-header img { height:48px; width:auto; object-fit:contain; filter:brightness(0) invert(1); }
.vp-receipt-title { text-align:right; }
.vp-receipt-title h1 { color:#fff; font-size:1.7rem; font-weight:900; letter-spacing:.06em; text-transform:uppercase; margin:0; }
.vp-receipt-title p { color:rgba(255,255,255,.7); font-size:.78rem; margin-top:3px; }

/* Meta strip */
.vp-meta { background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:12px 32px; display:flex; gap:28px; flex-wrap:wrap; }
.vp-meta-item .lbl { font-size:.65rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.07em; display:block; }
.vp-meta-item .val { font-size:.84rem; font-weight:700; color:#1e293b; }

/* Body */
.vp-receipt-body { padding:24px 32px; }
.vp-two-col { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
@media(max-width:600px){ .vp-two-col{ grid-template-columns:1fr; } }
.vp-info-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px 18px; }
.vp-info-box h6 { font-size:.65rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:10px; }
.vp-info-box p { font-size:.84rem; color:#334155; line-height:1.75; margin:0; }

/* Services table */
.vp-tbl-title { font-size:.65rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:10px; }
.vp-services-tbl { width:100%; border-collapse:separate; border-spacing:0; border-radius:12px; overflow:hidden; border:1px solid #e2e8f0; margin-bottom:20px; }
.vp-services-tbl thead th { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; padding:11px 14px; text-align:left; }
.vp-services-tbl tbody td { padding:12px 14px; font-size:.84rem; color:#334155; border-bottom:1px solid #f1f5f9; }
.vp-services-tbl tbody tr:last-child td { border-bottom:0; }

/* Totals */
.vp-totals-wrap { display:flex; justify-content:flex-end; margin-bottom:24px; }
.vp-totals { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px; min-width:260px; }
.vp-total-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:.84rem; }
.vp-total-row:last-child { border-bottom:0; padding-top:10px; }
.vp-total-row .tl { color:#64748b; font-weight:600; }
.vp-total-row .tv { font-weight:800; color:#0f172a; }
.vp-total-row.paid .tv { color:#059669; }
.vp-total-row.due  .tv { color:#e11d48; }
.vp-total-row.ref  .tv { color:#d97706; }

/* Notice */
.vp-notice { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:9px 14px; text-align:center; font-size:.75rem; color:#1d4ed8; font-weight:600; margin-bottom:20px; }

/* Signatures */
.vp-sigs { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
@media(max-width:500px){ .vp-sigs{ grid-template-columns:1fr; } }
.vp-sig { text-align:center; }
.vp-sig-line { height:1px; background:#cbd5e1; margin:0 16px 8px; }
.vp-sig p { font-size:.75rem; color:#64748b; font-weight:600; }

/* Footer */
.vp-receipt-footer { background:linear-gradient(135deg,#1e3a8a,#1d4ed8); padding:18px 32px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
.vp-receipt-footer img { height:36px; width:auto; object-fit:contain; filter:brightness(0) invert(1); }
.vp-footer-info { text-align:right; }
.vp-footer-info p { color:rgba(255,255,255,.8); font-size:.75rem; margin:2px 0; }

/* Print */
@media print {
    .vp-panel, .vp-preview-toolbar, .vp-preview-wrap > *:not(.vp-receipt) { display:none !important; }
    .vp-wrap { display:block !important; }
    .vp-preview-wrap { background:none !important; padding:0 !important; border-radius:0 !important; }
    .vp-receipt { border-radius:0 !important; box-shadow:none !important; max-width:100% !important; }
    body, html { background:#fff !important; }
}
</style>
@endpush

@section('content')

<div class="vp-wrap">

    {{-- ── LEFT: Form Panel ── --}}
    <div class="vp-panel">

        {{-- Company / Owner Details --}}
        <div class="vp-panel-section">
            <div class="vp-section-title"><i class="ti ti-building"></i> Company Details</div>
            <label class="vp-label">Company Name</label>
            <input type="text" id="co-name" class="vp-input" value="{{ $settings['company_name'] ?? '' }}" placeholder="Company name">
            <label class="vp-label">Address</label>
            <input type="text" id="co-address" class="vp-input" value="{{ $settings['company_address'] ?? '' }}" placeholder="Street address">
            <label class="vp-label">City / Country</label>
            <input type="text" id="co-city" class="vp-input" value="{{ ($settings['company_city'] ?? '') . (isset($settings['company_country']) && $settings['company_country'] ? ', '.$settings['company_country'] : '') }}" placeholder="City, Country">
            <label class="vp-label">Phone</label>
            <input type="text" id="co-phone" class="vp-input" value="{{ $settings['company_telephone'] ?? '' }}" placeholder="Phone number">
            <label class="vp-label">Email</label>
            <input type="text" id="co-email" class="vp-input" value="{{ $settings['mail_from_address'] ?? '' }}" placeholder="Email address">
        </div>

        {{-- Client / Agent Details --}}
        <div class="vp-panel-section">
            <div class="vp-section-title"><i class="ti ti-user"></i> Client / Agent Details</div>
            <label class="vp-label">Select Agent</label>
            <select id="select-agent" class="vp-input vp-select">
                <option value="">— Select Agent —</option>
                @foreach($agents as $key => $agent)
                    <option value="{{ $agent }}">{{ $agent }}</option>
                @endforeach
            </select>
            <label class="vp-label">Client Name</label>
            <input type="text" id="cl-name" class="vp-input" placeholder="Full name">
            <label class="vp-label">Phone</label>
            <input type="text" id="cl-phone" class="vp-input" placeholder="Phone number">
            <label class="vp-label">Address</label>
            <input type="text" id="cl-address" class="vp-input" placeholder="Client address">
            <label class="vp-label">Date</label>
            <input type="date" id="cl-date" class="vp-input" value="{{ date('Y-m-d') }}">
        </div>

        {{-- Ledger Info --}}
        <div class="vp-panel-section">
            <div class="vp-section-title"><i class="ti ti-receipt"></i> Ledger Info</div>
            <label class="vp-label">Visa Type</label>
            <select id="visa-type" class="vp-input vp-select">
                <option value="">— Select Visa Type —</option>
                <option value="Work Permit Visa">Work Permit Visa</option>
                <option value="Tourist Visa">Tourist Visa</option>
                <option value="Student Visa">Student Visa</option>
                <option value="Business Visa">Business Visa</option>
                <option value="Other Visa">Other Visa</option>
            </select>
            <label class="vp-label">Unit Price</label>
            <input type="number" id="unit-price" class="vp-input" placeholder="0.00" step="0.01">
            <label class="vp-label">Quantity</label>
            <input type="number" id="total-unit" class="vp-input" placeholder="1" value="1">
            <label class="vp-label">Advance Paid</label>
            <input type="number" id="advanced" class="vp-input" placeholder="0.00" step="0.01">
            <label class="vp-label">Due Amount</label>
            <input type="number" id="due" class="vp-input" placeholder="Auto-calculated" readonly style="background:#fef2f2;color:#e11d48;">
            <label class="vp-label">Payment Mode</label>
            <select id="payment-mode" class="vp-input vp-select">
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Bkash">Bkash</option>
                <option value="Nagad">Nagad</option>
                <option value="Rocket">Rocket</option>
                <option value="Card">Card</option>
            </select>
            <div class="vp-refund-row">
                <input type="checkbox" id="hasRefund" style="width:16px;height:16px;cursor:pointer;">
                <label for="hasRefund">Agent claimed a refund</label>
            </div>
            <input type="number" id="refund" class="vp-input" placeholder="Refund amount" disabled style="opacity:.5;">
        </div>

        <button class="vp-print-btn" onclick="window.print()">
            <i class="ti ti-printer"></i> Print Receipt
        </button>
    </div>

    {{-- ── RIGHT: Live Preview ── --}}
    <div class="vp-preview-wrap">
        <div class="vp-preview-toolbar">
            <div class="vp-preview-label">
                <div class="vp-preview-dots">
                    <span class="vp-dot" style="background:#ef4444;"></span>
                    <span class="vp-dot" style="background:#f59e0b;"></span>
                    <span class="vp-dot" style="background:#22c55e;"></span>
                </div>
                Live Preview — updates as you type
            </div>
            <div class="vp-preview-actions">
                <button class="vp-action-btn print" onclick="window.print()">
                    <i class="ti ti-printer"></i> Print
                </button>
            </div>
        </div>

        {{-- Receipt --}}
        <div class="vp-receipt" id="this-pdf">

            {{-- Header --}}
            <div class="vp-receipt-header">
                <img src="{{ $logoSrc }}" alt="Logo" id="receipt-logo">
                <div class="vp-receipt-title">
                    <h1>Money Receipt</h1>
                    <p id="preview-co-name">{{ $settings['company_name'] ?? '' }}</p>
                </div>
            </div>

            {{-- Meta --}}
            <div class="vp-meta">
                <div class="vp-meta-item">
                    <span class="lbl">Receipt No</span>
                    <span class="val">{{ $inv_id }}</span>
                </div>
                <div class="vp-meta-item">
                    <span class="lbl">Date</span>
                    <span class="val" id="preview-date">{{ date('d M Y') }}</span>
                </div>
                <div class="vp-meta-item">
                    <span class="lbl">Agent</span>
                    <span class="val" id="preview-agent">—</span>
                </div>
                <div class="vp-meta-item">
                    <span class="lbl">Payment</span>
                    <span class="val" id="preview-payment">Cash</span>
                </div>
            </div>

            <div class="vp-receipt-body">

                {{-- Bill To / Company --}}
                <div class="vp-two-col">
                    <div class="vp-info-box">
                        <h6>Bill To (Client / Agent)</h6>
                        <p>
                            <strong id="preview-cl-name">—</strong><br>
                            <span id="preview-cl-phone"></span><br>
                            <span id="preview-cl-address"></span>
                        </p>
                    </div>
                    <div class="vp-info-box">
                        <h6>From (Company)</h6>
                        <p>
                            <strong id="preview-co-name2">{{ $settings['company_name'] ?? '' }}</strong><br>
                            <span id="preview-co-address">{{ $settings['company_address'] ?? '' }}</span><br>
                            <span id="preview-co-city">{{ ($settings['company_city'] ?? '') . (isset($settings['company_country']) && $settings['company_country'] ? ', '.$settings['company_country'] : '') }}</span><br>
                            <span id="preview-co-phone">{{ $settings['company_telephone'] ?? '' }}</span><br>
                            <span id="preview-co-email">{{ $settings['mail_from_address'] ?? '' }}</span>
                        </p>
                    </div>
                </div>

                {{-- Services Table --}}
                <p class="vp-tbl-title">Services</p>
                <table class="vp-services-tbl">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Visa Type</th>
                            <th style="text-align:right;">Unit Price</th>
                            <th style="text-align:right;">Qty</th>
                            <th style="text-align:right;">Total</th>
                            <th style="text-align:right;">Advance</th>
                            <th style="text-align:right;">Due</th>
                            <th>Paid Via</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td id="preview-visa">—</td>
                            <td style="text-align:right;" id="preview-unit-price">0.00</td>
                            <td style="text-align:right;" id="preview-qty">1</td>
                            <td style="text-align:right;" id="preview-total">0.00</td>
                            <td style="text-align:right;" id="preview-advance">0.00</td>
                            <td style="text-align:right;color:#e11d48;font-weight:700;" id="preview-due-td">0.00</td>
                            <td id="preview-payment2">Cash</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Totals --}}
                <div class="vp-totals-wrap">
                    <div class="vp-totals">
                        <div class="vp-total-row paid">
                            <span class="tl">Total Paid</span>
                            <span class="tv" id="preview-total-paid">৳0</span>
                        </div>
                        <div class="vp-total-row due">
                            <span class="tl">Total Due</span>
                            <span class="tv" id="preview-total-due">৳0</span>
                        </div>
                        <div class="vp-total-row ref">
                            <span class="tl">Refund</span>
                            <span class="tv" id="preview-total-refund">৳0</span>
                        </div>
                    </div>
                </div>

                {{-- Notice --}}
                <div class="vp-notice">
                    Money receipts will not be considered valid without the MD's seal and signature.
                </div>

                {{-- Signatures --}}
                <div class="vp-sigs">
                    <div class="vp-sig"><div class="vp-sig-line"></div><p>Cashier Signature</p></div>
                    <div class="vp-sig"><div class="vp-sig-line"></div><p>Manager Signature</p></div>
                    <div class="vp-sig"><div class="vp-sig-line"></div><p>MD Signature & Seal</p></div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="vp-receipt-footer">
                <div>
                    <img src="{{ $logoSrc }}" alt="Logo">
                    <p style="color:rgba(255,255,255,.7);font-size:.72rem;margin-top:5px;" id="preview-footer-addr">{{ $settings['company_address'] ?? '' }}</p>
                </div>
                <div class="vp-footer-info">
                    <p><strong style="color:#fff;" id="preview-footer-name">{{ $settings['company_name'] ?? '' }}</strong></p>
                    <p id="preview-footer-phone">{{ $settings['company_telephone'] ?? '' }}</p>
                    <p id="preview-footer-email">{{ $settings['mail_from_address'] ?? '' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script-page')
<script>
function fmt(n) {
    return '৳' + (parseFloat(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}
function recalc() {
    var up  = parseFloat($('#unit-price').val()) || 0;
    var qty = parseFloat($('#total-unit').val()) || 1;
    var adv = parseFloat($('#advanced').val()) || 0;
    var ref = parseFloat($('#refund').val()) || 0;
    var total = up * qty;
    var due   = Math.max(0, total - adv);

    $('#due').val(due.toFixed(2));

    $('#preview-unit-price').text(fmt(up));
    $('#preview-qty').text(qty);
    $('#preview-total').text(fmt(total));
    $('#preview-advance').text(fmt(adv));
    $('#preview-due-td').text(fmt(due));
    $('#preview-total-paid').text(fmt(adv));
    $('#preview-total-due').text(fmt(due));
    $('#preview-total-refund').text(fmt(ref));
}

$(document).ready(function(){

    // Company fields → preview
    $('#co-name').on('input', function(){ $('#preview-co-name, #preview-co-name2, #preview-footer-name').text($(this).val()||'—'); });
    $('#co-address').on('input', function(){ $('#preview-co-address, #preview-footer-addr').text($(this).val()); });
    $('#co-city').on('input', function(){ $('#preview-co-city').text($(this).val()); });
    $('#co-phone').on('input', function(){ $('#preview-co-phone, #preview-footer-phone').text($(this).val()); });
    $('#co-email').on('input', function(){ $('#preview-co-email, #preview-footer-email').text($(this).val()); });

    // Client fields → preview
    $('#select-agent').on('change', function(){
        var name = $(this).val() || '—';
        $('#preview-agent').text(name);
        if (!$('#cl-name').val()) $('#preview-cl-name').text(name);
    });
    $('#cl-name').on('input', function(){ $('#preview-cl-name').text($(this).val()||'—'); });
    $('#cl-phone').on('input', function(){ $('#preview-cl-phone').text($(this).val()); });
    $('#cl-address').on('input', function(){ $('#preview-cl-address').text($(this).val()); });
    $('#cl-date').on('change', function(){
        var d = new Date($(this).val());
        var opts = {day:'2-digit', month:'short', year:'numeric'};
        $('#preview-date').text(d.toLocaleDateString('en-GB', opts));
    });

    // Ledger fields → preview
    $('#visa-type').on('change', function(){ $('#preview-visa').text($(this).val()||'—'); });
    $('#payment-mode').on('change', function(){ $('#preview-payment, #preview-payment2').text($(this).val()); });
    $('#unit-price, #total-unit, #advanced').on('input', recalc);

    // Refund toggle
    $('#hasRefund').on('change', function(){
        $('#refund').prop('disabled', !$(this).is(':checked')).css('opacity', $(this).is(':checked') ? 1 : 0.5);
        if (!$(this).is(':checked')) { $('#refund').val(''); recalc(); }
    });
    $('#refund').on('input', recalc);

    // Init
    recalc();
});
</script>
@endpush

@endsection
