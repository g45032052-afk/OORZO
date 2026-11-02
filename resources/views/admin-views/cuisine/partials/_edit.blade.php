<form action="{{ route('admin.cuisine.update', [$cuisine['id']]) }}" method="post" class="d-flex flex-column h-100"
      enctype="multipart/form-data">
    @csrf
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0">{{ translate('Edit_Cuisine') }}</h3>
            <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;
            </button>
        </div>
        <div class="custom-offcanvas-body p-20">
            <div class="bg--secondary rounded pt-2 px-3 mb-20 pb-3">
                @if ($language)
                    <div class="js-nav-scroller hs-nav-scroller-horizontal">
                        <ul class="nav nav-tabs mb-4 border-0">
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
                    </div>
                @endif
                <div class="row">
                    <div class="col-12">
                        @if ($language)
                            <div class="form-group lang_form" id="default-form">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{ translate('messages.Cuisine_Name') }}
                                    ({{ translate('messages.default') }})
                                    <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                          data-placement="right"
                                          data-original-title="{{ translate('messages.Required.') }}"> *
                                    </span>

                                </label>
                                <input type="text" name="name[]"
                                       value="{{ $cuisine?->getRawOriginal('name') }}" class="form-control"
                                       placeholder="{{ translate('messages.new_cuisine') }}" maxlength="255">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            @foreach ($language as $key => $lang)
                                    <?php
                                    if (count($cuisine['translations'])) {
                                        $translate = [];
                                        foreach ($cuisine['translations'] as $t) {
                                            if ($t->locale == $lang && $t->key == 'name') {
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>

                                <div class="form-group d-none lang_form" id="{{ $lang }}-form">
                                    <label class="input-label"
                                           for="exampleFormControlInput1">{{ translate('messages.Cuisine_Name') }}
                                        ({{ strtoupper($lang) }})
                                    </label>
                                    <input type="text" name="name[]" value="{{ $translate[$lang]['name'] ?? '' }}"
                                           class="form-control"
                                           placeholder="{{ translate('messages.Cuisine_Name') }}" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                            @endforeach
                        @else
                            <div class="form-group">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{ translate('messages.Cuisine_Name') }}</label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="{{ translate('messages.new_cuisine') }}"
                                       value="{{ $cuisine?->getRawOriginal('name') }}" maxlength="191">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                        @endif

                    </div>

                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex flex-column align-items-center gap-3">
                            <div class="d-flex flex-column align-items-center">
                                <div class="mb-20 text-center">
                                    <h5 class="mb-1">
                                        {{ translate('Cuisine image') }}
                                    </h5>
                                    <p class="mb-0 fs-12 gray-dark">{{ translate('Update Cuisine image') }}</p>
                                </div>
                                <div class="text-center">
                                @include('admin-views.partials._image-uploader', [
                                            'id' => 'image-input',
                                            'name' => 'image',
                                            'ratio' => '1:1',
                                            'isRequired' => $cuisine['image_full_url'] ? false : true,
                                            'existingImage' => $cuisine['image_full_url'] ?? null,
                                            'imageExtension' => IMAGE_EXTENSION,
                                            'imageFormat' => IMAGE_FORMAT,
                                            'maxSize' => MAX_FILE_SIZE,
                                        ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin-views.category.partials._meta-section', ['data' => $cuisine])

        </div>
    </div>
    <div
        class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
        <button type="button"
                class="btn w-100 btn--secondary offcanvas-close h--40px">{{ translate('Cancel') }}</button>
        <button type="submit" class="btn w-100 btn--primary h--40px">{{ translate('Update') }}</button>
    </div>
</form>


