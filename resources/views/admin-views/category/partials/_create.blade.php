<form action="{{ route('admin.category.store') }}" method="post" class="d-flex flex-column h-100"
      enctype="multipart/form-data">
    @csrf
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0">{{ translate('Add New Main Category') }}</h3>
            <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;
            </button>
        </div>
        <div class="custom-offcanvas-body p-20">
            <div class="bg--secondary rounded p-3 mb-20">
                <div class="bg-white rounded p-3 mb-20">
                    @if ($language)
                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs mb-4 border-0">
                                <li class="nav-item">
                                    <a class="nav-link lang_link1 active" href="#"
                                       id="default-link">{{ translate('messages.default') }}</a>
                                </li>
                                @foreach ($language as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link1" href="#"
                                           id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <input name="position" value="0" type="hidden">
                    @if ($language)
                        <div class="form-group lang_form1 mb-0" id="default-form1">
                            <label class="input-label"
                                   for="exampleFormControlInput1">{{ translate('Main Category Name') }}
                                ({{ translate('messages.default') }})
                                <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                      data-placement="right"
                                      data-original-title="{{ translate('messages.Required.') }}"> *
                                </span>
                            </label>
                            <input type="text" name="name[]" class="form-control"
                                   placeholder="{{ translate('messages.new_category') }}" maxlength="255" required>
                        </div>
                        <input type="hidden" name="lang[]" value="default">

                        @foreach ($language as $lang)
                            <div class="form-group d-none lang_form1 mb-0" id="{{ $lang }}-form1">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{ translate('Main Category Name') }}
                                    ({{ strtoupper($lang) }})
                                </label>
                                <input type="text" name="name[]" class="form-control"
                                       placeholder="{{ translate('messages.Type_Category_Name') }}" maxlength="191">
                            </div>
                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                        @endforeach
                    @else
                        <div class="form-group mb-0">
                            <label class="input-label"
                                   for="exampleFormControlInput1">{{ translate('Main Category Name') }}</label>
                            <input type="text" name="name[]" class="form-control"
                                   placeholder="{{ translate('messages.new_category') }}" maxlength="191" required>
                        </div>
                        <input type="hidden" name="lang[]" value="default">
                    @endif
                </div>

                <div class="bg-white rounded p-3">
      
                     <div class="mb-3">
                        <label class="input-label" for="">
                            {{ translate('messages.Priority') }}
                            <span class="form-label-secondary" data-toggle="tooltip"
                                    data-placement="right"
                                    title="{{ translate('messages.category_priority_help') }}">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <select name="priority" id="priority" class="form-control custom-select js-select2-custom">
                            <option value="0" {{ old('priority', '0') == '0' ? 'selected' : '' }}>{{ translate('messages.Normal') }}</option>
                            <option value="1" {{ old('priority') == '1' ? 'selected' : '' }}>{{ translate('messages.Medium') }}</option>
                            <option value="2" {{ old('priority') == '2' ? 'selected' : '' }}>{{ translate('messages.High') }}</option>
                        </select>
                    </div>
                    @if ($categoryWiseTax)
                        <div class="mb-3">
                            <span class="input-label">{{ translate('Select Tax Rate') }}
                                <span class="text-danger">*</span>
                            </span>
                            <select name="tax_ids[]" id="tax__rate" class="form-control multiple-select2" required
                                    multiple="multiple" placeholder="{{translate('--Select Tax Rate--')}}">
                                @foreach ($taxVats as $taxVat)
                                    <option value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                        ({{ $taxVat->tax_rate }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="bg--secondary rounded p-3">
                        <div class="d-flex flex-column align-items-center">
                            <div class="mb-20 text-center">
                                <h5 class="mb-1">
                                    {{ translate('Category image') }}
                                    <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                          data-placement="right"
                                          data-original-title="{{ translate('messages.Required.') }}"> *
                                </span>
                                </h5>

                                <p class="mb-0 fs-12 gray-dark">{{ translate('Upload Category image') }}</p>
                            </div>
                            <div class="upload-file mx-auto">
                                <input type="file" name="image" class="upload-file__input single_file_input"
                                       accept="{{IMAGE_EXTENSION}}" required>
                                <label class="upload-file__wrapper w-150 h-150px mx-auto ratio-1 m-0">
                                    <div class="upload-file-textbox text-center">
                                        <img width="22" class="svg"
                                             src="{{dynamicAsset('assets/admin/img/image-upload.png')}}"
                                             alt="img">
                                        <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                            <span class="text-info">{{translate('Click to upload')}}</span>
                                            <br>
                                            {{translate('Or drag and drop')}}
                                        </h6>
                                    </div>
                                    <img class="upload-file-img" loading="lazy" src="" data-default-src=""
                                         alt="" style="display: none;">
                                </label>
                                <div class="overlay">
                                    <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                        <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                            <i class="tio-invisible"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                            <i class="tio-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="fs-10 text-center mb-0 mt-20">
                                {{ translate(IMAGE_FORMAT.' Less Than '.MAX_FILE_SIZE.'MB') }} <span
                                    class="font-medium text-title">{{ translate('(1:1)')}}</span>
                            </p>
                        </div>
                    </div>
                </div>

            </div>
            @include('admin-views.category.partials._meta-section', ['data' => null])

        </div>
    </div>

    <div
        class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
        <button type="button"
                class="btn w-100 btn--secondary offcanvas-close h--40px">{{ translate('Cancel') }}</button>
        <button type="submit" class="btn w-100 btn--primary h--40px">{{ translate('Add') }}</button>
    </div>
</form>
