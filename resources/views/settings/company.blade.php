@extends('layouts.admin')

@section('body-class', 'settings-page')
@section('hide-page-header')
@endsection

@php
    $logoPreview = \App\Models\Utility::companyLogoUrl();
    $faviconPreview = \App\Models\Utility::companyFaviconUrl();
    $defaultBrandImage = asset('Alphainno.png');
    $brandVersion = \App\Models\Utility::companyBrandVersion();
@endphp

@push('css-page')
    <style>
        .settings-wrap {
            margin-top: 0 !important;
        }

        .settings-wrap .settings-breadcrumb {
            margin-bottom: 12px;
        }

        .settings-wrap .card {
            margin-bottom: 0 !important;
        }

        /* Blue accent bar instead of theme green */
        .settings-wrap .card-header h5::after,
        .settings-wrap .card .card-header h5::after,
        body.theme-3 .settings-wrap .card .card-header h5::after,
        body.custom-color .settings-wrap .card .card-header h5::after {
            background: #2563eb !important;
        }

        .settings-wrap .card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.06);
        }

        .settings-wrap .card-header {
            padding: 14px 20px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }

        .settings-wrap .card-body {
            padding: 20px;
        }

        .settings-wrap .settings-section-title {
            color: #2563eb;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .settings-wrap .upload-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            height: 100%;
        }

        .settings-wrap .upload-card .card-header {
            padding: 10px 14px;
            background: #f8fafc;
        }

        .settings-wrap .upload-card .card-body {
            padding: 16px;
            text-align: center;
        }

        .settings-wrap .upload-card img {
            max-height: 90px;
            width: auto;
            object-fit: contain;
            margin-bottom: 12px;
        }

        .settings-wrap .upload-card .favicon-preview {
            max-height: 48px;
        }

        .settings-wrap .btn-primary,
        .settings-wrap .btn-upload {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
        }

        .settings-wrap .btn-primary:hover,
        .settings-wrap .btn-upload:hover {
            background-color: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }

        .settings-wrap .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        }

        @media (max-width: 767.98px) {
            .settings-wrap .upload-card {
                margin-bottom: 12px;
            }
        }
    </style>
@endpush

@push('script-page')
    <script>
        (function () {
            const defaultImg = @json($defaultBrandImage);

            function bindPreview(inputId, previewId) {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);
                if (!input || !preview) return;

                preview.addEventListener('error', function () {
                    if (this.dataset.fallbackApplied) {
                        return;
                    }
                    this.dataset.fallbackApplied = '1';
                    this.onerror = null;
                    this.src = defaultImg;
                });

                input.addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        preview.src = URL.createObjectURL(this.files[0]);
                    }
                });
            }

            bindPreview('company_logo', 'logo-preview');
            bindPreview('company_favicon', 'favicon-preview');
        })();
    </script>
@endpush

@section('content')
    <div class="settings-wrap">
        <ul class="breadcrumb settings-breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item">{{ __('Settings') }}</li>
        </ul>
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h5 class="mb-0">{{ __('Website Settings') }}</h5>
                    <small class="text-muted">{{ __('Visa consultancy — branding and office profile') }}</small>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('business.setting') }}" enctype="multipart/form-data">
                    @csrf

                    <h6 class="settings-section-title">{{ __('Branding') }}</h6>
                    <div class="row mb-3">
                        <div class="col-lg-6 col-md-6 mb-3 mb-lg-0">
                            <div class="card upload-card">
                                <div class="card-header py-2">
                                    <h6 class="mb-0">{{ __('Website Logo') }}</h6>
                                </div>
                                <div class="card-body">
                                    <img id="logo-preview"
                                        src="{{ $logoPreview }}?v={{ $brandVersion }}"
                                        alt="{{ __('Website Logo') }}"
                                        onerror="this.onerror=null;this.src='{{ $defaultBrandImage }}';">
                                    <label for="company_logo" class="btn btn-sm btn-primary btn-upload">
                                        <i class="ti ti-upload"></i> {{ __('Choose file') }}
                                    </label>
                                    <input type="file" name="company_logo" id="company_logo" class="d-none"
                                        accept="image/png,image/jpeg,image/jpg">
                                    <small class="d-block text-muted mt-2">{{ __('PNG or JPG, max 20MB') }}</small>
                                    @error('company_logo')
                                        <span class="text-danger d-block mt-1"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="card upload-card">
                                <div class="card-header py-2">
                                    <h6 class="mb-0">{{ __('Favicon') }}</h6>
                                </div>
                                <div class="card-body">
                                    <img id="favicon-preview" class="favicon-preview"
                                        src="{{ $faviconPreview }}?v={{ $brandVersion }}"
                                        alt="{{ __('Favicon') }}"
                                        onerror="this.onerror=null;this.src='{{ $defaultBrandImage }}';">
                                    <label for="company_favicon" class="btn btn-sm btn-primary btn-upload">
                                        <i class="ti ti-upload"></i> {{ __('Choose file') }}
                                    </label>
                                    <input type="file" name="company_favicon" id="company_favicon" class="d-none"
                                        accept="image/png,image/x-icon,image/jpeg,.ico">
                                    <small class="d-block text-muted mt-2">{{ __('PNG recommended, max 20MB') }}</small>
                                    @error('company_favicon')
                                        <span class="text-danger d-block mt-1"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <h6 class="settings-section-title">{{ __('Website Information') }}</h6>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="title_text" class="form-label">{{ __('Website Name') }}</label>
                            <input type="text" name="title_text" id="title_text" class="form-control"
                                value="{{ old('title_text', $settings['title_text'] ?? '') }}"
                                placeholder="{{ __('Enter website name') }}">
                            <small class="text-muted">{{ __('Shown in the browser tab, sidebar, and header') }}</small>
                            @error('title_text')
                                <span class="text-danger d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="footer_text" class="form-label">{{ __('Website Description') }}</label>
                            <textarea name="footer_text" id="footer_text" class="form-control" rows="3"
                                placeholder="{{ __('Enter a short description of your website or company') }}">{{ old('footer_text', $settings['footer_text'] ?? '') }}</textarea>
                            @error('footer_text')
                                <span class="text-danger d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mail_from_address" class="form-label">{{ __('Support Email') }}</label>
                            <input type="email" name="mail_from_address" id="mail_from_address" class="form-control"
                                value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}"
                                placeholder="{{ __('support@example.com') }}">
                            @error('mail_from_address')
                                <span class="text-danger d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_telephone" class="form-label">{{ __('Support Phone') }}</label>
                            <input type="text" name="company_telephone" id="company_telephone" class="form-control"
                                value="{{ old('company_telephone', $settings['company_telephone'] ?? '') }}"
                                placeholder="{{ __('+1 (555) 000-0000') }}">
                            @error('company_telephone')
                                <span class="text-danger d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy"></i> {{ __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
