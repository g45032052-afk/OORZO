@extends('layouts.vendor.app')

@section('title',translate('messages.test'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <h1 class="fs-20 mb-3">{{ translate('Blog') }}</h1>
        <div class="d-flex flex-wrap justify-content-between align-items-center my-3 gap-3">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <!-- Nav -->
                <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" aria-disabled="true">{{ translate('Blog List') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  " href="#" aria-disabled="true">{{ translate('Priority Setup') }}</a>
                    </li>
                </ul>
                <!-- End Nav -->
            </div>
        </div>
        <div class="card card-body mb-20">
            <h3 class="mb-20">{{ translate('Filter Data') }}</h3>
            <form action="">
                <div class="__bg-F8F9FC-card">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label for="" class="input-label text-capitalize">
                                {{ translate('Category') }} <span class="text-danger">*</span>
                            </label>
                            <select name="restaurant_id" id="restaurant_id" class="form-control custom-select js-data-example-ajax" 
                                data-placeholder="{{translate('messages.select_category')}}" required oninvalid="this.setCustomValidity('{{translate('messages.please_select_category')}}')">
                                <option value="test">test</option>
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label for="" class="input-label text-capitalize">
                                {{ translate('Publish Date') }} <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative">
                                <i class="tio-calendar-month icon-absolute-on-right z-index-2"></i>
                                <input required type="text" class="form-control bg-white h-45 position-relative bg-transparent"  name="dates" placeholder="{{ translate('messages.Select_Date') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="btn--container mt-4 justify-content-end">
                    <button id="resetbtn" type="reset" data-url="" class="btn btn--reset">{{ translate('Reset') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('Filter') }}</button>
                </div>
            </form>
        </div>

        <!-- Blog List Table -->
        <div class="card">

            <!-- Card Header -->
            <div class="card-header flex-wrap gap-2 border-0 p-20">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="mb-0">{{ translate('Blog List') }}</h5>
                    <span class="badge badge-soft-dark ml-1">3,343</span>
                </div>
                <div class="d-flex align-items-center gap-lg-3 gap-2 flex-wrap ml-auto flex-grow-1 justify-content-sm-end">
                    <!-- Search -->
                    <form id="blog-search-form" class="flex-grow-1 max-w-300 min-w-100-mobile">
                        <div class="input--group input-group border rounded input-group-merge input-group-flush">
                            <input id="blogSearch" name="search" type="search"
                                   value="{{ request()?->search ?? null }}"
                                   class="form-control border-0"
                                   placeholder="{{ translate('Search by Category or Title...') }}"
                                   aria-label="{{ translate('Search here') }}">
                            <button type="submit" class="btn btn--reset w-auto px-2 py-2 h-40px min-w-35px">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                    </form>
                    <!-- Create Blog -->
                    <a href="javascript:" class="btn btn--primary lh-1 d-flex align-items-center gap-1 font-semibold fs-12 h-40 px-sm-4">
                        <i class="tio-add"></i>
                        <span>{{ translate('Create Blog') }}</span>
                    </a>
                </div>
            </div>
            <!-- End Card Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom pt-0">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table text-dark">
                    <thead class="global-bg-box">
                        <tr>
                            <th class="w-50px">{{ translate('SL') }}</th>
                            <th class="w-90px">{{ translate('ID') }}</th>
                            <th class="w-160px">{{ translate('Category') }}</th>
                            <th>{{ translate('Title') }}</th>
                            <th class="w-150px">{{ translate('Writer') }}</th>
                            <th class="w-140px">{{ translate('Publish Date') }}</th>
                            <th class="w-120px">
                                {{ translate('Featured') }}
                                <span data-toggle="tooltip" data-placement="top"
                                      title="{{ translate('Toggle to mark this blog as featured') }}">
                                    <i class="tio-info text-muted"></i>
                                </span>
                            </th>
                            <th class="w-100px">
                                {{ translate('Status') }}
                                <span data-toggle="tooltip" data-placement="top"
                                      title="{{ translate('Toggle to enable or disable this blog') }}">
                                    <i class="tio-info text-muted"></i>
                                </span>
                            </th>
                            <th class="w-120px text-center">{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>#2583</td>
                            <td>Product Guides</td>
                            <td>
                                <span class="line--limit-1 d-block max-w-300">
                                    The Ultimate Buying Guide: How to Choose the Best [Product] for Your Needs
                                </span>
                            </td>
                            <td>Courtney Henry</td>
                            <td>11 Feb, 2025</td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center">
                                    <label
                                        class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox"
                
                                            data-id=""
                                            data-type="toggle"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/modal/warning.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/modal/warning.png') }}"
                                            data-title-on="{{ translate('are_you_sure_to_enable_vendor_website_setup') }}?"
                                            data-title-off="{{ translate('are_you_sure_to_disable_vendor_website_setup') }}?"
                                            data-text-on="<p>{{ translate('vendors_will_be_able_to__customize_and_manage_their_own_websites_independently,_including_updating_content,_design,_and_settings.') }}</p>"
                                            data-text-off="<p>{{ translate('vendors_will_not_be_able_to_customize_or_manage_their_own_websites_independently,_including_updating_content,_design,_or_settings') }}</p>"
                                            class="toggle-switch-input dynamic-checkbox-toggle"
                
                                            value="1"
                                            name="" id="">
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center">
                                    <label
                                        class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox"
                
                                            data-id=""
                                            data-type="toggle"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/modal/warning.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/modal/warning.png') }}"
                                            data-title-on="{{ translate('are_you_sure_to_enable_vendor_website_setup') }}?"
                                            data-title-off="{{ translate('are_you_sure_to_disable_vendor_website_setup') }}?"
                                            data-text-on="<p>{{ translate('vendors_will_be_able_to__customize_and_manage_their_own_websites_independently,_including_updating_content,_design,_and_settings.') }}</p>"
                                            data-text-off="<p>{{ translate('vendors_will_not_be_able_to_customize_or_manage_their_own_websites_independently,_including_updating_content,_design,_or_settings') }}</p>"
                                            class="toggle-switch-input dynamic-checkbox-toggle"
                
                                            value="1"
                                            name="" id="">
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn btn-sm btn-outline-success action-btn"
                                        href="javascript:"
                                        title="{{ translate('View Draft') }}">
                                        <i class="tio-document-text"></i>
                                    </a>
                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                        href="javascript:"
                                        title="{{ translate('Edit Blog') }}">
                                        <i class="tio-edit"></i>
                                    </a>
                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn"
                                        data-toggle="modal"
                                        href="#delete-modal"
                                        title="{{ translate('Delete Blog') }}">
                                        <i class="tio-delete-outlined"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    <ul class="pagination mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="javascript:">&lsaquo;</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="javascript:">1</a></li>
                        <li class="page-item"><a class="page-link" href="javascript:">2</a></li>
                        <li class="page-item"><a class="page-link" href="javascript:">3</a></li>
                        <li class="page-item disabled"><a class="page-link" href="javascript:">...</a></li>
                        <li class="page-item"><a class="page-link" href="javascript:">13</a></li>
                        <li class="page-item">
                            <a class="page-link" href="javascript:">&rsaquo;</a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- End Pagination -->

        </div>
        <!-- End Blog List Table -->

    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="delete-modal">
        <div class="modal-dialog modal-dialog-centered status-warning-modal">
            <div class="modal-content">
                <div class="modal-header px-2 pt-2">
                    <button type="button" class="close btn btn--reset rounded-circle p-1" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear fs-20 opacity-70"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="mb-20 mx-auto">
                        <div class="text-center mb-30">
                            <img width="64" height="64" src="{{ dynamicAsset('assets/admin/img/modal/delete-icon.png') }}" alt="" class="mb-20">
                            <h3 class="mb-3">
                                {{ translate('Are you sure to delete this blog?') }}
                            </h3>
                            <p class="mb-0">
                                {{ translate('Once you delete it, This will permanently remove it from the list') }}
                            </p>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" class="btn btn-danger min-w-120" id="">
                                {{ translate('Yes, Delete') }}
                            </button>
                            <button type="button" class="btn btn--reset min-w-120" data-dismiss="modal">{{ translate('Cancel') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>
@endsection

@push('script_2')
<script>
    "use strict";
    $(function() {

        $('input[name="dates"]').daterangepicker({
            minDate: new Date(),
            startDate: moment().startOf('hour'),
            endDate: moment().startOf('hour').add(10, 'day'),
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });
        $('input[name="dates"]').attr('placeholder', "{{ translate('Select date') }}");

        $('input[name="dates"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('D MMM, YYYY') + ' - ' + picker.endDate.format('D MMM, YYYY'));
        });

        $('input[name="dates"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        $('.js-select').each(function () {
            let select2 = $.HSCore.components.HSSelect2.init($(this));
        });
    });
</script>
@endpush
