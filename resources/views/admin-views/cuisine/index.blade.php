@extends('layouts.admin.app')

@section('title',translate('messages.Add new cuisine'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{dynamicAsset('assets/admin/img/zone.png')}}" alt="">
                        </div>
                        <span>
                            {{translate('cuisine')}}
                        </span>
                    </h2>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card mt-3">
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title"><span class="card-header-icon">
                        <i class="tio-cuisine-outlined"></i>
                    </span> {{translate('messages.cuisine_list')}}<span class="badge badge-soft-dark ml-2"
                                                                        id="itemCount">{{$cuisine->total()}}</span></h5>
                    <form>
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input type="search" value="{{ request()?->search ?? null }}" name="search"
                                   class="form-control" placeholder="{{ translate('Ex:_search_by_name_or_ID') }}"
                                   aria-label="{{translate('messages.search_cuisine')}}">
                            <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i>
                            </button>
                        </div>
                        <!-- End Search -->
                    </form>

                    <div class="hs-unfold">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn export--btn btn-outline-primary btn--primary font--sm"
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
                            <a id="export-excel" class="dropdown-item"
                               href="{{route("admin.cuisine.export",['type'=>'excel' , request()->getQueryString() ])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{dynamicAsset('assets/admin')}}/svg/components/excel.svg"
                                     alt="Image Description">
                                {{translate('messages.excel')}}
                            </a>
                            <a id="export-csv" class="dropdown-item"
                               href="{{route("admin.cuisine.export",['type'=>'csv' , request()->getQueryString() ])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{dynamicAsset('assets/admin')}}/svg/components/placeholder-csv-format.svg"
                                     alt="Image Description">
                                {{translate('messages.csv')}}
                            </a>
                        </div>
                    </div>
                    <a href="javascript:void(0)" class="btn btn--primary pull-right offcanvas-trigger"
                       data-target="#offcanvas__customBtn3"
                       data-action="create">
                        {{translate('Add_New_Cuisine')}}
                    </a>
                </div>
            </div>
            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable"
                       class="table table-borderless table-thead-bordered table-align-middle"
                       data-hs-datatables-options='{
                        "isResponsive": false,
                        "isShowPaging": false,
                        "paging":false,
                    }'>
                    <thead class="thead-light">
                    <tr>
                        <th class="w-25px"> {{ translate('messages.sl') }}</th>
                        <th class="w-25px">{{translate('messages.cuisine_id')}}</th>
                        <th class="w-130px">{{translate('messages.cuisine_name')}}</th>
                        <th class="text-center w-130px">{{translate('messages.total_restaurant')}}</th>
                        <th class="text-center w-130px"> {{translate('messages.status')}}</th>
                        <th class="text-center w-130px">{{translate('messages.action')}}</th>
                    </tr>
                    </thead>

                    <tbody id="table-div">
                    @foreach($cuisine as $key=>$cu)
                        <tr>
                            <td>
                                <div class="pl-3">
                                    {{$key+$cuisine->firstItem()}}
                                </div>
                            </td>
                            @php($img_src =  isset($cu->image) ?  dynamicStorage('storage/app/public/cuisine').'/'.$cu['image'] : dynamicAsset('assets/admin/img/900x400/img2.jpg')  )
                            <td>

                                <div class="pl-2">{{ $cu->id }}</div>
                            </td>
                            <td>
                            <span class="d-block font-size-sm text-body pl-2">
                                {{Str::limit($cu['name'], 20,'...')}}
                            </span>
                            </td>
                            <td>
                                <div class="text-center"> {{  $cu->restaurants_count }}</div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center">
                                    <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$cu->id}}">
                                        <input type="checkbox"
                                               data-url="{{route('admin.cuisine.status',[$cu['id'],$cu->status?0:1])}}"
                                               class="toggle-switch-input redirect-url"
                                               id="stocksCheckbox{{$cu->id}}" {{$cu->status?'checked':''}}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </td>

                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn btn-sm text-end action-btn info--outline text--info info-hover offcanvas-trigger get_data data-info-show"
                                       data-target="#offcanvas__customBtn3"
                                       data-id="{{ $cu['id'] }}"
                                       data-url="{{ route('admin.cuisine.edit', [$cu['id']]) }}"
                                       data-action="edit"
                                       href="javascript:"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                       href="javascript:"
                                       data-id="cuisine-{{$cu['id']}}"
                                       data-message="{{ translate('Want_to_delete_this_cuisine_?') }}"
                                       title="{{translate('messages.delete_cuisine')}}"><i
                                            class="tio-delete-outlined"></i>
                                    </a>
                                </div>

                                <form action="{{route('admin.cuisine.delete',['id' =>$cu['id']])}}" method="post"
                                      id="cuisine-{{$cu['id']}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($cuisine) === 0)
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
                            {!! $cuisine->links() !!}
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
    <script src="{{dynamicAsset('assets/admin/js/view-pages/cuisine.js')}}"></script>
    <script>

        "use strict";
        // $('.reset-btn').on('click',function (){
        //     let image = $(this).data('image-src')
        //     $('#name').val(null);
        //     $('.preview-image').attr('src', image);
        //     $('#image').val(null);
        // });


        "use strict";
        $('.reset-btn').on('click', function () {
            let image = $(this).data('image-src')
            $('#name').val(null);
            $('.viewer').attr('src', image);
            $('#image').val(null);
        });
        $('#reset_btn1').on('click', function () {
            $('#name').val(null);
            $('.image_on_add').attr('src', "{{dynamicAsset('assets/admin/img/upload-6.png')}}");
            $('#image').val(null);
        });


    </script>
@endpush
@push('script_2')
    <script src="{{dynamicAsset('assets/admin')}}/js/view-pages/category-index.js"></script>
    <script src="{{ dynamicAsset('assets/admin/js/offcanvas.js') }}"></script>
    <script>
        "use strict";

        $(document).on('click', '.offcanvas-trigger', function () {
            let action = $(this).data('action');

            if (action === 'create') {
                let url = "{{ route('admin.cuisine.create') }}";
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
            const langLinks = document.querySelectorAll(".lang_link");

            langLinks.forEach(function (langLink) {
                langLink.addEventListener("click", function (e) {
                    e.preventDefault();

                    let section = this.parentElement;
                    while (section && section !== document.body) {
                        if (section.querySelector('.nav-tabs') && section.querySelector('.lang_form')) {
                            break;
                        }
                        section = section.parentElement;
                    }
                    section = section || document;

                    section.querySelectorAll(".lang_link").forEach(function (link) {
                        link.classList.remove("active");
                    });
                    this.classList.add("active");

                    section.querySelectorAll(".lang_form").forEach(function (form) {
                        form.classList.add("d-none");
                    });

                    let form_id = this.id;
                    let lang = form_id.split('-link')[0];
                    let suffix = form_id.substring(form_id.indexOf('-link') + 5);

                    $(section).find("#" + lang + "-form" + suffix).removeClass("d-none");
                    $(section).find("#" + lang + "-form1" + suffix).removeClass("d-none");
                    $(section).find("#" + lang + "-form2" + suffix).removeClass("d-none");
                    $(section).find("#" + lang + "-form3" + suffix).removeClass("d-none");
                    $(section).find("#" + lang + "-form4" + suffix).removeClass("d-none");

                    if (lang === "default") {
                        $(section).find(".default-form").removeClass("d-none");
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
            // Reinitialize file upload handlers if needed
            $('.single_file_input').off('change').on('change', function () {
                // Your file upload logic here
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
            console.log('clicked')
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
            const metaImageInput = document.getElementById('meta_image');
            const actionType = document.querySelector('input[name="action_type"]').value;


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

        function checked() {

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
