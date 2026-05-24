@extends('layouts.admin')
@section('page-title'){{ __('Print Settings') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Print Settings') }}</li>
@endsection

@push('script-page')
<script>
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
</style>

<div class="ps-page">

    {{-- ── Tab Navigation ── --}}
    <div class="ps-tabs">
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
