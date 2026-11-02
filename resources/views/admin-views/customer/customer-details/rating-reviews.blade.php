@extends('layouts.admin.app')

@section('title', translate('Customer_list'))

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
                        {{ translate('Customer Details') }} <span class="gray-dark">#{{ $customer->id }}</span>
                    </h1>
                </div>
            </div>
        </div>
        @include('admin-views.customer.partials._user_details_urls')
        <div class="card">
            <div class="card-header py-xl-20 flex-wrap gap-2 border-0">
                <h5 class="card-header-title">{{ translate('messages.Ratings & Reviews') }}
                    <span class="badge badge-soft-secondary" id="itemCount">{{ $reviews->total() }}</span>

                </h5>
                <div class="search--button-wrapper flex-xxs-nowrap">
                    <form>
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                value="{{ request()?->search ?? null }}" placeholder="{{ translate('Search By Food Name') }}"
                                aria-label="Search" required>
                            <button type="submit" class="btn btn--secondary">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                        <!-- End Search -->
                    </form>
                </div>
            </div>
            <!-- Table -->
             <div class="px-xxl-20 px-3">
                 <div class="table-responsive datatable-custom pt-0">
                     <table id=""
                         class="table table-borderless table-thead-borderless table-nowrap table-align-middle card-table">
                         <thead class="global-bg-box">
                             <tr>
                                 <th class="py-3 fs-14 text-capitalize">{{ translate('messages.sl') }}</th>
                                 <th class="py-3 fs-14 text-capitalize">{{ translate('messages.Item') }}</th>
                                 <th class="py-3 fs-14 text-capitalize">{{ translate('messages.Rating & Review') }}</th>
                                 <th class="py-3 fs-14 text-capitalize">{{ translate('messages.Date') }}</th>
                                 <th class="py-3 fs-14 text-capitalize">{{ translate('messages.Restaurant Reply') }}</th>
                                 <th class="py-3 fs-14 text-capitalize text-center">{{ translate('messages.status') }}</th>
                                 <th class="py-3 fs-14 text-capitalize text-center">{{ translate('messages.action') }}</th>
                             </tr>
                         </thead>
                         <tbody>
                             @forelse ($reviews as $key => $review)
                                 <tr>
                                     <td>{{ $key + $reviews->firstItem() }}</td>
                                     <td>
                                        <div class="d-flex text-dark align-items-sm-center gap-10">
                                            <a href="{{$review->food ?  route('admin.food.view', $review->food_id) : '#' }}" target="_blank" class="w-40 min-w-40">
                                                <img width="40" height="40"
                                                    src="{{ $review?->food?->image_full_url ?? dynamicAsset('assets/admin/img/160x160/img2.jpg') }}"
                                                    alt="img" class="rounded">
                                            </a>
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 flex-grow-1">
                                                <div>
                                                    <h5 class="mb-0 text-color font-medium max-w-220px min-w-170px text-wrap line--limit-1">
                                                        <a href="{{$review->food ?  route('admin.food.view', $review->food_id) : 'javascript:void(0)' }}"  target="_blank" class="text-dark">
                                                            {{ Str::limit($review->food?->name, 40, '...') ?? translate('food_not_found') }}
                                                        </a>
                                                    </h5>

                                                    <a class="fs-12 text-secondary" target="_blank" href="{{ route('admin.order.details', $review->order_id) }}">
                                                            {{ translate('Order ID') }}:
                                                            {{ $review->order_id }}
                                                    </a>

                                                </div>
                                            </div>
                                        </div>

                                     </td>
                                     <td>
                                         <div class="m-0 fs-12 font-regular gray-dark fs-14 mb-1">
                                             <i class="tio-star brand-base-clr"></i> {{ $review->rating }}
                                         </div>
                                         <div class="tooltip--custom">
                                            <div class="max-w-400px min-w-170px">
                                                <p class="line-limit-2 gray-dark fs-14 text-wrap">
                                                    <span class="d-inline" data-toggle="tooltip"
                                                    data-placement="top"
                                                    data-html="true"
                                                    title="{{ $review->comment }}">
                                                        {{ Str::limit($review->comment, 150, '...') }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                     </td>
                                     <td class="text-uppercase text-title fs-14">
                                         {{ \App\CentralLogics\Helpers::time_date_format($review->created_at) }}
                                     </td>
                                     <td>


                                         @if ($review->reply)
                                             <div class="tooltip--custom">
                                                 <p class="line-limit-2 gray-dark fs-14 text-wrap max-w-400px"
                                                     data-toggle="tooltip" data-placement="top" data-html="true"
                                                     data-title="
                                          <div class='text-left'>
                                              {{ $review->reply }}
                                          </div>  ">

                                                     {{ Str::limit($review->reply, 50, '...') }}
                                                 </p>
                                             </div>
                                         @else
                                             {{ translate('N/A') }}
                                         @endif


                                     </td>
                                     <td class="text-center">

                                         <label class="toggle-switch mx-auto toggle-switch-sm"
                                             for="reviewCheckbox{{ $review->id }}">
                                             <input type="checkbox" data-id="status-{{ $review['id'] }}"
                                                 data-message="{{ $review->status ? translate('messages.you_want_to_hide_this_review_for_customer') : translate('messages.you_want_to_show_this_review_for_customer') }}"
                                                 class="toggle-switch-input status_form_alert"
                                                 id="reviewCheckbox{{ $review->id }}" {{ $review->status ? 'checked' : '' }}>
                                             <span class="toggle-switch-label">
                                                 <span class="toggle-switch-indicator"></span>
                                             </span>
                                         </label>
                                         <form
                                             action="{{ route('admin.food.reviews.status', [$review['id'], $review->status ? 0 : 1]) }}"
                                             method="get" id="status-{{ $review['id'] }}">
                                         </form>


                                     </td>
                                     <td>
                                         <div class="btn--container justify-content-center">
                                             <a class="btn btn-sm btn--warning btn-outline-warning action-btn offcanvas-trigger data-info-show"
                                              data-target="#offcanvas__customBtn3"
                                              data-url="{{ route('admin.reviews.details', [$review->id]) }}"
                                              data-id="{{ $review->id }}"
                                                  href="javascript:void(0)">
                                                 <i class="tio-visible-outlined"></i>
                                             </a>


                                         </div>
                                     </td>
                                 </tr>
                             @empty
                                 <tr>
                                     <td class="py-lg-5" colspan="7">
                                         <div class="py-md-5 d-flex align-items-center justify-content-center w-100 h-100">
                                             <div class="text-center pt-80 pb-80 text-gray1 fs-16">
                                                 <img src="{{ dynamicAsset('assets/admin/img/emty-review.svg') }}"
                                                     alt="no" class="d-block mb-10px mx-auto">
                                                 {{ translate('No Ratting & Reviews') }}
                                             </div>
                                         </div>
                                     </td>
                                 </tr>
                             @endforelse



                         </tbody>
                     </table>

                 </div>
             </div>
              <div class="card-footer p-0 border-0">

                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $reviews->appends($_GET)->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
        <div id="offcanvas__customBtn3" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100">  </div>
    </div>

@endsection

@push('script_2')
<script src="{{dynamicAsset('assets/admin/js/view-pages/offcanvas-edit.js')}}"></script>
    <script>
        "use strict";
        $(document).on('ready', function() {

            $('.tooltip--custom [data-toggle="tooltip"]').tooltip({
                html: true,
                container: 'body',
                template: '<div class="tooltip tooltip-custom" role="tooltip">' +
                    '<div class="arrow"></div>' +
                    '<div class="tooltip-inner"></div>' +
                    '</div>'
            });
        });




        $(".status_form_alert").on("click", function(e) {
            const id = $(this).data('id');
            const message = $(this).data('message');
            e.preventDefault();
            Swal.fire({
                title: '{{ translate('messages.Are_you_sure_?') }}',
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
                    $('#' + id).submit()
                }
            })
        })
        initSeeMoreToggle();
    </script>
@endpush
