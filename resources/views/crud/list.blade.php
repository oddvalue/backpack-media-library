@extends(backpack_view('layouts.top_left'))

@php
  $breadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    'Media library' => false,
  ];
@endphp

@section('header')
  <div class="container-fluid">
    <h2>
      <span class="text-capitalize">Media Library</span>
    </h2>
  </div>
@endsection

@section('content')
        {{-- Backpack List Filters --}}
        {{-- @if ($crud->filtersEnabled())
          @include('crud::inc.filters_navbar')
        @endif --}}

        <div class="overflow-hidden mt-2" id="app">

            <boz-media
                :can-create="true"
                :can-edit="true"
                :can-delete="true"
            ></boz-media>
            <edit-modal></edit-modal>

        </div><!-- /.box-body -->
@endsection

@section('after_styles')
@endsection

@section('after_scripts')
  <script src="{{ asset('packages/media-library/js/app.js') }}"></script>
  @stack('crud_list_scripts')
@endsection
