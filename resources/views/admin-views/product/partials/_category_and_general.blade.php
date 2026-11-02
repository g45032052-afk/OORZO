


  <div class="col-lg-12">
      <div class="general_wrapper">
            <div class="outline-wrapper">
                <div class="card shadow--card-2 border-0 bg-animate">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span class="card-header-icon mr-2">
                                <i class="tio-dashboard-outlined"></i>
                            </span>
                            <span> {{ translate('Restaurants_&_Category_Info') }}</span>
                        </h5>

                      @if (isset($openai_config) && data_get($openai_config, 'status') == 1)
                      <button type="button" class="btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-2 general_setup_auto_fill" id="general_setup_auto_fill"
                          data-route="{{ route('admin.product.general-setup-auto-fill') }}" data-restaurant-id="" data-lang="en">
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
                        <div class="row g-2">
                              @php($column = 4)
                            @if (Auth::guard('admin')->check())
                                <div class="col-sm-6 col-lg-3">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="exampleFormControlSelect1">{{ translate('messages.restaurant') }} <span
                                                class="form-label-secondary text-danger" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('messages.Required.') }}"> *
                                            </span><span class="input-label-secondary"></span></label>
                                        <select name="restaurant_id" id="restaurant_id"
                                            data-placeholder="{{ translate('messages.select_restaurant') }}"
                                            class="js-data-example-ajax form-control">
                                            @if (isset($product->restaurant))
                                                <option value="{{ $product->restaurant_id }}" selected="selected">
                                                    {{ $product->restaurant->name }}</option>

                                            @elseif (isset($restaurant))
                                                    <option value="{{ $restaurant->id }}" data-verified="{{ (int) $restaurant->verified_seller }}" selected="selected">
                                                        {{ $restaurant->name }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                @php($column = 3)
                            @endif


                            <div class="col-sm-6 col-lg-{{ $column }}">
                                <div class="form-group mb-0">
                                    <label class="input-label"
                                        for="exampleFormControlSelect1">{{ translate('messages.category') }}<span
                                            class="form-label-secondary text-danger" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Required.') }}"> *
                                        </span></label>
                                    <select name="category_id" id="category_id"
                                     @if (!Auth::guard('admin')->check())
                                      data-url="{{url('/')}}/restaurant-panel/food/get-categories?parent_id=" data-id="sub-categories"
                                    @endif
                                        class="form-control js-select2-custom get-request">
                                        <option value="" selected disabled>
                                            {{ translate('Select_Category') }}</option>
                                        @foreach ($categories as $category)
                                            <option
                                                {{ isset($product) && $product_category[0]->id == $category['id'] ? 'selected' : '' }}
                                                value="{{ $category['id'] }}">{{ $category['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-{{ $column }}">
                                <div class="form-group mb-0">
                                    <label class="input-label"
                                        for="exampleFormControlSelect1">{{ translate('messages.sub_category') }} </label>
                                    <select name="sub_category_id" id="sub-categories"
                                        data-id="{{ isset($product) && count($product_category) >= 2 ? $product_category[1]->id : '' }}"
                                        class="form-control js-select2-custom">
                                        <option value="" selected disabled>
                                            {{ translate('Select_Sub_Category') }}</option>
                                    </select>
                                </div>
                            </div>
                            @if(\App\CentralLogics\Helpers::restaurantCategoryStatus() && (Auth::guard('admin')->check() || \App\CentralLogics\Helpers::hasAnyRestaurantCategory()))
                                <div class="col-sm-6 col-lg-{{ $column }}">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="restaurant_category_id">
                                            {{ Auth::guard('admin')->check() ? translate('messages.Restaurant_Category') : translate('My Category') }}
                                            <span class="text-danger restaurant-category-required-mark"
                                                style="{{ \App\CentralLogics\Helpers::hasAnyRestaurantCategory() ? '' : 'display: none;' }}">*</span>
                                        </label>
                                        <select name="restaurant_category_id" id="restaurant_category_id"
                                            data-id="{{ $product->restaurant_category_id ?? '' }}"
                                            class="form-control js-select2-custom"
                                            {{ \App\CentralLogics\Helpers::hasAnyRestaurantCategory() ? 'required' : '' }}>
                                            <option value="">{{ Auth::guard('admin')->check() ? translate('Select_Restaurant_Category') : translate('Select My Category') }}</option>
                                        </select>
                                    </div>
                                </div>
                            @endif
                            <div class="col-sm-6 col-lg-{{ $column }}">
                                <div class="form-group mb-0">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ translate('messages.food_type') }}<span
                                            class="form-label-secondary text-danger" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Required.') }}"> *
                                        </span></label>
                                    <select name="veg" id="veg" class="form-control js-select2-custom">
                                        <option value="" selected disabled>
                                            {{ translate('Select_Preferences') }}</option>
                                        <option {{ isset($product) && $product->veg == 1 ? 'selected' : '' }} value="1">
                                            {{ translate('messages.veg') }}</option>
                                        <option {{ isset($product) && $product->veg == 0 ? 'selected' : '' }} value="0">
                                            {{ translate('messages.non_veg') }}</option>
                                    </select>
                                </div>
                            </div>

                            @php($product_nutritions = isset($product) ? $product->nutritions->pluck('id') : null)
                            @php($product_allergies = isset($product) ? $product->allergies->pluck('id') : null)


                            <div class="col-sm-6 col-lg-6" id="nutrition">
                                <label class="input-label" for="sub-categories">
                                    {{ translate('Nutrition') }}
                                    <span class="input-label-secondary"
                                        title="{{ translate('Specify the necessary keywords relating to energy values for the item and type this content & press enter.') }}"
                                        data-toggle="tooltip">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <select name="nutritions[]" class="form-control multiple-select2" id="nutritions_input"
                                    data-placeholder="{{ translate('messages.Type your content and  press enter') }}" multiple>
                                    @php($nutritions = \App\Models\Nutrition::select(['id','nutrition'])->get() ?? [])

                                    @foreach ($nutritions as $nutrition)
                                        <option
                                            {{ $product_nutritions && $product_nutritions->contains($nutrition->id) ? 'selected' : '' }}
                                            value="{{ $nutrition->nutrition }}">{{ $nutrition->nutrition }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-sm-6 col-lg-6" id="allergy">
                                <label class="input-label" for="sub-categories">
                                    {{ translate('Allegren Ingredients') }}
                                    <span class="input-label-secondary"
                                        title="{{ translate('Specify the ingredients of the item which can make a reaction as an allergen and type this content & press enter.') }}"
                                        data-toggle="tooltip">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <select name="allergies[]" class="form-control multiple-select2" id="allergy_input"
                                    data-placeholder="{{ translate('messages.Type your content and  press enter') }}" multiple>
                                    @php($allergies = \App\Models\Allergy::select(['id','allergy'])->get() ?? [])

                                    @foreach ($allergies as $allergy)
                                        <option
                                            {{ $product_allergies && $product_allergies->contains($allergy->id) ? 'selected' : '' }}
                                            value="{{ $allergy->allergy }}">{{ $allergy->allergy }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-sm-6 col-lg-3" id="halal">
                                <div class="form-check mb-0 p-4">
                                    <input class="form-check-input" name="is_halal" type="checkbox" value="1"
                                        id="is_halal" {{ isset($product) && $product?->is_halal == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_halal">
                                        {{ translate('messages.Is_It_Halal') }}
                                    </label>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
      </div>
  </div>

  @if (Auth::guard('admin')->check())
      {{-- Addons belong to a restaurant, so the field stays disabled until one is picked. --}}
      @php($addon_restaurant_selected = isset($product->restaurant_id) || isset($restaurant))
      <div class="col-lg-6" id="addon_section">
        <div class="general_wrapper">
            <div class="outline-wrapper">
                <div class="card shadow--card-2 border-0 bg-animate">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span class="card-header-icon mr-2">
                                <i class="tio-dashboard-outlined"></i>
                            </span>
                            <span>{{ translate('messages.addon') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <label class="input-label" for="exampleFormControlSelect1">{{ translate('Select_Add-on') }}<span
                                class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.The_selected_addon’s_will_be_displayed_in_this_food_details') }}"><img
                                    src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}"
                                    alt="{{ translate('messages.The_selected_addon’s_will_be_displayed_in_this_food_details') }}"></span></label>
                        {{-- Filled by AJAX once a restaurant is picked. --}}
                        <select name="addon_ids[]" class="form-control border js-select2-custom" multiple="multiple"
                            id="add_on" data-placeholder="{{ translate('Select_Add-on') }}"
                            data-search-placeholder="{{ translate('messages.search_addon') }}"
                            {{ $addon_restaurant_selected ? '' : 'disabled' }}>

                        </select>
                        {{-- No d-flex here: bootstrap declares .d-flex after .d-none, so the two
                             together would keep the hint on screen once a restaurant is picked. --}}
                        <div class="fs-12 text-muted mt-2 {{ $addon_restaurant_selected ? 'd-none' : '' }}"
                            id="addon_hint">
                            <i class="tio-info text-gray1 fs-14"></i>
                            <span>{{ translate('messages.Make_sure_you_have_selected_a_restaurant_first_!') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
  @else
      <div class="col-lg-6">
        <div class="general_wrapper">
            <div class="outline-wrapper">
                <div class="card shadow--card-2 border-0 bg-animate">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span class="card-header-icon mr-2">
                                <i class="tio-dashboard-outlined"></i>
                            </span>
                            <span>{{ translate('messages.addon') }}</span>
                        </h5>
                    </div>
                    <div class="card-body pickup-zone-tag">
                        <label class="input-label" for="exampleFormControlSelect1">{{ translate('Select Add-on') }}</label>
                        <select name="addon_ids[]" class="form-control border multiple-select2" multiple="multiple"
                            id="add_on">
                            @foreach (\App\Models\AddOn::where('restaurant_id', \App\CentralLogics\Helpers::get_restaurant_id())->orderBy('name')->get(['id', 'name']) as $addon)
                                <option {{ isset($product)&& in_array($addon->id,json_decode($product['add_ons'],true))?'selected':''}}  value="{{ $addon['id'] }}">{{ $addon['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
      </div>

  @endif

  <div class="col-lg-6">
    <div class="general_wrapper">
        <div class="outline-wrapper">
            <div class="card shadow--card-2 border-0 bg-animate">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon mr-2"><i class="tio-date-range"></i></span>
                        <span>{{ translate('messages.Availability') }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <div class="form-group mb-0">
                                <label class="input-label"
                                    for="exampleFormControlInput1">{{ translate('messages.available_time_starts') }}<span
                                        class="form-label-secondary text-danger" data-toggle="tooltip"
                                        data-placement="right" data-original-title="{{ translate('messages.Required.') }}">
                                        *
                                    </span></label>
                                <input type="time" name="available_time_starts"
                                    value="{{ isset($product) ? $product?->available_time_starts : old('available_time_starts') }}"
                                    class="form-control" id="available_time_starts"
                                    placeholder="{{ translate('messages.Ex:_10:30_am') }} " required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-0">
                                <label class="input-label"
                                    for="exampleFormControlInput1">{{ translate('messages.available_time_ends') }}<span
                                        class="form-label-secondary text-danger" data-toggle="tooltip"
                                        data-placement="right" data-original-title="{{ translate('messages.Required.') }}">
                                        *
                                    </span></label>
                                <input type="time" name="available_time_ends" class="form-control"
                                    value="{{ isset($product) ? $product?->available_time_ends : old('available_time_ends') }}"
                                    id="available_time_ends" placeholder="5:45 pm" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <div class="col-lg-12">
    <div class="general_wrapper">
        <div class="outline-wrapper">
            <div class="card shadow--card-2 border-0 bg-animate">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon mr-2"><i class="tio-label"></i></span>
                        <span>{{ translate('Search_Tags') }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <input type="text" class="form-control" id="tags" name="tags"
                        @if (isset($product)) value="@foreach ($product->tags as $c) {{ $c->tag . ',' }} @endforeach" @endif
                        placeholder="Enter tags" data-role="tagsinput">
                </div>
            </div>
        </div>
    </div>
  </div>
