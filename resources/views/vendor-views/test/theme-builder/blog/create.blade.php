@extends('layouts.vendor.app')

@section('title', translate('Create New Blog'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ dynamicAsset('assets/admin/css/AI/animation/product/ai-sidebar.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset('assets/admin/plugins/quill-editor/quill-editor.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">

        <div class="page-header pb-0 mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h1 class="page-header-title mb-0">{{ translate('Create New Blog') }}</h1>
                <a href="javascript:" class="btn text-primary border bg-white d-flex align-items-center gap-2 px-4">
                    {{ translate('View Preview') }}
                    <i class="tio-invisible"></i>
                </a>
            </div>
        </div>

        <form action="javascript:" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card card-body mb-3">
                <div class="bg-light p-xxl-20 p-12 rounded">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="input-label mb-0">
                                    {{ translate('Category') }}
                                    <span class="input-label-secondary m-0" data-toggle="tooltip" data-placement="top"
                                        title="{{ translate('Select Category') }}">
                                        <i class="tio-info text-muted fs-14"></i>
                                    </span>
                                </label>
                                <a href="javascript:" class="text-primary font-medium offcanvas-trigger" data-target="#offcanvas__category">
                                    {{ translate('Manage Category') }}
                                </a>
                            </div>
                            <select name="category_id" class="form-control custom-select js-data-example-ajax"
                                    data-placeholder="{{ translate('Select Category') }}">
                                <option value="" selected disabled>{{ translate('Select Category') }}</option>
                                <option value="1">{{ translate('Product Guides') }}</option>
                                <option value="2">{{ translate('Tips & Tricks') }}</option>
                                <option value="3">{{ translate('Trending Topics') }}</option>
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="input-label">{{ translate('Writer') }}</label>
                            <input type="text" name="writer" class="form-control" placeholder="{{ translate('Writer name') }}" value="">
                        </div>
                        <div class="col-lg-4">
                            <label class="input-label">
                                {{ translate('Publish Date') }}
                                <span class="input-label-secondary m-0" data-toggle="tooltip" data-placement="top"
                                    title="{{ translate('Set the date on which this blog will be published') }}">
                                    <i class="tio-info text-muted fs-14"></i>
                                </span>
                            </label>
                            <div class="position-relative">
                                <input type="date" name="publish_date" class="form-control"
                                    placeholder="{{ translate('Select Date') }}" id="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-body mb-3">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="d-flex flex-column align-items-center global-bg-box p-xxl-20 p-12 rounded">
                            <div class="text-center mb-4">
                                <label class="input-label d-block mb-1">
                                    {{ translate('Write Image') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <p class="fs-12 mb-0">{{ translate('Upload Write image') }}</p>
                            </div>
                            @include('admin-views.partials._image-uploader', [
                                'id' => 'image-input',
                                'name' => '',
                                'isRequired' => true,
                                'existingImage' => null,
                                'ratio' => '1:1',
                                'imageExtension' => IMAGE_EXTENSION,
                                'imageFormat' => IMAGE_FORMAT,
                                'maxSize' => 2,
                                ])
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="d-flex flex-column align-items-center global-bg-box p-xxl-20 p-12 rounded">
                            <div class="text-center mb-4">
                                <label class="input-label d-block mb-1">
                                    {{ translate('Thumbnail') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <p class="fs-12 mb-0">{{ translate('Upload thumbnail image') }}</p>
                            </div>
                            @include('admin-views.partials._image-uploader', [
                                'id' => 'image-input',
                                'name' => '',
                                'isRequired' => true,
                                'existingImage' => null,
                                'ratio' => '3:1',
                                'pixel' => '1200 x 630',
                                'imageExtension' => IMAGE_EXTENSION,
                                'imageFormat' => IMAGE_FORMAT,
                                'maxSize' => 2,
                                ])
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-body mb-3">
                <div class="js-nav-scroller hs-nav-scroller-horizontal position-relative nav--tab-wrapper mb-2">
                    <ul class="nav nav-tabs nav--tab lang_tab border-0">
                        <li class="nav-item">
                            <a class="nav-link px-0 active" href="#" id="default-link">Default</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#" id="en-link">English(EN)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#" id="bn-link">Bengali - বাংলা(BN)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#" id="ar-link">Arabic - العربية(AR)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#" id="es-link">Spanish - español(ES)</a>
                        </li>
                    </ul>
                    <div class="nav--tab__prev">
                        <button type="button" class="btn btn-circle border-0 bg-secondary text-white">
                            <i class="tio-chevron-left"></i>
                        </button>
                    </div>
                    <div class="nav--tab__next">
                        <button type="button" class="btn btn-circle border-0 bg-secondary text-white">
                            <i class="tio-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="tab-content">
                    @foreach (['default' => 'Default', 'en' => 'English', 'ar' => 'Arabic'] as $langKey => $langLabel)
                    <div class="tab-pane fade {{ $langKey === 'default' ? 'show active' : '' }}" id="lang-{{ $langKey }}">

                        <div class="form-group mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="input-label mb-0">{{ translate('Title') }}</label>
                                <button type="button" class="flex-shrink-0 btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-2 auto_fill_title"
                                    id="title-default-action-btn" data-type="default"
                                    data-error="{{ translate('Please provide a product name so the AI can generate a suitable title.') }}"
                                    data-lang="{{ \App\CentralLogics\Helpers::system_default_language() }}"
                                    data-route="">
                                    <div class="btn-svg-wrapper">
                                        <img width="18" height="18"
                                            src="{{ dynamicAsset('assets/admin/img/svg/blink-right-small.svg') }}" alt="">
                                    </div>
                                    <span class="ai-text-animation d-none" role="status">
                                        {{ translate('Just_a_second') }}
                                    </span>
                                    <span class="btn-text">{{ translate('Generate') }}</span>
                                </button>
                            </div>
                            <div class="outline-wrapper">
                                <input type="text" name="title[{{ $langKey }}]" class="form-control"
                                    placeholder="{{ translate('Why a POS System is Essential for Your Business') }}">
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="input-label mb-0">{{ translate('Description') }}</label>
                                <button type="button" class="flex-shrink-0 btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-2 auto_fill_description"
                                    id="description-default-action-btn" data-type="default"
                                    data-error="{{ translate('Please provide a product description so the AI can generate a description.') }}"
                                    data-lang="{{ \App\CentralLogics\Helpers::system_default_language() }}"
                                    data-route="">
                                    <div class="btn-svg-wrapper">
                                        <img width="18" height="18"
                                            src="{{ dynamicAsset('assets/admin/img/svg/blink-right-small.svg') }}" alt="">
                                    </div>
                                    <span class="ai-text-animation d-none" role="status">
                                        {{ translate('Just_a_second') }}
                                    </span>
                                    <span class="btn-text">{{ translate('Generate') }}</span>
                                </button>
                            </div>
                            <div class="outline-wrapper">
                               <div class="quill-editor editor-min-h-80"></div>
                               <textarea name="description[]" style="display:none;"></textarea>
                           </div>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>

            <div class="seo_wrapper">
                <div class="outline-wrapper">
                    <div class="card card-body bg-animate">
                        <div class="d-flex justify-content-between align-items-start w-100 mb-20">
                            <div>
                                <h3 class="mb-1 font-semibold">{{ translate('Meta Data Setup') }}</h3>
                                <p class="fs-12 text-muted mb-0">
                                    {{ translate('Include Meta Information to improve search engine visibility and social sharing') }}
                                </p>
                            </div>
                            <button type="button" class="btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-0 price_others_auto_fill"
                                data-route="" data-lang="en">
                                <div class="btn-svg-wrapper">
                                    <img width="18" height="18"
                                        src="{{ dynamicAsset('assets/admin/img/svg/blink-right-small.svg') }}" alt="">
                                </div>
                                <span class="ai-text-animation d-none" role="status">
                                    {{ translate('Just_a_second') }}
                                </span>
                                <span class="btn-text">{{ translate('Generate') }}</span>
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="global-bg-box p-xxl-20 p-12 rounded h-100">
                                    <div class="form-group mb-3">
                                        <label class="input-label">
                                            {{ translate('Meta Title') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="meta_title" id="meta_title" maxlength="100"
                                            class="form-control" placeholder="{{ translate('Type meta title') }}"
                                            value="{{ old('meta_title') }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="input-label">
                                            {{ translate('Meta Description') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="meta_description" id="meta_description" rows="3"
                                                maxlength="160" class="form-control"
                                                placeholder="{{ translate('Type meta description') }}">{{ old('meta_description') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex flex-column align-items-center global-bg-box p-xxl-20 p-12 rounded h-100">
                                    <div class="text-center mb-4">
                                        <label class="input-label d-block mb-1">
                                            {{ translate('Meta Image') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <p class="fs-12 mb-0">{{ translate('Upload image') }}</p>
                                    </div>
                                    @include('admin-views.partials._image-uploader', [
                                        'id' => 'image-input',
                                        'name' => '',
                                        'isRequired' => true,
                                        'existingImage' => null,
                                        'ratio' => '2:1',
                                        'imageExtension' => IMAGE_EXTENSION,
                                        'imageFormat' => IMAGE_FORMAT,
                                        'maxSize' => 1,
                                        ])
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="global-bg-box p-xxl-20 p-12 rounded h-100">
                                    <div class="h-100 d-flex flex-column gap-3">
                                        <div class="bg-white p-3 rounded">
                                            <div class="row gy-3">
                                                <div class="col-sm-6">
                                                    <label class="form-check d-flex gap-2 mb-0">
                                                        <input type="radio" name="meta_index" value="index" checked
                                                            class="form-check-input radio--input">
                                                        <span class="form-check-label user-select-none">{{ translate('Index') }}</span>
                                                        <span data-toggle="tooltip"
                                                            title="{{ translate('Allow search engines to index this page') }}">
                                                            <i class="tio-info text-muted fs-14"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-check d-flex gap-2 mb-0">
                                                        <input type="radio" name="meta_index" value="noindex"
                                                            class="form-check-input radio--input">
                                                        <span class="form-check-label user-select-none">{{ translate('No Index') }}</span>
                                                        <span data-toggle="tooltip"
                                                            title="{{ translate('Disallow search engines from indexing this page') }}">
                                                            <i class="tio-info text-muted fs-14"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-white p-3 rounded h-100">
                                            <div class="row gy-3">
                                                <div class="col-sm-6">
                                                    <div class="d-flex gap-3 flex-column">
                                                        <label class="form-check d-flex gap-2 mb-0">
                                                            <input type="checkbox" name="meta_no_follow" value="1"
                                                                class="form-check-input checkbox--input">
                                                            <span class="form-check-label user-select-none">{{ translate('No Follow') }}</span>
                                                            <span data-toggle="tooltip"
                                                                title="{{ translate('Instruct search engines not to follow links from this page') }}">
                                                                <i class="tio-info text-muted fs-14"></i>
                                                            </span>
                                                        </label>
                                                        <label class="form-check d-flex gap-2 mb-0">
                                                            <input type="checkbox" name="meta_no_image_index" value="1"
                                                                class="form-check-input checkbox--input">
                                                            <span class="form-check-label user-select-none">{{ translate('No Image Index') }}</span>
                                                            <span data-toggle="tooltip"
                                                                title="{{ translate('Prevents images from being indexed by search engines') }}">
                                                                <i class="tio-info text-muted fs-14"></i>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="d-flex gap-3 flex-column">
                                                        <label class="form-check d-flex gap-2 mb-0">
                                                            <input type="checkbox" name="meta_no_index" value="noindex"
                                                                class="form-check-input checkbox--input">
                                                            <span class="form-check-label user-select-none">{{ translate('No Index') }}</span>
                                                            <span data-toggle="tooltip"
                                                                title="{{ translate('Disallow search engines from indexing this page') }}">
                                                                <i class="tio-info text-muted fs-14"></i>
                                                            </span>
                                                        </label>
                                                        <label class="form-check d-flex gap-2 mb-0">
                                                            <input type="checkbox" name="meta_no_snippet" value="1"
                                                                class="form-check-input checkbox--input">
                                                            <span class="form-check-label user-select-none">{{ translate('No Snippet') }}</span>
                                                            <span data-toggle="tooltip"
                                                                title="{{ translate('Instruct search engines not to show a snippet in search results') }}">
                                                                <i class="tio-info text-muted fs-14"></i>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="global-bg-box p-xxl-20 p-12 rounded h-100">
                                    <div class="bg-white p-3 rounded d-flex flex-column gap-3 gap-sm-2 h-100">
                                        <div class="row gy-2">
                                            <div class="col-sm-6">
                                                <label class="form-check d-flex gap-2 mb-0">
                                                    <input type="checkbox" name="meta_max_snippet" value="1"
                                                        class="form-check-input checkbox--input">
                                                    <span class="form-check-label user-select-none">{{ translate('Max Snippet') }}</span>
                                                    <span data-toggle="tooltip"
                                                        title="{{ translate('Determine the maximum length of a snippet in search results') }}">
                                                        <i class="tio-info text-muted fs-14"></i>
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="col-sm-6">
                                                <input type="number" name="meta_max_snippet_value" value="-1"
                                                class="form-control w-100">
                                            </div>
                                        </div>
                                        <div class="row gy-2">
                                            <div class="col-sm-6">
                                                <label class="form-check d-flex gap-2 mb-0">
                                                    <input type="checkbox" name="meta_max_video_preview" value="1" checked
                                                        class="form-check-input checkbox--input">
                                                    <span class="form-check-label user-select-none">{{ translate('Max Video Preview') }}</span>
                                                    <span data-toggle="tooltip"
                                                        title="{{ translate('Determine the maximum duration of a video preview') }}">
                                                        <i class="tio-info text-muted fs-14"></i>
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="col-sm-6">
                                                <input type="number" name="meta_max_video_preview_value" value="-1"
                                                class="form-control w-100">
                                            </div>
                                        </div>
                                        <div class="row gy-2">
                                            <div class="col-sm-6">
                                                <label class="form-check d-flex gap-2 mb-0">
                                                    <input type="checkbox" name="meta_max_image_preview" value="1" checked
                                                        class="form-check-input checkbox--input">
                                                    <span class="form-check-label user-select-none">{{ translate('Max Image Preview') }}</span>
                                                    <span data-toggle="tooltip"
                                                        title="{{ translate('Determine the maximum size of an image preview') }}">
                                                        <i class="tio-info text-muted fs-14"></i>
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="col-sm-6">
                                                <select name="" class="form-control custom-select w-100">
                                                    <option value="large" selected>{{ translate('Large') }}</option>
                                                    <option value="medium">{{ translate('Medium') }}</option>
                                                    <option value="small">{{ translate('Small') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn--container justify-content-end mt-4">
                <button type="reset" class="btn btn--reset px-sm-4">{{ translate('Reset') }}</button>
                <a href="javascript:" class="btn btn-outline-primary px-sm-4">
                    {{ translate('Save to Draft') }}
                </a>
                <button type="submit" class="btn btn--primary px-sm-4">{{ translate('Publish') }}</button>
            </div>

        </form>
    </div>

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>

    <div id="offcanvas__category" class="custom-offcanvas d-flex flex-column">

        <div class="custom-offcanvas-header d-flex justify-content-between align-items-center px-4 py-3 border-bottom flex-shrink-0">
            <h5 class="mb-0 font-semibold">{{ translate('Category Setup') }}</h5>
            <button type="button"
                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                aria-label="Close">&times;</button>
        </div>

        <div class="custom-offcanvas-body p-20">

            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="mb-0">{{ translate('Add New Category') }}</h4>
                </div>
                <div class="card-body">
                    <div class="js-nav-scroller hs-nav-scroller-horizontal position-relative nav--tab-wrapper mb-2">
                        <ul class="nav nav-tabs nav--tab lang_tab border-0">
                            <li class="nav-item">
                                <a class="nav-link px-0 active" href="#" id="default-link">Default</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-0" href="#" id="en-link">English(EN)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-0" href="#" id="bn-link">Bengali - বাংলা(BN)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-0" href="#" id="ar-link">Arabic - العربية(AR)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-0" href="#" id="es-link">Spanish - español(ES)</a>
                            </li>
                        </ul>
                        <div class="nav--tab__prev">
                            <button type="button" class="btn btn-circle border-0 bg-secondary text-white">
                                <i class="tio-chevron-left"></i>
                            </button>
                        </div>
                        <div class="nav--tab__next">
                            <button type="button" class="btn btn-circle border-0 bg-secondary text-white">
                                <i class="tio-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="bg--secondary rounded px-3 pt-2 mb-20">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group lang_form1" id="default-form1">
                                    <label class="input-label" for="exampleFormControlInput1">Category Name
                                        (Default)
                                        <span class="form-label-secondary text-danger" data-toggle="tooltip" data-placement="right"
                                            data-original-title="Required."> *
                                        </span>
                                    </label>
                                    <input type="text" name="name[]" class="form-control" placeholder="New category" maxlength="255"
                                        required="">
                                </div>
                                <input type="hidden" name="lang[]" value="default">

                                <div class="form-group lang_form1 d-none" id="en-form1">
                                    <label class="input-label" for="exampleFormControlInput1">Category Name
                                        (EN)
                                    </label>
                                    <input type="text" name="name[]" class="form-control" placeholder="Type Category Name" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="en">
                                <div class="form-group lang_form1 d-none" id="bn-form1">
                                    <label class="input-label" for="exampleFormControlInput1">Category Name
                                        (BN)
                                    </label>
                                    <input type="text" name="name[]" class="form-control" placeholder="Type Category Name" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="bn">
                                <div class="form-group lang_form1 d-none" id="ar-form1">
                                    <label class="input-label" for="exampleFormControlInput1">Category Name
                                        (AR)
                                    </label>
                                    <input type="text" name="name[]" class="form-control" placeholder="Type Category Name" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="ar">
                                <div class="form-group d-none lang_form1" id="es-form1">
                                    <label class="input-label" for="exampleFormControlInput1">Category Name
                                        (ES)
                                    </label>
                                    <input type="text" name="name[]" class="form-control" placeholder="Type Category Name" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="es">
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-4">
                        <button type="reset" class="btn btn--reset px-4">{{ translate('Reset') }}</button>
                        <button type="button" class="btn btn--primary px-4">{{ translate('Submit') }}</button>
                    </div>
                </div>

            </div>

            <div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h4 class="mb-0">{{ translate('Category List') }}</h4>
                    <form id="blog-search-form" class="flex-grow-1 max-w-300 min-w-100-mobile">
                        <div class="input--group input-group border rounded input-group-merge input-group-flush">
                            <input id="blogSearch" name="search" type="search"
                                   value="{{ request()?->search ?? null }}"
                                   class="form-control border-0"
                                   placeholder="{{ translate('Search Category') }}"
                                   aria-label="{{ translate('Search here') }}">
                            <button type="submit" class="btn btn--reset w-auto px-2 py-2 h-40px min-w-35px">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table mb-2">
                        <thead class="global-bg-box">
                            <tr>
                                <th class="w-50px">{{ translate('SL') }}</th>
                                <th>{{ translate('Category') }}</th>
                                <th class="text-center">{{ translate('Status') }}</th>
                                <th class="text-center">{{ translate('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>
                                    <div class="fs-12">Comparison</div>
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
                                                data-title-on="{{ translate('are_you_sure_to_turn_on_the_category_status') }}?"
                                                data-title-off="{{ translate('are_you_sure_to_turn_off_the_category') }}?"
                                                data-text-on="<p>{{ translate('when_you_turn_on_this_category,_it_can_be_accessed_and_visible_for_selection') }}</p>"
                                                data-text-off="<p>{{ translate('once_you_turn_off,_it_will_not_be_accessed_when_selecting_the_category') }}</p>"
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
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                           href="javascript:" title="{{ translate('Edit') }}">
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
            </div>

        </div>
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
                                {{ translate('Are you sure to delete this category?') }}
                            </h3>
                            <p class="mb-0">
                                {{ translate('Once you delete it, This will permanently remove it from the category list and can not be accessed') }}
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
    <script src="{{ dynamicAsset('assets/admin/plugins/quill-editor/quill-editor.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/plugins/quill-editor/quill-editor-init.js') }}"></script>
@endpush
