{{ Form::model($country, ['route' => ['countries.update', $country->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="mb-3">
        <label class="form-label fw-bold text-uppercase" style="font-size:.74rem;letter-spacing:.07em;color:#334155;">
            Country Name <span class="text-danger">*</span>
        </label>
        {{ Form::text('country_name', null, ['class' => 'form-control', 'placeholder' => 'Enter country name', 'required' => 'required', 'style' => 'min-height:46px;border-radius:13px;border:1.5px solid #e2e8f0;background:#f8fafc;']) }}
    </div>
</div>
<div class="modal-footer" style="border:0;padding:16px 24px 22px;">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
            style="border-radius:13px;font-weight:800;min-height:44px;background:#f1f5f9;border-color:#e2e8f0;color:#475569;">
        Cancel
    </button>
    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"
            style="border-radius:13px;font-weight:800;min-height:44px;background:linear-gradient(135deg,#38bdf8,#0284c7);border:0;box-shadow:0 12px 22px rgba(8,145,178,.22);">
        <i class="ti ti-check"></i> Save Changes
    </button>
</div>
{{ Form::close() }}
