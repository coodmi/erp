@extends('layouts.admin')
@section('page-title')
    {{__('Clients')}}
@endsection

@php
    // Fetch clients and agent names
    //$clients = DB::table('clients')->get();
    $connection = DB::connection();

    $results = $connection->select("
    SELECT clients.*, countries.country_name
    FROM clients
    LEFT JOIN countries ON clients.visa_country_id = countries.id
");
    $agents = DB::table('agents')->pluck('agent_name', 'id');
    $vendors = DB::table('vendors')->pluck('vendor_name', 'id');
@endphp

@push('script-page')
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Clients')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{__('Sl')}}</th>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Passport Number')}}</th>
                                    <th>{{__('Client ID')}}</th>
                                    <th>{{__('Visa Type')}}</th>
                                    <th>{{__('Country')}}</th>
                                    <th>{{__('Unit Price')}}</th>
                                    <th>{{__('Paid')}}</th>
                                    <th>{{__('Due')}}</th>
                                    <th>{{__('Refund')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th>{{__('Attachment')}}</th>
                                    <th>{{__('Agent')}}</th>
                                    <th>{{__('Vendor')}}</th>
                                    <th>{{__('Ticket')}}</th>
                                    <th>{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($results as $index=>$client)
                                    <tr class="font-style">
                                        <td>{{ $index + 1  }}</td>
                                        <td>{{ $client->client_name }}</td>
                                        <td>{{ $client->passport_no }}</td>
                                        <td>{{ $client->unique_code }}</td>
                                        <td>
                                            @if ($client->visa_type == "WV")
                                                Work Permit Visa
                                            @elseif ($client->visa_type == "SV")
                                                Student Visa
                                            @elseif ($client->visa_type == "TV")
                                                Tourist Visa
                                            @elseif ($client->visa_type == "BV")
                                                Business Visa
                                            @else
                                                Other Visa
                                            @endif
                                        </td>
                                        <td>{{ $client->country_name }}</td>
                                        <td>{{ $client->unit_price }}</td>
                                        
                                        <td>{{ $client->amount_paid - $client->refund }}</td>
                                        <td>{{ $client->amount_due }}</td>

                                        <td>{{ $client->refund }}</td>
                                        <td>{{ $client->status }}</td>
                                        <td>
                            @if (!empty($client->attachment) || !empty($client->attachment2) || !empty($client->attachmen3) || !empty($client->attachment4))
                                          
                                        @if (!empty($client->attachment)) 

                                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="Passport" href="{{ asset(Storage::url($client->attachment)) }}" class="text-body" download>
                                                <i class="fas fa-passport"></i>
                                            </a>
                                        
                                        @endif
                                        @if (!empty($client->attachment2)) 
                                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="Photo" href="{{ asset(Storage::url($client->attachment2)) }}" class="text-body" download>
                                                <i class="fas fa-file-image"></i>
                                            </a>
                                        
                                        @endif
                                        @if (!empty($client->attachmen3)) 
                                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="PCC" href="{{ asset(Storage::url($client->attachmen3)) }}" class="text-body" download>
                                                <i class="fas fa-file"></i>
                                            </a>
                                        
                                        @endif
                                        @if (!empty($client->attachment4)) 
                                            <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="Others" href="{{ asset(Storage::url($client->attachment4)) }}" class="text-body" download>
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        
                                        @endif
                                        @else
                                            <i class="fas fa-times"></i>
                                        @endif
                                    </td>
                                        <td>{{ $agents[$client->agent_id] ?? 'N/A' }}</td>
                                        <td>{{ $vendors[$client->vendor_id] ?? 'N/A' }}</td>

                                        <td>
                                            @if ($client->isTicket == 0)
                                            <i class="fas fa-times"></i>
                                            @else
                                            <i class="fas fa-check"></i>

                                            @endif
                                        </td>
                                      
                                        @if(Gate::check('show warehouse') || Gate::check('edit warehouse') || Gate::check('delete warehouse'))
                                            <td class="Action">
                                                @can('edit warehouse')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-url="{{ route('vclients.edit',$client->id) }}" data-ajax-popup="true"  data-size="lg " data-bs-toggle="tooltip" title="{{__('Edit')}}"  data-title="{{__('Edit ')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete warehouse')
                                                    
                                                    
                                                    
                                                    <div class="action-btn bg-danger ms-2">
    {!! Form::open(['method' => 'DELETE', 'route' => ['vclients.destroy', $client->id], 'id' => 'delete-form-'.$client->id]) !!}
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

                                                @endcan
                                            </td>
                                        @endif
                                        
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
