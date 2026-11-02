@extends('layouts.admin.app')

@section('title', translate('messages.Delivery_Rule_Setup'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/surge-price.css') }}?v={{ @filemtime(public_path('assets/admin/css/surge-price.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/delivery-rule.css') }}?v={{ @filemtime(public_path('assets/admin/css/delivery-rule.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.Delivery_Rule_Setup') }}</h1>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.delivery-rule.store') }}" method="post" id="delivery-rule-form">
            @csrf
            @include('admin-views.delivery-rule.partials._form', ['rule' => null])
        </form>
    </div>

    @include('admin-views.delivery-rule.partials._success-modal')
@endsection

@push('script_2')
    @include('admin-views.delivery-rule.partials._form-scripts')
@endpush
