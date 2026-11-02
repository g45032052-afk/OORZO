@extends('layouts.admin.app')

@section('title', translate('messages.Add_New_Free_Delivery'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/admin-shared.css') }}?v={{ @filemtime(public_path('assets/admin/css/admin-shared.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/surge-price.css') }}?v={{ @filemtime(public_path('assets/admin/css/surge-price.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/delivery-rule.css') }}?v={{ @filemtime(public_path('assets/admin/css/delivery-rule.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/free-delivery.css') }}?v={{ @filemtime(public_path('assets/admin/css/free-delivery.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.Add_New_Free_Delivery') }}</h1>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.free-delivery.store') }}" method="post" id="free-delivery-form">
            @csrf
            @include('admin-views.free-delivery.partials._form')
        </form>
    </div>

    @include('admin-views.free-delivery.partials._success-modal')
    @include('admin-views.free-delivery.partials._conflict-modal')
@endsection

@push('script_2')
    @include('admin-views.free-delivery.partials._form-scripts')
@endpush
