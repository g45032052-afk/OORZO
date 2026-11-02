@extends('layouts.vendor.app')

@section('title',translate('Review List'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
    <!-- Page Header -->
     <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{dynamicAsset('assets/admin/img/resturant-panel/page-title/review.png')}}" alt="public">
                        </div>
                        <span>
                            {{translate('messages.customers_reviews')}}
                        </span>
                    </h2>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header p-3 flex-wrap gap-2 border-0">
                <form action="{{ url()->current() }}" class="vendor--search">

                    @if(request('rating'))
                        <input type="hidden" name="rating" value="{{ request('rating') }}">
                    @endif

                    @if(request('start_date'))
                        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    @endif

                    @if(request('end_date'))
                        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    @endif

                    @if(request('reply_status'))
                        @foreach(request('reply_status') as $status)
                            <input type="hidden" name="reply_status[]" value="{{ $status }}">
                        @endforeach
                    @endif

                    <div class="input--group input-group border rounded">
                        <input type="search" name="search" id="column1_search" class="form-control border-0"
                               placeholder="{{ translate('messages.Ex :_Search by food name, or phone...') }}"
                               value="{{ request('search') }}">
                        <button type="submit" class="btn btn--reset px-2 py-1">
                            <i class="tio-search"></i>
                        </button>
                    </div>

                </form>

                @php($filtered = request()->has('rating') || request()->has('start_date') && request('start_date') != '' || request()->has('end_date') && request('end_date') != '' || request()->has('reply_status'))
                <div class="hs-unfold">
                    <a class="js-hs-unfold-invoker h-35 btn min-w-100px justify-content-center font-medium btn-sm filter-show offcanvas-trigger {{ $filtered ? 'filter-active' : 'btn-outline-primary' }}"
                            data-target="#Food-list_filter" href="javascript:">
                        <i class="tio-tune-horizontal mr-1 fs-16"></i>{{translate('Filter')}}
                        @if($filtered)
                            <span class="filter-dot"></span>
                        @endif
                    </a>
                </div>
            </div>
            <!-- End Header -->
@php($restaurant_review_reply = App\Models\BusinessSetting::where('key' , 'restaurant_review_reply')->first()->value ?? 0)
            <!-- Table -->
            <div class="table-responsive pt-0">
                <table
                        class="table table-borderless table-thead-borderedless table-nowrap table-align-middle card-table"
                        data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true,
                            "paging": false
                        }'>
                    <thead class="global-bg-box">
                    <tr>
                        <th>{{ translate('messages.sl') }}</th>
                        <th>{{translate('messages.food')}}</th>
                        <th>{{translate('messages.reviewer')}}</th>
                        <th>{{translate('messages.review')}}</th>
                        <th>{{translate('messages.date')}}</th>
                        @if($restaurant_review_reply == '1')
                        <th class="text-center">{{translate('messages.action')}}</th>
                        @endif
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($reviews as $key=>$review)
                        <tr>
                            <td>{{$key+$reviews->firstItem()}}</td>
                            <td class="w-200px">
                                @if ($review->food)
                                <div class="position-relative media align-items-center min-w-370">
                                    <a class=" text-hover-primary absolute--link" href="{{route('vendor.food.view',[$review->food['id']])}}">
                                    </a>
                                    <img class="avatar avatar-lg mr-3  onerror-image"  data-onerror-image="{{dynamicAsset('assets/admin/img/100x100/food-default-image.png')}}"
                                         src="{{ $review->food['image_full_url'] ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}" alt="{{$review->food->name}} image">
                                    <div class="media-body">
                                        <h5 class="important--link max-w-260px line--limit-1 min-w-160 mb-0 text-hover-primary text-wrap">{{Str::limit($review->food['name'],10)}}</h5>
                                        <!-- Static -->
                                        <a href="{{route('vendor.order.details',['id'=>$review->order_id])}}"  class="fz--12 text-body important--link">{{ translate('Order ID') }} #{{$review->order_id}}</a>
                                        <!-- Static -->
                                    </div>
                                </div>
                                @else
                                    {{translate('messages.Food_deleted!')}}
                                @endif
                            </td>
                            <td>
                                @if($review->customer)
                                <div>
                                    <h5 class="d-block text-hover-primary mb-1">{{Str::limit($review->customer['f_name']." ".$review->customer['l_name'])}} <i
                                            class="tio-verified text-primary" data-toggle="tooltip" data-placement="top"
                                            title="Verified Customer"></i></h5>
                                    <span class="d-block font-size-sm gray-dark">{{Str::limit($review->customer->phone)}}</span>
                                </div>
                                @else
                                {{translate('messages.customer_not_found')}}
                                @endif
                            </td>
                            <td>
                                <div class="max-w-315 min-w-280 text-wrap">
                                    <label class="rating">
                                        <i class="tio-star"></i>
                                        <span>{{$review->rating}}</span>
                                    </label>
                                    <p class="gray-dark line-limit-2" data-toggle="tooltip" data-placement="bottom"
                                    data-original-title="{{ $review?->comment }}" >
                                        {{Str::limit($review['comment'], 80)}}
                                    </p>
                                </div>
                            </td>
                            <td>
                                <span class="d-block">
                                    {{ \App\CentralLogics\Helpers::date_format($review->created_at)  }}
                                </span>
                                <span class="d-block"> {{ \App\CentralLogics\Helpers::time_format($review->created_at)  }}</span>
                            </td>
                            @if($restaurant_review_reply == '1')
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a  class="btn btn-sm h-35 d-center min-w-105 px-2 btn--primary {{ $review->reply ? 'btn-outline-primary' : ''}}" data-toggle="modal" data-target="#reply-{{$review->id}}" title="View Details">
                                        {{ $review->reply ? translate('view_reply') : translate('give_reply')}}
                                    </a>
                                </div>
                            </td>
                            @endif
                            <div class="modal fade" id="reply-{{$review->id}}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header pb-4">
                                            <button type="button" class="payment-modal-close btn-close border-0 outline-0 bg-transparent" data-dismiss="modal">
                                                <i class="tio-clear"></i>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="position-relative media align-items-center">
                                                <a class="absolute--link" href="{{route('vendor.food.view',[$review->food['id']])}}">
                                                </a>
                                                <img class="avatar avatar-lg mr-3  onerror-image"  data-onerror-image="{{dynamicAsset('assets/admin/img/100x100/food-default-image.png')}}"
                                                     src="{{ $review->food['image_full_url'] ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}" alt="{{$review->food->name}} image">
                                                <div>
                                                    <h5 class="text-hover-primary mb-0">{{ $review->food['name'] }}</h5>
                                                    @if ($review->food['avg_rating'] == 5)
                                                        <div class="rating">
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] < 5 && $review->food['avg_rating'] >= 4.5)
                                                        <div class="rating">
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star-half"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] < 4.5 && $review->food['avg_rating'] >= 4)
                                                        <div class="rating">
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] < 4 && $review->food['avg_rating'] >= 3.5)
                                                        <div class="rating">
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star-half"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] < 3.5 && $review->food['avg_rating'] >= 3)
                                                        <div class="rating">
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] < 3 && $review->food['avg_rating'] >= 2.5)
                                                        <div class="rating">
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star-half"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] < 2.5 && $review->food['avg_rating'] > 2)
                                                        <div class="rating">
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] < 2 && $review->food['avg_rating'] >= 1.5)
                                                        <div class="rating">
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star-half"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] < 1.5 && $review->food['avg_rating'] > 1)
                                                        <div class="rating">
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] < 1 && $review->food['avg_rating'] > 0)
                                                        <div class="rating">
                                                            <span><i class="tio-star-half"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] == 1)
                                                        <div class="rating">
                                                            <span><i class="tio-star"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                        </div>
                                                    @elseif ($review->food['avg_rating'] == 0)
                                                        <div class="rating">
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                            <span><i class="tio-star-outlined"></i></span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                @if($review->customer)
                                                    <div class="min-w-160">
                                                        <h5 class="d-block text-hover-primary mb-1">{{Str::limit($review->customer['f_name']." ".$review->customer['l_name'])}} <i
                                                                class="tio-verified text-primary" data-toggle="tooltip" data-placement="top"
                                                                title="Verified Customer"></i></h5>
                                                        <span class="d-block font-size-sm gray-dark">{{Str::limit($review->comment)}}</span>
                                                    </div>
                                                @else
                                                    {{translate('messages.customer_not_found')}}
                                                @endif
                                            </div>
                                            <div class="mt-2">
                                                <form action="{{route('vendor.review-reply',[$review['id']])}}" method="POST">
                                                    @csrf
                                                    <textarea id="reply" name="reply" required class="form-control" cols="30" rows="3" placeholder="{{ translate('Write_your_reply_here') }}">{{ $review->reply ?? '' }}</textarea>
                                                    <div class="mt-3 btn--container justify-content-end">
                                                        <button class="btn btn-primary">{{ $review->reply ? translate('update_reply') : translate('send_reply')}}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                <table>
                    <tfoot>
                    {!! $reviews->links() !!}
                    </tfoot>
                </table>
            </div>
            <!-- End Table -->
        </div>
        <!-- End Card -->
    </div>


<!-- Review Customer Filter -->
<div id="Food-list_filter" class="custom-offcanvas d-flex flex-column justify-content-between"
    style="--offcanvas-width: 500px">
    <div>
        <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
            <div class="px-3 py-3 d-flex justify-content-between w-100">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Filter') }}</h2>
                </div>
                <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;
                </button>
            </div>
        </div>
        <div class="custom-offcanvas-body p-20">
            <form id="filter_form" action="{{ route('vendor.reviews') }}" method="GET">
                <input type="hidden" name="start_date" id="start_date_value" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" id="end_date_value" value="{{ request('end_date') }}">
            <div class="d-flex flex-column gap-20px">
                <div class="global-bg-box rounded p-xl-20 p-16">
                    <h5 class="mb-10px font-regular text-color font-normal not-scrolling-date">Select date Range</h5>
                    <button type="button" class="btn w-100 btn-white d-flex justify-content-between gap-10 align-items-center dateRange">
                        <span class="fs-14 text-title"></span>
                        <i class="tio-calendar-month"></i>
                    </button>
                </div>
                <div class="global-bg-box rounded p-xl-20 p-16">
                    <h5 class="mb-10px font-regular text-color font-normal">Ratting</h5>
                    <div class="bg-white rounded p-xl-3 p-2">
                        <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2">
                            <div class="col-sm-12">
                                <div class="form-group m-0">
                                    <div class="custom__radio d-flex align-items-center justify-content-between m-0">
                                        <label class="text-title m-0 lh-1" for="ratting5">
                                            {{translate('messages.5 Rating')}}
                                        </label>
                                        <input type="radio" class="custom--input" id="ratting5" name="rating" value="5" {{ request('rating') == '5' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group m-0">
                                    <label class="custom__radio d-flex align-items-center justify-content-between m-0">
                                        <div class="text-title m-0 lh-1">
                                            {{translate('messages.4+ Rating')}}
                                        </div>
                                        <input type="radio" class="custom--input" id="ratting4" name="rating" value="4" {{ request('rating') == '4' ? 'checked' : '' }}>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group m-0">
                                    <div class="custom__radio d-flex align-items-center justify-content-between m-0">
                                        <label for="ratting3" class="text-title m-0 lh-1">
                                            {{translate('messages.3+ Rating')}}
                                        </label>
                                        <input type="radio" class="custom--input" id="ratting3" name="rating" value="3" {{ request('rating') == '3' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group m-0">
                                    <div class="custom__radio d-flex align-items-center justify-content-between m-0">
                                        <label for="ratting2" class="text-title m-0 lh-1">
                                            {{translate('messages.2+ Rating')}}
                                        </label>
                                        <input type="radio" class="custom--input" id="ratting2" name="rating" value="2" {{ request('rating') == '2' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group m-0">
                                    <div class="custom__radio d-flex align-items-center justify-content-between m-0">
                                        <label for="ratting1" class="text-title m-0 lh-1">
                                            {{translate('messages.1+ Rating')}}
                                        </label>
                                        <input type="radio" class="custom--input" id="ratting1" name="rating" value="1" {{ request('rating') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="global-bg-box rounded p-xl-20 p-16">
                    <h5 class="mb-10px font-regular text-color font-normal">Reply status </h5>
                    <div class="bg-white rounded p-xl-3 p-2">
                        <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2">
                            <div class="col-sm-6 col-auto">
                                <div class="form-group m-0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input check-all" id="allready__replied" name="reply_status[]" value="replied"
                                            {{ request()->has('reply_status') ? (is_array(request('reply_status')) && in_array('replied', request('reply_status')) ? 'checked' : '') : '' }}>
                                        <label class="custom-control-label text-title" for="allready__replied">
                                            {{translate('messages.Already Replied')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-auto">
                                <div class="form-group m-0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="no__reply" name="reply_status[]" value="no_reply"
                                            {{ request()->has('reply_status') ? (is_array(request('reply_status')) && in_array('no_reply', request('reply_status')) ? 'checked' : '') : '' }}>
                                        <label class="custom-control-label text-title" for="no__reply">
                                            {{translate('messages.No Reply')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
    <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
        <button type="button" id="filter_reset" class="btn w-100 btn--reset offcanvas-close">Reset</button>
        <button type="submit" form="filter_form" class="btn w-100 btn--primary">Apply</button>
    </div>
</div>
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>
@endsection

@push('script_2')
    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                    datatable
                        .columns(1)
                        .search(this.value)
                        .draw();
                });
        });
    </script>
    <script>
        $(function () {
            const $button = $('.dateRange');
            const $label = $button.find('span');
            const placeholder = $label.data('placeholder') || 'Select Date';

            const currentUrl = new URL(window.location.href);
            const startDateUrl = currentUrl.searchParams.get('start_date');
            const endDateUrl = currentUrl.searchParams.get('end_date');

            const startDate = startDateUrl ? moment(startDateUrl, 'YYYY-MM-DD') : null;
            const endDate = endDateUrl ? moment(endDateUrl, 'YYYY-MM-DD') : null;

            if (startDate && endDate) {
                $label.text(`${startDate.format('DD MMM, YYYY')} - ${endDate.format('DD MMM, YYYY')}`);
            } else {
                currentUrl.searchParams.delete('start_date');
                currentUrl.searchParams.delete('end_date');
                window.history.replaceState({}, '', currentUrl.toString());
                $label.text(placeholder);
            }

            $button.daterangepicker({
                autoUpdateInput: false,
                startDate: startDate || undefined,
                endDate: endDate || undefined,
                locale: {
                    cancelLabel: 'Clear',
                    applyLabel: 'Confirm'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'Last 2 Months': [moment().subtract(2, 'months').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, function(start, end, label) {
                // callback when selecting a range
            });

            // ✅ Add custom class after showing
            $button.on('show.daterangepicker', function (ev, picker) {
                picker.container.addClass('mydaterangePicker');
                $('body').css('overflow', 'hidden');
                $('.custom-offcanvas-body')
                .addClass('not-scrolling-date')
                .css('overflow-y', 'hidden');
            });
            // ✅ Re-enable scrolling after hide
            $button.on('hide.daterangepicker', function (ev, picker) {
                $('body').css('overflow', '');
                $('.custom-offcanvas-body')
                    .removeClass('not-scrolling-date')
                    .css('overflow-y', 'visible');
            });

            $button.on('apply.daterangepicker', function (ev, picker) {
                $label.text(`${picker.startDate.format('DD MMM, YYYY')} - ${picker.endDate.format('DD MMM, YYYY')}`);

                $('#start_date_value').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date_value').val(picker.endDate.format('YYYY-MM-DD'));

                $(document).trigger('dateRangeUpdated', {
                    startDate: picker.startDate.format('YYYY-MM-DD'),
                    endDate: picker.endDate.format('YYYY-MM-DD')
                });
            });

            $button.on('cancel.daterangepicker', function (ev, picker) {
                $(document).trigger('dateRangeCleared');
            });
        });
    </script>
    <script>
        $(function(){
            // Reset filters to base route (no query params)
            const baseRoute = "{{ route('vendor.reviews') }}";
            $('#filter_reset').on('click', function(){
                window.location.href = baseRoute;
            });

            // When date range cleared, clear hidden inputs and label text
            $(document).on('dateRangeCleared', function(){
                $('#start_date_value').val('');
                $('#end_date_value').val('');
                $('.dateRange').find('span').text('Select Date');
            });
        });
    </script>
@endpush
