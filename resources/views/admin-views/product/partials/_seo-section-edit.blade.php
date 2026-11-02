{{--
<div class="seo_wrapper">
    <div class="outline-wrapper">
        <div class="card rest-part bg-animate">
            <div class="card-header">
                <div class="d-flex gap-2">
                    <i class="fi fi-sr-user"></i>
                    <h3 class="mb-0">
                        {{ translate('seo_section') }}
                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ translate('add_meta_titles_descriptions_and_images_for_products') . ', ' . translate('this_will_help_more_people_to_find_them_on_search_engines_and_see_the_right_details_while_sharing_on_other_social_platforms') }}">
                            <i class="tio-info text-gray1 fs-16"></i>
                        </span>

                    </h3>
                </div>
                 @if (!Request::is('admin/campaign*') && isset($openai_config) && data_get($openai_config, 'status') == 1 )

                <button type="button" class="btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-2 seo_section_auto_fill" id="seo_section_auto_fill"
                    data-route="{{ route('admin.product.seo-section-auto-fill') }}" data-lang="en">
                    <div class="btn-svg-wrapper">
                        <img width="18" height="18" class=""
                            src="{{ dynamicAsset('assets/admin/img/svg/blink-right-small.svg') }}" alt="">
                    </div>
                    <span class="ai-text-animation d-none" role="status">
                        {{ translate('Just_a_second') }}
                    </span>
                    <span class="btn-text">{{ translate('Generate') }}</span>
                </button>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="form-label">
                                {{ translate('meta_Title') }}
                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('add_the_products_title_name_taglines_etc_here') . ' ' . translate('this_title_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') . ' [ ' . translate('character_Limit') }} : 100 ]">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>

                            </label>
                            <input type="text" maxlength="100" name="meta_title" value="{{ $product?->foodSeoData?->title }}" placeholder="{{ translate('meta_Title') }}"
                                class="form-control" id="meta_title">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                {{ translate('meta_Description') }}

                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('write_a_short_description_of_the_InHouse_shops_product') . ' ' . translate('this_description_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') . ' [ ' . translate('character_Limit') }} : 160 ]">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>

                            </label>
                            <textarea rows="8" type="text" placeholder="{{ translate('meta_Description') }}" name="meta_description" maxlength="160" id="meta_description"
                                class="form-control"> {{ $product?->foodSeoData?->description }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow--card-2 border-0 h-100">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center gap-3">
                                    <p class="mb-0">{{ translate('Meta Image') }}</p>
                                    <div class="image-box">
                                        <label for="image-input2"
                                            class="d-flex flex-column align-items-center justify-content-center h-100 cursor-pointer gap-2">

                                                 <img class="upload-icon initial-26"   src="{{ $product?->foodSeoData?->image_full_url ??dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}" alt="Upload Icon">
                                            <img src="#" alt="Preview Image" class="preview-image">
                                        </label>
                                        <button type="button" class="delete_image">
                                            <i class="tio-delete"></i>
                                        </button>
                                        <input type="file" id="image-input2"   name="meta_image" accept="image/*" hidden>
                                    </div>

                                    <p class="opacity-75 max-w220 mx-auto text-center">
                                        {{ translate('Image format - jpg png jpeg gif Image Size -maximum size 2 MB Image Ratio - 1:1') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row g-3 pt-4">
                    <div class="col-lg-6 col-xl-6">
                        <div class="card border h-100">
                            <div class="card-body d-flex flex-wrap gap-2 justify-content-between h-100">
                                <div class="item d-flex flex-column gap-2 flex-grow-1">
                                    <label class="form-check d-flex gap-2">
                                        <input type="radio" name="meta_index" value="index" {{ $product?->foodSeoData?->index != 'noindex' ? 'checked' : '' }}
                                            class="form-check-input radio--input">
                                        <span class="user-select-none form-check-label">{{ translate('Index') }}</span>
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('allow_search_engines_to_put_this_web_page_on_their_list_or_index_and_show_it_on_search_results.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>



                                    </label>

                                    <label class="form-check d-flex gap-2">
                                        <input type="checkbox" name="meta_no_follow" value="1" {{ $product?->foodSeoData?->no_follow == 'nofollow' ? 'checked' : '' }}
                                            class="input-no-index-sub-element form-check-input checkbox--input">
                                        <span class="user-select-none form-check-label">{{ translate('No_Follow') }}</span>

                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('instruct_search_engines_not_to_follow_links_from_this_web_page.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>



                                    </label>
                                    <label class="form-check d-flex gap-2">
                                        <input type="checkbox" name="meta_no_image_index" value="1" {{ $product?->foodSeoData?->no_image_index == 'noimageindex' ? 'checked' : '' }}
                                            class="input-no-index-sub-element form-check-input checkbox--input">
                                        <span
                                            class="user-select-none form-check-label">{{ translate('No_Image_Index') }}</span>
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('prevents_images_from_being_listed_or_indexed_by_search_engines') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>



                                    </label>
                                </div>
                                <div class="item d-flex flex-column gap-2 flex-grow-1">
                                    <label class="form-check d-flex gap-2">
                                        <input type="radio" name="meta_index" value="noindex" {{ $product?->foodSeoData?->index == 'noindex' ? 'checked' : '' }}
                                            class="action-input-no-index-event form-check-input radio--input">
                                        <span class="user-select-none form-check-label">{{ translate('no_index') }}</span>

                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('disallow_search_engines_to_put_this_web_page_on_their_list_or_index_and_do_not_show_it_on_search_results.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>

                                    </label>
                                    <label class="form-check d-flex gap-2">
                                        <input type="checkbox" name="meta_no_archive" value="1" {{ $product?->foodSeoData?->no_archive == 'noarchive' ? 'checked' : '' }}
                                            class="input-no-index-sub-element form-check-input checkbox--input">
                                        <span class="user-select-none form-check-label">{{ translate('No_Archive') }}</span>
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('instruct_search_engines_not_to_display_this_webpages_cached_or_saved_version.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>


                                    </label>
                                    <label class="form-check d-flex gap-2">
                                        <input type="checkbox" name="meta_no_snippet" value="1" {{ $product?->foodSeoData?->no_snippet == 1 ? 'checked' : '' }}
                                            class="input-no-index-sub-element form-check-input checkbox--input">
                                        <span class="user-select-none form-check-label">
                                            {{ translate('No_Snippet') }}
                                        </span>
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
                    <div class="col-lg-6 col-xl-6">
                        <div class="card border h-100">
                            <div class="card-body d-flex flex-column gap-2 h-100">
                                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                                    <div class="item">
                                        <label class="form-check d-flex gap-2">
                                            <input type="checkbox" name="meta_max_snippet" value="1" {{ $product?->foodSeoData?->max_snippet == 1 ? 'checked' : '' }}
                                                class="form-check-input checkbox--input">
                                            <span class="user-select-none form-check-label">
                                                {{ translate('max_Snippet') }}
                                            </span>
                                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('determine_the_maximum_length_of_a_snippet_or_preview_text_of_the_webpage.') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>

                                        </label>
                                    </div>
                                    <div class="item flex-grow-0 item_in">
                                        <input type="number" placeholder="-1" class="form-control h-30 py-0"
                                            name="meta_max_snippet_value" value="{{ $product?->foodSeoData?->max_snippet_value }}">
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                                    <div class="item">
                                        <label class="form-check d-flex gap-2 m-0">
                                            <input type="checkbox" name="meta_max_video_preview" value="1" {{ $product?->foodSeoData?->max_video_preview == 1 ? 'checked' : '' }}
                                                class="form-check-input checkbox--input">
                                            <span class="user-select-none form-check-label">
                                                {{ translate('max_Video_Preview') }}
                                            </span>
                                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('determine_the_maximum_duration_of_a_video_preview_that_search_engines_will_display') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>

                                        </label>
                                    </div>
                                    <div class="item flex-grow-0 item_in">
                                        <input type="number" placeholder="-1"  class="form-control h-30 py-0"
                                            name="meta_max_video_preview_value" value="{{ $product?->foodSeoData?->max_video_preview_value }}">
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                                    <div class="item">
                                        <label class="form-check d-flex gap-2 m-0">
                                            <input type="checkbox" name="meta_max_image_preview" value="1" {{ $product?->foodSeoData?->max_image_preview == 1 ? 'checked' : '' }}
                                                class="form-check-input checkbox--input">
                                            <span
                                                class="user-select-none form-check-label">{{ translate('max_Image_Preview') }}</span>

                                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title=" {{ translate('determine_the_maximum_size_or_dimensions_of_an_image_preview_that_search_engines_will_display.') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>

                                        </label>
                                    </div>
                                    <div class="item w-120 flex-grow-0">
                                        <div class="select-wrapper">
                                            <select class="form-select h-30 js-select2-custom py-0" name="meta_max_image_preview_value">
                                                <option {{ $product?->foodSeoData?->max_image_preview_value == 'large' ? 'selected' : '' }} value="large">{{ translate('large') }}</option>
                                                <option {{ $product?->foodSeoData?->max_image_preview_value == 'medium' ? 'selected' : '' }} value="medium">{{ translate('medium') }}</option>
                                                <option {{ $product?->foodSeoData?->max_image_preview_value == 'small' ? 'selected' : '' }} value="small">{{ translate('small') }}</option>
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
    </div>
</div> --}}
