@php use App\CentralLogics\Helpers; @endphp
@php
    $prefix = $type ? $type . '_' : '';
@endphp
<div class="row g-3">

    <div class="col-lg-8">
        <div class="bg-light2 p-xl-20 p-3 rounded">
            @csrf
{{--            @if ($language)--}}
{{--                <ul class="nav nav-tabs mb-4 border-">--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link lang_link active" href="#"--}}
{{--                           id="default-link">{{ translate('messages.default') }}</a>--}}
{{--                    </li>--}}
{{--                    @foreach ($language as $lang)--}}
{{--                        <li class="nav-item">--}}
{{--                            <a class="nav-link lang_link" href="#"--}}
{{--                               id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>--}}
{{--                        </li>--}}
{{--                    @endforeach--}}
{{--                </ul>--}}
{{--            @endif--}}
            <div class="lang_form default-form">
                <div class="row g-3">
                    <input type="hidden" name="lang[]" value="default">
                    <div class="col-md-12">
                        <label for="meta_data_title"
                               class="form-label fw-400">{{ translate('Meta Title') }}
                             <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                  data-placement="right"
                                  data-original-title="{{ translate('This title appears in browser tabs, search results, and link previews. Use a short, clear, and keyword-focused title (recommended: 80-100 characters') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                        </label>
                        <input required id="meta_data_title"
                                                                                      data-maxlength="100"
                               type="text"
                               name="{{ $type == 'admin' ? '' : $prefix }}meta_data_title[]" class="form-control"
                               placeholder="{{ translate('Ex:Type meta title') }}"
                               value="{{ $meta_data_title?->getRawOriginal('value') ?? '' }}">
                        <div class="d-flex justify-content-end">
                            <span class="text-body-light text-right d-block mt-1">0/100</span>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label for="meta_data_description"
                               class="form-label fw-400">{{ translate('messages.Meta Description') }}
                             <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                  data-placement="right"
                                  data-original-title="{{ translate('A brief summary that appears under your page title in search results. Keep it compelling and relevant (recommended: 120—160 characters)') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                        </label>
                        <textarea required id="meta_data_description" type="text"
                                  name="{{ $type == 'admin' ? '' : $prefix }}meta_data_description[]"
                                  placeholder="{{ translate('type a short description for campaign') }}" data-maxlength="160"
                                  class="form-control">{{ $meta_data_description?->getRawOriginal('value') ?? '' }}</textarea>
                        <span class="text-body-light text-right d-block mt-1">0/160</span>
                    </div>
                </div>
            </div>

            @if ($language)
                @forelse($language as $lang)
                    <div class="col-lg-12 d-none lang_form" id="{{ $lang }}-form">
                            <?php
                            if ($meta_data_title?->translations) {
                                $meta_data_title_translate = [];
                                foreach ($meta_data_title->translations as $t) {
                                    if ($t->locale == $lang && $t->key == ($type == 'admin' ? '' : $prefix).'meta_data_title') {
                                        $meta_data_title_translate[$lang]['value'] = $t->value;
                                    }
                                }
                            }
                            if ($meta_data_description?->translations) {
                                $meta_data_description_translate = [];
                                foreach ($meta_data_description->translations as $t) {
                                    if ($t->locale == $lang && $t->key == ($type == 'admin' ? '' : $prefix).'meta_data_description') {
                                        $meta_data_description_translate[$lang]['value'] = $t->value;
                                    }
                                }
                            }

                            ?>
                        <div class="row g-3">
                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                            <div class="col-md-12">
                                <label for="meta_data_title{{ $lang }}"
                                       class="form-label fw-400">{{ translate('Meta Title') }}
                                    ({{ strtoupper($lang) }})
                                    <span class="input-label-secondary text--title"
                                          data-toggle="tooltip" data-placement="right"
                                          data-original-title="{{ translate('This title appears in browser tabs, search results, and link previews. Use a short, clear, and keyword-focused title (recommended: 80-100 characters') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                </label>
                                <input id="meta_data_title{{ $lang }}"
                                                                                                      data-maxlength="100"
                                       type="text"
                                       name="{{$type == 'admin' ? '' : $prefix}}meta_data_title[]" class="form-control"
                                       placeholder="{{ translate('Ex:Type meta title') }}"
                                       value="{{ $meta_data_title_translate[$lang]['value'] ?? '' }}">
                                <div class="d-flex justify-content-end">
                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="meta_data_description{{ $lang }}"
                                       class="form-label fw-400">{{ translate('Meta Description') }}
                                    ({{ strtoupper($lang) }})
                                    <span class="input-label-secondary text--title"
                                          data-toggle="tooltip" data-placement="right"
                                          data-original-title="{{ translate('A brief summary that appears under your page title in search results. Keep it compelling and relevant (recommended: 120—160 characters)') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                </label>
                                <textarea id="meta_data_description{{ $lang }}"
                                          type="text" data-maxlength="160"
                                          name="{{$type == 'admin' ? '' : $prefix}}meta_data_description[]"
                                          placeholder="{{ translate('Type a short description for campaign') }}"
                                          class="form-control">{{ $meta_data_description_translate[$lang]['value'] ?? '' }}</textarea>
                                <span class="text-body-light text-right d-block mt-1">0/160</span>

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
                <div class="text-center">
                                            @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => ($type == 'admin' ? '' : $prefix).'meta_data_image',
                                                'ratio' => '3:1',
                                                'isRequired' => $meta_data_image ? false : true,
                                                'existingImage' => $meta_data_image?->value ? Helpers::get_full_url(($type == 'admin' ? '' : $prefix).'meta_data_image', $meta_data_image?->value, $meta_data_image?->storage[0]?->value ?? 'public'): null,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                                'pixel'=>'1260 x 360'
                                            ])
                                        </div>
                {{-- <div class="upload-file image-260 mx-auto">
                    <input type="file" name="{{$type == 'admin' ? '' : $prefix}}meta_data_image"
                           class="upload-file__input single_file_input"
                           accept=".jpg, .jpeg, .png, .gif"
                           @if (!$meta_data_image?->image) required @endif>
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
                             src="{{ $meta_data_image?->value ? Helpers::get_full_url(($type == 'admin' ? '' : $prefix).'meta_data_image', $meta_data_image?->value, $meta_data_image?->storage[0]?->value ?? 'public'): '' }}"
                             data-default-src="{{ $meta_data_image?->image_full_url }}"
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
                        </div>
                    </div>
                </div>
                <p class="fs-10 text-center mb-0 mt-lg-4 mt-3">
                    {{ translate('JPG, JPEG, PNG Less Than 2MB') }} <span
                        class="font-medium text-title">{{ translate('(1260  x 360 px)') }}</span>
                </p> --}}
            </div>
        </div>

    </div>






{{--    <div class="col-lg-4">--}}
{{--        <div class="p-xxl-20 d-flex align-items-center justify-content-center p-12 global-bg-box text-center rounded h-100">--}}
{{--            <div class="">--}}
{{--                <div class="mb-xxl-5 mb-xl-4 mb-3 text-start">--}}
{{--                    <label--}}
{{--                        class="form-label fw-400">{{ translate('Meta Image') }}<span class="text-danger">*</span>--}}
{{--                        <span class="input-label-secondary text--title"--}}
{{--                              data-toggle="tooltip" data-placement="right"--}}
{{--                              data-original-title="{{ translate('This image is used as a preview thumbnail when the page link is shared on social media or messaging  platforms.') }}">--}}
{{--                                                    <i class="tio-info text-gray1 fs-16"></i>--}}
{{--                                                </span>--}}
{{--                    </label>--}}
{{--                    --}}{{-- <h5 class="mb-1">{{ translate('Meta Image') }}<span class="text-danger">*</span></h5>--}}
{{--                      <span class="input-label-secondary text--title" data-toggle="tooltip"--}}
{{--                                  data-placement="right"--}}
{{--                                  data-original-title="{{ translate('A brief summary that appears under your page title in search results. Keep it compelling and relevant (recommended: 120—160 characters)') }}">--}}
{{--                                <i class="tio-info text-gray1 fs-16"></i>--}}
{{--                            </span> --}}
{{--                    <p class="mb-0 fs-12 gray-dark">--}}
{{--                        {{ translate('Upload your meta image') }}</p>--}}
{{--                </div>--}}
{{--                <div class="upload-file image-260 mx-auto">--}}
{{--                    <input type="file" name="{{$type == 'admin' ? '' : $prefix}}meta_data_image"--}}
{{--                           class="upload-file__input single_file_input"--}}
{{--                           accept=".jpg, .jpeg, .png, .gif" @if(!$meta_data_image?->value) required @endif>--}}
{{--                    <label class="upload-file__wrapper m-0">--}}
{{--                        <div class="upload-file-textbox text-center" style="">--}}
{{--                            <img width="22" class="svg"--}}
{{--                                 src="{{ dynamicAsset('assets/admin/img/image-upload.png') }}"--}}
{{--                                 alt="img">--}}
{{--                            <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">--}}
{{--                                <span class="text-info">{{ translate('Click to upload') }}</span>--}}
{{--                                <br>--}}
{{--                                {{ translate('Or drag and drop') }}--}}
{{--                            </h6>--}}
{{--                        </div>--}}
{{--                        <img class="upload-file-img" loading="lazy"--}}
{{--                             src="{{ Helpers::get_full_url(($type == 'admin' ? '' : $prefix).'meta_data_image', $meta_data_image?->value, $meta_data_image?->storage[0]?->value ?? 'public', 'upload_1_1') }}"--}}
{{--                             data-default-src="{{ Helpers::get_full_url(($type == 'admin' ? '' : $prefix).'meta_data_image', $meta_data_image?->value, $meta_data_image?->storage[0]?->value ?? 'public', 'upload_1_1') }}" alt="" style="display: none;">--}}
{{--                    </label>--}}
{{--                    <div class="overlay">--}}
{{--                        <div class="d-flex gap-1 justify-content-center align-items-center h-100">--}}
{{--                            <button type="button" class="btn btn-outline-info icon-btn view_btn">--}}
{{--                                <i class="tio-invisible"></i>--}}
{{--                            </button>--}}
{{--                            <button type="button" class="btn btn-outline-info icon-btn edit_btn">--}}
{{--                                <i class="tio-edit"></i>--}}
{{--                            </button>--}}

{{--                            <input type="hidden" name="meta_data_image_remove" id="meta_data_image_remove" value="0">--}}
{{--                            <button type="button" id="remove_meta_data_image_btn" class="remove_btn btn icon-btn">--}}
{{--                                <i class="tio-delete text-danger"></i>--}}
{{--                            </button>--}}


{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <p class="fs-10 text-center mb-0 mt-lg-4 mt-3">--}}
{{--                    {{ translate('JPG, JPEG, PNG Less Than 2MB') }} <span--}}
{{--                        class="font-medium text-title">{{ translate('(1260  x 360 px)') }}</span>--}}
{{--                </p>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--    </div>--}}

</div>

<div class="row g-3 pt-4">
    <!-- LEFT COLUMN -->
    <div class="col-lg-6 col-xl-6">
        <div class="bg-light2 rounded p-xxl-20 p-3 h-100">
            <div class="bg-white d-flex flex-wrap gap-2 justify-content-between rounded mb-3 index-area-here">
                <div class="d-flex flex-column gap-2 flex-grow-1">
                    {{-- Index --}}
                    <label class="form-check radio-sclae-1 mb-0 d-flex gap-2">
                        <input type="radio" name="{{ $prefix }}meta_index" value="1"
                               {{ ($meta_index ?? '') != 0 ? 'checked' : '' }}
                               class="form-check-input radio--input">
                        <span class="user-select-none form-check-label">{{ translate('Index') }}</span>
                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                              data-placement="right"
                              data-original-title="{{ translate('allow_search_engines_to_put_this_web_page_on_their_list_or_index_and_show_it_on_search_results.') }}">
                                                   <i class="tio-info text-gray1 fs-16"></i>
                                               </span>
                    </label>
                </div>
                <div class="d-flex flex-column gap-2 flex-grow-1 ps-sm-47">
                    {{-- No Index --}}
                    <label class="form-check radio-sclae-1 mb-0 d-flex gap-2">
                        <input type="radio" name="{{ $prefix }}meta_index" value="0"
                               {{ ($meta_index ?? '') == 0 ? 'checked' : '' }}
                               class="form-check-input radio--input">
                        <span class="user-select-none form-check-label">{{ translate('No_Index') }}</span>
                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                              data-placement="right"
                              data-original-title="{{ translate('disallow_search_engines_to_put_this_web_page_on_their_list_or_index_and_do_not_show_it_on_search_results.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                    </label>
                </div>
            </div>
            <div class="card border-0 rounded">
                <div class="card-body d-flex flex-wrap gap-2 justify-content-between h-100">
                    <div class="item d-flex flex-column gap-2 flex-grow-1">

                        {{-- No Follow --}}
                        <label class="form-check d-flex gap-2">
                            <input type="checkbox" name="{{ $prefix }}meta_no_follow" value="nofollow"
                                   {{ ($meta_no_follow ?? '') == 'nofollow' ? 'checked' : '' }}
                                   class="form-check-input checkbox--input">
                            <span class="user-select-none form-check-label">{{ translate('No_Follow') }}</span>
                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                  data-placement="right"
                                  data-original-title="{{ translate('instruct_search_engines_not_to_follow_links_from_this_web_page.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                        </label>
                        {{-- No Image Index --}}
                        <label class="form-check mb-0 d-flex gap-2">
                            <input type="checkbox" name="{{ $prefix }}meta_no_image_index" value="noimageindex"
                                   {{ ($meta_no_image_index ?? '') == 'noimageindex' ? 'checked' : '' }}
                                   class="form-check-input checkbox--input">
                            <span class="user-select-none form-check-label">{{ translate('No_Image_Index') }}</span>
                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                  data-placement="right"
                                  data-original-title="{{ translate('prevents_images_from_being_listed_or_indexed_by_search_engines') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                        </label>

                    </div>

                    <div class="item d-flex flex-column gap-2 flex-grow-1">


                        {{-- No Archive --}}
                        <label class="form-check d-flex gap-2">
                            <input type="checkbox" name="{{ $prefix }}meta_no_archive" value="noarchive"
                                   {{ ($meta_no_archive ?? '') == 'noarchive' ? 'checked' : '' }}
                                   class="form-check-input checkbox--input">
                            <span class="user-select-none form-check-label">{{ translate('No_Archive') }}</span>
                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                  data-placement="right"
                                  data-original-title="{{ translate('instruct_search_engines_not_to_display_this_webpages_cached_or_saved_version.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                        </label>

                        {{-- No Snippet --}}
                        <label class="form-check d-flex gap-2">
                            <input type="checkbox" name="{{ $prefix }}meta_no_snippet" value="nosnippet"
                                   {{ ($meta_no_snippet ?? '') == 'nosnippet' ? 'checked' : '' }}
                                   class="form-check-input checkbox--input">
                            <span class="user-select-none form-check-label">{{ translate('No_Snippet') }}</span>
                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                  data-placement="right"
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
                <div class="card-body d-flex flex-column gap-2 h-100">

                    {{-- Max Snippet --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                        <div class="item">
                            <label class="form-check d-flex gap-2 m-0">
                                <input type="checkbox" name="{{ $prefix }}meta_max_snippet" value="1"
                                       {{ ($meta_max_snippet ?? '') == 1 ? 'checked' : '' }}
                                       class="form-check-input checkbox--input">
                                <span class="user-select-none form-check-label">{{ translate('Max_Snippet') }}</span>
                                <span class="input-label-secondary text--title" data-toggle="tooltip"
                                      data-placement="right"
                                      data-original-title="{{ translate('determine_the_maximum_length_of_a_snippet_or_preview_text_of_the_webpage.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                            </label>
                        </div>
                        <div class="item flex-grow-0 item_in">
                            <input type="number" placeholder="-1" class="form-control h-35px py-0"
                                   name="{{ $prefix }}meta_max_snippet_value"
                                   value="{{ $meta_max_snippet_value ?? '' }}">
                        </div>
                    </div>

                    {{-- Max Video Preview --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                        <div class="item">
                            <label class="form-check d-flex gap-2 m-0">
                                <input type="checkbox" name="{{ $prefix }}meta_max_video_preview" value="1"
                                       {{ ($meta_max_video_preview ?? '') == 1 ? 'checked' : '' }}
                                       class="form-check-input checkbox--input">
                                <span
                                    class="user-select-none form-check-label">{{ translate('Max_Video_Preview') }}</span>
                                <span class="input-label-secondary text--title" data-toggle="tooltip"
                                      data-placement="right"
                                      data-original-title="{{ translate('determine_the_maximum_duration_of_a_video_preview_that_search_engines_will_display') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                            </label>
                        </div>
                        <div class="item flex-grow-0 item_in">
                            <input type="number" placeholder="-1" class="form-control h-35px py-0"
                                   name="{{ $prefix }}meta_max_video_preview_value"
                                   value="{{ $meta_max_video_preview_value ?? '' }}">
                        </div>
                    </div>

                    {{-- Max Image Preview --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                        <div class="item">
                            <label class="form-check d-flex gap-2 m-0">
                                <input type="checkbox" name="{{ $prefix }}meta_max_image_preview" value="1"
                                       {{ ($meta_max_image_preview ?? '') == 1 ? 'checked' : '' }}
                                       class="form-check-input checkbox--input">
                                <span
                                    class="user-select-none form-check-label">{{ translate('Max_Image_Preview') }}</span>
                                <span class="input-label-secondary text--title" data-toggle="tooltip"
                                      data-placement="right"
                                      data-original-title="{{ translate('determine_the_maximum_size_or_dimensions_of_an_image_preview_that_search_engines_will_display.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                            </label>
                        </div>
                        <div class="item max-w-240 w-100">
                            <div class="select-wrapper">
                                <select class="form-select seo-select-large h-35px js-select2-custom py-0"
                                        name="admin_meta_max_image_preview_value">
                                    <option value="large"
                                        {{ ($meta_max_image_preview_value ?? '') == 'large' ? 'selected' : '' }}>
                                        {{ translate('large') }}</option>
                                    <option value="medium"
                                        {{ ($meta_max_image_preview_value ?? '') == 'medium' ? 'selected' : '' }}>
                                        {{ translate('medium') }}</option>
                                    <option value="small"
                                        {{ ($meta_max_image_preview_value ?? '') == 'small' ? 'selected' : '' }}>
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
