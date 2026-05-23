@extends('layouts.admin')

@php
    $profile = \App\Models\Utility::get_file('uploads/avatar');

    $vtype = request('visa_type');
    $showAll = request('all') == '1' || (empty($vtype) && !request()->has('visa_type'));
    $canManage = true;
    $results = [];
    $countries = [];
    $totPaid = $totDue = $totRefund = 0;

    $visaLabels = [
        'WV' => 'Work Permit Visa',
        'SV' => 'Student Visa',
        'BV' => 'Business Visa',
        'TV' => 'Tourist Visa',
        'OV' => 'Others',
    ];

    try {
        $connection = DB::connection();
        $baseSql = "
            SELECT vendors.*, countries.country_name,
            COALESCE(SUM(clients.amount_paid), 0) AS total_amount_paid,
            COALESCE(SUM(clients.amount_due), 0) AS total_amount_due,
            COALESCE(SUM(clients.refund), 0) AS total_refund,
            COALESCE(AVG(clients.unit_price), 0) AS total_unit_price
            FROM vendors
            LEFT JOIN countries ON vendors.visa_country_id = countries.id
            LEFT JOIN clients ON clients.vendor_id = vendors.id
        ";
        if (!$showAll && !empty($vtype)) {
            $vtypeQ = $connection->getPdo()->quote($vtype);
            $results = $connection->select($baseSql . " WHERE vendors.visa_type = $vtypeQ GROUP BY vendors.id ORDER BY vendors.vendor_name ASC");
        } else {
            $results = $connection->select($baseSql . " GROUP BY vendors.id ORDER BY vendors.vendor_name ASC");
        }
        $countries = $connection->select('SELECT * FROM countries ORDER BY country_name ASC');

        foreach ($results as $row) {
            $totPaid   += (float)($row->total_amount_paid ?? 0) - (float)($row->total_refund ?? 0);
            $totDue    += (float)($row->total_amount_due ?? 0);
            $totRefund += (float)($row->total_refund ?? 0);
        }
        $totPaid   = number_format($totPaid, 2);
        $totDue    = number_format($totDue, 2);
        $totRefund = number_format($totRefund, 2);
    } catch (\Exception $e) {
        $results = [];
    }

    $currentLabel = !$showAll && !empty($vtype) ? ($visaLabels[$vtype] ?? __('Vendors')) : __('All Vendors');
@endphp

@section('page-title'){{ __('Vendors') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ $currentLabel }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#createVendor">
            <i class="ti ti-plus"></i> {{ __('Add Vendor') }}
        </button>
    </div>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/visa-manage.css') }}?v=1">
@endpush

@section('content')
<style>
:root { --vendor-primary:#7c3aed; --vendor-dark:#0f172a; --vendor-muted:#64748b; --vendor-border:#e2e8f0; }
.ai-stat-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:18px; margin-bottom:24px; }
@media(max-width:1199px){ .ai-stat-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:575px){ .ai-stat-grid{ grid-template-columns:1fr; gap:12px; margin-bottom:16px; } }
.ai-stat { background:rgba(255,255,255,.92); border:1px solid rgba(226,232,240,.8); border-radius:20px; padding:20px; box-shadow:0 14px 35px rgba(15,23,42,.07); display:flex; align-items:center; gap:14px; min-height:96px; overflow:hidden; position:relative; transition:box-shadow .2s,transform .2s,border-color .2s; }
.ai-stat:hover { box-shadow:0 18px 42px rgba(15,23,42,.11); transform:translateY(-2px); border-color:#cbd5e1; }
.ai-stat-icon { width:54px; height:54px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.45rem; flex-shrink:0; box-shadow:0 10px 22px rgba(15,23,42,.14); }
.ai-stat-icon.purple { background:linear-gradient(135deg,#a78bfa,#7c3aed); color:#fff; }
.ai-stat-icon.green  { background:linear-gradient(135deg,#34d399,#059669); color:#fff; }
.ai-stat-icon.red    { background:linear-gradient(135deg,#fb7185,#e11d48); color:#fff; }
.ai-stat-icon.amber  { background:linear-gradient(135deg,#fbbf24,#d97706); color:#fff; }
.ai-stat-val { font-size:1.55rem; font-weight:800; color:var(--vendor-dark); line-height:1.05; margin-bottom:5px; }
.ai-stat-lbl { font-size:.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; }
.visa-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 11px; border-radius:999px; font-size:.73rem; font-weight:800; letter-spacing:.03em; white-space:nowrap; }
.visa-badge.WV { background:#dbeafe; color:#1d4ed8; }
.visa-badge.SV { background:#ede9fe; color:#6d28d9; }
.visa-badge.BV { background:#e0f2fe; color:#0369a1; }
.visa-badge.TV { background:#d1fae5; color:#065f46; }
.visa-badge.OV { background:#fef3c7; color:#92400e; }
.vendor-link { font-weight:600; color:#7c3aed; text-decoration:none; }
.vendor-link:hover { text-decoration:underline; }
.amt-paid   { color:#059669; font-weight:700; }
.amt-due    { color:#e11d48; font-weight:700; }
.amt-refund { color:#d97706; font-weight:700; }
.vendors-code { background:#f1f5f9; padding:3px 8px; border-radius:8px; font-size:.78rem; color:#475569; }
.vendors-attachment-link { color:#e11d48; margin-right:6px; font-size:1rem; text-decoration:none; }
.vendors-empty-mark { color:#cbd5e1; font-size:.78rem; }
.vendors-table-card { border:1px solid rgba(226,232,240,.9) !important; border-radius:22px !important; box-shadow:0 16px 42px rgba(15,23,42,.07) !important; overflow:hidden; }
.vendors-table-card .card-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; background:linear-gradient(135deg,#fff,#f8fafc) !important; border-bottom:1px solid var(--vendor-border) !important; padding:18px 20px !important; }
.vendors-card-title { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.vendors-record-count { font-size:.8rem; color:var(--vendor-muted); font-weight:600; }
.vendors-table-card .table { border-collapse:separate; border-spacing:0; min-width:1000px; }
.vendors-table-card .table thead th { background:#f8fafc; color:#475569; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--vendor-border); padding:14px 16px; white-space:nowrap; }
.vendors-table-card .table tbody td { padding:14px 16px; color:#334155; vertical-align:middle; border-color:#eef2f7; }
.vendors-table-card .table tbody tr:hover td { background:#faf8ff; }
.ai-empty { text-align:center; padding:48px 20px; color:#94a3b8; }
.ai-empty i { font-size:2.5rem; opacity:.3; display:block; margin-bottom:12px; }
.ai-empty p { font-size:.85rem; margin:0; }
/* Vendor modal */
.vendor-modal .modal-dialog { max-width:680px; margin:1.25rem auto; }
.vendor-modal .modal-content { border:0 !important; border-radius:26px !important; overflow:hidden; box-shadow:0 30px 90px rgba(15,23,42,.28) !important; }
.vendor-modal .modal-header { padding:24px 28px !important; border:0 !important; background:linear-gradient(135deg,#ffffff 0,#faf8ff 56%,#f3eeff 100%); }
.vendor-modal-title-wrap { display:flex; align-items:center; gap:14px; }
.vendor-modal-icon { width:46px; height:46px; border-radius:16px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#a78bfa,#7c3aed); color:#fff; font-size:1.25rem; box-shadow:0 14px 26px rgba(124,58,237,.28); }
.vendor-modal .modal-title { font-size:1.05rem !important; font-weight:800 !important; color:var(--vendor-dark) !important; margin:0; }
.vendor-modal-subtitle { color:var(--vendor-muted); font-size:.82rem; font-weight:500; margin-top:3px; }
.vendor-modal .btn-close { width:36px; height:36px; border-radius:12px; background-color:#eef2f7; opacity:1; background-size:.72rem; }
.vendor-modal .modal-body { padding:4px 28px 26px !important; background:#fff; }
.vendor-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
.vendor-form-group.full { grid-column:1 / -1; }
.vendor-modal .form-label { font-size:.74rem !important; font-weight:800 !important; color:#334155 !important; text-transform:uppercase; letter-spacing:.07em; margin-bottom:8px !important; }
.vendor-modal .form-control, .vendor-modal .form-select { min-height:46px; border:1.5px solid #e2e8f0 !important; border-radius:14px !important; font-size:.88rem !important; padding:11px 14px !important; background:#f8fafc !important; color:#0f172a !important; transition:all .2s !important; }
.vendor-modal .form-control::placeholder { color:#94a3b8; }
.vendor-modal .form-control:focus, .vendor-modal .form-select:focus { border-color:#7c3aed !important; background:#fff !important; box-shadow:0 0 0 4px rgba(124,58,237,.11) !important; }
.vendor-modal .modal-footer { padding:18px 28px 26px !important; border:0 !important; background:#fff; display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
.vendor-modal .btn { min-height:44px; border-radius:14px !important; font-weight:800 !important; padding:10px 18px !important; }
.vendor-modal .btn-light { background:#f1f5f9 !important; border-color:#e2e8f0 !important; color:#475569 !important; }
.vendor-modal .btn-primary { background:linear-gradient(135deg,#a78bfa,#7c3aed) !important; border:0 !important; box-shadow:0 14px 24px rgba(124,58,237,.24); }
@media(max-width:767px){ .vendor-modal .modal-dialog { max-width:calc(100% - 24px); margin:.75rem auto; } .vendor-modal .modal-header { padding:20px !important; } .vendor-modal .modal-body { padding:0 20px 22px !important; } .vendor-modal .modal-footer { padding:0 20px 20px !important; } .vendor-form-grid { grid-template-columns:1fr; gap:14px; } }
</style>

{{-- Stats --}}
<div class="ai-stat-grid">
    <div class="ai-stat">
        <div class="ai-stat-icon purple"><i class="ti ti-building-community"></i></div>
        <div><div class="ai-stat-val">{{ count($results) }}</div><div class="ai-stat-lbl">Total Vendors</div></div>
    </div>
    <div class="ai-stat">
        <div class="ai-stat-icon green"><i class="ti ti-cash"></i></div>
        <div><div class="ai-stat-val">{{ $totPaid ?: '0.00' }}</div><div class="ai-stat-lbl">Total Collected</div></div>
    </div>
    <div class="ai-stat">
        <div class="ai-stat-icon red"><i class="ti ti-alert-circle"></i></div>
        <div><div class="ai-stat-val">{{ $totDue ?: '0.00' }}</div><div class="ai-stat-lbl">Total Due</div></div>
    </div>
    <div class="ai-stat">
        <div class="ai-stat-icon amber"><i class="ti ti-refresh"></i></div>
        <div><div class="ai-stat-val">{{ $totRefund ?: '0.00' }}</div><div class="ai-stat-lbl">Total Refund</div></div>
    </div>
</div>

{{-- Table Card --}}
<div class="card vendors-table-card">
    <div class="card-header">
        <div class="vendors-card-title">
            <span class="visa-badge {{ !$showAll && !empty($vtype) ? $vtype : 'OV' }}">
                <i class="ti ti-building-community"></i> {{ $currentLabel }}
            </span>
            <span class="vendors-record-count">{{ count($results) }} {{ count($results) == 1 ? 'record' : 'records' }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Vendor Name') }}</th>
                        <th>{{ __('Vendor ID') }}</th>
                        <th>{{ __('Company Details') }}</th>
                        <th>{{ __('Visa Type') }}</th>
                        <th>{{ __('Country') }}</th>
                        <th>{{ __('Paid') }}</th>
                        <th>{{ __('Due') }}</th>
                        <th>{{ __('Refund') }}</th>
                        <th>{{ __('Attachment') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $index => $result)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <a href="/vendors?visa_type={{ $result->visa_type }}&vendor={{ $result->id }}" class="vendor-link">
                                    {{ $result->vendor_name }}
                                </a>
                            </td>
                            <td><code class="vendors-code">{{ $result->unique_code }}</code></td>
                            <td>{{ Str::limit($result->company_details, 40) }}</td>
                            <td>
                                <span class="visa-badge {{ $result->visa_type }}">
                                    {{ $visaLabels[$result->visa_type] ?? $result->visa_type }}
                                </span>
                            </td>
                            <td>{{ $result->country_name }}</td>
                            <td><span class="amt-paid">{{ number_format((float)($result->total_amount_paid ?? 0) - (float)($result->total_refund ?? 0), 2) }}</span></td>
                            <td><span class="amt-due">{{ number_format((float)($result->total_amount_due ?? 0), 2) }}</span></td>
                            <td><span class="amt-refund">{{ number_format((float)($result->total_refund ?? 0), 2) }}</span></td>
                            <td>
                                @if(!empty($result->attachment) || !empty($result->attachment2) || !empty($result->attachmen3) || !empty($result->attachment4))
                                    @if(!empty($result->attachment))<a href="{{ asset(Storage::url($result->attachment)) }}" download title="Passport" class="vendors-attachment-link"><i class="fas fa-passport"></i></a>@endif
                                    @if(!empty($result->attachment2))<a href="{{ asset(Storage::url($result->attachment2)) }}" download title="Photo" class="vendors-attachment-link"><i class="fas fa-file-image"></i></a>@endif
                                    @if(!empty($result->attachmen3))<a href="{{ asset(Storage::url($result->attachmen3)) }}" download title="PCC" class="vendors-attachment-link"><i class="fas fa-file"></i></a>@endif
                                    @if(!empty($result->attachment4))<a href="{{ asset(Storage::url($result->attachment4)) }}" download title="Others" class="vendors-attachment-link"><i class="fas fa-file-pdf"></i></a>@endif
                                @else
                                    <span class="vendors-empty-mark">—</span>
                                @endif
                            </td>
                            <td>
                                @include('partials.visa-crud-actions', [
                                    'editUrl' => route('vendors.edit', $result->id),
                                    'deleteUrl' => route('vendors.destroy', $result->id),
                                    'entityName' => __('vendor'),
                                    'editTitle' => __('Edit Vendor'),
                                    'extra' => '<a href="/vendors?visa_type='.$result->visa_type.'&vendor='.$result->id.'" class="visa-crud-btn clients" title="'.e(__('Clients')).'"><i class="ti ti-users"></i></a>',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="ai-empty">
                                    <i class="ti ti-building-off"></i>
                                    <p>No vendors found for <strong>{{ $currentLabel }}</strong></p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Vendor Modal --}}
<div class="modal fade vendor-modal" id="createVendor" tabindex="-1" aria-labelledby="createVendorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="{{ route('vendors.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <div class="vendor-modal-title-wrap">
                        <div class="vendor-modal-icon"><i class="ti ti-building-plus"></i></div>
                        <div>
                            <h5 class="modal-title" id="createVendorLabel">Add New Vendor</h5>
                            <div class="vendor-modal-subtitle">Create a new vendor / service provider profile</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="vendor-form-grid">
                        <div class="vendor-form-group">
                            <label class="form-label">Vendor Name</label>
                            <input type="text" name="vendor_name" class="form-control" placeholder="Enter vendor name" required>
                        </div>
                        <div class="vendor-form-group">
                            <label class="form-label">Visa Type</label>
                            <select name="visa_type" class="form-select" required>
                                <option value="">— Select Visa Type —</option>
                                <option value="WV" @selected(!$showAll && $vtype === 'WV')>Work Permit Visa</option>
                                <option value="BV" @selected(!$showAll && $vtype === 'BV')>Business Visa</option>
                                <option value="SV" @selected(!$showAll && $vtype === 'SV')>Student Visa</option>
                                <option value="TV" @selected(!$showAll && $vtype === 'TV')>Tourist Visa</option>
                                <option value="OV" @selected(!$showAll && $vtype === 'OV')>Others</option>
                            </select>
                        </div>
                        <div class="vendor-form-group full">
                            <label class="form-label">Company Details</label>
                            <textarea name="company_details" class="form-control" rows="3" placeholder="Company details / description" required></textarea>
                        </div>
                        <div class="vendor-form-group full">
                            <label class="form-label">Visa Country</label>
                            <select name="visa_country_id" class="form-select">
                                <option value="">— Select Country —</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <i class="ti ti-check"></i> Add Vendor
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
