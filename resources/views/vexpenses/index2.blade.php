@extends('layouts.admin')

@php
    $vtype      = request('category');
    $results    = [];
    $categories = [];
    $totalAmount = 0;

    try {
        $connection = DB::connection();
        $vtypeQ = $connection->getPdo()->quote($vtype);

        $results = $connection->select("
            SELECT vexpense.*, vexcat.category_name
            FROM vexpense
            LEFT JOIN vexcat ON vexpense.vexcat_id = vexcat.id
            WHERE vexcat.category_name = $vtypeQ
            ORDER BY vexpense.expense_date DESC
        ");
        $categories = $connection->select("SELECT * FROM vexcat ORDER BY category_name ASC");

        foreach ($results as $r) { $totalAmount += (float)($r->expense_amount ?? 0); }
    } catch (\Exception $e) {}
@endphp

@section('page-title'){{ __('Expenses') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="/expenses">{{ __('Expense Categories') }}</a></li>
    <li class="breadcrumb-item active">{{ $vtype ?: __('All Expenses') }}</li>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/visa-manage.css') }}?v=2">
@endpush

@section('content')
<style>
:root { --ex-primary:#f59e0b; --ex-dark:#0f172a; --ex-muted:#64748b; --ex-border:#e2e8f0; }

/* Stat cards */
.ex2-stat-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:18px; margin-bottom:24px; }
@media(max-width:991px){ .ex2-stat-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:575px){ .ex2-stat-grid{ grid-template-columns:1fr; gap:12px; } }
.ex2-stat { background:#fff; border:1px solid rgba(226,232,240,.8); border-radius:20px; padding:20px; box-shadow:0 14px 35px rgba(15,23,42,.06); display:flex; align-items:center; gap:14px; min-height:96px; transition:box-shadow .2s,transform .2s; }
.ex2-stat:hover { box-shadow:0 18px 42px rgba(15,23,42,.1); transform:translateY(-2px); }
.ex2-stat-icon { width:54px; height:54px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.45rem; flex-shrink:0; box-shadow:0 10px 22px rgba(15,23,42,.12); }
.ex2-stat-icon.amber  { background:linear-gradient(135deg,#fbbf24,#d97706); color:#fff; }
.ex2-stat-icon.red    { background:linear-gradient(135deg,#fb7185,#e11d48); color:#fff; }
.ex2-stat-icon.green  { background:linear-gradient(135deg,#34d399,#059669); color:#fff; }
.ex2-stat-val { font-size:1.55rem; font-weight:800; color:var(--ex-dark); line-height:1.05; margin-bottom:4px; }
.ex2-stat-lbl { font-size:.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; }

/* Add form card */
.ex2-form-card { background:#fff; border:1px solid var(--ex-border); border-radius:20px; box-shadow:0 14px 35px rgba(15,23,42,.06); overflow:hidden; margin-bottom:24px; }
.ex2-form-header { padding:16px 22px; border-bottom:1px solid var(--ex-border); background:linear-gradient(135deg,#fff,#fffbeb); display:flex; align-items:center; gap:10px; }
.ex2-form-icon { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#fbbf24,#d97706); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1rem; }
.ex2-form-title { font-size:.92rem; font-weight:800; color:var(--ex-dark); margin:0; }
.ex2-form-body { padding:20px 22px; }
.ex2-form-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.ex2-form-grid .full { grid-column:1/-1; }
.ex2-form-grid .half { grid-column:span 1; }
@media(max-width:767px){ .ex2-form-grid{ grid-template-columns:1fr; } .ex2-form-grid .full,.ex2-form-grid .half{ grid-column:1/-1; } }
.ex2-label { font-size:.72rem; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:.07em; margin-bottom:7px; display:block; }
.ex2-input { width:100%; min-height:44px; border:1.5px solid #e2e8f0; border-radius:12px; font-size:.88rem; padding:10px 14px; background:#f8fafc; color:#0f172a; transition:all .2s; outline:none; }
.ex2-input:focus { border-color:#d97706; background:#fff; box-shadow:0 0 0 3px rgba(217,119,6,.1); }
.ex2-textarea { min-height:80px; resize:vertical; }
.ex2-submit { min-height:44px; border-radius:12px; font-weight:800; font-size:.88rem; border:0; cursor:pointer; padding:10px 22px; background:linear-gradient(135deg,#fbbf24,#d97706); color:#fff; box-shadow:0 10px 20px rgba(217,119,6,.22); display:inline-flex; align-items:center; gap:7px; transition:all .2s; }
.ex2-submit:hover { filter:brightness(1.05); transform:translateY(-1px); }

/* Table card */
.ex2-table-card { border:1px solid rgba(226,232,240,.9) !important; border-radius:22px !important; box-shadow:0 16px 42px rgba(15,23,42,.07) !important; overflow:hidden; }
.ex2-table-card .card-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; background:linear-gradient(135deg,#fff,#fffbeb) !important; border-bottom:1px solid var(--ex-border) !important; padding:18px 20px !important; }
.ex2-table-card .table { border-collapse:separate; border-spacing:0; min-width:800px; }
.ex2-table-card .table thead th { background:#fffbeb; color:#475569; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--ex-border); padding:14px 16px; white-space:nowrap; }
.ex2-table-card .table tbody td { padding:13px 16px; color:#334155; vertical-align:middle; border-color:#eef2f7; }
.ex2-table-card .table tbody tr:hover td { background:#fffbeb; }
.ex2-amount { font-weight:700; color:#e11d48; }
.ex2-date { font-size:.82rem; color:#64748b; }
.ex2-cat-badge { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:999px; font-size:.72rem; font-weight:700; background:#fef3c7; color:#92400e; }
.ex2-empty { text-align:center; padding:48px 20px; color:#94a3b8; }
.ex2-empty i { font-size:2.5rem; opacity:.3; display:block; margin-bottom:12px; }
</style>

{{-- Stats --}}
<div class="ex2-stat-grid">
    <div class="ex2-stat">
        <div class="ex2-stat-icon amber"><i class="ti ti-tag"></i></div>
        <div><div class="ex2-stat-val">{{ $vtype ?: 'All' }}</div><div class="ex2-stat-lbl">Category</div></div>
    </div>
    <div class="ex2-stat">
        <div class="ex2-stat-icon red"><i class="ti ti-receipt"></i></div>
        <div><div class="ex2-stat-val">{{ count($results) }}</div><div class="ex2-stat-lbl">Total Entries</div></div>
    </div>
    <div class="ex2-stat">
        <div class="ex2-stat-icon green"><i class="ti ti-cash"></i></div>
        <div><div class="ex2-stat-val">৳{{ number_format($totalAmount, 2) }}</div><div class="ex2-stat-lbl">Total Amount</div></div>
    </div>
</div>

{{-- Add Expense Form --}}
<div class="ex2-form-card">
    <div class="ex2-form-header">
        <div class="ex2-form-icon"><i class="ti ti-plus"></i></div>
        <span class="ex2-form-title">Add New Expense</span>
    </div>
    <div class="ex2-form-body">
        <form method="post" action="{{ route('vexpense.store') }}">
            @csrf
            <div class="ex2-form-grid">
                <div class="half">
                    <label class="ex2-label">Category <span class="text-danger">*</span></label>
                    <select name="vexcat_id" class="ex2-input" required>
                        <option value="">— Select Category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $cat->category_name == $vtype ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="half">
                    <label class="ex2-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="expense_name" class="ex2-input" placeholder="Expense title" required>
                </div>
                <div class="half">
                    <label class="ex2-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" name="expense_amount" class="ex2-input" placeholder="0.00" step="0.01" required>
                </div>
                <div class="half">
                    <label class="ex2-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="expense_date" class="ex2-input" required>
                </div>
                <div class="half">
                    <label class="ex2-label">Description</label>
                    <textarea name="expense_type" class="ex2-input ex2-textarea" placeholder="Description (optional)"></textarea>
                </div>
                <div class="half">
                    <label class="ex2-label">Remarks</label>
                    <textarea name="remarks" class="ex2-input ex2-textarea" placeholder="Remarks (optional)"></textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="ex2-submit">
                    <i class="ti ti-check"></i> Add Expense
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Expense Table --}}
<div class="card ex2-table-card">
    <div class="card-header">
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="ex2-cat-badge"><i class="ti ti-tag"></i> {{ $vtype ?: 'All' }}</span>
            <span style="font-size:.8rem;color:#94a3b8;font-weight:600;">{{ count($results) }} {{ count($results)==1?'record':'records' }}</span>
        </div>
        <span style="font-size:.88rem;font-weight:800;color:#e11d48;">Total: ৳{{ number_format($totalAmount,2) }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Remarks') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $index => $result)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $result->expense_name }}</strong></td>
                            <td><span class="ex2-cat-badge"><i class="ti ti-tag"></i> {{ $result->category_name }}</span></td>
                            <td style="max-width:200px;color:#64748b;font-size:.83rem;">{{ $result->expense_type ?: '—' }}</td>
                            <td><span class="ex2-date">{{ date('d M Y', strtotime($result->expense_date)) }}</span></td>
                            <td><span class="ex2-amount">৳{{ number_format($result->expense_amount, 2) }}</span></td>
                            <td style="color:#64748b;font-size:.83rem;">{{ $result->remarks ?: '—' }}</td>
                            <td>
                                @include('partials.visa-crud-actions', [
                                    'editUrl'    => null,
                                    'deleteUrl'  => route('vexpense.destroy', $result->id),
                                    'entityName' => __('expense'),
                                    'editTitle'  => null,
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="ex2-empty">
                                    <i class="ti ti-receipt-off"></i>
                                    <p>No expenses found for <strong>{{ $vtype }}</strong></p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
