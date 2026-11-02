@extends('layouts.admin.app')

@section('title', translate('messages.Restaurant_Earning_Report'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header pb-0">
            <div>
                <h1 class="page-header-title text-capitalize">
                    {{translate('messages.Restaurant_Earning_Report') }}
                </h1>
                <p>
                    {{translate('messages.Comprehensive_financial_overview_and_analytics')}}
                </p>
            </div>
        </div>
        <!-- End Page Header -->

        @include('admin-views.report.partials._restaurant_earning_report_content', [
            'summary_url' => route('admin.report.restaurant-earning-summary'),
            'breakdown_url' => route('admin.report.restaurant-earning-breakdown'),
            'expense_url' => route('admin.report.restaurant-expense-breakdown'),
            'trend_url' => route('admin.report.restaurant-earning-trend'),
            'reset_url' => route('admin.report.restaurant-earning-report'),
            'export_url_excel' => route('admin.report.restaurant-earning-export', array_merge(request()->query(), ['export_type' => 'excel'])),
            'export_url_csv' => route('admin.report.restaurant-earning-export', array_merge(request()->query(), ['export_type' => 'csv'])),
            'transactions_export_url' => route('admin.report.restaurant-earning-export'),
            'transactions_url' => route('admin.report.restaurant-earning-transactions'),
            'show_restaurant_select' => true,
            'restaurants' => $restaurants,
            'restaurant_id' => $restaurant_id,
        ])
    </div>
@endsection
