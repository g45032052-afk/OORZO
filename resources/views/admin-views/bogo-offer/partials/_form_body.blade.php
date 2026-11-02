@php($offer = $offer ?? null)
@php($quantity_locked = $quantity_locked ?? false)
@php($language=\App\Models\BusinessSetting::where('key','language')->first())
@php($language = $language->value ?? null)
@php($selected_types = $offer?->order_types ?? ['delivery', 'dine_in'])

<div class="card">
    <div class="card-body">
        <h5 class="mb-1">{{ $offer ? translate('messages.Update_BOGO_Offer') : translate('messages.Create_BOGO_Offer') }}</h5>
        <p class="opacity-75">{{translate('messages.Here_you_can_give_BOGO_offer_information')}}</p>

        <div class="row">
            <div class="col-md-8 mb-3">
                <div class="bg-light rounded p-3 h-100">
                    @if($language)
                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active" href="#" id="default-link">{{ translate('Default') }}</a>
                                </li>
                                @foreach(json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link" href="#" id="{{$lang}}-link">{{\App\CentralLogics\Helpers::get_language_name($lang).'('.strtoupper($lang).')'}}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="lang_form" id="default-form">
                            <div class="form-group">
                                <label class="input-label" for="default_title">
                                    {{translate('messages.title')}} ({{ translate('Default') }}) <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="{{translate('messages.The_offer_name_customers_will_see')}}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <input type="text" name="title[]" id="default_title" class="form-control h--45px" maxlength="100"
                                       data-counter="count_title_default" placeholder="{{ translate('messages.Type_Title') }}"
                                       value="{{ $offer?->getRawOriginal('title') }}">
                                <small class="d-block text-right opacity-75">
                                    <span id="count_title_default">{{ strlen($offer?->getRawOriginal('title') ?? '') }}</span>/100
                                </small>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            <div class="form-group mb-0">
                                <label class="input-label">
                                    {{translate('messages.description')}} ({{ translate('Default') }})
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="{{translate('messages.A_short_description_shown_with_the_offer')}}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <textarea name="description[]" class="form-control" maxlength="150" rows="2"
                                          data-counter="count_desc_default"
                                          placeholder="{{ translate('messages.Type_about_the_description') }}">{{ $offer?->getRawOriginal('description') }}</textarea>
                                <small class="d-block text-right opacity-75">
                                    <span id="count_desc_default">{{ strlen($offer?->getRawOriginal('description') ?? '') }}</span>/150
                                </small>
                            </div>
                        </div>

                        @foreach(json_decode($language) as $lang)
                            <?php
                                $translate = [];
                                if ($offer && count($offer['translations'])) {
                                    foreach ($offer['translations'] as $t) {
                                        if ($t->locale == $lang && $t->key == 'title') {
                                            $translate[$lang]['title'] = $t->value;
                                        }
                                        if ($t->locale == $lang && $t->key == 'description') {
                                            $translate[$lang]['description'] = $t->value;
                                        }
                                    }
                                }
                            ?>
                            <div class="d-none lang_form" id="{{$lang}}-form">
                                <div class="form-group">
                                    <label class="input-label" for="{{$lang}}_title">
                                        {{translate('messages.title')}} ({{strtoupper($lang)}})
                                        <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                              data-original-title="{{translate('messages.The_offer_name_customers_will_see')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="text" name="title[]" id="{{$lang}}_title" class="form-control h--45px" maxlength="100"
                                           data-counter="count_title_{{$lang}}" placeholder="{{ translate('messages.Type_Title') }}"
                                           value="{{ $translate[$lang]['title'] ?? '' }}">
                                    <small class="d-block text-right opacity-75">
                                        <span id="count_title_{{$lang}}">{{ strlen($translate[$lang]['title'] ?? '') }}</span>/100
                                    </small>
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                <div class="form-group mb-0">
                                    <label class="input-label">
                                        {{translate('messages.description')}} ({{strtoupper($lang)}})
                                        <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                              data-original-title="{{translate('messages.A_short_description_shown_with_the_offer')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <textarea name="description[]" class="form-control" maxlength="150" rows="2"
                                              data-counter="count_desc_{{$lang}}"
                                              placeholder="{{ translate('messages.Type_about_the_description') }}">{{ $translate[$lang]['description'] ?? '' }}</textarea>
                                    <small class="d-block text-right opacity-75">
                                        <span id="count_desc_{{$lang}}">{{ strlen($translate[$lang]['description'] ?? '') }}</span>/150
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div id="default-form">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.title')}} <span class="text-danger">*</span></label>
                                <input type="text" name="title[]" class="form-control h--45px" maxlength="100"
                                       data-counter="count_title_default" placeholder="{{ translate('messages.Type_Title') }}"
                                       value="{{ $offer?->getRawOriginal('title') }}">
                                <small class="d-block text-right opacity-75">
                                    <span id="count_title_default">{{ strlen($offer?->getRawOriginal('title') ?? '') }}</span>/100
                                </small>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            <div class="form-group mb-0">
                                <label class="input-label">{{translate('messages.description')}}</label>
                                <textarea name="description[]" class="form-control" maxlength="150" rows="2"
                                          data-counter="count_desc_default"
                                          placeholder="{{ translate('messages.Type_about_the_description') }}">{{ $offer?->getRawOriginal('description') }}</textarea>
                                <small class="d-block text-right opacity-75">
                                    <span id="count_desc_default">{{ strlen($offer?->getRawOriginal('description') ?? '') }}</span>/150
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="bg-light rounded p-3 h-100 text-center d-flex flex-column">
                    <h6 class="mb-1">{{translate('messages.BOGO_Offer_image')}}
                        <span class="text-danger">*</span>
                    </h6>
                    <p class="opacity-75 font-size-sm mb-3">{{translate('messages.Upload_your_BOGO_Offer_image')}}</p>

                    {{-- The same uploader the food form uses, so a picked file, its preview and
                         the size and format line all behave identically across the panel. Kept
                         required: an offer is created with an image and there is no route that
                         clears one, so the partial's remove button would promise a delete the
                         controller does not do. An existing image drops the required attribute,
                         which is what makes the image optional on update. --}}
                    @include('admin-views.partials._image-uploader', [
                        'id' => 'image-input',
                        'name' => 'image',
                        'ratio' => '3:1',
                        'isRequired' => true,
                        'existingImage' => $offer && $offer->image ? $offer->image_full_url : null,
                        // What Reset puts back: the image the offer is saved with.
                        'defaultImage' => $offer && $offer->image ? $offer->image_full_url : null,
                        'imageExtension' => IMAGE_EXTENSION,
                        'imageFormat' => IMAGE_FORMAT,
                        'maxSize' => MAX_FILE_SIZE,
                    ])
                </div>
            </div>
        </div>

        @if($quantity_locked)
            <div class="alert alert-soft-warning alert--note d-flex gap-2">
                <i class="tio-error"></i>
                <span>{{translate('messages.Buy_and_Get_quantity_cannot_be_changed_once_a_restaurant_has_joined_this_offer')}}</span>
            </div>
        @endif

        <div class="bg-light rounded p-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="input-label">
                            {{translate('messages.Buy_Food_Qty')}} <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="{{translate('messages.How_many_items_the_customer_must_buy_to_qualify')}}">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <input type="number" min="1" name="buy_qty" class="form-control h--45px" placeholder="{{ translate('Ex : 2') }}"
                               value="{{ $offer?->buy_qty }}" required {{$quantity_locked ? 'readonly' : ''}}>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="input-label">
                            {{translate('messages.Get_Food_Qty')}} <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="{{translate('messages.How_many_items_the_customer_receives_free')}}">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <input type="number" min="1" name="get_qty" class="form-control h--45px" placeholder="{{ translate('Ex : 1') }}"
                               value="{{ $offer?->get_qty }}" required {{$quantity_locked ? 'readonly' : ''}}>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="input-label">{{translate('messages.Start_Date_&_Time')}} <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_date" id="start_date" class="form-control h--45px"
                               placeholder="{{ translate('messages.Select_date_&_Time') }}"
                               value="{{ $offer?->start_date?->format('Y-m-d\TH:i') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="input-label">{{translate('messages.End_Date_&_Time')}} <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_date" id="end_date" class="form-control h--45px"
                               placeholder="{{ translate('messages.Select_date_&_Time') }}"
                               value="{{ $offer?->end_date?->format('Y-m-d\TH:i') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="input-label">
                            {{translate('messages.Usage_Limit_Total')}}
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="{{translate('messages.Total_times_this_offer_can_be_used_across_all_customers')}}">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <input type="number" min="1" name="usage_limit_total" class="form-control h--45px"
                               placeholder="{{ translate('Ex : 100') }}" value="{{ $offer?->usage_limit_total }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="input-label">
                            {{translate('messages.Usage_Limit_Per_Customer')}}
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="{{translate('messages.How_many_times_one_customer_can_use_this_offer')}}">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <input type="number" min="1" name="usage_limit_per_customer" class="form-control h--45px"
                               placeholder="{{ translate('Ex : 20') }}" value="{{ $offer?->usage_limit_per_customer }}">
                    </div>
                </div>
            </div>

            {{-- Hidden while order types are switched off - see BogoOffer::ORDER_TYPES_ENABLED.
                 The field is left whole, not deleted: turning the constant back on brings the
                 picker back with whatever each offer already had ticked. --}}
            @if(\App\Models\BogoOffer::orderTypesEnabled())
                <div class="form-group mb-0">
                    <label class="input-label">{{translate('messages.Select_Order_Type')}}</label>
                    <div class="row border rounded mx-0 py-3">
                        @foreach(['delivery' => 'Home_Delivery', 'take_away' => 'Take_Away', 'dine_in' => 'Dine_In'] as $value => $label)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="order_types[]" value="{{$value}}"
                                           id="order_type_{{$value}}" {{ in_array($value, $selected_types) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="order_type_{{$value}}">{{translate('messages.'.$label)}}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
