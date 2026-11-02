@extends('layouts.vendor.app')

@section('title', translate('My Category'))

@section('content')
    <div class="content container-fluid">
        <div class="card border-0">
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper justify-content-end flex-wrap">
                    <h2 class="page-header-title card-title text-capitalize mb-0">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{ dynamicAsset('assets/admin/img/resturant-panel/page-title/category.png') }}" alt="public">
                        </div>
                        <span>
                            {{ translate('My Category') }}
                            @if(count($categories) > 0)
                                <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $categories->total() }}</span>
                            @endif
                        </span>
                    </h2>

                    @if(count($categories) > 0)
                        <form id="vendorRestaurantCategoryFilterForm" class="row g-2 align-items-center ml-auto">
                     
                            <div class="col-auto">
                                <div class="input-group input--group">
                                    <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                                        placeholder="{{ translate('Search by name or ID') }}"
                                        aria-label="{{ translate('messages.search_here') }}">
                                    <button type="submit" class="btn btn--secondary">
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="hs-unfold ml-2">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm"
                                href="javascript:;"
                                data-hs-unfold-options='{
                                    "target": "#vendorRestaurantCategoryExportDropdown",
                                    "type": "css-animation"
                                }'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>

                            <div id="vendorRestaurantCategoryExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a target="__blank" class="dropdown-item"
                                    href="{{ route('vendor.restaurant-category.export', array_merge(['type' => 'excel'], request()->query())) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                        alt="Image Description">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a target="__blank" class="dropdown-item"
                                    href="{{ route('vendor.restaurant-category.export', array_merge(['type' => 'csv'], request()->query())) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="Image Description">
                                    {{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="{{ count($categories) > 0 ? 'ml-2' : 'ml-auto' }}">
                        <a href="javascript:void(0)" class="btn btn--primary offcanvas-trigger"
                            data-target="#offcanvas__vendorRestaurantCategory" data-action="create">
                            <i class="tio-add-circle"></i> {{ translate('Add My Category') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                @if(count($categories) > 0)
            <div class="px-3 mb-3">
                <div class="alert alert-warning d-inline-flex align-items-start gap-2 text-left mx-auto py-2 px-3 mb-0"
                        style=" background-color: #FFF8E5; border: 1px solid #FFE6A8;" role="alert">
                        <i class="tio-info text-warning mt-1" style="color: #F2A93B !important;"></i>
                        <div class="fs-12 text-body">
                            {{ translate('Once you create restaurant categories, you must add your foods to those categories. Without assigning foods, they will not appear on your restaurant details page. If you want to proceed with the main category, you can skip adding any restaurant categories.') }}
                        </div>
                    </div>
            </div>

                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center w-5p">{{ translate('messages.sl') }}</th>
                                <th class="w-30p">{{ translate('Category Info') }}</th>
                                <th class=" w-15p">{{ translate('messages.priority Level') }}</th>
                                <th class="text-center w-10p">{{ translate('messages.status') }}</th>
                                <th class="text-center w-10p">{{ translate('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="table-div">
                            @foreach($categories as $key => $category)
                                <tr>
                                    <td class="text-center">{{ $key + $categories->firstItem() }}</td>
                                    <td>
                                        <div class="media align-items-center gap-2">
                                            <img class="avatar avatar-lg rounded onerror-image"
                                                src="{{ $category->image_full_url }}"
                                                data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                                                alt="{{ $category->name }}">
                                            <div class="media-body">
                                                <h6 class="mb-0 text-hover-primary text-wrap line-limit-2 max-w-215px">
                                                    {{ ucfirst(Str::limit($category->name, 32, '...')) }}
                                                </h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('vendor.restaurant-category.priority', $category->id) }}" class="priority-form">
                                            <select name="priority" class="form-control form--control-select priority-select {{ $category->priority == 0 ? 'text--title' : '' }} {{ $category->priority == 1 ? 'text--info' : '' }} {{ $category->priority == 2 ? 'text--success' : '' }}">
                                                <option value="0" {{ $category->priority == 0 ? 'selected' : '' }}>{{ translate('messages.normal') }}</option>
                                                <option value="1" {{ $category->priority == 1 ? 'selected' : '' }}>{{ translate('messages.medium') }}</option>
                                                <option value="2" {{ $category->priority == 2 ? 'selected' : '' }}>{{ translate('messages.high') }}</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <label class="toggle-switch toggle-switch-sm ml-2" for="vendorRestaurantCategoryStatus{{ $category->id }}">
                                            <input type="checkbox"
                                                data-url="{{ route('vendor.restaurant-category.status', [$category->id, $category->status ? 0 : 1]) }}"
                                                class="toggle-switch-input redirect-url"
                                                id="vendorRestaurantCategoryStatus{{ $category->id }}"
                                                {{ $category->status ? 'checked' : '' }}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm action-btn btn--warning btn-outline-warning assign-foods-trigger"
                                                data-target="#offcanvas__vendorAssignFoods"
                                                data-url="{{ route('vendor.restaurant-category.foods', $category->id) }}"
                                                href="javascript:"
                                                title="{{ translate('Assign Foods') }}">
                                                <i class="tio-add-circle-outlined"></i>
                                            </a>
                                            <a class="btn btn-sm text-end action-btn info--outline text--info info-hover offcanvas-trigger"
                                                data-target="#offcanvas__vendorRestaurantCategory"
                                                data-url="{{ route('vendor.restaurant-category.edit', $category->id) }}"
                                                data-action="edit"
                                                href="javascript:"
                                                title="{{ translate('Edit My Category') }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                                href="javascript:"
                                                data-id="vendor-restaurant-category-{{ $category->id }}"
                                                data-message="{{ translate('Want to delete this my category?') }}"
                                                title="{{ translate('Delete My Category') }}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                        </div>

                                        <form action="{{ route('vendor.restaurant-category.delete', $category->id) }}" method="post"
                                            id="vendor-restaurant-category-{{ $category->id }}">
                                            @csrf
                                            @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <div class="my-category-empty p-4 p-md-5 text-center">
                        <img src="{{ dynamicAsset('assets/admin/img/empty-my-category.svg') }}"
                            alt="{{ translate('No My Category') }}"
                            class="mb-3" width="64" height="64">
                        <h5 class="mb-2 font-weight-bold">{{ translate('Add My Restaurant Category') }}</h5>
                        <p class="text-muted mb-3 mx-auto" style="max-width: 560px;">
                            {{ translate('Organize your foods with custom categories to make your menu easier for customers to browse.') }}
                        </p>
                        <div class="alert alert-warning d-inline-flex align-items-start gap-2 text-left mx-auto py-2 px-3 mb-0"
                            style="max-width: 640px; background-color: #FFF8E5; border: 1px solid #FFE6A8;" role="alert">
                            <i class="tio-info text-warning mt-1" style="color: #F2A93B !important;"></i>
                            <div class="fs-12 text-body">
                                {{ translate('Once you create restaurant categories, you must add your foods to those categories. Without assigning foods, they will not appear on your restaurant details page. If you want to proceed with the main category, you can skip adding any restaurant categories.') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if(count($categories) > 0)
            <div class="card-footer page-area">
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm-auto">
                        <div class="d-flex justify-content-center justify-content-sm-end">
                            {!! $categories->links() !!}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div id="offcanvas__vendorRestaurantCategory" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100"></div>
    </div>

    <div id="offcanvas__vendorAssignFoods" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="assign-foods-view" class="h-100"></div>
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

            $('#vendorPriorityFilterSelect').on('change', function () {
                $('#vendorRestaurantCategoryFilterForm').submit();
            });
        });

        $(document).on('click', '.offcanvas-trigger', function () {
            const action = $(this).data('action');

            if (action === 'create') {
                fetchVendorRestaurantCategoryForm("{{ route('vendor.restaurant-category.create') }}");
                return;
            }

            fetchVendorRestaurantCategoryForm($(this).data('url'));
        });

        function fetchVendorRestaurantCategoryForm(url) {
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

        // ----- Assign Foods to Category flow -----
        $(document).on('click', '.assign-foods-trigger', function () {
            const url = $(this).data('url');
            const target = $(this).data('target') || '#offcanvas__vendorAssignFoods';
            fetchAssignFoodsOffcanvas(url, target);
        });

        function fetchAssignFoodsOffcanvas(url, target) {
            $.ajax({
                url: url,
                type: 'get',
                beforeSend: function () {
                    $('#assign-foods-view').empty();
                    $('#loading').show();
                },
                success: function (data) {
                    $('#assign-foods-view').html(data.view);
                    $(target).addClass('open');
                    $('#offcanvasOverlay').addClass('show');
                    initAssignFoodsHandlers();
                    initCloseButtons();
                },
                complete: function () {
                    $('#loading').hide();
                }
            });
        }

        // Source-of-truth Set of currently-selected food ids (as strings).
        // Survives across server-side search refreshes so the user's pending
        // changes (checks/unchecks) are not lost when the list reloads.
        let assignFoodsSelectedIds = new Set();

        function syncAssignFoodsDomToSet() {
            $('#assignFoodsForm .assign-food-checkbox').each(function () {
                const id = String(this.value);
                // Locked rows (server-rendered as disabled) are always selected
                // and cannot be toggled off — make sure they stay in the Set.
                if (this.disabled) {
                    assignFoodsSelectedIds.add(id);
                    this.checked = true;
                    $(this).closest('.assign-food-row').addClass('is-selected');
                    return;
                }
                const shouldBeChecked = assignFoodsSelectedIds.has(id);
                this.checked = shouldBeChecked;
                $(this).closest('.assign-food-row').toggleClass('is-selected', shouldBeChecked);
            });
        }

        function initAssignFoodsHandlers() {
            const $form = $('#assignFoodsForm');
            if (!$form.length) return;

            const $searchInput = $form.find('input[name="assign_search"]');
            const $foodListContainer = $('#assignFoodsListContainer');
            const searchUrl = $form.data('search-url');
            let searchTimer = null;

            // Seed the selection set from whatever the server marked checked on
            // initial render (i.e. foods currently assigned to this category).
            assignFoodsSelectedIds = new Set();
            $form.find('.assign-food-checkbox:checked').each(function () {
                assignFoodsSelectedIds.add(String(this.value));
            });
            updateAssignFoodsSelectedCount();
            updateAssignFoodsSaveState();

            const assignFoodsLoaderHtml =
                '<div class="d-flex flex-column align-items-center justify-content-center text-muted py-5">' +
                    '<div class="spinner-border text-primary mb-2" role="status" aria-hidden="true"></div>' +
                    '<span class="fs-12">{{ translate('Searching') }}...</span>' +
                '</div>';

            $searchInput.off('input').on('input', function () {
                clearTimeout(searchTimer);
                const term = $(this).val().trim();

                if (term.length === 1) {
                    return;
                }

                searchTimer = setTimeout(function () {
                    $.ajax({
                        url: searchUrl,
                        type: 'get',
                        data: { search: term },
                        beforeSend: function () {
                            $foodListContainer.html(assignFoodsLoaderHtml);
                        },
                        success: function (data) {
                            $foodListContainer.html(data.view);
                            // Re-apply the user's pending selection to the freshly
                            // rendered rows so checks/unchecks aren't lost.
                            syncAssignFoodsDomToSet();
                            updateAssignFoodsSelectedCount();
                            updateAssignFoodsSaveState();
                        }
                    });
                }, 300);
            });

            $form.off('change', '.assign-food-checkbox').on('change', '.assign-food-checkbox', function () {
                const id = String(this.value);
                if (this.checked) {
                    assignFoodsSelectedIds.add(id);
                } else {
                    assignFoodsSelectedIds.delete(id);
                }
                $(this).closest('.assign-food-row').toggleClass('is-selected', this.checked);
                updateAssignFoodsSelectedCount();
            });

            $form.find('.reset-assign-btn').off('click').on('click', function () {
                // Reset only clears the newly-checked (non-locked) items.
                // Locked rows are already saved to this category and stay selected.
                $form.find('.assign-food-checkbox:not(:disabled)').each(function () {
                    assignFoodsSelectedIds.delete(String(this.value));
                    this.checked = false;
                    $(this).closest('.assign-food-row').removeClass('is-selected');
                });
                updateAssignFoodsSelectedCount();

                if ($searchInput.val() !== '') {
                    $searchInput.val('').trigger('input');
                } else {
                    updateAssignFoodsSaveState();
                }
            });

            $form.off('submit').on('submit', function (e) {
                e.preventDefault();
                const $submitBtn = $form.find('button[type="submit"]');
                $submitBtn.prop('disabled', true);

                // Build payload from the Set, not from DOM — items checked but
                // currently filtered out by search must still be submitted.
                const formData = new FormData();
                formData.append('_token', $form.find('input[name="_token"]').val());
                assignFoodsSelectedIds.forEach(function (id) {
                    formData.append('food_ids[]', id);
                });

                $.ajax({
                    url: $form.attr('action'),
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response && response.success) {
                            toastr.success(response.message || '{{ translate('Foods assigned successfully') }}');
                            $('.custom-offcanvas').removeClass('open');
                            $('#offcanvasOverlay').removeClass('show');
                            setTimeout(function () { location.reload(); }, 600);
                        } else {
                            toastr.error((response && response.message) || '{{ translate('Something went wrong') }}');
                            $submitBtn.prop('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) || '{{ translate('Something went wrong') }}';
                        toastr.error(msg);
                        $submitBtn.prop('disabled', false);
                    }
                });
            });
        }

        function updateAssignFoodsSelectedCount() {
            // Count from the source-of-truth Set (includes selections that may
            // currently be filtered out of view by the search box).
            $('#assignFoodsSelectedCount').text(assignFoodsSelectedIds.size);
        }

        function updateAssignFoodsSaveState() {
            const isEmpty = $('#assignFoodsListContainer').find('.assign-food-row').length === 0;
            $('#assignFoodsForm').find('button[type="submit"]').prop('disabled', isEmpty);
        }

        // Auto-open assign-foods offcanvas after a category is just created
        document.addEventListener('DOMContentLoaded', function () {
            const params = new URLSearchParams(window.location.search);
            const newId = params.get('assign_foods');
            if (newId) {
                const url = "{{ url('restaurant-panel/restaurant-category/foods') }}/" + newId;
                fetchAssignFoodsOffcanvas(url, '#offcanvas__vendorAssignFoods');

                // Clean the URL so a reload doesn't keep re-opening
                params.delete('assign_foods');
                const cleanUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });
    </script>
@endpush
