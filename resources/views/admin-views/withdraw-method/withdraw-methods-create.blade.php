@extends('layouts.admin.app')

@section('title',  translate('messages.Withdrawal_Methods'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                <h2 class="page-title">
                    {{ translate('messages.Withdrawal_Methods')}}
                </h2>

            </div>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-md-12">
                <form action="{{route('admin.business-settings.withdraw-method.store')}}" method="POST">
                    <div class="card">
                        <div class="card-body">                            
                            @csrf
                            <div class="global-bg-box rounded p-xxl-4 p-3 mb-20">
                                <div class="form-floating">
                                    <div class="d-flex justify-content-between flex-wrap gap-1 mb-2">
                                        <label class="text-dark mb-0">{{ translate('messages.method_name')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" value="1" name="is_default" id="flexCheckDefaultMethod">
                                        <label class="form-check-label" for="flexCheckDefaultMethod">
                                            {{ translate('messages.default_method')}}
                                        </label>
                                        </div>
                                    </div>
                                        <input type="text" class="form-control d-flex" name="method_name" id="method_name"
                                        placeholder=" {{ translate('messages.Ex:_Bank')}}" value="" required>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-20">
                                <h4 class="fs-16 m-0">{{ translate('messages.Input Fields')}}</h4>
                                <!-- BUTTON -->
                                <button class="btn btn--primary m-0" id="add-more-field">
                                    <i class="tio-add-circle"></i> {{ translate('messages.Add_Fields')}}
                                </button>
                            </div>
                            <div class="">
                                <!-- HERE CUSTOM FIELDS WILL BE ADDED -->
                                <div id="custom-field-section">
                                    <div class="global-bg-box rounded p-xxl-4 p-3">
                                        <div class="row gy-2 align-items-end">
                                            <div class="col-lg-3 col-md-4 col-12">
                                                <label>{{ translate('messages.Input_Field_Type')}} <span
                                                    class="input-label-secondary text-danger">*</span></label>
                                                <select class="form-control js-select  js-select2-custom" name="field_type[]" required>
                                                    <option value="string">{{ translate('messages.Text')}}</option>
                                                    <option value="number">{{ translate('messages.Number')}}</option>
                                                    <option value="date">{{ translate('messages.Date')}}</option>
                                                    <option value="email">{{ translate('messages.Email')}}</option>
                                                    <option value="phone">{{ translate('messages.Phone')}}</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3 col-md-4 col-12">
                                                <div class="form-floating">
                                                    <label>{{ translate('messages.field_name')}} <span
                                                    class="input-label-secondary text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="field_name[]"
                                                            placeholder="{{ translate('messages.Ex:_Account_name')}} " value="" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-4 col-12">
                                                <div class="form-floating">
                                                    <label>{{ translate('messages.placeholder_text')}} <span
                                                    class="input-label-secondary text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="placeholder_text[]"
                                                            placeholder="{{ translate('messages.Ex:_John')}} " value="" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-4 col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" name="is_required[0]" id="flexCheckDefault__0" checked>
                                                    <label class="form-check-label" for="flexCheckDefault__0">
                                                        {{ translate('messages.Is_required_')}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- BUTTON -->
                                <!-- <button class="btn btn--primary mt-3" id="add-more-field">
                                    <i class="tio-add"></i> {{ translate('messages.Add_Fields')}}
                                </button> -->
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" class="btn min-w-120 btn--secondary mx-2">{{ translate('messages.Reset')}}</button>
                        <button type="submit" class="btn min-w-120 btn--primary demo_check">{{ translate('messages.Submit')}}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection


@push('script_2')
    <script>
        "use strict";
        let counter=0;

        $(document).on('click', '.remove_field', function() {
            let fieldRowId = $(this).data('id');
            $(`#field-row--${fieldRowId}`).remove();
            counter--;
        });

        jQuery(document).ready(function ($) {
            counter = 1;

            $('#add-more-field').on('click', function (event) {
                if(counter < 15) {
                    event.preventDefault();

                    $('#custom-field-section').append(
                        `<div class="global-bg-box rounded p-xxl-4 position-relative p-3 mt-3" id="field-row--${counter}">
                            <div class="row gy-2 align-items-end">
                                <div class="col-lg-3 col-md-4 col-12">
                                    <label>{{ translate('messages.Input_Field_Type')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                    <select class="form-control js-select js-select2-custom" name="field_type[]" required>

                                        <option value="string">{{ translate('messages.Text')}}</option>
                                        <option value="number">{{ translate('messages.Number')}}</option>
                                        <option value="date">{{ translate('messages.Date')}}</option>
                                        <option value="email">{{ translate('messages.Email')}}</option>
                                        <option value="phone">{{ translate('messages.Phone')}}</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-4 col-12">
                                    <div class="form-floating">
                                        <label>{{ translate('messages.field_name')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                        <input type="text" class="form-control" name="field_name[]"
                                            placeholder="{{ translate('messages.Ex:_Bank')}}" value="" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-43 col-12">
                                    <div class="form-floating">
                                        <label>{{ translate('messages.placeholder_text')}} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                        <input type="text" class="form-control" name="placeholder_text[]"
                                            placeholder="{{ translate('messages.Ex:_John')}}" value="" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4 col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="is_required[${counter}]" id="flexCheckDefault__${counter}" checked>
                                        <label class="form-check-label" for="flexCheckDefault__${counter}">
                                            {{ translate('messages.Is_required_')}}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-1 remove_filed-new-btn">
                                    <span class="btn btn-danger remove_field w-30px h-30px min-w-30px p-0 d-center ml-auto rounded-circle" data-id="${counter}">
                                    <i class="tio-clear"></i>
                                    </span>
                                </div>
                            </div>
                        </div>`
                        );

                    $(".js-select").select2();

                    counter++;
                } else {
                    Swal.fire({
                        title: '{{ translate('messages.Reached_maximum')}}',
                        confirmButtonText: '{{ translate('messages.ok')}}',
                    });
                }
            })

            $('form').on('reset', function (event) {
                if(counter > 1) {
                    $('#custom-field-section').html("");
                    $('#method_name').val("");
                }

                counter = 1;
            })
        });
    </script>
@endpush
