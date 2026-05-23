@extends('layouts.admin')


@php
    $profile = \App\Models\Utility::get_file('uploads/avatar');

    // Check if 'visa_type' parameter is set in the URL
    $vtype = isset($_GET['visa_type']) ? $_GET['visa_type'] : null;
    $aid = isset($_GET['vendor']) ? $_GET['vendor'] : null;
    $results = [];
    $vexcat = [];
    

    // Get data from the database based on the 'visa_type' parameter
    
        try {
            // Establish a database connection
            $connection = DB::connection();



            // Execute a raw SQL query
            //$results = $connection->select("SELECT clients.*, vexcat.country_name FROM clients LEFT JOIN vexcat ON clients.visa_country_id = vexcat.id WHERE clients.vendor_id = $aid");
            $vexcat = $connection->select("SELECT * FROM vexcat");

           // $vexcat = $connection->select("
//    SELECT DISTINCT vexpense.vexcat_id, vexcat.category_name, vexcat.category_description
  //  FROM vexpense
    //LEFT JOIN vexcat ON vexpense.vexcat_id = vexcat.id
//");
            
        } catch (\Exception $e) {
            // Log the error message
        }
    
@endphp

@section('page-title')
    {{ __('Expenses') }}
@endsection

@push('script-page')
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Expense') }}</li>
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
  <form method="post" action="{{ route('categories.store') }}">
  @csrf
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Expense Category</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
                <div class="form-group">
                    <label for="category_name" class="form-label">Category Name</label>
                    <input type="text" name="category_name" class="form-control" placeholder="Enter a Category Name" required>
                </div>
                <div class="form-group">
                    <label for="category_description" class="form-label">Category Name</label>
                    <textarea name="category_description" class="form-control" placeholder="Write description"></textarea>
                </div>
                

                </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <input type="submit" class="btn btn-primary" value="Add Category"></input>
      </div>
    </div>
    </form>
  </div>
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
                        <th scope="col">{{ __('Category') }}</th>
                        <th scope="col">{{ __('Description') }}</th>
                        <th scope="col">{{ __('Action') }}</th>
                        
                        


                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @foreach ($vexcat as $index => $result)
                        <tr>
                            <th scope="row">{{ $index + 1 }}</th>
                            <td>
                            <a href="/expenses?category={{ $result->category_name }}">{{ $result->category_name }}</a>
                        </td>
                        <td>
                                {{ $result->category_description }}
                        </td>
                        <td>
                        <div class="action-btn bg-danger ms-2">
    {!! Form::open(['method' => 'DELETE', 'route' => ['vexcat.destroy', $result->id], 'id' => 'delete-form-'.$result->id, 'class' => 'delete-form']) !!}
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
