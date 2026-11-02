@extends('layouts.admin.app')

@section('title', translate('Restaurants_Bulk_Export'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mb-2 text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{ dynamicAsset('assets/admin/img/export.png') }}" alt="">
                </div>
                {{ translate('Restaurants_Bulk_Export') }}
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="card mt-2 rest-part">
            <div class="card-body">
                <div class="export-steps">

                    @includeIf('partials._bulk_export_common_instruction')

                    <form class="product-form" action="{{ route('admin.restaurant.bulk-export') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @includeIf('partials._bulk_export_common_filter')
                    </form>
                </div>
            </div>
        </div>
    @endsection

    @push('script_2')
        <script src="{{ dynamicAsset('assets/admin/js/view-pages/common-import-export.js') }}"></script>
    @endpush
