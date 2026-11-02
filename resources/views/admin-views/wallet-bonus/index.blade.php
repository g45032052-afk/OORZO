@extends('layouts.admin.app')

@section('title',translate('messages.bonuses'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{dynamicAsset('assets/admin/img/wallet-setup.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.wallet_bonus_setup')}}
                </span>
            </h1>
            <!-- <div class="text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{translate('See_how_it_works!')}}</strong>
                <div class="blinkings">
                    <i class="tio-info text-gray1 fs-16"></i>
                </div>
            </div> -->
        </div>

        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
        @php($language = $language->value ?? null)
        @php($default_lang = str_replace('_', '-', app()->getLocale()))
        <!-- End Page Header -->
        <div class="row g-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.customer.wallet.bonus.store')}}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="global-bg-box rounded p-xxl-20 p-3 mb-20">
                                        @if ($language)
                                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                            <ul class="nav nav-tabs mb-3">
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link active"
                                                    href="#"
                                                    id="default-link">{{translate('messages.default')}}</a>
                                                </li>
                                                @foreach (json_decode($language) as $lang)
                                                    <li class="nav-item">
                                                        <a class="nav-link lang_link"
                                                            href="#"
                                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="lang_form" id="default-form">
                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label"
                                                            for="default_title">{{ translate('messages.Bonus_Title') }}
                                                            ({{ translate('messages.Default') }})
                                                        </label>
                                                        <input type="text" maxlength="50" name="title[]" id="default_title"
                                                            class="form-control" placeholder="{{ translate('messages.Ex:_EID_Dhamaka') }}"

                                                             >
                                                            <div class="d-flex justify-content-end mt-1">
                                                                <span class="text-body-light fs-12">0/50</span>
                                                            </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label"
                                                            for="default_description">{{ translate('messages.Short_Description') }}
                                                            ({{ translate('messages.Default') }})
                                                        </label>
                                                        <input maxlength="100" type="text" name="description[]" id="default_description"
                                                            class="form-control" placeholder="{{ translate('messages.Ex:_EID_Dhamaka') }}"

                                                             >
                                                            <div class="d-flex justify-content-end mt-1">
                                                                <span class="text-body-light fs-12">0/100</span>
                                                            </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                        @foreach (json_decode($language) as $lang)
                                            <div class="d-none lang_form"
                                                id="{{ $lang }}-form">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="input-label"
                                                                for="{{ $lang }}_title">{{ translate('messages.Bonus_Title') }}
                                                                ({{ strtoupper($lang) }})
                                                            </label>
                                                            <input type="text" maxlength="50" name="title[]" id="{{ $lang }}_title"
                                                                class="form-control" placeholder="{{ translate('messages.Ex:_EID_Dhamaka') }}"
                                                                 >
                                                                 <div class="d-flex justify-content-end mt-1">
                                                                    <span class="text-body-light fs-12">0/50</span>
                                                                </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="input-label"
                                                                for="{{ $lang }}_description">{{ translate('messages.Short_Description') }}
                                                                ({{ strtoupper($lang) }})
                                                            </label>
                                                            <input type="text" maxlength="100" name="description[]" id="{{ $lang }}_description"
                                                                class="form-control" placeholder="{{ translate('messages.Ex:_EID_Dhamaka') }}"
                                                                 >
                                                                 <div class="d-flex justify-content-end mt-1">
                                                                    <span class="text-body-light fs-12">0/50</span>
                                                                </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            </div>
                                        @endforeach
                                        @else
                                            <div id="default-form">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="exampleFormControlInput1">{{ translate('messages.Bonus_Title') }} ({{ translate('messages.default') }})</label>
                                                    <input type="text" maxlength="255" name="title[]" class="form-control"
                                                    placeholder="{{ translate('messages.Ex:_EID_Dhamaka') }}">
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="global-bg-box rounded p-xxl-20 p-3 mb-20">
                                        <div class="row g-4">
                                            <div class="col-md-6 col-lg-6">
                                                <div class="form-group mb-0">
                                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.Bonus_Amount')}}
                                                        <span  class="d-none" id='cuttency_symbol'>
                                                            ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                        </span>
                                                        <span id="percentage">(%)</span>
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="{{ translate('Set_the_bonus_amount/percentage_a_customer_will_receive_after_adding_money_to_his_wallet.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <div class="d-flex align-items-center gap-0 border rounded bg-white min-h-41">
                                                        <input type="number" step="0.01" min="1" max="999999999999.99"  placeholder="{{ translate('messages.Ex:_100') }}"  name="bonus_amount" id="bonus_amount" class="form-control border-0 outline-0" required>
                                                        <select name="bonus_type" class="custom-select section-bg1 border-0 w-15p" id="bonus_type" required>
                                                            <option value="percentage">(%)</option>
                                                            <option value="amount">($)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-6">
                                                <div class="form-group mb-0">
                                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.Minimum_Add_Money_Amount')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                        <span
                                                        class="input-label-secondary text--title" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ translate('Set_the_minimum_add_money_amount_for_a_customer_to_be_eligible_for_the_bonus.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                    </label>
                                                    <input type="number" step="0.01" min="1" max="999999999999.99" placeholder="{{ translate('messages.Ex:_10') }}" name="minimum_add_amount" id="minimum_add_amount" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-6">
                                                <div class="form-group mb-0">
                                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.Maximum_Bonus')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                        <span
                                                        class="input-label-secondary text--title" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ translate('Set_the_maximum_bonus_amount_a_customer_can_receive_for_adding_money_to_his_wallet.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>

                                                    </label>
                                                    <input type="number" step="0.01" min="1" max="999999999999.99"  placeholder="{{ translate('messages.Ex:_1000') }}" name="maximum_bonus_amount" id="maximum_bonus_amount" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-6">
                                                <div class="form-group mb-0">
                                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.start_date')}}</label>
                                                    <input type="date" name="start_date" class="form-control" id="date_from" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-6">
                                                <div class="form-group mb-0">
                                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.expire_date')}}</label>
                                                    <input type="date" name="end_date" class="form-control" id="date_to" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">{{translate('messages.bonus_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$bonuses->total()}}</span></h5>
                            <form id="dataSearch" class="search-form min--270">
                            @csrf
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch" type="search" name="search" class="form-control" placeholder="{{ translate('messages.Ex_:_Search_by_bonus_title') }}" aria-label="{{translate('messages.search_here')}}">
                                    <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom" id="table-div">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,

                                "entries": "#datatableEntries",
                                "isResponsive": false,
                                "isShowPaging": false,
                                "paging":false
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{translate('sl')}}</th>
                                <th class="border-0">{{translate('messages.bonus_title')}}</th>
                                <th class="border-0">{{translate('messages.bonus_info')}}</th>
                                <th class="border-0">{{translate('messages.bonus_amount')}}</th>
                                <th class="border-0">{{translate('messages.started_on')}}</th>
                                <th class="border-0">{{translate('messages.expires_on')}}</th>
                                <th class="border-0">{{translate('messages.last_modified')}}</th>
                                <th class="border-0">{{translate('messages.activity_status')}}</th>
                                <th class="border-0">{{translate('messages.status')}}</th>
                                <th class="border-0 text-center">{{translate('messages.action')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($bonuses as $key=>$bonus)
                                <tr>
                                    <td>{{$key+$bonuses->firstItem()}}</td>
                                    <td>
                                    <span class="d-block font-size-sm text-body max-w-215px min-w-170px line--limit-1">
                                        {{Str::limit($bonus['title'],25,'...')}}
                                    </span>
                                    </td>
                                    <td>{{ translate('messages.minimum_add_amount') }} -    {{\App\CentralLogics\Helpers::format_currency($bonus['minimum_add_amount'])}} <br>
                                        {{ translate('messages.maximum_bonus') }} - {{\App\CentralLogics\Helpers::format_currency($bonus['maximum_bonus_amount'])}}</td>
                                    <td>{{$bonus->bonus_type == 'amount'?\App\CentralLogics\Helpers::format_currency($bonus['bonus_amount']): $bonus['bonus_amount'].' (%)'}}</td>
                                   <?php
                                        $now = \Carbon\Carbon::now();
                                        $start = \Carbon\Carbon::parse($bonus->start_date);
                                        $end = \Carbon\Carbon::parse($bonus->end_date);
                                    ?>

                                    <td>{{ $start->format('d M Y') }}</td>
                                    <td>{{ $end->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bonus->updated_at)->format('d M Y') }}</td>

                                    <td>
                                        @if ($now->lt($start))
                                            <span class="badge badge-soft-primary">
                                                {{ translate('messages.Upcoming') }}
                                            </span>

                                        @elseif ($now->between($start, $end))
                                            <span class="badge badge-soft-success">
                                                {{ translate('messages.Ongoing') }}
                                            </span>

                                        @else
                                            <span class="badge badge-soft-danger">
                                                {{ translate('messages.Expired') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="bonusCheckbox{{$bonus->id}}">
                                            <input type="checkbox" data-url="{{route('admin.customer.wallet.bonus.status',[$bonus['id'],$bonus->status?0:1])}}" class="toggle-switch-input redirect-url" id="bonusCheckbox{{$bonus->id}}" {{$bonus->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">

                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.customer.wallet.bonus.update',[$bonus['id']])}}"title="{{translate('messages.edit_bonus')}}"><i class="tio-edit"></i>
                                            </a>
                                            {{-- <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="bonus-{{$bonus['id']}}" data-message="{{ translate('Want to delete this bonus ?') }}" title="{{translate('messages.delete_bonus')}}"><i class="tio-delete-outlined"></i>
                                            </a> --}}
                                            <div class="btn action-btn btn--danger btn-outline-danger" data-toggle="modal"
                                                data-target="#confirmation_modal_customer" data-form-id="bonus-{{ $bonus['id'] }}"><i class="tio-delete-outlined"></i></div>
                                            <form action="{{route('admin.customer.wallet.bonus.delete',[$bonus['id']])}}"
                                            method="post" id="bonus-{{$bonus['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if(count($bonuses) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $bonuses->links() !!}
                        </div>
                        @if(count($bonuses) === 0)
                        <div class="empty--data">
                            <img src="{{dynamicAsset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                            <h5>
                                {{translate('no_data_found')}}
                            </h5>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>
    <div class="modal fade" id="how-it-works">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="single-item-slider owl-carousel">
                        <div class="item">
                            <div class="mb-20">
                                <div class="text-center">
                                    <img src="{{dynamicAsset('assets/admin/img/image_127.png')}}" alt="" class="mb-20">
                                    <h5 class="modal-title">{{translate('Wallet_bonus_is_only_applicable_when_a_customer_add_fund_to_wallet_via_outside_payment_gateway_!')}}</h5>
                                </div>
                                <ul>
                                    <li>
                                        {{ translate('Customer_will_get_extra_amount_to_his_/_her_wallet_additionally_with_the_amount_he_/_she_added_from_other_payment_gateways._The_bonus_amount_will_be_deduct_from_admin_wallet_&_will_consider_as_admin_expense.') }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="slide-counter"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


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
                    <h3 class="mb-0">{{ translate('messages.Wallet Bonus Setup Guideline') }}</h3>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
                </div>
                <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
                    <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#maintenance_mode_guide" aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Wallet Bonus Setup ') }}</span>
                            </button>
                            {{-- <a href="#maintenance_mode"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a> --}}
                        </div>
                        <div class="collapse show mt-3" id="maintenance_mode_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3">{{translate('Wallet Bonus Setup')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.Use this section to create and manage bonus offers customers receive when they add money to their wallet.') }}
                                    </p>
                                    <p class="fs-12 mb-3">
                                        <strong>{{ translate('messages.Bonus Title & Description') }} <br>
                                        </strong> {{ translate('messages. Add a clear title and a short description to explain the bonus offer') }}
                                    </p>
                                    <p class="fs-12 mb-3">
                                        <strong>{{ translate('messages.Bonus Type & Amount') }} <br>
                                        </strong> {{ translate('messages.Choose whether the bonus is a percentage or a fixed amount, then enter the bonus value.') }}
                                    </p>
                                    <p class="fs-12 mb-3">
                                        <strong>{{ translate('messages.Minimum Add Money Amount') }} <br>
                                        </strong> {{ translate('messages.Set the minimum wallet deposit required to qualify for the bonus.') }}
                                    </p>
                                    <p class="fs-12 mb-3">
                                        <strong>{{ translate('messages.Maximum Bonus') }} <br>
                                        </strong> {{ translate('messages.Define the highest bonus a customer can receive for a single deposit.') }}
                                    </p>
                                    <p class="fs-12 mb-3">
                                        <strong>{{ translate('messages.Start & Expire Date') }} <br>
                                        </strong> {{ translate('messages.Select when the bonus campaign will begin and end.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <!-- Delete Modal modal -->
     <div class="modal fade" id="confirmation_modal_customer" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class=" modal-dialog max-w-500px modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img src="{{dynamicAsset('assets/admin/img/delete.png')}}" class="mb-4">

                                <h5 class="modal-title"></h5>
                            </div>
                            <div class="text-center pb-0" >
                                <h3 class="mb-2 pb-1 fs-18"> {{ translate('Are you sure to delete this Bonus?') }}</h3>
                                <div> <p>{{ translate('If once you delete this bonus, You won’t be able to restore it later.') }}</h3></p></div>
                            </div>

                            <div class="btn--container justify-content-center mt-4 pt-1">
                                <button data-dismiss="modal"  class="btn btn--reset min-w-120" >{{translate("No")}}</button>
                                <button type="button"
                                        id="confirm_delete_btn"
                                        class="btn btn-danger min-w-120">
                                    {{ translate('Delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Delete Modal modal -->
@endsection

@push('script_2')
<script>
    "use strict";
    $("#date_from").on("change", function () {
        $('#date_to').attr('min',$(this).val());
    });

    $("#date_to").on("change", function () {
        $('#date_from').attr('max',$(this).val());
    });

    $(document).on('ready', function () {
        $('#bonus_type').on('change', function() {
         if($('#bonus_type').val() == 'amount')
            {
                $('#maximum_bonus_amount').attr("readonly","true");
                $('#maximum_bonus_amount').val(null);
                $('#percentage').addClass('d-none');
                $('#cuttency_symbol').removeClass('d-none');
            }
            else
            {
                $('#maximum_bonus_amount').removeAttr("readonly");
                $('#percentage').removeClass('d-none');
                $('#cuttency_symbol').addClass('d-none');
            }
        });

        $('#date_from').attr('min',(new Date()).toISOString().split('T')[0]);
        $('#date_to').attr('min',(new Date()).toISOString().split('T')[0]);

            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'), {
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
                    '<img class="w-7rem mb-3" src="{{dynamicAsset('assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">' +

                    '</div>'
                }
            });

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $('#dataSearch').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.customer.wallet.bonus.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#table-div').html(data.view);
                    $('#itemCount').html(data.count);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        $('#reset_btn').click(function(){
            $('#module_select').val(null).trigger('change');
            $('#store_id').val(null).trigger('change');
            $('#store_wise').show();
            $('#zone_wise').hide();
        })
    </script>
    <script>
        let deleteFormId = null;

        $('#confirmation_modal_customer').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            deleteFormId = button.data('form-id');
        });

        $('#confirm_delete_btn').on('click', function () {
            if (deleteFormId) {
                document.getElementById(deleteFormId).submit();
            }
        });
    </script>
@endpush
