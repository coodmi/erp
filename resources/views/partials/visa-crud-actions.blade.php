@php
    $entityLabel = $entityName ?? __('record');
@endphp
<div class="visa-crud-actions">
    @if(!empty($editUrl))
        <a href="#"
           class="visa-crud-btn edit"
           data-url="{{ $editUrl }}"
           data-ajax-popup="true"
           data-size="{{ $modalSize ?? 'lg' }}"
           data-title="{{ $editTitle ?? __('Edit') }}"
           title="{{ __('Edit') }}">
            <i class="ti ti-pencil"></i>
        </a>
    @endif
    @if(!empty($deleteUrl))
        <form method="POST"
              action="{{ $deleteUrl }}"
              class="d-inline ai-delete-form">
            @csrf
            @method('DELETE')
            <button type="button"
                    class="visa-crud-btn delete ai-delete-btn"
                    data-entity="{{ $entityLabel }}"
                    title="{{ __('Delete') }}">
                <i class="ti ti-trash"></i>
            </button>
        </form>
    @endif
    @if(!empty($extra))
        {!! $extra !!}
    @endif
</div>
