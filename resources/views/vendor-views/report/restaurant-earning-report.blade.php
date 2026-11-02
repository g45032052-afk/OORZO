@extends('layouts.vendor.app')

@section('title', translate('messages.Restaurant_Earning_Report'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header mt-20">
            <div class="d-flex flex-wrap justify-content-between align-items-center g-20">
                <div class="page--title">
                    <h1 class="page-header-title text-capitalize">
                        <span class="page-header-icon">
                            <img src="{{dynamicAsset('assets/admin/img/report.png')}}" class="w--22" alt="">
                        </span>
                        <span>{{translate('messages.Restaurant_Earning_Report')}}</span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        @include('admin-views.report.partials._restaurant_earning_report_content', [
            'summary_url' => route('vendor.report.restaurant-earning-summary'),
            'breakdown_url' => route('vendor.report.restaurant-earning-breakdown'),
            'expense_url' => route('vendor.report.restaurant-expense-breakdown'),
            'trend_url' => route('vendor.report.restaurant-earning-trend'),
            'top_selling_foods_url' => route('vendor.report.top-selling-foods'),
            'reset_url' => route('vendor.report.restaurant-earning-report'),
            'export_url_excel' => route('vendor.report.restaurant-earning-export', ['export_type' => 'excel', request()->getQueryString()]),
            'export_url_csv' => route('vendor.report.restaurant-earning-export', ['export_type' => 'csv', request()->getQueryString()]),
            'transactions_export_url' => route('vendor.report.restaurant-earning-transactions-export'),
            'transactions_url' => route('vendor.report.restaurant-earning-transactions'),
            'show_restaurant_select' => false,
            'show_earning_vs_expense' => true,
            'restaurant_id' => $restaurant_id,
        ])
    </div>
@endsection
