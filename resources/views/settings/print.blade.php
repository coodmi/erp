@extends('layouts.admin')
@section('page-title'){{ __('Print Settings') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Print Settings') }}</li>
@endsection

@push('script-page')
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
// ── data from server ──
var _psCustomers = {!! json_encode($customers) !!};
var _psVendors   = {!! json_encode($vendors) !!};
var _psAgents    = {!! json_encode($agents) !!};

var _psSelectedParty = null; // { id, name, email, partyType: 'agent'|'client'|'vendor' }

var _psDocType = 'invoice';

// ── color live preview ──
$(document).on('change', "input[name='invoice_color']", function () {
    if (_psDocType === 'invoice') refreshPreview();
});
$(document).on('change', "input[name='receipt_header_color']", function () {
    if (_psDocType === 'receipt') refreshReceiptPreview();
});

function partyQuery() {
    if (!_psSelectedParty || !_psSelectedParty.id) return '';
    var apiType = _psSelectedParty.partyType === 'client' ? 'customer' : _psSelectedParty.partyType;
    return '&party_id=' + _psSelectedParty.id + '&party_type=' + apiType;
}

function previewColor(val, fallback) {
    var c = (val || fallback || 'ffffff').toString().replace(/^#/, '');
    return c.match(/^[0-9a-fA-F]{6}$/) ? c : (fallback || 'ffffff');
}

function refreshPreview() {
    var color = previewColor($("input[name='invoice_color']:checked").val(), 'ffffff');
    var src   = '{{ url("/invoices/preview") }}/template1/' + color + '?t=' + Date.now() + partyQuery();
    document.getElementById('invoice_frame').src = src;
}

function refreshReceiptPreview() {
    var color = previewColor($("input[name='receipt_header_color']:checked").val(), '1e3a8a');
    var src   = '{{ url("/receipts/preview") }}/' + color + '?t=' + Date.now() + partyQuery();
    document.getElementById('receipt_frame').src = src;
}

function refreshActivePreview() {
    if (_psDocType === 'receipt') refreshReceiptPreview();
    else refreshPreview();
}

function getActivePreviewFrame() {
    return document.getElementById(_psDocType === 'receipt' ? 'receipt_frame' : 'invoice_frame');
}

function psPreviewFilename() {
    var base = _psDocType === 'receipt' ? 'money-receipt' : 'invoice';
    if (_psSelectedParty && _psSelectedParty.name) {
        base = _psSelectedParty.name.replace(/[^\w\s-]/g, '').trim().replace(/\s+/g, '-') + '-' + base;
    }
    return base + '-preview.pdf';
}

function whenPreviewFrameReady(frame, cb) {
    if (!frame) return;
    var run = function () {
        try {
            var doc = frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
            if (doc && doc.getElementById('boxes')) { cb(doc); return; }
        } catch (e) {}
        cb(null);
    };
    if (frame.contentDocument && frame.contentDocument.readyState === 'complete') {
        run();
    } else {
        frame.addEventListener('load', run, { once: true });
    }
}

function psPrintPreview() {
    var frame = getActivePreviewFrame();
    if (!frame || !frame.src) return;
    whenPreviewFrameReady(frame, function (doc) {
        if (doc) {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
                return;
            } catch (e) {}
        }
        window.open(frame.src, '_blank');
    });
}

function psDownloadPdf() {
    var frame = getActivePreviewFrame();
    if (!frame || !frame.src) return;
    if (typeof html2pdf === 'undefined') {
        window.open(frame.src + (frame.src.indexOf('?') >= 0 ? '&' : '?') + 'pdf=1', '_blank');
        return;
    }
    var btn = document.querySelector('.ps-preview-btn.pdf');
    if (btn) btn.disabled = true;
    whenPreviewFrameReady(frame, function (doc) {
        if (!doc) {
            if (btn) btn.disabled = false;
            window.open(frame.src + (frame.src.indexOf('?') >= 0 ? '&' : '?') + 'pdf=1', '_blank');
            return;
        }
        var element = doc.getElementById('boxes');
        if (!element) {
            if (btn) btn.disabled = false;
            alert('{{ __("Preview not ready. Please wait a moment and try again.") }}');
            return;
        }
        var opt = {
            filename: psPreviewFilename(),
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 3, useCORS: true, letterRendering: true },
            jsPDF: { unit: 'in', format: 'A4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save().then(function () {
            if (btn) btn.disabled = false;
        }).catch(function () {
            if (btn) btn.disabled = false;
            window.open(frame.src + (frame.src.indexOf('?') >= 0 ? '&' : '?') + 'pdf=1', '_blank');
        });
    });
}

function setDocType(type) {
    _psDocType = type;
    var invBtn = document.getElementById('ps_doc_invoice');
    var recBtn = document.getElementById('ps_doc_receipt');
    if (invBtn) invBtn.className = 'ps-doc-tab' + (type === 'invoice' ? ' active-invoice' : '');
    if (recBtn) recBtn.className = 'ps-doc-tab' + (type === 'receipt' ? ' active-receipt' : '');
    document.getElementById('ps_invoice_settings').style.display = type === 'invoice' ? '' : 'none';
    document.getElementById('ps_receipt_settings').style.display = type === 'receipt' ? '' : 'none';
    document.getElementById('ps_preview_invoice').style.display = type === 'invoice' ? '' : 'none';
    document.getElementById('ps_preview_receipt').style.display = type === 'receipt' ? '' : 'none';
    var sub = document.getElementById('ps_party_sub');
    if (sub) sub.textContent = type === 'receipt'
        ? '{{ __("Preview money receipt for Agent, Client or Vendor") }}'
        : '{{ __("Preview invoice for Agent, Client or Vendor") }}';
    refreshActivePreview();
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

    // After page reload (company info saved), refresh preview with cache bust
    @if(session('success'))
    setTimeout(function() { refreshActivePreview(); }, 300);
    @endif
    var rInp = document.getElementById('receipt_logo');
    var rImg = document.getElementById('receipt_image');
    if (rInp && rImg) {
        rInp.addEventListener('change', function () {
            if (this.files[0]) { rImg.src = URL.createObjectURL(this.files[0]); rImg.style.display = 'block'; }
        });
    }
    ['signature_cashier', 'signature_manager', 'signature_md'].forEach(function (id) {
        var inp = document.getElementById(id);
        var prev = document.getElementById('sig_preview_' + id.replace('signature_', ''));
        if (!inp || !prev) return;
        inp.addEventListener('change', function () {
            if (this.files[0]) {
                prev.src = URL.createObjectURL(this.files[0]);
                prev.style.display = 'block';
            }
        });
    });
    initPsSigCarousel();
});

function initPsSigCarousel() {
    var track = document.getElementById('ps_sig_carousel');
    var viewport = track && track.closest('.ps-sig-carousel-viewport');
    var prevBtn = document.getElementById('ps_sig_prev');
    var nextBtn = document.getElementById('ps_sig_next');
    var dotsWrap = document.getElementById('ps_sig_dots');
    if (!track || !viewport) return;

    var slots = track.querySelectorAll('.ps-sig-slot');
    if (!slots.length) return;

    function scrollAmount() {
        var slot = slots[0];
        return slot.offsetWidth + 14;
    }

    function activeIndex() {
        var amt = scrollAmount();
        if (amt <= 0) return 0;
        return Math.max(0, Math.min(slots.length - 1, Math.round(track.scrollLeft / amt)));
    }

    function updateUi() {
        var idx = activeIndex();
        var maxScroll = track.scrollWidth - track.clientWidth - 2;
        slots.forEach(function (el, i) {
            el.classList.toggle('is-active', i === idx);
        });
        if (dotsWrap) {
            dotsWrap.querySelectorAll('.ps-sig-dot').forEach(function (dot, i) {
                dot.classList.toggle('active', i === idx);
                dot.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
        }
        if (prevBtn) prevBtn.disabled = track.scrollLeft <= 2;
        if (nextBtn) nextBtn.disabled = track.scrollLeft >= maxScroll;
        viewport.style.setProperty('--sig-fade-l', track.scrollLeft > 4 ? '1' : '0');
        viewport.style.setProperty('--sig-fade-r', track.scrollLeft < maxScroll - 2 ? '1' : '0');
    }

    function scrollToIndex(i) {
        track.scrollTo({ left: scrollAmount() * i, behavior: 'smooth' });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            scrollToIndex(Math.max(0, activeIndex() - 1));
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            scrollToIndex(Math.min(slots.length - 1, activeIndex() + 1));
        });
    }
    if (dotsWrap) {
        dotsWrap.querySelectorAll('.ps-sig-dot').forEach(function (dot) {
            dot.addEventListener('click', function () {
                scrollToIndex(parseInt(this.getAttribute('data-index'), 10) || 0);
            });
        });
    }

    track.addEventListener('scroll', function () {
        window.requestAnimationFrame(updateUi);
    }, { passive: true });
    window.addEventListener('resize', updateUi);
    updateUi();
}

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
.ps-preview-col { position:relative; min-width:0; }

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
.ps-card-body  { padding:18px 20px; container-type:inline-size; }

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
.ps-preview-actions { display:flex; align-items:center; gap:8px; margin-left:auto; flex-shrink:0; }
.ps-preview-btn {
    display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:10px;
    font-size:.76rem; font-weight:700; border:0; cursor:pointer; transition:all .18s; white-space:nowrap;
}
.ps-preview-btn.print { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; box-shadow:0 4px 12px rgba(37,99,235,.2); }
.ps-preview-btn.pdf { background:#fff; color:#475569; border:1.5px solid #e2e8f0; }
.ps-preview-btn:hover { filter:brightness(1.05); transform:translateY(-1px); }
.ps-preview-btn:disabled { opacity:.55; cursor:wait; transform:none; }
/* ── Signature carousel ── */
.ps-sig-carousel-outer { margin-top:4px; }
.ps-sig-carousel-top {
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    margin-bottom:10px; flex-wrap:wrap;
}
.ps-sig-scroll-hint {
    display:inline-flex; align-items:center; gap:6px; font-size:.68rem; font-weight:600;
    color:#94a3b8; letter-spacing:.02em;
}
.ps-sig-nav-group { display:flex; gap:6px; }
.ps-sig-nav {
    width:34px; height:34px; border-radius:10px; border:1.5px solid #e2e8f0;
    background:#fff; color:#475569; cursor:pointer; display:inline-flex;
    align-items:center; justify-content:center; transition:all .18s;
    box-shadow:0 2px 8px rgba(15,23,42,.06);
}
.ps-sig-nav:hover:not(:disabled) {
    border-color:#6366f1; color:#4f46e5; background:#eef2ff;
    transform:translateY(-1px); box-shadow:0 4px 12px rgba(99,102,241,.18);
}
.ps-sig-nav:disabled { opacity:.35; cursor:not-allowed; transform:none; }
.ps-sig-carousel-viewport {
    position:relative; margin:0 -6px; padding:0 6px;
}
.ps-sig-carousel-viewport::before,
.ps-sig-carousel-viewport::after {
    content:''; position:absolute; top:0; bottom:10px; width:28px; z-index:2;
    pointer-events:none; transition:opacity .2s;
}
.ps-sig-carousel-viewport::before {
    left:0; background:linear-gradient(90deg,#fff 0%,rgba(255,255,255,0));
    opacity:var(--sig-fade-l, 0);
}
.ps-sig-carousel-viewport::after {
    right:0; background:linear-gradient(270deg,#fff 0%,rgba(255,255,255,0));
    opacity:var(--sig-fade-r, 1);
}
.ps-sig-carousel {
    display:flex; gap:14px; overflow-x:auto; overflow-y:hidden;
    scroll-snap-type:x mandatory; scroll-behavior:smooth;
    padding:4px 2px 12px; -webkit-overflow-scrolling:touch;
    scrollbar-width:thin; scrollbar-color:#c7d2fe transparent;
}
.ps-sig-carousel::-webkit-scrollbar { height:5px; }
.ps-sig-carousel::-webkit-scrollbar-track { background:transparent; border-radius:99px; }
.ps-sig-carousel::-webkit-scrollbar-thumb {
    background:linear-gradient(90deg,#a5b4fc,#818cf8); border-radius:99px;
}
.ps-sig-slot {
    flex:0 0 88%; min-width:240px; max-width:280px;
    scroll-snap-align:center; scroll-snap-stop:always;
    border:1.5px dashed #cbd5e1; border-radius:14px; padding:14px 12px;
    background:linear-gradient(180deg,#fff 0%,#f8fafc 100%);
    text-align:center; box-shadow:0 4px 16px rgba(15,23,42,.05);
    transition:border-color .2s, box-shadow .2s;
}
.ps-sig-slot.is-active {
    border-color:#818cf8; box-shadow:0 8px 24px rgba(99,102,241,.12);
}
.ps-sig-slot label.ps-label {
    display:block; margin-bottom:10px; font-size:.7rem;
    letter-spacing:.04em; color:#475569;
}
.ps-sig-current { max-height:56px; max-width:100%; object-fit:contain; margin:0 auto 8px; display:block; }
.ps-sig-upload { margin-top:6px; min-height:72px; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.ps-sig-dots {
    display:flex; justify-content:center; align-items:center; gap:8px; margin-top:12px;
}
.ps-sig-dot {
    width:8px; height:8px; border-radius:50%; border:0; padding:0; cursor:pointer;
    background:#e2e8f0; transition:all .22s;
}
.ps-sig-dot.active {
    width:22px; border-radius:99px;
    background:linear-gradient(90deg,#818cf8,#6366f1);
    box-shadow:0 2px 8px rgba(99,102,241,.35);
}
@container (min-width: 560px) {
    .ps-sig-carousel-top .ps-sig-scroll-hint,
    .ps-sig-carousel-top .ps-sig-nav-group,
    .ps-sig-dots { display:none; }
    .ps-sig-carousel-viewport::before,
    .ps-sig-carousel-viewport::after { display:none; }
    .ps-sig-carousel {
        display:grid; grid-template-columns:repeat(3,1fr);
        overflow:visible; scroll-snap-type:none; gap:12px; padding-bottom:0;
    }
    .ps-sig-slot {
        flex:unset; min-width:0; max-width:none;
        scroll-snap-align:unset; scroll-snap-stop:normal;
    }
}
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

/* ── Document type tabs ── */
.ps-doc-tabs {
    display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px;
}
.ps-doc-tab {
    padding:12px 10px; border-radius:12px; border:2px solid var(--c-border);
    background:#fff; cursor:pointer; font-size:.8rem; font-weight:800;
    color:var(--c-muted); display:flex; align-items:center; justify-content:center; gap:7px;
    transition:all .18s;
}
.ps-doc-tab i { font-size:1rem; }
.ps-doc-tab.active-invoice { border-color:#059669; color:#047857; background:#ecfdf5; }
.ps-doc-tab.active-receipt { border-color:#2563eb; color:#1d4ed8; background:#eff6ff; }
.ps-card-icon.purple { background:linear-gradient(135deg,#a78bfa,#7c3aed); box-shadow:0 5px 12px rgba(124,58,237,.25); }
</style>

<div class="ps-page">
<div class="ps-main-grid">

{{-- ════════════════════════════════════════ --}}
{{-- LEFT PANEL --}}
{{-- ════════════════════════════════════════ --}}
<div class="ps-left">

    {{-- Document type: Invoice | Money Receipt --}}
    <div class="ps-doc-tabs">
        <button type="button" class="ps-doc-tab active-invoice" id="ps_doc_invoice" onclick="setDocType('invoice')">
            <i class="ti ti-file-invoice"></i> {{ __('Invoice') }}
        </button>
        <button type="button" class="ps-doc-tab" id="ps_doc_receipt" onclick="setDocType('receipt')">
            <i class="ti ti-receipt"></i> {{ __('Money Receipt') }}
        </button>
    </div>

    {{-- ── 1. Party Selector ── --}}
    <div class="ps-card">
        <div class="ps-card-head">
            <div class="ps-card-icon orange"><i class="ti ti-users"></i></div>
            <div>
                <p class="ps-card-title">{{ __('Select Party') }}</p>
                <p class="ps-card-sub" id="ps_party_sub">{{ __('Preview invoice for Agent, Client or Vendor') }}</p>
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
    <div class="ps-card" id="ps_invoice_settings">
        <div class="ps-card-head">
            <div class="ps-card-icon green"><i class="ti ti-file-invoice"></i></div>
            <div>
                <p class="ps-card-title">{{ __('Invoice Settings') }}</p>
                <p class="ps-card-sub">{{ __('Color & branding') }}</p>
            </div>
        </div>
        <div class="ps-card-body">
            <form method="post" action="{{ route('template.setting') }}" enctype="multipart/form-data">
                @csrf
                {{-- hidden template field so save still works --}}
                <input type="hidden" name="invoice_template" value="{{ $settings['invoice_template'] ?? 'template1' }}">
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
                            <img src="{{ \App\Models\Utility::printFileUrl('invoice_logo', $cur_invoice_logo) }}" alt="Logo">
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

    {{-- ── Money Receipt Settings ── --}}
    <div class="ps-card" id="ps_receipt_settings" style="display:none;">
        <div class="ps-card-head">
            <div class="ps-card-icon purple"><i class="ti ti-receipt"></i></div>
            <div>
                <p class="ps-card-title">{{ __('Money Receipt Settings') }}</p>
                <p class="ps-card-sub">{{ __('Color, logo & receipt text') }}</p>
            </div>
        </div>
        <div class="ps-card-body">
            <form method="post" action="{{ route('receipt.settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="ps-field">
                    <label class="ps-label">{{ __('Header Color') }}</label>
                    <div class="ps-swatches">
                        @foreach(App\Models\Utility::templateData()['colors'] as $key => $rcolor)
                            <label class="ps-swatch-label">
                                <input name="receipt_header_color" type="radio" value="{{ $rcolor }}"
                                    {{ (isset($settings['receipt_header_color']) && $settings['receipt_header_color'] == $rcolor) ? 'checked' : ((!isset($settings['receipt_header_color']) && $rcolor == '1e3a8a') ? 'checked' : '') }}>
                                <span class="ps-swatch" style="background:#{{ $rcolor }}"></span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="ps-divider"></div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Receipt Logo') }}</label>
                    @php $cur_receipt_logo = \App\Models\Utility::getValByName('receipt_logo'); @endphp
                    @if(!empty($cur_receipt_logo))
                        <div class="ps-current-logo">
                            <img src="{{ \App\Models\Utility::printFileUrl('receipt_logo', $cur_receipt_logo) }}" alt="Logo">
                            <span>✓ {{ __('Current logo active') }}</span>
                        </div>
                    @endif
                    <label class="ps-upload" for="receipt_logo">
                        <i class="ti ti-cloud-upload ps-upload-icon"></i>
                        <span class="ps-upload-text">{{ __('Click to upload receipt logo') }}</span>
                        <input type="file" name="receipt_logo" id="receipt_logo" accept="image/*">
                    </label>
                    <img id="receipt_image" class="ps-logo-preview" src="" alt="">
                </div>
                <div class="ps-divider"></div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Display Company Name') }}</label>
                    <input type="text" name="receipt_company_name" class="ps-input"
                        value="{{ $settings['receipt_company_name'] ?? '' }}"
                        placeholder="{{ $settings['company_name'] ?? __('Company Name') }}">
                </div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Notice Text') }}</label>
                    <textarea name="receipt_notice_text" class="ps-input" rows="2" style="height:auto;background-image:none;">{{ $settings['receipt_notice_text'] ?? __("Money receipts will not be considered valid without the MD's seal and signature.") }}</textarea>
                </div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Footer Text') }}</label>
                    <input type="text" name="receipt_footer_text" class="ps-input"
                        value="{{ $settings['receipt_footer_text'] ?? '' }}"
                        placeholder="{{ __('Optional footer line') }}">
                </div>
                <div class="ps-divider"></div>
                <div class="ps-field">
                    <label class="ps-label">{{ __('Signatures') }}</label>
                    <p class="ps-card-sub" style="margin:0 0 12px;">{{ __('Upload signature images for Cashier, Manager and MD. Shown on money receipts.') }}</p>
                    @php
                        $sigSlots = [
                            ['field' => 'signature_cashier', 'label' => __('Cashier Signature'), 'preview' => 'sig_preview_cashier'],
                            ['field' => 'signature_manager', 'label' => __('Manager Signature'), 'preview' => 'sig_preview_manager'],
                            ['field' => 'signature_md', 'label' => __('MD Signature & Seal'), 'preview' => 'sig_preview_md'],
                        ];
                    @endphp
                    <div class="ps-sig-carousel-outer">
                        <div class="ps-sig-carousel-top">
                            <span class="ps-sig-scroll-hint">
                                <i class="ti ti-hand-move"></i> {{ __('Swipe or scroll') }}
                            </span>
                            <div class="ps-sig-nav-group">
                                <button type="button" class="ps-sig-nav" id="ps_sig_prev" aria-label="{{ __('Previous signature') }}">
                                    <i class="ti ti-chevron-left"></i>
                                </button>
                                <button type="button" class="ps-sig-nav" id="ps_sig_next" aria-label="{{ __('Next signature') }}">
                                    <i class="ti ti-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="ps-sig-carousel-viewport">
                            <div class="ps-sig-carousel" id="ps_sig_carousel" role="tablist" aria-label="{{ __('Signature uploads') }}">
                                @foreach($sigSlots as $i => $slot)
                                    @php $curSig = $settings[$slot['field']] ?? ''; @endphp
                                    <div class="ps-sig-slot{{ $i === 0 ? ' is-active' : '' }}" role="tab" data-index="{{ $i }}">
                                        <label class="ps-label">{{ $slot['label'] }}</label>
                                @if(!empty($curSig))
                                    <img src="{{ \App\Models\Utility::printFileUrl('signatures', $curSig) }}" alt="{{ $slot['label'] }}" class="ps-sig-current">
                                            <span style="font-size:.68rem;color:#059669;font-weight:700;">✓ {{ __('Uploaded') }}</span>
                                        @endif
                                        <label class="ps-upload ps-sig-upload" for="{{ $slot['field'] }}">
                                            <i class="ti ti-writing ps-upload-icon"></i>
                                            <span class="ps-upload-text">{{ __('Upload image') }}</span>
                                            <input type="file" name="{{ $slot['field'] }}" id="{{ $slot['field'] }}" accept="image/png,image/jpeg,image/jpg">
                                        </label>
                                        <img id="{{ $slot['preview'] }}" class="ps-sig-current" src="" alt="" style="display:none;">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="ps-sig-dots" id="ps_sig_dots" role="tablist" aria-label="{{ __('Signature slides') }}">
                            @foreach($sigSlots as $i => $slot)
                                <button type="button" class="ps-sig-dot{{ $i === 0 ? ' active' : '' }}"
                                    data-index="{{ $i }}" aria-label="{{ $slot['label'] }}"
                                    aria-selected="{{ $i === 0 ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button type="submit" class="ps-btn green" style="background:linear-gradient(135deg,#818cf8,#6366f1);box-shadow:0 8px 20px rgba(99,102,241,.25);">
                    <i class="ti ti-device-floppy"></i> {{ __('Save Receipt Settings') }}
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
<div class="ps-preview-col">
<div id="ps_preview_invoice" class="ps-preview">
    <div class="ps-preview-bar">
        <span class="ps-dot" style="background:#ef4444;"></span>
        <span class="ps-dot" style="background:#f59e0b;"></span>
        <span class="ps-dot" style="background:#22c55e;"></span>
        <span class="ps-preview-label">{{ __('Live Preview — Invoice') }}</span>
        <span id="ps_preview_party_label" style="font-size:.72rem;font-weight:700;color:#2563eb;background:#eff6ff;padding:3px 10px;border-radius:20px;display:none;"></span>
        <div class="ps-preview-actions">
            <button type="button" class="ps-preview-btn print" onclick="psPrintPreview()" title="{{ __('Print') }}">
                <i class="ti ti-printer"></i> {{ __('Print') }}
            </button>
        </div>
    </div>
    @php
        $i_tpl   = $settings['invoice_template'] ?? 'template1';
        $i_color = $settings['invoice_color']    ?? 'ffffff';
    @endphp
    <iframe id="invoice_frame" class="ps-iframe"
        src="{{ route('invoice.preview', [$i_tpl, $i_color]) }}"></iframe>
</div>

<div id="ps_preview_receipt" class="ps-preview" style="display:none;">
    <div class="ps-preview-bar">
        <span class="ps-dot" style="background:#ef4444;"></span>
        <span class="ps-dot" style="background:#f59e0b;"></span>
        <span class="ps-dot" style="background:#22c55e;"></span>
        <span class="ps-preview-label">{{ __('Live Preview — Money Receipt') }}</span>
        <span id="ps_preview_party_label_rc" style="font-size:.72rem;font-weight:700;color:#6366f1;background:#eef2ff;padding:3px 10px;border-radius:20px;display:none;"></span>
        <div class="ps-preview-actions">
            <button type="button" class="ps-preview-btn print" onclick="psPrintPreview()" title="{{ __('Print') }}">
                <i class="ti ti-printer"></i> {{ __('Print') }}
            </button>
        </div>
    </div>
    @php $r_color = $settings['receipt_header_color'] ?? '1e3a8a'; @endphp
    <iframe id="receipt_frame" class="ps-iframe"
        src="{{ route('receipt.preview', $r_color) }}"></iframe>
</div>
</div>{{-- /ps-preview-col --}}

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
    var lblRc = document.getElementById('ps_preview_party_label_rc');
    if (lblRc) lblRc.style.display = 'none';
    _psSelectedParty = null;
    refreshActivePreview();
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

    var lbl = document.getElementById('ps_preview_party_label');
    lbl.textContent = item.name;
    lbl.style.display = 'inline-block';
    var lblRc = document.getElementById('ps_preview_party_label_rc');
    if (lblRc) { lblRc.textContent = item.name; lblRc.style.display = 'inline-block'; }

    refreshActivePreview();

  // Load address / contact for the info card
    var apiType = type === 'client' ? 'customer' : type;
    fetch('{{ route("print.recipient.data") }}?type=' + apiType + '&id=' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var addr = [];
        if (data.billing_address) addr.push(data.billing_address);
        if (data.billing_city)    addr.push(data.billing_city);
        if (data.billing_country) addr.push(data.billing_country);
        if (!addr.length && data.contact) addr.push(data.contact);
        if (!addr.length && data.email)   addr.push(data.email);
        document.getElementById('ps_party_addr').textContent = addr.join(', ') || '{{ __("No address on file") }}';
    }).catch(function() {});
}

function updatePartyDropdown() {
    // agents are pre-rendered in blade, just set the type
    _psCurrentType = 'agent';
}
</script>
@endsection
