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
                            action="{{ route('admin.landing_page.statusUpdate', ['type' => 'react_landing_page', 'key' => 'category_section_status']) }}"
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
                                {{ $category_section_status?->value ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="">
                    <h3 class="mb-1">{{ translate('Category Section') }}</h3>
                    <p class="mb-0 gray-dark fs-12">{{ translate('Manage the main headline and sub-headline for the food categories section.') }}</p>
                </div>
            </div>
          <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'react-category') }}" method="POST" enctype="multipart/form-data">
            @csrf



            <div class="card-body">
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
                                    <label class="input-label fw-400" for="default_title">{{ translate('messages.title') }}
                                        ({{ translate('messages.Default') }}) <span class="text-danger">*</span>
                                        <span class="form-label-secondary"
                                            data-toggle="tooltip" data-maxlength="50" data-placement="right"
                                            data-original-title="{{ translate('Write the main headline for the food categories section. Keep it under 50 characters.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input required type="text" name="category_section_title[]" id="default_title"
                                           class="form-control" data-maxlength="50" placeholder="{{ translate('Title') }}"
                                        value="{{ $category_section_title?->getRawOriginal('value') ?? '' }}">
                                    <div class="d-flex justify-content-end">
                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="input-label fw-400" for="subtitle">{{ translate('messages.Subtitle') }}
                                        ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                        <span class="form-label-secondary"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('Write a brief line to encourage exploration, organized by your favorite cuisines and food types.. Keep it under 100 characters.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input required type="text" name="category_section_sub_title[]"
                                        value="{{ $category_section_sub_title?->getRawOriginal('value') ?? '' }}"
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
                                    if ($category_section_title?->translations) {
                                        $category_section_title_translate = [];
                                        foreach ($category_section_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'category_section_title') {
                                                $category_section_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if ($category_section_sub_title?->translations) {
                                        $category_section_sub_title_translate = [];
                                        foreach ($category_section_sub_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'category_section_sub_title') {
                                                $category_section_sub_title_translate[$lang]['value'] = $t->value;
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
                                                    data-original-title="{{ translate('Write the main headline for the food categories section. Keep it under 50 characters.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <input type="text" name="category_section_title[]" id="default_title"
                                                class="form-control" placeholder="{{ translate('Title') }}" data-maxlength="50"
                                                value="{{ $category_section_title_translate[$lang]['value'] ?? '' }}">
                                            <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="input-label fw-400"
                                                for="subtitle">{{ translate('messages.Subtitle') }}
                                                ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('Write a brief line to encourage exploration, organized by your favorite cuisines and food types.. Keep it under 100 characters.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <input type="text" name="category_section_sub_title[]"
                                                value="{{ $category_section_sub_title_translate[$lang]['value'] ?? '' }}"
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

                            <div class="card shadow-none border-0 bg-soft-primary">
                                <div class="card-body d-flex align-items-center gap-2">
                                    <img src="{{ dynamicAsset('assets/admin/img/idea.png') }}" alt="icon">

                                    <p class="fs-15 text-dark m-0">
                                        {{ translate('Customise the section by adding a title and subtitle in the') }} <a
                                            href="{{ route('admin.category.add') }}" target="_blank"
                                            class="text--underline text-006AE5">{{ translate('Category Setup') }}</a>
                                        {{ translate('Section. All created categories will be automatically displayed on the') }}
                                        <strong>{{ translate('React Landing Pgae') }}</strong>
                                        {{ translate('The categories will be based on the Category Display Name.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end mt-4">
                            <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                            <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
         </form>
    </div>
</div>

    @include('admin-views.landing_page.react.partials.category_guideline')

@endsection
