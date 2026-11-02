@extends('layouts.admin.app')

@section('title',$restaurant->name."'s ".translate('messages.settings'))

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{dynamicAsset('assets/admin/css/croppie.css')}}" rel="stylesheet">

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h1 class="page-header-title text-break">
                    <i class="tio-museum"></i> <span>{{$restaurant->name}} @include('partials._verified_restaurant_badge', ['restaurant' => $restaurant])</span>
                </h1>
            </div>
            <!-- Nav Scroller -->
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev initial-hidden">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-left"></i>
                </a>
            </span>

                <span class="hs-nav-scroller-arrow-next initial-hidden">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-right"></i>
                </a>
            </span>

                <!-- Nav -->
                @include('admin-views.vendor.view.partials._header',['restaurant'=>$restaurant])

                <!-- End Nav -->
            </div>
            <!-- End Nav Scroller -->
        </div>    <!-- Page Heading -->
        <div class="tab-content">
            <div class="tab-pane fade show active" id="vendor">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                        <span class="card-header-icon">
                            <img class="w--22" src="{{dynamicAsset('assets/admin/img/restaurant.png')}}" alt="">
                        </span>
                            <span class="p-md-1"> {{translate('messages.restaurant_meta_data')}}</span>
                        </h5>
                    </div>
                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = 'en')
                    <div class="card-body">
                        <form action="{{route('admin.restaurant.update-meta-data',[$restaurant['id']])}}" method="post"
                              enctype="multipart/form-data" class="">
                            @csrf
                            <div class="row g-2">
                                <div class="col-lg-7 col-xl-8">
                                    <div class="bg-light2 rounded">
                                        <div class="card-body">
{{--                                            @if($language)--}}
{{--                                                <div class="js-nav-scroller hs-nav-scroller-horizontal">--}}
{{--                                                    <ul class="nav nav-tabs mb-4">--}}
{{--                                                        <li class="nav-item">--}}
{{--                                                            <a class="nav-link lang_link active"--}}
{{--                                                               href="#"--}}
{{--                                                               id="default-link">{{ translate('Default') }}</a>--}}
{{--                                                        </li>--}}
{{--                                                            <?php--}}
{{--                                                            $restaurant = \App\Models\Restaurant::withoutGlobalScope('translate')->with('translations')->findOrFail($restaurant->id);--}}
{{--                                                            ?>--}}
{{--                                                        @foreach (json_decode($language) as $lang)--}}
{{--                                                            <li class="nav-item">--}}
{{--                                                                <a class="nav-link lang_link"--}}
{{--                                                                   href="#"--}}
{{--                                                                   id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>--}}
{{--                                                            </li>--}}
{{--                                                        @endforeach--}}
{{--                                                    </ul>--}}
{{--                                                </div>--}}
{{--                                            @endif--}}
                                            @if ($language)
                                                <div class="lang_form"
                                                     id="default-form">
                                                    <div class="form-group">
                                                        <label class="input-label"
                                                               for="default_title">{{ translate('messages.meta_title') }}

                                                        </label>
                                                        <input maxlength="100" type="text" name="meta_title[]"
                                                               id="default_title"
                                                               class="form-control"
                                                               placeholder="{{ translate('messages.meta_title') }}"
                                                               value="{{$restaurant->getRawOriginal('meta_title')}}"

                                                        >
                                                        <div class="d-flex justify-content-end">
                                                            <span
                                                                class="text-body-light text-right d-block mt-1">0/100</span>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label"
                                                               for="exampleFormControlInput1">{{ translate('messages.meta_description') }}
                                                            </label>
                                                        <textarea maxlength="160" type="text" name="meta_description[]"
                                                                  placeholder="{{translate('messages.meta_description')}}"
                                                                  class="form-control min-h-90px ckeditor">{{$restaurant->getRawOriginal('meta_description')}}</textarea>
                                                        <div class="d-flex justify-content-end">
                                                            <span
                                                                class="text-body-light text-right d-block mt-1">0/160</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @foreach (json_decode($language) as $lang)
                                                        <?php
                                                        if (count($restaurant['translations'])) {
                                                            $translate = [];
                                                            foreach ($restaurant['translations'] as $t) {
                                                                if ($t->locale == $lang && $t->key == "meta_title") {
                                                                    $translate[$lang]['meta_title'] = $t->value;
                                                                }
                                                                if ($t->locale == $lang && $t->key == "meta_description") {
                                                                    $translate[$lang]['meta_description'] = $t->value;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    <div class="d-none lang_form"
                                                         id="{{ $lang }}-form">
                                                        <div class="form-group">
                                                            <label class="input-label"
                                                                   for="{{ $lang }}_title">{{ translate('messages.meta_title') }}
                                                                ({{ strtoupper($lang) }})
                                                            </label>
                                                            <input maxlength="100" type="text" name="meta_title[]"
                                                                   id="{{ $lang }}_title"
                                                                   class="form-control"
                                                                   value="{{ $translate[$lang]['meta_title']??'' }}"
                                                                   placeholder="{{ translate('messages.meta_title') }}"
                                                            >
                                                            <div class="d-flex justify-content-end">
                                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label"
                                                                   for="exampleFormControlInput1">{{ translate('messages.meta_description') }}
                                                                ({{ strtoupper($lang) }})</label>
                                                            <textarea maxlength="160" type="text"
                                                                      name="meta_description[]"
                                                                      placeholder="{{translate('messages.meta_description')}}"
                                                                      class="form-control min-h-90px ckeditor">{{ $translate[$lang]['meta_description']??'' }}</textarea>
                                                            <div class="d-flex justify-content-end">
                                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div id="default-form">
                                                    <div class="form-group">
                                                        <label class="input-label"
                                                               for="exampleFormControlInput1">{{ translate('messages.meta_title') }}
                                                            </label>
                                                        <input type="text" name="meta_title[]" class="form-control"
                                                               placeholder="{{ translate('messages.meta_title') }}">
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label"
                                                               for="exampleFormControlInput1">{{ translate('messages.meta_description') }}
                                                        </label>
                                                        <textarea type="text" name="meta_description[]"
                                                                  placeholder="{{translate('messages.meta_description')}}"
                                                                  class="form-control min-h-90px ckeditor"></textarea>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5 col-xl-4">
                                    <div class="bg-light2 rounded">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap flex-sm-nowrap __gap-12px">
                                                <label class="__custom-upload-img mx-auto text-center">
                                                    <div class="position-relative">
                                                        <h5 class="mb-sm-4 mb-3">
                                                            <span>{{ translate('restaurant_meta_image') }}</span>
                                                        </h5>
                                                        <div class="text-center">
                                                            <img
                                                                class="img--110 min-height-170px mx-auto min-width-170px onerror-image"
                                                                id="viewer"
                                                                src="{{ $restaurant?->meta_image_full_url ?? dynamicAsset('assets/admin/img/upload.png') }}"
                                                                data-onerror-image="{{ dynamicAsset('assets/admin/img/upload.png') }}"
                                                                alt="image">
                                                        </div>
                                                        <input type="file" name="meta_image" id="customFileEg1"
                                                               class="custom-file-input"
                                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                                        @if (isset($restaurant->meta_image))
                                                            <span id="earning_delivery_img"
                                                                  class="remove_image_button mt-4 dynamic-checkbox"
                                                                  data-id="earning_delivery_img"
                                                                  data-type="status"
                                                                  data-image-on='{{dynamicAsset('assets/admin/img/modal')}}/mail-success.png'
                                                                  data-image-off="{{dynamicAsset('assets/admin/img/modal')}}/mail-warning.png"
                                                                  data-title-on="{{translate('Important!')}}"
                                                                  data-title-off="{{translate('Warning!')}}"
                                                                  data-text-on="<p>{{translate('Are_you_sure_you_want_to_remove_this_image')}}</p>"
                                                                  data-text-off="<p>{{translate('Are_you_sure_you_want_to_remove_this_image.')}}</p>"
                                                            > <i class="tio-clear"></i></span>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                            <p class="fs-12 text-center mt-3">{{ translate('jpg, .png, .jpeg, .gif, .bmp, .tif size : Max 2 MB  (1:1)') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 pt-4">

                                <!-- LEFT COLUMN -->
                                <div class="col-lg-6">
                                    <div class="bg-light2 rounded p-xxl-20 p-3 h-100">
                                        <div
                                            class="bg-white d-flex flex-wrap gap-2 justify-content-between rounded mb-3 index-area-here">
                                            <div class="d-flex flex-column gap-2 flex-grow-1">
                                                <label class="form-check d-flex gap-2 mb-0 radio-sclae-1">
                                                    <input type="radio" name="meta_index" value="index"
                                                           {{ isset($meta['meta_index']) && $meta['meta_index'] === 'index' ? 'checked' : '' }}
                                                           class="form-check-input radio--input">
                                                    <span class="form-check-label">{{ translate('Index') }}</span>
                                                    <span class="input-label-secondary" data-toggle="tooltip"
                                                          title="{{ translate('allow_search_engines_to_index_this_page') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                                </label>
                                            </div>
                                            <div class="d-flex flex-column gap-2 flex-grow-1 ps-sm-47">
                                                <label class="form-check d-flex gap-2 mb-0 radio-sclae-1">
                                                    <input type="radio" name="meta_index" value="noindex"
                                                           {{ isset($meta['meta_index']) && $meta['meta_index'] === 'noindex' ? 'checked' : '' }}
                                                           class="form-check-input radio--input">
                                                    <span class="form-check-label">{{ translate('No_Index') }}</span>
                                                    <span class="input-label-secondary" data-toggle="tooltip"
                                                          title="{{ translate('disallow_search_engines_from_indexing_this_page') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card border-0 rounded">
                                            <div class="card-body d-flex flex-wrap gap-2 justify-content-between h-100">

                                                <div class="item d-flex flex-column gap-2 flex-grow-1">

                                                    <label class="form-check d-flex gap-2">
                                                        <input type="checkbox" name="meta_no_follow" value="nofollow"
                                                               {{ ($meta['meta_no_follow'] ?? '') == 'nofollow' ? 'checked' : '' }}
                                                               class="form-check-input checkbox--input">
                                                        <span
                                                            class="form-check-label">{{ translate('No_Follow') }}</span>
                                                        <span class="input-label-secondary" data-toggle="tooltip"
                                                              title="{{ translate('instruct_search_engines_not_to_follow_links_from_this_page') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                    </label>

                                                    <label class="form-check d-flex gap-2">
                                                        <input type="checkbox" name="meta_no_image_index"
                                                               value="noimageindex"
                                                               {{ ($meta['meta_no_image_index'] ?? '') == 'noimageindex' ? 'checked' : '' }}
                                                               class="form-check-input checkbox--input">
                                                        <span
                                                            class="form-check-label">{{ translate('No_Image_Index') }}</span>
                                                        <span class="input-label-secondary" data-toggle="tooltip"
                                                              title="{{ translate('prevent_images_from_being_indexed') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                    </label>
                                                </div>

                                                <div class="item d-flex flex-column gap-2 flex-grow-1">


                                                    <label class="form-check d-flex gap-2">
                                                        <input type="checkbox" name="meta_no_archive" value="noarchive"
                                                               {{ ($meta['meta_no_archive'] ?? '') == 'noarchive' ? 'checked' : '' }}
                                                               class="form-check-input checkbox--input">
                                                        <span
                                                            class="form-check-label">{{ translate('No_Archive') }}</span>
                                                        <span class="input-label-secondary" data-toggle="tooltip"
                                                              title="{{ translate('prevent_search_engines_from_caching_this_page') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                    </label>

                                                    <label class="form-check d-flex gap-2">
                                                        <input type="checkbox" name="meta_no_snippet" value="nosnippet"
                                                               {{ ($meta['meta_no_snippet'] ?? '') == 'nosnippet' ? 'checked' : '' }}
                                                               class="form-check-input checkbox--input">
                                                        <span
                                                            class="form-check-label">{{ translate('No_Snippet') }}</span>
                                                        <span class="input-label-secondary" data-toggle="tooltip"
                                                              title="{{ translate('prevent_search_engines_from_showing_snippets') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                    </label>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="bg-light2 rounded p-xxl-20 p-3 h-100">
                                        <div class="card border-0 h-100 rounded">
                                            <div class="card-body d-flex flex-column gap-3">
                                                <div
                                                    class="d-flex flex-sm-nowrap flex-wrap gap-1 justify-content-between align-items-center">
                                                    <label class="form-check d-flex gap-2 m-0">
                                                        <input type="checkbox" name="meta_max_snippet" value="1"
                                                               {{ ($meta['meta_max_snippet'] ?? 0) == 1 ? 'checked' : '' }}
                                                               class="form-check-input checkbox--input">
                                                        <span
                                                            class="form-check-label">{{ translate('Max_Snippet') }}</span>
                                                    </label>
                                                    <input type="number" name="meta_max_snippet_value"
                                                           class="form-control max-w-240 h-35px"
                                                           placeholder="-1"
                                                           value="{{ $meta['meta_max_snippet_value'] ?? '' }}">
                                                </div>

                                                <div
                                                    class="d-flex flex-sm-nowrap flex-wrap gap-1 justify-content-between align-items-center">
                                                    <label class="form-check d-flex gap-2 m-0">
                                                        <input type="checkbox" name="meta_max_video_preview" value="1"
                                                               {{ ($meta['meta_max_video_preview'] ?? 0) == 1 ? 'checked' : '' }}
                                                               class="form-check-input checkbox--input">
                                                        <span
                                                            class="form-check-label">{{ translate('Max_Video_Preview') }}</span>
                                                    </label>
                                                    <input type="number" name="meta_max_video_preview_value"
                                                           class="form-control h-35px max-w-240"
                                                           placeholder="-1"
                                                           value="{{ $meta['meta_max_video_preview_value'] ?? '' }}">
                                                </div>

                                                <div
                                                    class="d-flex flex-sm-nowrap flex-wrap gap-1 justify-content-between align-items-center">
                                                    <label class="form-check d-flex gap-2 m-0">
                                                        <input type="checkbox" name="meta_max_image_preview" value="1"
                                                               {{ ($meta['meta_max_image_preview'] ?? 0) == 1 ? 'checked' : '' }}
                                                               class="form-check-input checkbox--input">
                                                        <span
                                                            class="form-check-label">{{ translate('Max_Image_Preview') }}</span>
                                                    </label>
                                                    <select name="meta_max_image_preview_value"
                                                            class="form-control h-35px max-w-240 fs-12 p-1">
                                                        <option
                                                            value="large" {{ ($meta['meta_max_image_preview_value'] ?? '') == 'large' ? 'selected' : '' }}>
                                                            Large
                                                        </option>
                                                        <option
                                                            value="medium" {{ ($meta['meta_max_image_preview_value'] ?? '') == 'medium' ? 'selected' : '' }}>
                                                            Medium
                                                        </option>
                                                        <option
                                                            value="small" {{ ($meta['meta_max_image_preview_value'] ?? '') == 'small' ? 'selected' : '' }}>
                                                            Small
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="justify-content-end btn--container">
                                        <button type="submit"
                                                class="btn btn--primary">{{ translate('Save Changes') }}</button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="earning_delivery_img_form" action="{{ route('admin.remove_image') }}" method="post">
        @csrf
        <input type="hidden" name="id" value="{{  $restaurant?->id}}">
        <input type="hidden" name="model_name" value="Restaurant">
        <input type="hidden" name="image_path" value="restaurant">
        <input type="hidden" name="field_name" value="meta_image">
    </form>
@endsection

@push('script_2')
    <script>
        "use strict";
        $("#customFileEg1").change(function () {
            readURL(this, 'viewer');
        });

        $("#coverImageUpload").change(function () {
            readURL(this, 'coverImageViewer');
        });

        $(document).ready(function () {
            initTextMaxLimit();
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
        });
    </script>
@endpush
