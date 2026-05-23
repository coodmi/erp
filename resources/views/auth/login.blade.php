@extends('layouts.auth')
@php
    $logo       = \App\Models\Utility::get_file('uploads/logo/');
    $login_logo = \App\Models\Utility::GetLogo();
    $settings   = Utility::settings();
@endphp
@push('custom-scripts')
    @if ($settings['recaptcha_module'] == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush
@section('page-title'){{ __('Login') }}@endsection
@php $languages = App\Models\Utility::languages(); @endphp
@section('language-bar')
<div class="lang-dropdown-only-desk">
    <li class="dropdown dash-h-item drp-language">
        <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="drp-text">{{ $languages[$lang] }}</span>
        </a>
        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
            @foreach($languages as $code => $language)
                <a href="{{ route('login',$code) }}" tabindex="0" class="dropdown-item">
                    <span>{{ Str::ucfirst($language) }}</span>
                </a>
            @endforeach
        </div>
    </li>
</div>
@endsection

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html, body {
    min-height:100% !important; width:100% !important;
    overflow-x:hidden !important;
    font-family:'Inter','Segoe UI',system-ui,sans-serif !important;
    background:#0f172a !important;
}
/* Kill layout wrappers */
.custom-login,.custom-login-inner,.custom-wrapper,.custom-row,.card,.card-body {
    all:unset !important; display:block !important;
    width:100% !important; height:100% !important;
    padding:0 !important; margin:0 !important;
    background:transparent !important;
    border:none !important; box-shadow:none !important;
}

/* ── Shell ── */
.lp-shell {
    display:flex; flex-wrap:nowrap;
    width:100%; min-height:100vh; min-height:100dvh;
    font-family:'Inter','Segoe UI',system-ui,sans-serif;
}

/* ══════════════════════════
   LEFT PANEL
══════════════════════════ */
.lp-left {
    flex:1 1 480px; width:100%; max-width:520px; min-width:0;
    min-height:100vh; min-height:100dvh;
    background:#f1f5f9;
    display:flex; flex-direction:column;
    justify-content:center; align-items:center;
    padding:clamp(24px,4vw,52px);
    position:relative; z-index:2;
}
.lp-left::before {
    content:''; position:absolute;
    top:-80px; left:-80px;
    width:260px; height:260px;
    background:radial-gradient(circle,rgba(99,102,241,.07) 0%,transparent 70%);
    border-radius:50%; pointer-events:none;
}
.lp-left::after {
    content:''; position:absolute;
    bottom:-60px; right:-60px;
    width:200px; height:200px;
    background:radial-gradient(circle,rgba(16,185,129,.06) 0%,transparent 70%);
    border-radius:50%; pointer-events:none;
}

.lp-form-inner {
    width:100%; max-width:420px;
    position:relative; z-index:1;
    background:#fff;
    border-radius:clamp(16px,2vw,20px);
    padding:clamp(28px,4vw,40px) clamp(24px,3.5vw,40px) clamp(24px,3vw,36px);
    box-shadow:0 4px 32px rgba(15,23,42,.08), 0 1px 4px rgba(15,23,42,.04);
    overflow:hidden;
}

/* Logo */
.lp-logo { margin-bottom:clamp(24px,3vw,32px); }
.lp-logo img {
    height:auto; max-height:48px; max-width:min(220px,100%);
    width:auto; object-fit:contain; display:block;
}
.lp-logo-fallback {
    display:inline-flex; align-items:center; gap:10px;
}
.lp-logo-icon {
    width:38px; height:38px;
    background:linear-gradient(135deg,#6366f1,#4f46e5);
    border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:16px; font-weight:800; flex-shrink:0;
}
.lp-logo-name { font-size:18px; font-weight:800; color:#0f172a; letter-spacing:-0.3px; }

/* Heading (mobile / narrow — hidden when right panel shows copy) */
.lp-heading { margin-bottom:clamp(20px,3vw,28px); }
.lp-heading .lp-badge--light {
    display:inline-flex; align-items:center; gap:8px;
    background:rgba(99,102,241,.1);
    border:1px solid rgba(99,102,241,.25);
    color:#4f46e5; font-size:11px; font-weight:700;
    padding:7px 14px; border-radius:100px;
    margin-bottom:12px; letter-spacing:0.8px; text-transform:uppercase;
}
.lp-heading .lp-badge--light .lp-badge-dot { background:#6366f1; }
.lp-heading p {
    font-size:clamp(13px,1.8vw,14px); color:#64748b;
    margin:0; line-height:1.6;
}

/* Alert */
.lp-alert {
    background:#f0fdf4; border:1px solid #bbf7d0;
    border-left:4px solid #22c55e; color:#166534;
    padding:11px 14px; border-radius:10px;
    margin-bottom:20px; font-size:13px; font-weight:500;
}

/* Field */
.lp-field { margin-bottom:18px; }
.lp-label {
    display:block; font-size:11.5px; font-weight:700;
    color:#374151; margin-bottom:7px;
    text-transform:uppercase; letter-spacing:0.7px;
}
.lp-input-wrap { position:relative; }
.lp-input {
    display:block; width:100%; height:48px;
    padding:0 16px;
    border:1.5px solid #e8edf2;
    border-radius:10px;
    font-size:14px; color:#0f172a;
    background:#f4f7fb;
    outline:none;
    box-sizing:border-box;
    transition:border-color .2s, box-shadow .2s, background .2s;
    font-family:inherit;
}
.lp-input:focus {
    border-color:#6366f1; background:#fff;
    box-shadow:0 0 0 4px rgba(99,102,241,.1);
}
.lp-input.pw { padding-right:50px; }
.lp-input::placeholder { color:#a0aec0; }

.lp-eye {
    position:absolute; right:10px; top:50%;
    transform:translateY(-50%);
    width:34px; height:34px;
    background:#eef2ff; border:1px solid #dbe3ff; border-radius:9px;
    cursor:pointer; padding:0;
    color:#4f46e5; display:flex; align-items:center; justify-content:center;
    transition:background .2s, color .2s; line-height:1;
    z-index:3; flex-shrink:0;
}
.lp-eye:hover { background:#e0e7ff; }
.lp-eye-icon {
    position:relative; width:18px; height:18px; display:block;
}
.lp-eye-icon .ti {
    position:absolute; inset:0;
    font-size:18px !important; line-height:18px !important;
    color:#4f46e5 !important;
    display:flex !important; align-items:center; justify-content:center;
    transition:opacity .15s;
}
.lp-eye-icon .ti.is-hidden {
    opacity:0; visibility:hidden; pointer-events:none;
}
.lp-eye:hover .ti { color:#312e81 !important; }

.lp-error {
    display:block; color:#ef4444;
    font-size:12px; margin-top:5px; font-weight:500;
}

/* Forgot */
.lp-forgot-row {
    display:flex; justify-content:flex-end;
    margin:-4px 0 20px;
}
.lp-forgot {
    font-size:13px; color:#6366f1;
    text-decoration:none; font-weight:600;
    transition:color .2s;
}
.lp-forgot:hover { color:#4f46e5; text-decoration:underline; }

/* reCAPTCHA */
.lp-captcha { margin-bottom:20px; display:flex; justify-content:center; }

/* Submit */
.lp-btn {
    display:flex; align-items:center; justify-content:center; gap:8px;
    width:100%; height:48px;
    background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);
    color:#fff; border:none; border-radius:10px;
    font-size:15px; font-weight:700; letter-spacing:0.2px;
    cursor:pointer; box-sizing:border-box;
    box-shadow:0 4px 20px rgba(99,102,241,.35);
    transition:transform .18s, box-shadow .18s, filter .18s;
    font-family:inherit;
}
.lp-btn:hover {
    filter:brightness(1.08); transform:translateY(-2px);
    box-shadow:0 8px 28px rgba(99,102,241,.42);
}
.lp-btn:active { transform:translateY(0); box-shadow:0 2px 10px rgba(99,102,241,.3); }
.lp-btn svg { display:block !important; width:17px !important; height:17px !important; }

/* Divider */
.lp-divider {
    display:flex; align-items:center; gap:12px;
    margin:22px 0 0;
    color:#cbd5e1; font-size:11.5px; font-weight:600;
    text-transform:uppercase; letter-spacing:0.5px;
}
.lp-divider::before,.lp-divider::after {
    content:''; flex:1; height:1px; background:#e8edf2;
}

/* Copyright */
.lp-copy {
    text-align:center; margin-top:28px;
    font-size:12px; color:#94a3b8;
}

/* ══════════════════════════
   RIGHT PANEL
══════════════════════════ */
.lp-right {
    flex:1 1 50%; min-width:0; min-height:100vh; min-height:100dvh;
    background:linear-gradient(145deg,#0f172a 0%,#1e1b4b 55%,#0f172a 100%);
    display:flex; align-items:center; justify-content:center;
    padding:clamp(32px,5vw,48px) clamp(24px,4vw,40px);
    position:relative; overflow:hidden;
}
.lp-dots {
    position:absolute; inset:0;
    background-image:radial-gradient(rgba(255,255,255,.055) 1px,transparent 1px);
    background-size:30px 30px; pointer-events:none;
}
.lp-blob {
    position:absolute; border-radius:50%;
    filter:blur(70px); pointer-events:none;
    animation:blobPulse 9s ease-in-out infinite;
}
.lp-blob-1 {
    width:420px; height:420px;
    background:radial-gradient(circle,rgba(99,102,241,.5),rgba(79,70,229,.2));
    top:-120px; right:-100px; animation-delay:0s;
}
.lp-blob-2 {
    width:320px; height:320px;
    background:radial-gradient(circle,rgba(6,182,212,.45),rgba(14,165,233,.15));
    bottom:-60px; left:-80px; animation-delay:-3.5s;
}
.lp-blob-3 {
    width:220px; height:220px;
    background:radial-gradient(circle,rgba(168,85,247,.4),rgba(236,72,153,.15));
    bottom:180px; right:100px; animation-delay:-6s;
}
@keyframes blobPulse {
    0%,100% { transform:scale(1) translateY(0); }
    50%      { transform:scale(1.06) translateY(-20px); }
}

.lp-right-inner {
    position:relative; z-index:1;
    max-width:min(420px,100%); width:100%; text-align:center;
}

/* Badge */
.lp-badge {
    display:inline-flex; align-items:center; gap:8px;
    background:rgba(99,102,241,.15);
    border:1px solid rgba(99,102,241,.3);
    color:#a5b4fc; font-size:11px; font-weight:700;
    padding:7px 16px; border-radius:100px;
    margin-bottom:24px; letter-spacing:0.8px; text-transform:uppercase;
}
.lp-badge-dot {
    width:7px; height:7px; background:#6366f1;
    border-radius:50%; animation:dotBlink 2s ease-in-out infinite;
}
@keyframes dotBlink { 0%,100%{opacity:1;} 50%{opacity:.3;} }

.lp-right-title {
    font-size:32px; font-weight:900; color:#f1f5f9;
    line-height:1.15; letter-spacing:-0.5px;
    margin-bottom:14px; white-space:nowrap;
}
.lp-right-title .grad {
    background:linear-gradient(90deg,#818cf8 0%,#38bdf8 100%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    background-clip:text;
}

.lp-right-sub {
    font-size:clamp(13px,1.6vw,14.5px); color:#94a3b8; line-height:1.7;
    margin-bottom:clamp(24px,3vw,36px); max-width:400px;
    margin-left:auto; margin-right:auto;
}

/* Feature cards */
.lp-features { display:flex; flex-direction:column; gap:12px; text-align:left; margin-bottom:28px; }
.lp-feat {
    display:flex; align-items:center; gap:16px;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.08);
    border-radius:14px; padding:15px 18px;
    backdrop-filter:blur(10px);
    transition:background .25s, border-color .25s, transform .25s;
}
.lp-feat:hover {
    background:rgba(255,255,255,.08);
    border-color:rgba(99,102,241,.35);
    transform:translateX(4px);
}
.lp-feat-icon {
    width:44px; height:44px; border-radius:11px;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; font-size:0; line-height:1;
}
.fi-purple { background:rgba(99,102,241,.22); border:1px solid rgba(99,102,241,.45); }
.fi-cyan   { background:rgba(6,182,212,.18);  border:1px solid rgba(6,182,212,.45);  }
.fi-green  { background:rgba(16,185,129,.18); border:1px solid rgba(16,185,129,.45); }
.lp-feat-icon .ti {
    font-size:20px !important; line-height:1 !important;
    display:block !important;
}
.lp-feat-text h4 { font-size:13.5px; font-weight:700; color:#e2e8f0; margin:0 0 3px; }
.lp-feat-text p  { font-size:12px; color:#64748b; margin:0; line-height:1.45; }

/* Stats */
.lp-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
.lp-stat {
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.07);
    border-radius:12px; padding:16px 8px; text-align:center;
}
.lp-stat strong {
    display:block; font-size:20px; font-weight:900;
    color:#f1f5f9; letter-spacing:-0.5px; margin-bottom:4px;
}
.lp-stat span {
    font-size:10.5px; color:#475569; font-weight:600;
    text-transform:uppercase; letter-spacing:0.5px;
}

/* ── Responsive ── */
@media (min-width:992px) {
    .lp-heading { display:none; }
}
@media (max-width:1100px) {
    .lp-left { max-width:460px; }
    .lp-feat:hover { transform:none; }
}
@media (max-width:991px) {
    .lp-shell {
        flex-direction:column;
        min-height:100dvh;
    }
    .lp-right {
        flex:none; width:100%; min-height:auto;
        order:-1;
        padding:clamp(28px,5vw,40px) clamp(20px,4vw,28px) clamp(20px,3vw,24px);
    }
    .lp-right-inner { max-width:560px; }
    .lp-right-sub { max-width:100%; }
    .lp-features {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));
        gap:10px; text-align:left;
    }
    .lp-stats { max-width:420px; margin:0 auto; }
    .lp-left {
        flex:1; max-width:100%; min-height:auto;
        padding:clamp(20px,4vw,32px) clamp(16px,4vw,28px) clamp(32px,5vw,48px);
    }
    .lp-form-inner { max-width:480px; margin:0 auto; }
    .lp-blob { opacity:.7; }
}
@media (max-width:640px) {
    .lp-features { grid-template-columns:1fr; }
    .lp-feat { padding:14px 16px; }
    .lp-stat strong { font-size:18px; }
    .lp-badge { font-size:10px; padding:6px 14px; }
    .lp-input { height:46px; font-size:16px; }
    .lp-btn { height:46px; min-height:46px; }
}
@media (max-width:400px) {
    .lp-form-inner { padding:24px 18px 22px; border-radius:16px; }
    .lp-stats { gap:8px; }
    .lp-stat { padding:12px 6px; }
    .lp-stat strong { font-size:16px; }
    .lp-stat span { font-size:9px; }
}
@media (prefers-reduced-motion:reduce) {
    .lp-blob, .lp-badge-dot { animation:none; }
    .lp-btn:hover, .lp-feat:hover { transform:none; }
}
@keyframes lpSpin { to { transform:rotate(360deg); } }
</style>

<div class="lp-shell">

    {{-- ══ LEFT: Form ══ --}}
    <div class="lp-left">
        <div class="lp-form-inner">

            {{-- Logo --}}
            <div class="lp-logo">
                <img src="{{ !empty($login_logo) ? $logo . '/' . $login_logo : asset('Alphainno.png') }}"
                     alt="Alphainno ERP"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                <div class="lp-logo-fallback" style="display:none;">
                    <span class="lp-logo-icon">A</span>
                    <span class="lp-logo-name">Alphainno ERP</span>
                </div>
            </div>

            <div class="lp-heading">
                <div class="lp-badge lp-badge--light">
                    <span class="lp-badge-dot"></span>
                    Alphainno ERP
                </div>
                <p>Welcome to Alphainno Visa Consultancy ERP, your all-in-one solution for managing visa applications, clients, and documentation with ease and efficiency.</p>
            </div>

            {{-- Status --}}
            @if(session('status'))
                <div class="lp-alert">{{ session('status') }}</div>
            @endif

            {{-- Form --}}
            {{ Form::open(['route'=>'login','method'=>'post','id'=>'loginForm']) }}
                @csrf

                {{-- Email --}}
                <div class="lp-field">
                    <label class="lp-label" for="lp-email">{{ __('Email Address') }}</label>
                    <div class="lp-input-wrap">
                        {{ Form::email('email', null, [
                            'class'        => 'lp-input',
                            'id'           => 'lp-email',
                            'placeholder'  => 'you@example.com',
                            'required'     => 'required',
                            'autocomplete' => 'email',
                        ]) }}
                    </div>
                    @error('email')
                        <span class="lp-error" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="lp-field">
                    <label class="lp-label" for="input-password">{{ __('Password') }}</label>
                    <div class="lp-input-wrap">
                        {{ Form::password('password', [
                            'class'        => 'lp-input pw',
                            'id'           => 'input-password',
                            'placeholder'  => __('Enter your password'),
                            'required'     => 'required',
                            'autocomplete' => 'current-password',
                        ]) }}
                        <button type="button" class="lp-eye" id="lpTogglePw" aria-label="{{ __('Toggle password visibility') }}">
                            <span class="lp-eye-icon">
                                <i class="ti ti-eye" id="lp-eye-on"></i>
                                <i class="ti ti-eye-off is-hidden" id="lp-eye-off"></i>
                            </span>
                        </button>
                    </div>
                    @error('password')
                        <span class="lp-error" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                {{-- Forgot password removed --}}

                {{-- reCAPTCHA --}}
                @if($settings['recaptcha_module'] == 'on')
                    <div class="lp-captcha">
                        {!! NoCaptcha::display($settings['cust_darklayout']=='on' ? ['data-theme'=>'dark'] : []) !!}
                        @error('g-recaptcha-response')
                            <span class="lp-error" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                @endif

                {{-- Submit --}}
                <button type="submit" class="lp-btn" id="saveBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    {{ __('Login') }}
                </button>

                <div class="lp-copy">
                    &copy; {{ date('Y') }} Alphainno. All rights reserved.
                </div>

            {{ Form::close() }}
        </div>
    </div>

    {{-- ══ RIGHT: Branding ══ --}}
    <div class="lp-right">
        <div class="lp-dots"></div>
        <div class="lp-blob lp-blob-1"></div>
        <div class="lp-blob lp-blob-2"></div>
        <div class="lp-blob lp-blob-3"></div>

        <div class="lp-right-inner">
            <div class="lp-badge">
                <span class="lp-badge-dot"></span>
                Alphainno ERP
            </div>
            <p class="lp-right-sub">
                Welcome to Alphainno Visa Consultancy ERP, your all-in-one solution for managing visa applications, clients, and documentation with ease and efficiency.
            </p>

            <div class="lp-features">
                <div class="lp-feat">
                    <div class="lp-feat-icon fi-purple">
                        <i class="ti ti-chart-bar" style="font-size:20px;color:#a5b4fc;"></i>
                    </div>
                    <div class="lp-feat-text">
                        <h4>{{ __('Powerful Analytics') }}</h4>
                        <p>{{ __('Real-time dashboards and actionable insights across all modules') }}</p>
                    </div>
                </div>
                <div class="lp-feat">
                    <div class="lp-feat-icon fi-cyan">
                        <i class="ti ti-shield-lock" style="font-size:20px;color:#67e8f9;"></i>
                    </div>
                    <div class="lp-feat-text">
                        <h4>{{ __('Enterprise-grade Security') }}</h4>
                        <p>{{ __('Role-based access control with bank-level data encryption') }}</p>
                    </div>
                </div>
                <div class="lp-feat">
                    <div class="lp-feat-icon fi-green">
                        <i class="ti ti-cloud" style="font-size:20px;color:#6ee7b7;"></i>
                    </div>
                    <div class="lp-feat-text">
                        <h4>{{ __('Cloud-first Architecture') }}</h4>
                        <p>{{ __('Seamlessly access your workspace from any device, anywhere') }}</p>
                    </div>
                </div>
            </div>

            <div class="lp-stats">
                <div class="lp-stat">
                    <strong>456+</strong>
                    <span>{{ __('Users') }}</span>
                </div>
                <div class="lp-stat">
                    <strong>4.5★</strong>
                    <span>{{ __('Rating') }}</span>
                </div>
                <div class="lp-stat">
                    <strong>50+</strong>
                    <span>{{ __('Modules') }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
(function(){
    var pw     = document.getElementById('input-password');
    var eyeOn  = document.getElementById('lp-eye-on');
    var eyeOff = document.getElementById('lp-eye-off');
    document.getElementById('lpTogglePw').addEventListener('click', function(){
        if(pw.type==='password'){
            pw.type='text';
            eyeOn.classList.add('is-hidden');
            eyeOff.classList.remove('is-hidden');
        } else {
            pw.type='password';
            eyeOn.classList.remove('is-hidden');
            eyeOff.classList.add('is-hidden');
        }
    });
    document.getElementById('loginForm').addEventListener('submit', function(){
        var btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" style="width:17px;height:17px;animation:lpSpin .7s linear infinite;display:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>&nbsp;Signing in…';
    });
})();
</script>
@endsection
