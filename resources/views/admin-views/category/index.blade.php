@extends('layouts.admin.app')

@section('title',translate('Add New Main Category'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header pb-0">
            <div class="row g-1 align-items-center">
                <div class="col-sm-6 mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{dynamicAsset('assets/admin/img/category.png')}}" alt="">
                        </div>
                        <span>
                            {{ translate('messages.Main_Category_Setup') }}
                        </span>
                    </h2>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header border-0 pt-3 pb-1">
                <div class="search--button-wrapper">
                    <h5 class="card-title"><span class="card-header-icon">
                        {{translate('messages.Main_Category_List')}}
                        <span class="badge badge-soft-dark ml-2" id="itemCount">{{$categories->total()}}</span>
                    </h5>
                    <form id="campaignFilterForm" class="row g-3 align-items-center">
                        <div class="col-md-6 w-18rem">
                            <select name="priority"
                                    id="campaignFilterSelect"
                                    class="form-control select2-basic">
                                <option value="">{{ translate('messages.select_priority') }}</option>
                                <option value="0" {{ request('priority') == '0' ? 'selected' : '' }}>
                                    {{ translate('messages.normal') }}
                                </option>
                                <option value="1" {{ request('priority') == '1' ? 'selected' : '' }}>
                                    {{ translate('messages.medium') }}
                                </option>
                                <option value="2" {{ request('priority') == '2' ? 'selected' : '' }}>
                                    {{ translate('messages.high') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 w-18rem">
                            <div class="input--group input-group input-group-merge input-group-flush">
                                <input type="search" name="search" value="{{ request()?->search ?? null }}"
                                       class="form-control" placeholder="{{ translate('Ex_:_Search_by_name\Id') }}"
                                       aria-label="{{translate('messages.search_categories')}}">
                                <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="hs-unfold ml-3">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm"
                           href="javascript:;"
                           data-hs-unfold-options='{
                                "target": "#usersExportDropdown",
                                "type": "css-animation"
                            }'>
                            <i class="tio-download-to mr-1"></i> {{translate('messages.export')}}
                        </a>

                        <div id="usersExportDropdown"
                             class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{translate('messages.download_options')}}</span>
                            <a target="__blank" id="export-excel" class="dropdown-item"
                               href="{{route('admin.category.export-categories', ['type'=>'excel', request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{dynamicAsset('assets/admin')}}/svg/components/excel.svg"
                                     alt="Image Description">
                                {{translate('messages.excel')}}
                            </a>
                            <a target="__blank" id="export-csv" class="dropdown-item"
                               href="{{route('admin.category.export-categories', ['type'=>'csv', request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{dynamicAsset('assets/admin')}}/svg/components/placeholder-csv-format.svg"
                                     alt="Image Description">
                                {{translate('messages.csv')}}
                            </a>
                        </div>
                    </div>
                    <div class="">
                        <a href="javascript:void(0)" class="btn btn--primary pull-right offcanvas-trigger"
                           data-target="#offcanvas__customBtn3"
                           data-action="create">
                            <i class="tio-add-circle"></i> {{ translate('Add Main Category') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="table-responsive datatable-custom pb-0">
                <table id="columnSearchDatatable"
                       class="table mb-0 table-borderless table-thead-bordered table-align-middle text-nowrap"
                       data-hs-datatables-options='{
                        "isResponsive": false,
                        "isShowPaging": false,
                        "paging":false,
                    }'>
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('messages.SL') }}</th>
                        <th>{{ translate('messages.Category_Info') }}</th>
                        @if ($categoryWiseTax)
                            <th class="border-0 w--1">{{ translate('messages.Vat/Tax') }}</th>
                        @endif
                        <th>
                            <div class="">
                                {{translate('messages.priority Level')}}
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                      data-original-title="{{ translate('Set how prominently this category should appear to customers') }}"><i
                                        class="tio-info text-gray1 fs-14"></i></span>
                            </div>
                        </th>
                        <th>{{translate('messages.status')}}</th>
                        <th class="text-cetner w-130px">{{translate('messages.action')}}</th>
                    </tr>
                    </thead>

                    <tbody id="table-div">
                    @foreach($categories as $key=>$category)
                        <tr>
                            <td>
                                <div class="pl-3">
                                    {{$key+$categories->firstItem()}}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2 align-items-center">
                                    <img class="avatar border flex-shrink-0"
                                         src="{{ $category['image_full_url'] }}"
                                         alt="{{Str::limit($category['name'], 20,'...')}}">
                                    <div class="d-block font-size-sm text-title">
                                        <div>{{Str::limit($category['name'], 20,'...')}}</div>
                                        <div class="text-muted">{{translate('ID')}} #{{$category->id}}</div>
                                    </div>
                                </div>
                            </td>
                            @if ($categoryWiseTax)
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        @forelse ($category?->taxVats?->pluck('tax.name', 'tax.tax_rate')->toArray() as $key => $item)
                                            <span> {{ $item }} : <span class="font-bold">
                                                    ({{ $key }}%)
                                                </span> </span>
                                            <br>
                                        @empty
                                            <span> {{ translate('messages.N/A') }} </span>
                                        @endforelse
                                    </span>
                                </td>
                            @endif

                            <td>
                                <form action="{{route('admin.category.priority',$category->id)}}" class="priority-form">
                                    <select name="priority" id="priority"
                                            class=" form-control form--control-select priority-select {{$category->priority == 0 ? 'text--title':''}} {{$category->priority == 1 ? 'text--info':''}} {{$category->priority == 2 ? 'text--success':''}} ">
                                        <option class="text--title"
                                                value="0" {{$category->priority == 0?'selected':''}}>{{translate('messages.normal')}}</option>
                                        <option class="text--info"
                                                value="1" {{$category->priority == 1?'selected':''}}>{{translate('messages.medium')}}</option>
                                        <option class="text--success"
                                                value="2" {{$category->priority == 2?'selected':''}}>{{translate('messages.high')}}</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <label class="toggle-switch toggle-switch-sm ml-2"
                                       for="stocksCheckbox{{$category->id}}">
                                    <input type="checkbox"
                                           data-url="{{route('admin.category.status',[$category['id'],$category->status?0:1])}}"
                                           class="toggle-switch-input redirect-url"
                                           id="stocksCheckbox{{$category->id}}" {{$category->status?'checked':''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </td>
                            <td>
                                <div class="btn--container">
                                    <a class="btn btn-sm text-end action-btn info--outline text--info info-hover offcanvas-trigger get_data data-info-show"
                                       data-target="#offcanvas__customBtn3"
                                       data-id="{{ $category['id'] }}"
                                       data-url="{{ route('admin.category.edit', [$category['id']]) }}"
                                       data-action="edit"
                                       href="javascript:"
                                       title="{{ translate('messages.edit_category') }}">
                                        <i class="tio-edit"></i>
                                    </a>
                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                       href="javascript:"
                                       data-id="category-{{$category['id']}}"
                                       data-message="{{ translate('Want_to_delete_this_category_?') }}"
                                       title="{{translate('messages.delete_category')}}">
                                        <i class="tio-delete-outlined"></i>
                                    </a>
                                </div>

                                <form action="{{route('admin.category.delete',[$category['id']])}}" method="post"
                                      id="category-{{$category['id']}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($categories) === 0)
                    <div class="empty--data">
                        <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                    </div>
                @endif
            </div>
            <div class="card-footer pt-0 border-0">
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $categories->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="offcanvas__customBtn3" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100">
        </div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>

@endsection

@push('script_2')
    <script src="{{dynamicAsset('assets/admin')}}/js/view-pages/category-index.js"></script>
    <script src="{{ dynamicAsset('assets/admin/js/offcanvas.js') }}"></script>
    <script>
        "use strict";
        document.addEventListener("DOMContentLoaded", function () {
            const filterSelect = document.getElementById("campaignFilterSelect");
            const form = document.getElementById("campaignFilterForm");

            if (filterSelect && form) {
                filterSelect.addEventListener("change", function () {
                    form.submit();
                });
            }

            $('.select2-basic').select2({
                width: '100%'
            });

            $('#campaignFilterSelect').on('change', function () {
                $('#campaignFilterForm').submit();
            });
        });

        $(document).on('click', '.offcanvas-trigger', function () {
            let action = $(this).data('action');

            if (action === 'create') {
                let url = "{{ route('admin.category.create') }}";
                fetch_data(null, url);
            } else if (action === 'edit') {
                let id = $(this).data('id');
                let url = $(this).data('url');
                fetch_data(id, url);
            }
        });

        function fetch_data(id, url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function () {
                    $('#data-view').empty();
                    $('#loading').show()
                },
                success: function (data) {
                    $("#data-view").append(data.view);
                    initLangTabs();
                    initSelect2Dropdowns();
                    initFileUpload();
                    initCloseButtons();
                    removeImage();
                    removeMetaImage();
                    checkPreExistingImages();
                    syncImage();
                    checked();
                    initTextMaxLimit();
                    $(".multiple-select2").select2DynamicDisplay();
                },
                complete: function () {
                    $('#loading').hide()
                }
            })
        }


        function initLangTabs() {
            const langLinks = document.querySelectorAll(".lang_link1");
            langLinks.forEach(function(langLink) {
                langLink.addEventListener("click", function(e) {
                    e.preventDefault();
                    langLinks.forEach(function(link) {
                        link.classList.remove("active");
                    });
                    this.classList.add("active");
                    document.querySelectorAll(".lang_form1").forEach(function(form) {
                        form.classList.add("d-none");
                    });
                    let form_id = this.id;
                    let lang = form_id.substring(0, form_id.length - 5);
                    $("#" + lang + "-form1").removeClass("d-none");
                    if (lang === "default") {
                        $(".default-form1").removeClass("d-none");
                    }
                });
            });
        }
        function initSelect2Dropdowns() {
            $('.js-select2-custom1, .multiple-select2').select2({
                placeholder: 'Select tax rate',
                allowClear: true
            });
        }

        function initFileUpload() {
            // Show the selected image inside its own upload placeholder. This mirrors
            // the theme's handleFileChange in common.js, which isn't active here because
            // this page defines its own initFileUpload() that shadows the theme one.
            $('.single_file_input').off('change.categoryPreview').on('change.categoryPreview', function (e) {
                var $card = $(this).closest('.upload-file');
                var file = e.target.files && e.target.files[0];
                if (!file) {
                    return;
                }
                var reader = new FileReader();
                reader.onload = function (ev) {
                    $card.find('.upload-file-textbox').hide();
                    $card.find('.upload-file-img').attr('src', ev.target.result).show();
                    $card.find('.remove_btn').css('opacity', 1);
                    $card.find('.overlay').addClass('show');
                    $card.addClass('input-disabled');
                };
                reader.readAsDataURL(file);
            });

            // The theme's edit/remove overlay handlers (common.js initFileUpload) never
            // run here because this page shadows initFileUpload() and the upload fields
            // are injected via AJAX after document ready. Re-bind them for both the
            // category image and meta image overlays.
            $('.edit_btn').off('click.categoryEdit').on('click.categoryEdit', function (e) {
                e.stopImmediatePropagation();
                var $card = $(this).closest('.upload-file');
                // The file input sits above the overlay (z-index) and is click-blocked
                // while input-disabled; re-enable it before opening the file picker.
                $card.removeClass('input-disabled');
                $card.find('.single_file_input').trigger('click');
            });

            $('.remove_btn').off('click.categoryRemove').on('click.categoryRemove', function () {
                // No stopImmediatePropagation here: the per-field removeImage()/
                // removeMetaImage() listeners live on this same button and must still
                // fire. We also set the delete flag directly so removal works even if
                // those listeners are absent or re-ordered — without it the server
                // (CategoryController@update) keeps the previous meta image on save.
                var $card = $(this).closest('.upload-file');
                $card.find('input[name="meta_image_remove"], input[name="image_remove"]').val('1');
                $card.find('.single_file_input').val('').removeAttr('disabled');
                $card.find('.upload-file-img').attr('src', '').hide();
                $card.find('.upload-file-textbox').show();
                $card.find('.overlay').removeClass('show');
                $card.find('.remove_btn').css('opacity', 0);
                $card.removeClass('input-disabled');
            });
        }

        function initCloseButtons() {
            $('.offcanvas-close, #offcanvasOverlay').off('click').on('click', function () {
                $('.custom-offcanvas').removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
            });
        }

        function removeImage() {
            var removeBtn = document.getElementById('remove_image_btn');
            var removeFlag = document.getElementById('image_remove');
            var fileInput = document.querySelector('input[name="image"]');
            if (removeBtn && removeFlag) {
                removeBtn.addEventListener('click', function () {
                    removeFlag.value = '1';
                    if (fileInput) {
                        fileInput.removeAttribute('disabled');
                        fileInput.setAttribute('required', 'required');
                        fileInput.value = '';
                        fileInput.closest('.upload-file__wrapper').querySelector('.upload-file-textbox').style.display = 'block';
                    }
                });
            }
        }

        function removeMetaImage() {
            var removeBtn = document.getElementById('remove_meta_image_btn');
            var removeFlag = document.getElementById('meta_image_remove');
            var fileInput = document.querySelector('input[name="meta_image"]');
            if (removeBtn && removeFlag) {
                removeBtn.addEventListener('click', function () {
                    removeFlag.value = '1';
                    if (fileInput) {
                        fileInput.removeAttribute('disabled');
                        fileInput.value = '';
                        fileInput.closest('.upload-file__wrapper').querySelector('.upload-file-textbox').style.display = 'block';
                    }
                });
            }
        }

        function syncImage() {
            const categoryImageInput = document.querySelector('input[name="image"]');
            const metaImagePreview = document.getElementById('meta_image_preview');
            // The meta image file input only has name="meta_image" (no id), so resolve
            // it by name — otherwise metaImageInput stays null and the file is never
            // attached to the form (meta image would not be saved).
            const metaImageInput = document.querySelector('input[name="meta_image"]');
            const actionType=document.querySelector('input[name="action_type"]').value;


            let metaImageManuallyChanged = false;

            if (metaImageInput) {
                metaImageInput.addEventListener('change', function () {
                    metaImageManuallyChanged = true;
                });
            }

            if (categoryImageInput && metaImagePreview && actionType === 'add') {
                categoryImageInput.addEventListener('change', function (e) {
                    const file = e.target.files[0];

                    if (file) {
                        const reader = new FileReader();

                        reader.onload = function (event) {
                            metaImagePreview.src = event.target.result;
                            metaImagePreview.style.display = 'block';

                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);

                            if (metaImageInput) {
                                metaImageInput.files = dataTransfer.files;
                            }
                            const metaUploadFile = document.getElementById('meta-image-upload');

                            if (metaUploadFile) {
                                metaUploadFile.classList.add('overlay-show');
                                metaUploadFile.style.display = 'none';

                            }
                        };

                        reader.readAsDataURL(file);
                    }
                });
            }
        }
        function checked(){

            $('input[name="meta_index"][value="noindex"]').on('change', function () {
                if ($(this).is(':checked')) {
                    $('input[name="meta_no_follow"]').prop('checked', true);
                    $('input[name="meta_no_image_index"]').prop('checked', true);
                    $('input[name="meta_no_archive"]').prop('checked', true);
                    $('input[name="meta_no_snippet"]').prop('checked', true);
                }
            });

            $('input[name="meta_index"][value="index"]').on('change', function () {
                if ($(this).is(':checked')) {
                    $('input[name="meta_no_follow"]').prop('checked', false);
                    $('input[name="meta_no_image_index"]').prop('checked', false);
                    $('input[name="meta_no_archive"]').prop('checked', false);
                    $('input[name="meta_no_snippet"]').prop('checked', false);
                }
            });
        }
    </script>
@endpush
