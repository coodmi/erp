@extends('layouts.admin')

@php
    $profile = \App\Models\Utility::get_file('uploads/avatar');
    $vtype   = request('visa_type');
    $showAll = request('all') == '1' || (empty($vtype) && !request()->has('visa_type'));
    $results = [];
    $countries = [];
    $totPaid = $totDue = $totRefund = $totUnitPrice = 0;

    $visaLabels = [
        'WV' => 'Work Permit Visa',
        'SV' => 'Student Visa',
        'BV' => 'Business Visa',
        'TV' => 'Tourist Visa',
        'OV' => 'Others',
    ];
    $visaColors = [
        'WV' => 'blue',
        'SV' => 'purple',
        'BV' => 'cyan',
        'TV' => 'green',
        'OV' => 'amber',
    ];

    try {
        $connection = DB::connection();
        $baseSql = "
            SELECT agents.*, countries.country_name,
            COALESCE(SUM(clients.amount_paid), 0) AS total_amount_paid,
            COALESCE(SUM(clients.amount_due), 0) AS total_amount_due,
            COALESCE(SUM(clients.refund), 0) AS total_refund,
            COALESCE(AVG(clients.unit_price), 0) AS total_unit_price
            FROM agents
            LEFT JOIN countries ON agents.visa_country_id = countries.id
            LEFT JOIN clients ON clients.agent_id = agents.id
        ";
        if (!$showAll && !empty($vtype)) {
            $vtypeQ = $connection->getPdo()->quote($vtype);
            $results = $connection->select($baseSql . " WHERE agents.visa_type = $vtypeQ GROUP BY agents.id ORDER BY agents.agent_name ASC");
        } else {
            $results = $connection->select($baseSql . " GROUP BY agents.id ORDER BY agents.agent_name ASC");
        }
        $countries = $connection->select('SELECT * FROM countries ORDER BY country_name ASC');

        $sumPaidRaw = $sumDueRaw = $sumRefundRaw = 0;
        foreach ($results as $row) {
            $sumPaidRaw += (float) ($row->total_amount_paid ?? 0) - (float) ($row->total_refund ?? 0);
            $sumDueRaw += (float) ($row->total_amount_due ?? 0);
            $sumRefundRaw += (float) ($row->total_refund ?? 0);
        }
        $totPaid = number_format($sumPaidRaw, 2);
        $totDue = number_format($sumDueRaw, 2);
        $totRefund = number_format($sumRefundRaw, 2);
    } catch (\Exception $e) {
        // silently fail
    }

    $currentLabel = !$showAll && !empty($vtype) ? (isset($visaLabels[$vtype]) ? $visaLabels[$vtype] : __('Agents')) : __('All Agents');
    $canManage = true;
    $currentColor = isset($visaColors[$vtype]) ? $visaColors[$vtype] : 'blue';
@endphp

@section('page-title'){{ __('Agents') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ $currentLabel }}</li>
@endsection

@section('action-btn')
    @if($canManage ?? false)
    <div class="float-end">
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#createAgent">
            <i class="ti ti-plus"></i> {{ __('Add Agent') }}
        </button>
    </div>
    @endif
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/visa-manage.css') }}?v=1">
@endpush

@section('content')
<style>
:root {
    --agent-primary:#2563eb;
    --agent-dark:#0f172a;
    --agent-muted:#64748b;
    --agent-soft:#f8fafc;
    --agent-border:#e2e8f0;
}
.page-content { background:linear-gradient(180deg,#f8fafc 0,#eef4fb 100%); }
.ai-stat-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:18px; margin-bottom:24px; }
@media(max-width:1199px){ .ai-stat-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:575px){ .ai-stat-grid{ grid-template-columns:1fr; gap:12px; margin-bottom:16px; } }

.ai-stat {
    background:rgba(255,255,255,.92); border:1px solid rgba(226,232,240,.8); border-radius:20px;
    padding:20px;
    box-shadow:0 14px 35px rgba(15,23,42,.07);
    display:flex; align-items:center; gap:14px;
    min-height:96px; overflow:hidden; position:relative;
    transition:box-shadow .2s, transform .2s, border-color .2s;
}
.ai-stat:after {
    content:""; position:absolute; right:-28px; top:-28px; width:92px; height:92px;
    background:radial-gradient(circle,rgba(37,99,235,.12),rgba(37,99,235,0) 70%);
}
.ai-stat:hover { box-shadow:0 18px 42px rgba(15,23,42,.11); transform:translateY(-2px); border-color:#cbd5e1; }
.ai-stat-icon {
    width:54px; height:54px; border-radius:16px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.45rem; flex-shrink:0;
    box-shadow:0 10px 22px rgba(15,23,42,.14);
}
.ai-stat-icon.blue   { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; }
.ai-stat-icon.green  { background:linear-gradient(135deg,#34d399,#059669); color:#fff; }
.ai-stat-icon.red    { background:linear-gradient(135deg,#fb7185,#e11d48); color:#fff; }
.ai-stat-icon.amber  { background:linear-gradient(135deg,#fbbf24,#d97706); color:#fff; }
.ai-stat-icon.purple { background:linear-gradient(135deg,#a78bfa,#7c3aed); color:#fff; }
.ai-stat-icon.cyan   { background:linear-gradient(135deg,#38bdf8,#0284c7); color:#fff; }
.ai-stat-val  { font-size:1.55rem; font-weight:800; color:var(--agent-dark); line-height:1.05; margin-bottom:5px; word-break:break-word; }
.ai-stat-lbl  { font-size:.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; }

.visa-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:6px 12px; border-radius:999px;
    font-size:.73rem; font-weight:800; letter-spacing:.03em; white-space:nowrap;
}
.visa-badge.WV { background:#dbeafe; color:#1d4ed8; }
.visa-badge.SV { background:#ede9fe; color:#6d28d9; }
.visa-badge.BV { background:#e0f2fe; color:#0369a1; }
.visa-badge.TV { background:#d1fae5; color:#065f46; }
.visa-badge.OV { background:#fef3c7; color:#92400e; }

.agent-link { font-weight:600; color:#2563eb; text-decoration:none; }
.agent-link:hover { text-decoration:underline; }

.amt-paid   { color:#059669; font-weight:700; }
.amt-due    { color:#e11d48; font-weight:700; }
.amt-refund { color:#d97706; font-weight:700; }

.tbl-action { display:inline-flex; align-items:center; gap:6px; }
.tbl-action a {
    width:32px; height:32px; border-radius:8px;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:.9rem; transition:all .15s;
    text-decoration:none;
}
.tbl-action a.pdf  { background:#fee2e2; color:#e11d48; }
.tbl-action a.rcpt { background:#dbeafe; color:#2563eb; }
.tbl-action a:hover { filter:brightness(.9); transform:translateY(-1px); }

.ai-empty { text-align:center; padding:48px 20px; color:#94a3b8; }
.ai-empty i { font-size:2.5rem; opacity:.3; display:block; margin-bottom:12px; }
.ai-empty p { font-size:.85rem; margin:0; }

.agents-table-card {
    border:1px solid rgba(226,232,240,.9) !important; border-radius:22px !important;
    box-shadow:0 16px 42px rgba(15,23,42,.07) !important; overflow:hidden;
}
.agents-table-card .card-header {
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px;
    background:linear-gradient(135deg,#fff,#f8fafc) !important;
    border-bottom:1px solid var(--agent-border) !important; padding:18px 20px !important;
}
.agents-card-title { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.agents-record-count { font-size:.8rem; color:var(--agent-muted); font-weight:600; }
.agents-add-btn {
    display:inline-flex !important; align-items:center; justify-content:center; gap:7px;
    border-radius:12px !important; font-weight:700 !important; font-size:.84rem !important;
    padding:9px 16px !important; box-shadow:0 10px 20px rgba(37,99,235,.22);
}
.agents-code {
    background:#f1f5f9; padding:3px 8px; border-radius:8px; font-size:.78rem; color:#475569;
}
.agents-attachment-link { color:#e11d48; margin-right:6px; font-size:1rem; text-decoration:none; }
.agents-empty-mark { color:#cbd5e1; font-size:.78rem; }
.agents-table-card .table { border-collapse:separate; border-spacing:0; min-width:1080px; }
.agents-table-card .table thead th {
    background:#f8fafc; color:#475569; font-size:.72rem; font-weight:800;
    text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--agent-border);
    padding:14px 16px; white-space:nowrap;
}
.agents-table-card .table tbody td {
    padding:14px 16px; color:#334155; vertical-align:middle; border-color:#eef2f7;
}
.agents-table-card .table tbody tr:hover td { background:#f8fbff; }
.agents-table-card .dataTable-top,
.agents-table-card .dataTable-bottom {
    padding:16px 20px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
}
.agents-table-card .dataTable-selector,
.agents-table-card .dataTable-input {
    border:1px solid var(--agent-border); border-radius:10px; padding:8px 12px; outline:none;
}
.agents-table-card .dataTable-input:focus,
.agents-table-card .dataTable-selector:focus {
    border-color:var(--agent-primary); box-shadow:0 0 0 3px rgba(37,99,235,.1);
}
@media(max-width:575px){
    .ai-stat { padding:16px; min-height:84px; border-radius:16px; }
    .ai-stat-icon { width:48px; height:48px; border-radius:14px; font-size:1.25rem; }
    .ai-stat-val { font-size:1.35rem; }
    .agents-table-card { border-radius:18px !important; }
    .agents-table-card .card-header { align-items:stretch; padding:15px !important; }
    .agents-card-title, .agents-add-btn { width:100%; }
    .agents-add-btn { padding:10px 14px !important; }
    .agents-table-card .dataTable-top,
    .agents-table-card .dataTable-bottom { align-items:stretch; padding:14px 15px; }
    .agents-table-card .dataTable-search,
    .agents-table-card .dataTable-dropdown,
    .agents-table-card .dataTable-input { width:100%; }
}
.agent-modal .modal-dialog { max-width:720px; margin:1.25rem auto; }
.agent-modal .modal-content {
    border:0 !important; border-radius:26px !important; overflow:hidden;
    box-shadow:0 30px 90px rgba(15,23,42,.28) !important;
}
.agent-modal .modal-header {
    padding:24px 28px !important; border:0 !important;
    background:linear-gradient(135deg,#ffffff 0,#f8fbff 56%,#eef5ff 100%);
}
.agent-modal-title-wrap { display:flex; align-items:center; gap:14px; }
.agent-modal-icon {
    width:46px; height:46px; border-radius:16px; display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg,#3b82f6,#1d4ed8); color:#fff; font-size:1.25rem;
    box-shadow:0 14px 26px rgba(37,99,235,.28);
}
.agent-modal .modal-title { font-size:1.05rem !important; font-weight:800 !important; color:var(--agent-dark) !important; margin:0; }
.agent-modal-subtitle { color:var(--agent-muted); font-size:.82rem; font-weight:500; margin-top:3px; }
.agent-modal .btn-close {
    width:36px; height:36px; border-radius:12px; background-color:#eef2f7; opacity:1;
    background-size:.72rem;
}
.agent-modal .modal-body { padding:4px 28px 26px !important; background:#fff; }
.agent-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
.agent-form-group.full { grid-column:1 / -1; }
.agent-modal .form-label {
    font-size:.74rem !important; font-weight:800 !important; color:#334155 !important;
    text-transform:uppercase; letter-spacing:.07em; margin-bottom:8px !important;
}
.agent-modal .form-control,
.agent-modal .form-select {
    min-height:46px; border:1.5px solid #e2e8f0 !important; border-radius:14px !important;
    font-size:.88rem !important; padding:11px 14px !important;
    background:#f8fafc !important; color:#0f172a !important; transition:all .2s !important;
}
.agent-modal .form-control::placeholder { color:#94a3b8; }
.agent-modal .form-control:hover,
.agent-modal .form-select:hover { border-color:#cbd5e1 !important; background:#fff !important; }
.agent-modal .form-control:focus,
.agent-modal .form-select:focus {
    border-color:#2563eb !important; background:#fff !important;
    box-shadow:0 0 0 4px rgba(37,99,235,.11) !important;
}
.agent-modal .modal-footer {
    padding:18px 28px 26px !important; border:0 !important; background:#fff;
    display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap;
}
.agent-modal .btn { min-height:44px; border-radius:14px !important; font-weight:800 !important; padding:10px 18px !important; }
.agent-modal .btn-light { background:#f1f5f9 !important; border-color:#e2e8f0 !important; color:#475569 !important; }
.agent-modal .btn-primary { box-shadow:0 14px 24px rgba(37,99,235,.24); }
@media(max-width:767px){
    .agent-modal .modal-dialog { max-width:calc(100% - 24px); margin:.75rem auto; }
    .agent-modal .modal-header { padding:20px !important; }
    .agent-modal .modal-body { padding:0 20px 22px !important; }
    .agent-modal .modal-footer { padding:0 20px 20px !important; }
    .agent-form-grid { grid-template-columns:1fr; gap:14px; }
}
@media(max-width:420px){
    .agent-modal .modal-footer .btn { width:100%; }
    .agent-modal-title-wrap { align-items:flex-start; }
}
</style>

{{-- ── Stats Bar ── --}}
<div class="ai-stat-grid">
    <div class="ai-stat">
        <div class="ai-stat-icon {{ $currentColor }}"><i class="ti ti-users"></i></div>
        <div>
            <div class="ai-stat-val">{{ count($results) }}</div>
            <div class="ai-stat-lbl">Total Agents</div>
        </div>
    </div>
    <div class="ai-stat">
        <div class="ai-stat-icon green"><i class="ti ti-cash"></i></div>
        <div>
            <div class="ai-stat-val">{{ $totPaid ?: '0.00' }}</div>
            <div class="ai-stat-lbl">Total Collected</div>
        </div>
    </div>
    <div class="ai-stat">
        <div class="ai-stat-icon red"><i class="ti ti-alert-circle"></i></div>
        <div>
            <div class="ai-stat-val">{{ $totDue ?: '0.00' }}</div>
            <div class="ai-stat-lbl">Total Due</div>
        </div>
    </div>
    <div class="ai-stat">
        <div class="ai-stat-icon amber"><i class="ti ti-refresh"></i></div>
        <div>
            <div class="ai-stat-val">{{ $totRefund ?: '0.00' }}</div>
            <div class="ai-stat-lbl">Total Refund</div>
        </div>
    </div>
</div>

{{-- ── Main Table Card ── --}}
<div class="card agents-table-card">
    <div class="card-header">
        <div class="agents-card-title">
            <span class="visa-badge {{ $showAll ? 'OV' : $vtype }}">
                <i class="ti ti-id-badge"></i>
                {{ $currentLabel }}
            </span>
            <span class="agents-record-count">
                {{ count($results) }} {{ count($results) == 1 ? 'record' : 'records' }}
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Agent Name') }}</th>
                        <th>{{ __('Agent ID') }}</th>
                        <th>{{ __('Visa Type') }}</th>
                        <th>{{ __('Country') }}</th>
                        <th>{{ __('Unit Price') }}</th>
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
                                <a href="/agents?visa_type={{ $result->visa_type }}&agent={{ $result->id }}" class="agent-link" title="{{ __('Manage clients') }}">
                                    {{ $result->agent_name }}
                                </a>
                            </td>
                            <td>
                                <code class="agents-code">
                                    {{ $result->unique_code }}
                                </code>
                            </td>
                            <td>
                                <span class="visa-badge {{ $result->visa_type }}">
                                    {{ $visaLabels[$result->visa_type] ?? $result->visa_type }}
                                </span>
                            </td>
                            <td>{{ $result->country_name }}</td>
                            <td>{{ number_format((float) ($result->total_unit_price ?? 0), 2) }}</td>
                            <td><span class="amt-paid">{{ number_format((float) ($result->total_amount_paid ?? 0) - (float) ($result->total_refund ?? 0), 2) }}</span></td>
                            <td><span class="amt-due">{{ number_format((float) ($result->total_amount_due ?? 0), 2) }}</span></td>
                            <td><span class="amt-refund">{{ number_format((float) ($result->total_refund ?? 0), 2) }}</span></td>
                            <td>
                                @if(!empty($result->attachment) || !empty($result->attachment2) || !empty($result->attachment3))
                                    @if(!empty($result->attachment))
                                        <a href="{{ asset(Storage::url($result->attachment)) }}" download title="Attachment 1"
                                           class="agents-attachment-link"><i class="ti ti-file-type-pdf"></i></a>
                                    @endif
                                    @if(!empty($result->attachment2))
                                        <a href="{{ asset(Storage::url($result->attachment2)) }}" download title="Attachment 2"
                                           class="agents-attachment-link"><i class="ti ti-file-type-pdf"></i></a>
                                    @endif
                                    @if(!empty($result->attachment3))
                                        <a href="{{ asset(Storage::url($result->attachment3)) }}" download title="Attachment 3"
                                           class="agents-attachment-link"><i class="ti ti-file-type-pdf"></i></a>
                                    @endif
                                @else
                                    <span class="agents-empty-mark">—</span>
                                @endif
                            </td>
                            <td>
                                @include('partials.visa-crud-actions', [
                                    'editUrl' => route('agents.edit', $result->id),
                                    'deleteUrl' => route('agents.destroy', $result->id),
                                    'entityName' => __('agent'),
                                    'editTitle' => __('Edit Agent'),
                                    'extra' => '<a href="/agents?visa_type='.$result->visa_type.'&agent='.$result->id.'" class="visa-crud-btn clients" title="'.e(__('Clients')).'"><i class="ti ti-users"></i></a><a href="/agents?visa_type='.$result->visa_type.'&invoiceof='.$result->id.'" class="visa-crud-btn edit" title="'.e(__('Invoice')).'"><i class="ti ti-file-invoice"></i></a><a href="/agents?visa_type='.$result->visa_type.'&receiptof='.$result->id.'" class="visa-crud-btn edit" title="'.e(__('Receipt')).'"><i class="ti ti-receipt"></i></a>',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="ai-empty">
                                    <i class="ti ti-users-off"></i>
                                    <p>No agents found for <strong>{{ $currentLabel }}</strong></p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Add Agent Modal ── --}}
<div class="modal fade agent-modal" id="createAgent" tabindex="-1" aria-labelledby="createAgentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="{{ route('agents.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <div class="agent-modal-title-wrap">
                        <div class="agent-modal-icon"><i class="ti ti-user-plus"></i></div>
                        <div>
                            <h5 class="modal-title" id="createAgentLabel">Add New Agent</h5>
                            <div class="agent-modal-subtitle">Create a new agent profile with visa details</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="agent-form-grid">
                        <div class="agent-form-group">
                            <label class="form-label">Agent Name</label>
                            <input type="text" name="agent_name" class="form-control" placeholder="Enter agent name" required>
                        </div>
                        <div class="agent-form-group">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Enter address" required>
                        </div>
                        <div class="agent-form-group">
                            <label class="form-label">Visa Type</label>
                            <select name="visa_type" class="form-select" required>
                                <option value="">— Select Visa Type —</option>
                                <option value="WV" @selected($vtype === 'WV')>Work Permit Visa</option>
                                <option value="BV" @selected($vtype === 'BV')>Business Visa</option>
                                <option value="SV" @selected($vtype === 'SV')>Student Visa</option>
                                <option value="TV" @selected($vtype === 'TV')>Tourist Visa</option>
                                <option value="OV" @selected($vtype === 'OV')>Others</option>
                            </select>
                        </div>
                        <div class="agent-form-group">
                            <label class="form-label">Visa Country</label>
                            <select name="visa_country_id" class="form-select" required>
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
                        <i class="ti ti-check"></i> Add Agent
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script-page')
<script>
$(document).on('change','#password_switch',function(){
    if($(this).is(':checked')){
        $('.ps_div').removeClass('d-none');
        $('#password').attr('required',true);
    } else {
        $('.ps_div').addClass('d-none');
        $('#password').val(null).removeAttr('required');
    }
});
</script>
@endpush
