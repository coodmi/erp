@extends('layouts.admin')


@section('page-title')
    {{__('Agent Ledger')}}

@endsection

@push('script-page')
<!-- change amount field: unit price*number of unit -->
<script>
    $(document).on('change', 'input[name=unit_pirce], input[name=number_of_unit]', function(){
        var unit_price = $('input[name=unit_pirce]').val();
        var number_of_unit = $('input[name=number_of_unit]').val();
        var amount = unit_price * number_of_unit;
        $('input[name=amount]').val(amount);
    });
    
      $(document).on('change', 'input[id=unit_pirce], input[id=number_of_unit]', function(){
        var unit_prices = $('input[id=unit_pirce]').val();
        var number_of_units = $('input[id=number_of_unit]').val();
        var amounts = unit_prices * number_of_units;
        $('input[id=amount]').val(amounts);
    });
    
</script>
<!-- Calculate due : amount - advance -->

<script>
    $(document).on('change', 'input[name=amount], input[name=advance]', function(){
        var amount = $('input[name=amount]').val();
        var advance = $('input[name=advance]').val();
        var due = amount - advance;
        if (due < 0) {
            
            $('input[name=deposit]').val(-due);
            $('input[name=due]').val(0);
            
        }
        else {
            $('input[name=due]').val(due);
            $('input[name=deposit]').val(0);
        }
    });
    
    $(document).on('change', 'input[id=amount], input[id=advance]', function(){
        var amounts = $('input[id=amount]').val();
        var advances = $('input[id=advance]').val();
        var dues = amounts - advances;
        if (dues < 0) {
            
            $('input[id=deposit]').val(-dues);
            $('input[id=due]').val(0);
            
        }
        else {
            $('input[id=due]').val(dues);
            $('input[id=deposit]').val(0);
        }
    });
</script>
@endpush


@php
    $agentId = request()->query('agent_id');
    $agent_name = \App\Models\Agent::where('id', $agentId)->first();
    $agents = \App\Models\Agent::pluck('agent_name', 'id');
    $ledgers = \App\Models\Ledger::where('agent_id', $agentId)->get();
@endphp




@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active"><a href="/ledger/agent">{{__('Agent Ledger')}}</a></li>
    <li class="breadcrumb-item active">{{ $agent_name->agent_name}}</li>

@endsection

@section('action-btn')
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createAgent">
        <i class="ti ti-plus"></i>
        </button>
    </div>
@endsection

@section('modal')

@endsection

@section('content')

<style>
    @media(max-width:575px){.ai-ledger-grid{grid-template-columns:1fr !important;}}
</style>
<div class="modal fade" id="createAgent" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
  <form method="post" action="{{ route('ledger.store') }}">
  @csrf
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Ledger Information</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="padding: 8px 24px 20px;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px 18px;" class="ai-ledger-grid">

          {{-- Row 1: Agent (full width) --}}
          <div style="grid-column: 1 / -1;">
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Agent</label>
            <select name="agent_id" class="form-control" required>
              <option value="" disabled selected>Select Agent</option>
              @foreach($agents as $key => $value)
                <option value="{{$key}}">{{$value}}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Date</label>
            <input type="date" name="date" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Paid For</label>
            <input type="text" name="paid_for" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Unit Price</label>
            <input type="number" name="unit_pirce" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Number of Unit</label>
            <input type="number" name="number_of_unit" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Amount</label>
            <input type="number" name="amount" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Payment Mode</label>
            <input type="text" name="payment_mode" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Advance</label>
            <input type="number" name="advance" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Deposit</label>
            <input type="number" name="deposit" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Due</label>
            <input type="number" name="due" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Refund</label>
            <input type="number" name="refund" class="form-control" value="0" required>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="ti ti-check"></i> Add Ledger</button>
      </div>
    </div>
    </form>
  </div>
</div>

<div class="modal fade" id="editAgent" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
  <form method="post" action="{{ route('ledger.updateinfo') }}" id="EditLedger">
  @csrf
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Update Ledger Information</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="padding: 8px 24px 20px;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px 18px;" class="ai-ledger-grid">

          {{-- Row 1: Agent (full width) --}}
          <div style="grid-column: 1 / -1;">
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Agent</label>
            <select name="agent_id" id="agent_id" class="form-control" required>
              <option value="" disabled selected>Select Agent</option>
              @foreach($agents as $key => $value)
                <option value="{{$key}}">{{$value}}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Date</label>
            <input type="date" name="date" id="date" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Paid For</label>
            <input type="text" name="paid_for" id="paid_for" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Unit Price</label>
            <input type="number" name="unit_pirce" id="unit_pirce" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Number of Unit</label>
            <input type="number" name="number_of_unit" id="number_of_unit" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Payment Mode</label>
            <input type="text" name="payment_mode" id="payment_mode" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Advance</label>
            <input type="number" name="advance" id="advance" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Deposit</label>
            <input type="number" name="deposit" id="deposit" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Due</label>
            <input type="number" name="due" id="due" class="form-control" required>
          </div>

          <div>
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Refund</label>
            <input type="number" name="refund" id="refund" class="form-control" value="0" required>
          </div>

          <input type="hidden" name="ledger_id" id="ledger_id" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="ti ti-check"></i> Update Ledger</button>
      </div>
    </div>
    </form>
  </div>
</div>


    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="datainfo">
                            <thead>
                            <tr>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Agent')}}</th>
                                <th>{{__('Paid For')}}</th>
                                <th>{{__('Unit Price')}}</th>
                                <th>{{__('Number of Unit')}}</th>
                                <th>{{__('Payment Mode')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Advance')}}</th>
                                <th>{{__('Due')}}</th>
                                <th>{{__('Deposit')}}</th>
                                <th>{{__('Refund')}}</th>
                                <th>{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($ledgers as $ledger)
                                <tr>
                                    <td>{{$ledger->date}}</td>
                                    <!-- If agent name show name, else show N/A -->
                                    <td>{{ $ledger->agent ? $ledger->agent->agent_name : 'N/A' }}</td>
                                    <td>{{$ledger->paid_for}}</td>
                                    <td>{{$ledger->unit_pirce}}</td>
                                    <td>{{$ledger->number_of_unit}}</td>
                                    <td>{{$ledger->payment_mode}}</td>
                                    <td>{{$ledger->amount}}</td>
                                    <td>{{$ledger->advance}}</td>
                                    <td>{{$ledger->due}}</td>
                                    <td>{{$ledger->deposit}}</td>
                                    <td>{{$ledger->refund}}</td>
                                    <td>
                                        <button type="button" data-id="{{$ledger->id}}" id="editLedgerBtn" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editAgent">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['ledger.destroy', $ledger->id], 'id' => 'delete-form-'.$ledger->id, 'class' => 'delete-form']) !!}
                                        <button type="button" class="btn btn-sm align-items-center bs-pass-para delete-btn" data-bs-toggle="tooltip" title="{{__('Delete')}}">
                                            <i class="ti ti-trash text-white"></i>
                                        </button>
                                        {!! Form::close() !!}
                                    </div>

                                    <script>
                                        // Add event listener to all delete buttons
                                        document.querySelectorAll('.delete-btn').forEach(button => {
                                            button.addEventListener('click', function() {
                                                if (confirm("Are you sure you want to delete?")) {
                                                    // If confirmed, submit the form
                                                    this.closest('form').submit();
                                                }
                                            });
                                        });
                                    </script>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    @push('script-page')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.dataTables.css">
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
    <script>
    $(document).ready( function(){
        new DataTable('#datainfo', {
            layout: {
                topStart: { 
                    buttons: [ 
                        'print',
                        {
                            extend: 'pdfHtml5',
                            download: 'open'
                        }
                    ]
                }
            }
        });
    });
    
        </script>
@endpush


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <script>
        
        $(document).ready( function(){
            
            $(document).on('click', '#editLedgerBtn', function() {
                let ledgerID = $(this).data('id');
    
                $.ajax({
                    type: 'GET',
                    url: 'infoedit/' + ledgerID,
    
                    success: function(data) {
                         
                        $('#EditLedger').find('#agent_id').val(data.agent_id);
                        $('#EditLedger').find('#date').val(data.date);
                        $('#EditLedger').find('#paid_for').val(data.paid_for);
                        $('#EditLedger').find('#unit_pirce').val(data.unit_pirce);
                        $('#EditLedger').find('#number_of_unit').val(data.number_of_unit);
                        $('#EditLedger').find('#amount').val(data.amount);
                        $('#EditLedger').find('#payment_mode').val(data.payment_mode);
                        $('#EditLedger').find('#advance').val(data.advance);
                        $('#EditLedger').find('#deposit').val(data.deposit);
                        $('#EditLedger').find('#due').val(data.due);
                        $('#EditLedger').find('#refund').val(data.refund); 
                        $('#EditLedger').find('#ledger_id').val(data.id);  
                        $('#EditLedger').attr('data-id', data.id);
                    },
                    error: function(error) {
                        console.log('error');
                    }
    
                });
            });
        });
        
    </script>
@endsection

