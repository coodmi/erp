@extends('layouts.admin')


@section('page-title')
    {{__('Vendor Ledger')}}

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
    $agents = \App\Models\Vendor::pluck('vendor_name', 'id');
    $ledgers = \App\Models\LedgerV::get();
@endphp




@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active">{{__('Vendor Ledger')}}</li>

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
  <form method="post" action="{{ route('vledger.store') }}">
  @csrf
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Ledger Information</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="padding: 8px 24px 20px;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px 18px;" class="ai-ledger-grid">

          {{-- Row 1: Vendor (full width) --}}
          <div style="grid-column: 1 / -1;">
            <label class="form-label" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#334155;margin-bottom:6px;">Vendor</label>
            <select name="vendor_id" class="form-control" required>
              <option value="" disabled selected>Select Vendor</option>
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
                        <table class="table datatable">
                            <thead>
                            <tr>
                               
                                <th>{{__('Vendor')}}</th>
                               
                                <th>{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($ledgers as $ledger)
                                <tr>
                                   
                                    <td>{{ $ledger->vendor ? $ledger->vendor->vendor_name : 'N/A' }}</td>
                                    
                                    <td>
                                    <a class="btn btn-sm align-items-center  btn-success" href="/ledger/vendor?vendor_id={{ $ledger->vendor ? $ledger->vendor->id : '0' }}" data-bs-toggle="tooltip" title="{{__('View')}}">
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

