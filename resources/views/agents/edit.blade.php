{{ Form::model($agent, array('route' => array('agents.update', $agent->id), 'method' => 'PUT' , 'enctype' => 'multipart/form-data')) }}

<div class="modal-body">
@php
    // Fetch clients and agents
    $clients = \App\Models\VClient::all();
    $agents = \App\Models\Agent::pluck('agent_name', 'id');
    $vendors = \App\Models\Vendor::pluck('vendor_name', 'id');
    $countries = \App\Models\Country::pluck('country_name', 'id');
    $connection = DB::connection();
    $results = $connection->select("
                SELECT agents.*, countries.country_name,
                SUM(clients.amount_paid) AS total_amount_paid,
                SUM(clients.amount_due) AS total_amount_due,
                SUM(clients.refund) AS total_refund,
                AVG(clients.unit_price) AS total_unit_price
                FROM agents
                LEFT JOIN countries ON agents.visa_country_id = countries.id
                LEFT JOIN clients ON clients.agent_id = agents.id
                where agents.id = {$agent->id}
                
            ");
@endphp
    {{-- start for ai module--}}
    @php
        $settings = \App\Models\Utility::settings();
    @endphp
    @if($settings['ai_chatgpt_enable'] == 'on')
    <div class="text-end">
        <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['warehouse']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('agent_name', __('Name'),['class'=>'form-label']) }}
            {{ Form::text('agent_name', null, array('class' => 'form-control','required'=>'required')) }}
            @error('name')
            <small class="invalid-name" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </small>
            @enderror
        </div>
        <div class="form-group col-md-6">
    <div class="form-group">
        <label for="agent_id" class="form-label">{{ __('Country') }}</label>
        <select name="visa_country_id" class="form-control" >
            <option value="">{{ __('Select Country') }}</option>
            @foreach($countries as $countryId => $countryName)
                <option value="{{ $countryId }}" {{ $agent->visa_country_id == $countryId ? 'selected' : '' }}>{{ $countryName }}</option>
            @endforeach
            <option value="">{{ __('N/A') }}</option>
        </select>
    </div>
</div>
        
<div class="row">
        
        @foreach($results as $result) 
        <div class="form-group col-md-6">
            {{Form::label('total_amount_paid',__('Total Amount Paid'),array('class'=>'form-label')) }}
            {{Form::number('total_amount_paid', $result->total_amount_paid, array('class'=>'form-control', 'step'=>'any', 'id' => 'total_amount_paid', 'readonly'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('total_amount_due',__('Total Amount Due'),array('class'=>'form-label')) }}
            {{Form::number('total_amount_due', $result->total_amount_due, array('class'=>'form-control', 'step'=>'any', 'id' => 'total_amount_due', 'readonly'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('total_refund',__('Total Refund'),array('class'=>'form-label')) }}
            {{Form::number('total_refund', $result->total_refund, array('class'=>'form-control', 'step'=>'any', 'id' => 'total_refund', 'readonly'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('total_unit_price',__('Total Unit Price'),array('class'=>'form-label')) }}
            {{Form::number('total_unit_price', $result->total_unit_price, array('class'=>'form-control', 'step'=>'any', 'id' => 'total_unit_price', 'readonly'))}}
        </div>
        
        @endforeach
    </div>
        

        <div class="form-group col-md-12">
    {{Form::label('attachment',__('Passport'),array('class'=>'form-label')) }}
    {{Form::file('attachment', array('class'=>'form-control'))}}
</div>
        <div class="form-group col-md-12">
    {{Form::label('attachment2',__('Photo'),array('class'=>'form-label')) }}
    {{Form::file('attachment2', array('class'=>'form-control'))}}
</div>
        <div class="form-group col-md-12">
    {{Form::label('attachmen3',__('PCC'),array('class'=>'form-label')) }}
    {{Form::file('attachmen3', array('class'=>'form-control'))}}
</div>
        <div class="form-group col-md-12">
    {{Form::label('attachment4',__('Others'),array('class'=>'form-label')) }}
    {{Form::file('attachment4', array('class'=>'form-control'))}}
</div>
        <div class="form-group col-md-12">
        <div class="form-group">
        <label for="visa_type" class="form-label">{{ __('Visa Type') }}</label>
        <select name="visa_type" class="form-control" required>
            <option value="WV">{{ __('Work Permit Visa') }}</option>
            <option value="BV">{{ __('Business Visa') }}</option>
            <option value="SV">{{ __('Student Visa') }}</option>
            <option value="TV">{{ __('Tourist Visa') }}</option>
            <option value="OV">{{ __('Others') }}</option>
        </select>
    </div>
</div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Edit')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
    // Get the original due amount
    var originalDue = parseFloat($('#amount_due').val());
    var originalPaid = parseFloat($('#original_paid').val());
    console.log(originalPaid);
    

    // Function to toggle the readonly attribute based on the due amount
    function toggleDueInputEditable(due) {
        if (due === 0) {
            $('#amount_due').prop('readonly', false); // Make the input editable
        } else {
            $('#amount_due').prop('readonly', false); // Make the input readonly
        }
    }

    // Initial call to toggle the due input based on the original due amount
    toggleDueInputEditable(originalDue);

    // Calculate due amount when paid amount changes
    $('#amount_paid_new').on('input', function() {
        var amountPaid = parseFloat($(this).val());
        var unitPrice = parseFloat($('#unit_price').val());

        if (!isNaN(unitPrice) && !isNaN(amountPaid)) {
            // Subtract the paid amount from the original due amount only once
            var newDue = unitPrice - amountPaid - originalPaid;
            // Ensure the new due amount is not negative
            newDue = Math.max(0, newDue);
            $('#amount_due').val(newDue.toFixed(2));

            // Toggle the readonly attribute based on the new due amount
            toggleDueInputEditable(newDue);
        }

        // if (!isNaN(amountPaid)) {
        //     // Subtract the paid amount from the original due amount only once
        //     var newDue = originalDue - amountPaid;
        //     // Ensure the new due amount is not negative
        //     newDue = Math.max(0, newDue);
        //     $('#amount_due').val(newDue.toFixed(2));

        //     // Toggle the readonly attribute based on the new due amount
        //     toggleDueInputEditable(newDue);
        // }
    });
});

</script>
