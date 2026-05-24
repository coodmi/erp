@extends('layouts.admin')
@section('page-title'){{ __('Print Settings') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Print Settings') }}</li>
@endsection

@push('script-page')
<script>
// ── data from server ──
var _psCustomers = {!! json_encode($customers) !!};
var _psVendors   = {!! json_encode($vendors) !!};
var _psAgents    = {!! json_encode($agents) !!};

var _psSelectedParty = null; // { id, name, email, partyType: 'agent'|'client'|'vendor' }

// ── template/color live preview ──
$(document).on('change', "select[name='invoice_template'], input[name='invoice_color']", function () {
    refreshPreview();
});

function refreshPreview() {
    var template = $("select[name='invoice_template']").val();
    var color    = $("input[name='invoice_color']:checked").val() || 'ffffff';
    var src      = '{{ url("/invoices/preview") }}/' + template + '/' + color;
    document.getElementById('invoice_frame').src = src;
}

// logo preview
document.addEventListener('DOMContentLoaded', function () {
    var inp = document.getElementById('invoice_logo');
    var img = document.getElementById('invoice_image');
    if (inp && img) {
        inp.addEventListener('change', function () {
            if (this.files[0]) { img.src = URL.createObjectURL(this.files[0]); img.style.display = 'block'; }
        });
    }
    // set initial type without rebuilding dropdown (agents already rendered in blade)
    _psCurrentType = 'agent';
});

// ── Party type selector ──
function updatePartyDropdown() {
    var type = document.getElementById('ps_party_type').value;
    var sel  = document.getElementById('ps_party_id');
    sel.innerHTML = '<option value="">— ' + (type === 'agent' ? '{{ __("Select Agent") }}' : type === 'client' ? '{{ __("Select Client") }}' : '{{ __("Select Vendor") }}') + ' —</option>';

    var list = type === 'agent' ? _psAgents : (type === 'client' ? _psCustomers : _psVendors);
    list.forEach(function (item) {
        var opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.name + (item.email ? '  ·  ' + item.email : '');
        sel.appendChild(opt);
    });
    clearPartyPreview();
}
</script>
@endpush

@section('content')
<style>
:root {
    --c-bg:#f0f4f8; --c-white:#fff; --c-border:#e2e8f0;
    --c-dark:#0f172a; --c-muted:#64748b;
    --c-blue:#2563eb; --c-blue-lt:#eff6ff;
    --c-green:#059669; --c-purple:#7c3aed;
    --r-lg:18px; --r-md:12px;
    --sh-sm:0 2px 8px rgba(15,23,42,.07);
    --sh-md:0 8px 28px rgba(15,23,42,.10);
}
.ps-page { padding:4px 0 40px; }

/* ── Main grid: left panel + right preview ── */
.ps-main-grid {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 20px;
    align-items: start;
}
@media(max-width:1100px){ .ps-main-grid{ grid-template-columns:1fr; } }

/* ── Left panel ── */
.ps-left { display:flex; flex-direction:column; gap:16px; }

/* ── Card ── */
.ps-card {
    background:var(--c-white); border:1px solid var(--c-border);
    border-radius:var(--r-lg); box-shadow:var(--sh-md); overflow:hidden;
}
.ps-card-head {
    padding:16px 20px; border-bottom:1px solid var(--c-border);
    background:linear-gradient(135deg,#f8fafc,#f1f5f9);
    display:flex; align-items:center; gap:10px;
}
.ps-card-icon {
    width:38px; height:38px; border-radius:11px;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; color:#fff; flex-shrink:0;
}
.ps-card-icon.blue   { background:linear-gradient(135deg,#3b82f6,#2563eb); box-shadow:0 5px 12px rgba(37,99,235,.25); }
.ps-card-icon.green  { background:linear-gradient(135deg,#34d399,#059669); box-shadow:0 5px 12px rgba(5,150,105,.25); }
.ps-card-icon.orange { background:linear-gradient(135deg,#fb923c,#ea580c); box-shadow:0 5px 12px rgba(234,88,12,.25); }
.ps-card-title { font-size:.9rem; font-weight:800; color:var(--c-dark); margin:0; }
.ps-card-sub   { font-size:.72rem; color:var(--c-muted); margin:2px 0 0; }
.ps-card-body  { padding:18px 20px; }

/* ── Labels & inputs ── */
.ps-label {
    display:block; font-size:.68rem; font-weight:800;
    color:#475569; text-transform:uppercase; letter-spacing:.08em; margin-bottom:7px;
}
.ps-select, .ps-input {
    width:100%; min-height:42px; border:1.5px solid var(--c-border);
    border-radius:var(--r-md); font-size:.86rem; padding:9px 36px 9px 13px;
    background:#f8fafc; color:var(--c-dark); outline:none;
    appearance:none; transition:all .2s; font-family:inherit;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 12px center; cursor:pointer;
}
.ps-input { padding:9px 13px; background-image:none; cursor:text; }
.ps-select:focus, .ps-input:focus { border-color:var(--c-blue); background:#fff; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.ps-field { margin-bottom:16px; }
.ps-field:last-child { margin-bottom:0; }
.ps-divider { height:1px; background:var(--c-border); margin:14px 0; }
</style>

<style>
/* ── Color swatches ── */
.ps-swatches { display:flex; flex-wrap:wrap; gap:6px; }
.ps-swatch-label { cursor:pointer; position:relative; }
.ps-swatch-label input { position:absolute; opacity:0; width:0; height:0; }
.ps-swatch {
    width:24px; height:24px; border-radius:6px; display:block;
    border:2.5px solid transparent; transition:all .15s;
    box-shadow:0 2px 5px rgba(15,23,42,.12);
}
.ps-swatch-label input:checked + .ps-swatch { border-color:#0f172a; transform:scale(1.2); box-shadow:0 4px 12px rgba(15,23,42,.3); }
.ps-swatch:hover { transform:scale(1.1); }

/* ── Logo upload ── */
.ps-upload {
    position:relative; border:2px dashed var(--c-border); border-radius:var(--r-md);
    padding:16px; text-align:center; background:#f8fafc; cursor:pointer; transition:all .2s; overflow:hidden;
}
.ps-upload:hover { border-color:var(--c-blue); background:var(--c-blue-lt); }
.ps-upload input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.ps-upload-icon { font-size:1.4rem; color:#94a3b8; display:block; margin-bottom:4px; }
.ps-upload-text { font-size:.76rem; color:var(--c-muted); font-weight:600; }
.ps-upload-hint { font-size:.66rem; color:#94a3b8; margin-top:2px; }
.ps-logo-preview { width:80px; height:38px; object-fit:contain; border-radius:6px; margin-top:10px; display:none; border:1px solid var(--c-border); padding:3px; }
.ps-current-logo {
    display:flex; align-items:center; gap:8px; padding:8px 10px;
    background:#f0fdf4; border:1px solid #bbf7d0; border-radius:9px; margin-bottom:8px;
}
.ps-current-logo img { height:30px; width:auto; object-fit:contain; border-radius:5px; }
.ps-current-logo span { font-size:.7rem; color:var(--c-green); font-weight:700; }

/* ── Save button ── */
.ps-btn {
    width:100%; min-height:44px; border-radius:var(--r-md); font-weight:800;
    font-size:.86rem; border:0; cursor:pointer;
    display:flex; align-items:center; justify-content:center; gap:7px;
    transition:all .2s; margin-top:18px; letter-spacing:.02em;
}
.ps-btn.green { background:linear-gradient(135deg,#34d399,#059669); color:#fff; box-shadow:0 8px 20px rgba(5,150,105,.25); }
.ps-btn:hover { filter:brightness(1.06); transform:translateY(-1px); }

/* ── Party selector card ── */
.ps-party-type-row {
    display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:14px;
}
.ps-party-btn {
    padding:10px 6px; border-radius:10px; border:2px solid var(--c-border);
    background:#fff; cursor:pointer; text-align:center; transition:all .18s;
    font-size:.78rem; font-weight:700; color:var(--c-muted);
    display:flex; flex-direction:column; align-items:center; gap:4px;
}
.ps-party-btn i { font-size:1.1rem; }
.ps-party-btn:hover { border-color:#93c5fd; color:var(--c-blue); background:#f0f9ff; }
.ps-party-btn.active-agent  { border-color:#f59e0b; color:#b45309; background:#fffbeb; }
.ps-party-btn.active-client { border-color:#3b82f6; color:#1d4ed8; background:#eff6ff; }
.ps-party-btn.active-vendor { border-color:#a78bfa; color:#6d28d9; background:#f5f3ff; }

/* ── Party info card ── */
.ps-party-info {
    display:none; padding:12px 14px; border-radius:12px;
    background:linear-gradient(135deg,#f0fdf4,#dcfce7);
    border:1px solid #bbf7d0; margin-top:12px;
    display:flex; align-items:center; gap:12px;
}
.ps-party-avatar {
    width:40px; height:40px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; font-weight:800; color:#fff;
}
.ps-party-avatar.agent  { background:linear-gradient(135deg,#fb923c,#ea580c); }
.ps-party-avatar.client { background:linear-gradient(135deg,#3b82f6,#2563eb); }
.ps-party-avatar.vendor { background:linear-gradient(135deg,#a78bfa,#7c3aed); }
.ps-party-name  { font-size:.88rem; font-weight:800; color:var(--c-dark); }
.ps-party-email { font-size:.74rem; color:var(--c-muted); margin-top:1px; }
.ps-party-addr  { font-size:.72rem; color:#94a3b8; margin-top:2px; font-style:italic; }
.ps-party-badge {
    margin-left:auto; padding:3px 10px; border-radius:20px;
    font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; flex-shrink:0;
}
.ps-party-badge.agent  { background:#fef3c7; color:#92400e; }
.ps-party-badge.client { background:#dbeafe; color:#1d4ed8; }
.ps-party-badge.vendor { background:#ede9fe; color:#6d28d9; }

/* ── Preview panel ── */
.ps-preview {
    background:var(--c-white); border:1px solid var(--c-border);
    border-radius:var(--r-lg); box-shadow:var(--sh-md); overflow:hidden;
    position:sticky; top:20px;
}
.ps-preview-bar {
    padding:11px 16px; border-bottom:1px solid var(--c-border);
    background:linear-gradient(135deg,#f8fafc,#f1f5f9);
    display:flex; align-items:center; gap:6px;
}
.ps-dot { width:10px; height:10px; border-radius:50%; }
.ps-preview-label { font-size:.73rem; color:var(--c-muted); font-weight:700; margin-left:6px; }
.ps-iframe { width:100%; height:720px; border:0; display:block; background:#f8fafc; }

/* ── Company info collapsible ── */
.ps-collapse-head {
    padding:14px 20px; border-bottom:1px solid var(--c-border);
    background:linear-gradient(135deg,#f8fafc,#f1f5f9);
    display:flex; align-items:center; justify-content:space-between;
    cursor:pointer; user-select:none;
}
.ps-collapse-head-left { display:flex; align-items:center; gap:10px; }
.ps-chevron { transition:transform .2s; font-size:.82rem; color:var(--c-muted); }
.ps-chevron.open { transform:rotate(180deg); }
.ps-collapse-body { padding:18px 20px; display:none; }
.ps-collapse-body.open { display:block; }
.ps-input-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
@media(max-width:600px){ .ps-input-grid{ grid-template-columns:1fr; } }
.ps-save-sm {
    display:inline-flex; align-items:center; gap:6px; padding:9px 20px;
    border-radius:var(--r-md); font-weight:700; font-size:.83rem;
    border:0; cursor:pointer; background:linear-gradient(135deg,#3b82f6,#2563eb);
    color:#fff; box-shadow:0 6px 16px rgba(37,99,235,.22); transition:all .2s; margin-top:16px;
}
.ps-save-sm:hover { filter:brightness(1.06); transform:translateY(-1px); }
.ps-toggle-row { display:flex; align-items:center; gap:10px; padding:8px 0; }
.ps-toggle-row label { font-size:.82rem; font-weight:600; color:var(--c-dark); cursor:pointer; }
.ps-switch { position:relative; display:inline-block; width:38px; height:21px; flex-shrink:0; }
.ps-switch input { opacity:0; width:0; height:0; }
.ps-slider { position:absolute; inset:0; background:#cbd5e1; border-radius:21px; cursor:pointer; transition:.2s; }
.ps-slider:before { content:''; position:absolute; width:15px; height:15px; border-radius:50%; background:#fff; left:3px; top:3px; transition:.2s; box-shadow:0 1px 4px rgba(0,0,0,.2); }
.ps-switch input:checked + .ps-slider { background:var(--c-blue); }
.ps-switch input:checked + .ps-slider:before { transform:translateX(17px); }
</style>

<div class="ps-page">
<div class="ps-main-grid">

{{-- ════════════════════════════════════════ --}}
{{-- LEFT PANEL --}}
{{-- ════════════════════════════════════════ --}}
<div class="ps-left">

    {{-- ── 1. Party Selector ── --}}
    <div class="ps-card">
        <div class="ps-card-head">
            <div class="ps-card-icon orange"><i class="ti ti-users"></i></div>
            <div>
                <p class="ps-card-title">{{ __('Select Party') }}</p>
                <p class="ps-card-sub">{{ __('Preview invoice for Agent, Client or Vendor') }}</p>
            </div>
        </div>
        <div class="ps-card-body">

            {{-- Type buttons --}}
            <div class="ps-party-type-row">
                <button type="button" class="ps-party-btn active-agent" id="btn_agent"
                    onclick="setPartyType('agent')">
                    <i class="ti ti-user-star"></i> {{ __('Agent') }}
                </button>
                <button type="button" class="ps-party-btn" id="btn_client"
                    onclick="setPartyType('client')">
                    <i class="ti ti-user-circle"></i> {{ __('Client') }}
                </button>
                <button type="button" class="ps-party-btn" id="btn_vendor"
                    onclick="setPartyType('vendor')">
                    <i class="ti ti-building-store"></i> {{ __('Vendor') }}
                </button>
            </div>

            {{-- Dropdown --}}
            <div class="ps-field">
                <label class="ps-label" id="ps_party_label">{{ __('Select Agent') }}</label>
                <select id="ps_party_type" style="display:none;"></select>
                <select id="ps_party_id" class="ps-select" onchange="onPartySelect(this.value)">
                    <option value="">— {{ __('Select Agent') }} —</option>
                    @foreach($agents as $a)
                        <option value="{{ $a['id'] }}">{{ $a['name'] }}@if($a['email']) · {{ $a['email'] }}@endif</option>
                    @endforeach
                </select>            </div>

            {{-- Party info preview --}}
            <div id="ps_party_info" style="display:none;padding:12px 14px;border-radius:12px;border:1px solid #bbf7d0;background:linear-gradient(135deg,#f0fdf4,#dcfce7);margin-top:4px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div class="ps-party-avatar agent" id="ps_party_avatar">A</div>
                    <div style="flex:1;min-width:0;">
                        <div class="ps-party-name" id="ps_party_name">—</div>
                        <div class="ps-party-email" id="ps_party_email">—</div>
                        <div class="ps-party-addr" id="ps_party_addr"></div>
                    </div>
                    <span class="ps-party-badge agent" id="ps_party_badge">{{ __('Agent') }}</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ── 2. Invoice Template Settings ── --}}
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
                    <select class="ps-select" name="invoice_template" onchange="refreshPreview()">
                        @foreach(App\Models\Utility::templateData()['templates'] as $key => $template)
                            <option value="{{ $key }}" {{ (isset($settings['invoice_template']) && $settings['invoice_template'] == $key) ? 'selected' : '' }}>{{ $template }}</option>
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
                            <img src="{{ \App\Models\Utility::get_file('invoice_logo/') . $cur_invoice_logo }}" alt="Logo">
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

    {{-- ── 3. Company Info (collapsible) ── --}}
    <div class="ps-card">
        <div class="ps-collapse-head" onclick="toggleCollapse('companyBody',this)">
            <div class="ps-collapse-head-left">
                <div class="ps-card-icon blue" style="width:34px;height:34px;font-size:.9rem;"><i class="ti ti-building"></i></div>
                <div>
                    <p class="ps-card-title">{{ __('Company Info') }}</p>
                    <p class="ps-card-sub">{{ __('Address shown on all documents') }}</p>
                </div>
            </div>
            <i class="ti ti-chevron-down ps-chevron"></i>
        </div>
        <div class="ps-collapse-body" id="companyBody">
            <form method="post" action="{{ route('company.settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="ps-field">
                    <label class="ps-label">{{ __('Company Name') }}</label>
                    <input type="text" name="company_name" class="ps-input" value="{{ $settings['company_name'] ?? '' }}" placeholder="{{ __('Company Name') }}" required>
                </div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Email') }}</label>
                    <input type="email" name="mail_from_address" class="ps-input" value="{{ $settings['mail_from_address'] ?? '' }}" placeholder="info@company.com">
                </div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Phone') }}</label>
                    <input type="text" name="company_telephone" class="ps-input" value="{{ $settings['company_telephone'] ?? '' }}" placeholder="+1 234 567 8900">
                </div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Street Address') }}</label>
                    <input type="text" name="company_address" class="ps-input" value="{{ $settings['company_address'] ?? '' }}" placeholder="{{ __('Street address') }}">
                </div>
                <div class="ps-input-grid ps-field">
                    <div><label class="ps-label">{{ __('City') }}</label><input type="text" name="company_city" class="ps-input" value="{{ $settings['company_city'] ?? '' }}"></div>
                    <div><label class="ps-label">{{ __('State') }}</label><input type="text" name="company_state" class="ps-input" value="{{ $settings['company_state'] ?? '' }}"></div>
                    <div><label class="ps-label">{{ __('ZIP') }}</label><input type="text" name="company_zipcode" class="ps-input" value="{{ $settings['company_zipcode'] ?? '' }}"></div>
                    <div><label class="ps-label">{{ __('Country') }}</label><input type="text" name="company_country" class="ps-input" value="{{ $settings['company_country'] ?? '' }}"></div>
                </div>
                <div class="ps-divider"></div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Registration Number') }}</label>
                    <input type="text" name="registration_number" class="ps-input" value="{{ $settings['registration_number'] ?? '' }}">
                </div>
                <div class="ps-toggle-row ps-field">
                    <label class="ps-switch">
                        <input type="checkbox" name="vat_gst_number_switch" value="on"
                            {{ (isset($settings['vat_gst_number_switch']) && $settings['vat_gst_number_switch'] == 'on') ? 'checked' : '' }}
                            onchange="document.getElementById('vat-fields').style.display=this.checked?'block':'none'">
                        <span class="ps-slider"></span>
                    </label>
                    <label>{{ __('Show VAT / GST Number') }}</label>
                </div>
                <div id="vat-fields" style="{{ (isset($settings['vat_gst_number_switch']) && $settings['vat_gst_number_switch'] == 'on') ? '' : 'display:none;' }}">
                    <div class="ps-input-grid ps-field">
                        <div><label class="ps-label">{{ __('Tax Type') }}</label><input type="text" name="tax_type" class="ps-input" value="{{ $settings['tax_type'] ?? '' }}" placeholder="VAT"></div>
                        <div><label class="ps-label">{{ __('VAT / GST Number') }}</label><input type="text" name="vat_number" class="ps-input" value="{{ $settings['vat_number'] ?? '' }}"></div>
                    </div>
                </div>
                <div class="ps-divider"></div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Footer Title') }}</label>
                    <input type="text" name="footer_title" class="ps-input" value="{{ $settings['footer_title'] ?? '' }}" placeholder="{{ __('Thank you for your business!') }}">
                </div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Footer Notes') }}</label>
                    <textarea name="footer_notes" class="ps-input" rows="3" style="height:auto;resize:vertical;background-image:none;">{{ $settings['footer_notes'] ?? '' }}</textarea>
                </div>
                <button type="submit" class="ps-save-sm"><i class="ti ti-device-floppy"></i> {{ __('Save Company Info') }}</button>
            </form>
        </div>
    </div>

</div>{{-- /ps-left --}}

{{-- ════════════════════════════════════════ --}}
{{-- RIGHT: LIVE PREVIEW --}}
{{-- ════════════════════════════════════════ --}}
<div class="ps-preview">
    <div class="ps-preview-bar">
        <span class="ps-dot" style="background:#ef4444;"></span>
        <span class="ps-dot" style="background:#f59e0b;"></span>
        <span class="ps-dot" style="background:#22c55e;"></span>
        <span class="ps-preview-label">{{ __('Live Preview — Invoice') }}</span>
        <span id="ps_preview_party_label" style="margin-left:auto;font-size:.72rem;font-weight:700;color:#2563eb;background:#eff6ff;padding:3px 10px;border-radius:20px;display:none;"></span>
    </div>
    @php
        $i_tpl   = $settings['invoice_template'] ?? 'template1';
        $i_color = $settings['invoice_color']    ?? 'ffffff';
    @endphp
    <iframe id="invoice_frame" class="ps-iframe"
        src="{{ route('invoice.preview', [$i_tpl, $i_color]) }}"></iframe>
</div>

</div>{{-- /ps-main-grid --}}
</div>{{-- /ps-page --}}

<script>
var _psCurrentType = 'agent';

function toggleCollapse(bodyId, header) {
    var body = document.getElementById(bodyId);
    var icon = header.querySelector('.ps-chevron');
    var open = body.classList.contains('open');
    body.classList.toggle('open', !open);
    if (icon) icon.classList.toggle('open', !open);
}

function setPartyType(type) {
    _psCurrentType = type;
    // update button styles
    ['agent','client','vendor'].forEach(function(t) {
        var btn = document.getElementById('btn_' + t);
        btn.className = 'ps-party-btn' + (t === type ? ' active-' + t : '');
    });
    // update label
    var labels = { agent: '{{ __("Select Agent") }}', client: '{{ __("Select Client") }}', vendor: '{{ __("Select Vendor") }}' };
    document.getElementById('ps_party_label').textContent = labels[type];
    // rebuild dropdown
    var sel  = document.getElementById('ps_party_id');
    var list = type === 'agent' ? _psAgents : (type === 'client' ? _psCustomers : _psVendors);
    sel.innerHTML = '<option value="">— ' + labels[type] + ' —</option>';
    list.forEach(function(item) {
        var opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.name + (item.email ? '  ·  ' + item.email : '');
        sel.appendChild(opt);
    });
    clearPartyPreview();
}

function clearPartyPreview() {
    document.getElementById('ps_party_info').style.display = 'none';
    document.getElementById('ps_preview_party_label').style.display = 'none';
    _psSelectedParty = null;
}

function onPartySelect(id) {
    if (!id) { clearPartyPreview(); return; }
    var type = _psCurrentType;
    var list = type === 'agent' ? _psAgents : (type === 'client' ? _psCustomers : _psVendors);
    var item = null;
    for (var i = 0; i < list.length; i++) { if (list[i].id == id) { item = list[i]; break; } }
    if (!item) return;

    _psSelectedParty = { id: id, name: item.name, email: item.email, partyType: type };

    // avatar
    var av = document.getElementById('ps_party_avatar');
    av.textContent = item.name.charAt(0).toUpperCase();
    av.className = 'ps-party-avatar ' + type;

    document.getElementById('ps_party_name').textContent  = item.name;
    document.getElementById('ps_party_email').textContent = item.email || item.contact || '—';
    document.getElementById('ps_party_addr').textContent  = '';

    var badge = document.getElementById('ps_party_badge');
    var labels = { agent: '{{ __("Agent") }}', client: '{{ __("Client") }}', vendor: '{{ __("Vendor") }}' };
    badge.textContent = labels[type];
    badge.className   = 'ps-party-badge ' + type;

    document.getElementById('ps_party_info').style.display = 'block';

    // preview label
    var lbl = document.getElementById('ps_preview_party_label');
    lbl.textContent = item.name;
    lbl.style.display = 'inline-block';

    // if client or vendor — load address and update preview
    if (type === 'client' || type === 'vendor') {
        var apiType = type === 'client' ? 'customer' : 'vendor';
        fetch('{{ route("print.recipient.data") }}?type=' + apiType + '&id=' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var addr = [];
            if (data.billing_address) addr.push(data.billing_address);
            if (data.billing_city)    addr.push(data.billing_city);
            if (data.billing_country) addr.push(data.billing_country);
            document.getElementById('ps_party_addr').textContent = addr.join(', ') || '{{ __("No address on file") }}';
        }).catch(function() {});
    }
}

function updatePartyDropdown() {
    // agents are pre-rendered in blade, just set the type
    _psCurrentType = 'agent';
}
</script>
@endsection
