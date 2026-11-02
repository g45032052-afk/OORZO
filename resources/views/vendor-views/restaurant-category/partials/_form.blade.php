@php
    $isEdit = isset($restaurantCategory);
    $defaultName = $isEdit ? $restaurantCategory->getRawOriginal('name') : '';
    $translate = [];
    if ($isEdit && count($restaurantCategory['translations'])) {
        foreach ($restaurantCategory['translations'] as $translation) {
            if ($translation->key == 'name') {
                $translate[$translation->locale]['name'] = $translation->value;
            }
        }
    }
@endphp

<form action="{{ $isEdit ? route('vendor.restaurant-category.update', $restaurantCategory->id) : route('vendor.restaurant-category.store') }}"
    method="post"
    class="d-flex flex-column h-100"
    enctype="multipart/form-data">
    @csrf
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0">{{ $isEdit ? translate('Edit My Category') : translate('Add My Category') }}</h3>
            <button type="button"
                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                aria-label="Close">&times;</button>
        </div>
        <div class="custom-offcanvas-body p-20">
            <div class="alert alert-warning d-inline-flex align-items-start gap-2 text-left mx-auto py-2 px-3 mb-0"
                            style="max-width: 640px; background-color: #FFF8E5; border: 1px solid #FFE6A8;" role="alert">
                            <i class="tio-info text-warning mt-1" style="color: #F2A93B !important;"></i>
                            <div class="fs-12 text-body">
                                {{ translate('Once you create restaurant categories, you must add your foods to those categories. Without assigning foods, they will not appear on your restaurant details page. If you want to proceed with the main category, you can skip adding any restaurant categories.') }}
                            </div>
                        </div>
            <div class="bg--secondary rounded p-3 mb-20">
                <div class="bg-white rounded p-3 mb-20">
                    @if ($language)
                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs mb-4 border-0">
                                <li class="nav-item">
                                    <a class="nav-link lang_link1 active" href="#" id="default-link">{{ translate('messages.default') }}</a>
                                </li>
                                @foreach ($language as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link1" href="#" id="{{ $lang }}-link">
                                            {{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($language)
                        <div class="form-group lang_form1 mb-3" id="default-form1">
                            <label class="input-label">{{ translate('Category Name') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span></label>
                            <input type="text" name="name[]" class="form-control" value="{{ $defaultName }}"
                                placeholder="{{ translate('Type restaurant category name') }}" maxlength="255" required>
                        </div>
                        <input type="hidden" name="lang[]" value="default">
                        @foreach ($language as $lang)
                            <div class="form-group d-none lang_form1 mb-3" id="{{ $lang }}-form1">
                                <label class="input-label">{{ translate('Category Name') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="name[]" class="form-control"
                                    value="{{ $translate[$lang]['name'] ?? '' }}"
                                    placeholder="{{ translate('Type category name') }}" maxlength="191">
                            </div>
                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                        @endforeach
                    @else
                        <div class="form-group mb-3">
                            <label class="input-label">{{ translate('Category Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name[]" class="form-control" value="{{ $defaultName }}"
                                placeholder="{{ translate('Type category name') }}" maxlength="191" required>
                        </div>
                        <input type="hidden" name="lang[]" value="default">
                    @endif
                </div>

                <div class="bg-white rounded p-3">
                    <div class="mb-3">
                        <label class="input-label">{{ translate('messages.Priority') }}</label>
                        <select name="priority" class="form-control custom-select js-select2-custom1">
                            <option value="0" {{ old('priority', (string) ($isEdit ? $restaurantCategory->priority : 0)) == '0' ? 'selected' : '' }}>{{ translate('messages.Normal') }}</option>
                            <option value="1" {{ old('priority', (string) ($isEdit ? $restaurantCategory->priority : 0)) == '1' ? 'selected' : '' }}>{{ translate('messages.Medium') }}</option>
                            <option value="2" {{ old('priority', (string) ($isEdit ? $restaurantCategory->priority : 0)) == '2' ? 'selected' : '' }}>{{ translate('messages.High') }}</option>
                        </select>
                    </div>

                    <div class="bg--secondary rounded p-3">
                        <div class="d-flex flex-column align-items-center">
                            <div class="mb-20 text-center">
                                <h5 class="mb-1">{{ translate('Category image') }} <span class="text-danger">*</span></h5>
                                <p class="mb-0 fs-12 gray-dark">{{ $isEdit ? translate('Update Category image') : translate('Upload Category image') }}</p>
                            </div>
                            <div class="upload-file mx-auto">
                                <input type="file" name="image" class="upload-file__input single_file_input"
                                    accept="{{ IMAGE_EXTENSION }}" {{ $isEdit && $restaurantCategory->image ? '' : 'required' }}>
                                <label class="upload-file__wrapper w-150 h-150px mx-auto ratio-1 m-0">
                                    <div class="upload-file-textbox text-center">
                                        <img width="22" class="svg"
                                            src="{{ dynamicAsset('assets/admin/img/image-upload.png') }}"
                                            alt="img">
                                        <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                            <span class="text-info">{{ translate('Click to upload') }}</span>
                                            <br>
                                            {{ translate('Or drag and drop') }}
                                        </h6>
                                    </div>
                                    <img class="upload-file-img" loading="lazy"
                                        src="{{ $isEdit ? $restaurantCategory->image_full_url : '' }}"
                                        data-default-src="{{ $isEdit ? $restaurantCategory->image_full_url : '' }}"
                                        alt=""
                                        style="{{ $isEdit && $restaurantCategory->image ? '' : 'display: none;' }}">
                                </label>
                                <div class="overlay">
                                    <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                        <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                            <i class="tio-invisible"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                            <i class="tio-edit"></i>
                                        </button>
                                        @if($isEdit)
                                    <input type="hidden" name="image_remove" id="image_remove" value="0">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <p class="fs-10 text-center mb-0 mt-20">
                                {{ translate(IMAGE_FORMAT . ' Less Than ' . MAX_FILE_SIZE . 'MB') }}
                                <span class="font-medium text-title">{{ translate('(1:1)') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
        <button type="button" class="btn w-100 btn--secondary offcanvas-close h--40px">{{ translate('Cancel') }}</button>
        <button type="submit" class="btn w-100 btn--primary h--40px">{{ $isEdit ? translate('Update') : translate('Add') }}</button>
    </div>
</form>
