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
</script>
@endpush


@php
    $agents = \App\Models\Agent::pluck('agent_name', 'id');
    $ledgers = \App\Models\Ledger::get();
@endphp




@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active">{{__('Agent Ledger')}}</li>

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
    div.dt-container div.dt-layout-row div.dt-layout-cell.dt-layout-start {
        justify-content: flex-start;
        margin-right: auto;
        padding-left: 25px;
        padding-top: 20px;
    }
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
                                <th>{{__('SL')}}</th>
                                <th>{{__('Agent')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Advance')}}</th>
                                <th>{{__('Due')}}</th>
                                <th>{{__('Deposit')}}</th>
                                <th>{{__('Refund')}}</th>
                                <th>{{__('Action')}}</th>
                                
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($ledgers->unique('agent_id') as $index=> $ledger)
                                <tr>
                                    @php
                                        $amount=App\Models\Ledger::where('agent_id',$ledger->agent->id)->get();
                                    @endphp
                                    <td>{{$index+1}}</td>
                                    <td>{{ $ledger->agent ? $ledger->agent->agent_name : 'N/A' }}</td>
                                    <td>{{ $amount->sum('amount') }}</td> 
                                    <td>{{ $amount->sum('advance') }}</td>
                                    <td>{{ $amount->sum('due') }}</td>
                                    <td>{{ $amount->sum('deposit') }}</td>
                                    <td>{{ $amount->sum('refund') }}</td> 
                                    <td>
                                  
    <a class="btn btn-sm align-items-center  btn-success" href="/ledger/agent?agent_id={{ $ledger->agent ? $ledger->agent->id : '0' }}" data-bs-toggle="tooltip" title="{{__('View')}}">
        <i class="ti ti-eye text-white"></i>
    </a>
    


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
    

@endsection

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