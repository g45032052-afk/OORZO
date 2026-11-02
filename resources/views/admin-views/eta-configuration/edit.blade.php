@extends('layouts.admin.app')

@section('title', translate('messages.Update_ETA_Configuration'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/admin-shared.css') }}?v={{ @filemtime(public_path('assets/admin/css/admin-shared.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/surge-price.css') }}?v={{ @filemtime(public_path('assets/admin/css/surge-price.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/delivery-rule.css') }}?v={{ @filemtime(public_path('assets/admin/css/delivery-rule.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/eta-configuration.css') }}?v={{ @filemtime(public_path('assets/admin/css/eta-configuration.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.Update_ETA_Configuration') }}</h1>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.eta-configuration.update', [$configuration->id]) }}" method="post"
            id="eta-configuration-form">
            @csrf
            @include('admin-views.eta-configuration.partials._form')
        </form>
    </div>

    @include('admin-views.eta-configuration.partials._success-modal')
@endsection

@push('script_2')
    @include('admin-views.eta-configuration.partials._form-scripts')
@endpush
