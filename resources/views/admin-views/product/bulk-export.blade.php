@extends('layouts.admin.app')

@section('title', translate('Food_Bulk_Export'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">

        <div class="page-header">
            <h1 class="page-header-title text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{ dynamicAsset('assets/admin/img/export.png') }}" alt="">
                </div>
                {{ translate('Export_Foods') }}
            </h1>
        </div>

        <div class="card mt-2 rest-part">
            <div class="card-body">
                <div class="export-steps">
                    @includeIf('partials._bulk_export_common_instruction')
                    <form class="product-form" action="{{ route('admin.food.bulk-export') }}" method="POST">
                        @csrf
                        @includeIf('partials._bulk_export_common_filter')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script_2')
    <script src="{{ dynamicAsset('assets/admin/js/view-pages/common-import-export.js') }}"></script>
@endpush
