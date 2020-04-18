<!-- browse server input -->

<div @include('crud::inc.field_wrapper_attributes')>

    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
    <div class="controls">
        <media-modal
            {{-- id="{{$id}}" --}}
            name="{{ $field->calculateName() }}"
            :is-many-relation="{{ $field['is_many'] ? 'true' : 'false' }}"
            :data='{{ json_encode(old(square_brackets_to_dots($field->calculateName())) ?? $field->getValue() ?? $field['default'] ?? []) }}'
        ></media-modal>

    </div>

</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    <edit-modal></edit-modal>

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    @endpush

    @push('crud_fields_scripts')
        <script src="{{ asset('packages/media-library/js/app.js') }}"></script>
    @endpush

@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
