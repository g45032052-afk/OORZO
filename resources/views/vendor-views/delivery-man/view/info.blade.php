@extends('layouts.vendor.app')

@section('title',translate('Delivery Man Preview'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">

        <!--- New --->
        <h2 class="page-header-title text-capitalize mb-20">
            <div class="card-header-icon d-inline-flex img">
                <i class="tio-add-circle-outlined"></i>
            </div>
            <span>{{translate('Delivery Man Preview')}}</span>
        </h2>

        <div class="card mb-3 mb-lg-5 mt-2">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3 justify-content-between mb-20">
                    <div class="title-grp">
                        <h5 class="fs-16 mb-1">{{translate('Deliveryman Overview')}}</h5>
                        <p class="fs-14 mb-0">{{translate('Joining Date')}} 28 Dec 2024</p>
                    </div>
                    <div class="d-flex align-items-center flex-md-nowrap flex-wrap gap-xxl-20 gap-2">
                        <a  href="javascript:" data-url="{{route('vendor.delivery-man.status',[$dm['id'],$dm->status?0:1])}}" data-message="{{$dm->status?'Want to suspend this deliveryman ?':'Want to unsuspend this deliveryman'}}" class="btn request_alert {{$dm->status?'btn--danger':'btn-success'}}">
                            {{$dm->status?translate('messages.suspend_this_delivery_man'):translate('messages.unsuspend_this_delivery_man')}}
                        </a>
                        <button type="button" class="btn btn--primary">
                            <img class="svg" src="{{dynamicAsset('assets/admin/img/bx_edit.svg')}}" alt="icon">
                            {{translate('messages.edit')}}
                        </button>
                        <button type="button" class="btn btn--danger text-hover-white">
                            <i class="tio-delete"></i>
                            {{translate('messages.delete')}}
                        </button>
                    </div>
                </div>

                <div class="border rounded p-xxl-20 p-3">
                    <div class="row g-3 align-items-md-center justify-content-between">
                        <div class="col-lg-6">
                            <div class="review-status-information flex-wrap flex-sm-nowrap d-flex align-items-center gap-20px">
                                <div class="thumb ratio-1 h--120px w-120px h-120px rounded">
                                    <img class="w-100 rounded h-100"
                                    src="{{ $dm?->image_full_url ?? dynamicAsset('assets/admin/img/160x160/img1.jpg') }}"
                                    alt="image">
                                </div>
                                <div class="cont">
                                    <h4 class="page-header-title mb-0">
                                        <span class="mr-2 fs-25 line--limit-1 max-w-353px">{{$dm['f_name'].' '.$dm['l_name']}}</span>
                                        @if($dm['status']) @if($dm['active']) <label class="text-success fw-semibold fs-16 mb-0">{{translate('messages.online')}}</label> @else <label class="text-primary fw-semibold fs-16">{{translate('messages.offline')}}</label> @endif  @else <span class="text-danger fw-semibold fs-16">{{translate('messages.suspended')}}</span> @endif
                                    </h4>
                                    <div class="d-flex flex-column gap-1 mt-10px">
                                        <div class="d-flex gap-10 align-items-center">
                                            <span class="fs-14 text-dark min-w-100px">{{translate('messages.Deliveryman ID')}}</span>
                                            <span>:</span>
                                            <span class="fs-14 text-dark font-weight-semibold">#{{ $dm['id'] }}</span>
                                        </div>
                                        <div class="d-flex gap-10 align-items-center">
                                            <span class="fs-14 text-dark min-w-100px">{{translate('messages.Email')}}</span>
                                            <span>:</span>
                                            <span class="fs-14 text-dark font-weight-semibold">{{ $dm['email'] }}</span>
                                        </div>
                                        <div class="d-flex gap-10 align-items-center">
                                            <span class="fs-14 text-dark min-w-100px">{{translate('messages.Phone')}}</span>
                                            <span>:</span>
                                            <span class="fs-14 text-dark font-weight-semibold">{{ $dm['phone'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="ml-lg-auto max-w-487">
                                <div class="dm-review-custom-box d-flex align-items-center flex-wrap flex-sm-nowrap">
                                    <div class="d-block">
                                        <div class="min-w-120px bg-global-gray rounded py-3 px-20px">
                                            <div class="rating--review text-center">
                                                <img class="svg mb-2 d-block mx-auto text-center" src="{{dynamicAsset('assets/admin/img/ratting-star.svg')}}" alt="icon">
                                                <h1 class="mb-2 text--primary-2"><span class="fs-25">{{count($dm->rating)>0?number_format($dm->rating[0]->average, 1, '.', ' '):0}}</span><span class="out-of fs-16 gray-dark">/5</span></h1>
                                                @if (count($dm->rating)>0)
                                                @if ($dm->rating[0]->average == 5)
                                                <div class="rating">
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average < 5 && $dm->rating[0]->average >= 4.5)
                                                <div class="rating">
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star-half"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average < 4.5 && $dm->rating[0]->average >= 4)
                                                <div class="rating">
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average < 4 && $dm->rating[0]->average >= 3.5)
                                                <div class="rating">
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star-half"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average < 3.5 && $dm->rating[0]->average >= 3)
                                                <div class="rating">
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average < 3 && $dm->rating[0]->average >= 2.5)
                                                <div class="rating">
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star-half"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average < 2.5 && $dm->rating[0]->average > 2)
                                                <div class="rating">
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average < 2 && $dm->rating[0]->average >= 1.5)
                                                <div class="rating">
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star-half"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average < 1.5 && $dm->rating[0]->average > 1)
                                                <div class="rating">
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average < 1 && $dm->rating[0]->average > 0)
                                                <div class="rating">
                                                    <span><i class="tio-star-half"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average == 1)
                                                <div class="rating">
                                                    <span><i class="tio-star"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                </div>
                                                @elseif ($dm->rating[0]->average == 0)
                                                <div class="rating">
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                    <span><i class="tio-star-outlined"></i></span>
                                                </div>
                                                @endif
                                                @endif
                                                <div class="info fs-12">
                                                    <span>{{translate('messages.of')}} {{$dm->reviews->count()}} {{translate('messages.reviews')}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled list-unstyled-py-2 w-100 mb-0 rating--review-right rating--review-right-style2">
                                    @php($total=$dm->reviews->count())
                                    <!-- Review Ratings -->
                                        <li class="d-flex align-items-center font-size-sm">
                                            @php($five=\App\CentralLogics\Helpers::dm_rating_count($dm['id'],5))
                                            <span
                                                class="progress-name mr-3">{{translate('messages.Excellent')}}</span>
                                            <div class="progress flex-grow-1">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$total==0?0:($five/$total)*100}}%;"
                                                     aria-valuenow="{{$total==0?0:($five/$total)*100}}"
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="ml-3">{{$five}}</span>
                                        </li>
                                        <!-- End Review Ratings -->

                                        <!-- Review Ratings -->
                                        <li class="d-flex align-items-center font-size-sm">
                                            @php($four=\App\CentralLogics\Helpers::dm_rating_count($dm['id'],4))
                                            <span class="progress-name mr-3">{{translate('messages.Good')}}</span>
                                            <div class="progress flex-grow-1">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$total==0?0:($four/$total)*100}}%;"
                                                     aria-valuenow="{{$total==0?0:($four/$total)*100}}"
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="ml-3">{{$four}}</span>
                                        </li>
                                        <!-- End Review Ratings -->

                                        <!-- Review Ratings -->
                                        <li class="d-flex align-items-center font-size-sm">
                                            @php($three=\App\CentralLogics\Helpers::dm_rating_count($dm['id'],3))
                                            <span class="progress-name mr-3">{{translate('messages.Average')}}</span>
                                            <div class="progress flex-grow-1">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$total==0?0:($three/$total)*100}}%;"
                                                     aria-valuenow="{{$total==0?0:($three/$total)*100}}"
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="ml-3">{{$three}}</span>
                                        </li>
                                        <!-- End Review Ratings -->

                                        <!-- Review Ratings -->
                                        <li class="d-flex align-items-center font-size-sm">
                                            @php($two=\App\CentralLogics\Helpers::dm_rating_count($dm['id'],2))
                                            <span class="progress-name mr-3">{{translate('messages.Below_Average')}}</span>
                                            <div class="progress flex-grow-1">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$total==0?0:($two/$total)*100}}%;"
                                                     aria-valuenow="{{$total==0?0:($two/$total)*100}}"
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="ml-3">{{$two}}</span>
                                        </li>
                                        <!-- End Review Ratings -->

                                        <!-- Review Ratings -->
                                        <li class="d-flex align-items-center font-size-sm">
                                            @php($one=\App\CentralLogics\Helpers::dm_rating_count($dm['id'],1))
                                            <span class="progress-name mr-3">{{translate('messages.poor')}}</span>
                                            <div class="progress flex-grow-1">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$total==0?0:($one/$total)*100}}%;"
                                                     aria-valuenow="{{$total==0?0:($one/$total)*100}}"
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="ml-3">{{$one}}</span>
                                        </li>
                                        <!-- End Review Ratings -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-20 border rounded p-xxl-20 p-3">
                    <h5 class="fs-16 mb-20">{{translate('messages.identity_documents')}}</h5>
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <div class="bg-global-gray d-flex align-items-center rounded p-3 h-100">
                                <div>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex gap-10 align-items-center">
                                            <span class="fs-14 text-dark minmax-xxl-140">{{translate('messages.Identity Type')}}</span>
                                            <span>:</span>
                                            <strong class="fs-14 text-dark font-weight-semibold">{{translate('messages.Passport')}}</strong>
                                        </div>
                                        <div class="d-flex gap-10 align-items-center">
                                            <span class="fs-14 text-dark minmax-xxl-140">{{translate('messages.Identification number')}}</span>
                                            <span>:</span>
                                            <strong class="fs-14 text-dark font-weight-semibold">{{translate('messages.2351684567235')}}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="bg-global-gray p-xxl-3 p-2 overflow-hidden rounded">
                                <div class="tabs-slide-language tabs-slide-identity position-relative">
                                    <div class="nav nav-tabs gap-10px">
                                        <div class="nav-item">
                                            <div class="identity-thumbs ratio-2-1 h-92px overflow-hidden rounded">
                                                <img src="{{dynamicAsset('assets/admin/img/400x400/img3.jpg')}}" alt="thumb" class="rounded">
                                            </div>
                                        </div>
                                        <div class="nav-item">
                                            <div class="identity-thumbs ratio-2-1 h-92px overflow-hidden rounded">
                                                <img src="{{dynamicAsset('assets/admin/img/400x400/img3.jpg')}}" alt="thumb" class="rounded">
                                            </div>
                                        </div>
                                        <div class="nav-item">
                                            <div class="identity-thumbs ratio-2-1 h-92px overflow-hidden rounded">
                                                <img src="{{dynamicAsset('assets/admin/img/400x400/img3.jpg')}}" alt="thumb" class="rounded">
                                            </div>
                                        </div>
                                        <div class="nav-item">
                                            <div class="identity-thumbs ratio-2-1 h-92px overflow-hidden rounded">
                                                <img src="{{dynamicAsset('assets/admin/img/400x400/img3.jpg')}}" alt="thumb" class="rounded">
                                            </div>
                                        </div>
                                        <div class="nav-item">
                                            <div class="identity-thumbs ratio-2-1 h-92px overflow-hidden rounded">
                                                <img src="{{dynamicAsset('assets/admin/img/400x400/img3.jpg')}}" alt="thumb" class="rounded">
                                            </div>
                                        </div>
                                        <div class="nav-item">
                                            <div class="identity-thumbs ratio-2-1 h-92px overflow-hidden rounded">
                                                <img src="{{dynamicAsset('assets/admin/img/400x400/img3.jpg')}}" alt="thumb" class="rounded">
                                            </div>
                                        </div>
                                        <div class="nav-item">
                                            <div class="identity-thumbs ratio-2-1 h-92px overflow-hidden rounded">
                                                <img src="{{dynamicAsset('assets/admin/img/400x400/img3.jpg')}}" alt="thumb" class="rounded">
                                            </div>
                                        </div>
                                        <div class="nav-item">
                                            <div class="identity-thumbs ratio-2-1 h-92px overflow-hidden rounded">
                                                <img src="{{dynamicAsset('assets/admin/img/400x400/img3.jpg')}}" alt="thumb" class="rounded">
                                            </div>
                                        </div>
                                        <div class="nav-item">
                                            <div class="identity-thumbs ratio-2-1 h-92px overflow-hidden rounded">
                                                <img src="{{dynamicAsset('assets/admin/img/400x400/img3.jpg')}}" alt="thumb" class="rounded">
                                            </div>
                                        </div>
                                        <div class="nav-item">
                                            <div class="identity-thumbs ratio-2-1 h-92px overflow-hidden rounded">
                                                <img src="{{dynamicAsset('assets/admin/img/400x400/img3.jpg')}}" alt="thumb" class="rounded">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="arrow-area">
                                        <div class="button-prev align-items-center">
                                            <button type="button"
                                                class="btn btn-click-prev mr-auto ms-8px border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                                <i class="tio-chevron-left fs-24"></i>
                                            </button>
                                        </div>
                                        <div class="button-next align-items-center">
                                            <button type="button"
                                                class="btn btn-click-next ml-auto me-8px border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                                <i class="tio-chevron-right fs-24"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 mb-20">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Earnings (Monthly) Card Example -->
                    <div class="col-sm-6 col-md-4">
                        <div class="resturant-card py-4 px-20 d-flex align-items-center bg-soft-success justify-content-between">
                            <div>
                                <h4 class="title fs-24 text-success">{{$dm->orders()->delivered()->count()}}</h4>
                                <span class="subtitle font-weight-normal">
                                    {{translate('messages.total_delivered_orders')}}
                                </span>
                            </div>
                            <div class="w-50px h-50px rounded-pill bg-white d-center">
                                <img class="resturant-icon position-static" src="{{dynamicAsset('assets/admin/img/resturant-panel/deliveryman/delivered.png')}}" alt="dashboard">
                            </div>
                        </div>
                    </div>

                    <!-- Collected Cash Card Example -->
                    <div class="col-sm-6 col-md-4">
                        <div class="resturant-card py-4 px-20 d-flex align-items-center bg-soft-danger justify-content-between">
                            <div>
                                <h4 class="title fs-24 text-danger">{{\App\CentralLogics\Helpers::format_currency($dm->wallet?$dm->wallet->collected_cash:0.0)}}</h4>
                                <span class="subtitle font-weight-normal">
                                    {{translate('messages.cash_in_hand')}}
                                </span>
                            </div>
                            <div class="w-50px h-50px rounded-pill bg-white d-center">
                                <img class="resturant-icon position-static" src="{{dynamicAsset('assets/admin/img/resturant-panel/deliveryman/cash.png')}}" alt="dashboard">
                            </div>
                        </div>
                    </div>

                    @php($restaurant_data = \App\CentralLogics\Helpers::get_restaurant_data())
                    @if (($restaurant_data->restaurant_model == 'commission' && $restaurant_data->self_delivery_system == 1) || 
                        ($restaurant_data->restaurant_model == 'subscription' && isset($restaurant_data->restaurant_sub) && $restaurant_data->restaurant_sub->self_delivery == 1))

                    <!-- Total Earning Card Example -->
                    <div class="col-sm-6 col-md-4">
                        <div class="resturant-card py-4 px-20 d-flex align-items-center bg-soft-info justify-content-between">
                            <div>
                                <h4 class="title fs-24 text-primary">{{\App\CentralLogics\Helpers::format_currency($dm->wallet?$dm->wallet->total_earning:0.00)}}</h4>
                                <span class="subtitle font-weight-normal">
                                    {{translate('messages.total_earning')}}
                                </span>
                            </div>
                            <div class="w-50px h-50px rounded-pill bg-white d-center">
                                <img class="resturant-icon position-static" src="{{dynamicAsset('assets/admin/img/resturant-panel/deliveryman/earning.png')}}" alt="dashboard">
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        @php($restaurant=\App\CentralLogics\Helpers::get_restaurant_data())
        @if ($restaurant->restaurant_model == 'commission' && $restaurant->reviews_section || ($restaurant->restaurant_model == 'subscription' && isset($restaurant->restaurant_sub) && $restaurant->restaurant_sub->review))
        <div class="card pb-0 p-20">
            <!-- Table -->
            <div class="table-responsive datatable-custom p-0">
                <table id="datatable" class="table table-borderless table-thead-bordered table-nowrap card-table"
                       data-hs-datatables-options='{
                     "columnDefs": [{
                        "targets": [0, 3, 6],
                        "orderable": false
                      }],
                     "order": [],
                     "info": {
                       "totalQty": "#datatableWithPaginationInfoTotalQty"
                     },
                     "search": "#datatableSearch",
                     "entries": "#datatableEntries",
                     "pageLength": 25,
                     "isResponsive": false,
                     "isShowPaging": false,
                     "pagination": "datatablePagination"
                   }'>
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('messages.SL')}}</th>
                        <th>{{translate('messages.Order ID')}}</th>
                        <th>{{translate('messages.date')}}</th>
                        <th>{{translate('messages.reviewer')}}</th>
                        <th>{{translate('messages.review')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($reviews as $review)
                        <tr>
                            <td>
                                1
                            </td>
                            <td>
                                <a href="{{route('vendor.order.details',['id'=>$review->order_id])}}">{{$review->order_id}}</a>
                            </td>
                            <td>
                                {{date('d M Y '. config('timeformat'),strtotime($review['created_at']))}}
                            </td>
                            <td>
                                @if ($review->customer)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-circle">
                                             <img class="avatar-img" width="60" height="60"
                                             src="{{ $review?->customer?->image ?? dynamicAsset('assets/admin/img/160x160/img1.jpg') }}"
                                             alt="image">
                                    </div>
                                    <div class="ml-3">
                                    <span class="d-block h5 text-hover-primary mb-0">{{$review->customer['f_name']." ".$review->customer['l_name']}} <i
                                            class="tio-verified text-primary" data-toggle="tooltip" data-placement="top"
                                            title="Verified Customer"></i></span>
                                        <span class="d-block font-12 text-body">{{$review->customer->email}}</span>
                                    </div>
                                </div>
                                @else
                                    {{translate('messages.customer_not_found')}}
                                @endif
                            </td>
                            <td>
                                <div class="text-wrap w-18rem">
                                    <div class="d-flex gap-1 align-items-center fw-semibold text-dark mb-2">
                                        {{$review->rating}} <i class="tio-star text-warning"></i>
                                    </div>

                                    <p class="fs-14 line-limit-2 max-w-353px" data-toggle="tooltip" data-placement="top"
                                        data-original-title="{{ translate('I am so grateful for the exceptional service provided by Ellis! He navigated a complex delivery with grace and a smile. Highly recommended for anyone needing a reliable and friendly delivery expert.') }}">
                                        {{$review['comment']}}
                                    </p>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($reviews) === 0)
                <div class="empty--data pt-5">
                   <div class="py-5 my-md-5">
                         <img src="{{dynamicAsset('assets/admin/img/no-review-list.png')}}" alt="public">
                        <p class="mt-2 mb-0">
                            {{translate('No review List')}}
                        </p>
                   </div>
                </div>
                @endif
            </div>
            <!-- End Table -->

            <!-- Footer -->
            <div class="card-footer border-0">
                <!-- Pagination -->
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-12">
                        {!! $reviews->links() !!}
                    </div>
                </div>
                <!-- End Pagination -->
            </div>
            <!-- End Footer -->
        </div>
        @endif
        <!-- New Code -->


        <!-- Previous Code -->
        {{--<div class="card border-0">
            <div class="card-header border-0">
                <h2 class="page-header-title text-capitalize">
                    <div class="card-header-icon d-inline-flex img">
                        <i class="tio-add-circle-outlined"></i>
                    </div>
                    <span>{{translate('Delivery Man Preview')}}</span>
                </h2>
            </div>
            <div class="card-body pt-0">
                <div class="js-nav-scroller hs-nav-scroller-horizontal mb-4">
                    <!-- Nav -->
                    <ul class="nav nav-tabs page-header-tabs m-0">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{route('vendor.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'info'])}}"  aria-disabled="true">{{translate('messages.info')}}</a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link" href="{{route('vendor.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'transaction'])}}"  aria-disabled="true">{{translate('messages.transaction')}}</a>
                        </li> -->
                    </ul>
                    <!-- End Nav -->
                </div>
                <div class="row g-3">
                    <!-- Earnings (Monthly) Card Example -->
                    <div class="col-sm-6 col-md-4">
                        <div class="resturant-card dashboard--card bg--2">
                            <h4 class="title">{{$dm->orders->count()}}</h4>
                            <span class="subtitle">
                                {{translate('messages.total_delivered_orders')}}
                            </span>
                            <img class="resturant-icon" src="{{dynamicAsset('assets/admin/img/resturant-panel/deliveryman/delivered.png')}}" alt="dashboard">
                        </div>
                    </div>

                    <!-- Collected Cash Card Example -->
                    <div class="col-sm-6 col-md-4">
                        <div class="resturant-card dashboard--card bg--3">
                            <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($dm->wallet?$dm->wallet->collected_cash:0.0)}}</h4>
                            <span class="subtitle">{{translate('messages.cash_in_hand')}}</span>
                            <img class="resturant-icon" src="{{dynamicAsset('assets/admin/img/resturant-panel/deliveryman/cash.png')}}" alt="dashboard">
                        </div>
                    </div>

                    <!-- Total Earning Card Example -->
                    <div class="col-sm-6 col-md-4">
                        <div class="resturant-card dashboard--card bg--1">
                            <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($dm->wallet?$dm->wallet->total_earning:0.00)}}</h4>
                            <span class="subtitle">{{translate('messages.total_earning')}}</span>
                            <img class="resturant-icon" src="{{dynamicAsset('assets/admin/img/resturant-panel/deliveryman/earning.png')}}" alt="dashboard">
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="card mb-20 mt-2">
            <div class="card-header border-0">
                <h4 class="page-header-title">
                    <span class="mr-2">{{$dm['f_name'].' '.$dm['l_name']}}</span>
                    @if($dm['status']) @if($dm['active']) <label class="badge badge-soft-primary m-0">{{translate('messages.online')}}</label> @else <label class="badge badge-soft-success m-0">{{translate('messages.offline')}}</label> @endif  @else <span class="badge badge-danger">{{translate('messages.suspended')}}</span> @endif</h4>

                <a  href="javascript:" data-url="{{route('vendor.delivery-man.status',[$dm['id'],$dm->status?0:1])}}" data-message="{{$dm->status?'Want to suspend this deliveryman ?':'Want to unsuspend this deliveryman'}}" class="btn request_alert {{$dm->status?'btn--danger':'btn-success'}}">
                        {{$dm->status?translate('messages.suspend_this_delivery_man'):translate('messages.unsuspend_this_delivery_man')}}
                </a>
            </div>
            <div class="card-body">
                <div class="row align-items-md-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-center">
                                 <img class="avatar avatar-xxl avatar-4by3 mr-4 mw-120px"
                                 src="{{ $dm?->image_full_url ?? dynamicAsset('assets/admin/img/160x160/img1.jpg') }}"
                                 alt="image">
                                 <div class="d-block">
                                    <div class="rating--review">
                                        <h1 class="title">{{count($dm->rating)>0?number_format($dm->rating[0]->average, 1, '.', ' '):0}}<span class="out-of">/5</span></h1>
                                        @if (count($dm->rating)>0)
                                        @if ($dm->rating[0]->average == 5)
                                        <div class="rating">
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average < 5 && $dm->rating[0]->average >= 4.5)
                                        <div class="rating">
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star-half"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average < 4.5 && $dm->rating[0]->average >= 4)
                                        <div class="rating">
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average < 4 && $dm->rating[0]->average >= 3.5)
                                        <div class="rating">
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star-half"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average < 3.5 && $dm->rating[0]->average >= 3)
                                        <div class="rating">
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average < 3 && $dm->rating[0]->average >= 2.5)
                                        <div class="rating">
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star-half"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average < 2.5 && $dm->rating[0]->average > 2)
                                        <div class="rating">
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average < 2 && $dm->rating[0]->average >= 1.5)
                                        <div class="rating">
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star-half"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average < 1.5 && $dm->rating[0]->average > 1)
                                        <div class="rating">
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average < 1 && $dm->rating[0]->average > 0)
                                        <div class="rating">
                                            <span><i class="tio-star-half"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average == 1)
                                        <div class="rating">
                                            <span><i class="tio-star"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                        </div>
                                        @elseif ($dm->rating[0]->average == 0)
                                        <div class="rating">
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                            <span><i class="tio-star-outlined"></i></span>
                                        </div>
                                        @endif
                                    @endif
                                    <div class="info">
                                        <span>{{$dm->reviews->count()}} {{translate('messages.reviews')}}</span>
                                    </div>
                                    </div>
                                </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <ul class="list-unstyled list-unstyled-py-2 mb-0 rating--review-right py-3">
                        @php($total=$dm->reviews->count())
                        <!-- Review Ratings -->
                            <li class="d-flex align-items-center font-size-sm">
                                @php($five=\App\CentralLogics\Helpers::dm_rating_count($dm['id'],5))
                                <span
                                    class="progress-name mr-3">{{translate('messages.Excellent')}}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{$total==0?0:($five/$total)*100}}%;"
                                         aria-valuenow="{{$total==0?0:($five/$total)*100}}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="ml-3">{{$five}}</span>
                            </li>
                            <!-- End Review Ratings -->

                            <!-- Review Ratings -->
                            <li class="d-flex align-items-center font-size-sm">
                                @php($four=\App\CentralLogics\Helpers::dm_rating_count($dm['id'],4))
                                <span class="progress-name mr-3">{{translate('messages.Good')}}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{$total==0?0:($four/$total)*100}}%;"
                                         aria-valuenow="{{$total==0?0:($four/$total)*100}}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="ml-3">{{$four}}</span>
                            </li>
                            <!-- End Review Ratings -->

                            <!-- Review Ratings -->
                            <li class="d-flex align-items-center font-size-sm">
                                @php($three=\App\CentralLogics\Helpers::dm_rating_count($dm['id'],3))
                                <span class="progress-name mr-3">{{translate('messages.Average')}}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{$total==0?0:($three/$total)*100}}%;"
                                         aria-valuenow="{{$total==0?0:($three/$total)*100}}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="ml-3">{{$three}}</span>
                            </li>
                            <!-- End Review Ratings -->

                            <!-- Review Ratings -->
                            <li class="d-flex align-items-center font-size-sm">
                                @php($two=\App\CentralLogics\Helpers::dm_rating_count($dm['id'],2))
                                <span class="progress-name mr-3">{{translate('messages.Below_Average')}}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{$total==0?0:($two/$total)*100}}%;"
                                         aria-valuenow="{{$total==0?0:($two/$total)*100}}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="ml-3">{{$two}}</span>
                            </li>
                            <!-- End Review Ratings -->

                            <!-- Review Ratings -->
                            <li class="d-flex align-items-center font-size-sm">
                                @php($one=\App\CentralLogics\Helpers::dm_rating_count($dm['id'],1))
                                <span class="progress-name mr-3">{{translate('messages.poor')}}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{$total==0?0:($one/$total)*100}}%;"
                                         aria-valuenow="{{$total==0?0:($one/$total)*100}}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="ml-3">{{$one}}</span>
                            </li>
                            <!-- End Review Ratings -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @php($restaurant=\App\CentralLogics\Helpers::get_restaurant_data())
        @if ($restaurant->restaurant_model == 'commission' && $restaurant->reviews_section || ($restaurant->restaurant_model == 'subscription' && isset($restaurant->restaurant_sub) && $restaurant->restaurant_sub->review))
        <div class="card">
            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table id="datatable" class="table table-borderless table-thead-bordered table-nowrap card-table"
                       data-hs-datatables-options='{
                     "columnDefs": [{
                        "targets": [0, 3, 6],
                        "orderable": false
                      }],
                     "order": [],
                     "info": {
                       "totalQty": "#datatableWithPaginationInfoTotalQty"
                     },
                     "search": "#datatableSearch",
                     "entries": "#datatableEntries",
                     "pageLength": 25,
                     "isResponsive": false,
                     "isShowPaging": false,
                     "pagination": "datatablePagination"
                   }'>
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('messages.reviewer')}}</th>
                        <th>Order ID</th>
                        <th>{{translate('messages.review')}}</th>
                        <th>{{translate('messages.date')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($reviews as $review)
                        <tr>
                            <td>
                                @if ($review->customer)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-circle">
                                             <img class="avatar-img" width="75" height="75"
                                             src="{{ $review?->customer?->image ?? dynamicAsset('assets/admin/img/160x160/img1.jpg') }}"
                                             alt="image">
                                    </div>
                                    <div class="ml-3">
                                    <span class="d-block h5 text-hover-primary mb-0">{{$review->customer['f_name']." ".$review->customer['l_name']}} <i
                                            class="tio-verified text-primary" data-toggle="tooltip" data-placement="top"
                                            title="Verified Customer"></i></span>
                                        <span class="d-block font-size-sm text-body">{{$review->customer->email}}</span>
                                    </div>
                                </div>
                                @else
                                    {{translate('messages.customer_not_found')}}
                                @endif
                            </td>
                            <td>
                                <a href="{{route('vendor.order.details',['id'=>$review->order_id])}}">{{$review->order_id}}</a>

                            </td>
                            <td>
                                <div class="text-wrap w-18rem">
                                    <div class="d-flex mb-2">
                                        <label class="badge badge-soft-info">
                                            {{$review->rating}} <i class="tio-star"></i>
                                        </label>
                                    </div>

                                    <p>
                                        {{$review['comment']}}
                                    </p>
                                </div>
                            </td>
                            <td>
                                {{date('d M Y '. config('timeformat'),strtotime($review['created_at']))}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($reviews) === 0)
                <div class="empty--data">
                    <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                    <h5>
                        {{translate('no_data_found')}}
                    </h5>
                </div>
                @endif
            </div>
            <!-- End Table -->

            <!-- Footer -->
            <div class="card-footer border-0">
                <!-- Pagination -->
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-12">
                        {!! $reviews->links() !!}
                    </div>
                </div>
                <!-- End Pagination -->
            </div>
            <!-- End Footer -->
        </div>
        @endif--}}


    </div>
@endsection

@push('script_2')
<script>
    $('.request_alert').on('click', function (event) {
        let url = $(this).data('url');
        let message = $(this).data('message');
        request_alert(url, message)
    })
    function request_alert(url, message) {
        Swal.fire({
            title: '{{ translate('Are_you_sure?') }}',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '{{ translate('no') }}',
            confirmButtonText: '{{ translate('yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                location.href = url;
            }
        })
    }
</script>
@endpush
