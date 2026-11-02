@extends('layouts.vendor.app')

@section('title',translate('Food Bulk Export'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title mb-2 text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{dynamicAsset('assets/admin/img/export.png')}}" alt="">
                </div>
                {{ translate('Export Foods') }}
            </h1>
        </div>
        <div class="card rest-part">
            <div class="card-body">
                <div class="export-steps">
                    <div class="export-steps-item">
                        <div class="inner h-100">
                            <h5 class="mb-1">{{ translate('STEP_1') }}</h5>
                            <p class="m-0 fs-14">
                                {{ translate('Select how you want to export the data — by ID or by Date.') }}
                            </p>
                        </div>
                    </div>
                    <div class="export-steps-item">
                        <div class="inner h-100">
                            <h5 class="mb-1">{{ translate('STEP_2') }}</h5>
                            <!-- Id wise text -->
                            <p class="m-0 fs-14">
                                {{ translate('is selected, specify the ID range to export the data.') }}
                            </p>
                            <!-- Date wise text -->
                            <p class="m-0 fs-14">
                                <!-- {{ translate('is selected, define the date range to export the data accordingly.') }} -->
                            </p>
                        </div>
                    </div>
                </div>
                <form class="product-form" action="{{route('vendor.food.bulk-export')}}" method="POST"
                        enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('select type')}}<span
                                        class="input-label-secondary"></span></label>
                                <select name="type" id="type" data-placeholder="{{translate('select type')}}" class="form-control" required title="Select Type">
                                    <option value="all">{{translate('all data')}}</option>
                                    <option value="date_wise">{{translate('date wise')}}</option>
                                    <option value="id_wise">{{translate('id wise')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group id_wise">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('start id')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="number" name="start_id" class="form-control">
                            </div>
                            <div class="form-group date_wise">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('from date')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="date" name="from_date"  id="date_from" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group id_wise">
                                <label class="input-label" for="exampleFormControlSelect1">{{translate('end id')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="number" name="end_id" class="form-control">
                            </div>
                            <div class="form-group date_wise">
                                <label class="input-label text-capitalize" for="exampleFormControlSelect1">{{translate('to date')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="date" name="to_date"  id="date_to" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')

    <script>
        "use strict";
        $(document).on('ready', function () {
            const today = (new Date()).toISOString().split('T')[0];
            $('#date_from, #date_to').attr('max', today);

            $('.id_wise').hide();
            $('.date_wise').hide();

            $('#type').on('change', function () {
                $('.id_wise').hide();
                $('.date_wise').hide();
                $('.' + $(this).val()).show();
            });

            // Date validation
            $('#date_from, #date_to').on('change', function () {
                const fromDate = $('#date_from').val();
                const toDate = $('#date_to').val();

                if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                    toastr.error("{{ translate('from date cannot be greater than to date') }}");
                    $('#date_from').val('');
                }
            });

            // Reset button logic
            $('button[type="reset"]').on('click', function () {
                $('#type').val('all').trigger('change');
                $('#date_from').val('');
                $('#date_to').val('');
                $('input[name="start_id"], input[name="end_id"]').val('');
            });
        });
    </script>
@endpush
