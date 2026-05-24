@extends('layouts.admin')

@php
    $vexcat = [];
    $totalExpenses = 0;
    $totalAmount = 0;

    try {
        $connection = DB::connection();
        $vexcat = $connection->select("
            SELECT vexcat.*,
                COUNT(vexpense.id) AS expense_count,
                COALESCE(SUM(vexpense.expense_amount), 0) AS total_amount
            FROM vexcat
            LEFT JOIN vexpense ON vexpense.vexcat_id = vexcat.id
            GROUP BY vexcat.id
            ORDER BY vexcat.category_name ASC
        ");
        foreach ($vexcat as $cat) {
            $totalExpenses += $cat->expense_count;
            $totalAmount   += $cat->total_amount;
        }
    } catch (\Exception $e) {}
@endphp

@section('page-title'){{ __('Expenses') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Expense Categories') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#createCategory">
            <i class="ti ti-plus"></i> {{ __('Add Category') }}
        </button>
    </div>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/visa-manage.css') }}?v=2">
@endpush

@section('content')
<style>
:root { --ex-primary:#f59e0b; --ex-dark:#0f172a; --ex-muted:#64748b; --ex-border:#e2e8f0; }

.ex-stat-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:18px; margin-bottom:24px; }
@media(max-width:991px){ .ex-stat-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:575px){ .ex-stat-grid{ grid-template-columns:1fr; gap:12px; margin-bottom:16px; } }

.ex-stat { background:#fff; border:1px solid rgba(226,232,240,.8); border-radius:20px; padding:20px; box-shadow:0 14px 35px rgba(15,23,42,.06); display:flex; align-items:center; gap:14px; min-height:96px; transition:box-shadow .2s,transform .2s; }
.ex-stat:hover { box-shadow:0 18px 42px rgba(15,23,42,.1); transform:translateY(-2px); }
.ex-stat-icon { width:54px; height:54px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.45rem; flex-shrink:0; box-shadow:0 10px 22px rgba(15,23,42,.12); }
.ex-stat-icon.amber  { background:linear-gradient(135deg,#fbbf24,#d97706); color:#fff; }
.ex-stat-icon.red    { background:linear-gradient(135deg,#fb7185,#e11d48); color:#fff; }
.ex-stat-icon.green  { background:linear-gradient(135deg,#34d399,#059669); color:#fff; }
.ex-stat-val { font-size:1.55rem; font-weight:800; color:var(--ex-dark); line-height:1.05; margin-bottom:4px; }
.ex-stat-lbl { font-size:.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; }

.ex-table-card { border:1px solid rgba(226,232,240,.9) !important; border-radius:22px !important; box-shadow:0 16px 42px rgba(15,23,42,.07) !important; overflow:hidden; }
.ex-table-card .card-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; background:linear-gradient(135deg,#fff,#fffbeb) !important; border-bottom:1px solid var(--ex-border) !important; padding:18px 20px !important; }
.ex-card-title { display:flex; align-items:center; gap:10px; }
.ex-record-count { font-size:.8rem; color:var(--ex-muted); font-weight:600; }

.ex-table-card .table { border-collapse:separate; border-spacing:0; min-width:600px; }
.ex-table-card .table thead th { background:#fffbeb; color:#475569; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--ex-border); padding:14px 18px; white-space:nowrap; }
.ex-table-card .table tbody td { padding:14px 18px; color:#334155; vertical-align:middle; border-color:#eef2f7; }
.ex-table-card .table tbody tr:hover td { background:#fffbeb; }

.ex-cat-name { display:inline-flex; align-items:center; gap:8px; font-weight:700; color:var(--ex-dark); font-size:.92rem; text-decoration:none; }
.ex-cat-name:hover { color:#d97706; }
.ex-cat-icon { width:32px; height:32px; border-radius:10px; background:linear-gradient(135deg,#fef3c7,#fde68a); display:inline-flex; align-items:center; justify-content:center; font-size:.85rem; color:#d97706; flex-shrink:0; }

.ex-count-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:999px; font-size:.78rem; font-weight:700; background:#fef3c7; color:#92400e; white-space:nowrap; }
.ex-amount { font-weight:700; color:#059669; font-size:.92rem; }

.ex-desc { color:#64748b; font-size:.83rem; max-width:280px; }

.ex-empty { text-align:center; padding:48px 20px; color:#94a3b8; }
.ex-empty i { font-size:2.5rem; opacity:.3; display:block; margin-bottom:12px; }
.ex-empty p { font-size:.85rem; margin:0; }

/* Modal */
.ex-modal .modal-dialog { max-width:520px; margin:1.25rem auto; }
.ex-modal .modal-content { border:0 !important; border-radius:24px !important; overflow:hidden; box-shadow:0 30px 90px rgba(15,23,42,.25) !important; }
.ex-modal .modal-header { padding:22px 26px !important; border:0 !important; background:linear-gradient(135deg,#fff 0,#fffbeb 60%,#fef3c7 100%); }
.ex-modal-title-wrap { display:flex; align-items:center; gap:12px; }
.ex-modal-icon { width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#fbbf24,#d97706); color:#fff; font-size:1.2rem; box-shadow:0 12px 24px rgba(217,119,6,.25); }
.ex-modal .modal-title { font-size:1rem !important; font-weight:800 !important; color:var(--ex-dark) !important; margin:0; }
.ex-modal-subtitle { color:var(--ex-muted); font-size:.8rem; margin-top:2px; }
.ex-modal .btn-close { width:34px; height:34px; border-radius:10px; background-color:#eef2f7; opacity:1; background-size:.7rem; }
.ex-modal .modal-body { padding:6px 26px 24px !important; background:#fff; }
.ex-modal .form-label { font-size:.74rem !important; font-weight:800 !important; color:#334155 !important; text-transform:uppercase; letter-spacing:.07em; margin-bottom:8px !important; }
.ex-modal .form-control, .ex-modal textarea { min-height:46px; border:1.5px solid #e2e8f0 !important; border-radius:13px !important; font-size:.88rem !important; padding:11px 14px !important; background:#f8fafc !important; color:#0f172a !important; transition:all .2s !important; }
.ex-modal .form-control:focus, .ex-modal textarea:focus { border-color:#d97706 !important; background:#fff !important; box-shadow:0 0 0 4px rgba(217,119,6,.1) !important; }
.ex-modal .modal-footer { padding:16px 26px 24px !important; border:0 !important; background:#fff; display:flex; justify-content:flex-end; gap:10px; }
.ex-modal .btn { min-height:44px; border-radius:13px !important; font-weight:800 !important; padding:10px 18px !important; }
.ex-modal .btn-light { background:#f1f5f9 !important; border-color:#e2e8f0 !important; color:#475569 !important; }
.ex-modal .btn-primary { background:linear-gradient(135deg,#fbbf24,#d97706) !important; border:0 !important; color:#fff !important; box-shadow:0 12px 22px rgba(217,119,6,.22); }
</style>

{{-- ── Stat Cards ── --}}
<div class="ex-stat-grid">
    <div class="ex-stat">
        <div class="ex-stat-icon amber"><i class="ti ti-category"></i></div>
        <div><div class="ex-stat-val">{{ count($vexcat) }}</div><div class="ex-stat-lbl">Total Categories</div></div>
    </div>
    <div class="ex-stat">
        <div class="ex-stat-icon red"><i class="ti ti-receipt"></i></div>
        <div><div class="ex-stat-val">{{ $totalExpenses }}</div><div class="ex-stat-lbl">Total Expenses</div></div>
    </div>
    <div class="ex-stat">
        <div class="ex-stat-icon green"><i class="ti ti-cash"></i></div>
        <div><div class="ex-stat-val">{{ number_format($totalAmount, 2) }}</div><div class="ex-stat-lbl">Total Amount</div></div>
    </div>
</div>

{{-- ── Table Card ── --}}
<div class="card ex-table-card">
    <div class="card-header">
        <div class="ex-card-title">
            <span class="visa-badge OV" style="background:#fef3c7;color:#92400e;">
                <i class="ti ti-category"></i> Expense Categories
            </span>
            <span class="ex-record-count">{{ count($vexcat) }} {{ count($vexcat) == 1 ? 'record' : 'records' }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Expenses') }}</th>
                        <th>{{ __('Total Amount') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vexcat as $index => $result)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <a href="/expenses?category={{ urlencode($result->category_name) }}" class="ex-cat-name">
                                    <span class="ex-cat-icon"><i class="ti ti-tag"></i></span>
                                    {{ $result->category_name }}
                                </a>
                            </td>
                            <td>
                                <span class="ex-desc">{{ $result->category_description ?: '—' }}</span>
                            </td>
                            <td>
                                <span class="ex-count-badge">
                                    <i class="ti ti-receipt"></i>
                                    {{ $result->expense_count }} {{ $result->expense_count == 1 ? 'expense' : 'expenses' }}
                                </span>
                            </td>
                            <td>
                                <span class="ex-amount">{{ number_format($result->total_amount, 2) }}</span>
                            </td>
                            <td>
                                @include('partials.visa-crud-actions', [
                                    'editUrl'    => null,
                                    'deleteUrl'  => route('vexcat.destroy', $result->id),
                                    'entityName' => __('category'),
                                    'editTitle'  => __('Edit Category'),
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="ex-empty">
                                    <i class="ti ti-category-2"></i>
                                    <p>No expense categories found. Add one to get started.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Add Category Modal ── --}}
<div class="modal fade ex-modal" id="createCategory" tabindex="-1" aria-labelledby="createCategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="{{ route('categories.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <div class="ex-modal-title-wrap">
                        <div class="ex-modal-icon"><i class="ti ti-category-plus"></i></div>
                        <div>
                            <h5 class="modal-title" id="createCategoryLabel">Add Expense Category</h5>
                            <div class="ex-modal-subtitle">Create a new category to group expenses</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" placeholder="e.g. Office Supplies" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Description</label>
                        <textarea name="category_description" class="form-control" rows="3" placeholder="Brief description of this category" style="min-height:90px;resize:none;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <i class="ti ti-check"></i> Add Category
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
