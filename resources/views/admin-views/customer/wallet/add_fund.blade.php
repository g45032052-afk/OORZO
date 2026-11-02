@extends('layouts.admin.app')

@section('title',translate('messages.add_fund'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{dynamicAsset('assets/admin/img/money.png')}}" alt="public">
                </div>
                <span>
                    {{translate('messages.add_fund')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card gx-2 gx-lg-4 mx-sm-1">
            <div class="card-body">
                <form action="{{route('admin.customer.wallet.add-fund')}}" method="post" enctype="multipart/form-data" id="add_fund">
                    @csrf
                    <h5 class="fs-18 mb-20px"> {{translate('messages.add_fund_info')}}</h5>
                    <div class="global-bg-box rounded p-xxl-20 p-3">
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-4 col-12">
                                <div class="form-group mb-0">
                                    <label class="form-label" for="customer">{{translate('messages.customer')}}</label>
                                    <select id='customer' name="customer_id" data-placeholder="{{translate('messages.select_customer')}}" class="js-data-example-ajax form-control h--45px" required>
                                        @if(isset($customer))
                                            <option value="{{ $customer->id }}" selected>{{ $customer->full_name }} ({{ $customer->phone }})  </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-12">
                                <div class="form-group mb-0">
                                    <label class="form-label" for="amount">{{translate('messages.amount')}}</label>

                                    <input type="number" class="form-control h--45px" placeholder="{{translate('messages.Ex: 10')}}" name="amount" min="{{ \App\CentralLogics\Helpers::getDecimalPlaces() }}" id="amount" step="{{ \App\CentralLogics\Helpers::getDecimalPlaces() }}" required>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-12">
                                <div class="form-group mb-0">
                                    <label class="form-label" for="referance">{{translate('messages.reference')}} &nbsp; <small class="mt-1">({{translate('messages.optional')}})</small></label>

                                    <input type="text" class="form-control h--45px" placeholder="{{translate('messages.Ex: reference')}}" name="referance" id="referance">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="reset" id="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit" id="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                    </div>
                </form>
            </div>
            <!-- End Table -->
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
                    <h3 class="mb-0">{{ translate('messages.Add fund Guideline') }}</h3>
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
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Add Fund ') }}</span>
                            </button>
                            {{-- <a href="#maintenance_mode"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a> --}}
                        </div>
                        <div class="collapse show mt-3" id="maintenance_mode_guide">
                            <div class="card card-body">
                                <div class="">
                                    {{-- <h5 class="mb-3">{{translate('Wallet Bonus Setup')}}</h5> --}}
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.This section allows you to manually add funds to a customer’s wallet.') }}
                                    </p>
                                    <p class="fs-12 mb-3">
                                        <strong>{{ translate('messages.customer:') }}</strong> {{ translate('messages. Select the customer who will receive the added funds.') }}
                                    </p>
                                    <p class="fs-12 mb-3">
                                        <strong>{{ translate('messages.Amount:') }}</strong> {{ translate('messages. Enter the amount you want to add to the customer’s wallet.') }}
                                    </p>
                                    <p class="fs-12 mb-3">
                                        <strong>{{ translate('messages.Reference (Optional):') }}</strong> {{ translate('messages. Add a note or reference ID for internal tracking (e.g., transaction ID, reason).') }}
                                    </p>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.Use Submit to confirm the fund addition or Reset to clear all fields.') }}
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
    <script>
        // "use strict";
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


            $('#column3_search').on('change', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });


        $('#add_fund').on('submit', function (e) {

            e.preventDefault();
            let formData = new FormData(this);

            Swal.fire({
                title: '{{translate('messages.are_you_sure')}}',
                text: '{{translate('messages.you_want_to_add_fund')}}'+$('#amount').val()+' {{\App\CentralLogics\Helpers::currency_code().' '.translate('messages.to')}} '+$('#customer option:selected').text()+'{{translate('messages.wallet')}}',
                type: 'info',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: 'primary',
                cancelButtonText: '{{translate('messages.no')}}',
                confirmButtonText: '{{translate('messages.add')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post({
                        url: '{{route('admin.customer.wallet.add-fund')}}',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            if (data.errors) {
                                for (let i = 0; i < data.errors.length; i++) {
                                    toastr.error(data.errors[i].message, {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                                }
                            } else {
                                $('#customer').val(null).trigger('change');
                                $('#amount').val(null).trigger('change');
                                $('#referance').val(null).trigger('change');
                                toastr.success('{{translate("messages.fund_added_successfully")}}', {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            }
                        }
                    });
                }
            })
        })

        $('.js-data-example-ajax').select2({
            ajax: {
                url: '{{route('admin.customer.select-list')}}',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                    results: data
                    };
                },
                __port: function (params, success, failure) {
                    let $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });

        $('#reset').click(function(){
            $('#customer').val(null).trigger('change');
        })
    </script>
@endpush
