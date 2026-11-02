@extends('layouts.admin.app')

@section('title', translate('messages.Delivery_Man_Earning_Report'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header pb-0">
            <div>
                <h1 class="page-header-title text-capitalize">
                    {{translate('messages.Delivery_Man_Earning_Report') }}
                </h1>
                <p>
                    {{translate('messages.Comprehensive_financial_overview_and_analytics')}}
                </p>
            </div>
        </div>
        <!-- End Page Header -->

        @include('admin-views.report.partials._deliveryman_earning_report_content', [
            'summary_url' => route('admin.report.deliveryman-earning-summary'),
            'breakdown_url' => route('admin.report.deliveryman-earning-breakdown'),
            'expense_url' => route('admin.report.deliveryman-expense-breakdown'),
            'trend_url' => route('admin.report.deliveryman-earning-trend'),
            'reset_url' => route('admin.report.deliveryman-earning-report'),
            'export_url_excel' => route('admin.report.admin-deliveryman-earning-export', array_merge(request()->query(), ['export_type' => 'excel'])),
            'export_url_csv' => route('admin.report.admin-deliveryman-earning-export', array_merge(request()->query(), ['export_type' => 'csv'])),
            'delivery_men' => $delivery_men,
            'delivery_man_id' => $delivery_man_id,
        ])
    </div>
@endsection
