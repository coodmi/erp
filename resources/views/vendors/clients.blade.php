@extends('layouts.admin')


@php
    $profile = \App\Models\Utility::get_file('uploads/avatar');

    // Check if 'visa_type' parameter is set in the URL
    $vtype = isset($_GET['visa_type']) ? $_GET['visa_type'] : null;
    $aid = isset($_GET['vendor']) ? $_GET['vendor'] : null;
    $results = [];
    $countries = [];
    

    // Get data from the database based on the 'visa_type' parameter
    
        try {
            // Establish a database connection
            $connection = DB::connection();

            // Escape the user input to prevent SQL injection
            $aid = $connection->getPdo()->quote($aid);
            $vtype = $connection->getPdo()->quote($vtype);

            // Execute a raw SQL query
            $results = $connection->select("SELECT clients.*, countries.country_name FROM clients LEFT JOIN countries ON clients.visa_country_id = countries.id WHERE clients.vendor_id = $aid");
            $countries = $connection->select("SELECT * FROM countries");
            //$results = $connection->select("SELECT * FROM clients WHERE vendor_id = $aid");
            $agent_name = $connection->select("SELECT vendor_name FROM vendors WHERE id = $aid");
            $agent_id = $connection->select("SELECT id FROM vendors WHERE id = $aid");
            if (empty($agent_name)) {
                $agent_name = "Unknown";
                $agent_id = null;
            }
            else {
                $agent_name = $agent_name[0]->vendor_name ?? "Unknown";
                $agent_id = isset($agent_id[0]) ? $agent_id[0]->id : null;
            }
        } catch (\Exception $e) {
            // Log the error message
        }
    
@endphp

@section('page-title')
    {{ __('Vendors') }}
@endsection

@push('script-page')
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Service Providing') }}</li>
    <li class="breadcrumb-item">
    @if (isset($_GET['visa_type']))
    @php
        $vtype = $_GET['visa_type'];
    @endphp

    @if ($vtype == "WV")
        {{ __('Work Permit Visa') }}
    @elseif ($vtype == "BV")
        {{ __('Business Visa') }}
    @elseif ($vtype == "SV")
        {{ __('Student Visa') }}
    @elseif ($vtype == "TV")
        {{ __('Tourist Visa') }}
    @elseif ($vtype == "OV")
        {{ __('Others') }}
    @else
        {{ $vtype }}
    @endif
@endif
</li>

@endsection

@section('action-btn')
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createAgent">
        <i class="ti ti-plus"></i>
        </button>
    </div>
@endsection

@section('content')

<div class="modal fade" id="createAgent" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
  <form method="post" action="{{ route('vclients.store') }}">
  @csrf
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Client</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
                <div class="form-group">
                    <label for="agent_name" class="form-label">Client Name</label>
                    <input type="text" name="client_name" class="form-control" placeholder="Client Name" required>
                    <label for="passport_no" class="form-label">Passport Number</label>
                    <input type="text" name="passport_no" class="form-control" placeholder="Client Passport Number" required>
                </div>
                <div class="form-group">
                    <p class="alert alert-info mt-1 mb-3">Visa Type: <b>{{$vtype}}</b></p>
                    <p class="alert alert-info mt-1 mb-3">Vendor: <b>{{$agent_name}}</b></p>
                    
                    <input type="hidden" name="visa_type" value="{{$vtype}}">
                    <input type="hidden" name="vendor_id" value="{{$agent_id}}">
                    <input type="hidden" name="agent_id" value="">
                    <input type="hidden" name="isTicket" value="0">

                    <!-- <select name="visa_type" class="form-control" required>
                        <option value="WV">Work Permit Visa</option>
                        <option value="BV">Business Visa</option>
                        <option value="SV">Student Visa</option>
                        <option value="TV">Tourist Visa</option>
                        <option value="OV">Others</option>
                    </select> -->

                    <div class="form-group">
                    <label for="country" class="form-label">Visa Country</label>
                    <select name="visa_country_id" class="form-control" required>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                        @endforeach
                        
                    </select>

                </div>

                </div>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <input type="submit" class="btn btn-primary" value="Add Client"></input>
      </div>
    </div>
    </form>
  </div>
</div>




<div class="modal" aria-labelledby="createAgent">
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="modal-body">
            <div class="row">
                <div class="form-group">
                    <label for="agent_name">Agent Name</label>
                    <input type="text" name="agent_name" class="form-control" placeholder="Enter Agent Name" required>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <input type="button" value="Cancel" class="btn  btn-light" data-bs-dismiss="modal">
            <input type="submit" value="Create" class="btn  btn-primary">
        </div>
    </form>
</div>

    <div class="row mt-4">
        <div class="col-xxl-12">
           
        <div class="card">

        <div class="card-body table-border-style">


            <div class="table-responsive">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('Client Name') }}</th>
                        <th scope="col">{{ __('Passport Number') }}</th>
                        <th scope="col">{{ __('Client ID') }}</th>
                        <th scope="col">{{ __('Visa Type') }}</th>
                        <th scope="col">{{ __('Country') }}</th>
                        <th scope="col">{{ __('Paid') }}</th>
                        <th scope="col">{{ __('Due') }}</th>
                        <th scope="col">{{ __('Attachment') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>

                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @foreach ($results as $index => $result)
                        <tr>
                            <th scope="row">{{ $index + 1 }}</th>
                            <td>{{ $result->client_name }}</td>
                            <td>{{ $result->passport_no }}</td>
                            <td>{{ $result->unique_code }}</td>
                            <td>
                                @if ($result->visa_type == "WV")
                                    Work Visa
                                @elseif ($result->visa_type == "SV")
                                    Student Visa
                                @elseif ($result->visa_type == "TV")
                                    Tourist Visa
                                @elseif ($result->visa_type == "BV")
                                    Business Visa
                                @else
                                    Other Visa
                                @endif
                            </td>
                            <td>{{ $result->country_name }}</td>
                            <td>{{ $result->amount_paid }}</td>
                            <td>{{ $result->amount_due }}</td>
                            <td>
                            @if (!empty($result->attachment) || !empty($result->attachment2) || !empty($result->attachmen3) || !empty($result->attachment4))
                                          
                                        @if (!empty($result->attachment)) 

                                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="Passport" href="{{ asset(Storage::url($result->attachment)) }}" class="text-body" download>
                                                <i class="fas fa-passport"></i>
                                            </a>
                                        
                                        @endif
                                        @if (!empty($result->attachment2)) 
                                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="Photo" href="{{ asset(Storage::url($result->attachment2)) }}" class="text-body" download>
                                                <i class="fas fa-file-image"></i>
                                            </a>
                                        
                                        @endif
                                        @if (!empty($result->attachmen3)) 
                                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="PCC" href="{{ asset(Storage::url($result->attachmen3)) }}" class="text-body" download>
                                                <i class="fas fa-file"></i>
                                            </a>
                                        
                                        @endif
                                        @if (!empty($result->attachment4)) 
                                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="Others" href="{{ asset(Storage::url($result->attachment4)) }}" class="text-body" download>
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        
                                        @endif
                                        @else
                                            <i class="fas fa-times"></i>
                                        @endif
                                    </td>
                            <td>{{ $result->status }}</td>
                            <td>
                                @include('partials.visa-crud-actions', [
                                    'editUrl' => route('vclients.edit', $result->id),
                                    'deleteUrl' => route('vclients.destroy', $result->id),
                                    'entityName' => __('client'),
                                    'editTitle' => __('Edit Client'),
                                    'extra' => '<a target="_blank" href="/vendors?visa_type='.e(request('visa_type')).'&moneyrecipt='.e(request('vendor')).'&client_id='.$result->id.'" class="visa-crud-btn edit" title="'.e(__('Receipt')).'"><i class="ti ti-printer"></i></a>',
                                ])
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
    <script>
        $(document).on('change', '#password_switch', function() {
            if ($(this).is(':checked')) {
                $('.ps_div').removeClass('d-none');
                $('#password').attr('required', true);
            } else {
                $('.ps_div').addClass('d-none');
                $('#password').val(null);
                $('#password').removeAttr('required');
            }
        });

        $(document).on('click', '.login_enable', function() {
            setTimeout(function() {
                $('.modal-body').append($('<input>', {
                    type: 'hidden',
                    val: 'true',
                    name: 'login_enable'
                }));
            }, 2000);
        });
    </script>
@endpush
