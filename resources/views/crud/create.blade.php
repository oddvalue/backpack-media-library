@extends(backpack_view('layouts.top_left'))

@php
  $breadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    'Media library' => backpack_url('media-library'),
    'Upload' => false,
  ];
@endphp

@section('header')
  <div class="container-fluid">
    <h2>
      <span class="text-capitalize">Media Library</span>
      <small>Upload media</small>
      <small><a href="{{backpack_url('media-library')}}" class="hidden-print font-sm"><i class="fa fa-angle-double-left"></i> Back to all  <span>Media</span></a></small>
    </h2>
  </div>
@endsection

@section('content')
<main id="app">
    <boz-uploader
        {{ request()->input('folder') ? ':default-folder="'.request()->input('folder').'"' : '' }}
    ></boz-uploader>
    <edit-modal></edit-modal>
</main>
@stop

@section('after_scripts')
  <script src="{{ asset('packages/media-library/js/app.js') }}"></script>
  @stack('crud_list_scripts')
@endsection
