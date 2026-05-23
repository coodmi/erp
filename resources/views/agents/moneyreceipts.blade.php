@php
    $settings = Utility::settings();
    $logo=asset(Storage::url('uploads/logo/'));
    $company_logo=Utility::getValByName('company_logo_dark');
    $company_logos=Utility::getValByName('company_logo_light'); 
    $setting = \App\Models\Utility::colorset();
    
    
    $profile = \App\Models\Utility::get_file('uploads/avatar');

    // Check if 'visa_type' parameter is set in the URL
    $vtype = isset($_GET['visa_type']) ? $_GET['visa_type'] : null;
    $aid = isset($_GET['moneyrecipt']) ? $_GET['moneyrecipt'] : null;
    $clid = isset($_GET['client_id']) ? $_GET['client_id'] : null;
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
            
            $results = $connection->select("
                    SELECT clients.*, countries.country_name
                    FROM clients
                    LEFT JOIN countries ON clients.visa_country_id = countries.id
                    WHERE clients.agent_id = $aid
                    AND clients.id = $clid 
                ");
            
            $country_id=Session::get('country_id');
             
            $countries = $connection->select("SELECT * FROM countries");
            $agent_name = $connection->select("SELECT agent_name FROM agents WHERE id = $aid");
             
            $agent_id = $connection->select("SELECT id FROM agents WHERE id = $aid");
            
            
            
            $inv_id=strtoupper(\Str::random(10)); 
            
            if (empty($agent_name)) {
                $agent_name = "Unknown";
                $agent_id = null;
            }
            else {
                $agent_name = $agent_name[0]->agent_name;
                $agent_id = $agent_id[0]->id;
            }
        } catch (\Exception $e) {
            // Log the error message
        }
    }

@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <style>
        body {
        font-family: Arial, sans-serif;
    }
    
    .logo-img {
        width: 80px;
        height: auto;
    }
    
    h5, h3, p {
        margin: 0;
    }
    
    .table-bordered th, .table-bordered td {
        vertical-align: middle;
    }
    
    .table th, .table td {
        padding: 10px;
    }
    
    .container {
        border: 1px solid #dee2e6;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }
    
    .text-end {
        text-align: right;
    }
    
    p {
        margin: 0;
    }
    
    table {
        margin-bottom: 0;
    }
    
    .signature-line {
        display: inline-block;
        margin-left: 10px;
        width: 100px;
        border-bottom: 1px solid black;
    }
    
    .company-details p {
        margin: 0;
        font-size: 14px;
    }
    
    h3, h5 {
        color: #004085;
    }

    </style>
    
</head>
<body> 
    <div class="container-fluid">
        <div class="row p-4" style="background: #044fa1;">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <img src="https://erp.erazehanintl.com/storage/app/public/uploads/logo//logo-dark.png" alt="Eraz-ehan Logo" style="border-radius:6px;width: 130px;height: 60px;">
                    <h1 style="color: white;text-transform: uppercase;font-size: 50px;font-weight: bold;line-height: 60px;margin: 0;">Money Receipt</h1>
                </div>
            </div>
        </div>
        <div class="bodydata" style="height:810px;">
            <div class="row mb-4 p-4">
                <div class="col-md-6">
                    <h5 style="font-size: 22px;font-weight: bold;margin-bottom: 10px;">Bill To</h5>
                    <p style="font-size: 16px;padding-bottom: 6px;"><strong>{{$results[0]->client_name}}</strong></p>
                    <p style="font-size: 16px;padding-bottom: 6px;">PASPORT NO: <b>{{$results[0]->passport_no}}</b></p>
                    <p style="font-size: 16px;padding-bottom: 6px;">ADDRESS : <b>{{$results[0]->address }}</b></p>
                </div>
                <div class="col-md-6 text-end"> 
                    <p style="font-size: 16px;padding-bottom: 6px;">Invoice Number: <strong>{{$inv_id}}</strong></p>
                    <p style="font-size: 16px;padding-bottom: 6px;">Invoice Date: <strong>{{date('d/m/Y')}}</strong></p>
                </div>
            </div>
    
            <div class="row mb-4 p-4">
                <div class="col-12">
                    <h5 style="font-size: 26px;font-weight: bold;margin-bottom: 10px;">Services</h5>
                </div>
                <div class="col-12 mt-4">
                    <table class="table table-bordered text-center" id="datainfotable">
                        <thead class="table style=" color: white; font-weight: bold;"" style="background:#044fa1;background:#044fa1">
                            <tr>
                                <th style=" color: white; font-weight: bold;">SL</th>
                                <th style=" color: white; font-weight: bold;">Visa Type</th>
                                <th style=" color: white; font-weight: bold;">Countries</th> 
                                <th style=" color: white; font-weight: bold;">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $index => $result)
                                <tr>
                                    <td>{{$index+1}}</td>
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
                                    <td><input type="number" onkeyup="calculation()" name="unit_price" id="unit_price" value="{{ $result->unit_price }}" style="border: none;text-align: center;"></td>
                                </tr>
                            @empty
                            
                            @endforelse
                        </tbody>
                    </table> 
                </div>
            </div>
    
            <div class="row p-4">
                <div class="col-md-6"></div>
                <div class="col-md-6 text-end">
                    <h5 class="mb-2">Subtotal: 
                        <strong> 
                            @php
                                $res=0;
                                foreach($results as $result){
                                    $res+=$result->unit_price;
                                }
                            @endphp
                           <input type="number" name="subtotal" value="{{$res}}" id="subtotal" style="border: none;text-align: right;"> 
                        </strong>
                    </h5>
                    <h5>Paid: 
                        <strong> 
                            <input type="number" name="paid"  onkeyup="calculation()"  value="@if(isset($results)) 0 @else {{intval($results[0]->amount_paid)}} @endif" id="paid" style="border: none;text-align: right;">
                        </strong>
                    </h5>
                    <hr>
                    <h5>Due: 
                        <strong> 
                            <input type="number" name="due" value="@if(isset($results)) 0 @else {{intval($results[0]->amount_due)}} @endif" id="due" style="border: none;text-align: right;">
                        </strong>
                    </h5>
                </div>
            </div>
    
            <div class="row p-4 pt-0">
                <div class="col-3"></div>
                <div class="col-6 m-auto pb-4 mb-2">
                    <h6 class="text-center">Money receipts will not be considered valid without the  MD's seal and signature</h6>
                </div>
                <div class="col-3"></div>
                <div class="col-md-4 text-center">
                    <p><span class="signature-line"></span></p>
                    <p>Cashier Signature</p>
                </div>
                <div class="col-md-4 text-center">
                    <p><span class="signature-line"></span></p>
                    <p>Manager Signature</p>
                </div>
                <div class="col-md-4 text-center">
                    <p><span class="signature-line"></span></p>
                    <p>MD Signature</p>
                </div>
            </div>

        </div>
        <div class="row  px-4 py-2 pt-3" style="background: #044fa1;">
            <div class="col-5">
                <div class="company-details">
                    <img src="https://erp.erazehanintl.com/storage/app/public/uploads/logo//logo-dark.png" alt="Eraz-ehan Logo" style="border-radius:6px;width:130px;height: 60px;">
                    <p class="pt-1" style="font-size: 20px;color: white;"><strong>Eraz-ehan INT. LTD.</strong></p>
                    <p class="pt-0" style="font-size: 16px;color: white;">Mohakhali DOHS, House-337, Road: 23, Flat 3 (A)</p> 
                </div>
            </div> 
            <div class="col-7">
                <div class="company-details" style="text-align:right"> 
                    <h4 class="m-0" style="color:white;text-transform:uppercase;">Contact Info</h4>
                    <p class="m-0 pt-2" style="font-size: 16px;color: white;">01877-654064<br> 01611272578</p>
                    <p style="font-size: 16px;color: white;">Email: erazehaninternational@gmail.com</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function calculation() {
            var subtotal = 0; 
            $("#datainfotable tbody tr").each(function (index) {
                subtotal = subtotal + +$(this).find("#unit_price").val(); 
                
            });
            $("#subtotal").val(subtotal); 
            var paid=$("#paid").val(); 
            var due=subtotal-paid;
            $("#due").val(due); 
        }
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
