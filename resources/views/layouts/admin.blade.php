@php
    $SITE_RTL = Utility::getValByName('SITE_RTL');
    $setting = \App\Models\Utility::settings();
    $color = 'theme-3';
    if (!empty($setting['color'])) {
        $color = $setting['color'];
    }

    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true')
    {
        $themeColor = 'custom-color';
    }
    else {
        $themeColor = $color;
    }

    $getseo= App\Models\Utility::getSeoSetting();
    $metatitle =  isset($getseo['meta_title']) ? $getseo['meta_title'] :'';
    $metsdesc= isset($getseo['meta_desc'])?$getseo['meta_desc']:'';
    $meta_image = \App\Models\Utility::get_file('uploads/meta/');
    $meta_logo = isset($getseo['meta_image'])?$getseo['meta_image']:'';
@endphp
<!DOCTYPE html>
<html lang="en" dir="{{$SITE_RTL == 'on'?'rtl':''}}">
<head>
    <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
    <title>{{(Utility::getValByName('title_text')) ? Utility::getValByName('title_text') : config('app.name', 'Alphainno ERP')}} - @yield('page-title')</title>

    <meta name="title" content="{{$metatitle}}">
    <meta name="description" content="{{$metsdesc}}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{$metatitle}}">
    <meta property="og:description" content="{{$metsdesc}}">
    <meta property="og:image" content="{{$meta_image.$meta_logo}}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title" content="{{$metatitle}}">
    <meta property="twitter:description" content="{{$metsdesc}}">
    <meta property="twitter:image" content="{{$meta_image.$meta_logo}}">


    <script src="{{ asset('js/html5shiv.js') }}"></script>

    <!-- ── Critical: prevent sidebar/page flash on load ── -->
    <style>
        html,body{background:#f1f5f9!important}
        .dash-sidebar{background:#0f1629!important;visibility:visible!important}
        .loader-bg{background:#0f1629!important;position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center}
    </style>

{{--    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>--}}

    <!-- Meta -->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="url" content="{{ url('').'/'.config('chatify.path') }}" data-user="{{ Auth::user()->id }}">
    <link rel="icon" href="{{ \App\Models\Utility::companyFaviconUrl() }}?v={{ \App\Models\Utility::companyBrandVersion() }}" type="image/png" sizes="16x16">

    <!-- Favicon icon -->
{{--    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon"/>--}}
    <!-- Calendar-->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">

    <!--bootstrap switch-->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">

    <!-- vendor css -->
    @if ($SITE_RTL == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" >
    @endif

    @if ($setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
    @endif

    @if ($SITE_RTL != 'on' && $setting['cust_darklayout'] != 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif


    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v=20260523" >

    @if ($setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('css/custom-dark.css') }}" >
    @endif

    <style>
        :root {
            --color-customColor: <?= $color ?>;    
        }
        
        .navbar-content.ps.ps--active-y {
            overflow-y: scroll !important;
        }
        .card-body.table-border-style {
            overflow-x: scroll;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">

    {{-- Alphainno Brand Override — critical inline (must override style.css) --}}
    <style>
    html { background: #f1f5f9; }

    /* Sidebar — always dark navy, never flashes white */
    .dash-sidebar,
    .dash-sidebar.light-sidebar,
    body .dash-sidebar,
    body.custom-color .dash-sidebar,
    body.theme-3 .dash-sidebar { background: #0f1629 !important; border-right: none !important; box-shadow: 4px 0 20px rgba(0,0,0,.2) !important; }

    .dash-sidebar .main-logo,
    body.custom-color .dash-sidebar .main-logo { background: rgba(255,255,255,.04) !important; border-bottom: 1px solid rgba(255,255,255,.08) !important; }
    .dash-sidebar .main-logo a.b-brand.ai-sidebar-brand .ai-sidebar-brand-img {
        max-height: 52px !important; width: auto !important; max-width: 100% !important;
        object-fit: contain !important; opacity: 1 !important; visibility: visible !important; display: block !important; margin: 0 auto !important;
    }

    .dash-sidebar .dash-navbar .dash-item > .dash-link,
    body.custom-color .dash-sidebar .dash-navbar .dash-item > .dash-link,
    body.custom-color .dash-sidebar.light-sidebar .dash-navbar > .dash-item > .dash-link { color: #94a3b8 !important; border-radius: 10px !important; margin: 1px 8px !important; font-size: .84rem !important; font-weight: 500 !important; background: transparent !important; }

    .dash-sidebar .dash-navbar .dash-item > .dash-link:hover,
    body.custom-color .dash-sidebar .dash-navbar .dash-item > .dash-link:hover,
    body.custom-color .dash-sidebar.light-sidebar .dash-navbar > .dash-item:hover > .dash-link,
    body.theme-3 .dash-sidebar .dash-navbar > .dash-item:hover > .dash-link,
    body.theme-3 .dash-sidebar .dash-navbar > .dash-item:focus > .dash-link { background: rgba(255,255,255,.07) !important; color: #e2e8f0 !important; box-shadow: none !important; outline: none !important; border: none !important; }

    .dash-sidebar .dash-navbar .dash-item.active > .dash-link,
    body.custom-color .dash-sidebar .dash-navbar .dash-item.active > .dash-link,
    body.custom-color .dash-sidebar.light-sidebar .dash-navbar > .dash-item.active > .dash-link,
    body.theme-3 .dash-sidebar .dash-navbar > .dash-item.active > .dash-link { background: linear-gradient(135deg,#2563eb,#1d4ed8) !important; color: #fff !important; box-shadow: 0 4px 12px rgba(37,99,235,.35) !important; }

    .dash-sidebar .dash-micon,
    body.custom-color .dash-sidebar .dash-micon { width:32px !important; height:32px !important; border-radius:6px !important; background:rgba(255,255,255,.08) !important; display:inline-flex !important; align-items:center !important; justify-content:center !important; margin-right:10px !important; flex-shrink:0 !important; }

    .dash-sidebar .dash-micon i,
    body.custom-color .dash-sidebar .dash-micon i { color: #94a3b8 !important; font-size: 1rem !important; }

    .dash-sidebar .dash-navbar .dash-item > .dash-link:hover .dash-micon { background: rgba(37,99,235,.25) !important; }
    .dash-sidebar .dash-navbar .dash-item > .dash-link:hover .dash-micon i { color: #93c5fd !important; }
    .dash-sidebar .dash-navbar .dash-item.active > .dash-link .dash-micon { background: rgba(255,255,255,.18) !important; }
    .dash-sidebar .dash-navbar .dash-item.active > .dash-link .dash-micon i { color: #fff !important; }

    /* Kill green dots */
    body:not(.minimenu) .dash-sidebar .dash-submenu .dash-item::before,
    body:not(.minimenu) .dash-sidebar .dash-submenu .dash-item.active::before,
    body:not(.minimenu) .dash-sidebar .dash-submenu .dash-item:hover::before { display: none !important; content: none !important; }

    /* Sidebar "Add New" quick-action item */
    .dash-sidebar .dash-submenu .dash-item.ai-sidebar-add-item > .dash-link.ai-sidebar-add-link {
        color: #38bdf8 !important;
        font-weight: 700 !important;
        font-size: .8rem !important;
        border: 1px dashed rgba(56,189,248,.35) !important;
        border-radius: 10px !important;
        margin: 4px 8px 6px !important;
        padding: 8px 12px !important;
        background: rgba(56,189,248,.06) !important;
        display: flex !important;
        align-items: center !important;
        gap: 7px !important;
        transition: all .18s !important;
    }
    .dash-sidebar .dash-submenu .dash-item.ai-sidebar-add-item > .dash-link.ai-sidebar-add-link:hover {
        background: rgba(56,189,248,.14) !important;
        border-color: rgba(56,189,248,.6) !important;
        color: #7dd3fc !important;
    }
    .dash-sidebar .dash-submenu .dash-item.ai-sidebar-add-item > .dash-link.ai-sidebar-add-link i {
        font-size: 1rem !important;
        color: inherit !important;
    }

    .dash-sidebar .dash-submenu,
    body.custom-color .dash-sidebar .dash-submenu { background: rgba(0,0,0,.2) !important; border-radius: 8px !important; }
    .dash-sidebar .dash-submenu .dash-item > .dash-link,
    body.custom-color .dash-sidebar .dash-submenu .dash-item > .dash-link { color: #64748b !important; }
    .dash-sidebar .dash-submenu .dash-item > .dash-link:hover { color: #e2e8f0 !important; background: rgba(255,255,255,.06) !important; }
    .dash-sidebar .dash-submenu .dash-item.active > .dash-link { color: #93c5fd !important; background: rgba(37,99,235,.2) !important; }

    .dash-sidebar .dash-mtext { color: inherit !important; }
    .dash-sidebar .dash-arrow svg { color: #475569 !important; }
    .dash-sidebar .dash-item.active .dash-arrow svg { color: rgba(255,255,255,.6) !important; }

    /* Loader bar — blue */
    .loader-bg { background: #0f1629 !important; }
    .loader-fill { background: linear-gradient(135deg,#2563eb,#1d4ed8) !important; }

    /* Header layout — beat theme absolute positioning */
    .dash-header, .dash-header.ai-topbar, .dash-header:not(.transprent-bg) {
        position: sticky !important; top: 0 !important; left: 0 !important; right: 0 !important;
        margin-left: 260px !important; width: auto !important; border-radius: 0 !important;
        min-height: 64px !important; height: 64px !important;
    }
    .ai-header-right { margin-left: auto !important; }
    .ai-sidebar-brand-fallback { display: flex !important; }
    .dash-sidebar .main-logo img.ai-sidebar-brand-img {
        display: block !important;
    }
    .dash-header .ai-header-brand,
    .dash-header .ai-header-brand-img {
        display: none !important;
    }
    .dash-sidebar .navbar-content .card { display: none !important; }

    /* Theme uses position:absolute + top:70px on container — doubles gap with sticky header */
    .dash-container,
    .dash-header ~ .dash-container,
    body .dash-container {
        top: 0 !important;
        margin-top: 0 !important;
    }
    .dash-header ~ .dash-container .dash-content,
    .dash-container .dash-content {
        padding-top: 12px !important;
    }
    @media (min-width: 1024px) {
        .dash-header:not(.transprent-bg):not(.dash-mob-header) ~ .dash-container .dash-content {
            padding-top: 12px !important;
        }
    }
    body.settings-page .page-header { display: none !important; }
    body.settings-page .dash-container .dash-content { padding-top: 0 !important; }
    </style>

    @stack('css-page')
</head>
<body class="{{ $themeColor }} @yield('body-class')">
<!-- [ Pre-loader ] start -->
<div class="loader-bg">
    <div class="loader-track">
        <div class="loader-fill"></div>
    </div>
</div>
@include('partials.admin.menu')
<!-- [ navigation menu ] end -->
<!-- [ Header ] start -->
@include('partials.admin.header')

<!-- Modal -->
<div class="modal notification-modal fade"
     id="notification-modal"
     tabindex="-1"
     role="dialog"
     aria-hidden="true"
>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <button
                    type="button"
                    class="btn-close float-end"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
                <h6 class="mt-2">
                    <i data-feather="monitor" class="me-2"></i>Desktop settings
                </h6>
                <hr/>
                <div class="form-check form-switch">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        id="pcsetting1"
                        checked
                    />
                    <label class="form-check-label f-w-600 pl-1" for="pcsetting1"
                    >Allow desktop notification</label
                    >
                </div>
                <p class="text-muted ms-5">
                    you get lettest content at a time when data will updated
                </p>
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="pcsetting2"/>
                    <label class="form-check-label f-w-600 pl-1" for="pcsetting2"
                    >Store Cookie</label
                    >
                </div>
                <h6 class="mb-0 mt-5">
                    <i data-feather="save" class="me-2"></i>Application settings
                </h6>
                <hr/>
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="pcsetting3"/>
                    <label class="form-check-label f-w-600 pl-1" for="pcsetting3"
                    >Backup Storage</label
                    >
                </div>
                <p class="text-muted mb-4 ms-5">
                    Automaticaly take backup as par schedule
                </p>
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="pcsetting4"/>
                    <label class="form-check-label f-w-600 pl-1" for="pcsetting4"
                    >Allow guest to print file</label
                    >
                </div>
                <h6 class="mb-0 mt-5">
                    <i data-feather="cpu" class="me-2"></i>System settings
                </h6>
                <hr/>
                <div class="form-check form-switch">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        id="pcsetting5"
                        checked
                    />
                    <label class="form-check-label f-w-600 pl-1" for="pcsetting5"
                    >View other user chat</label
                    >
                </div>
                <p class="text-muted ms-5">Allow to show public user message</p>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-light-danger btn-sm"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>
                <button type="button" class="btn btn-light-primary btn-sm">
                    Save changes
                </button>
            </div>
        </div>
    </div>
</div>
<!-- [ Header ] end -->

<!-- [ Main Content ] start -->
<div class="dash-container">
    <div class="dash-content">
        @hasSection('hide-page-header')
        @else
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="page-header-title">
                            <h4 class="m-b-10">@yield('page-title')</h4>
                        </div>
                        <ul class="breadcrumb">
                            @yield('breadcrumb')
                        </ul>
                    </div>
                    <div class="col">
                        @yield('action-btn')
                    </div>
                </div>
            </div>
        </div>
        @endif
    @yield('content')
    <!-- [ Main Content ] end -->
    </div>
</div>
<div class="modal fade" id="commonModal" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body body">
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="commonModalOver" tabindex="-1" role="dialog" aria-labelledby="commonModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commonModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>


<div class="position-fixed top-0 end-0 p-3" style="z-index: 99999; display:none;">
    <div id="liveToast" class="toast text-white  fade" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"> </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>






@include('partials.admin.footer')
@if(request()->is('chats*') || request()->is('*chatify*'))
    @include('Chatify::layouts.footerLinks')
@endif

{{-- Sidebar "Add New" quick-action: navigate to the list page and auto-open the modal --}}
<script>
(function(){
    document.querySelectorAll('.ai-sidebar-add-link[data-open-modal]').forEach(function(link){
        link.addEventListener('click', function(e){
            e.preventDefault();
            var modalId = this.getAttribute('data-open-modal');
            var href    = this.getAttribute('href');
            // If we're already on the target page, just open the modal
            var currentPath = window.location.pathname + window.location.search;
            var targetPath  = href;
            if (currentPath === targetPath || window.location.href.indexOf(href.replace('?','')) !== -1) {
                var el = document.getElementById(modalId);
                if (el && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(el).show();
                }
            } else {
                // Navigate to the page with a hash so the page can auto-open the modal
                window.location.href = href + (href.indexOf('?') !== -1 ? '&' : '?') + 'openModal=' + modalId;
            }
        });
    });

    // Auto-open modal if openModal param is present in URL
    var params = new URLSearchParams(window.location.search);
    var openModal = params.get('openModal');
    if (openModal) {
        var el = document.getElementById(openModal);
        if (el) {
            // Wait for page to fully load
            window.addEventListener('load', function(){
                if (typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(el).show();
                }
            });
        }
    }
})();
</script>
</body>
</html>
