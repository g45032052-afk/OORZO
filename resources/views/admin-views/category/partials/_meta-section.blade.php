<div class="bg--secondary rounded py-3 px-3">
    <div>
        <div class=" border-0 h-100 form-group">
            <div class="card-body-0">
                <div class="d-flex flex-column align-items-center gap-3">
                    <div class="mb-0 text-center">
                        <h5 class="mb-1">
                           {{ translate('Meta Image') }}
                        </h5>
                        <p class="mb-0 fs-12">{{ translate('Upload your meta image') }}</p>
                    </div>

                    <div class="upload-file mx-auto">
                        <input type="file" name="meta_image" class="upload-file__input single_file_input"
                               accept="{{IMAGE_EXTENSION}}">
                        <input type="hidden" name="action_type" value="{{$data ? 'update':'add'}}">
                        <label class="upload-file__wrapper h-100px mx-auto ratio-2-1 m-0">
                            <div class="upload-file-textbox text-center" id="meta-image-upload">
                                <img width="22" class="svg"
                                     src="{{dynamicAsset('assets/admin/img/image-upload.png')}}"
                                     alt="img">
                                <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                    <span class="text-info">{{translate('Click to upload')}}</span>
                                    <br>
                                    {{translate('Or drag and drop')}}
                                </h6>
                            </div>
                            <img class="upload-file-img" id="meta_image_preview" loading="lazy"
                                 src="{{ $data?->meta_image ? $data?->meta_image_full_url : '' }}"
                                 alt="" style="">
                        </label>
                        <div class="overlay">
                            <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                    <i class="tio-invisible"></i>
                                </button>
                                <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                    <i class="tio-edit"></i>
                                </button>
                                <input type="hidden" name="meta_image_remove" id="meta_image_remove"
                                       value="0">
                                <button type="button" id="remove_meta_image_btn"
                                        class="remove_btn btn icon-btn">
                                    <i class="tio-delete text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="fs-10 text-center mb-0">
                      {{ translate(IMAGE_FORMAT.' Less Than '.MAX_FILE_SIZE.'MB') }}  {{ translate('(Ratio 2:1)') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="card card-body mb-3 p-xl-3 py-3">
            @if ($language)
                <div class="row lang_form" id="default-form-meta-section">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="meta_title" class="form-label fw-400 fs-13">
                                {{ translate('meta_Title') }}
                                <span class="input-label-secondary text--title" data-toggle="tooltip"
                                      data-placement="right"
                                      title="{{ translate('add_the_products_title_name_taglines_etc_here') . ' ' . translate('this_title_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms')}}">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>

                            </label>
                            <input type="text" maxlength="100" name="meta_title[]"
                                   value="{{ $data?->getRawOriginal('meta_title') ?? '' }}"
                                   placeholder="{{ translate('meta_Title') }}"
                                   class="form-control" id="meta_title">
                            <div class="d-flex justify-content-end">
                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                            </div>
                        </div>
                        <div class="form-group mb-">
                            <label for="meta_description" class="form-label0 fw-400 fs-13">
                                {{ translate('meta_Description') }}

                                <span class="input-label-secondary text--title" data-toggle="tooltip"
                                      data-placement="right"
                                      title="{{ translate('write_a_short_description_of_the_InHouse_shops_product') . ' ' . translate('this_description_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms')}}">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>

                            </label>
                            <textarea rows="4" type="text" placeholder="{{ translate('meta_Description') }}" name="meta_description[]" maxlength="160"
                                      id="meta_description"
                                      class="form-control">{{$data?->getRawOriginal('meta_description')??''}}</textarea>
                            <div class="d-flex justify-content-end">
                                <span class="text-body-light text-right d-block mt-1">0/160</span>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach ($language as $key => $lang)
                        <?php
                        $data_meta_title_translate = [];
                        $data_meta_description_translate = [];
                        if (isset($data->translations) && count($data->translations)) {
                            foreach ($data->translations as $t) {
                                if ($t->locale == $lang && $t->key == 'meta_title') {
                                    $data_meta_title_translate[$lang]['value'] = $t->value;
                                }
                            }
                        }
                        if (isset($data->translations) && count($data->translations)) {
                            foreach ($data->translations as $t) {
                                if ($t->locale == $lang && $t->key == 'meta_description') {
                                    $data_meta_description_translate[$lang]['value'] = $t->value;
                                }
                            }
                        }
                        ?>
                    <div class="row d-none lang_form" id="{{$lang}}-form-meta-section">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="meta_title{{$lang}}" class="form-label fw-400 fs-13">
                                    {{ translate('meta_Title')  }} ({{ strtoupper($lang) }})
                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                          data-placement="right"
                                          title="{{ translate('add_the_products_title_name_taglines_etc_here') . ' ' . translate('this_title_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') . ' [ ' . translate('character_Limit') }} : 100 ]">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>

                                </label>
                                <input id="meta_title{{$lang}}" type="text" maxlength="100" name="meta_title[]"
                                       value="{{ $data_meta_title_translate[$lang]['value'] ?? '' }}"
                                       placeholder="{{ translate('meta_Title') }}"
                                       class="form-control">
                                <div class="d-flex justify-content-end">
                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="meta_description{{$lang}}" class="form-label mb-0 fw-400 fs-13">
                                    {{ translate('meta_Description') }} ({{ strtoupper($lang) }})

                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                          data-placement="right"
                                          title="{{ translate('write_a_short_description_of_the_InHouse_shops_product') . ' ' . translate('this_description_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') . ' [ ' . translate('character_Limit') }} : 160 ]">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>

                                </label>
                                <textarea rows="4" type="text" placeholder="{{ translate('meta_Description') }}" name="meta_description[]" maxlength="160"
                                          id="meta_description"
                                          class="form-control">{{ $data_meta_description_translate[$lang]['value'] ?? '' }}</textarea>
                                <div class="d-flex justify-content-end">
                                    <span class="text-body-light text-right d-block mt-1">0/160</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-400 fs-13">
                                {{ translate('meta_Title') }}
                                <span class="input-label-secondary text--title" data-toggle="tooltip"
                                      data-placement="right"
                                      title="{{ translate('add_the_products_title_name_taglines_etc_here') . ' ' . translate('this_title_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') . ' [ ' . translate('character_Limit') }} : 100 ]">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>

                            </label>
                            <input type="text" maxlength="100" name="meta_title"
                                   value=""
                                   placeholder="{{ translate('meta_Title') }}"
                                   class="form-control" id="meta_title">
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label fw-400 fs-13">
                                {{ translate('meta_Description') }}

                                <span class="input-label-secondary text--title" data-toggle="tooltip"
                                      data-placement="right"
                                      title="{{ translate('write_a_short_description_of_the_InHouse_shops_product') . ' ' . translate('this_description_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') . ' [ ' . translate('character_Limit') }} : 160 ]">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>

                            </label>
                            <textarea rows="4" type="text" placeholder="{{ translate('meta_Description') }}" name="meta_description" maxlength="160"
                                      id="meta_description"
                                      class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="card shadow--card-2 border-0 h-100 form-group">
            <div class="card-body d-flex flex-wrap gap-2 justify-content-between h-100">
                <div class="item d-flex flex-column gap-2 flex-grow-1">
                    <label class="form-check d-flex gap-2">
                        <input type="radio" name="meta_index" value="index"
                               {{ data_get($data,'meta_data.meta_index') == 'index' ? 'checked' : '' }}
                               class="form-check-input radio--input">
                        <span class="user-select-none form-check-label">{{ translate('Index') }}</span>
                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                              data-placement="right"
                              title="{{ translate('allow_search_engines_to_put_this_web_page_on_their_list_or_index_and_show_it_on_search_results.') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>


                    </label>

                    <label class="form-check d-flex gap-2">
                        <input type="checkbox" name="meta_no_follow" value="1"
                               {{ data_get($data,'meta_data.meta_no_follow') == '1' ? 'checked' : '' }}
                               class="input-no-index-sub-element form-check-input checkbox--input">
                        <span class="user-select-none form-check-label">{{ translate('No_Follow') }}</span>

                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                              data-placement="right"
                              title="{{ translate('instruct_search_engines_not_to_follow_links_from_this_web_page.') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>


                    </label>
                    <label class="form-check d-flex gap-2">
                        <input type="checkbox" name="meta_no_image_index" value="1"
                               {{ data_get($data,'meta_data.meta_no_image_index') == '1' ? 'checked' : '' }}
                               class="input-no-index-sub-element form-check-input checkbox--input">
                        <span
                            class="user-select-none form-check-label">{{ translate('No_Image_Index') }}</span>
                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                              data-placement="right"
                              title="{{ translate('prevents_images_from_being_listed_or_indexed_by_search_engines') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>


                    </label>
                </div>

                <div class="item d-flex flex-column gap-2 flex-grow-1">
                    <label class="form-check d-flex gap-2">
                        <input type="radio" name="meta_index" value="noindex"
                               {{ data_get($data,'meta_data.meta_index')== 'noindex' ? 'checked' : '' }}
                               class="action-input-no-index-event form-check-input radio--input">
                        <span class="user-select-none form-check-label">{{ translate('no_index') }}</span>

                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                              data-placement="right"
                              title="{{ translate('disallow_search_engines_to_put_this_web_page_on_their_list_or_index_and_do_not_show_it_on_search_results.') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>

                    </label>
                    <label class="form-check d-flex gap-2">
                        <input type="checkbox" name="meta_no_archive" value="1"
                               {{ data_get($data,'meta_data.meta_no_archive') == '1' ? 'checked' : '' }}
                               class="input-no-index-sub-element form-check-input checkbox--input">
                        <span class="user-select-none form-check-label">{{ translate('No_Archive') }}</span>
                        <span class="input-label-secondary text--title" data-bs-toggle="tooltip"
                              data-placement="right"
                              title="{{ translate('instruct_search_engines_not_to_display_this_webpages_cached_or_saved_version.') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>


                    </label>
                    <label class="form-check d-flex gap-2">
                        <input type="checkbox" name="meta_no_snippet" value="1"
                               {{ data_get($data,'meta_data.meta_no_snippet') == '1' ? 'checked' : '' }}
                               class="input-no-index-sub-element form-check-input checkbox--input">
                        <span class="user-select-none form-check-label">
                                        {{ translate('No_Snippet') }}
                                    </span>
                        <span class="input-label-secondary text--title" data-bs-toggle="tooltip"
                              data-placement="right"
                              title="{{ translate('instruct_search_engines_not_to_show_a_summary_or_snippet_of_this_webpages_content_in_search_results.') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>

                    </label>
                </div>
            </div>
        </div>

        <div class="card shadow--card-2 border-0 h-100 form-group">
            <div class="card-body d-flex flex-column gap-2 h-100">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                    <div class="item">
                        <label class="form-check d-flex gap-2">
                            <input type="checkbox" name="meta_max_snippet" value="1"
                                   {{ data_get($data,'meta_data.meta_max_snippet') == "1" ? 'checked' : '' }}
                                   class="form-check-input checkbox--input">
                            <span class="user-select-none form-check-label">
                                            {{ translate('max_Snippet') }}
                                        </span>
                            <span class="input-label-secondary text--title" data-bs-toggle="tooltip"
                                  data-placement="right"
                                  title="{{ translate('determine_the_maximum_length_of_a_snippet_or_preview_text_of_the_webpage.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>

                        </label>
                    </div>
                    <div class="item flex-grow-0 item_in box-input-type">
                        <input type="number" placeholder="-1" class="form-control h-35px py-0"
                               name="meta_max_snippet_value"
                               value="{{ data_get($data,'meta_data.meta_max_snippet_value') }}">
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                    <div class="item">
                        <label class="form-check d-flex gap-2 m-0">
                            <input type="checkbox" name="meta_max_video_preview" value="1"
                                   {{ data_get($data,'meta_data.meta_max_video_preview') == "1" ? 'checked' : '' }}
                                   class="form-check-input checkbox--input">
                            <span class="user-select-none form-check-label">
                                            {{ translate('max_Video_Preview') }}
                                        </span>
                            <span class="input-label-secondary text--title" data-bs-toggle="tooltip"
                                  data-placement="right"
                                  title="{{ translate('determine_the_maximum_duration_of_a_video_preview_that_search_engines_will_display') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>

                        </label>
                    </div>
                    <div class="item flex-grow-0 item_in box-input-type">
                        <input type="number" placeholder="-1" class="form-control h-35px py-0"
                               name="meta_max_video_preview_value"
                               value="{{ data_get($data,'meta_data.meta_max_video_preview_value') }}">
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                    <div class="item">
                        <label class="form-check d-flex gap-2 m-0">
                            <input type="checkbox" name="meta_max_image_preview" value="1"
                                   {{ data_get($data,'meta_data.meta_max_image_preview') == "1" ? 'checked' : '' }}
                                   class="form-check-input checkbox--input">
                            <span
                                class="user-select-none form-check-label">{{ translate('max_Image_Preview') }}</span>

                            <span class="input-label-secondary text--title" data-bs-toggle="tooltip"
                                  data-placement="right"
                                  title=" {{ translate('determine_the_maximum_size_or_dimensions_of_an_image_preview_that_search_engines_will_display.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>

                        </label>
                    </div>
                    <div class="item flex-grow-0 item_in box-input-type">
                        <div class="select-wrapper">
                            <select
                                class="form-select border seo-select-large seo-select-small js-select2-custom py-0 fs-12"
                                name="meta_max_image_preview_value">
                                <option
                                    {{ data_get($data,'meta_data.meta_max_image_preview_value') == 'large' ? 'selected' : '' }} value="large">{{ translate('large') }}</option>
                                <option
                                    {{ data_get($data,'meta_data.meta_max_image_preview_value') == 'medium' ? 'selected' : '' }} value="medium">{{ translate('medium') }}</option>
                                <option
                                    {{ data_get($data,'meta_data.meta_max_image_preview_value') == 'small' ? 'selected' : '' }} value="small">{{ translate('small') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


