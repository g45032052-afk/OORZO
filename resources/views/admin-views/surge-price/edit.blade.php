@extends('layouts.admin.app')

@section('title', translate('messages.Edit_Surge_Price'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                    <h1 class="page-header-title">{{ translate('messages.Edit_Surge_Price') }}</h1>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.surge-price.update', $surge->id) }}" method="post" id="surge_form" class="validate-form">
            @csrf
            @include('admin-views.surge-price.partials._form', ['surge' => $surge])
        </form>
    </div>
@endsection

@push('script_2')
    @include('admin-views.surge-price.partials._scripts')
@endpush
