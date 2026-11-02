
<form action="{{ route('admin.react_landing_page.reactTestimonialUpdate', [$testimonial['id']]) }}" method="post"
    class="validate-form d-flex flex-column h-100" enctype="multipart/form-data">

    @csrf
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0">{{ translate('Edit_Testimonial') }}</h3>
            <button type="button"
                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                aria-label="Close">&times;</button>
        </div>
        <div class="custom-offcanvas-body p-20">
            {{-- <div class="bg--secondary rounded p-20 mb-20">
                <div class="mb-15">
                    <h4 class="mb-0">{{ translate('Availability') }}</h4>
                    <p class="fz-12px">{{ translate('If_you_turn_off_this_status_your_tax_calculation_will_effect') }}
                    </p>
                </div>
                <label class="border d-flex align-items-center bg-white-n justify-content-between rounded p-10px px-3">
                    {{ translate('Status') }}
                    <div class="toggle-switch ml-auto justify-content-end toggle-switch-sm" for="status">
                        <input type="checkbox" name="status" value="1"
                            {{ $testimonial['status'] ? 'checked' : '' }} class="toggle-switch-input" id="status">
                        <span class="toggle-switch-label">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </div>
                </label>
            </div> --}}




            <div class="bg--secondary rounded p-20 mb-20">

                @if ($language)
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
                @endif
                <div class="row">
                    <div class="col-12">
                        @if ($language)
                            <div class="form-group lang_form1" id="default-form1">
                                <div class="col-md-12">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ translate('messages.Reviewer Name') }}
                                        ({{ translate('messages.default') }})

                                        <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('messages.Required.') }}"> *
                                        </span>

                                    </label>
                                    <input required id="Reviewer_name" data-maxlength="50" type="text" name="name[]"
                                        class="form-control" value="{{ $testimonial?->getRawOriginal('name') }}"
                                        placeholder="{{ translate('Ex: John') }}">

                                    <div class="d-flex justify-content-end">
                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ translate('messages.Review') }}
                                        ({{ translate('messages.default') }})
                                        <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('messages.Required.') }}"> *
                                        </span>

                                    </label>

                                    <input required id="Reviewer_review" data-maxlength="100"
                                        value="{{ $testimonial?->getRawOriginal('review') }}" type="text"
                                        name="review[]" class="form-control" placeholder="{{ translate('Ex: John') }}">

                                    <div class="d-flex justify-content-end">
                                        <span class="text-body-light text-right d-block mt-1">0/100</span>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="lang[]" value="default">

                            @foreach ($language as $key => $lang)
                                <?php
                                if (count($testimonial['translations'])) {
                                    $translate = [];
                                    foreach ($testimonial['translations'] as $t) {
                                        if ($t->locale == $lang && $t->key == 'name') {
                                            $translate[$lang]['name'] = $t->value;
                                        }
                                        if ($t->locale == $lang && $t->key == 'review') {
                                            $translate[$lang]['review'] = $t->value;
                                        }
                                    }
                                }
                                ?>

                                <div class="form-group d-none lang_form1" id="{{ $lang }}-form1">

                                    <div class="col-md-12">

                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.Reviewer Name') }}
                                            ({{ strtoupper($lang) }})
                                        </label>
                                        <input type="text" name="name[]"
                                            value="{{ $translate[$lang]['name'] ?? '' }}" class="form-control"
                                            data-maxlength="50" placeholder="{{ translate('messages.Reviewer Name') }}"
                                            maxlength="191">
                                        <div class="d-flex justify-content-end">
                                            <span class="text-body-light text-right d-block mt-1">0/50</span>
                                        </div>
                                    </div>

                                    <div class="col-md-12">

                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('Review') }}
                                            ({{ strtoupper($lang) }})
                                        </label>
                                        <input type="text" name="review[]"
                                            value="{{ $translate[$lang]['review'] ?? '' }}" class="form-control"
                                            data-maxlength="100" placeholder="{{ translate('messages.Review') }}"
                                            maxlength="191">
                                        <div class="d-flex justify-content-end">
                                            <span class="text-body-light text-right d-block mt-1">0/100</span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                            @endforeach

                        @endif

                    </div>

                </div>

            </div>

            <div class="col-lg-12">
                <div class="bg-light2 d-flex align-items-center justify-content-center p-xl-20 p-4 rounded h-100">
                    <div class="card-body p-0">
                        <div>
                            <div class="mb-xxl-4 pb-1 mb-xl-4 mb-3 text-center">
                                <h5 class="mb-1">{{ translate('Reviewer Image') }}</h5>
                                <p class="mb-0 fs-12">{{ translate('Upload Reviewer Image') }}</p>
                            </div>

                            <div class="text-center">
                                                @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'image',
                                                'ratio' => '1:1',
                                                'isRequired' => $testimonial?->image ? false : true,
                                                'existingImage' =>$testimonial?->image_full_url ?? null,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                                'pixel'=>'200 x 230'
                                                ])
                                                </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div
        class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
        <button type="button"
            class="btn w-100 btn--secondary offcanvas-close h--40px">{{ translate('Cancel') }}</button>
        <button type="submit" class="btn w-100 btn--primary h--40px">{{ translate('Update') }}</button>
    </div>
</form>
