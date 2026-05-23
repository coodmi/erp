{{ Form::model($vclient, array('route' => array('vclients.update', $vclient->id), 'method' => 'PUT' , 'enctype' => 'multipart/form-data')) }}

@php
    // Fetch clients and agents
    $clients = \App\Models\VClient::all();
    $agents = \App\Models\Agent::pluck('agent_name', 'id');
    $vendors = \App\Models\Vendor::pluck('vendor_name', 'id');
    $countries = \App\Models\Country::pluck('country_name', 'id');
@endphp

<div class="modal-body">
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
            {{ Form::label('client_name', __('Name'),['class'=>'form-label']) }}
            {{ Form::text('client_name', null, array('class' => 'form-control','required'=>'required')) }}
            @error('name')
            <small class="invalid-name" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </small>
            @enderror
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('passport_no', __('Passport Number'),['class'=>'form-label']) }}
            {{ Form::text('passport_no', null, array('class' => 'form-control','required'=>'required')) }}
        </div>


        <div class="form-group col-md-6">
            {{Form::label('unit_price',__('Unit Price'),array('class'=>'form-label')) }}
            {{Form::number('unit_price',null,array('class'=>'form-control', 'step'=>'any', 'id' => 'unit_price'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('refund',__('Refund'),array('class'=>'form-label')) }}
            {{Form::number('refund',null,array('class'=>'form-control', 'step'=>'any', 'id' => 'refund', 'max' => $vclient->amount_paid, 'min' => 0))}}
            <div class="alert alert-info mt-2">
                <p>{{__('Amount Paid')}}: <b>{{ $vclient->amount_paid }}</b></p>
            </div>
        </div>
        


        <div class="form-group col-md-4">
            {{Form::label('amount_paid_new',__('Paid'),array('class'=>'form-label')) }}
            {{Form::number('amount_paid_new',null,array('class'=>'form-control', 'step'=>'any', 'id' => 'amount_paid_new'))}}
            {{Form::hidden('original_paid', $vclient->amount_paid, array('id' => 'original_paid'))}}
        </div>
        <div class="form-group col-md-4">
            {{Form::label('amount_due',__('Due'),array('class'=>'form-label')) }}
            {{Form::hidden('original_due', $vclient->amount_due)}}
            {{Form::number('amount_due',null,array('class'=>'form-control', 'step'=>'any', 'id' => 'amount_due'))}}
        </div>
        <div class="form-group col-md-4">
        <div class="form-group">
        <label for="isTicket" class="form-label">{{ __('Ticket') }}</label>
        <select name="isTicket" class="form-control">
    <option value="{{$vclient->isTicket}}">{{$vclient->isTicket == 1 ? __('Yes') : __('No')}}</option>
    <option value="{{ $vclient->isTicket == 1 ? 0 : 1 }}">
        {{ $vclient->isTicket == 1 ? __('No') : __('Yes') }}
    </option>
</select>

</div>
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
        <label for="agent_id" class="form-label">{{ __('Country') }}</label>
        <select name="visa_country_id" class="form-control" > 
            @foreach($countries as $countryId => $countryName)
                <option value="{{ $countryId }}" {{ $vclient->visa_country_id == $countryId ? 'selected' : '' }}>{{ $countryName }}</option>
            @endforeach
            <option value="">{{ __('N/A') }}</option>
        </select>
    </div>
</div>

        <div class="form-group col-md-3">
        <div class="form-group">
        <label for="visa_type" class="form-label">{{ __('Visa Type') }}</label>
        <select name="visa_type" class="form-control" required>
            <option value="{{$vclient->visa_type}}" selected disabled>{{ $vclient->visa_type == "SV" ?
            __('Student Visa') : ($vclient->visa_type == "TV" ? __('Tourist Visa') : ($vclient->visa_type == "WV" ? __('Work Permit Visa') : ($vclient->visa_type == "BV" ? __('Business Visa') : ($vclient->visa_type == "OV" ? __('Others') : __('Select Visa Type')))))
             }}</option>
            <option value="WV">{{ __('Work Permit Visa') }}</option>
            <option value="BV">{{ __('Business Visa') }}</option>
            <option value="SV">{{ __('Student Visa') }}</option>
            <option value="TV">{{ __('Tourist Visa') }}</option>
            <option value="OV">{{ __('Others') }}</option>
        </select>
    </div>
</div>
        <div class="form-group col-md-3">
        <div class="form-group">
        <label for="status" class="form-label">{{ __('Visa Status') }}</label>
        <select name="status" class="form-control" required>
            <option value="submitted" @if($vclient->status == "submitted") selected @endif>{{ __('Submitted') }}</option>
            <option value="Work Permit Received" @if($vclient->status == "Work Permit Received") selected @endif>{{ __('Work Permit Received') }}</option>
            <option value="Applied For Visa" @if($vclient->status == "Applied For Visa") selected @endif>{{ __('Applied For Visa') }}</option>
            <option value="Visa Received" @if($vclient->status == "Visa Received") selected @endif>{{ __('Visa Received') }}</option>
            <option value="Completed" @if($vclient->status == "Completed") selected @endif>{{ __('Completed') }}</option>
            <option value="File Received" @if($vclient->status == "File Received") selected @endif>{{ __('File Received') }}</option>
            <option value="Cancelled" @if($vclient->status == "Cancelled") selected @endif>{{ __('Cancelled') }}</option>
        </select>
</div>




    </div>

    <div class="form-group col-md-3">
    <div class="form-group">
        <label for="agent_id" class="form-label">{{ __('Agent') }}</label>
        <select name="agent_id" class="form-control" >
            <option value="">{{ __('Select Agent') }}</option>
            @foreach($agents as $agentId => $agentName)
                <option value="{{ $agentId }}" {{ $vclient->agent_id == $agentId ? 'selected' : '' }}>{{ $agentName }}</option>
            @endforeach
            <option value="">{{ __('N/A') }}</option>
        </select>
    </div>
</div>
    <div class="form-group col-md-3">
    <div class="form-group">
        <label for="vendor_id" class="form-label">{{ __('Vendor') }}</label>
        <select name="vendor_id" class="form-control" >
            <option value="">{{ __('Select Vendor') }}</option>
            @foreach($vendors as $vendorId => $vendorName)
    <option value="{{ $vendorId }}" {{ $vclient->vendor_id == $vendorId ? 'selected' : '' }}>{{ $vendorName }}</option>
@endforeach

            <option value="">{{ __('N/A') }}</option>
        </select>
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
