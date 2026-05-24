@extends('layouts.admin')
@section('page-title'){{ __('Print Settings') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Print Settings') }}</li>
@endsection

@push('script-page')
<script>
// Toggle VAT field visibility
function toggleVat(el) {
    document.getElementById('vat-fields').style.display = el.checked ? 'block' : 'none';
}
// Live preview update on template/color change
$(document).on("change", "select[name='proposal_template'], input[name='proposal_color']", function () {
    var template = $("select[name='proposal_template']").val();
    var color    = $("input[name='proposal_color']:checked").val() || 'ffffff';
    $('#proposal_frame').attr('src', '{{ url('/proposal/preview') }}/' + template + '/' + color);
});
$(document).on("change", "select[name='invoice_template'], input[name='invoice_color']", function () {
    var template = $("select[name='invoice_template']").val();
    var color    = $("input[name='invoice_color']:checked").val() || 'ffffff';
    $('#invoice_frame').attr('src', '{{ url('/invoices/preview') }}/' + template + '/' + color);
});
$(document).on("change", "select[name='bill_template'], input[name='bill_color']", function () {
    var template = $("select[name='bill_template']").val();
    var color    = $("input[name='bill_color']:checked").val() || 'ffffff';
    $('#bill_frame').attr('src', '{{ url('/bill/preview') }}/' + template + '/' + color);
});

// Logo preview on file select
['proposal','invoice','bill'].forEach(function(type) {
    var input = document.getElementById(type + '_logo');
    var img   = document.getElementById(type + '_image');
    if (input && img) {
        input.addEventListener('change', function() {
            if (this.files[0]) {
                img.src = URL.createObjectURL(this.files[0]);
                img.style.display = 'block';
            }
        });
    }
});

function switchTab(tab, btn) {
    document.querySelectorAll('.ps-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.ps-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('pane-' + tab).classList.add('active');
    btn.classList.add('active');
}

function toggleCompanyInfo(header) {
    var body = document.getElementById('companyInfoBody');
    var icon = header.querySelector('.ps-company-toggle-icon');
    var isOpen = body.classList.contains('open');
    body.classList.toggle('open', !isOpen);
    icon.classList.toggle('open', !isOpen);
}

// ── Generic section toggle ──
function toggleSection(bodyId, header) {
    var body = document.getElementById(bodyId);
    var icon = header.querySelector('.ps-company-toggle-icon');
    var isOpen = body.classList.contains('open');
    body.classList.toggle('open', !isOpen);
    icon.classList.toggle('open', !isOpen);
}

// ── Recipient Address ──
var _customerOptions = @json($customers->map(fn($c) => ['id'=>$c->id,'name'=>$c->name,'email'=>$c->email??'']));
var _vendorOptions   = @json($vendors->map(fn($v) => ['id'=>$v->id,'name'=>$v->name,'email'=>$v->email??'']));

function clearRecipientForm() {
    document.getElementById('recipientSelect').innerHTML = '<option value="">— {{ __("Choose one") }} —</option>';
    var type = document.getElementById('recipientType').value;
    var list = type === 'customer' ? _customerOptions : _vendorOptions;
    list.forEach(function(item) {
        var opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.name + (item.email ? ' (' + item.email + ')' : '');
        document.getElementById('recipientSelect').appendChild(opt);
    });
    document.getElementById('recipientForm').style.display = 'none';
}

function loadRecipient(id) {
    if (!id) { document.getElementById('recipientForm').style.display = 'none'; return; }
    var type = document.getElementById('recipientType').value;
    fetch('{{ route("print.recipient.data") }}?type=' + type + '&id=' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.error) { alert(data.error); return; }
        var fields = ['billing_name','billing_phone','billing_address','billing_city','billing_state','billing_zip','billing_country',
                      'shipping_name','shipping_phone','shipping_address','shipping_city','shipping_state','shipping_zip','shipping_country'];
        fields.forEach(function(f) {
            var el = document.getElementById(f);
            if (el) el.value = data[f] || '';
        });
        document.getElementById('recipientForm').style.display = 'block';
        document.getElementById('recipientMsg').style.display = 'none';
    })
    .catch(function() { alert('{{ __("Failed to load data.") }}'); });
}

function copyBillingToShipping() {
    var map = {
        'billing_name':'shipping_name', 'billing_phone':'shipping_phone',
        'billing_address':'shipping_address', 'billing_city':'shipping_city',
        'billing_state':'shipping_state', 'billing_zip':'shipping_zip',
        'billing_country':'shipping_country'
    };
    Object.keys(map).forEach(function(from) {
        var el = document.getElementById(map[from]);
        if (el) el.value = document.getElementById(from).value;
    });
}

function saveRecipientAddress() {
    var id   = document.getElementById('recipientSelect').value;
    var type = document.getElementById('recipientType').value;
    if (!id) return;

    var fields = ['billing_name','billing_phone','billing_address','billing_city','billing_state','billing_zip','billing_country',
                  'shipping_name','shipping_phone','shipping_address','shipping_city','shipping_state','shipping_zip','shipping_country'];
    var body = new FormData();
    body.append('_token', '{{ csrf_token() }}');
    body.append('id', id);
    body.append('type', type);
    fields.forEach(function(f) {
        var el = document.getElementById(f);
        body.append(f, el ? el.value : '');
    });

    var btn = document.getElementById('recipientSaveBtn');
    var msg = document.getElementById('recipientMsg');
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader"></i> {{ __("Saving...") }}';

    fetch('{{ route("print.recipient.update") }}', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: body
    })
    .then(r => r.json())
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-device-floppy"></i> {{ __("Save Address") }}';
        if (data.success) {
            msg.style.display = 'inline';
            msg.style.color = '#059669';
            msg.textContent = '✓ ' + data.message;
            setTimeout(function() { msg.style.display = 'none'; }, 3000);
        } else {
            msg.style.display = 'inline';
            msg.style.color = '#dc2626';
            msg.textContent = data.error || '{{ __("Error saving.") }}';
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-device-floppy"></i> {{ __("Save Address") }}';
        alert('{{ __("Request failed.") }}');
    });
}
</script>
@endpush

@section('content')
<style>
/* ─── Root Variables ─── */
:root {
    --c-bg:      #f0f4f8;
    --c-white:   #ffffff;
    --c-border:  #e2e8f0;
    --c-dark:    #0f172a;
    --c-muted:   #64748b;
    --c-blue:    #2563eb;
    --c-blue-lt: #eff6ff;
    --c-green:   #059669;
    --c-purple:  #7c3aed;
    --radius-lg: 18px;
    --radius-md: 12px;
    --shadow-sm: 0 2px 8px rgba(15,23,42,.07);
    --shadow-md: 0 8px 28px rgba(15,23,42,.10);
}

/* ─── Page Wrapper ─── */
.ps-page { padding: 4px 0 32px; }

/* ─── Tab Navigation ─── */
.ps-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 24px;
    background: var(--c-white);
    padding: 8px;
    border-radius: var(--radius-lg);
    border: 1px solid var(--c-border);
    box-shadow: var(--shadow-sm);
}
.ps-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: var(--radius-md);
    font-size: .84rem;
    font-weight: 700;
    border: none;
    background: transparent;
    color: var(--c-muted);
    cursor: pointer;
    transition: all .18s ease;
    letter-spacing: .01em;
}
.ps-tab:hover { background: var(--c-bg); color: var(--c-dark); }
.ps-tab.active {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    box-shadow: 0 6px 18px rgba(37,99,235,.28);
}
.ps-tab i { font-size: 1rem; }

/* ─── Tab Panes ─── */
.ps-pane { display: none; }
.ps-pane.active { display: block; }

/* ─── Two-column layout ─── */
.ps-grid {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 1024px) { .ps-grid { grid-template-columns: 1fr; } }

/* ─── Settings Card ─── */
.ps-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}
.ps-card-head {
    padding: 18px 22px;
    border-bottom: 1px solid var(--c-border);
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    display: flex;
    align-items: center;
    gap: 12px;
}
.ps-card-icon {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem; color: #fff;
    flex-shrink: 0;
}
.ps-card-icon.blue   { background: linear-gradient(135deg,#3b82f6,#2563eb); box-shadow: 0 6px 14px rgba(37,99,235,.25); }
.ps-card-icon.green  { background: linear-gradient(135deg,#34d399,#059669); box-shadow: 0 6px 14px rgba(5,150,105,.25); }
.ps-card-icon.purple { background: linear-gradient(135deg,#a78bfa,#7c3aed); box-shadow: 0 6px 14px rgba(124,58,237,.25); }
.ps-card-title { font-size: .95rem; font-weight: 800; color: var(--c-dark); margin: 0; line-height: 1.2; }
.ps-card-sub   { font-size: .73rem; color: var(--c-muted); margin: 2px 0 0; }
.ps-card-body  { padding: 22px; }

/* ─── Form Labels ─── */
.ps-label {
    display: block;
    font-size: .7rem;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 8px;
}

/* ─── Select ─── */
.ps-select {
    width: 100%;
    min-height: 46px;
    border: 1.5px solid var(--c-border);
    border-radius: var(--radius-md);
    font-size: .88rem;
    padding: 10px 38px 10px 14px;
    background: #f8fafc;
    color: var(--c-dark);
    transition: all .2s;
    outline: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 13px center;
    cursor: pointer;
}
.ps-select:focus { border-color: var(--c-blue); background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }

/* ─── Color Swatches ─── */
.ps-swatches { display: flex; flex-wrap: wrap; gap: 7px; }
.ps-swatch-label { cursor: pointer; position: relative; }
.ps-swatch-label input { position: absolute; opacity: 0; width: 0; height: 0; }
.ps-swatch {
    width: 26px; height: 26px;
    border-radius: 7px;
    display: block;
    border: 2.5px solid transparent;
    transition: all .15s;
    box-shadow: 0 2px 5px rgba(15,23,42,.12);
}
.ps-swatch-label input:checked + .ps-swatch {
    border-color: #0f172a;
    transform: scale(1.2);
    box-shadow: 0 4px 12px rgba(15,23,42,.3);
}
.ps-swatch:hover { transform: scale(1.12); }

/* ─── Logo Upload ─── */
.ps-upload {
    position: relative;
    border: 2px dashed var(--c-border);
    border-radius: var(--radius-md);
    padding: 20px 16px;
    text-align: center;
    background: #f8fafc;
    cursor: pointer;
    transition: all .2s;
    overflow: hidden;
}
.ps-upload:hover { border-color: var(--c-blue); background: var(--c-blue-lt); }
.ps-upload input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.ps-upload-icon { font-size: 1.6rem; color: #94a3b8; display: block; margin-bottom: 6px; }
.ps-upload-text { font-size: .78rem; color: var(--c-muted); font-weight: 600; }
.ps-upload-hint { font-size: .68rem; color: #94a3b8; margin-top: 3px; }
.ps-logo-preview { width: 90px; height: 45px; object-fit: contain; border-radius: 8px; margin-top: 12px; display: none; border: 1px solid var(--c-border); padding: 4px; }

/* ─── Current Logo Badge ─── */
.ps-current-logo {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    margin-bottom: 10px;
}
.ps-current-logo img { height: 34px; width: auto; object-fit: contain; border-radius: 6px; }
.ps-current-logo span { font-size: .73rem; color: var(--c-green); font-weight: 700; }

/* ─── Save Button ─── */
.ps-btn {
    width: 100%;
    min-height: 46px;
    border-radius: var(--radius-md);
    font-weight: 800;
    font-size: .88rem;
    border: 0;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all .2s;
    margin-top: 22px;
    letter-spacing: .02em;
}
.ps-btn.blue   { background: linear-gradient(135deg,#3b82f6,#2563eb); color: #fff; box-shadow: 0 8px 20px rgba(37,99,235,.25); }
.ps-btn.green  { background: linear-gradient(135deg,#34d399,#059669); color: #fff; box-shadow: 0 8px 20px rgba(5,150,105,.25); }
.ps-btn.purple { background: linear-gradient(135deg,#a78bfa,#7c3aed); color: #fff; box-shadow: 0 8px 20px rgba(124,58,237,.25); }
.ps-btn:hover  { filter: brightness(1.06); transform: translateY(-1px); }
.ps-btn:active { transform: translateY(0); }

/* ─── Preview Card ─── */
.ps-preview {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}
.ps-preview-bar {
    padding: 12px 18px;
    border-bottom: 1px solid var(--c-border);
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    display: flex; align-items: center; gap: 7px;
}
.ps-dot { width: 11px; height: 11px; border-radius: 50%; }
.ps-preview-label { font-size: .75rem; color: var(--c-muted); font-weight: 700; margin-left: 6px; }
.ps-iframe { width: 100%; height: 700px; border: 0; display: block; background: #f8fafc; }

/* ─── Divider ─── */
.ps-divider { height: 1px; background: var(--c-border); margin: 18px 0; }

/* ─── Section spacing ─── */
.ps-field { margin-bottom: 20px; }
.ps-field:last-child { margin-bottom: 0; }

/* ─── Company Info Card ─── */
.ps-company-card {
    background: var(--c-white);
    border: 1px solid var(--c-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-bottom: 20px;
}
.ps-company-head {
    padding: 16px 22px;
    border-bottom: 1px solid var(--c-border);
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
}
.ps-company-head-left { display: flex; align-items: center; gap: 12px; }
.ps-company-toggle-icon { transition: transform .2s; font-size: .85rem; color: var(--c-muted); }
.ps-company-toggle-icon.open { transform: rotate(180deg); }
.ps-company-body { padding: 22px; display: none; }
.ps-company-body.open { display: block; }
.ps-input {
    width: 100%;
    min-height: 42px;
    border: 1.5px solid var(--c-border);
    border-radius: var(--radius-md);
    font-size: .88rem;
    padding: 9px 13px;
    background: #f8fafc;
    color: var(--c-dark);
    transition: all .2s;
    outline: none;
    font-family: inherit;
}
.ps-input:focus { border-color: var(--c-blue); background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.ps-input-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
@media (max-width: 600px) { .ps-input-grid { grid-template-columns: 1fr; } }
.ps-toggle-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
}
.ps-toggle-row label { font-size: .84rem; font-weight: 600; color: var(--c-dark); cursor: pointer; }
.ps-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
.ps-switch input { opacity: 0; width: 0; height: 0; }
.ps-slider {
    position: absolute; inset: 0;
    background: #cbd5e1; border-radius: 22px;
    cursor: pointer; transition: .2s;
}
.ps-slider:before {
    content: ''; position: absolute;
    width: 16px; height: 16px; border-radius: 50%;
    background: white; left: 3px; top: 3px;
    transition: .2s; box-shadow: 0 1px 4px rgba(0,0,0,.2);
}
.ps-switch input:checked + .ps-slider { background: var(--c-blue); }
.ps-switch input:checked + .ps-slider:before { transform: translateX(18px); }
.ps-save-sm {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 22px;
    border-radius: var(--radius-md);
    font-weight: 700; font-size: .85rem;
    border: 0; cursor: pointer;
    background: linear-gradient(135deg,#3b82f6,#2563eb);
    color: #fff;
    box-shadow: 0 6px 16px rgba(37,99,235,.22);
    transition: all .2s;
    margin-top: 18px;
}
.ps-save-sm:hover { filter: brightness(1.06); transform: translateY(-1px); }
</style>

<div class="ps-page">

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- COMPANY INFO CARD (collapsible) --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="ps-company-card">
        <div class="ps-company-head" onclick="toggleSection('companyInfoBody', this)">
            <div class="ps-company-head-left">
                <div class="ps-card-icon blue" style="width:36px;height:36px;font-size:.95rem;">
                    <i class="ti ti-building"></i>
                </div>
                <div>
                    <p class="ps-card-title">{{ __('Company Info') }}</p>
                    <p class="ps-card-sub">{{ __('Address & details shown on all print documents') }}</p>
                </div>
            </div>
            <i class="ti ti-chevron-down ps-company-toggle-icon"></i>
        </div>
        <div class="ps-company-body" id="companyInfoBody">
            <form method="post" action="{{ route('company.settings') }}" enctype="multipart/form-data">
                @csrf

                <div class="ps-field">
                    <label class="ps-label">{{ __('Company Name') }}</label>
                    <input type="text" name="company_name" class="ps-input"
                        value="{{ $settings['company_name'] ?? '' }}"
                        placeholder="{{ __('Your Company Name') }}" required>
                </div>

                <div class="ps-field">
                    <label class="ps-label">{{ __('Email Address') }}</label>
                    <input type="email" name="mail_from_address" class="ps-input"
                        value="{{ $settings['mail_from_address'] ?? '' }}"
                        placeholder="info@company.com">
                </div>

                <div class="ps-field">
                    <label class="ps-label">{{ __('Phone / Telephone') }}</label>
                    <input type="text" name="company_telephone" class="ps-input"
                        value="{{ $settings['company_telephone'] ?? '' }}"
                        placeholder="+1 234 567 8900">
                </div>

                <div class="ps-field">
                    <label class="ps-label">{{ __('Street Address') }}</label>
                    <input type="text" name="company_address" class="ps-input"
                        value="{{ $settings['company_address'] ?? '' }}"
                        placeholder="{{ __('Street address') }}">
                </div>

                <div class="ps-input-grid ps-field">
                    <div>
                        <label class="ps-label">{{ __('City') }}</label>
                        <input type="text" name="company_city" class="ps-input"
                            value="{{ $settings['company_city'] ?? '' }}"
                            placeholder="{{ __('City') }}">
                    </div>
                    <div>
                        <label class="ps-label">{{ __('State / Province') }}</label>
                        <input type="text" name="company_state" class="ps-input"
                            value="{{ $settings['company_state'] ?? '' }}"
                            placeholder="{{ __('State') }}">
                    </div>
                    <div>
                        <label class="ps-label">{{ __('ZIP / Postal Code') }}</label>
                        <input type="text" name="company_zipcode" class="ps-input"
                            value="{{ $settings['company_zipcode'] ?? '' }}"
                            placeholder="10001">
                    </div>
                    <div>
                        <label class="ps-label">{{ __('Country') }}</label>
                        <input type="text" name="company_country" class="ps-input"
                            value="{{ $settings['company_country'] ?? '' }}"
                            placeholder="{{ __('Country') }}">
                    </div>
                </div>

                <div class="ps-divider"></div>

                <div class="ps-field">
                    <label class="ps-label">{{ __('Registration Number') }}</label>
                    <input type="text" name="registration_number" class="ps-input"
                        value="{{ $settings['registration_number'] ?? '' }}"
                        placeholder="{{ __('Business registration number') }}">
                </div>

                {{-- VAT / GST Toggle --}}
                <div class="ps-toggle-row ps-field">
                    <label class="ps-switch">
                        <input type="checkbox" name="vat_gst_number_switch" value="on"
                            {{ (isset($settings['vat_gst_number_switch']) && $settings['vat_gst_number_switch'] == 'on') ? 'checked' : '' }}
                            onchange="toggleVat(this)">
                        <span class="ps-slider"></span>
                    </label>
                    <label>{{ __('Show VAT / GST Number on documents') }}</label>
                </div>

                <div id="vat-fields" style="{{ (isset($settings['vat_gst_number_switch']) && $settings['vat_gst_number_switch'] == 'on') ? '' : 'display:none;' }}">
                    <div class="ps-input-grid ps-field">
                        <div>
                            <label class="ps-label">{{ __('Tax Type') }} <small style="text-transform:none;font-weight:500;">(e.g. VAT, GST)</small></label>
                            <input type="text" name="tax_type" class="ps-input"
                                value="{{ $settings['tax_type'] ?? '' }}"
                                placeholder="VAT">
                        </div>
                        <div>
                            <label class="ps-label">{{ __('VAT / GST Number') }}</label>
                            <input type="text" name="vat_number" class="ps-input"
                                value="{{ $settings['vat_number'] ?? '' }}"
                                placeholder="GB123456789">
                        </div>
                    </div>
                </div>

                <div class="ps-divider"></div>

                <div class="ps-field">
                    <label class="ps-label">{{ __('Footer Title') }}</label>
                    <input type="text" name="footer_title" class="ps-input"
                        value="{{ $settings['footer_title'] ?? '' }}"
                        placeholder="{{ __('e.g. Thank you for your business!') }}">
                </div>

                <div class="ps-field">
                    <label class="ps-label">{{ __('Footer Notes') }}</label>
                    <textarea name="footer_notes" class="ps-input" rows="3"
                        placeholder="{{ __('Payment terms, bank details, or any notes...') }}" style="height:auto;resize:vertical;">{{ $settings['footer_notes'] ?? '' }}</textarea>
                </div>

                <button type="submit" class="ps-save-sm">
                    <i class="ti ti-device-floppy"></i> {{ __('Save Company Info') }}
                </button>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- RECIPIENT ADDRESS CARD (collapsible) --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="ps-company-card">
        <div class="ps-company-head" onclick="toggleSection('recipientBody', this)">
            <div class="ps-company-head-left">
                <div class="ps-card-icon" style="width:36px;height:36px;font-size:.95rem;background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 6px 14px rgba(217,119,6,.25);">
                    <i class="ti ti-user-circle"></i>
                </div>
                <div>
                    <p class="ps-card-title">{{ __('Recipient Address') }}</p>
                    <p class="ps-card-sub">{{ __('Edit billing & shipping address of any customer or vendor') }}</p>
                </div>
            </div>
            <i class="ti ti-chevron-down ps-company-toggle-icon"></i>
        </div>
        <div class="ps-company-body" id="recipientBody">

            {{-- Type + Search Row --}}
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;align-items:flex-end;">
                <div style="flex:0 0 160px;">
                    <label class="ps-label">{{ __('Type') }}</label>
                    <select id="recipientType" class="ps-select" onchange="clearRecipientForm()">
                        <option value="customer">{{ __('Customer') }}</option>
                        <option value="vendor">{{ __('Vendor') }}</option>
                    </select>
                </div>
                <div style="flex:1;min-width:200px;">
                    <label class="ps-label">{{ __('Select Customer / Vendor') }}</label>
                    <select id="recipientSelect" class="ps-select" onchange="loadRecipient(this.value)">
                        <option value="">— {{ __('Choose one') }} —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-type="customer">{{ $c->name }}@if($c->email) ({{ $c->email }})@endif</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Address Form (hidden until a recipient is selected) --}}
            <div id="recipientForm" style="display:none;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                    {{-- Billing --}}
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                            <div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#3b82f6,#2563eb);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;flex-shrink:0;">
                                <i class="ti ti-map-pin"></i>
                            </div>
                            <span style="font-size:.82rem;font-weight:800;color:#0f172a;text-transform:uppercase;letter-spacing:.07em;">{{ __('Billing Address') }}</span>
                        </div>
                        <div class="ps-field">
                            <label class="ps-label">{{ __('Full Name') }}</label>
                            <input type="text" id="billing_name" class="ps-input" placeholder="{{ __('Recipient name') }}">
                        </div>
                        <div class="ps-field">
                            <label class="ps-label">{{ __('Phone') }}</label>
                            <input type="text" id="billing_phone" class="ps-input" placeholder="+1 234 567 8900">
                        </div>
                        <div class="ps-field">
                            <label class="ps-label">{{ __('Street Address') }}</label>
                            <textarea id="billing_address" class="ps-input" rows="2" style="height:auto;resize:vertical;" placeholder="{{ __('Street address') }}"></textarea>
                        </div>
                        <div class="ps-input-grid ps-field">
                            <div>
                                <label class="ps-label">{{ __('City') }}</label>
                                <input type="text" id="billing_city" class="ps-input" placeholder="{{ __('City') }}">
                            </div>
                            <div>
                                <label class="ps-label">{{ __('State') }}</label>
                                <input type="text" id="billing_state" class="ps-input" placeholder="{{ __('State') }}">
                            </div>
                            <div>
                                <label class="ps-label">{{ __('ZIP Code') }}</label>
                                <input type="text" id="billing_zip" class="ps-input" placeholder="10001">
                            </div>
                            <div>
                                <label class="ps-label">{{ __('Country') }}</label>
                                <input type="text" id="billing_country" class="ps-input" placeholder="{{ __('Country') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Shipping --}}
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#34d399,#059669);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;flex-shrink:0;">
                                    <i class="ti ti-truck"></i>
                                </div>
                                <span style="font-size:.82rem;font-weight:800;color:#0f172a;text-transform:uppercase;letter-spacing:.07em;">{{ __('Shipping Address') }}</span>
                            </div>
                            <button type="button" onclick="copyBillingToShipping()" style="font-size:.72rem;font-weight:700;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:4px 10px;cursor:pointer;">
                                <i class="ti ti-copy"></i> {{ __('Same as Billing') }}
                            </button>
                        </div>
                        <div class="ps-field">
                            <label class="ps-label">{{ __('Full Name') }}</label>
                            <input type="text" id="shipping_name" class="ps-input" placeholder="{{ __('Recipient name') }}">
                        </div>
                        <div class="ps-field">
                            <label class="ps-label">{{ __('Phone') }}</label>
                            <input type="text" id="shipping_phone" class="ps-input" placeholder="+1 234 567 8900">
                        </div>
                        <div class="ps-field">
                            <label class="ps-label">{{ __('Street Address') }}</label>
                            <textarea id="shipping_address" class="ps-input" rows="2" style="height:auto;resize:vertical;" placeholder="{{ __('Street address') }}"></textarea>
                        </div>
                        <div class="ps-input-grid ps-field">
                            <div>
                                <label class="ps-label">{{ __('City') }}</label>
                                <input type="text" id="shipping_city" class="ps-input" placeholder="{{ __('City') }}">
                            </div>
                            <div>
                                <label class="ps-label">{{ __('State') }}</label>
                                <input type="text" id="shipping_state" class="ps-input" placeholder="{{ __('State') }}">
                            </div>
                            <div>
                                <label class="ps-label">{{ __('ZIP Code') }}</label>
                                <input type="text" id="shipping_zip" class="ps-input" placeholder="10001">
                            </div>
                            <div>
                                <label class="ps-label">{{ __('Country') }}</label>
                                <input type="text" id="shipping_country" class="ps-input" placeholder="{{ __('Country') }}">
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Save Button --}}
                <div style="margin-top:20px;display:flex;align-items:center;gap:12px;">
                    <button type="button" onclick="saveRecipientAddress()" class="ps-save-sm" id="recipientSaveBtn">
                        <i class="ti ti-device-floppy"></i> {{ __('Save Address') }}
                    </button>
                    <span id="recipientMsg" style="font-size:.82rem;font-weight:600;display:none;"></span>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Tab Navigation ── --}}
    <div class="ps-tabs" id="psTabNav">
        <button class="ps-tab active" onclick="switchTab('proposal', this)">
            <i class="ti ti-file-text"></i> {{ __('Proposal Print Setting') }}
        </button>
        <button class="ps-tab" onclick="switchTab('invoice', this)">
            <i class="ti ti-file-invoice"></i> {{ __('Invoice Print Setting') }}
        </button>
        <button class="ps-tab" onclick="switchTab('bill', this)">
            <i class="ti ti-receipt"></i> {{ __('Bill Print Setting') }}
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- PROPOSAL TAB --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="ps-pane active" id="pane-proposal">
        <div class="ps-grid">

            {{-- Settings Panel --}}
            <div class="ps-card">
                <div class="ps-card-head">
                    <div class="ps-card-icon blue"><i class="ti ti-file-text"></i></div>
                    <div>
                        <p class="ps-card-title">{{ __('Proposal Settings') }}</p>
                        <p class="ps-card-sub">{{ __('Template & branding') }}</p>
                    </div>
                </div>
                <div class="ps-card-body">
                    <form method="post" action="{{ route('proposal.template.setting') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Template --}}
                        <div class="ps-field">
                            <label class="ps-label">{{ __('Template') }}</label>
                            <select class="ps-select" name="proposal_template">
                                @foreach(App\Models\Utility::templateData()['templates'] as $key => $template)
                                    <option value="{{ $key }}" {{ (isset($settings['proposal_template']) && $settings['proposal_template'] == $key) ? 'selected' : '' }}>
                                        {{ $template }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ps-divider"></div>

                        {{-- Color Theme --}}
                        <div class="ps-field">
                            <label class="ps-label">{{ __('Color Theme') }}</label>
                            <div class="ps-swatches">
                                @foreach(App\Models\Utility::templateData()['colors'] as $key => $color)
                                    <label class="ps-swatch-label">
                                        <input name="proposal_color" type="radio" value="{{ $color }}"
                                            {{ (isset($settings['proposal_color']) && $settings['proposal_color'] == $color) ? 'checked' : '' }}>
                                        <span class="ps-swatch" style="background:#{{ $color }}"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="ps-divider"></div>

                        {{-- Logo --}}
                        <div class="ps-field">
                            <label class="ps-label">{{ __('Proposal Logo') }}</label>
                            @php $cur_proposal_logo = \App\Models\Utility::getValByName('proposal_logo'); @endphp
                            @if(!empty($cur_proposal_logo))
                                <div class="ps-current-logo">
                                    <img src="{{ \App\Models\Utility::get_file('proposal_logo/') . $cur_proposal_logo }}" alt="Current Logo">
                                    <span>✓ {{ __('Current logo active') }}</span>
                                </div>
                            @endif
                            <label class="ps-upload" for="proposal_logo">
                                <i class="ti ti-cloud-upload ps-upload-icon"></i>
                                <span class="ps-upload-text">{{ __('Click to upload new logo') }}</span>
                                <span class="ps-upload-hint">PNG, JPG — max 20MB</span>
                                <input type="file" name="proposal_logo" id="proposal_logo" accept="image/*">
                            </label>
                            <img id="proposal_image" class="ps-logo-preview" src="" alt="Logo Preview">
                        </div>

                        <button type="submit" class="ps-btn blue">
                            <i class="ti ti-device-floppy"></i> {{ __('Save Changes') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Preview Panel --}}
            <div class="ps-preview">
                <div class="ps-preview-bar">
                    <span class="ps-dot" style="background:#ef4444;"></span>
                    <span class="ps-dot" style="background:#f59e0b;"></span>
                    <span class="ps-dot" style="background:#22c55e;"></span>
                    <span class="ps-preview-label">{{ __('Live Preview') }}</span>
                </div>
                @php
                    $p_tpl   = $settings['proposal_template'] ?? 'template1';
                    $p_color = $settings['proposal_color']    ?? 'ffffff';
                @endphp
                <iframe id="proposal_frame" class="ps-iframe"
                    src="{{ route('proposal.preview', [$p_tpl, $p_color]) }}"></iframe>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- INVOICE TAB --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="ps-pane" id="pane-invoice">
        <div class="ps-grid">

            <div class="ps-card">
                <div class="ps-card-head">
                    <div class="ps-card-icon green"><i class="ti ti-file-invoice"></i></div>
                    <div>
                        <p class="ps-card-title">{{ __('Invoice Settings') }}</p>
                        <p class="ps-card-sub">{{ __('Template & branding') }}</p>
                    </div>
                </div>
                <div class="ps-card-body">
                    <form method="post" action="{{ route('template.setting') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="ps-field">
                            <label class="ps-label">{{ __('Template') }}</label>
                            <select class="ps-select" name="invoice_template">
                                @foreach(App\Models\Utility::templateData()['templates'] as $key => $template)
                                    <option value="{{ $key }}" {{ (isset($settings['invoice_template']) && $settings['invoice_template'] == $key) ? 'selected' : '' }}>
                                        {{ $template }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ps-divider"></div>

                        <div class="ps-field">
                            <label class="ps-label">{{ __('Color Theme') }}</label>
                            <div class="ps-swatches">
                                @foreach(App\Models\Utility::templateData()['colors'] as $key => $color)
                                    <label class="ps-swatch-label">
                                        <input name="invoice_color" type="radio" value="{{ $color }}"
                                            {{ (isset($settings['invoice_color']) && $settings['invoice_color'] == $color) ? 'checked' : '' }}>
                                        <span class="ps-swatch" style="background:#{{ $color }}"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="ps-divider"></div>

                        <div class="ps-field">
                            <label class="ps-label">{{ __('Invoice Logo') }}</label>
                            @php $cur_invoice_logo = \App\Models\Utility::getValByName('invoice_logo'); @endphp
                            @if(!empty($cur_invoice_logo))
                                <div class="ps-current-logo">
                                    <img src="{{ \App\Models\Utility::get_file('invoice_logo/') . $cur_invoice_logo }}" alt="Current Logo">
                                    <span>✓ {{ __('Current logo active') }}</span>
                                </div>
                            @endif
                            <label class="ps-upload" for="invoice_logo">
                                <i class="ti ti-cloud-upload ps-upload-icon"></i>
                                <span class="ps-upload-text">{{ __('Click to upload new logo') }}</span>
                                <span class="ps-upload-hint">PNG, JPG — max 20MB</span>
                                <input type="file" name="invoice_logo" id="invoice_logo" accept="image/*">
                            </label>
                            <img id="invoice_image" class="ps-logo-preview" src="" alt="Logo Preview">
                        </div>

                        <button type="submit" class="ps-btn green">
                            <i class="ti ti-device-floppy"></i> {{ __('Save Changes') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="ps-preview">
                <div class="ps-preview-bar">
                    <span class="ps-dot" style="background:#ef4444;"></span>
                    <span class="ps-dot" style="background:#f59e0b;"></span>
                    <span class="ps-dot" style="background:#22c55e;"></span>
                    <span class="ps-preview-label">{{ __('Live Preview') }}</span>
                </div>
                @php
                    $i_tpl   = $settings['invoice_template'] ?? 'template1';
                    $i_color = $settings['invoice_color']    ?? 'ffffff';
                @endphp
                <iframe id="invoice_frame" class="ps-iframe"
                    src="{{ route('invoice.preview', [$i_tpl, $i_color]) }}"></iframe>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- BILL TAB --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="ps-pane" id="pane-bill">
        <div class="ps-grid">

            <div class="ps-card">
                <div class="ps-card-head">
                    <div class="ps-card-icon purple"><i class="ti ti-receipt"></i></div>
                    <div>
                        <p class="ps-card-title">{{ __('Bill Settings') }}</p>
                        <p class="ps-card-sub">{{ __('Template & branding') }}</p>
                    </div>
                </div>
                <div class="ps-card-body">
                    <form method="post" action="{{ route('bill.template.setting') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="ps-field">
                            <label class="ps-label">{{ __('Template') }}</label>
                            <select class="ps-select" name="bill_template">
                                @foreach(App\Models\Utility::templateData()['templates'] as $key => $template)
                                    <option value="{{ $key }}" {{ (isset($settings['bill_template']) && $settings['bill_template'] == $key) ? 'selected' : '' }}>
                                        {{ $template }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ps-divider"></div>

                        <div class="ps-field">
                            <label class="ps-label">{{ __('Color Theme') }}</label>
                            <div class="ps-swatches">
                                @foreach(App\Models\Utility::templateData()['colors'] as $key => $color)
                                    <label class="ps-swatch-label">
                                        <input name="bill_color" type="radio" value="{{ $color }}"
                                            {{ (isset($settings['bill_color']) && $settings['bill_color'] == $color) ? 'checked' : '' }}>
                                        <span class="ps-swatch" style="background:#{{ $color }}"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="ps-divider"></div>

                        <div class="ps-field">
                            <label class="ps-label">{{ __('Bill Logo') }}</label>
                            @php $cur_bill_logo = \App\Models\Utility::getValByName('bill_logo'); @endphp
                            @if(!empty($cur_bill_logo))
                                <div class="ps-current-logo">
                                    <img src="{{ \App\Models\Utility::get_file('bill_logo/') . $cur_bill_logo }}" alt="Current Logo">
                                    <span>✓ {{ __('Current logo active') }}</span>
                                </div>
                            @endif
                            <label class="ps-upload" for="bill_logo">
                                <i class="ti ti-cloud-upload ps-upload-icon"></i>
                                <span class="ps-upload-text">{{ __('Click to upload new logo') }}</span>
                                <span class="ps-upload-hint">PNG, JPG — max 20MB</span>
                                <input type="file" name="bill_logo" id="bill_logo" accept="image/*">
                            </label>
                            <img id="bill_image" class="ps-logo-preview" src="" alt="Logo Preview">
                        </div>

                        <button type="submit" class="ps-btn purple">
                            <i class="ti ti-device-floppy"></i> {{ __('Save Changes') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="ps-preview">
                <div class="ps-preview-bar">
                    <span class="ps-dot" style="background:#ef4444;"></span>
                    <span class="ps-dot" style="background:#f59e0b;"></span>
                    <span class="ps-dot" style="background:#22c55e;"></span>
                    <span class="ps-preview-label">{{ __('Live Preview') }}</span>
                </div>
                @php
                    $b_tpl   = $settings['bill_template'] ?? 'template1';
                    $b_color = $settings['bill_color']    ?? 'ffffff';
                @endphp
                <iframe id="bill_frame" class="ps-iframe"
                    src="{{ route('bill.preview', [$b_tpl, $b_color]) }}"></iframe>
            </div>

        </div>
    </div>

</div>
@endsection
