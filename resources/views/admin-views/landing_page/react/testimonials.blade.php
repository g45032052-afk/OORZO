@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))

@section('content')


    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h1 class="page-header-title text-capitalize fs-24">
                    <div class="card-header-icon d-inline-flex mr-2 img">
                        <img src="{{ dynamicAsset('assets/admin/img/landing-page.png') }}" class="mw-26px"
                            alt="public">
                    </div>
                    <span>
                        {{ translate('React_Landing_Page') }}
                    </span>
                </h1>
            </div>
        </div>
        <div class="mb-15">
            <div class="js-nav-scroller tabs-slide-wrap tabs-slide-language hs-nav-scroller-horizontal">
                @include('admin-views.landing_page.top_menu.react_landing_menu')
                <div class="arrow-area">
                    <div class="button-prev align-items-center">
                        <button type="button"
                            class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                            <i class="tio-chevron-left fs-24"></i>
                        </button>
                    </div>
                    <div class="button-next align-items-center">
                        <button type="button"
                            class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                            <i class="tio-chevron-right fs-24"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card py-3 px-xxl-4 px-3 mb-15 mt-4">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-xxl-9 col-lg-8 col-md-7 col-sm-6">
                    <div class="">
                        <h3 class="mb-1">{{ translate('Show_on_Website') }}</h3>
                        <p class="mb-0 gray-dark fs-12">
                            {{ translate('If you turn of the availability status, this section will not show in the website') }}
                        </p>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6">
                    <div class="py-2 px-3 rounded d-flex justify-content-between border align-items-center w-300">
                        <h5 class="text-capitalize fw-normal mb-0">{{ translate('Status') }}</h5>

                        <form
                            action="{{ route('admin.landing_page.statusUpdate', ['type' => 'react_landing_page', 'key' => 'testimonial_section_status']) }}"
                            method="get" id="CheckboxStatus_form">
                        </form>
                        <label class="toggle-switch toggle-switch-sm" for="CheckboxStatus">
                            <input type="checkbox" data-id="CheckboxStatus" data-type="status"
                                data-image-on="{{ dynamicAsset('assets/admin/img/status-ons.png') }}"
                                data-image-off="{{ dynamicAsset('assets/admin/img/off-danger.png') }}"
                                data-title-on="{{ translate('Do you want turn on this section ?') }}"
                                data-title-off="{{ translate('Do you want to turn off this section ?') }}"
                                data-text-on="<p>{{ translate('If you turn on this section will be show in react landing page.') }}"
                                data-text-off="<p>{{ translate('If you turn off this section will not be show in react landing page.') }}</p>"
                                class="toggle-switch-input  status dynamic-checkbox" id="CheckboxStatus"
                                {{ $testimonial_section_status?->value ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-15">
            <div class="card-header">
                <div class="">
                    <h3 class="mb-1">{{ translate('Testimonial Section') }}</h3>
                    <p class="mb-0 gray-dark fs-12">
                         {{ translate('Manage the main title for the customer reviews section.') }}
                    </p>
                </div>
            </div>
            <div class="card-body">
                <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'react-testimonial') }}" method="POST">
                    @csrf
                    <div class="card-custom-xl">
                        <div class="bg-light2 p-xl-20 p-3 rounded">
                            <div class="card-body p-0 mb-3">
                                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                    @if ($language)
                                        <ul class="nav nav-tabs mb-4 border-">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active" href="#"
                                                    id="default-link">{{ translate('messages.default') }}</a>
                                            </li>
                                            @foreach ($language as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link" href="#"
                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div class="lang_form" id="default-form">
                                    <input type="hidden" name="lang[]" value="default">
                                    <div class="form-group">
                                        <label class="input-label fw-400"
                                            for="default_title">{{ translate('messages.title') }}
                                            ({{ translate('messages.Default') }}) <span class="text-danger">*</span>
                                            <span class="form-label-secondary"
                                                data-toggle="tooltip"
{{--                                                  data-maxlength="50" --}}
                                                  data-placement="right"
                                                data-original-title="{{ translate('The main headline for the customer reviews section.') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                        <input required type="text" name="testimonial_section_title[]" id="default_title"
                                            maxlength="50" class="form-control"
{{--                                               data-maxlength="50"--}}
                                            placeholder="{{ translate('Title') }}"
                                            value="{{ $testimonial_section_title?->getRawOriginal('value') ?? '' }}">
                                        <div class="d-flex justify-content-end">
                                            <span class="text-body-light text-right d-block mt-1">0/50</span>
                                        </div>
                                    </div>

                                </div>
                                @if ($language)
                                    @forelse($language as $lang)
                                        <?php
                                        if ($testimonial_section_title?->translations) {
                                            $testimonial_section_title_translate = [];
                                            foreach ($testimonial_section_title->translations as $t) {
                                                if ($t->locale == $lang && $t->key == 'testimonial_section_title') {
                                                    $testimonial_section_title_translate[$lang]['value'] = $t->value;
                                                }
                                            }
                                        }
                                        ?>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">

                                        <div class="d-none lang_form" id="{{ $lang }}-form">
                                            <div class="form-group">
                                                <label class="input-label fw-400"
                                                    for="default_title">{{ translate('messages.title') }}
                                                    ({{ strtoupper($lang) }})
                                                    <span class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ translate('The main headline for the customer reviews section.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input type="text" name="testimonial_section_title[]"
                                                    id="default_title" maxlength="50" class="form-control"
                                                    placeholder="{{ translate('Title') }}"
{{--                                                       data-maxlength="50"--}}
                                                    value="{{ $testimonial_section_title_translate[$lang]['value'] ?? '' }}">
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                </div>
                                            </div>

                                        </div>
                                    @empty
                                    @endforelse
                                @endif
                            </div>
                            <div class="btn--container justify-content-end mt-4">
                                <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                                <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
                            </div>
                        </div>
                </form>
            </div>
        </div>

        <div class="card mb-15">
            <div class="card-header">
                <div class="">
                    <h3 class="mb-1">{{ translate('Add Reviewers ') }}</h3>
                    <p class="mb-0 gray-dark fs-12">
                         {{ translate('Add and manage individual customer testimonials.') }}
                    </p>
                </div>
            </div>
            <div class="card-body">

                <form class="validate-form" action="{{ route('admin.react_landing_page.reactTestimonialStore') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="card-custom-xl mb-15">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="bg-light2 p-xl-20 p-3 rounded">
                                    <div class="card-body p-0">
                                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                            @if ($language)
                                                <ul class="nav nav-tabs mb-4 border-0">
                                                    <li class="nav-item">
                                                        <a class="nav-link lang_link1 active" href="#"
                                                            id="default-link1">{{ translate('messages.default') }}</a>
                                                    </li>
                                                    @foreach ($language as $lang)
                                                        <li class="nav-item">
                                                            <a class="nav-link lang_link1" href="#"
                                                                id="{{ $lang }}-link1">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                        <div class="lang_form-float default-form-float">
                                            <div class="row g-3">
                                                <input type="hidden" name="lang[]" value="default">
                                                <div class="col-md-12">
                                                    <label for="name"
                                                        class="form-label fw-400">{{ translate('Reviewer Name') }}
                                                        ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                        <span class="input-label-secondary text--title"
                                                            data-toggle="tooltip" data-placement="right"
                                                            data-original-title="{{ translate('The full name of the customer giving the testimonial.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <input required id="name"
                                                           data-maxlength="50"
                                                           type="text"
                                                        name="name[]" class="form-control"
                                                        placeholder="{{ translate('Ex: John') }}">
                                                    <div class="d-flex justify-content-end">
                                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="review"
                                                        class="form-label fw-400">{{ translate('Review') }}
                                                        ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                        <span class="input-label-secondary text--title"
                                                            data-toggle="tooltip" data-placement="right"
                                                            data-original-title="{{ translate(' The customer\'s direct quote. Keep it under 100 characters.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <input required id="review" data-maxlength="100" type="text"
                                                        name="review[]" class="form-control"
                                                        placeholder="{{ translate('Ex:  Great Service') }}">
                                                    <div class="d-flex justify-content-end">
                                                        <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        @forelse($language as $lang)


                                            <div class="d-none lang_form-float" id="{{ $lang }}-form-float">
                                                <div class="row g-3">
                                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                    <div class="col-md-12">
                                                        <label for="name{{ $lang }}"
                                                            class="form-label fw-400">{{ translate('Reviewer Name') }}
                                                            ({{ strtoupper($lang) }})
                                                            <span class="input-label-secondary text--title"
                                                                data-toggle="tooltip" data-placement="right"
                                                                data-original-title="{{ translate('The full name of the customer giving the testimonial.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                        <input id="name{{ $lang }}"
                                                               data-maxlength="50"
                                                            type="text" name="name[]" class="form-control"
                                                            placeholder="{{ translate('Ex: john') }}">
                                                        <div class="d-flex justify-content-end">
                                                            <span
                                                                class="text-body-light text-right d-block mt-1">0/50</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label for="review{{ $lang }}"
                                                            class="form-label fw-400">{{ translate('Review') }}
                                                            ({{ strtoupper($lang) }})
                                                            <span class="input-label-secondary text--title"
                                                                data-toggle="tooltip" data-placement="right"
                                                                data-original-title="{{ translate(' The customer\'s direct quote. Keep it under 100 characters.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                        <input id="review{{ $lang }}" data-maxlength="100"
                                                            type="text" name="review[]" class="form-control"
                                                            placeholder="{{ translate('Ex: Great Service') }}">
                                                        <div class="d-flex justify-content-end">
                                                            <span
                                                                class="text-body-light text-right d-block mt-1">0/100</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div
                                    class="bg-light2 d-flex align-items-center justify-content-center p-xl-20 p-4 rounded h-100">
                                    <div class="card-body p-0">
                                        <div>
                                            <div class="mb-xxl-4 pb-1 mb-xl-4 mb-3 text-center">
                                                <h5 class="mb-1">{{ translate('Reviewer Image') }}</h5>
                                                <p class="mb-0 fs-12">{{ translate('Upload Reviewer Image') }}</p>
                                            </div>
                                            <div class="text-center">
                                                @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'image',
                                                'ratio' => '1:1',
                                                'isRequired' => true,
                                                'existingImage' =>null,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                                'pixel'=>'200 x 230'
                                                ])
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end mt-4">
                            <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                            <button type="submit" class="btn btn--primary">{{ translate('Add') }}</button>
                        </div>
                    </div>

                </form>

                <div class="card-custom-static">
                    <div class="card-header border-0 p-20">
                        <div class="search--button-wrapper">
                            <h5 class="card-title d-flex align-items-center">{{ translate('messages.Testimonial List') }}
                            </h5>
                            <form class="search-form">
                                <div class="input-group input--group">
                                    <input id="datatableSearch_" type="search" name="search"
                                        value="{{ request()?->search ?? null }}" class="form-control"
                                        placeholder="{{ translate('Search_Name') }}"
                                        aria-label="{{ translate('messages.search') }}">
                                    <button type="submit" class="btn btn--secondary secondary-cmn"><i
                                            class="tio-search"></i></button>

                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-20 pt-0">
                        <div class="table-responsive datatable-custom py-0">
                            <table
                                class="table table-borderless table-thead-borderless table-align-middle table-nowrap card-table">
                                <thead class="thead-light border-0">
                                    <tr>
                                        <th class="border-top-0">{{ translate('messages.sl') }}</th>
                                        <th class="border-top-0">{{ translate('messages.image') }}</th>
                                        <th class="border-top-0">{{ translate('Reviewer Name') }} </th>
                                        <th class="border-top-0">{{ translate('Reviews') }} </th>
                                        <th class="border-top-0">{{ translate('Status') }}</th>
                                        <th class="text-center border-top-0">{{ translate('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($testimonials as $key => $testimonial)
                                        <tr>
                                            <td>{{ $key + $testimonials->firstItem() }}</td>
                                            <td>
                                                <a class="w-50px h-50px overflow-hidden rounded align-items-center"
                                                    href="#0">
                                                    <img src="{{ $testimonial?->image_full_url ?? dynamicAsset('assets/admin/img/aspect-1.png') }}"
                                                        width="48px" class="rounded overflow-hidden" alt="img">
                                                </a>
                                            </td>
                                            <td>
                                                {{ $testimonial?->name }}
                                            </td>
                                            <td>
                                                <div
                                                    class="text--title word-break min-w-100px line-limit-2 max-w-220px text-wrap">
                                                    {{ $testimonial?->review }}
                                                </div>
                                            </td>
                                            <td>

                                                <label class="toggle-switch toggle-switch-sm">
                                                    <input type="checkbox"
                                                        data-id="testimonial_status_{{ $testimonial->id }}"
                                                        data-type="status"
                                                        data-image-on="{{ dynamicAsset('assets/admin/img/modal/testimonial-on.png') }}"
                                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal/testimonial-off.png') }}"
                                                        data-title-on="{{ translate('Want_to_Enable_this') }} <strong>{{ translate('Testimonial') }}</strong>"
                                                        data-title-off="{{ translate('Want_to_Disable_this') }} <strong>{{ translate('Testimonial') }}</strong>"
                                                        data-text-on="<p>{{ translate('If_enabled,_it_will_be_shown_on_the_React_Landing_page') }}</p>"
                                                        data-text-off="<p>{{ translate('If_disabled,_it_will_be_hidden_from_the_React_Landing_page') }}</p>"
                                                        class="status toggle-switch-input dynamic-checkbox"
                                                        id="testimonial_status_{{ $testimonial->id }}"
                                                        {{ $testimonial->status ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                                <form
                                                    action="{{ route('admin.react_landing_page.reactTestimonialStatus', [$testimonial->id, $testimonial->status ? 0 : 1]) }}"
                                                    method="get" id="testimonial_status_{{ $testimonial->id }}_form">
                                                </form>

                                            </td>
                                            <td>
                                                <div class="btn--container justify-content-center">
                                                    <a class="btn btn-sm text-end action-btn info--outline text--info info-hover offcanvas-trigger get_data data-info-show"
                                                        data-target="#offcanvas__customBtn3"
                                                        data-id="{{ $testimonial['id'] }}"
                                                        data-url="{{ route('admin.react_landing_page.reactTestimonialEdit', [$testimonial['id']]) }}"
                                                        href="javascript:"
                                                        title="{{ translate('messages.edit_testimonial') }}"><i
                                                            class="tio-edit"></i>
                                                    </a>
                                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert-testimonial"
                                                        href="javascript:"
                                                        data-id="testimonial-{{ $testimonial['id'] }}"
                                                        data-message="{{ translate('Want_to_Delete_this_testimonial') }}"
                                                        data-message-2="{{ translate('If_yes,_the_testimonial_will_be_removed_from_this_list') }}"
                                                        title="{{ translate('messages.delete_testimonial') }}"><i
                                                            class="tio-delete-outlined"></i>
                                                    </a>
                                                    <form
                                                        action="{{ route('admin.react_landing_page.reactTestimonialDestroy', [$testimonial['id']]) }}"
                                                        method="post" id="testimonial-{{ $testimonial['id'] }}">
                                                        @csrf @method('delete')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if (count($testimonials) === 0)
                                <div class="empty--data">
                                    <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="public">
                                    <h5>
                                        {{ translate('no_data_found') }}
                                    </h5>
                                </div>
                            @endif
                        </div>
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $testimonials->appends(request()->all())->links() !!}
                                </div>
                            </div>
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

    @include('admin-views.landing_page.react.partials.testimonial_guideline')

@endsection

@push('script_2')
    <script>
        $(".lang_link1").click(function(e) {
            e.preventDefault();
            $(".lang_link1").removeClass('active');
            $(".lang_form-float").addClass('d-none');
            $(this).addClass('active');
            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 6);
            $("#" + lang + "-form-float").removeClass('d-none');
            if (lang === 'default') {
                $(".default-form-float").removeClass('d-none');
            }
        })
        $(document).on('click', '.form-alert-testimonial', function() {
            Swal.fire({
                title: $(this).data('message'),
                text: $(this).data('message-2'),
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('messages.No') }}',
                confirmButtonText: '{{ translate('messages.Yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $('#' + $(this).data('id')).submit()
                }
            })
        });


        document.getElementById('reset_btn').addEventListener('click', function() {
            const select = $('#tax__rate');
            select.val(null).trigger('change');
        });


        $(document).on('click', '.data-info-show', function() {
            let id = $(this).data('id');
            let url = $(this).data('url');
            fetch_data(id, url)
        })

        function fetch_data(id, url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function() {
                    $('#data-view').empty();
                    $('#loading').show()
                },
                success: function(data) {
                    $("#data-view").append(data.view);
                    initLangTabs();
                    initSelect2Dropdowns();
                    initTextMaxLimit();
                    checkPreExistingImages();
                    if (window.formValidation) {
                        window.formValidation.attachValidators();
                    }
                    if (window.FileUploadValidator) {
                        window.fileValidators = window.fileValidators.concat(window.FileUploadValidator.initByClass('single_file_input'));
                    }
                },
                complete: function() {
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

            $('.offcanvas-close, #offcanvasOverlay').on('click', function() {
                $('.custom-offcanvas').removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
            });
        }
    </script>
@endpush
