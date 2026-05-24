@extends('layouts.admin')

@php
    $agents  = \App\Models\Agent::pluck('agent_name', 'id');
    $ledgers = \App\Models\Ledger::get();

    $totAmount = $totAdvance = $totDue = $totDeposit = $totRefund = 0;
    foreach ($ledgers->unique('agent_id') as $l) {
        $rows = \App\Models\Ledger::where('agent_id', $l->agent_id)->get();
        $totAmount  += $rows->sum('amount');
        $totAdvance += $rows->sum('advance');
        $totDue     += $rows->sum('due');
        $totDeposit += $rows->sum('deposit');
        $totRefund  += $rows->sum('refund');
    }
@endphp

@section('page-title'){{ __('Agent Ledger') }}@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Agent Ledger') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#createLedger">
            <i class="ti ti-plus"></i> {{ __('Add Entry') }}
        </button>
    </div>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/visa-manage.css') }}?v=2">
@endpush

@push('script-page')
<script>
$(document).on('change', 'input[name=unit_pirce], input[name=number_of_unit]', function(){
    var up = parseFloat($('input[name=unit_pirce]').val()) || 0;
    var nu = parseFloat($('input[name=number_of_unit]').val()) || 0;
    $('input[name=amount]').val((up * nu).toFixed(2));
    triggerDue();
});
$(document).on('change input', 'input[name=amount], input[name=advance]', function(){ triggerDue(); });
function triggerDue(){
    var amount  = parseFloat($('input[name=amount]').val()) || 0;
    var advance = parseFloat($('input[name=advance]').val()) || 0;
    var due = amount - advance;
    if (due < 0) { $('input[name=deposit]').val((-due).toFixed(2)); $('input[name=due]').val(0); }
    else         { $('input[name=due]').val(due.toFixed(2)); $('input[name=deposit]').val(0); }
}
</script>
@endpush

@section('content')
<style>
:root { --ld-dark:#0f172a; --ld-muted:#64748b; --ld-border:#e2e8f0; }

.ld-stat-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:16px; margin-bottom:24px; }
@media(max-width:1199px){ .ld-stat-grid{ grid-template-columns:repeat(3,1fr); } }
@media(max-width:767px){ .ld-stat-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:480px){ .ld-stat-grid{ grid-template-columns:1fr; } }

.ld-stat { background:#fff; border:1px solid rgba(226,232,240,.8); border-radius:18px; padding:18px; box-shadow:0 12px 30px rgba(15,23,42,.06); display:flex; align-items:center; gap:12px; transition:box-shadow .2s,transform .2s; }
.ld-stat:hover { box-shadow:0 16px 38px rgba(15,23,42,.1); transform:translateY(-2px); }
.ld-stat-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
.ld-stat-icon.blue   { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; }
.ld-stat-icon.green  { background:linear-gradient(135deg,#34d399,#059669); color:#fff; }
.ld-stat-icon.red    { background:linear-gradient(135deg,#fb7185,#e11d48); color:#fff; }
.ld-stat-icon.amber  { background:linear-gradient(135deg,#fbbf24,#d97706); color:#fff; }
.ld-stat-icon.purple { background:linear-gradient(135deg,#a78bfa,#7c3aed); color:#fff; }
.ld-stat-val { font-size:1.3rem; font-weight:800; color:var(--ld-dark); line-height:1.1; margin-bottom:3px; }
.ld-stat-lbl { font-size:.68rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; }

.ld-table-card { border:1px solid rgba(226,232,240,.9) !important; border-radius:22px !important; box-shadow:0 16px 42px rgba(15,23,42,.07) !important; overflow:hidden; }
.ld-table-card .card-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; background:linear-gradient(135deg,#fff,#f8fafc) !important; border-bottom:1px solid var(--ld-border) !important; padding:18px 20px !important; }
.ld-table-card .table { border-collapse:separate; border-spacing:0; min-width:800px; }
.ld-table-card .table thead th { background:#f8fafc; color:#475569; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--ld-border); padding:14px 16px; white-space:nowrap; }
.ld-table-card .table tbody td { padding:13px 16px; color:#334155; vertical-align:middle; border-color:#eef2f7; }
.ld-table-card .table tbody tr:hover td { background:#f8fbff; }

.ld-agent-name { font-weight:700; color:#1d4ed8; font-size:.92rem; }
.ld-amt { font-weight:700; }
.ld-amt.green { color:#059669; }
.ld-amt.red   { color:#e11d48; }
.ld-amt.amber { color:#d97706; }
.ld-amt.blue  { color:#2563eb; }
.ld-amt.purple{ color:#7c3aed; }

.ld-view-btn { display:inline-flex; align-items:center; gap:5px; padding:6px 14px; border-radius:10px; font-size:.78rem; font-weight:700; background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; text-decoration:none; transition:all .18s; box-shadow:0 4px 12px rgba(37,99,235,.2); }
.ld-view-btn:hover { filter:brightness(1.08); transform:translateY(-1px); color:#fff; }

.ld-empty { text-align:center; padding:48px 20px; color:#94a3b8; }
.ld-empty i { font-size:2.5rem; opacity:.3; display:block; margin-bottom:12px; }

/* Modal */
.ld-modal .modal-dialog { max-width:680px; }
.ld-modal .modal-content { border:0 !important; border-radius:24px !important; overflow:hidden; box-shadow:0 30px 90px rgba(15,23,42,.25) !important; }
.ld-modal .modal-header { padding:22px 26px !important; border:0 !important; background:linear-gradient(135deg,#fff 0,#eff6ff 60%,#dbeafe 100%); }
.ld-modal-title-wrap { display:flex; align-items:center; gap:12px; }
.ld-modal-icon { width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; font-size:1.2rem; box-shadow:0 12px 24px rgba(37,99,235,.25); }
.ld-modal .modal-title { font-size:1rem !important; font-weight:800 !important; color:var(--ld-dark) !important; margin:0; }
.ld-modal .modal-body { padding:6px 26px 24px !important; background:#fff; }
.ld-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.ld-form-grid .full { grid-column:1/-1; }
@media(max-width:575px){ .ld-form-grid{ grid-template-columns:1fr; } .ld-form-grid .full{ grid-column:1/-1; } }
.ld-label { font-size:.72rem; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:.07em; margin-bottom:7px; display:block; }
.ld-input { width:100%; min-height:44px; border:1.5px solid #e2e8f0; border-radius:12px; font-size:.88rem; padding:10px 14px; background:#f8fafc; color:#0f172a; transition:all .2s; outline:none; }
.ld-input:focus { border-color:#2563eb; background:#fff; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.ld-modal .modal-footer { padding:16px 26px 24px !important; border:0 !important; background:#fff; display:flex; justify-content:flex-end; gap:10px; }
.ld-modal .btn { min-height:44px; border-radius:12px !important; font-weight:800 !important; padding:10px 18px !important; }
.ld-modal .btn-light { background:#f1f5f9 !important; border-color:#e2e8f0 !important; color:#475569 !important; }
.ld-modal .btn-primary { background:linear-gradient(135deg,#3b82f6,#2563eb) !important; border:0 !important; box-shadow:0 12px 22px rgba(37,99,235,.22); }
</style>

{{-- Stats --}}
<div class="ld-stat-grid">
    <div class="ld-stat">
        <div class="ld-stat-icon blue"><i class="ti ti-users"></i></div>
        <div><div class="ld-stat-val">{{ $ledgers->unique('agent_id')->count() }}</div><div class="ld-stat-lbl">Agents</div></div>
    </div>
    <div class="ld-stat">
        <div class="ld-stat-icon green"><i class="ti ti-cash"></i></div>
        <div><div class="ld-stat-val">{{ number_format($totAmount,2) }}</div><div class="ld-stat-lbl">Total Amount</div></div>
    </div>
    <div class="ld-stat">
        <div class="ld-stat-icon amber"><i class="ti ti-wallet"></i></div>
        <div><div class="ld-stat-val">{{ number_format($totAdvance,2) }}</div><div class="ld-stat-lbl">Total Advance</div></div>
    </div>
    <div class="ld-stat">
        <div class="ld-stat-icon red"><i class="ti ti-alert-circle"></i></div>
        <div><div class="ld-stat-val">{{ number_format($totDue,2) }}</div><div class="ld-stat-lbl">Total Due</div></div>
    </div>
    <div class="ld-stat">
        <div class="ld-stat-icon purple"><i class="ti ti-refresh"></i></div>
        <div><div class="ld-stat-val">{{ number_format($totRefund,2) }}</div><div class="ld-stat-lbl">Total Refund</div></div>
    </div>
</div>

{{-- Table --}}
<div class="card ld-table-card">
    <div class="card-header">
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="visa-badge BV"><i class="ti ti-book"></i> Agent Ledger</span>
            <span style="font-size:.8rem;color:#94a3b8;font-weight:600;">{{ $ledgers->unique('agent_id')->count() }} agents</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable mb-0" id="datainfo">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Agent') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Advance') }}</th>
                        <th>{{ __('Due') }}</th>
                        <th>{{ __('Deposit') }}</th>
                        <th>{{ __('Refund') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgers->unique('agent_id') as $index => $ledger)
                        @php
                            $rows = \App\Models\Ledger::where('agent_id', $ledger->agent_id)->get();
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="ld-agent-name">{{ $ledger->agent ? $ledger->agent->agent_name : 'N/A' }}</span></td>
                            <td><span class="ld-amt green">{{ number_format($rows->sum('amount'),2) }}</span></td>
                            <td><span class="ld-amt blue">{{ number_format($rows->sum('advance'),2) }}</span></td>
                            <td><span class="ld-amt red">{{ number_format($rows->sum('due'),2) }}</span></td>
                            <td><span class="ld-amt amber">{{ number_format($rows->sum('deposit'),2) }}</span></td>
                            <td><span class="ld-amt purple">{{ number_format($rows->sum('refund'),2) }}</span></td>
                            <td>
                                <a href="/ledger/agent?agent_id={{ $ledger->agent ? $ledger->agent->id : '0' }}" class="ld-view-btn">
                                    <i class="ti ti-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="ld-empty">
                                    <i class="ti ti-book-off"></i>
                                    <p>No ledger entries found. Add one to get started.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Ledger Modal --}}
<div class="modal fade ld-modal" id="createLedger" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="{{ route('ledger.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <div class="ld-modal-title-wrap">
                        <div class="ld-modal-icon"><i class="ti ti-book-plus"></i></div>
                        <div>
                            <h5 class="modal-title">Add Ledger Entry</h5>
                            <p style="font-size:.75rem;color:#94a3b8;margin:0;">Record a new agent transaction</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="ld-form-grid">
                        <div class="full">
                            <label class="ld-label">Agent <span class="text-danger">*</span></label>
                            <select name="agent_id" class="ld-input" required>
                                <option value="" disabled selected>— Select Agent —</option>
                                @foreach($agents as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="ld-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="ld-input" required>
                        </div>
                        <div>
                            <label class="ld-label">Paid For <span class="text-danger">*</span></label>
                            <input type="text" name="paid_for" class="ld-input" placeholder="e.g. Work Permit Visa" required>
                        </div>
                        <div>
                            <label class="ld-label">Unit Price <span class="text-danger">*</span></label>
                            <input type="number" name="unit_pirce" class="ld-input" placeholder="0.00" step="0.01" required>
                        </div>
                        <div>
                            <label class="ld-label">Number of Units <span class="text-danger">*</span></label>
                            <input type="number" name="number_of_unit" class="ld-input" placeholder="1" required>
                        </div>
                        <div>
                            <label class="ld-label">Amount</label>
                            <input type="number" name="amount" class="ld-input" placeholder="Auto-calculated" step="0.01" required>
                        </div>
                        <div>
                            <label class="ld-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="ld-input" required>
                                <option value="">— Select —</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank">Bank Transfer</option>
                                <option value="Bkash">Bkash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Rocket">Rocket</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>
                        <div>
                            <label class="ld-label">Advance</label>
                            <input type="number" name="advance" class="ld-input" placeholder="0.00" step="0.01" required>
                        </div>
                        <div>
                            <label class="ld-label">Deposit</label>
                            <input type="number" name="deposit" class="ld-input" placeholder="Auto-calculated" step="0.01" required>
                        </div>
                        <div>
                            <label class="ld-label">Due</label>
                            <input type="number" name="due" class="ld-input" placeholder="Auto-calculated" step="0.01" required>
                        </div>
                        <div>
                            <label class="ld-label">Refund</label>
                            <input type="number" name="refund" class="ld-input" value="0" step="0.01" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <i class="ti ti-check"></i> Add Entry
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
