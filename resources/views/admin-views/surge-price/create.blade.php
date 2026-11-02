@extends('layouts.admin.app')

@section('title', translate('messages.Create_New_Surge_Price'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- filemtime busts the cache on every edit; style.css has no version query
         and would otherwise be served stale after a deploy. --}}
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/admin-shared.css') }}?v={{ @filemtime(public_path('assets/admin/css/admin-shared.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/surge-price.css') }}?v={{ @filemtime(public_path('assets/admin/css/surge-price.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.Create_New_Surge_Price') }}</h1>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.surge-price.store') }}" method="post" id="surge_form" class="validate-form">
            @csrf
            @include('admin-views.surge-price.partials._form', ['surge' => null])
        </form>
    </div>
@endsection

@push('script_2')
    @include('admin-views.surge-price.partials._scripts')
@endpush
