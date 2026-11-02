@extends('layouts.vendor.app')

@section('title', translate('messages.Happy_Hour'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.promotion._list_styles')
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">{{ translate('messages.Happy_Hour') }}</h1>
        </div>

        <div class="card">
            <div class="card-header border-0 py-2 search--button-wrapper">
                <h5 class="card-title">
                    {{ translate('messages.Happy_Hour_List') }}
                    <span class="badge badge-soft-dark ml-2">{{ $happyHours->total() }}</span>
                </h5>
                <form id="search-form" action="{{ url()->current() }}" method="GET">
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" value="{{ request('search') }}"
                               class="form-control" placeholder="{{ translate('messages.search_here') }}" aria-label="Search">
                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                    </div>
                </form>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('messages.SL') }}</th>
                        <th>{{ translate('messages.Title') }}</th>
                        <th>{{ translate('messages.Image') }}</th>
                        <th>{{ translate('messages.Discount') }}</th>
                        <th>{{ translate('messages.Duration') }}</th>
                        <th>{{ translate('messages.Min_Order_Amount') }}</th>
                        <th>{{ translate('messages.Offer_Schedule') }}</th>
                        <th>{{ translate('messages.status') }}</th>
                        <th class="text-center">{{ translate('messages.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @include('vendor-views.happy-hour.partials._offer_table')
                    </tbody>
                </table>

                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>{!! $happyHours->links() !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail drawer, filled on demand by the eye action and the title link. --}}
    <div id="offcanvas__happy_hour_detail" class="custom-offcanvas d-flex flex-column justify-content-between"
         style="--offcanvas-width: 620px">
        <div id="happy-hour-detail-body" class="h-100"></div>
    </div>

    {{-- Opened by the warning icon beside any happy hour this restaurant runs but customers
         are not being given. --}}
    @include('partials.promotion._visibility_modal', [
        'promotionLabel' => translate('messages.Happy_Hour'),
    ])

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin/js/offcanvas.js') }}"></script>
    <script>
        "use strict";

        // offcanvas.js binds .offcanvas-close on ready, so it never sees the drawer's own
        // button - that markup arrives later over ajax. Close here instead, delegated.
        $(document).on('click', '.offcanvas-close, #offcanvasOverlay', function () {
            $('.custom-offcanvas').removeClass('open');
            $('#offcanvasOverlay').removeClass('show');
        });

        $(document).on('click', '.offer-detail', function () {
            const target = $('#offcanvas__happy_hour_detail');

            $('#happy-hour-detail-body').html(
                '<div class="d-flex align-items-center justify-content-center h-100">' +
                '<div class="spinner-border text--primary" role="status"></div></div>'
            );
            target.addClass('open');
            $('#offcanvasOverlay').addClass('show');

            $.get($(this).data('url'), function (html) {
                $('#happy-hour-detail-body').html(html);
            }).fail(function () {
                toastr.error('{{ translate('messages.something_went_wrong') }}');
                target.removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
            });
        });

        $(document).on('click', '.toggle-description', function () {
            const wrapper = $(this).closest('p');
            wrapper.find('.description-short, .description-full').toggleClass('d-none');
            $(this).text($(this).text().trim() === '{{ translate('messages.See_More') }}'
                ? '{{ translate('messages.See_Less') }}'
                : '{{ translate('messages.See_More') }}');
        });
    </script>
@endpush
