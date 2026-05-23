@extends('layouts.admin')
@section('page-title')
    {{__('Print')}}
@endsection

@php
    $profile = \App\Models\Utility::get_file('uploads/avatar');
    $agents = DB::table('agents')->pluck('agent_name', 'id');
    $invoiceNumber = "INV-0001";
@endphp

@php
    $logo=\App\Models\Utility::get_file('uploads/logo/');
    $connection = DB::connection();



            // Execute a raw SQL query
            //$results = $connection->select("SELECT clients.*, vexcat.country_name FROM clients LEFT JOIN vexcat ON clients.visa_country_id = vexcat.id WHERE clients.vendor_id = $aid");
    $company_name = $connection->select("SELECT value FROM settings where name='company_name'");
    $company_address = $connection->select("SELECT value FROM settings where name='company_address'");
    $company_city = $connection->select("SELECT value FROM settings where name='company_city'");
    $company_country = $connection->select("SELECT value FROM settings where name='company_country'");
    $company_telephone = $connection->select("SELECT value FROM settings where name='company_telephone'");


    // $company_name = $company[0]->company_name;
    // $company_address = $company[0]->company_address;
    // $company_city = $company[0]->company_city;
    // $company_country = $company[0]->company_country;
    // $company_telephone = $company[0]->company_telephone;

    $company_logo=Utility::getValByName('company_logo_dark');
    $company_logos=Utility::getValByName('company_logo_light');
    $setting = \App\Models\Utility::settings();
    $emailTemplate     = \App\Models\EmailTemplate::first();
    $lang= Auth::user()->lang;
    $show_dashboard = \App\Models\User::show_dashboard();
@endphp


@push('script-page')

    <script>
        $(function() {
  $('.selectpicker').selectpicker();
});
    </script>

    <script>
        function formatCurrency(amount) {
        // Create a new Intl.NumberFormat object with currency formatting options
        var formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'BDT',
            minimumFractionDigits: 0, // Set minimumFractionDigits to 0 to remove decimal points
        maximumFractionDigits: 0
        });
        // Format the amount as currency and return the formatted string
        return formatter.format(amount);
    }
    </script>

    <script>
        $('#select-agent').on('change', function() {
            var agent_id = $(this).val();
            var agent_name = $(this).find(':selected').text();
            $('#agent-name-value').text(agent_name);
            $('#agent-name-value2').text(agent_name);

});
    </script>

    <script>
        $('#date-pick').on('change', function() {
            var date = $(this).val();
            $('#date-value').text(date);
            console.log(date);
});
</script>

<script>
    $('#address1').on('change', function() {
        var address1 = $(this).val();
        $('#address1-value').text(address1);
        console.log(address1);
});
</script>

<script>
    $('#address2').on('change', function() {
        var address2 = $(this).val();
        $('#address2-value').text(address2);
        console.log(address2);
});
</script>

<script>
    $('#phone').on('change', function() {
        var phone = $(this).val();
        $('#phone-value').text(phone);
        console.log(phone);
});
</script>

<script>
    $('#paid-for').on('change', function() {
        var paidFor = $(this).val();
        $('#paid-for-td').text(paidFor);
        console.log(paidFor);
});
</script>

<script>
    $('#unit-price').on('change', function() {
        var unitPrice = $(this).val();
        $('#unit-price-td').text(formatCurrency(unitPrice));
        console.log(unitPrice);
});

</script>

<script>
    $('#total-unit').on('change', function() {
        var totalUnit = $(this).val();
        $('#total-unit-td').text(totalUnit);
        console.log(totalUnit);
});

</script>

<script>
    $('#advanced').on('change', function() {
        var advanced = $(this).val();
        $('#advanced-td').text(formatCurrency(advanced));
        $('#total-paid').text(formatCurrency(advanced));
        console.log(advanced);
});

</script>



<script>
    $('#payment-mode').on('change', function() {
        var paymentMode = $(this).val();
        $('td:nth-child(8)').text(paymentMode);
        console.log(paymentMode);
});

</script>

<!-- calculate total: unitprice*totalunit -->

<script>
    $('#unit-price').on('change', function() {
        var unitPrice = $(this).val();
        $('#total-unit').on('change', function() {
            var totalUnit = $(this).val();
            var total = unitPrice * totalUnit;
            $('#total-td').text(formatCurrency(total));
            console.log(total);
        });
});

</script>

<!-- calculate due amount: total - advanced -->

<script>
    // Calculate due amount
    function calculateDue() {
        var total = parseFloat($('#total-unit').val()) * parseFloat($('#unit-price').val());
        var advanced = parseFloat($('#advanced').val());
        var due = total - advanced;
        $('#due').val(due);
        $('#due-td').text(formatCurrency(due));
        $('#total-due').text(formatCurrency(due));
        console.log(due);
    }

    $(document).ready(function() {
        // Call calculateDue function whenever any of the inputs change
        $('#total-unit, #unit-price, #advanced').on('change', calculateDue);
    });
</script>

<!-- enable refund -->

<script>
    $('#hasRefund').on('change', function() {
        var hasRefund = $(this).is(':checked');
        $('#refund').prop('disabled', !hasRefund);
        console.log(hasRefund);
});

</script>

<!-- calculate refund -->

<script>
    $('#refund').on('change', function() {
        var refund = $(this).val();
        $('#total-refund').text(formatCurrency(refund));
        console.log(refund);
});

</script>

<script src=
"https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js">
    </script>

<script>
        var qrcode = new QRCode("qrcode",{
            text: "{{ $invoiceNumber }}",
            width: 130,
            height: 130
        });
    </script>

<script>
    function printDiv(divId) {
     var printContents = document.getElementById(divId).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}
</script>


@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Print')}}</li>
@endsection

@section('content')
<style>
    td {
  white-space: normal !important; 
  word-wrap: break-word;  
}
table {
  table-layout: fixed;
}
@media print {
  body {
    visibility: hidden;
  }
  #this-pdf * {
    visibility: visible;
    position: absolute;
    left: 0;
    top: 0;
  }
}
</style>
    <div class="row" style="overflow-x:scroll">
        <div class="col-xl-12">
            <div class="card p-3">
                <div class="row p-3">
                    <div class="col-md-3">
                        <div class="card-header">
                            <h4 class="h4">{{__(' Print')}}</h4>
                        </div>
                <div class="card-body table-border-style">
                    <div class="form flex">

                        <select class="form-control selectpicker mb-3" id="select-agent" data-live-search="true">
                            <option value="0">Select Agent</option>
                            @foreach($agents as $key => $agent)
                            <option value="{{$key}}">{{$agent}}</option>
                            @endforeach
                        </select>
                        <input type="date" name="date-pick" id="date-pick" class="form-control mb-3">
                        <input type="text" name="phone" id="phone" class="form-control mb-3" placeholder="Phone Number">
                        <input type="text" name="address1" id="address1" class="form-control mb-3" placeholder="Address Line 1">
                        <input type="text" name="address2" id="address2" class="form-control mb-3" placeholder="Address Line 2">
                    </div>
                    
                    <div class="mb-3 mt-5">
                        <h5 class="mb-3 mt-3">Ledger Info</h5>
                        <div class="form-group">
                            <select class="form-control mb-3" id="paid-for" data-live-search="true">
                                <option value="0">Select Visa Type</option>
                                <option value="Work Permit">Work Permit Visa</option>
                                <option value="Tourist">Tourist Visa</option>
                                <option value="Student">Student Visa</option>
                                <option value="Business">Business Visa</option>
                                <option value="Other">Other Visa</option>
                            </select>
                            <input type="text" name="unit-price" id="unit-price" class="form-control mb-3" placeholder="Unit Price">
                            <input type="text" name="total-unit" id="total-unit" class="form-control mb-3" placeholder="Total Unit">
                            <input type="text" name="advanced" id="advanced" class="form-control mb-3" placeholder="Paid in Advance">
                            <input type="text" name="due" id="due" class="form-control mb-3" placeholder="Due Amount" readonly>
                            <!-- <input type="text" name="payment-mode" id="payment-mode" class="form-control mb-3" placeholder="Payment Mode"> -->
                            <select class="form-control mb-3" id="payment-mode" data-live-search="true">
                                <option value="0">Select Payment Mode</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank">Bank</option>
                                <option value="Bkash">Bkash</option>
                                <option value="Rocket">Rocket</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Card">Card</option>
                            </select>

                            <input type="checkbox" name="hasRefund" id="hasRefund">
                            <label for="hasRefund">The agent claimed a refund</label>
                            <input type="text" name="refund" id="refund" class="form-control mb-3" placeholder="Refund" disabled>
                            <input type="button" class="btn btn-primary" onclick="printDiv('this-pdf')" value="Print" />
                        </div>
                    </div>
                </div>
</div>
<div class="col-md-9">





<div id="this-pdf" class="card card-body" style="padding:90px 40px 90px 40px; height:1100px; width:780px">

<div class="header">
    <img src="{{ $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png') }}"
                         alt="{{ config('app.name', 'Alphainno ERP') }}" class="mb-5" width="180px">
    <div class="row">
        <div class="col-md-8">
            <h4>
            <b>
                @foreach ($company_name as $name) 
                    {{
                        $name->value;
                    }}
                
                @endforeach     
            </b>
            </h4>
            <h4>
                @foreach ($company_address as $address) 
                    {{
                        $address->value;
                    }}
                
                @endforeach
            </h4>
                <h4>
                @foreach ($company_city as $city) 
                    {{
                        $city->value;
                    }}
                
                @endforeach

                @foreach ($company_country as $country) 
                    {{
                        $country->value;
                    }}

                @endforeach
                </h4>
                <h4>
                Phone: 
                @foreach ($company_telephone as $telephone) 
                    {{
                        $telephone->value;
                    }}
                
                @endforeach

            </h4>
        </div>
        <div class="col-md-4">
                <h4>
                Name: <b id="agent-name-value" style="border-bottom: 1px dotted #4c4c4c; padding-bottom:2px">Agent Name</b>
                </h4>
                <h4>
                Date: <b id="date-value" style="border-bottom: 1px dotted #4c4c4c; padding-bottom:2px">YYYY-MM-DD</b>
                </h4>
                <h4>
                Invoice No: <b style="border-bottom: 1px dotted #4c4c4c; padding-bottom:2px">INV-0001</b>
                </h4>
                <div id="qrcode" ></div>
        </div>
        
    </div>
    <div class="row">
        <div class="col-md-9">
    <h4><b>Bill To:</b></h4>
    <h4>
    <span id="agent-name-value2">Agent Name</span>
            </h4>
                <h4>
                <span id="phone-value">Phone Number</span>
                </h4>
                <h4>
                <span id="address1-value">Address</span>
                </h4>
                <h4>
                <span id="address2-value"></span>

            </h4>
            </div>
            </div>


            <div class="mt-5" style="font-size: 80%">
                <table class="table table-bordered" width="100">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Visa Type</th>
                            <th scope="col">Unit Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Total</th>
                            <th scope="col">Advanced</th>
                            <th scope="col">Due</th>
                            <th scope="col">Paid in</th>


                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        <th scope="col">1</th>
                            <td id="paid-for-td">Example</td>
                            <td style="text-align:right" id="unit-price-td">1000</td>
                            <td style="text-align:right" id="total-unit-td">3</td>
                            <td style="text-align:right" id="total-td">3000</td>
                            <td style="text-align:right" id="advanced-td">500</td>
                            <td style="text-align:right" id="due-td">2500</td>
                            <td style="text-align:right">Cash</td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>

            <div class="row mt-5">
                <div class="col-md-7">
                    &nbsp;
                </div>
                <div class="col-md-5" >
                    <table class="table table-bordered">
                        
                        <tr><td style="text-align:left;margin-right:1px">Total paid</td><td align="right" style="text-align:right"> <b id="total-paid">000</b> </td></tr>
                        <tr><td style="text-align:left;margin-right:1px">Total due</td><td align="right" style="text-align:right"> <b id="total-due">000</b> </td></tr>
                        <tr><td style="text-align:left;margin-right:1px">Refund</td><td align="right" style="text-align:right"> <b id="total-refund">000</b> </td></tr>
                    </table>
                    
                </div>
            </div>

            <div style="position:relative; margin:140px 40px 0px 40px;display:flex;justify-content:space-between;">
                <!-- margin:140px 40px 0px 40px; -->
                <div style="position:absolute;bottom:0;">
                    <div>
                        <h4 style="border-top: 1px dotted #bbb; padding: 10px 30px 0px 30px">Signature</h4>
                    </div>
                </div>
                <div style="position:absolute;bottom:0;right:0;">
                    <div>
                        <h4 style="border-top: 1px dotted #bbb; padding: 10px 30px 0px 30px">Signature</h4>
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
    

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css" rel="stylesheet" />

@endsection