@extends('layouts.admin.app')

@section('title', translate('Restaurant Categories'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header pb-0">
            <div class="row g-1 align-items-center">
                <div class="col-sm-6 mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{ dynamicAsset('assets/admin/img/category.png') }}" alt="">
                        </div>
                        <span>{{ translate('Restaurant Category Setup') }}</span>
                    </h2>
                </div>
            </div>
        </div>
             <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning mt-3 bg-opacity-10">
            <span class="text-warning lh-1 fs-14">
                <i class="tio-info"></i>
            </span>
            <span>
                {{ translate('messages.All restaurant categories will be show only in restaurant details page.') }}
            </span>
        </div>

        <div class="card mt-3">
            <div class="card-header border-0 pt-3 pb-1">
                <div class="search--button-wrapper">
                    <h5 class="card-title">
                        <span class="card-header-icon">
                            {{ translate('Restaurant Category List') }}
                            <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $categories->total() }}</span>
                        </span>
                    </h5>

                    <div class="hs-unfold ml-3">
                         <form method="get" >
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch" type="search" value="{{ request()?->search ?? null }}"  name="search" class="form-control" placeholder="{{ translate('messages.search_here') }}" aria-label="{{translate('messages.search_here')}}">
                            <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>

                   </div>

                    <div class="hs-unfold ml-3">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm"
                            href="javascript:;"
                            data-hs-unfold-options='{
                                "target": "#restaurantCategoryExportDropdown",
                                "type": "css-animation"
                            }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="restaurantCategoryExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a target="__blank" class="dropdown-item"
                                href="{{ route('admin.restaurant-category.export', array_merge(['type' => 'excel'], request()->query())) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a target="__blank" class="dropdown-item"
                                href="{{ route('admin.restaurant-category.export', array_merge(['type' => 'csv'], request()->query())) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                {{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>

                    <div>
                        <a href="javascript:void(0)" class="btn btn--primary pull-right offcanvas-trigger"
                            data-target="#offcanvas__restaurantCategory" data-action="create">
                            <i class="tio-add-circle"></i> {{ translate('Add Restaurant Category') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive datatable-custom pb-0">
                <table id="columnSearchDatatable"
                    class="table mb-0 table-borderless table-thead-bordered table-align-middle text-nowrap">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('messages.SL') }}</th>
                            <th>{{ translate('messages.Category_Info') }}</th>
                            <th>{{ translate('messages.Restaurant') }}</th>
                            <th>{{ translate('messages.priority Level') }}
                                 <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                      data-original-title="{{ translate('Set how prominently this category should appear to customers') }}"><i
                                        class="tio-info text-gray1 fs-14"></i></span>
                            </th>
                            <th>{{ translate('messages.status') }}</th>
                            <th class="text-center w-130px">{{ translate('messages.action') }}</th>
                        </tr>
                    </thead>
                    <tbody id="table-div">
                        @foreach($categories as $key => $category)
                            <tr>
                                <td><div class="pl-3">{{ $key + $categories->firstItem() }}</div></td>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img class="avatar border flex-shrink-0"
                                            src="{{ $category->image_full_url }}"
                                            alt="{{ Str::limit($category->name, 20, '...') }}">
                                        <div class="d-block font-size-sm text-title">
                                            <div>{{ Str::limit($category->name, 20, '...') }}</div>
                                            <div class="text-muted">{{ translate('ID') }} #{{ $category->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                         <img class="avatar border rounded-circle flex-shrink-0"
                                         src="{{ $category?->restaurant['logo_full_url'] }}"
                                         alt="{{Str::limit('Hungry Puppet', 20,'...')}}">
                                        {{ Str::limit($category?->restaurant?->name, 20, '...') }}
                                         @include('partials._verified_restaurant_badge', ['restaurant' => $category?->restaurant])
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.restaurant-category.priority', $category->id) }}" class="priority-form">
                                        <select name="priority" class="form-control form--control-select priority-select {{ $category->priority == 0 ? 'text--title' : '' }} {{ $category->priority == 1 ? 'text--info' : '' }} {{ $category->priority == 2 ? 'text--success' : '' }}">
                                            <option value="0" {{ $category->priority == 0 ? 'selected' : '' }}>{{ translate('messages.normal') }}</option>
                                            <option value="1" {{ $category->priority == 1 ? 'selected' : '' }}>{{ translate('messages.medium') }}</option>
                                            <option value="2" {{ $category->priority == 2 ? 'selected' : '' }}>{{ translate('messages.high') }}</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm ml-2" for="restaurantCategoryStatus{{ $category->id }}">
                                        <input type="checkbox"
                                            data-url="{{ route('admin.restaurant-category.status', [$category->id, $category->status ? 0 : 1]) }}"
                                            class="toggle-switch-input redirect-url"
                                            id="restaurantCategoryStatus{{ $category->id }}"
                                            {{ $category->status ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm text-end action-btn info--outline text--info info-hover offcanvas-trigger"
                                            data-target="#offcanvas__restaurantCategory"
                                            data-id="{{ $category->id }}"
                                            data-url="{{ route('admin.restaurant-category.edit', $category->id) }}"
                                            data-action="edit"
                                            href="javascript:"
                                            title="{{ translate('Edit Restaurant Category') }}">
                                            <i class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                            href="javascript:"
                                            data-id="restaurant-category-{{ $category->id }}"
                                            data-message="{{ translate('Want to delete this restaurant category?') }}"
                                            title="{{ translate('Delete Restaurant Category') }}">
                                            <i class="tio-delete-outlined"></i>
                                        </a>
                                    </div>

                                    <form action="{{ route('admin.restaurant-category.delete', $category->id) }}" method="post"
                                        id="restaurant-category-{{ $category->id }}">
                                        @csrf
                                        @method('delete')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if(count($categories) === 0)
                    <div class="empty--data">
                        <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="public">
                        <h5>{{ translate('no_data_found') }}</h5>
                    </div>
                @endif
            </div>

            <div class="card-footer pt-0 border-0">
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>{!! $categories->links() !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="offcanvas__restaurantCategory" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100"></div>
    </div>

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin') }}/js/view-pages/category-index.js"></script>
    <script src="{{ dynamicAsset('assets/admin/js/offcanvas.js') }}"></script>
    <script>
        "use strict";

        document.addEventListener("DOMContentLoaded", function () {
            $('.select2-basic').select2({ width: '100%' });

            $('#restaurantFilterSelect, #priorityFilterSelect').on('change', function () {
                $('#restaurantCategoryFilterForm').submit();
            });
        });

        $(document).on('click', '.offcanvas-trigger', function () {
            const action = $(this).data('action');

            if (action === 'create') {
                fetchRestaurantCategoryForm("{{ route('admin.restaurant-category.create') }}");
                return;
            }

            fetchRestaurantCategoryForm($(this).data('url'));
        });

        function fetchRestaurantCategoryForm(url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function () {
                    $('#data-view').empty();
                    $('#loading').show();
                },
                success: function (data) {
                    $('#data-view').append(data.view);
                    initLangTabs();
                    initSelect2Dropdowns();
                    initFileUpload();
                    checkPreExistingImages();
                    initCloseButtons();
                    initRemoveImage();
                },
                complete: function () {
                    $('#loading').hide();
                }
            });
        }

        function initLangTabs() {
            const langLinks = document.querySelectorAll('.lang_link1');
            langLinks.forEach(function (langLink) {
                langLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    langLinks.forEach(function (link) {
                        link.classList.remove('active');
                    });
                    this.classList.add('active');
                    document.querySelectorAll('.lang_form1').forEach(function (form) {
                        form.classList.add('d-none');
                    });
                    const lang = this.id.substring(0, this.id.length - 5);
                    $('#' + lang + '-form1').removeClass('d-none');
                });
            });
        }

        function initSelect2Dropdowns() {
            $('.js-select2-custom1').select2({
                width: '100%'
            });
        }

        function initCloseButtons() {
            $('.offcanvas-close, #offcanvasOverlay').off('click').on('click', function () {
                $('.custom-offcanvas').removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
            });
        }

        function initRemoveImage() {
            const removeBtn = document.getElementById('remove_image_btn');
            const removeFlag = document.getElementById('image_remove');
            const fileInput = document.querySelector('input[name="image"]');

            if (removeBtn && removeFlag) {
                removeBtn.addEventListener('click', function () {
                    removeFlag.value = '1';
                    if (fileInput) {
                        fileInput.removeAttribute('disabled');
                        fileInput.value = '';
                    }
                });
            }
        }
    </script>
@endpush
