@extends('layouts.admin.app')

@section('title', translate('Wallet'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@section('customerDetails')
    active
@endsection
@section('content')

<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <h1 class="page-header-title gap-1 flex-wrap">
                    {{ translate('messages.Customer Details') }} <span class="gray-dark">#{{ $customer->id }}</span>
                </h1>
            </div>
        </div>
    </div>
    @include('admin-views.customer.partials._user_details_urls')
    <div class="card mb-10px">
        <div class="card-body p-10px">
            <div class="row g-1">
                <div class="col-sm-6 col-md-4">
                    <a class="order--card justify-content-start gap-3 h-100 card-bg1" href="javascript:void(0)">
                        <div class="d-flex align-items-center gap-3">
                            <div class="w-45px h-45px rounded-circle d-center bg-white">
                                <img width="20" height="20" src="{{dynamicAsset('assets/admin/img/blance-time.png')}}" alt="img" class="object--contain">
                            </div>
                            <div>
                                <h3 class="text-title font-bold mb-1 lh-1 align-items-center">
                                   {{ \App\CentralLogics\Helpers::format_currency($customer->wallet_balance) }}
                                </h3>
                                <span class="fs-14 font-normal text-title">{{ translate('Current Balance') }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-md-4">
                    <a class="order--card justify-content-start gap-3 h-100 card-bg2" href="javascript:void(0)">
                        <div class="d-flex align-items-center gap-3">
                            <div class="w-45px h-45px rounded-circle d-center bg-white">
                                <img width="20" height="20" src="{{dynamicAsset('assets/admin/img/deposit-wallet.png')}}" alt="img" class="object--contain">
                            </div>
                            <div>
                                <h3 class="text-title font-bold mb-1 lh-1 align-items-center">
                                   {{ \App\CentralLogics\Helpers::format_currency($totalDebit) }}
                                </h3>
                                <span class="fs-14 font-normal text-title">{{ translate('Total Debit') }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-md-4">
                    <a class="order--card justify-content-start gap-3 h-100 card-bg3" href="javascript:void(0)">
                        <div class="d-flex align-items-center gap-3">
                            <div class="w-45px h-45px rounded-circle d-center bg-white">
                                <img width="20" height="20" src="{{dynamicAsset('assets/admin/img/blance-time.png')}}" alt="img" class="object--contain">
                            </div>
                            <div>
                                <h3 class="text-title font-bold mb-1 lh-1 align-items-center">

                                    {{ \App\CentralLogics\Helpers::format_currency($totalCredit) }}
                                </h3>
                                <span class="fs-14 font-normal text-title">{{ translate('Total Credit') }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header py-xl-20 flex-wrap gap-2 border-0">
            <h5 class="card-header-title">{{ translate('messages.Wallet Transaction History') }}
                   <span class="badge badge-soft-secondary" id="itemCount">{{ $walletHistory->total() }}</span>
            </h5>
            <div class="search--button-wrapper flex-xxs-nowrap">
                <form>
                    <input type="hidden" name="id" value="" id="">
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" class="form-control" value="{{ $search }}"
                            placeholder="{{  translate('Search By Transaction ID') }}" aria-label="Search" required>
                        <button type="submit" class="btn btn--reset px-2 w-35px">
                            <i class="tio-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Table -->
         <div class="px-xxl-20 px-3">
             <div class="table-responsive datatable-custom pt-0">
                 <table id="columnSearchDatatable"
                     class="table table-borderless table-thead-borderless table-nowrap table-align-middle card-table"
                     data-hs-datatables-options='{
                                     "order": [],
                                     "orderCellsTop": true,
                                     "paging":false
                                 }'>
                     <thead class="global-bg-box">
                         <tr>
                             <th class="py-3 fs-14 text-capitalize">{{ translate('sl') }}</th>
                             <th class="py-3 fs-14 text-capitalize">{{translate('Transaction Id')}}</th>
                             <th class="py-3 fs-14 text-capitalize">{{translate('Transaction Date')}}</th>
                             <th class="py-3 fs-14 text-capitalize text-center">{{translate('Amount')}}</th>
                             <th class="py-3 fs-14 text-capitalize">{{translate('Reference')}}</th>
                             <th class="py-3 fs-14 text-capitalize">{{translate('Transaction type')}}</th>
                         </tr>
                     </thead>
                     <tbody>
                         @foreach ($walletHistory as $key => $walletHistoryTransaction )
                             <tr>
                                <td>{{ $key + $walletHistory->firstItem() }}</td>
                                 <td class=" ">
                                 <div class="text-title line-limit-2 text-wrap max-w-260px  ">
                                     {{ $walletHistoryTransaction->reference_id }}
                                 </div>
                             </td>
                             <td class="text-uppercase fs-12">
                                         <div>
                                             {{ \App\CentralLogics\Helpers::date_format($walletHistoryTransaction->created_at) }}
                                         </div>
                                         <div>
                                             {{ \App\CentralLogics\Helpers::time_format($walletHistoryTransaction->created_at) }}
                                         </div>
                             </td>
                             <td class="text-center">
                                 @if($walletHistoryTransaction->debit == 0)
                                     <div class="text-title"> +   {{ \App\CentralLogics\Helpers::format_currency($walletHistoryTransaction->credit + $walletHistoryTransaction->admin_bonus) }}
                                    @if ($walletHistoryTransaction->admin_bonus > 0)
                                            <small>
                                               ( {{ translate('Bonus ') . \App\CentralLogics\Helpers::format_currency( $walletHistoryTransaction->admin_bonus) }} )
                                            </small>
                                        @endif
                                    </div>
                                        <span class="badge badge-soft-success min-w--50 px-2 mt-1">
                                            {{ translate('Credit') }}
                                     </span>
                                 @else
                                     <div class="text-title"> - {{ \App\CentralLogics\Helpers::format_currency($walletHistoryTransaction->debit) }}</div>
                                     <span class="badge badge-soft-danger min-w--50 px-2 mt-1">
                                         {{ translate('Debit') }}
                                     </span>
                                 @endif
                             </td>
                            <td>
                                @if (is_numeric($walletHistoryTransaction->reference))
                                <a href="{{ route('admin.order.details', $walletHistoryTransaction->reference) }}" target="_blank" rel="noopener noreferrer">
                                    {{ $walletHistoryTransaction->reference }}
                                </a>
                                @else
                                <div class="text-title">
                                     {{ $walletHistoryTransaction->reference }}
                                 </div>
                                @endif
                            </td>
                            <td>
                                 <div class="text-title text-wrap line--limit-1">
                                     {{ translate($walletHistoryTransaction->transaction_type) }}
                                 </div>
                            </td>
                         </tr>
                         @endforeach
                     </tbody>

                 </table>
             </div>
         </div>
        @if (count($walletHistory) === 0)
                <div class="empty--data">
                    <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="public">
                    <h5>
                        {{ translate('no_data_found') }}
                    </h5>
                </div>
            @endif
            <div class="card-footer p-0 border-0">
                <!-- Pagination -->
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $walletHistory->appends($_GET)->links() !!}
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>


</div>


@endsection

@push('script_2')

@endpush
