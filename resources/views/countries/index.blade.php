@extends('layouts.admin')

@php
    $countries = [];
    $totAgents = $totClients = $totVendors = 0;

    try {
        $connection = DB::connection();
        $countries = $connection->select("
            SELECT countries.*,
                COUNT(DISTINCT agents.id)  AS agent_count,
                COUNT(DISTINCT clients.id) AS client_count,
                COUNT(DISTINCT vendors.id) AS vendor_count
            FROM countries
            LEFT JOIN agents  ON agents.visa_country_id  = countries.id
            LEFT JOIN clients ON clients.visa_country_id = countries.id
            LEFT JOIN vendors ON vendors.visa_country_id = countries.id
            GROUP BY countries.id
            ORDER BY countries.country_name ASC
        ");
        foreach ($countries as $c) {
            $totAgents  += $c->agent_count;
            $totClients += $c->client_count;
            $totVendors += $c->vendor_count;
        }
    } catch (\Exception $e) {}
@endphp

@section('page-title'){{ __('Countries') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Countries') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#createCountry">
            <i class="ti ti-plus"></i> {{ __('Add Country') }}
        </button>
    </div>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/visa-manage.css') }}?v=2">
@endpush

@section('content')
<style>
:root { --co-primary:#0891b2; --co-dark:#0f172a; --co-muted:#64748b; --co-border:#e2e8f0; }

/* ── Stat Cards ── */
.co-stat-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:18px; margin-bottom:24px; }
@media(max-width:1199px){ .co-stat-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:575px){ .co-stat-grid{ grid-template-columns:1fr; gap:12px; margin-bottom:16px; } }
.co-stat { background:#fff; border:1px solid rgba(226,232,240,.8); border-radius:20px; padding:20px; box-shadow:0 14px 35px rgba(15,23,42,.06); display:flex; align-items:center; gap:14px; min-height:96px; transition:box-shadow .2s,transform .2s; }
.co-stat:hover { box-shadow:0 18px 42px rgba(15,23,42,.1); transform:translateY(-2px); }
.co-stat-icon { width:54px; height:54px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.45rem; flex-shrink:0; box-shadow:0 10px 22px rgba(15,23,42,.12); }
.co-stat-icon.teal   { background:linear-gradient(135deg,#38bdf8,#0284c7); color:#fff; }
.co-stat-icon.blue   { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; }
.co-stat-icon.purple { background:linear-gradient(135deg,#a78bfa,#7c3aed); color:#fff; }
.co-stat-icon.green  { background:linear-gradient(135deg,#34d399,#059669); color:#fff; }
.co-stat-val { font-size:1.55rem; font-weight:800; color:var(--co-dark); line-height:1.05; margin-bottom:4px; }
.co-stat-lbl { font-size:.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; }

/* ── Table Card ── */
.co-table-card { border:1px solid rgba(226,232,240,.9) !important; border-radius:22px !important; box-shadow:0 16px 42px rgba(15,23,42,.07) !important; overflow:hidden; }
.co-table-card .card-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; background:linear-gradient(135deg,#fff,#f8fafc) !important; border-bottom:1px solid var(--co-border) !important; padding:18px 20px !important; }
.co-card-title { display:flex; align-items:center; gap:10px; }
.co-record-count { font-size:.8rem; color:var(--co-muted); font-weight:600; }
.co-table-card .table { border-collapse:separate; border-spacing:0; min-width:700px; }
.co-table-card .table thead th { background:#f8fafc; color:#475569; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--co-border); padding:14px 18px; white-space:nowrap; }
.co-table-card .table tbody td { padding:14px 18px; color:#334155; vertical-align:middle; border-color:#eef2f7; }
.co-table-card .table tbody tr:hover td { background:#f0fbff; }

/* Country name pill */
.co-name { display:inline-flex; align-items:center; gap:8px; font-weight:700; color:var(--co-dark); font-size:.92rem; }
.co-flag { width:28px; height:20px; border-radius:4px; background:linear-gradient(135deg,#e0f2fe,#bae6fd); display:inline-flex; align-items:center; justify-content:center; font-size:.75rem; }

/* Count badges */
.co-count { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:999px; font-size:.78rem; font-weight:700; text-decoration:none; transition:all .18s; white-space:nowrap; }
.co-count.agents  { background:#dbeafe; color:#1d4ed8; }
.co-count.clients { background:#d1fae5; color:#065f46; }
.co-count.vendors { background:#ede9fe; color:#6d28d9; }
.co-count:hover { filter:brightness(.93); transform:translateY(-1px); }

/* Empty state */
.co-empty { text-align:center; padding:48px 20px; color:#94a3b8; }
.co-empty i { font-size:2.5rem; opacity:.3; display:block; margin-bottom:12px; }
.co-empty p { font-size:.85rem; margin:0; }

/* Modal */
.co-modal .modal-dialog { max-width:480px; margin:1.25rem auto; }
.co-modal .modal-content { border:0 !important; border-radius:24px !important; overflow:hidden; box-shadow:0 30px 90px rgba(15,23,42,.25) !important; }
.co-modal .modal-header { padding:22px 26px !important; border:0 !important; background:linear-gradient(135deg,#fff 0,#f0fbff 60%,#e0f7ff 100%); }
.co-modal-title-wrap { display:flex; align-items:center; gap:12px; }
.co-modal-icon { width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#38bdf8,#0284c7); color:#fff; font-size:1.2rem; box-shadow:0 12px 24px rgba(8,145,178,.25); }
.co-modal .modal-title { font-size:1rem !important; font-weight:800 !important; color:var(--co-dark) !important; margin:0; }
.co-modal-subtitle { color:var(--co-muted); font-size:.8rem; margin-top:2px; }
.co-modal .btn-close { width:34px; height:34px; border-radius:10px; background-color:#eef2f7; opacity:1; background-size:.7rem; }
.co-modal .modal-body { padding:6px 26px 24px !important; background:#fff; }
.co-modal .form-label { font-size:.74rem !important; font-weight:800 !important; color:#334155 !important; text-transform:uppercase; letter-spacing:.07em; margin-bottom:8px !important; }
.co-modal .form-control { min-height:46px; border:1.5px solid #e2e8f0 !important; border-radius:13px !important; font-size:.88rem !important; padding:11px 14px !important; background:#f8fafc !important; color:#0f172a !important; transition:all .2s !important; }
.co-modal .form-control:focus { border-color:#0891b2 !important; background:#fff !important; box-shadow:0 0 0 4px rgba(8,145,178,.1) !important; }
.co-modal .modal-footer { padding:16px 26px 24px !important; border:0 !important; background:#fff; display:flex; justify-content:flex-end; gap:10px; }
.co-modal .btn { min-height:44px; border-radius:13px !important; font-weight:800 !important; padding:10px 18px !important; }
.co-modal .btn-light { background:#f1f5f9 !important; border-color:#e2e8f0 !important; color:#475569 !important; }
.co-modal .btn-primary { background:linear-gradient(135deg,#38bdf8,#0284c7) !important; border:0 !important; box-shadow:0 12px 22px rgba(8,145,178,.22); }
</style>

{{-- ── Stat Cards ── --}}
<div class="co-stat-grid">
    <div class="co-stat">
        <div class="co-stat-icon teal"><i class="ti ti-world"></i></div>
        <div><div class="co-stat-val">{{ count($countries) }}</div><div class="co-stat-lbl">Total Countries</div></div>
    </div>
    <div class="co-stat">
        <div class="co-stat-icon blue"><i class="ti ti-user-check"></i></div>
        <div><div class="co-stat-val">{{ $totAgents }}</div><div class="co-stat-lbl">Total Agents</div></div>
    </div>
    <div class="co-stat">
        <div class="co-stat-icon green"><i class="ti ti-users"></i></div>
        <div><div class="co-stat-val">{{ $totClients }}</div><div class="co-stat-lbl">Total Clients</div></div>
    </div>
    <div class="co-stat">
        <div class="co-stat-icon purple"><i class="ti ti-building-community"></i></div>
        <div><div class="co-stat-val">{{ $totVendors }}</div><div class="co-stat-lbl">Total Vendors</div></div>
    </div>
</div>

{{-- ── Table Card ── --}}
<div class="card co-table-card">
    <div class="card-header">
        <div class="co-card-title">
            <span class="visa-badge BV"><i class="ti ti-world"></i> All Countries</span>
            <span class="co-record-count">{{ count($countries) }} {{ count($countries) == 1 ? 'record' : 'records' }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Country Name') }}</th>
                        <th>{{ __('Agents') }}</th>
                        <th>{{ __('Clients') }}</th>
                        <th>{{ __('Vendors') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($countries as $index => $result)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="co-name">
                                    <span class="co-flag"><i class="ti ti-map-pin" style="font-size:.7rem;color:#0284c7;"></i></span>
                                    {{ $result->country_name }}
                                </span>
                            </td>
                            <td>
                                <a href="/countries/agents?country={{ urlencode($result->country_name) }}" class="co-count agents">
                                    <i class="ti ti-user-check"></i>
                                    {{ $result->agent_count }} Agent{{ $result->agent_count != 1 ? 's' : '' }}
                                </a>
                            </td>
                            <td>
                                <a href="/countries/clients?country={{ urlencode($result->country_name) }}" class="co-count clients">
                                    <i class="ti ti-users"></i>
                                    {{ $result->client_count }} Client{{ $result->client_count != 1 ? 's' : '' }}
                                </a>
                            </td>
                            <td>
                                <a href="/countries/vendors?country={{ urlencode($result->country_name) }}" class="co-count vendors">
                                    <i class="ti ti-building-community"></i>
                                    {{ $result->vendor_count }} Vendor{{ $result->vendor_count != 1 ? 's' : '' }}
                                </a>
                            </td>
                            <td>
                                @include('partials.visa-crud-actions', [
                                    'editUrl'    => route('countries.edit', $result->id),
                                    'deleteUrl'  => route('countries.destroy', $result->id),
                                    'entityName' => __('country'),
                                    'editTitle'  => __('Edit Country'),
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="co-empty">
                                    <i class="ti ti-world-off"></i>
                                    <p>No countries added yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Add Country Modal ── --}}
<div class="modal fade co-modal" id="createCountry" tabindex="-1" aria-labelledby="createCountryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="{{ route('countries.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <div class="co-modal-title-wrap">
                        <div class="co-modal-icon"><i class="ti ti-world-plus"></i></div>
                        <div>
                            <h5 class="modal-title" id="createCountryLabel">Add New Country</h5>
                            <div class="co-modal-subtitle">Add a country to manage agents, clients & vendors</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Country Name <span class="text-danger">*</span></label>
                        <input type="text" name="country_name" class="form-control" placeholder="e.g. United Kingdom" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <i class="ti ti-check"></i> Add Country
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
