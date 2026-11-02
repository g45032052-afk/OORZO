@php use App\CentralLogics\Helpers;use App\Models\BusinessSetting;use App\Models\RefundReason; @endphp
@extends('layouts.admin.app')

@section('title', translate('Refund_Settings'))

@section('content')
    <div class="content container-fluid">
         <!-- Page Header -->
        <div class="page-header pb-0">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h1 class="mb-0">{{ translate('messages.business_setup') }}</h1>
                <div class="d-flex flex-wrap justify-content-end align-items-center flex-grow-1">
                    <div class="blinkings active">
                        <i class="tio-info text-gray1 fs-16"></i>
                        <div class="business-notes">
                            <h6><img src="{{dynamicAsset('assets/admin/img/notes.png')}}" alt=""> {{translate('Note')}}</h6>
                            <div>
                                {{translate('Click_on_the_Add_Now_button_to_add_a_refund_reason_to_the_list')}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin-views.business-settings.partials.nav-menu')
        </div>
        <div class="card card-body p-12 p-xxl-20 mb-20" id="refund_request_mode">
            <form action="{{ route('admin.refund.refund_mode') }}" id="refund_mode_form" method="get"></form>
            <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap mb-20">
                <div class="flex-grow-1">
                    <h4 class="mb-1">{{ translate('messages.Refund Request Mode') }}</h4>
                    <p class="fs-12 mb-0">{{ translate('If enabled, Customer can request for refund for the orders they have placed') }}</p>
                </div>
                <div class="flex-grow-1 max-w-360 min-w-220">
                    @php($config = $refund_active_status?->value)
                    <label
                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control m-0 maintainance-mode-toggle-bar">
                        <span class="pr-1 d-flex align-items-center switch--label">
                            <span>{{ translate('messages.Status') }}</span>
                        </span>
                        <label class="switch m-0">
                            <input type="checkbox" class="status dynamic-checkbox" id="refund_mode"
                                    data-id="refund_mode"
                                    data-type="status"
                                    data-image-on='{{dynamicAsset('assets/admin/img/modal')}}/mail-success.png'
                                    data-image-off="{{dynamicAsset('assets/admin/img/modal')}}/mail-warning.png"
                                    data-title-on="{{translate('Important!')}}"
                                    data-title-off="{{translate('Warning!')}}"
                                    data-text-on="<p>{{translate('By_turning_on_refund_request_mode,_customer_can_place_refund_requests.')}}</p>"
                                    data-text-off="<p>{{translate('By_turning_off_refund_request_mode,_customer_can_not_place_refund_requests')}}</p>"
                                {{ isset($config) && $config ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </label>
                </div>
            </div>
            <div class="fs-12 text-dark px-3 py-2 rounded bg-info mb-20" style="--bs-bg-opacity: 0.1;">
                <div class="d-flex gap-2 ">
                    <span class="text-info lh-1 fs-14">
                        <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                    </span>
                    <span class="font-semibold">
                        {{ translate('messages.Here you can setup the channels how you want to notify the admin about the action / cases mentioned below in the list') }}
                    </span>
                </div>
                <ul>
                    <li>
                        {{ translate('messages.All Refund Request you can see & manage them from') }}
                        <a href="{{ route('admin.refund.refund_attr', ['requested']) }}" class="font-semibold text-info text-underline">{{ translate('messages.New Refund Request') }}</a>
                        {{ translate('messages.page.') }}
                    </li>
                    <li>
                        {{ translate('messages.Refunds will be automatically processed by adding funds to the customer\'s wallet. If the wallet is unavailable, the admin will need to manually manage the refund') }}
                    </li>
                </ul>
            </div>
        </div>

        <div class="card" id="refund_reason">
            <div class="card-header">
                <div>
                    <h3 class="mb-1">{{ translate('messages.Refund_Reason') }}</h3>
                    <p class="fs-12 mb-0">{{ translate('messages.Add the Refund Reasons to the List for selection by the customers when they are requesting for refund') }}</p>
                </div>
            </div>
            <div class="card-body">
                <div class="bg-light rounded-10 p-12 p-xxl-20 mb-20">
                    <form action="{{route('admin.refund.refund_reason')}}" method="post">
                        @csrf
                        @if($language)
                            <ul class="nav  nav--tabs nav--tabs-border mb-3 w-100 flex-nowrap text-nowrap overflow-x-auto">
                                <li class="nav-item">
                                    <a class="nav-link lang_link1 active"
                                    href="#"
                                    id="default-link1">{{ translate('Default') }}</a>
                                </li>
                                @foreach (json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link1"
                                        href="#"
                                        id="{{ $lang }}-link1">{{ Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="row align-items-end g-3">


                            <div class="col-12 lang_form1 default-form1">
                                <label for="reason" class="form-label">{{translate('Reason')}} ({{ translate('Default') }}
                                    )</label>
                                <input id="reason" type="text" class="form-control h--45px" name="reason[]"
                                    maxlength="191" placeholder="{{ translate('Ex:_Item_is_Broken') }}">
                                <input type="hidden" name="lang[]" value="default">
                            </div>

                            @if ($language)
                                @foreach(json_decode($language) as $lang)
                                    <div class="col-12 d-none lang_form1" id="{{$lang}}-form1">
                                        <label for="reason{{$lang}}" class="form-label">{{translate('Reason')}}
                                            ({{strtoupper($lang)}})</label>
                                        <input id="reason{{$lang}}" type="text" class="form-control h--45px" name="reason[]"
                                            maxlength="191" placeholder="{{ translate('Ex:_Item_is_Broken') }}">
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                    </div>
                                @endforeach
                            @endif


                            <div class="col-12">
                                <div class="btn--container justify-content-end">
                                    <button type="reset" class="btn btn--secondary min-w-120">{{ translate('messages.Reset') }} </button>
                                    <button type="submit" class="btn btn--primary h--45px min-w-120">{{translate('messages.Save')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <h4 class="mb-0">
                            {{translate('messages.Refund_Reason_List')}}
                        </h4>
                        <form action="{{route('admin.refund.refund_settings')}}" class="input--group input-group input-group-merge input-group-flush w-18rem">
                            <input type="search" name="search" value="{{ request()?->search ?? null }}"
                                    class="form-control" placeholder="{{ translate('messages.Search_Here') }}"
                                    aria-label="{{translate('messages.Search_Here')}}">
                            <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card-body p-0">
                        <div class="table-responsive datatable-custom">
                            <table id="columnSearchDatatable"
                                   class="table table-borderless table-thead-bordered table-align-middle">
                                <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{ translate('messages.sl') }}</th>
                                    <th class="border-0">{{translate('messages.Reason')}}</th>
                                    <th class="border-0 text-center">{{translate('messages.status')}}</th>
                                    <th class="border-0 text-center">{{translate('messages.action')}}</th>
                                </tr>
                                </thead>

                                <tbody id="table-div">
                                @foreach($reasons as $key=>$reason)
                                    <tr>
                                        <td>{{$key+$reasons->firstItem()}}</td>

                                        <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{Str::limit($reason->reason, 25,'...')}}
                                    </span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center align-items-center">
                                                <label class="toggle-switch toggle-switch-sm"
                                                       for="stocksCheckbox{{$reason->id}}">
                                                    <input type="checkbox"
                                                           data-url="{{route('admin.refund.reason_status',[$reason['id'],$reason->status?0:1])}}"
                                                           class="toggle-switch-input redirect-url"
                                                           id="stocksCheckbox{{$reason->id}}" {{$reason->status?'checked':''}}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn btn-sm btn--primary btn-outline-primary action-btn edit-reason"
                                                   title="{{ translate('messages.edit') }}"
                                                   data-toggle="modal" data-target="#add_update_reason_{{$reason->id}}"
                                                ><i class="tio-edit"></i>
                                                </a>

                                                <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                                   href="javascript:"
                                                   data-id="refund_reason-{{$reason['id']}}"
                                                   data-message="{{ translate('Want to delete this refund reason ?') }}"

                                                   title="{{translate('messages.delete')}}">
                                                    <i class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{route('admin.refund.reason_delete',[$reason['id']])}}"
                                                      method="post" id="refund_reason-{{$reason['id']}}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Modal -->
                                    <div class="modal fade" id="add_update_reason_{{$reason->id}}" tabindex="-1"
                                         role="dialog" aria-labelledby="exampleModalLabel"
                                         aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="exampleModalLabel">{{ translate('messages.Refund_Reason_Update') }}</label></h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('admin.refund.reason_edit') }}" method="post">
                                                    <div class="modal-body">
                                                        @csrf
                                                        @method('put')

                                                        @php($reason = $reason)
                                                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                                            <ul class="nav nav-tabs nav--tabs mb-3 border-0">
                                                                <li class="nav-item">
                                                                    <a class="nav-link update-lang_link add_active active"
                                                                    href="#"

                                                                    id="default-link">{{ translate('Default') }}</a>
                                                                </li>
                                                                @if($language)
                                                                    @foreach (json_decode($language) as $lang)
                                                                        <li class="nav-item">
                                                                            <a class="nav-link update-lang_link"
                                                                            href="#"
                                                                            data-reason-id="{{$reason->id}}"
                                                                            id="{{ $lang }}-link">{{ Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                                        </li>
                                                                    @endforeach
                                                                @endif
                                                            </ul>
                                                        </div>
                                                        <input type="hidden" name="reason_id" value="{{$reason->id}}"/>

                                                        <div class="form-group mb-3 add_active_2  update-lang_form"
                                                             id="default-form_{{$reason->id}}">
                                                            <label for="reason" class="form-label">{{translate('Reason')}}
                                                                ({{translate('messages.default')}}) </label>
                                                            <input id="reason" class="form-control" name='reason[]'
                                                                   value="{{$reason?->getRawOriginal('reason')}}"
                                                                   type="text">
                                                            <input type="hidden" name="lang1[]" value="default">
                                                        </div>
                                                        @if($language)
                                                            @forelse(json_decode($language) as $lang)
                                                                    <?php
                                                                    if ($reason?->translations) {
                                                                        $translate = [];
                                                                        foreach ($reason?->translations as $t) {
                                                                            if ($t->locale == $lang && $t->key == "reason") {
                                                                                $translate[$lang]['reason'] = $t->value;
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>
                                                                <div class="form-group mb-3 d-none update-lang_form"
                                                                     id="{{$lang}}-langform_{{$reason->id}}">
                                                                    <label for="reason{{$lang}}"
                                                                           class="form-label">{{translate('Reason')}}
                                                                        ({{strtoupper($lang)}})</label>
                                                                    <input id="reason{{$lang}}" class="form-control"
                                                                           name='reason[]'
                                                                           value="{{ $translate[$lang]['reason'] ?? null }}"
                                                                           type="text">
                                                                    <input type="hidden" name="lang1[]" value="{{$lang}}">
                                                                </div>
                                                            @empty
                                                            @endforelse
                                                        @endif

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">{{ translate('Close') }}</button>
                                                        <button type="submit"
                                                                class="btn btn-primary">{{ translate('Save_changes') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </tbody>
                            </table>
                            @if(count($reasons) === 0)
                                <div class="empty--data">
                                    <img width="70" src="{{dynamicAsset('assets/admin/img/no-data.png')}}" alt="public">
                                    <p class="fs-16 mt-3">
                                        {{ translate('messages.No_Refund_Reason_List') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer pt-0 border-0">
                            <div class="page-area px-4 pb-3">
                                <div class="d-flex align-items-center justify-content-end">
                                    <div>
                                        {!! $reasons->links() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                            <!-- Guidline Offcanvas Btn -->
<div class="d-flexgap-2 w-40px gap-2 bg-white position-fixed end-0 translate-middle-y pointer view-guideline-btn flex-column pt-3 px-2 justify-content-center offcanvas-trigger"
    data-toggle="offcanvas" data-target="#offcanvasSetupGuide">
    <span class="arrow bg-primary py-1 px-2 text-white rounded fs-12"><i class="tio-share-vs"></i></span>
    <span class="view-guideline-btn-text text-dark font-semibold pb-2 text-nowrap">
        {{ translate('View_Guideline') }}
    </span>
</div>

<!-- Guidline Offcanvas -->
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
    <div class="custom-offcanvas" tabindex="-1" id="offcanvasSetupGuide" aria-labelledby="offcanvasSetupGuideLabel"
        style="--offcanvas-width: 500px">
            <div>
                <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
                    <h3 class="mb-0">{{ translate('messages.Refund Guideline') }}</h3>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
                </div>
                <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
                    <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#refund_request_mode_guide"
                                aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Refund Request Mode') }}</span>
                            </button>
                            <a href="#refund_request_mode"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                        </div>
                        <div class="collapse show mt-3" id="refund_request_mode_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3">{{translate('messages.Refund Request Mode')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.This option enables customers to submit refund requests for their orders. When this option is turned OFF, customers will not be able to request a refund.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#refund_reason_guide"
                                aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Refund Reason') }}</span>
                            </button>
                            <a href="#refund_reason"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                        </div>
                        <div class="collapse mt-3" id="refund_reason_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3">{{translate('messages.Refund Reason')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.This section allows the admin to manage refund cancellation reasons. Admin can create and configure cancellation reasons, control their active status. These reasons will be displayed to the customer for selection when they attempt to request an order.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{dynamicAsset('assets/admin/js/view-pages/business-settings-refund-page.js')}}"></script>
    <script>
        'use strict'
        $(document).ready(function () {
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'), {
                select: {
                    style: 'multi',
                    classMap: {
                        checkAll: '#datatableCheckAll',
                        counter: '#datatableCounter',
                        counterInfo: '#datatableCounterInfo'
                    }
                },
                language: {
                    zeroRecords: '<div class="text-center p-4">' +
                        '<img class="mb-3" src="{{dynamicAsset("assets/admin/svg/illustrations/sorry.svg")}}" alt="Image Description" style="width: 7rem;">' +
                        '<p class="mb-0">{{ translate("No data to show") }}</p>' +
                        '</div>'
                },
                paging: false,
                isResponsive: false,
                columnDefs: [{
                    targets: [2, 3],
                    orderable: false
                }]
            });
        })

        $(".collapse-div-toggler").on('change', function () {
            if ($(this).val() == '0') {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideDown();
            } else {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideUp();
            }
        });

        $(window).on('load', function () {
            $('.collapse-div-toggler').each(function () {
                if ($(this).prop('checked') == true && $(this).val() == '0') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').show();
                } else if ($(this).prop('checked') == true && $(this).val() == '1') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').hide();
                }
            });
        })


        $('.offcanvas-close-btn').on('click', function (e) {
            e.preventDefault();
            $('.custom-offcanvas').removeClass('open');
            $('#offcanvasOverlay').removeClass('show');
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top - 100
            }, 500);
        });
    </script>
@endpush
