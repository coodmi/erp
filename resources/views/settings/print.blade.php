@extends('layouts.admin')
@section('page-title'){{ __('Settings') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Print Settings') }}</li>
@endsection

@php
    $logo = asset(Storage::url('uploads/logo/'));
@endphp

@push('script-page')
<script>
$(document).on("change", "select[name='invoice_template'], input[name='invoice_color']", function () {
    var template = $("select[name='invoice_template']").val();
    var color = $("input[name='invoice_color']:checked").val();
    $('#invoice_frame').attr('src', '{{ url('/invoices/preview') }}/' + template + '/' + color);
});
$(document).on("change", "select[name='proposal_template'], input[name='proposal_color']", function () {
    var template = $("select[name='proposal_template']").val();
    var color = $("input[name='proposal_color']:checked").val();
    $('#proposal_frame').attr('src', '{{ url('/proposal/preview') }}/' + template + '/' + color);
});
$(document).on("change", "select[name='bill_template'], input[name='bill_color']", function () {
    var template = $("select[name='bill_template']").val();
    var color = $("input[name='bill_color']:checked").val();
    $('#bill_frame').attr('src', '{{ url('/bill/preview') }}/' + template + '/' + color);
});
document.getElementById('proposal_logo').onchange = function () {
    document.getElementById('proposal_image').src = URL.createObjectURL(this.files[0]);
}
document.getElementById('invoice_logo').onchange = function () {
    document.getElementById('invoice_image').src = URL.createObjectURL(this.files[0]);
}
document.getElementById('bill_logo').onchange = function () {
    document.getElementById('bill_image').src = URL.createObjectURL(this.files[0]);
}
</script>
@endpush

@section('content')
<style>
:root { --ps-dark:#0f172a; --ps-muted:#64748b; --ps-border:#e2e8f0; }

/* ── Tab Nav ── */
.ps-tab-nav { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:28px; }
.ps-tab-btn {
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 20px; border-radius:14px; font-size:.85rem; font-weight:700;
    border:1.5px solid #e2e8f0; color:#64748b; background:#fff;
    cursor:pointer; transition:all .18s; text-decoration:none;
}
.ps-tab-btn:hover { border-color:#2563eb; color:#2563eb; background:#eff6ff; }
.ps-tab-btn.active { background:linear-gradient(135deg,#3b82f6,#2563eb); border-color:#2563eb; color:#fff; box-shadow:0 6px 18px rgba(37,99,235,.28); }
.ps-tab-btn i { font-size:1rem; }

/* ── Tab Pane ── */
.ps-tab-pane { display:none; }
.ps-tab-pane.active { display:block; }

/* ── Layout ── */
.ps-layout { display:grid; grid-template-columns:300px 1fr; gap:24px; align-items:start; }
@media(max-width:991px){ .ps-layout{ grid-template-columns:1fr; } }

/* ── Settings Panel ── */
.ps-panel {
    background:#fff; border:1px solid var(--ps-border); border-radius:20px;
    box-shadow:0 14px 35px rgba(15,23,42,.06); overflow:hidden;
}
.ps-panel-header {
    padding:18px 20px; border-bottom:1px solid var(--ps-border);
    background:linear-gradient(135deg,#f8fafc,#f1f5f9);
    display:flex; align-items:center; gap:10px;
}
.ps-panel-icon {
    width:38px; height:38px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; color:#fff;
    box-shadow:0 8px 16px rgba(15,23,42,.12);
}
.ps-panel-icon.blue   { background:linear-gradient(135deg,#3b82f6,#2563eb); }
.ps-panel-icon.green  { background:linear-gradient(135deg,#34d399,#059669); }
.ps-panel-icon.purple { background:linear-gradient(135deg,#a78bfa,#7c3aed); }
.ps-panel-title { font-size:.92rem; font-weight:800; color:var(--ps-dark); margin:0; }
.ps-panel-body { padding:20px; }

/* ── Form Elements ── */
.ps-form-label { font-size:.72rem; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:.07em; margin-bottom:8px; display:block; }
.ps-select {
    width:100%; min-height:44px; border:1.5px solid #e2e8f0; border-radius:12px;
    font-size:.88rem; padding:10px 14px; background:#f8fafc; color:#0f172a;
    transition:all .2s; outline:none; appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 12px center;
    padding-right:36px;
}
.ps-select:focus { border-color:#2563eb; background:#fff; box-shadow:0 0 0 3px rgba(37,99,235,.1); }

/* ── Color Swatches ── */
.ps-colors { display:flex; flex-wrap:wrap; gap:8px; margin-top:4px; }
.ps-color-label { cursor:pointer; position:relative; }
.ps-color-label input { position:absolute; opacity:0; width:0; height:0; }
.ps-color-swatch {
    width:28px; height:28px; border-radius:8px; display:block;
    border:2px solid transparent; transition:all .15s;
    box-shadow:0 2px 6px rgba(15,23,42,.1);
}
.ps-color-label input:checked + .ps-color-swatch {
    border-color:#0f172a; transform:scale(1.15);
    box-shadow:0 4px 12px rgba(15,23,42,.25);
}
.ps-color-swatch:hover { transform:scale(1.1); }

/* ── Logo Upload ── */
.ps-upload-area {
    border:2px dashed #e2e8f0; border-radius:14px; padding:16px;
    text-align:center; background:#f8fafc; cursor:pointer;
    transition:all .2s; position:relative;
}
.ps-upload-area:hover { border-color:#3b82f6; background:#eff6ff; }
.ps-upload-area input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.ps-upload-area i { font-size:1.5rem; color:#94a3b8; display:block; margin-bottom:6px; }
.ps-upload-area span { font-size:.78rem; color:#64748b; font-weight:600; }
.ps-logo-preview { width:80px; height:40px; object-fit:contain; border-radius:6px; margin-top:10px; display:none; }

/* ── Save Button ── */
.ps-save-btn {
    width:100%; min-height:44px; border-radius:12px; font-weight:800; font-size:.88rem;
    border:0; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:7px;
    transition:all .2s; margin-top:20px;
}
.ps-save-btn.blue   { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; box-shadow:0 10px 20px rgba(37,99,235,.22); }
.ps-save-btn.green  { background:linear-gradient(135deg,#34d399,#059669); color:#fff; box-shadow:0 10px 20px rgba(5,150,105,.22); }
.ps-save-btn.purple { background:linear-gradient(135deg,#a78bfa,#7c3aed); color:#fff; box-shadow:0 10px 20px rgba(124,58,237,.22); }
.ps-save-btn:hover { filter:brightness(1.05); transform:translateY(-1px); }

/* ── Preview Panel ── */
.ps-preview-card {
    background:#fff; border:1px solid var(--ps-border); border-radius:20px;
    box-shadow:0 14px 35px rgba(15,23,42,.06); overflow:hidden;
}
.ps-preview-header {
    padding:14px 18px; border-bottom:1px solid var(--ps-border);
    background:linear-gradient(135deg,#f8fafc,#f1f5f9);
    display:flex; align-items:center; gap:8px;
}
.ps-preview-dot { width:10px; height:10px; border-radius:50%; }
.ps-preview-iframe { width:100%; height:680px; border:0; display:block; }
</style>

{{-- ── Tab Navigation ── --}}
<div class="ps-tab-nav" id="psTabNav">
    <button class="ps-tab-btn active" data-tab="proposal" onclick="switchTab('proposal', this)">
        <i class="ti ti-file-text"></i> Proposal Print Setting
    </button>
    <button class="ps-tab-btn" data-tab="invoice" onclick="switchTab('invoice', this)">
        <i class="ti ti-file-invoice"></i> Invoice Print Setting
    </button>
    <button class="ps-tab-btn" data-tab="bill" onclick="switchTab('bill', this)">
        <i class="ti ti-receipt"></i> Bill Print Setting
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- PROPOSAL TAB --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="ps-tab-pane active" id="tab-proposal">
    <div class="ps-layout">
        {{-- Settings --}}
        <div class="ps-panel">
            <div class="ps-panel-header">
                <div class="ps-panel-icon blue"><i class="ti ti-file-text"></i></div>
                <div>
                    <p class="ps-panel-title">Proposal Settings</p>
                    <p style="font-size:.75rem;color:#94a3b8;margin:0;">Template & branding</p>
                </div>
            </div>
            <div class="ps-panel-body">
                <form method="post" action="{{ route('proposal.template.setting') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="ps-form-label">Template</label>
                        <select class="ps-select" name="proposal_template">
                            @foreach(App\Models\Utility::templateData()['templates'] as $key => $template)
                                <option value="{{ $key }}" {{ (isset($settings['proposal_template']) && $settings['proposal_template'] == $key) ? 'selected' : '' }}>{{ $template }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="ps-form-label">Color Theme</label>
                        <div class="ps-colors">
                            @foreach(App\Models\Utility::templateData()['colors'] as $key => $color)
                                <label class="ps-color-label">
                                    <input name="proposal_color" type="radio" value="{{ $color }}" {{ (isset($settings['proposal_color']) && $settings['proposal_color'] == $color) ? 'checked' : '' }}>
                                    <span class="ps-color-swatch" style="background:#{{ $color }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="ps-form-label">Proposal Logo</label>
                        <label class="ps-upload-area" for="proposal_logo">
                            <i class="ti ti-cloud-upload"></i>
                            <span>Click to upload logo</span>
                            <input type="file" name="proposal_logo" id="proposal_logo" accept="image/*">
                        </label>
                        <img id="proposal_image" class="ps-logo-preview" src="" alt="Logo Preview">
                    </div>
                    <button type="submit" class="ps-save-btn blue">
                        <i class="ti ti-device-floppy"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
        {{-- Preview --}}
        <div class="ps-preview-card">
            <div class="ps-preview-header">
                <span class="ps-preview-dot" style="background:#ef4444;"></span>
                <span class="ps-preview-dot" style="background:#f59e0b;"></span>
                <span class="ps-preview-dot" style="background:#22c55e;"></span>
                <span style="font-size:.78rem;color:#94a3b8;font-weight:600;margin-left:8px;">Live Preview</span>
            </div>
            @if(isset($settings['proposal_template']) && isset($settings['proposal_color']))
                <iframe id="proposal_frame" class="ps-preview-iframe" src="{{ route('proposal.preview', [$settings['proposal_template'], $settings['proposal_color']]) }}"></iframe>
            @else
                <iframe id="proposal_frame" class="ps-preview-iframe" src="{{ route('proposal.preview', ['template1', 'ffffff']) }}"></iframe>
            @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- INVOICE TAB --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="ps-tab-pane" id="tab-invoice">
    <div class="ps-layout">
        <div class="ps-panel">
            <div class="ps-panel-header">
                <div class="ps-panel-icon green"><i class="ti ti-file-invoice"></i></div>
                <div>
                    <p class="ps-panel-title">Invoice Settings</p>
                    <p style="font-size:.75rem;color:#94a3b8;margin:0;">Template & branding</p>
                </div>
            </div>
            <div class="ps-panel-body">
                <form method="post" action="{{ route('template.setting') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="ps-form-label">Template</label>
                        <select class="ps-select" name="invoice_template">
                            @foreach(App\Models\Utility::templateData()['templates'] as $key => $template)
                                <option value="{{ $key }}" {{ (isset($settings['invoice_template']) && $settings['invoice_template'] == $key) ? 'selected' : '' }}>{{ $template }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="ps-form-label">Color Theme</label>
                        <div class="ps-colors">
                            @foreach(App\Models\Utility::templateData()['colors'] as $key => $color)
                                <label class="ps-color-label">
                                    <input name="invoice_color" type="radio" value="{{ $color }}" {{ (isset($settings['invoice_color']) && $settings['invoice_color'] == $color) ? 'checked' : '' }}>
                                    <span class="ps-color-swatch" style="background:#{{ $color }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="ps-form-label">Invoice Logo</label>
                        <label class="ps-upload-area" for="invoice_logo">
                            <i class="ti ti-cloud-upload"></i>
                            <span>Click to upload logo</span>
                            <input type="file" name="invoice_logo" id="invoice_logo" accept="image/*">
                        </label>
                        <img id="invoice_image" class="ps-logo-preview" src="" alt="Logo Preview">
                    </div>
                    <button type="submit" class="ps-save-btn green">
                        <i class="ti ti-device-floppy"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
        <div class="ps-preview-card">
            <div class="ps-preview-header">
                <span class="ps-preview-dot" style="background:#ef4444;"></span>
                <span class="ps-preview-dot" style="background:#f59e0b;"></span>
                <span class="ps-preview-dot" style="background:#22c55e;"></span>
                <span style="font-size:.78rem;color:#94a3b8;font-weight:600;margin-left:8px;">Live Preview</span>
            </div>
            @if(isset($settings['invoice_template']) && isset($settings['invoice_color']))
                <iframe id="invoice_frame" class="ps-preview-iframe" src="{{ route('invoice.preview', [$settings['invoice_template'], $settings['invoice_color']]) }}"></iframe>
            @else
                <iframe id="invoice_frame" class="ps-preview-iframe" src="{{ route('invoice.preview', ['template1', 'ffffff']) }}"></iframe>
            @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- BILL TAB --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="ps-tab-pane" id="tab-bill">
    <div class="ps-layout">
        <div class="ps-panel">
            <div class="ps-panel-header">
                <div class="ps-panel-icon purple"><i class="ti ti-receipt"></i></div>
                <div>
                    <p class="ps-panel-title">Bill Settings</p>
                    <p style="font-size:.75rem;color:#94a3b8;margin:0;">Template & branding</p>
                </div>
            </div>
            <div class="ps-panel-body">
                <form method="post" action="{{ route('bill.template.setting') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="ps-form-label">Template</label>
                        <select class="ps-select" name="bill_template">
                            @foreach(App\Models\Utility::templateData()['templates'] as $key => $template)
                                <option value="{{ $key }}" {{ (isset($settings['bill_template']) && $settings['bill_template'] == $key) ? 'selected' : '' }}>{{ $template }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="ps-form-label">Color Theme</label>
                        <div class="ps-colors">
                            @foreach(App\Models\Utility::templateData()['colors'] as $key => $color)
                                <label class="ps-color-label">
                                    <input name="bill_color" type="radio" value="{{ $color }}" {{ (isset($settings['bill_color']) && $settings['bill_color'] == $color) ? 'checked' : '' }}>
                                    <span class="ps-color-swatch" style="background:#{{ $color }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="ps-form-label">Bill Logo</label>
                        <label class="ps-upload-area" for="bill_logo">
                            <i class="ti ti-cloud-upload"></i>
                            <span>Click to upload logo</span>
                            <input type="file" name="bill_logo" id="bill_logo" accept="image/*">
                        </label>
                        <img id="bill_image" class="ps-logo-preview" src="" alt="Logo Preview">
                    </div>
                    <button type="submit" class="ps-save-btn purple">
                        <i class="ti ti-device-floppy"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
        <div class="ps-preview-card">
            <div class="ps-preview-header">
                <span class="ps-preview-dot" style="background:#ef4444;"></span>
                <span class="ps-preview-dot" style="background:#f59e0b;"></span>
                <span class="ps-preview-dot" style="background:#22c55e;"></span>
                <span style="font-size:.78rem;color:#94a3b8;font-weight:600;margin-left:8px;">Live Preview</span>
            </div>
            @if(isset($settings['bill_template']) && isset($settings['bill_color']))
                <iframe id="bill_frame" class="ps-preview-iframe" src="{{ route('bill.preview', [$settings['bill_template'], $settings['bill_color']]) }}"></iframe>
            @else
                <iframe id="bill_frame" class="ps-preview-iframe" src="{{ route('bill.preview', ['template1', 'ffffff']) }}"></iframe>
            @endif
        </div>
    </div>
</div>

<script>
function switchTab(tab, btn) {
    // Hide all panes
    document.querySelectorAll('.ps-tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.ps-tab-btn').forEach(b => b.classList.remove('active'));
    // Show selected
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}
// Show logo preview on file select
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
</script>
@endsection
