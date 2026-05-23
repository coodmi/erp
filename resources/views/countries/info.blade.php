@extends('layouts.admin')


@php
    $profile = \App\Models\Utility::get_file('uploads/avatar');

    $countryName = isset($_GET['country']) ? $_GET['country'] : null;
    $countryName2 = isset($_GET['country']) ? $_GET['country'] : null;
    $cresults = [];
    $aresults = [];
    $vresults = [];
    $countries = [];
    

    // Get data from the database based on the 'visa_type' parameter
    
        try {
            // Establish a database connection
            $connection = DB::connection();

            // Escape the user input to prevent SQL injection
            $countryName = $connection->getPdo()->quote($countryName);

            // Execute a raw SQL query
            $cresults = $connection->select("SELECT clients.*,
                countries.country_name FROM clients
                LEFT JOIN countries ON clients.visa_country_id = countries.id
                WHERE countries.country_name = $countryName
            ");

            $aresults = $connection->select("SELECT agents.*,
                countries.country_name FROM agents
                LEFT JOIN countries ON agents.visa_country_id = countries.id
                WHERE countries.country_name = $countryName
            ");

            $vresults = $connection->select("SELECT vendors.*,
                countries.country_name FROM vendors
                LEFT JOIN countries ON vendors.visa_country_id = countries.id
                WHERE countries.country_name = $countryName
            ");

            $countries = $connection->select("SELECT * FROM countries");
            //$results = $connection->select("SELECT * FROM clients");
            
        } catch (\Exception $e) {
            //echo "Error: " . $e->getMessage();
        }
    
@endphp

@section('page-title')
@if (isset($_GET['visa_type']))
    @php
        $countryName = $_GET['countryName'];
    @endphp
@endif
    {{ $countryName2 }}
@endsection

@push('script-page')
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Countries') }}</li>
    <li class="breadcrumb-item">
    @if (isset($_GET['visa_type']))
    @php
        $countryName = $_GET['countryName'];
    @endphp
@endif
    {{ $countryName2 }}
</li>

@endsection



@section('content')


    <div class="row mt-4">
        <div class="col-xxl-12">
           
        <div class="card">

        <div class="card-header">
            <h5 class="mt-1 mb-0">{{__('Clients')}}</h5>
        </div>

        <div class="card-body table-border-style">


            <div class="table-responsive">
            <table class="table ">
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
                        <th scope="col">{{ __('Status') }}</th>


                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @foreach ($cresults as $index => $result)
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
                            <td>{{ $result->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        </div>
        </div>
    </div>


    <!-- Agents -->

    <div class="row mt-4">
        <div class="col-xxl-12">
           
        <div class="card">

        <div class="card-header">
            <h5 class="mt-1 mb-0">{{__('Agents')}}</h5>
        </div>

        <div class="card-body table-border-style">


            <div class="table-responsive">
            <table class="table ">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('Agent Name') }}</th>
                        <th scope="col">{{ __('Agent ID') }}</th>
                        <th scope="col">{{ __('Visa Type') }}</th>
                        <th scope="col">{{ __('Country') }}</th>
                        <th scope="col">{{ __('Paid') }}</th>
                        <th scope="col">{{ __('Due') }}</th>


                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @foreach ($aresults as $index => $result)
                        <tr>
                            <th scope="row">{{ $index + 1 }}</th>
                            <td>{{ $result->agent_name }}</td>
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
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        </div>
        </div>
    </div>


    <!-- Vendors -->

    <div class="row mt-4">
        <div class="col-xxl-12">
           
        <div class="card">

        <div class="card-header">
            <h5 class="mt-1 mb-0">{{__('Vendors')}}</h5>
        </div>

        <div class="card-body table-border-style">


            <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('Vendor Name') }}</th>
                        <th scope="col">{{ __('Company Details') }}</th>
                        <th scope="col">{{ __('Vendor ID') }}</th>
                        <th scope="col">{{ __('Visa Type') }}</th>
                        <th scope="col">{{ __('Country') }}</th>
                        <th scope="col">{{ __('Paid') }}</th>
                        <th scope="col">{{ __('Due') }}</th>


                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @foreach ($vresults as $index => $result)
                        <tr>
                            <th scope="row">{{ $index + 1 }}</th>
                            <td>{{ $result->vendor_name }}</td>
                            <td>{{ $result->company_details }}</td>
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
