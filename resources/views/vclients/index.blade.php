@extends('layouts.admin')

@php
    $profile = \App\Models\Utility::get_file('uploads/avatar');

    $vtype   = request('visa_type');
    $showAll = request('all') == '1' || (empty($vtype) && !request()->has('visa_type'));
    $filterBy = request('filter', 'all'); // all | direct | agent | vendor
    $canManage = true;
    $results = [];
    $countries = [];
    $agents = [];
    $vendors = [];

    $visaLabels = [
        'WV' => 'Work Permit Visa',
        'SV' => 'Student Visa',
        'BV' => 'Business Visa',
        'TV' => 'Tourist Visa',
        'OV' => 'Others',
    ];

    try {
        $connection = DB::connection();

        // Base query — show ALL clients (no agent/vendor filter)
        $sql = "
            SELECT clients.*,
                   countries.country_name,
                   agents.agent_name,
                   vendors.vendor_name
            FROM clients
            LEFT JOIN countries ON clients.visa_country_id = countries.id
            LEFT JOIN agents    ON clients.agent_id  = agents.id
            LEFT JOIN vendors   ON clients.vendor_id = vendors.id
            WHERE clients.isTicket = 0
        ";

        // Visa type filter
        if (!$showAll && !empty($vtype)) {
            $vtypeQ = $connection->getPdo()->quote($vtype);
            $sql .= " AND clients.visa_type = $vtypeQ";
        }

        // Source filter
        if ($filterBy === 'direct') {
            $sql .= " AND clients.agent_id IS NULL AND clients.vendor_id IS NULL";
        } elseif ($filterBy === 'agent') {
            $sql .= " AND clients.agent_id IS NOT NULL";
        } elseif ($filterBy === 'vendor') {
            $sql .= " AND clients.vendor_id IS NOT NULL";
        }

        $sql .= " ORDER BY clients.client_name ASC";
        $results  = $connection->select($sql);
        $countries = $connection->select('SELECT * FROM countries ORDER BY country_name ASC');
        $agents    = $connection->select('SELECT id, agent_name FROM agents ORDER BY agent_name ASC');
        $vendors   = $connection->select('SELECT id, vendor_name FROM vendors ORDER BY vendor_name ASC');
    } catch (\Exception $e) {
        $results = [];
    }

    $currentLabel = !$showAll && !empty($vtype) ? ($visaLabels[$vtype] ?? __('Clients')) : __('All Clients');

    $totPaid = $totDue = $totRefund = 0;
    foreach ($results as $row) {
        $totPaid   += (float)($row->amount_paid ?? 0) - (float)($row->refund ?? 0);
        $totDue    += (float)($row->amount_due ?? 0);
        $totRefund += (float)($row->refund ?? 0);
    }
    $totPaid   = number_format($totPaid, 2);
    $totDue    = number_format($totDue, 2);
    $totRefund = number_format($totRefund, 2);

    // Count by source for filter badges
    $cntAll    = count($results);
@endphp

@section('page-title'){{ __('Clients') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ $currentLabel }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#createClient">
            <i class="ti ti-plus"></i> {{ __('Add Client') }}
        </button>
    </div>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/visa-manage.css') }}?v=1">
@endpush

@section('content')
<style>
:root { --client-primary:#0891b2; --client-dark:#0f172a; --client-muted:#64748b; --client-border:#e2e8f0; }
.ai-stat-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:18px; margin-bottom:24px; }
@media(max-width:1199px){ .ai-stat-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:575px){ .ai-stat-grid{ grid-template-columns:1fr; gap:12px; margin-bottom:16px; } }
.ai-stat { background:rgba(255,255,255,.92); border:1px solid rgba(226,232,240,.8); border-radius:20px; padding:20px; box-shadow:0 14px 35px rgba(15,23,42,.07); display:flex; align-items:center; gap:14px; min-height:96px; overflow:hidden; position:relative; transition:box-shadow .2s,transform .2s,border-color .2s; }
.ai-stat:hover { box-shadow:0 18px 42px rgba(15,23,42,.11); transform:translateY(-2px); border-color:#cbd5e1; }
.ai-stat-icon { width:54px; height:54px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.45rem; flex-shrink:0; box-shadow:0 10px 22px rgba(15,23,42,.14); }
.ai-stat-icon.cyan   { background:linear-gradient(135deg,#38bdf8,#0284c7); color:#fff; }
.ai-stat-icon.green  { background:linear-gradient(135deg,#34d399,#059669); color:#fff; }
.ai-stat-icon.red    { background:linear-gradient(135deg,#fb7185,#e11d48); color:#fff; }
.ai-stat-icon.amber  { background:linear-gradient(135deg,#fbbf24,#d97706); color:#fff; }
.ai-stat-val { font-size:1.55rem; font-weight:800; color:var(--client-dark); line-height:1.05; margin-bottom:5px; }
.ai-stat-lbl { font-size:.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; }
.visa-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 11px; border-radius:999px; font-size:.73rem; font-weight:800; letter-spacing:.03em; white-space:nowrap; }
.visa-badge.WV { background:#dbeafe; color:#1d4ed8; }
.visa-badge.SV { background:#ede9fe; color:#6d28d9; }
.visa-badge.BV { background:#e0f2fe; color:#0369a1; }
.visa-badge.TV { background:#d1fae5; color:#065f46; }
.visa-badge.OV { background:#fef3c7; color:#92400e; }
.status-badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:.72rem; font-weight:700; white-space:nowrap; }
.status-badge.submitted { background:#dbeafe; color:#1d4ed8; }
.status-badge.completed { background:#d1fae5; color:#065f46; }
.status-badge.cancelled { background:#fee2e2; color:#b91c1c; }
.status-badge.default   { background:#f1f5f9; color:#475569; }
.amt-paid   { color:#059669; font-weight:700; }
.amt-due    { color:#e11d48; font-weight:700; }
.amt-refund { color:#d97706; font-weight:700; }
.client-code { background:#f1f5f9; padding:3px 8px; border-radius:8px; font-size:.78rem; color:#475569; }
.client-attachment-link { color:#e11d48; margin-right:6px; font-size:1rem; text-decoration:none; }
.client-empty-mark { color:#cbd5e1; font-size:.78rem; }
.clients-table-card { border:1px solid rgba(226,232,240,.9) !important; border-radius:22px !important; box-shadow:0 16px 42px rgba(15,23,42,.07) !important; overflow:hidden; }
.clients-table-card .card-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; background:linear-gradient(135deg,#fff,#f8fafc) !important; border-bottom:1px solid var(--client-border) !important; padding:18px 20px !important; }
.clients-card-title { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.clients-record-count { font-size:.8rem; color:var(--client-muted); font-weight:600; }
.clients-filter-row { display:flex; gap:12px; flex-wrap:wrap; padding:16px 20px; border-bottom:1px solid var(--client-border); background:#fafbfc; align-items:center; }
.clients-filter-row .form-select { min-height:40px; border:1.5px solid #e2e8f0; border-radius:12px; font-size:.84rem; padding:8px 12px; background:#fff; max-width:200px; }
.clients-filter-row .form-select:focus { border-color:#0891b2; box-shadow:0 0 0 3px rgba(8,145,178,.1); outline:none; }

/* Source filter tabs */
.ai-source-tabs { display:flex; gap:6px; flex-wrap:wrap; }
.ai-source-tab {
    display:inline-flex; align-items:center; gap:5px;
    padding:7px 14px; border-radius:10px; font-size:.78rem; font-weight:700;
    text-decoration:none; border:1.5px solid #e2e8f0; color:#64748b;
    background:#fff; transition:all .18s; white-space:nowrap;
}
.ai-source-tab:hover { border-color:#2563eb; color:#2563eb; background:#eff6ff; }
.ai-source-tab.active { background:linear-gradient(135deg,#3b82f6,#2563eb); border-color:#2563eb; color:#fff; box-shadow:0 4px 12px rgba(37,99,235,.25); }
.ai-source-tab i { font-size:.85rem; }

/* Source badges in table */
.source-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:999px; font-size:.72rem; font-weight:700; white-space:nowrap;
}
.source-badge.agent  { background:#dbeafe; color:#1d4ed8; }
.source-badge.vendor { background:#ede9fe; color:#6d28d9; }
.source-badge.direct { background:#f1f5f9; color:#475569; }
.clients-table-card .table { border-collapse:separate; border-spacing:0; min-width:1100px; }
.clients-table-card .table thead th { background:#f8fafc; color:#475569; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--client-border); padding:14px 16px; white-space:nowrap; }
.clients-table-card .table tbody td { padding:14px 16px; color:#334155; vertical-align:middle; border-color:#eef2f7; }
.clients-table-card .table tbody tr:hover td { background:#f0fbff; }
.ai-empty { text-align:center; padding:48px 20px; color:#94a3b8; }
.ai-empty i { font-size:2.5rem; opacity:.3; display:block; margin-bottom:12px; }
.ai-empty p { font-size:.85rem; margin:0; }
/* Client modal */
.client-modal .modal-dialog { max-width:720px; margin:1.25rem auto; }
.client-modal .modal-content { border:0 !important; border-radius:26px !important; overflow:hidden; box-shadow:0 30px 90px rgba(15,23,42,.28) !important; }
.client-modal .modal-header { padding:24px 28px !important; border:0 !important; background:linear-gradient(135deg,#ffffff 0,#f0fbff 56%,#e0f7ff 100%); }
.client-modal-title-wrap { display:flex; align-items:center; gap:14px; }
.client-modal-icon { width:46px; height:46px; border-radius:16px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#38bdf8,#0284c7); color:#fff; font-size:1.25rem; box-shadow:0 14px 26px rgba(8,145,178,.28); }
.client-modal .modal-title { font-size:1.05rem !important; font-weight:800 !important; color:var(--client-dark) !important; margin:0; }
.client-modal-subtitle { color:var(--client-muted); font-size:.82rem; font-weight:500; margin-top:3px; }
.client-modal .btn-close { width:36px; height:36px; border-radius:12px; background-color:#eef2f7; opacity:1; background-size:.72rem; }
.client-modal .modal-body { padding:4px 28px 26px !important; background:#fff; }
.client-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
.client-form-group.full { grid-column:1 / -1; }
.client-modal .form-label { font-size:.74rem !important; font-weight:800 !important; color:#334155 !important; text-transform:uppercase; letter-spacing:.07em; margin-bottom:8px !important; }
.client-modal .form-control, .client-modal .form-select { min-height:46px; border:1.5px solid #e2e8f0 !important; border-radius:14px !important; font-size:.88rem !important; padding:11px 14px !important; background:#f8fafc !important; color:#0f172a !important; transition:all .2s !important; }
.client-modal .form-control::placeholder { color:#94a3b8; }
.client-modal .form-control:focus, .client-modal .form-select:focus { border-color:#0891b2 !important; background:#fff !important; box-shadow:0 0 0 4px rgba(8,145,178,.11) !important; }
.client-modal .modal-footer { padding:18px 28px 26px !important; border:0 !important; background:#fff; display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
.client-modal .btn { min-height:44px; border-radius:14px !important; font-weight:800 !important; padding:10px 18px !important; }
.client-modal .btn-light { background:#f1f5f9 !important; border-color:#e2e8f0 !important; color:#475569 !important; }
.client-modal .btn-primary { background:linear-gradient(135deg,#38bdf8,#0284c7) !important; border:0 !important; box-shadow:0 14px 24px rgba(8,145,178,.24); }
@media(max-width:767px){ .client-modal .modal-dialog { max-width:calc(100% - 24px); margin:.75rem auto; } .client-modal .modal-header { padding:20px !important; } .client-modal .modal-body { padding:0 20px 22px !important; } .client-modal .modal-footer { padding:0 20px 20px !important; } .client-form-grid { grid-template-columns:1fr; gap:14px; } }
</style>

{{-- Stats --}}
<div class="ai-stat-grid">
    <div class="ai-stat">
        <div class="ai-stat-icon cyan"><i class="ti ti-users"></i></div>
        <div><div class="ai-stat-val">{{ count($results) }}</div><div class="ai-stat-lbl">Total Clients</div></div>
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
<div class="card clients-table-card">
    <div class="card-header">
        <div class="clients-card-title">
            <span class="visa-badge {{ !$showAll && !empty($vtype) ? $vtype : 'BV' }}">
                <i class="ti ti-users"></i> {{ $currentLabel }}
            </span>
            <span class="clients-record-count">{{ count($results) }} {{ count($results) == 1 ? 'record' : 'records' }}</span>
        </div>
    </div>
    <div class="clients-filter-row">
        {{-- Source filter tabs --}}
        @php
            $baseUrl = request()->fullUrlWithQuery(['filter' => null]);
            $currentUrl = url()->current();
            $qParams = request()->except('filter');
        @endphp
        <div class="ai-source-tabs">
            <a href="{{ $currentUrl }}?{{ http_build_query(array_merge($qParams, ['filter'=>'all'])) }}"
               class="ai-source-tab {{ $filterBy === 'all' || !request()->has('filter') ? 'active' : '' }}">
                <i class="ti ti-users"></i> All
            </a>
            <a href="{{ $currentUrl }}?{{ http_build_query(array_merge($qParams, ['filter'=>'direct'])) }}"
               class="ai-source-tab {{ $filterBy === 'direct' ? 'active' : '' }}">
                <i class="ti ti-user"></i> Direct
            </a>
            <a href="{{ $currentUrl }}?{{ http_build_query(array_merge($qParams, ['filter'=>'agent'])) }}"
               class="ai-source-tab {{ $filterBy === 'agent' ? 'active' : '' }}">
                <i class="ti ti-user-check"></i> Via Agent
            </a>
            <a href="{{ $currentUrl }}?{{ http_build_query(array_merge($qParams, ['filter'=>'vendor'])) }}"
               class="ai-source-tab {{ $filterBy === 'vendor' ? 'active' : '' }}">
                <i class="ti ti-building-community"></i> Via Vendor
            </a>
        </div>
        {{-- Country & Status filters --}}
        <select id="country_id" class="form-select" onchange="setcountry()">
            <option value="">All Countries</option>
            <option value="all">All</option>
            @foreach($countries as $country)
                <option value="{{ $country->id }}">{{ $country->country_name }}</option>
            @endforeach
        </select>
        <select id="status" class="form-select" onchange="setstatus()">
            <option value="">All Statuses</option>
            <option value="all">All</option>
            <option value="submitted">Submitted</option>
            <option value="Work Permit Received">Work Permit Received</option>
            <option value="Applied For Visa">Applied For Visa</option>
            <option value="Visa Received">Visa Received</option>
            <option value="Completed">Completed</option>
            <option value="File Received">File Received</option>
            <option value="Cancelled">Cancelled</option>
        </select>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0" id="datainfo">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Client Name') }}</th>
                        <th>{{ __('Passport No') }}</th>
                        <th>{{ __('Client ID') }}</th>
                        <th>{{ __('Visa Type') }}</th>
                        <th>{{ __('Country') }}</th>
                        <th>{{ __('Unit Price') }}</th>
                        <th>{{ __('Paid') }}</th>
                        <th>{{ __('Due') }}</th>
                        <th>{{ __('Refund') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th>{{ __('Attachment') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $index => $result)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $result->client_name }}</strong></td>
                            <td><code class="client-code">{{ $result->passport_no }}</code></td>
                            <td><code class="client-code">{{ $result->unique_code }}</code></td>
                            <td>
                                <span class="visa-badge {{ $result->visa_type }}">
                                    {{ $visaLabels[$result->visa_type] ?? $result->visa_type }}
                                </span>
                            </td>
                            <td>{{ $result->country_name }}</td>
                            <td>{{ number_format((float)($result->unit_price ?? 0), 2) }}</td>
                            <td><span class="amt-paid">{{ number_format((float)($result->amount_paid ?? 0) - (float)($result->refund ?? 0), 2) }}</span></td>
                            <td><span class="amt-due">{{ number_format((float)($result->amount_due ?? 0), 2) }}</span></td>
                            <td><span class="amt-refund">{{ number_format((float)($result->refund ?? 0), 2) }}</span></td>
                            <td>
                                @php $sc = match(strtolower($result->status ?? '')) { 'submitted'=>'submitted','completed'=>'completed','cancelled'=>'cancelled',default=>'default' }; @endphp
                                <span class="status-badge {{ $sc }}">{{ $result->status ?: '—' }}</span>
                            </td>
                            <td>
                                @if(!empty($result->agent_name))
                                    <span class="source-badge agent" title="Via Agent">
                                        <i class="ti ti-user-check"></i> {{ $result->agent_name }}
                                    </span>
                                @elseif(!empty($result->vendor_name))
                                    <span class="source-badge vendor" title="Via Vendor">
                                        <i class="ti ti-building-community"></i> {{ $result->vendor_name }}
                                    </span>
                                @else
                                    <span class="source-badge direct" title="Direct Client">
                                        <i class="ti ti-user"></i> Direct
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($result->attachment) || !empty($result->attachment2) || !empty($result->attachmen3) || !empty($result->attachment4))
                                    @if(!empty($result->attachment))<a href="{{ asset(Storage::url($result->attachment)) }}" download title="Passport" class="client-attachment-link"><i class="fas fa-passport"></i></a>@endif
                                    @if(!empty($result->attachment2))<a href="{{ asset(Storage::url($result->attachment2)) }}" download title="Photo" class="client-attachment-link"><i class="fas fa-file-image"></i></a>@endif
                                    @if(!empty($result->attachmen3))<a href="{{ asset(Storage::url($result->attachmen3)) }}" download title="PCC" class="client-attachment-link"><i class="fas fa-file"></i></a>@endif
                                    @if(!empty($result->attachment4))<a href="{{ asset(Storage::url($result->attachment4)) }}" download title="Others" class="client-attachment-link"><i class="fas fa-file-pdf"></i></a>@endif
                                @else
                                    <span class="client-empty-mark">—</span>
                                @endif
                            </td>
                            <td>
                                @include('partials.visa-crud-actions', [
                                    'editUrl' => route('vclients.edit', $result->id),
                                    'deleteUrl' => route('vclients.destroy', $result->id),
                                    'entityName' => __('client'),
                                    'editTitle' => __('Edit Client'),
                                    'extra' => '<a target="_blank" href="/vclients?visa_type='.$result->visa_type.'&printclient_id='.$result->id.'" class="visa-crud-btn edit" title="'.e(__('Print')).'"><i class="ti ti-printer"></i></a>',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14">
                                <div class="ai-empty">
                                    <i class="ti ti-users-off"></i>
                                    <p>No clients found for <strong>{{ $currentLabel }}</strong></p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Client Modal --}}
<div class="modal fade client-modal" id="createClient" tabindex="-1" aria-labelledby="createClientLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="{{ route('vclients.store') }}" id="createClientForm">
            @csrf
            {{-- Preserve current page URL so redirect goes back to the same filtered view --}}
            <input type="hidden" name="_redirect_back" id="createClientRedirectBack" value="{{ request()->fullUrl() }}">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="client-modal-title-wrap">
                        <div class="client-modal-icon"><i class="ti ti-user-plus"></i></div>
                        <div>
                            <h5 class="modal-title" id="createClientLabel">Add New Client</h5>
                            <div class="client-modal-subtitle">Create a new individual client profile</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                            <i class="ti ti-circle-check me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                            <i class="ti ti-alert-circle me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <div class="client-form-grid">
                        <div class="client-form-group">
                            <label class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="client_name" class="form-control" placeholder="Enter full name" required>
                        </div>
                        <div class="client-form-group">
                            <label class="form-label">Passport Number</label>
                            <input type="text" name="passport_no" class="form-control" placeholder="Passport number">
                        </div>
                        <div class="client-form-group full">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Client address">
                        </div>
                        <div class="client-form-group">
                            <label class="form-label">Visa Type <span class="text-danger">*</span></label>
                            <select name="visa_type" class="form-select" id="createClientVisaType" required>
                                <option value="">— Select Visa Type —</option>
                                <option value="WV" @selected(!$showAll && $vtype === 'WV')>Work Permit Visa</option>
                                <option value="BV" @selected(!$showAll && $vtype === 'BV')>Business Visa</option>
                                <option value="SV" @selected(!$showAll && $vtype === 'SV')>Student Visa</option>
                                <option value="TV" @selected(!$showAll && $vtype === 'TV')>Tourist Visa</option>
                                <option value="OV" @selected(!$showAll && $vtype === 'OV')>Others</option>
                            </select>
                        </div>
                        <div class="client-form-group">
                            <label class="form-label">Visa Country</label>
                            <select name="visa_country_id" class="form-select">
                                <option value="">— Select Country —</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="agent_id" value="">
                        <input type="hidden" name="vendor_id" value="">
                        <input type="hidden" name="isTicket" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <i class="ti ti-check"></i> Add Client
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script-page')
<script>
function setcountry(){
    var country_id = $('#country_id').val();
    $.ajax({
        type:'GET', url:'vsetcountry/'+country_id,
        success: function(){ location.reload(); },
        error: function(){ console.log('error'); }
    });
}
function setstatus(){
    var status = $('#status').val();
    $.ajax({
        type:'GET', url:'vsetstatus/'+status,
        success: function(){ location.reload(); },
        error: function(){ console.log('error'); }
    });
}

// Keep _redirect_back always pointing to the current page URL
document.addEventListener('DOMContentLoaded', function () {
    var redirectField = document.getElementById('createClientRedirectBack');
    if (redirectField) {
        redirectField.value = window.location.href;
    }

    // When modal opens via sidebar "Add New Client" link, auto-select visa_type
    // The sidebar link passes data-visa-type attribute
    document.querySelectorAll('[data-open-modal="createClient"]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            var visaType = this.getAttribute('data-visa-type') || '';
            var select = document.getElementById('createClientVisaType');
            if (select && visaType) {
                select.value = visaType;
            }
            // Update redirect back to current URL
            if (redirectField) {
                redirectField.value = window.location.href;
            }
            var modal = new bootstrap.Modal(document.getElementById('createClient'));
            modal.show();
        });
    });

    // Also handle the header "Add Client" button
    var headerBtn = document.querySelector('[data-bs-target="#createClient"]');
    if (headerBtn) {
        headerBtn.addEventListener('click', function() {
            if (redirectField) {
                redirectField.value = window.location.href;
            }
        });
    }
});
</script>
@endpush
