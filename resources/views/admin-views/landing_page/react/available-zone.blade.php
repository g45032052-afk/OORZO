@extends('layouts.admin.app')

@section('title',translate('messages.react_landing_page'))

@section('content')
<div class="content container-fluid">
    <div class="page-header pb-0">
        <div class="d-flex flex-wrap justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{dynamicAsset('assets/admin/img/landing-page.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{ translate('messages.react_landing_pages') }}
                </span>
            </h1>
        </div>
    </div>
    <div class="mb-4 mt-2">
        <div class="js-nav-scroller tabs-slide-wrap tabs-slide-language hs-nav-scroller-horizontal">
            @include('admin-views.landing_page.top_menu.react_landing_menu')
            <div class="arrow-area">
                <div class="button-prev align-items-center">
                    <button type="button" class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                        <i class="tio-chevron-left fs-24"></i>
                    </button>
                </div>
                <div class="button-next align-items-center">
                    <button type="button" class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                        <i class="tio-chevron-right fs-24"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-15">
            <div class="card-header pt-3">
                <div class="row g-3 w-100">
                    <div class="col-sm-6">
                        <h3 class="mb-1">{{ translate('Available zone section') }}</h3>
                        <p class="mb-0 gray-dark fs-12">{{ translate('Manage delivery zones or cities available for your service.') }}</p>
                    </div>
                    <div class="col-sm-6">

                        <form
                            action="{{ route('admin.landing_page.statusUpdate', ['type' => 'react_landing_page', 'key' => 'react_available_zone_status']) }}"
                            method="get" id="AvailableZoneCheckboxStatus_form">
                        </form>
                        <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control" for="AvailableZoneCheckboxStatus">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <span class="line--limit-1 text--primary">
                                        {{translate('messages.Status') }}
                                    </span>
                                </span>
                            <input type="checkbox" data-id="AvailableZoneCheckboxStatus" data-type="status"
                                   data-image-on="{{ dynamicAsset('assets/admin/img/status-ons.png') }}"
                                   data-image-off="{{ dynamicAsset('assets/admin/img/off-danger.png') }}"
                                   data-title-on="{{ translate('Do you want turn on this section ?') }}"
                                   data-title-off="{{ translate('Do you want to turn off this section ?') }}"
                                   data-text-on="<p>{{ translate('If you turn on this section will be show in react landing page.') }}"
                                   data-text-off="<p>{{ translate('If you turn off this section will not be show in react landing page.') }}</p>"
                                   class="toggle-switch-input  status dynamic-checkbox" id="AvailableZoneCheckboxStatus"
                                {{ $react_available_zone_status?->value ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>

                    </div>
                </div>
            </div>
        <form class="validate-form" id="zone-setup-form" action="{{ route('admin.react_landing_page.availableZoneUpdate') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="page_type" value="react_landing_page">
            <div class="card-body">
                <div class="card-custom-xl">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="bg-light2 p-xl-20 p-3 rounded mb-10px">
                                <div class="card-body p-0">
                                    @if($language)
                                            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                        <ul class="nav nav-tabs mb-4">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active"
                                                   href="#"
                                                   id="default-link">{{ translate('Default') }}</a>
                                            </li>
                                            @foreach ($language as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link"
                                                       href="#"
                                                       id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                            </div>
                                    @endif
                                    @if ($language)
                                        <div class="lang_form"
                                             id="default-form">
                                            <div class="form-group">
                                                <label class="input-label"
                                                       for="default_title">{{ translate('messages.title') }}
                                                    ({{ translate('messages.Default') }}) <span class="text-danger">*</span>
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                          data-original-title="{{ translate('This is the main headline for the delivery zones section.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input required type="text" name="available_zone_title[]" id="default_title"
                                                       data-maxlength="50"
                                                       class="form-control" placeholder="{{ translate('messages.title') }}" value="{{$available_zone_title?->getRawOriginal('value')}}"
                                                >
                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            <div class="form-group mb-0">
                                                <label class="input-label"
                                                       for="exampleFormControlInput1">{{ translate('messages.short_description') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('This text should explain how users can check for delivery to their location and reassure them about your expanding network. Keep under 100 characters.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <textarea required type="text" name="available_zone_short_description[]" data-maxlength="100" placeholder="{{translate('messages.short_description')}}" class="form-control min-h-90px ckeditor">{{$available_zone_short_description?->getRawOriginal('value')}}</textarea>
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                </div>
                                            </div>
                                        </div>
                                        @foreach ($language as $lang)
                                                <?php
                                                    if(isset($available_zone_title->translations)&&count($available_zone_title->translations)){
                                                        $available_zone_title_translate = [];
                                                        foreach($available_zone_title->translations as $t)
                                                        {
                                                            if($t->locale == $lang && $t->key=='available_zone_title'){
                                                                $available_zone_title_translate[$lang]['value'] = $t->value;
                                                            }
                                                        }

                                                    }
                                                    if(isset($available_zone_short_description->translations)&&count($available_zone_short_description->translations)){
                                                        $available_zone_short_description_translate = [];
                                                        foreach($available_zone_short_description->translations as $t)
                                                        {
                                                            if($t->locale == $lang && $t->key=='available_zone_short_description'){
                                                                $available_zone_short_description_translate[$lang]['value'] = $t->value;
                                                            }
                                                        }

                                                    }
                                                ?>
                                            <div class="d-none lang_form"
                                                 id="{{ $lang }}-form">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                           for="{{ $lang }}_title">{{ translate('messages.title') }}
                                                        ({{ strtoupper($lang) }})
                                                        <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('This is the main headline for the delivery zones section.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <input type="text" name="available_zone_title[]"
                                                           data-maxlength="50"
                                                           id="{{ $lang }}_title"
                                                           class="form-control" value="{{ $available_zone_title_translate[$lang]['value']??'' }}" placeholder="{{ translate('messages.title') }}">
                                                    <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                <div class="form-group mb-0">
                                                    <label class="input-label"
                                                           for="exampleFormControlInput1">{{ translate('messages.short_description') }} ({{ strtoupper($lang) }})
                                                        <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('This text should explain how users can check for delivery to their location and reassure them about your expanding network. Keep under 100 characters.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <textarea type="text" name="available_zone_short_description[]" data-maxlength="100" placeholder="{{translate('messages.short_description')}}" class="form-control min-h-90px ckeditor">{{ $available_zone_short_description_translate[$lang]['value']??'' }}</textarea>
                                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="default-form">
                                            <div class="form-group">
                                                <label class="input-label"
                                                       for="exampleFormControlInput1">{{ translate('messages.title') }} ({{ translate('messages.default') }})
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                          data-original-title="{{ translate('This is the main headline for the delivery zones section.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input type="text" name="available_zone_title[]"
{{--                                                       data-maxlength="50" --}}
                                                       class="form-control"
                                                       placeholder="{{ translate('messages.title') }}" >
{{--                                                <span class="text-body-light text-right d-block mt-1">0/50</span>--}}
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            <div class="form-group mb-0">
                                                <label class="input-label"
                                                       for="exampleFormControlInput1">{{ translate('messages.short_description') }}
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                          data-original-title="{{ translate('This text should explain how users can check for delivery to their location and reassure them about your expanding network. Keep under 100 characters.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <textarea type="text" data-maxlength="100" name="available_zone_short_description[]" placeholder="{{translate('messages.short_description')}}" class="form-control min-h-90px ckeditor"></textarea>
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card shadow-none border-0 bg-soft-primary">
                                <div class="card-body d-flex align-items-center gap-2">
                                    <img src="{{ dynamicAsset('assets/admin/img/idea.png') }}" alt="icon">
                                    <p class="fs-15 text-dark m-0">
                                        {{ translate('Customize the section by adding a title, short description, and images in the') }} <a href="{{ route('admin.zone.home') }}" target="_blank" class="text--underline text-006AE5">{{ translate('Zone Setup') }}</a> {{ translate('section. All created zones will be automatically displayed on the') }} {{ translate('React Landing Page. The zones will be based on the Zone Display Name.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="bg-light2 d-flex align-items-center justify-content-center p-xl-20 p-4 rounded h-100">
                                <div class="card-body p-0">
                                    <div>
                                        <div class="mb-xxl-5 mb-xl-4 mb-3 text-center">
                                            <h5 class="mb-0">{{ translate('Upload background Image') }}</h5>
                                        </div>
                                        <div class="text-center">
                                                    @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'image',
                                                'ratio' => '2:1',
                                                'isRequired' => false,
                                                'existingImage' => \App\CentralLogics\Helpers::get_full_url('available_zone_image', $available_zone_image?->value ?? '', $available_zone_image?->storage[0]?->value ?? 'public','upload_image'),
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                                'pixel' => '590 x 232'
                                            ])

                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="btn--container justify-content-end">
                                <button class="btn btn--reset " type="reset">{{translate('reset')}}</button>
                                <button class="btn btn--primary" type="submit">{{translate('Save')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="card">
        <div class="card-header pt-3">
            <div class="row g-3 w-100">
                <div class="col-sm-6">
                    <h3 class="mb-1">{{ translate('Location Picker Section') }}</h3>
                    <p class="mb-0 gray-dark fs-12">{{ translate('Customise delivery location search bar and placeholder text to help users add delivery location.') }}</p>
                </div>
                <div class="col-sm-6">

                    <form
                        action="{{ route('admin.landing_page.statusUpdate', ['type' => 'react_landing_page', 'key' => 'react_location_picker_status']) }}"
                        method="get" id="LocationZoneCheckboxStatus_form">
                    </form>
                    <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control" for="LocationZoneCheckboxStatus">
                            <span class="pr-1 d-flex align-items-center switch--label">
                                <span class="line--limit-1 text--primary">
                                    {{translate('messages.Status') }}
                                </span>
                            </span>
                        <input type="checkbox" data-id="LocationZoneCheckboxStatus" data-type="status"
                               data-image-on="{{ dynamicAsset('assets/admin/img/status-ons.png') }}"
                               data-image-off="{{ dynamicAsset('assets/admin/img/off-danger.png') }}"
                               data-title-on="{{ translate('Do you want turn on this section ?') }}"
                               data-title-off="{{ translate('Do you want to turn off this section ?') }}"
                               data-text-on="<p>{{ translate('If you turn on this section will be show in react landing page.') }}"
                               data-text-off="<p>{{ translate('If you turn off this section will not be show in react landing page.') }}</p>"
                               class="toggle-switch-input  status dynamic-checkbox" id="LocationZoneCheckboxStatus"
                            {{ $react_location_picker_status?->value ? 'checked' : '' }}>
                        <span class="toggle-switch-label text">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <form class="validate-form" id="location-setup-form" action="{{ route('admin.react_landing_page.locationPickerUpdate') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="page_type" value="react_landing_page">
            <div class="card-body">
                <div class="card-custom-xl">
                    <div class="bg-light2 rounded p-xxl-4 p-3">
                        <div class="row g-3">
                            <div class="col-lg-12">
                                <div class="">
                                    @if($language)
                                        <ul class="nav nav-tabs mb-4 border-">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link1 active"
                                                   href="#"
                                                   id="default-link1">{{translate('messages.default')}}</a>
                                            </li>
                                            @foreach ($language as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link1"
                                                       href="#"
                                                       id="{{ $lang }}-link1">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="card-body p-0">
                                        <div class="row g-3 lang_form-float default-form-float">
                                            <input type="hidden" name="lang[]" value="default">

                                            <div class="col-sm-12">
                                                <div class="form-group mb-0">
                                                    <label for="title" class="form-label fw-400">
                                                        {{ translate('messages.Title') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('The title for the location search bar.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <input required type="text"
                                                           data-maxlength="50"
                                                           id="title" class="form-control" name="zone_location_picker_title[]"
                                                           value="{{ $zone_location_picker_title?->getRawOriginal('value') ?? null }}"
                                                           placeholder="{{ translate('messages.Enter_Title') }}">
                                                    <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                </div>
                                            </div>

                                            <div class="col-sm-12 mt-2">
                                                <div class="form-group mb-0">
                                                    <label for="description" class="form-label fw-400">
                                                        {{translate('messages.Description')}} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('The placeholder text inside the search input field. Keep it under 100 characters') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <textarea required id="description" data-maxlength="100" class="form-control min-h-90px ckeditor" name="zone_location_picker_description[]" placeholder="{{translate('messages.Description')}}">{{ $zone_location_picker_description?->getRawOriginal('value') ?? null }}</textarea>
                                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                </div>
                                            </div>
                                        </div>

                                        @forelse($language as $lang)
                                            <input type="hidden" name="lang[]" value="{{ $lang }}">

                                                <?php
                                                $description_translate = [];
                                                $title_translate = [];
                                                if(isset($zone_location_picker_title->translations) && count($zone_location_picker_title->translations)){
                                                    foreach($zone_location_picker_title->translations as $t){
                                                        if($t->locale == $lang && $t->key=='zone_location_picker_title'){
                                                            $title_translate[$lang]['value'] = $t->value;
                                                        }
                                                    }
                                                }

                                                if(isset($zone_location_picker_description->translations) && count($zone_location_picker_description->translations)){
                                                    foreach($zone_location_picker_description->translations as $t){
                                                        if($t->locale == $lang && $t->key=='zone_location_picker_description'){
                                                            $description_translate[$lang]['value'] = $t->value;
                                                        }
                                                    }
                                                }
                                                ?>

                                            <div class="row g-3 d-none lang_form-float" id="{{ $lang }}-form-float">
                                                {{-- Title / Name --}}
                                                <div class="col-sm-12">
                                                    <div class="form-group mb-0">
                                                        <label for="title{{$lang}}" class="form-label fw-400">
                                                            {{ translate('messages.Title') }} ({{ strtoupper($lang) }})
                                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                                  data-original-title="{{ translate('The title for the location search bar.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                        <input type="text"
{{--                                                               data-maxlength="20" --}}
                                                               id="title{{$lang}}" class="form-control"
                                                               name="zone_location_picker_title[]" value="{{ $title_translate[$lang]['value'] ?? '' }}"
                                                               placeholder="{{ translate('messages.Enter_Title') }}">
{{--                                                        <span class="text-body-light text-right d-block mt-1">0/20</span>--}}
                                                    </div>
                                                </div>

                                                <div class="col-sm-12 mt-1">
                                                    <div class="form-group mb-0">
                                                        <label for="description{{$lang}}" class="form-label fw-400">
                                                            {{translate('messages.Description')}} ({{ strtoupper($lang) }})
                                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                                  data-original-title="{{ translate('The placeholder text inside the search input field. Keep it under 100 characters') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                        <textarea id="description{{$lang}}" data-maxlength="100" class="form-control min-h-90px ckeditor" name="zone_location_picker_description[]" placeholder="{{translate('messages.Description')}}">{{ $description_translate[$lang]['value'] ?? '' }}</textarea>
                                                        <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-4">
                        <button class="btn btn--reset " type="reset">{{translate('reset')}}</button>
                        <button class="btn btn--primary" type="submit">{{translate('Save')}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@include('admin-views.landing_page.react.partials.available_zone_guideline')

@endsection

@push('script_2')
    <script>
        // Form on reset
        const prevImage = $('#viewer').attr('src');
        $('#zone-setup-form').on('reset', function(){
            $('#customFileEg1').val(null);
            $('#viewer').attr('src', prevImage);
        })

        function readURL(input, viewer) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();

                reader.onload = function (e) {
                    $('#'+viewer).attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this, 'viewer');
        });

        // Make image required after delete, optional again after re-select
        $(document).ready(function () {
            const $form = $('#zone-setup-form');
            const $imgInput = $form.find('input.single_file_input[name="image"]');
            const hadExistingImage = {{ $available_zone_image ? 'true' : 'false' }};

            // When delete icon is clicked, enforce required on this input
            $form.on('click', '.remove_btn', function () {
                const $card = $(this).closest('.upload-file');
                const $input = $card.find('input.single_file_input[name="image"]');
                if ($input.length) {
                    $input.prop('required', true);
                }
            });

            // On selecting a new file, remove required
            $form.on('change', 'input.single_file_input[name="image"]', function(){
                if (this.files && this.files.length > 0) {
                    $(this).prop('required', false);
                }
            });

            // On form reset, restore original required state
            $form.on('reset', function(){
                setTimeout(function(){
                    $imgInput.prop('required', !hadExistingImage);
                }, 0);
            });
        });

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
    </script>
@endpush
