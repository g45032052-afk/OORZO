@php
    use App\CentralLogics\Helpers;
    $product = null;
@endphp
@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))

@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h3 class="page-header-title fs-24 text-capitalize">
                    {{-- <div class="card-header-icon d-inline-flex mr-2 img">
                        <img src="{{ dynamicAsset('assets/admin/img/landing-page.png') }}" class="mw-26px"
                            alt="public">
                    </div> --}}
                    <span>
                        {{ request()->page_name == 'vendor_list'?translate('Restaurant_List_Setup'): translate(request()->page_name . '_Setup') }}
                    </span>
                </h3>
            </div>
        </div>

        <div class="seo_wrapper">
            <div class="outline-wrapper">
                <div class="card rest-part bg-animate">
                    <div class="card-header gap-1 flex-sm-nowrap flex-wrap">
                        <div class="gap-2">
                            <h3 class="mb-0">
                                {{ translate('Meta Data Setup') }}
                            </h3>
                            <p>
                                {{ translate('Optimize your Website\'s performance, indexing status, and search visibility.') }}
{{--                                <a href="#" class="text-primary">Learn more</a>--}}
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('admin.pageMetaData') }}" class="text--underline text-nowrap">
                                {{ translate('Back to List') }}
                            </a>
                        </div>

                    </div>


                    <div class="card border-0">
                        <!-- <div class="card-header">
                            <div class="">
                                <h3 class="mb-1">{{ translate('Meta Data Setup') }}</h3>
                                <p class="mb-0 gray-dark fs-12">
                                    {{ translate('Include meta title, description, and image to improve search engine visibility and social media sharing.') }}
                        </p>
                    </div>
                </div> -->
                        <div class="card-body p-xl-20 p-3">
                            <div class="">
                                <form action="{{ route('admin.pageMetaDataUpdate') }}" method="POST"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="page_name" value="{{ request()->page_name }}">
                                    <div class="row g-3">
                                        <div class="col-lg-8">
                                            <div class="bg-light2 p-xl-20 p-3 rounded">
                                                @csrf
{{--                                                @if ($language)--}}
{{--                                                    <ul class="nav nav-tabs mb-4 border-">--}}
{{--                                                        <li class="nav-item">--}}
{{--                                                            <a class="nav-link lang_link active" href="#"--}}
{{--                                                               id="default-link">{{ translate('messages.default') }}</a>--}}
{{--                                                        </li>--}}
{{--                                                        @foreach ($language as $lang)--}}
{{--                                                            <li class="nav-item">--}}
{{--                                                                <a class="nav-link lang_link" href="#"--}}
{{--                                                                   id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>--}}
{{--                                                            </li>--}}
{{--                                                        @endforeach--}}
{{--                                                    </ul>--}}
{{--                                                @endif--}}
                                                <div class="lang_form default-form">
                                                    <div class="row g-3">
                                                        <input type="hidden" name="lang[]" value="default">
                                                        <div class="col-md-12">
                                                            <label for="title"
                                                                   class="form-label fw-400">{{ translate('Meta Title') }}
                                                                 <span
                                                                    class="text-danger">*</span>
                                                                <span class="input-label-secondary text--title"
                                                                      data-toggle="tooltip" data-placement="right"
                                                                      data-original-title="{{ translate('This title appears in browser tabs, search results, and link previews. Use a short, clear, and keyword-focused title (recommended: 80-100 characters') }}">
                                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                                </span>
                                                            </label>
                                                            <input id="title"
                                                                                                          data-maxlength="100"  type="text"
                                                                   name="title[]" class="form-control"
                                                                   placeholder="{{ translate('Ex:Type meta title') }}"
                                                                   value="{{ $pageMetaData?->getRawOriginal('title') ?? '' }}">
                                                                                                            <div class="d-flex justify-content-end">
                                                                                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <label for="description"
                                                                   class="form-label fw-400">{{ translate('messages.Meta Description') }}
                                                                <span
                                                                    class="text-danger">*</span>
                                                                <span class="input-label-secondary text--title"
                                                                      data-toggle="tooltip" data-placement="right"
                                                                      data-original-title="{{ translate('A brief summary that appears under your page title in search results. Keep it compelling and relevant (recommended: 120—160 characters)') }}">
                                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                                </span>
                                                            </label>
                                                            <textarea id="description" type="text" name="description[]"
                                                                      placeholder="{{ translate('type a short meta description') }}"
                                                                      data-maxlength="160"
                                                                      class="form-control">{{ $pageMetaData?->getRawOriginal('description') ?? '' }}</textarea>
                                                            <span
                                                                class="text-body-light text-right d-block mt-1">0/160</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if ($language)
                                                    @forelse($language as $lang)
                                                        <div class="col-lg-12 d-none lang_form"
                                                             id="{{ $lang }}-form">
                                                                <?php
                                                                if ($pageMetaData?->translations) {
                                                                    $meta_data_title_translate = [];
                                                                    foreach ($pageMetaData->translations as $t) {
                                                                        if ($t->locale == $lang && $t->key == 'title') {
                                                                            $meta_data_title_translate[$lang]['value'] = $t->value;
                                                                        }
                                                                    }
                                                                }
                                                                if ($pageMetaData?->translations) {
                                                                    $meta_data_description_translate = [];
                                                                    foreach ($pageMetaData->translations as $t) {
                                                                        if ($t->locale == $lang && $t->key == 'description') {
                                                                            $meta_data_description_translate[$lang]['value'] = $t->value;
                                                                        }
                                                                    }
                                                                }

                                                                ?>
                                                            <div class="row g-3">
                                                                <input type="hidden" name="lang[]"
                                                                       value="{{ $lang }}">
                                                                <div class="col-md-12">
                                                                    <label for="title{{ $lang }}"
                                                                           class="form-label fw-400">{{ translate('Meta Title') }}
                                                                        ({{ strtoupper($lang) }})
                                                                        <span class="input-label-secondary text--title"
                                                                              data-toggle="tooltip"
                                                                              data-placement="right"
                                                                              data-original-title="{{ translate('This title appears in browser tabs, search results, and link previews. Use a short, clear, and keyword-focused title (recommended: 80-100 characters') }}">
                                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                                        </span>
                                                                    </label>
                                                                    <input id="title{{ $lang }}"
                                                                           data-maxlength="100" type="text"
                                                                           name="title[]"
                                                                           class="form-control"
                                                                           placeholder="{{ translate('Ex:Type meta title') }}"
                                                                           value="{{ $meta_data_title_translate[$lang]['value'] ?? '' }}">
                                                                    <span
                                                                        class="text-body-light text-right d-block mt-1">0/50</span>
                                                                </div>

                                                                <div class="col-md-12">
                                                                    <label for="description{{ $lang }}"
                                                                           class="form-label fw-400">{{ translate('Meta Description') }}
                                                                        ({{ strtoupper($lang) }})
                                                                        <span class="input-label-secondary text--title"
                                                                              data-toggle="tooltip"
                                                                              data-placement="right"
                                                                              data-original-title="{{ translate('A brief summary that appears under your page title in search results. Keep it compelling and relevant (recommended: 120—160 characters)') }}">
                                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                                        </span>
                                                                    </label>
                                                                    <textarea id="description{{ $lang }}" type="text"
                                                                              data-maxlength="160" name="description[]"
                                                                              placeholder="{{ translate('Type a short meta description') }}"
                                                                              class="form-control">{{ $meta_data_description_translate[$lang]['value'] ?? '' }}</textarea>
                                                                    <span
                                                                        class="text-body-light text-right d-block mt-1">0/160</span>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                    @endforelse
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div
                                                class="p-xxl-20 d-flex align-items-center justify-content-center p-12 global-bg-box text-center rounded h-100">
                                                <div class="">
                                                    <div class="mb-xxl-5 mb-xl-4 mb-3 text-start">
                                                        <label
                                                            class="form-label fw-400">{{ translate('Meta Image') }}<span
                                                                class="text-danger">*</span>
                                                            <span class="input-label-secondary text--title"
                                                                  data-toggle="tooltip" data-placement="right"
                                                                  data-original-title="{{ translate('This image is used as a preview thumbnail when the page link is shared on social media or messaging  platforms.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                        {{-- <h5 class="mb-1">{{ translate('Meta Image') }}<span class="text-danger">*</span></h5> --}}
                                                        <p class="mb-0 fs-12 gray-dark">
                                                            {{ translate('Upload your meta image') }}</p>
                                                    </div>
                                                    <div class="upload-file image-260 mx-auto">
                                                        <input type="file" name="image"
                                                               class="upload-file__input single_file_input"
                                                               accept=".jpg, .jpeg, .png, .gif"
                                                               @if (!$pageMetaData?->image) required @endif>
                                                        <label class="upload-file__wrapper m-0">
                                                            <div class="upload-file-textbox text-center" style="">
                                                                <img width="22" class="svg"
                                                                     src="{{ dynamicAsset('assets/admin/img/image-upload.png') }}"
                                                                     alt="img">
                                                                <h6
                                                                    class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                                    <span
                                                                        class="text-info">{{ translate('Click to upload') }}</span>
                                                                    <br>
                                                                    {{ translate('Or drag and drop') }}
                                                                </h6>
                                                            </div>
                                                            <img class="upload-file-img" loading="lazy"
                                                                 src="{{ $pageMetaData?->image_full_url }}"
                                                                 data-default-src="{{ $pageMetaData?->image_full_url }}"
                                                                 alt="" style="display: none;">
                                                        </label>
                                                        <div class="overlay">
                                                            <div
                                                                class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                                <button type="button"
                                                                        class="btn btn-outline-info icon-btn view_btn">
                                                                    <i class="tio-invisible"></i>
                                                                </button>
                                                                <button type="button"
                                                                        class="btn btn-outline-info icon-btn edit_btn">
                                                                    <i class="tio-edit"></i>
                                                                </button>

                                                                {{-- <input type="hidden" name="meta_data_image_remove"
                                                        id="meta_data_image_remove" value="0">
                                                    <button type="button" id="remove_meta_data_image_btn"
                                                        class="remove_btn btn icon-btn">
                                                        <i class="tio-delete text-danger"></i>
                                                    </button> --}}


                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p class="fs-10 text-center mb-0 mt-lg-4 mt-3">
                                                        {{ translate('JPG, JPEG, PNG Less Than 2MB') }} <span
                                                            class="font-medium text-title">{{ translate('(1260  x 360 px)') }}</span>
                                                    </p>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    @php($metaData = $jsonData = $pageMetaData->meta_data ?? [])
                                    <div class="row g-3 pt-4">
                                        <!-- LEFT COLUMN -->
                                        <div class="col-lg-6 col-xl-6">
                                            <div class="bg-light2 rounded p-xxl-20 p-3 h-100">
                                                <div
                                                    class="bg-white d-flex flex-wrap gap-2 justify-content-between rounded mb-3 index-area-here">
                                                    <div class="d-flex flex-column gap-2 flex-grow-1">
                                                        {{-- Index --}}
                                                        <label class="form-check radio-sclae-1 d-flex gap-2 mb-0">
                                                            <input type="radio" name="meta_index" value="1"
                                                                   {{ ($metaData['meta_index'] ?? '') != 0 ? 'checked' : '' }}
                                                                   class="form-check-input radio--input">
                                                            <span
                                                                class="user-select-none form-check-label">{{ translate('Index') }}</span>
                                                            <span class="input-label-secondary text--title"
                                                                  data-toggle="tooltip" data-placement="right"
                                                                  data-original-title="{{ translate('allow_search_engines_to_put_this_web_page_on_their_list_or_index_and_show_it_on_search_results.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                    </div>

                                                    <div class="d-flex flex-column gap-2 flex-grow-1 ps-sm-47">
                                                        {{-- No Index --}}
                                                        <label class="form-check radio-sclae-1 d-flex gap-2 mb-0">
                                                            <input type="radio" name="meta_index" value="0"
                                                                   {{ ($metaData['meta_index'] ?? '') == 0 ? 'checked' : '' }}
                                                                   class="form-check-input radio--input">
                                                            <span
                                                                class="user-select-none form-check-label">{{ translate('No_Index') }}</span>
                                                            <span class="input-label-secondary text--title"
                                                                  data-toggle="tooltip" data-placement="right"
                                                                  data-original-title="{{ translate('disallow_search_engines_to_put_this_web_page_on_their_list_or_index_and_do_not_show_it_on_search_results.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="card border-0 rounded">
                                                    <div
                                                        class="card-body py-3 px-3 d-flex flex-wrap gap-2 justify-content-between h-100">
                                                        <div class="item d-flex flex-column gap-2 flex-grow-1">


                                                            {{-- No Follow --}}
                                                            <label class="form-check d-flex gap-2">
                                                                <input type="checkbox" name="meta_no_follow"
                                                                       value="nofollow"
                                                                       {{ ($metaData['meta_no_follow'] ?? '') == 'nofollow' ? 'checked' : '' }}
                                                                       class="form-check-input checkbox--input">
                                                                <span
                                                                    class="user-select-none form-check-label">{{ translate('No_Follow') }}</span>
                                                                <span class="input-label-secondary text--title"
                                                                      data-toggle="tooltip" data-placement="right"
                                                                      data-original-title="{{ translate('instruct_search_engines_not_to_follow_links_from_this_web_page.') }}">
                                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                                </span>
                                                            </label>

                                                            {{-- No Image Index --}}
                                                            <label class="form-check d-flex gap-2">
                                                                <input type="checkbox" name="meta_no_image_index"
                                                                       value="noimageindex"
                                                                       {{ ($metaData['meta_no_image_index'] ?? '') == 'noimageindex' ? 'checked' : '' }}
                                                                       class="form-check-input checkbox--input">
                                                                <span
                                                                    class="user-select-none form-check-label">{{ translate('No_Image_Index') }}</span>
                                                                <span class="input-label-secondary text--title"
                                                                      data-toggle="tooltip" data-placement="right"
                                                                      data-original-title="{{ translate('prevents_images_from_being_listed_or_indexed_by_search_engines') }}">
                                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                                </span>
                                                            </label>
                                                        </div>

                                                        <div class="item d-flex flex-column gap-2 flex-grow-1">


                                                            {{-- No Archive --}}
                                                            <label class="form-check d-flex gap-2">
                                                                <input type="checkbox" name="meta_no_archive"
                                                                       value="noarchive"
                                                                       {{ ($metaData['meta_no_archive'] ?? '') == 'noarchive' ? 'checked' : '' }}
                                                                       class="form-check-input checkbox--input">
                                                                <span
                                                                    class="user-select-none form-check-label">{{ translate('No_Archive') }}</span>
                                                                <span class="input-label-secondary text--title"
                                                                      data-toggle="tooltip" data-placement="right"
                                                                      data-original-title="{{ translate('instruct_search_engines_not_to_display_this_webpages_cached_or_saved_version.') }}">
                                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                                </span>
                                                            </label>

                                                            {{-- No Snippet --}}
                                                            <label class="form-check d-flex gap-2">
                                                                <input type="checkbox" name="meta_no_snippet"
                                                                       value="nosnippet"
                                                                       {{ ($metaData['meta_no_snippet'] ?? '') == 'nosnippet' ? 'checked' : '' }}
                                                                       class="form-check-input checkbox--input">
                                                                <span
                                                                    class="user-select-none form-check-label">{{ translate('No_Snippet') }}</span>
                                                                <span class="input-label-secondary text--title"
                                                                      data-toggle="tooltip" data-placement="right"
                                                                      data-original-title="{{ translate('instruct_search_engines_not_to_show_a_summary_or_snippet_of_this_webpages_content_in_search_results.') }}">
                                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- RIGHT COLUMN -->
                                        <div class="col-lg-6 col-xl-6">
                                            <div class="bg-light2 rounded p-xxl-20 p-3 h-100">
                                                <div class="card border-0 h-100 rounded">
                                                    <div class="card-body py-3 px-3 d-flex flex-column gap-2 h-100">

                                                        {{-- Max Snippet --}}
                                                        <div
                                                            class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                                                            <div class="item">
                                                                <label class="form-check d-flex gap-2 m-0">
                                                                    <input type="checkbox" name="meta_max_snippet"
                                                                           value="1"
                                                                           {{ ($metaData['meta_max_snippet'] ?? '') == 1 ? 'checked' : '' }}
                                                                           class="form-check-input checkbox--input">
                                                                    <span
                                                                        class="user-select-none form-check-label">{{ translate('Max_Snippet') }}</span>
                                                                    <span class="input-label-secondary text--title"
                                                                          data-toggle="tooltip" data-placement="right"
                                                                          data-original-title="{{ translate('determine_the_maximum_length_of_a_snippet_or_preview_text_of_the_webpage.') }}">
                                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="item max-w-240">
                                                                <input type="number" placeholder="-1"
                                                                       class="form-control h-35px py-0"
                                                                       name="meta_max_snippet_value"
                                                                       value="{{ $metaData['meta_max_snippet_value'] ?? '' }}">
                                                            </div>
                                                        </div>

                                                        {{-- Max Video Preview --}}
                                                        <div
                                                            class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                                                            <div class="item">
                                                                <label class="form-check d-flex gap-2 m-0">
                                                                    <input type="checkbox" name="meta_max_video_preview"
                                                                           value="1"
                                                                           {{ ($metaData['meta_max_video_preview'] ?? '') == 1 ? 'checked' : '' }}
                                                                           class="form-check-input checkbox--input">
                                                                    <span
                                                                        class="user-select-none form-check-label">{{ translate('Max_Video_Preview') }}</span>
                                                                    <span class="input-label-secondary text--title"
                                                                          data-toggle="tooltip" data-placement="right"
                                                                          data-original-title="{{ translate('determine_the_maximum_duration_of_a_video_preview_that_search_engines_will_display') }}">
                                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="item max-w-240">
                                                                <input type="number" placeholder="-1"
                                                                       class="form-control h-35px py-0"
                                                                       name="meta_max_video_preview_value"
                                                                       value="{{ $metaData['meta_max_video_preview_value'] ?? '' }}">
                                                            </div>
                                                        </div>

                                                        {{-- Max Image Preview --}}
                                                        <div
                                                            class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                                                            <div class="item">
                                                                <label
                                                                    class="form-check d-flex align-items-center gap-2 m-0">
                                                                    <input type="checkbox" name="meta_max_image_preview"
                                                                           value="1"
                                                                           {{ ($metaData['meta_max_image_preview'] ?? '') == 1 ? 'checked' : '' }}
                                                                           class="form-check-input h-35px checkbox--input">
                                                                    <span
                                                                        class="user-select-none form-check-label mt-1">{{ translate('Max_Image_Preview') }}</span>
                                                                    <span class="input-label-secondary text--title"
                                                                          data-toggle="tooltip" data-placement="right"
                                                                          data-original-title="{{ translate('determine_the_maximum_size_or_dimensions_of_an_image_preview_that_search_engines_will_display.') }}">
                                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="item max-w-240 w-100">
                                                                <div class="select-wrapper">
                                                                    <select
                                                                        class="form-select seo-select-large js-select2-custom py-0"
                                                                        name="meta_max_image_preview_value">
                                                                        <option value="large"
                                                                            {{ ($metaData['meta_max_image_preview_value'] ?? '') == 'large' ? 'selected' : '' }}>
                                                                            {{ translate('large') }}</option>
                                                                        <option value="medium"
                                                                            {{ ($metaData['meta_max_image_preview_value'] ?? '') == 'medium' ? 'selected' : '' }}>
                                                                            {{ translate('medium') }}</option>
                                                                        <option value="small"
                                                                            {{ ($metaData['meta_max_image_preview_value'] ?? '') == 'small' ? 'selected' : '' }}>
                                                                            {{ translate('small') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="btn--container justify-content-end mt-4">
                                        <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                                        <button type="submit" class="btn btn--primary">{{ translate('Save') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>

@endsection

@push('script_2')
    <script>
        "use strict";
        document.addEventListener('DOMContentLoaded', function () {
            var removeBtn = document.getElementById('remove_meta_data_image_btn');
            var removeFlag = document.getElementById('meta_data_image_remove');
            var fileInput = document.querySelector('input[name="meta_data_image"]');
            var previewImg = document.querySelector('.upload-file-img');
            var uploadText = document.querySelector('.upload-file-textbox');
            var form = fileInput ? fileInput.closest('form') : null;

            if (removeBtn && removeFlag && previewImg) {
                removeBtn.addEventListener('click', function () {
                    removeFlag.value = '1';
                    fileInput.value = '';

                    previewImg.style.display = 'none';
                    previewImg.removeAttribute('src');
                    previewImg.removeAttribute('data-default-src');

                    if (uploadText) uploadText.style.display = 'block';
                });
            }

            if (form && removeFlag) {
                form.addEventListener('reset', function () {
                    removeFlag.value = '0';
                    if (previewImg && previewImg.dataset.defaultSrc) {
                        previewImg.src = previewImg.dataset.defaultSrc;
                        previewImg.style.display = 'block';
                    }
                });
            }

            if (fileInput && removeFlag) {
                fileInput.addEventListener('change', function () {
                    removeFlag.value = '0';
                    if (previewImg) previewImg.style.display = 'block';
                    if (uploadText) uploadText.style.display = 'none';
                });
            }
        });

        $(document).ready(function () {
            $('input[name="meta_index"][value="0"]').on('change', function () {
                if ($(this).is(':checked')) {
                    $('input[name="meta_no_follow"]').prop('checked', true);
                    $('input[name="meta_no_image_index"]').prop('checked', true);
                    $('input[name="meta_no_archive"]').prop('checked', true);
                    $('input[name="meta_no_snippet"]').prop('checked', true);
                }
            });

            $('input[name="meta_index"][value="1"]').on('change', function () {
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
