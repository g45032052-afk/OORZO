@extends('layouts.admin.app')

@section('title', translate('Refferal'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@section('customerDetails')
    active
@endsection
@section('content')

<div class="content container-fluid">
    <!--  -->
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
                <div class="col-sm-6 col-md-6 col-lg-4">
                    <a class="order--card justify-content-start gap-3 h-100 card-bg3" href="javascript:void(0)">
                        <div class="find-copy-text d-flex gap-3 justify-content-between">
                            <div>
                                <h3 class="copy-this text-danger font-bold mb-1 align-items-center">
                                    {{ $customer->ref_code }}
                                </h3>
                                <span class="fs-14 font-normal text-title">{{ translate('Reefer Code') }}</span>
                            </div>
                            <button type="button" class="btn p-0 w-30px h-30px d-center bg-white rounded-circle text-primary copy-btn">
                                <i class="tio-copy"></i>
                            </button>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-4">
                    <a class="order--card justify-content-start gap-3 h-100 card-bg4" href="javascript:void(0)">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <h3 class="font-bold mb-1 align-items-center" data-text-color="#E6A832">
                                  {{ $totalJoinedByReferral }}
                                </h3>
                                <span class="fs-14 font-normal text-title">{{ translate('Joined via Code') }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-4">
                    <a class="order--card justify-content-start gap-3 h-100 card-bg2" href="javascript:void(0)">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <h3 class="font-bold mb-1 align-items-center" data-text-color="#019463">
                               {{\App\CentralLogics\Helpers::format_currency($totalEarnedByReferral)  }}
                                </h3>
                                <span class="fs-14 font-normal text-title">{{ translate('Referral Earned') }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header py-xl-20 flex-wrap gap-2 border-0">
            <h5 class="card-header-title">{{ translate('Referral History') }}
                   <span class="badge badge-soft-secondary" id="itemCount">{{ $referral->total() }}</span>
            </h5>
            <div class="search--button-wrapper flex-xxs-nowrap">
                <form>
                    <input type="hidden" name="id" value="" id="">
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" class="form-control" value="{{ request()->search }}"
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
                 <table  class="table table-borderless table-thead-borderless table-nowrap table-align-middle card-table">
                     <thead class="global-bg-box">
                         <tr>
                             <th class="py-3 fs-14 text-capitalize">{{ translate('messages.sl') }}</th>
                             <th class="py-3 fs-14 text-capitalize">{{translate('messages.Transaction Id')}}</th>
                             <th class="py-3 fs-14 text-capitalize">{{translate('messages.Referral Date')}}</th>
                             <th class="py-3 fs-14 text-capitalize">{{translate('messages.Referral Amount')}}</th>
                         </tr>
                     </thead>
                     <tbody>
                         @foreach ($referral as $key => $referralTransaction)
                             <tr>
                                 <td>{{ $key + $referral->firstItem() }}</td>
                                 <td>
                                 <div class="text-title  max-w-220px text-wrap line--limit-1">
                                    {{ $referralTransaction->reference_id }}
                                 </div>
                             </td>
                             <td class="text-uppercase fs-12">
                                         <div>
                                             {{ \App\CentralLogics\Helpers::date_format($referralTransaction->created_at) }}
                                         </div>
                                         <div>
                                             {{ \App\CentralLogics\Helpers::time_format($referralTransaction->created_at) }}
                                         </div>
                             </td>
                             <td class="">
                                 <div class="text-title">

                                     {{\App\CentralLogics\Helpers::format_currency($referralTransaction->credit)  }}
                                 </div>
                            </td>
                         </tr>
                         @endforeach
                     </tbody>
                 </table>
             </div>
         </div>
        @if (count($referral) === 0)
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
                            {!! $referral->appends($_GET)->links() !!}
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
