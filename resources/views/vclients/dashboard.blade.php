@extends('layouts.admin')
@section('page-title')
    {{__('Dashboard')}}
@endsection

@php

    $profile = \App\Models\Utility::get_file('uploads/avatar');

    // Check if 'visa_type' parameter is set in the URL
   
    $results = [];
    

    // Get data from the database based on the 'visa_type' parameter

        try {
            // Establish a database connection
            $connection = DB::connection();

            // Escape the user input to prevent SQL injection
        

            // Execute a raw SQL query
            $results = $connection->select("SELECT * FROM clients");
            
        } catch (\Exception $e) {
            // Log the error message
        }
    
@endphp

@php
    $totalAmountPaid = 0;
    $totalAmountDue = 0;
    foreach ($results as $result) {
        $totalAmountPaid += $result->amount_paid;
    }
    foreach ($results as $result) {
        $totalAmountDue += $result->amount_due;
    }
$totalAmountPaidf = '$' . number_format($totalAmountPaid, 2);
$totalAmountDuef = '$' . number_format($totalAmountDue, 2);

@endphp

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Vendors')}}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xxl-7">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-lg-3 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-secondary">
                                                <i class="ti ti-users"></i>
                                            </div>
                                            <p class="text-muted text-sm mt-4 mb-2">{{__('Total')}}</p>
                                            <h6 class="mb-3">{{__('Clients')}}</h6>
                                            <h3 class="mb-0">{{count($results)}}</h3>

                                            </h3>
                                        </div>
                                    </div>
                                </div>
                               
                                <div class="col-lg-3 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-success">
                                                <i class="ti ti-report-money"></i>
                                            </div>
                                            <p class="text-muted text-sm mt-4 mb-2">{{__('Total')}}</p>
                                            <h6 class="mb-3">{{__('Paid')}}</h6>
                                            <h3 class="mb-0">{{$totalAmountPaidf}} </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-danger">
                                                <i class="ti ti-report-money"></i>
                                            </div>
                                            <p class="text-muted text-sm mt-4 mb-2">{{__('Total')}}</p>
                                            <h6 class="mb-3">{{__('Due')}}</h6>
                                            <h3 class="mb-0">{{$totalAmountDuef}} </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div class="card">
                            <div class="card-body table-border-style">


<div class="table-responsive">
<table class="table">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">{{ __('Client Name') }}</th>
            <th scope="col">{{ __('Passport Number') }}</th>
            <th scope="col">{{ __('Visa Type') }}</th>
            <th scope="col">{{ __('Paid') }}</th>
            <th scope="col">{{ __('Due') }}</th>
            <th scope="col">{{ __('Attachment') }}</th>


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





            </div>
        </div>
    </div>
@endsection
