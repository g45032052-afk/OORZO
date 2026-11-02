@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))

@section('content')
    @php use App\CentralLogics\Helpers; @endphp

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
                            action="{{ route('admin.landing_page.statusUpdate', ['type' => 'react_landing_page', 'key' => 'gallery_section_status']) }}"
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
                                {{ $gallery_section_status?->value ? 'checked' : '' }}>
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
                    <h3 class="mb-1">{{ translate('Gallery Section') }}</h3>
                    <p class="mb-0 gray-dark fs-12">{{ translate(' Showcase high-quality food images to attract users visually.') }}</p>
                </div>
            </div>
            <div class="card-body">
                <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'react-gallery') }}" method="POST">
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
                                                data-toggle="tooltip" data-maxlength="50" data-placement="right"
                                                data-original-title="{{ translate('This is the main headline for your image gallery.') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                        <input required type="text" name="gallery_section_title[]" id="default_title"
                                            maxlength="50" class="form-control"
                                               data-maxlength="50"
                                            placeholder="{{ translate('Title') }}"
                                            value="{{ $gallery_section_title?->getRawOriginal('value') ?? '' }}">
                                        <div class="d-flex justify-content-end">
                                            <span class="text-body-light text-right d-block mt-1">0/50</span>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="input-label fw-400"
                                            for="subtitle">{{ translate('messages.Subtitle') }}
                                            ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                            <span class="form-label-secondary"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ translate('This text should encourage users to browse the gallery and connect it to the experience of using your service. Keep it under 100 characters.') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                        <input required type="text" name="gallery_section_sub_title[]"
                                            value="{{ $gallery_section_sub_title?->getRawOriginal('value') ?? '' }}"
                                            data-maxlength="100" placeholder="{{ translate('Subtitle') }}"
                                            class="form-control">
                                        <div class="d-flex justify-content-end">
                                            <span class="text-body-light text-right d-block mt-1">0/100</span>
                                        </div>
                                    </div>
                                </div>
                                @if ($language)
                                    @forelse($language as $lang)
                                        <?php
                                        if ($gallery_section_title?->translations) {
                                            $gallery_section_title_translate = [];
                                            foreach ($gallery_section_title->translations as $t) {
                                                if ($t->locale == $lang && $t->key == 'gallery_section_title') {
                                                    $gallery_section_title_translate[$lang]['value'] = $t->value;
                                                }
                                            }
                                        }
                                        if ($gallery_section_sub_title?->translations) {
                                            $gallery_section_sub_title_translate = [];
                                            foreach ($gallery_section_sub_title->translations as $t) {
                                                if ($t->locale == $lang && $t->key == 'gallery_section_sub_title') {
                                                    $gallery_section_sub_title_translate[$lang]['value'] = $t->value;
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
                                                        data-original-title="{{ translate('This is the main headline for your image gallery.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input type="text" name="gallery_section_title[]" id="default_title"
                                                    maxlength="50"
                                                       class="form-control"
                                                    placeholder="{{ translate('Title') }}" data-maxlength="50"
                                                    value="{{ $gallery_section_title_translate[$lang]['value'] ?? '' }}">
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="input-label fw-400"
                                                    for="subtitle">{{ translate('messages.Subtitle') }}
                                                    ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ translate('This text should encourage users to browse the gallery and connect it to the experience of using your service. Keep it under 100 characters.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input type="text" name="gallery_section_sub_title[]"
                                                    value="{{ $gallery_section_sub_title_translate[$lang]['value'] ?? '' }}"
                                                    data-maxlength="100" placeholder="{{ translate('Subtitle') }}"
                                                    class="form-control">
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
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
    </div>

    <div class="card mb-15">
        <div class="card-header flex-wrap gap-2">
            <div class="">
                <h3 class="mb-1">{{ translate('Add Image') }}</h3>
{{--                <p class="mb-0 gray-dark fs-12">{{ translate('Here you setup your all business information.') }}</p>--}}
            </div>
            <button type="button" class="btn btn-outline-primary font-semibold offcanvas-trigger"
                data-target="#gallery_offcanvas_btn">
                <i class="tio-invisible"></i> {{ translate('Section Preview') }}
            </button>
        </div>
        <div class="card-body">

            <form action="{{ route('admin.react_landing_page.settings', 'react-gallery-images') }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                <div class="row g-lg-4 g-3">

                    @php
                        function ordinalSuffix($number)
                        {
                            if (!in_array($number % 100, [11, 12, 13])) {
                                switch ($number % 10) {
                                    case 1: return $number . 'st';
                                    case 2: return $number . 'nd';
                                    case 3: return $number . 'rd';
                                }
                            }
                            return $number . 'th';
                        }
                    @endphp

                    @for ($i = 1; $i <= 6; $i++)
                        @php
                            $imageVar = "gallery_image_{$i}";
                            $image = $$imageVar ?? null;
                            $imageUrl = $image?->value
                                ? Helpers::get_full_url(
                                    'react_gallery',
                                    $image->value,
                                    $image->storage[0]->value ?? 'public',
                                    'upload_1_1',
                                )
                                : '';
                            $label = translate(ordinalSuffix($i) . ' ' . 'Image');
                        @endphp

                        <div class="col-sm-6">
                            <div class="card-custom-static p-0">
                                <h4 class="m-0 p-20 border-bottom">{{ $label }}</h4>
                                <div class="card-body">
                                    <div class="bg-light2 d-flex align-items-center justify-content-center p-xl-20 p-4 rounded h-100">
                                        <div class="py-1">
                                            <div class="mb-xxl-4 pb-1 mb-xl-4 mb-3 text-center">
                                                <h5 class="mb-0">{{ translate('Upload Image') }}</h5>
                                            </div>
                                            <div class="text-center">
                                                    @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'gallery_image_'.$i,
                                                'ratio' => '1:1',
                                                'isRequired' => false,
                                                'existingImage' => $imageUrl,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                            ])

                                            </div>
                                    </span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="btn--container justify-content-end mt-20">
                                        <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                                        <button type="submit" class="btn btn--primary">{{ translate('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </form>
        </div>
    </div>

    </div>

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
    <div id="gallery_offcanvas_btn" class="custom-offcanvas d-flex flex-column justify-content-between"
        style="--offcanvas-width: 750px">
        <div>
            <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
                <h3 class="mb-0">{{ translate('messages.Gallery Section Preview') }}</h3>
                <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;
                </button>
            </div>
            <div class="custom-offcanvas-body p-20">
                <div class="text-center m-xl-5 m-lg-4 m-md-3 m-0">
                    <div>
                        <div class="mb-20 pb-1">
                            <h3 class="mb-2 font-bold">{{ translate('messages.Gallery Section Preview') }}</h3>
                            <p class="mb-0 fs-12">
                                {{ translate('messages.Enjoy Greshly prepared your favorite restaturants. carefuly delivered to your doorstep with sepeed and care') }}
                            </p>
                        </div>

                        @php

                            $galleryLayout = [
                                [
                                    ['col' => 'col-sm-12', 'height' => 'h-310'],
                                    ['col' => 'col-sm-6', 'height' => 'h-145'],
                                    ['col' => 'col-sm-6', 'height' => 'h-145'],
                                ],

                                [
                                    ['col' => 'col-sm-6', 'height' => 'h-145'],
                                    ['col' => 'col-sm-6', 'height' => 'h-145'],
                                    ['col' => 'col-sm-12', 'height' => 'h-310'],
                                ],
                            ];
                        @endphp

                        <div class="row g-3">
                            @php $imgIndex = 1; @endphp
                            @foreach ($galleryLayout as $column)
                                <div class="col-xl-6 col-md-12">
                                    <div class="row g-3">
                                        @foreach ($column as  $slot)
                                            @php
                                                $imageVar = "gallery_image_{$imgIndex}";
                                                $image = $$imageVar ?? null;
                                                $imageUrl = $image?->value
                                                    ? Helpers::get_full_url(
                                                        'react_gallery',
                                                        $image->value,
                                                        $image->storage[0]->value ?? 'public',
                                                        'upload_1_1',
                                                    )
                                                    : dynamicAsset('assets/admin/img/blank3.png');
                                            @endphp

                                            <div class="{{ $slot['col'] }}">
                                                <div class="bg-EDEDED {{ $slot['height'] }} w-100 rounded-10">
                                                    <div class="rounded-10 overflow-hidden w-100 h-100 bg-EDEDED">
                                                        <img src="{{ $imageUrl }}" class="w-100 initial-33 rounded-10"
                                                            alt="gallery {{ $imgIndex }}">
                                                    </div>
                                                </div>
                                            </div>
                                            @php $imgIndex++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin-views.landing_page.react.partials.gallery_guideline')

@endsection

@push('script_2')
    <script>
        $(document).on('click', '.remove_btn', function () {
            let wrapper = $(this).closest('.upload-file');
            wrapper.find('.upload-file-img').hide();
            wrapper.find('input[type=file]').val('');
            wrapper.find('.image-delete-flag').val('1');
        });
    </script>
@endpush
