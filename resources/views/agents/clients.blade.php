@extends('layouts.admin')


@php
    $profile = \App\Models\Utility::get_file('uploads/avatar');

    // Check if 'visa_type' parameter is set in the URL
    $vtype = isset($_GET['visa_type']) ? $_GET['visa_type'] : null;
    $aid = isset($_GET['agent']) ? $_GET['agent'] : null;
    $results = [];
    $countries = [];
    

    // Get data from the database based on the 'visa_type' parameter
    if (!is_null($aid)) {
   
        try {
            // Establish a database connection
            $connection = DB::connection();

            // Escape the user input to prevent SQL injection
            $aid = $connection->getPdo()->quote($aid);
            
            
              
            // Execute a raw SQL query
            //$results = $connection->select("SELECT * FROM clients WHERE agent_id = $aid");
            
            
            $country_id=Session::get('country_id');
            $status=Session::get('c_status');
            
            if(isset($country_id)){
                 
                if($country_id=='all'){
                    if(isset($status)){
                        if($status=='all'){
                            $results = $connection->select("
                                SELECT clients.*, countries.country_name
                                FROM clients
                                LEFT JOIN countries ON clients.visa_country_id = countries.id
                                WHERE clients.agent_id = $aid
                            ");
                        }else{
                            $results = $connection->select("
                                SELECT clients.*, countries.country_name
                                FROM clients 
                                LEFT JOIN countries ON clients.visa_country_id = countries.id
                                WHERE clients.agent_id = $aid  
                                AND clients.status = '$status'
                            ");
                        }
                    }else{
                        $results = $connection->select("
                            SELECT clients.*, countries.country_name
                            FROM clients
                            LEFT JOIN countries ON clients.visa_country_id = countries.id
                            WHERE clients.agent_id = $aid
                        ");
                    }
                }else{
                    if(isset($status)){
                        if($status=='all'){
                            $results = $connection->select("
                                SELECT clients.*, countries.country_name
                                FROM clients 
                                LEFT JOIN countries ON clients.visa_country_id = countries.id
                                WHERE clients.agent_id = $aid 
                                AND clients.visa_country_id = $country_id 
                            ");
                        }else{
                            $results = $connection->select("
                                SELECT clients.*, countries.country_name
                                FROM clients 
                                LEFT JOIN countries ON clients.visa_country_id = countries.id
                                WHERE clients.agent_id = $aid 
                                AND clients.visa_country_id = $country_id 
                                AND clients.status = '$status'
                            ");
                        }
                    }else{
                        $results = $connection->select("
                            SELECT clients.*, countries.country_name
                            FROM clients 
                            LEFT JOIN countries ON clients.visa_country_id = countries.id
                            WHERE clients.agent_id = $aid 
                            AND clients.visa_country_id = $country_id 
                        ");
                    }
                }
                
            }else{
            
                if(isset($status)){
                
                    if($status=='all'){
                        $results = $connection->select("
                            SELECT clients.*, countries.country_name
                            FROM clients
                            LEFT JOIN countries ON clients.visa_country_id = countries.id
                            WHERE clients.agent_id = $aid
                        ");
                    }else{
                     
                        $results = $connection->select("
                            SELECT clients.*, countries.country_name
                            FROM clients 
                            LEFT JOIN countries ON clients.visa_country_id = countries.id
                            WHERE clients.agent_id = $aid   
                            AND clients.status = '$status'
                        "); 
                    }
                }else{
                    $results = $connection->select("
                        SELECT clients.*, countries.country_name
                        FROM clients
                        LEFT JOIN countries ON clients.visa_country_id = countries.id
                        WHERE clients.agent_id = $aid
                    ");
                }
                
            }
           
            $countries = $connection->select("SELECT * FROM countries");
            
            $agent_name = $connection->select("SELECT agent_name FROM agents WHERE id = $aid");
             
            $agent_id = $connection->select("SELECT id FROM agents WHERE id = $aid");
 
            if (empty($agent_name)) {
                $agent_name = "Unknown";
                $agent_id = null;
            }
            else {
                $agent_name = $agent_name[0]->agent_name ?? "Unknown";
                $agent_id = isset($agent_id[0]) ? $agent_id[0]->id : null;
            }
        } catch (\Exception $e) {
            // Log the error message
        }
    }
@endphp

@section('page-title')
    {{ __('Agents') }}
@endsection

@push('script-page')

@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
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
        <!-- <button data-size="md" data-bs-target="#createAgent" title="{{ __('Create Client') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </button> -->
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
                    <label for="address" class="form-label">Client Address</label>
                    <input type="text" name="address" class="form-control" placeholder="Client Address" required>
                    <label for="passport_no" class="form-label">Passport Number</label>
                    <input type="text" name="passport_no" class="form-control" placeholder="Client Passport Number" required>
                </div>
                <div class="form-group">
                    <p class="alert alert-info mt-1 mb-3">Visa Type: <b>{{$vtype}}</b></p>
                    <p class="alert alert-info mt-1 mb-3">Agent: <b>{{$agent_name}}</b></p>
                    
                    <input type="hidden" name="visa_type" value="{{$vtype}}">
                    <input type="hidden" name="agent_id" value="{{$agent_id}}">
                    <input type="hidden" name="vendor_id" value="">
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
                    <label for="agent_name" style="text-transform:uppercase">Agent Name</label>
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
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Choose Country</label>
                        <select name="country_id" id="country_id" onchange="setcountry()" class="form-control">
                            <option value="">Choose</option>
                            <option value="all">All</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                            @endforeach
                        </select>
                    </div> 
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Choose Status</label>
                        <select name="status" id="status" onchange="setstatus()" class="form-control">
                            <option value="">Choose</option>
                            <option value="all">All</option> 
                            <option value="submitted">Submitted</option>  
                            <option value="Work Permit Received">{{ __('Work Permit Received') }}</option>
                            <option value="Applied For Visa">{{ __('Applied For Visa') }}</option>
                            <option value="Visa Received">{{ __('Visa Received') }}</option>
                            <option value="Completed">{{ __('Completed') }}</option>
                            <option value="File Received">{{ __('File Received') }}</option>
                            <option value="Cancelled">{{ __('Cancelled') }}</option>
                        </select>
                    </div> 
                </div>
                 <div class="col-12">
                     <div class="table-responsive" style="padding:20px">
                        <table class="table" id="datainfo">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">{{ __('Client Name') }}</th>
                                <th scope="col">{{ __('Passport Number') }}</th>
                                <th scope="col">{{ __('Visa Type') }}</th>
                                <th scope="col">{{ __('Country') }}</th>
                                <th scope="col">{{ __('Unit Price') }}</th>
                                <th scope="col">{{ __('Paid') }}</th>
                                <th scope="col">{{ __('Due') }}</th>
                                <th scope="col">{{ __('Attachment') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                                <th scope="col">{{ __('Action') }}</th> 
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            @foreach ($results as $index => $result)
                                <tr>
                                    <th scope="row">{{ $index + 1 }}</th>
                                    <td>{{ $result->client_name }}</td>
                                    <td>{{ $result->passport_no }}</td>
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
                                    <td>{{ $result->unit_price }}</td>
                                    <td>{{ $result->amount_paid }}</td>
                                    <td>{{ $result->amount_due }}</td>
                                    <td>
                                                @if (!empty($result->attachment) || !empty($result->attachment2) || !empty($result->attachment3))
                                                    <a href="{{ asset(Storage::url($result->attachment)) }}" class="text-body" download>
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                    <a href="{{ asset(Storage::url($result->attachment2)) }}" class="text-body" download>
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                    <a href="{{ asset(Storage::url($result->attachmen3)) }}" class="text-body" download>
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
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
                                            'extra' => '<a target="_blank" href="/agents?visa_type='.$vtype.'&moneyrecipt='.$_GET['agent'].'&client_id='.$result->id.'" class="visa-crud-btn edit" title="'.e(__('Receipt')).'"><i class="ti ti-printer"></i></a>',
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
    function setcountry(){
        var country_id = $('#country_id').val();
        
        $.ajax({
            type:'GET',
            url:'setcountry/'+country_id,

            success: function (data) {
                location.reload();
            },
            error: function(error){
                console.log('error');
            }

        });
        
    }
    
     function setstatus(){
        var status = $('#status').val();
        
        $.ajax({
            type:'GET',
            url:'setstatus/'+status,

            success: function (data) {
                location.reload();
            },
            error: function(error){
                console.log('error');
            }

        });
        
    }
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
