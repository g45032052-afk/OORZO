@extends('layouts.admin.app')

@section('title', translate('priority_setup'))


@section('content')
<div class="content" id="priority_setup">
    <form method="post" action="{{ route('admin.business-settings.update-priority') }}">
        @csrf
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-start">
                    <h1 class="mb-0" id="priority_setup_purpose">{{ translate('messages.business_setup') }}</h1>
                    <div class="d-flex flex-wrap justify-content-end align-items-center flex-grow-1">
                        <div class="blinkings active">
                            <i class="tio-info text-gray1 fs-16"></i>
                            <div class="business-notes"id="priority_setup_benefit">
                                <h6><img src="{{dynamicAsset('assets/admin/img/notes.png')}}" alt="">
                                    {{translate('Note')}}</h6>
                                <div>
                                    {{translate('Don’t_forget_to_click_the_respective_‘Save_Information’_and_‘Submit’_buttons_below_to_save_changes')}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('admin-views.business-settings.partials.nav-menu')
            </div>
            <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning mb-20" style="--bs-bg-opacity: 0.1;">
                <span class="text-info lh-1 fs-14">
                    <img src="{{dynamicAsset('assets/admin/img/info.png')}}" class="svg" alt="">
                </span>
                <span>
                    {{ translate('messages.After change any setup in this page must click the') }}
                    <span class="font-semibold">{{ translate('messages.Save Information') }} </span>
                    {{ translate('messages.button, otherwise changes are not work.') }}
                </span>
            </div>

            <div class="card card-body mb-20" id="sorting_options">
                <div class="mb-20">
                    <h4 class="mb-1" id="category_list">{{translate('Category_List')}}</h4>
                    <p class="m-0 fs-12">
                        {{ translate('The_Food_Category_list_groups_similar_items_together_arranged_with_the_latest_category_first_and_in_alphabetical_order.') }}
                    </p>
                </div>
                @php($category_list_default_status = \App\Models\BusinessSetting::where('key', 'category_list_default_status')->first()?->value ?? 1  )
                <div class="sorting-card bg-light rounded-10 p-12 p-xxl-20">
                    <div
                        class="bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="category_list_default_status" value="1" {{ $category_list_default_status == '1' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use default sorting list') }}
                                </span>
                            </label>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="category_list_default_status" value="0" {{ $category_list_default_status == '0' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use custom sorting list') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="inner-collapse-div">
                        <div class="">
                            @php($category_list_sort_by_general = \App\Models\PriorityList::where('name', 'category_list_sort_by_general')->where('type', 'general')->first()?->value ?? '' )
                            <div class="border bg-white rounded p-3 d-flex flex-column gap-2 fs-14 mt-3">
                                <label class="form-check form--check pb-2">
                                    <input class="form-check-input" type="radio" name="category_list_sort_by_general"
                                        value="latest" {{ $category_list_sort_by_general == 'latest' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by latest created')}}
                                    </span>
                                </label>
                                <label class="form-check form--check pb-2">
                                    <input class="form-check-input" type="radio" name="category_list_sort_by_general"
                                        value="oldest" {{ $category_list_sort_by_general == 'oldest' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by first created')}}
                                    </span>
                                </label>
                                <label class="form-check form--check pb-2">
                                    <input class="form-check-input" type="radio" name="category_list_sort_by_general"
                                        value="order_count" {{ $category_list_sort_by_general == 'order_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by orders')}}
                                    </span>
                                </label>
                                <label class="form-check form--check pb-2">
                                    <input class="form-check-input" type="radio" name="category_list_sort_by_general"
                                        value="a_to_z" {{ $category_list_sort_by_general == 'a_to_z' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (A to Z)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check pb-2">
                                    <input class="form-check-input" type="radio" name="category_list_sort_by_general"
                                        value="z_to_a" {{ $category_list_sort_by_general == 'z_to_a' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (Z to A)')}}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mt-20" style="--bs-bg-opacity: 0.1;">
                    <span class="text-info lh-1 fs-14">
                        <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                    </span>
                    <span>
                        {{ translate('messages.To manage all your categories & subcategories, visit these pages') }}
                        <a href="{{ route('admin.category.add') }}" class="font-semibold text-underline">
                            {{ translate('messages.Category') }}
                        </a>
                        &
                        <a href="{{ route('admin.category.add-sub-category') }}" class="font-semibold text-underline">
                            {{ translate('messages.Sub-category') }}
                        </a>
                    </span>
                </div>
            </div>

            <div class="card card-body mb-20">
                <div class="mb-20">
                    <h4 class="mb-1">{{translate('Cuisine_List')}}</h4>
                    <p class="m-0 fs-12">
                        {{ translate('Cuisines_are_lists_of_the_foods_people_like,_organize__by_putting_the_newest_ones_at_the_top_and_arranging_everything_alphabetically') }}
                    </p>
                </div>
                @php($cuisine_list_default_status = \App\Models\BusinessSetting::where('key', 'cuisine_list_default_status')->first()?->value ?? 1  )
                <div class="sorting-card bg-light rounded-10 p-12 p-xxl-20">
                    <div
                        class="bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="cuisine_list_default_status" value="1" {{ $cuisine_list_default_status == '1' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use default sorting list') }}
                                </span>
                            </label>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="cuisine_list_default_status" value="0" {{ $cuisine_list_default_status == '0' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use custom sorting list') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="inner-collapse-div">
                        <div class="">
                            @php($cuisine_list_sort_by_general = \App\Models\PriorityList::where('name', 'cuisine_list_sort_by_general')->where('type', 'general')->first()?->value ?? '' )
                            <div class="border bg-white rounded p-3 d-flex flex-column gap-2 fs-14 mt-3">
                                <label class="form-check form--check pb-2">
                                    <input class="form-check-input" type="radio" name="cuisine_list_sort_by_general"
                                        value="latest" {{ $cuisine_list_sort_by_general == 'latest' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by latest created')}}
                                    </span>
                                </label>
                                <label class="form-check form--check pb-2">
                                    <input class="form-check-input" type="radio" name="cuisine_list_sort_by_general"
                                        value="oldest" {{ $cuisine_list_sort_by_general == 'oldest' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by first created')}}
                                    </span>
                                </label>
                                <label class="form-check form--check pb-2">
                                    <input class="form-check-input" type="radio" name="cuisine_list_sort_by_general"
                                        value="restaurant_count" {{ $cuisine_list_sort_by_general == 'restaurant_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Total Restaurants')}}
                                    </span>
                                </label>
                                <label class="form-check form--check pb-2">
                                    <input class="form-check-input" type="radio" name="cuisine_list_sort_by_general"
                                        value="a_to_z" {{ $cuisine_list_sort_by_general == 'a_to_z' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (A to Z)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check pb-2">
                                    <input class="form-check-input" type="radio" name="cuisine_list_sort_by_general"
                                        value="z_to_a" {{ $cuisine_list_sort_by_general == 'z_to_a' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (Z to A)')}}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-body mb-20">
                <div class="mb-20">
                    <h4 class="mb-1" id="popular_food_nearby">{{translate('Popular Foods Nearby')}}</h4>
                    <p class="m-0 fs-12">
                        {{ translate('Popular food Nearby means the food items list  which are mostly ordered by the customers and have good reviews & ratings') }}
                    </p>
                </div>
                @php($popular_food_default_status = \App\Models\BusinessSetting::where('key', 'popular_food_default_status')->first())
                @php($popular_food_default_status = $popular_food_default_status ? $popular_food_default_status->value : 1)
                <div class="sorting-card bg-light rounded-10 p-12 p-xxl-20">
                    <div
                        class="bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="popular_food_default_status" value="1" {{ $popular_food_default_status == '1' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use default sorting list') }}
                                </span>
                            </label>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="popular_food_default_status" value="0" {{ $popular_food_default_status == '0' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use custom sorting list') }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="inner-collapse-div">
                        <div class="">
                            @php($popular_food_sort_by_general = \App\Models\PriorityList::where('name', 'popular_food_sort_by_general')->where('type', 'general')->first())
                            @php($popular_food_sort_by_general = $popular_food_sort_by_general ? $popular_food_sort_by_general->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_general"
                                        value="order_count" {{ $popular_food_sort_by_general == 'order_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by orders')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_general"
                                        value="review_count" {{ $popular_food_sort_by_general == 'review_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by reviews count')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_general"
                                        value="rating" {{ $popular_food_sort_by_general == 'rating' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by ratings')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_general"
                                        value="nearest_first" {{ $popular_food_sort_by_general == 'nearest_first' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show nearest food first')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_general"
                                        value="a_to_z" {{ $popular_food_sort_by_general == 'a_to_z' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (A to Z)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_general"
                                        value="z_to_a" {{ $popular_food_sort_by_general == 'z_to_a' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (Z to A)')}}
                                    </span>
                                </label>
                            </div>
                            @php($popular_food_sort_by_unavailable = \App\Models\PriorityList::where('name', 'popular_food_sort_by_unavailable')->where('type', 'unavailable')->first())
                            @php($popular_food_sort_by_unavailable = $popular_food_sort_by_unavailable ? $popular_food_sort_by_unavailable->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_unavailable"
                                        value="last" {{ $popular_food_sort_by_unavailable == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show unavailable foods in the last (both food & restaurant are unavailable)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_unavailable"
                                        value="remove" {{ $popular_food_sort_by_unavailable == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove unavailable foods from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_unavailable"
                                        value="none" {{ $popular_food_sort_by_unavailable == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                            @php($popular_food_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'popular_food_sort_by_temp_closed')->where('type', 'temp_closed')->first())
                            @php($popular_food_sort_by_temp_closed = $popular_food_sort_by_temp_closed ? $popular_food_sort_by_temp_closed->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_temp_closed"
                                        value="last" {{ $popular_food_sort_by_temp_closed == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show food in the last if restaurant is temporarily off')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_temp_closed"
                                        value="remove" {{ $popular_food_sort_by_temp_closed == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove food from the list if restaurant is temporarily off')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="popular_food_sort_by_temp_closed"
                                        value="none" {{ $popular_food_sort_by_temp_closed == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mt-20" style="--bs-bg-opacity: 0.1;">
                    <span class="text-info lh-1 fs-14">
                        <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                    </span>
                    <span>
                        {{ translate('messages.To manage all foods, visit this page') }}
                        <a href="{{ route('admin.food.list') }}" class="font-semibold text-underline">
                            {{ translate('messages.Food') }}
                        </a>
                    </span>
                </div>
            </div>
            <div class="card card-body mb-20">
                <div class="mb-20">
                    <h4 class="mb-1">{{translate('Popular Restaurant')}}</h4>
                    <p class="m-0 fs-12">
                        {{ translate('Popular Restaurants is the list of customer choices in which customer ordered items most and also highly rated with good reviews') }}
                    </p>
                </div>
                @php($popular_restaurant_default_status = \App\Models\BusinessSetting::where('key', 'popular_restaurant_default_status')->first())
                @php($popular_restaurant_default_status = $popular_restaurant_default_status ? $popular_restaurant_default_status->value : 1)
                <div class="sorting-card bg-light rounded-10 p-12 p-xxl-20">
                    <div
                        class="bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="popular_restaurant_default_status" value="1" {{ $popular_restaurant_default_status == '1' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use default sorting list') }}
                                </span>
                            </label>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="popular_restaurant_default_status" value="0" {{ $popular_restaurant_default_status == '0' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use custom sorting list') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="inner-collapse-div">
                        <div>
                            @php($popular_restaurant_sort_by_general = \App\Models\PriorityList::where('name', 'popular_restaurant_sort_by_general')->where('type', 'general')->first())
                            @php($popular_restaurant_sort_by_general = $popular_restaurant_sort_by_general ? $popular_restaurant_sort_by_general->value : '')
                            <div class="border bg-white rounded p-3 d-flex flex-column gap-2 fs-14 mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="popular_restaurant_sort_by_general" value="order_count" {{ $popular_restaurant_sort_by_general == 'order_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by orders')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="popular_restaurant_sort_by_general" value="review_count" {{ $popular_restaurant_sort_by_general == 'review_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by reviews count')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="popular_restaurant_sort_by_general" value="rating" {{ $popular_restaurant_sort_by_general == 'rating' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by ratings')}}
                                    </span>
                                </label>
                            </div>
                            @php($popular_restaurant_sort_by_unavailable = \App\Models\PriorityList::where('name', 'popular_restaurant_sort_by_unavailable')->where('type', 'unavailable')->first())
                            @php($popular_restaurant_sort_by_unavailable = $popular_restaurant_sort_by_unavailable ? $popular_restaurant_sort_by_unavailable->value : '')
                            <div class="border bg-white rounded p-3 d-flex flex-column gap-2 fs-14 mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="popular_restaurant_sort_by_unavailable" value="last" {{ $popular_restaurant_sort_by_unavailable == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show currently closed restaurants in the last')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="popular_restaurant_sort_by_unavailable" value="remove" {{ $popular_restaurant_sort_by_unavailable == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove currently closed restaurants from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="popular_restaurant_sort_by_unavailable" value="none" {{ $popular_restaurant_sort_by_unavailable == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                            @php($popular_restaurant_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'popular_restaurant_sort_by_temp_closed')->where('type', 'temp_closed')->first())
                            @php($popular_restaurant_sort_by_temp_closed = $popular_restaurant_sort_by_temp_closed ? $popular_restaurant_sort_by_temp_closed->value : '')
                            <div class="border bg-white rounded p-3 d-flex flex-column gap-2 fs-14 mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="popular_restaurant_sort_by_temp_closed" value="last" {{ $popular_restaurant_sort_by_temp_closed == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show temporarily off restaurants in the last')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="popular_restaurant_sort_by_temp_closed" value="remove" {{ $popular_restaurant_sort_by_temp_closed == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove temporarily off restaurants from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="popular_restaurant_sort_by_temp_closed" value="none" {{ $popular_restaurant_sort_by_temp_closed == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-body mb-20">
                <div class="mb-20">
                    <h4 class="mb-1">{{translate('New Restaurant')}}</h4>
                    <p class="m-0 fs-12">
                        {{ translate('The_New_restaurant_list_arranges_all_restaurants_based_on_the_latest_join_that_are_closest_to_the_customers_location.') }}
                    </p>
                </div>
                @php($new_restaurant_default_status = \App\Models\BusinessSetting::where('key', 'new_restaurant_default_status')->first())
                @php($new_restaurant_default_status = $new_restaurant_default_status ? $new_restaurant_default_status->value : 1)
                <div class="sorting-card bg-light rounded-10 p-12 p-xxl-20">
                    <div
                        class="bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="new_restaurant_default_status" value="1" {{ $new_restaurant_default_status == '1' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use default sorting list') }}
                                </span>
                            </label>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="new_restaurant_default_status" value="0" {{ $new_restaurant_default_status == '0' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use custom sorting list') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="inner-collapse-div">
                        <div class="">
                            @php($new_restaurant_sort_by_general = \App\Models\PriorityList::where('name', 'new_restaurant_sort_by_general')->where('type', 'general')->first())
                            @php($new_restaurant_sort_by_general = $new_restaurant_sort_by_general ? $new_restaurant_sort_by_general->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="new_restaurant_sort_by_general"
                                        value="latest_created" {{ $new_restaurant_sort_by_general == 'latest_created' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by latest created')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="new_restaurant_sort_by_general"
                                        value="nearby_first" {{ $new_restaurant_sort_by_general == 'nearby_first' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort new restaurants by distance')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="new_restaurant_sort_by_general"
                                        value="delivery_time" {{ $new_restaurant_sort_by_general == 'delivery_time' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort new restaurants by delivery time')}}
                                    </span>
                                </label>
                            </div>
                            @php($new_restaurant_sort_by_unavailable = \App\Models\PriorityList::where('name', 'new_restaurant_sort_by_unavailable')->where('type', 'unavailable')->first())
                            @php($new_restaurant_sort_by_unavailable = $new_restaurant_sort_by_unavailable ? $new_restaurant_sort_by_unavailable->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="new_restaurant_sort_by_unavailable" value="last" {{ $new_restaurant_sort_by_unavailable == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show currently closed restaurants in the last')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="new_restaurant_sort_by_unavailable" value="remove" {{ $new_restaurant_sort_by_unavailable == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove currently closed restaurants from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="new_restaurant_sort_by_unavailable" value="none" {{ $new_restaurant_sort_by_unavailable == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                            @php($new_restaurant_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'new_restaurant_sort_by_temp_closed')->where('type', 'temp_closed')->first())
                            @php($new_restaurant_sort_by_temp_closed = $new_restaurant_sort_by_temp_closed ? $new_restaurant_sort_by_temp_closed->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="new_restaurant_sort_by_temp_closed" value="last" {{ $new_restaurant_sort_by_temp_closed == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show temporarily off restaurants in the last')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="new_restaurant_sort_by_temp_closed" value="remove" {{ $new_restaurant_sort_by_temp_closed == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove temporarily off restaurants from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="new_restaurant_sort_by_temp_closed" value="none" {{ $new_restaurant_sort_by_temp_closed == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-body mb-20">
                <div class="mb-20">
                    <h4 class="mb-1">
                        {{translate('Restaurant List, Category wise restaurant list, Cuisine wise restaurant list')}}
                    </h4>
                    <p class="m-0 fs-12">
                        {{ translate('A list of all the restaurants which are sorted based on  latest joined, mostly ordered, customer choice and good review & ratings') }}
                    </p>
                </div>
                @php($all_restaurant_default_status = \App\Models\BusinessSetting::where('key', 'all_restaurant_default_status')->first())
                @php($all_restaurant_default_status = $all_restaurant_default_status ? $all_restaurant_default_status->value : 1)
                <div class="sorting-card bg-light rounded-10 p-12 p-xxl-20">
                    <div
                        class="bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="all_restaurant_default_status" value="1" {{ $all_restaurant_default_status == '1' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use default sorting list') }}
                                </span>
                            </label>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="all_restaurant_default_status" value="0" {{ $all_restaurant_default_status == '0' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use custom sorting list') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="inner-collapse-div">
                        <div class="">
                            @php($all_restaurant_sort_by_general = \App\Models\PriorityList::where('name', 'all_restaurant_sort_by_general')->where('type', 'general')->first())
                            @php($all_restaurant_sort_by_general = $all_restaurant_sort_by_general ? $all_restaurant_sort_by_general->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="all_restaurant_sort_by_general"
                                        value="latest_created" {{ $all_restaurant_sort_by_general == 'latest_created' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by latest created')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="all_restaurant_sort_by_general"
                                        value="first_created" {{ $all_restaurant_sort_by_general == 'first_created' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by first created')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="all_restaurant_sort_by_general"
                                        value="order_count" {{ $all_restaurant_sort_by_general == 'order_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by orders')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="all_restaurant_sort_by_general"
                                        value="nearest_first" {{ $all_restaurant_sort_by_general == 'nearest_first' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show nearest restaurant first')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="all_restaurant_sort_by_general"
                                        value="review_count" {{ $all_restaurant_sort_by_general == 'review_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by reviews count')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="all_restaurant_sort_by_general"
                                        value="rating" {{ $all_restaurant_sort_by_general == 'rating' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by ratings')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="all_restaurant_sort_by_general"
                                        value="a_to_z" {{ $all_restaurant_sort_by_general == 'a_to_z' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (A to Z)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="all_restaurant_sort_by_general"
                                        value="z_to_a" {{ $all_restaurant_sort_by_general == 'z_to_a' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (Z to A)')}}
                                    </span>
                                </label>
                            </div>
                            @php($all_restaurant_sort_by_unavailable = \App\Models\PriorityList::where('name', 'all_restaurant_sort_by_unavailable')->where('type', 'unavailable')->first())
                            @php($all_restaurant_sort_by_unavailable = $all_restaurant_sort_by_unavailable ? $all_restaurant_sort_by_unavailable->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="all_restaurant_sort_by_unavailable" value="last" {{ $all_restaurant_sort_by_unavailable == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show currently closed restaurants in the last')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="all_restaurant_sort_by_unavailable" value="remove" {{ $all_restaurant_sort_by_unavailable == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove currently closed restaurants from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="all_restaurant_sort_by_unavailable" value="none" {{ $all_restaurant_sort_by_unavailable == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                            @php($all_restaurant_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'all_restaurant_sort_by_temp_closed')->where('type', 'temp_closed')->first())
                            @php($all_restaurant_sort_by_temp_closed = $all_restaurant_sort_by_temp_closed ? $all_restaurant_sort_by_temp_closed->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="all_restaurant_sort_by_temp_closed" value="last" {{ $all_restaurant_sort_by_temp_closed == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show temporarily off restaurants in the last')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="all_restaurant_sort_by_temp_closed" value="remove" {{ $all_restaurant_sort_by_temp_closed == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove temporarily off restaurants from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="all_restaurant_sort_by_temp_closed" value="none" {{ $all_restaurant_sort_by_temp_closed == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-body mb-20">
                <div class="mb-20">
                    <h4 class="mb-1">{{translate('Food Campaign')}}</h4>
                    <p class="m-0 fs-12">
                        {{ translate('The food campaign includes the list of discounted food items offered for the customers') }}
                    </p>
                </div>
                @php($campaign_food_default_status = \App\Models\BusinessSetting::where('key', 'campaign_food_default_status')->first())
                @php($campaign_food_default_status = $campaign_food_default_status ? $campaign_food_default_status->value : 1)
                <div class="sorting-card bg-light rounded-10 p-12 p-xxl-20">
                    <div
                        class="bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="campaign_food_default_status" value="1" {{ $campaign_food_default_status == '1' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use default sorting list') }}
                                </span>
                            </label>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="campaign_food_default_status" value="0" {{ $campaign_food_default_status == '0' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use custom sorting list') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="inner-collapse-div">
                        <div class="">
                            @php($campaign_food_sort_by_general = \App\Models\PriorityList::where('name', 'campaign_food_sort_by_general')->where('type', 'general')->first())
                            @php($campaign_food_sort_by_general = $campaign_food_sort_by_general ? $campaign_food_sort_by_general->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="campaign_food_sort_by_general"
                                        value="latest_created" {{ $campaign_food_sort_by_general == 'latest_created' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by latest created')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="campaign_food_sort_by_general"
                                        value="first_created" {{ $campaign_food_sort_by_general == 'first_created' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by first created')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="campaign_food_sort_by_general"
                                        value="order_count" {{ $campaign_food_sort_by_general == 'order_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by orders')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="campaign_food_sort_by_general"
                                        value="nearest_first" {{ $campaign_food_sort_by_general == 'nearest_first' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show nearest food first')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="campaign_food_sort_by_general"
                                        value="nearest_end_first" {{ $campaign_food_sort_by_general == 'nearest_end_first' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show end date near foods first')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="campaign_food_sort_by_general"
                                        value="a_to_z" {{ $campaign_food_sort_by_general == 'a_to_z' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (A to Z)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="campaign_food_sort_by_general"
                                        value="z_to_a" {{ $campaign_food_sort_by_general == 'z_to_a' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (Z to A)')}}
                                    </span>
                                </label>
                            </div>
                            @php($campaign_food_sort_by_unavailable = \App\Models\PriorityList::where('name', 'campaign_food_sort_by_unavailable')->where('type', 'unavailable')->first())
                            @php($campaign_food_sort_by_unavailable = $campaign_food_sort_by_unavailable ? $campaign_food_sort_by_unavailable->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="campaign_food_sort_by_unavailable" value="last" {{ $campaign_food_sort_by_unavailable == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show unavailable foods in the last (both food & restaurant are unavailable)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="campaign_food_sort_by_unavailable" value="remove" {{ $campaign_food_sort_by_unavailable == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove unavailable foods from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="campaign_food_sort_by_unavailable" value="none" {{ $campaign_food_sort_by_unavailable == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                            @php($campaign_food_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'campaign_food_sort_by_temp_closed')->where('type', 'temp_closed')->first())
                            @php($campaign_food_sort_by_temp_closed = $campaign_food_sort_by_temp_closed ? $campaign_food_sort_by_temp_closed->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="campaign_food_sort_by_temp_closed" value="last" {{ $campaign_food_sort_by_temp_closed == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show food in the last if restaurant is temporarily off')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="campaign_food_sort_by_temp_closed" value="remove" {{ $campaign_food_sort_by_temp_closed == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove food from the list if restaurant is temporarily off')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="campaign_food_sort_by_temp_closed" value="none" {{ $campaign_food_sort_by_temp_closed == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-body mb-20">
                <div class="mb-20">
                    <h4 class="mb-1">{{translate('Best Reviewed Food')}}</h4>
                    <p class="m-0 fs-12">
                        {{ translate('Best Reviewed items are the top most ordered item list of customer choice which are highly rated & reviewed ') }}
                    </p>
                </div>
                @php($best_reviewed_food_default_status = \App\Models\BusinessSetting::where('key', 'best_reviewed_food_default_status')->first())
                @php($best_reviewed_food_default_status = $best_reviewed_food_default_status ? $best_reviewed_food_default_status->value : 1)
                <div class="sorting-card bg-light rounded-10 p-12 p-xxl-20">
                    <div
                        class="bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="best_reviewed_food_default_status" value="1" {{ $best_reviewed_food_default_status == '1' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use default sorting list') }}
                                </span>
                            </label>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="best_reviewed_food_default_status" value="0" {{ $best_reviewed_food_default_status == '0' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use custom sorting list') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="inner-collapse-div">
                        <div class="">
                            @php($best_reviewed_food_sort_by_general = \App\Models\PriorityList::where('name', 'best_reviewed_food_sort_by_general')->where('type', 'general')->first())
                            @php($best_reviewed_food_sort_by_general = $best_reviewed_food_sort_by_general ? $best_reviewed_food_sort_by_general->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_general" value="review_count" {{ $best_reviewed_food_sort_by_general == 'review_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by reviews count')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_general" value="rating" {{ $best_reviewed_food_sort_by_general == 'rating' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by ratings')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_general" value="nearest_first" {{ $best_reviewed_food_sort_by_general == 'nearest_first' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show nearest food first')}}
                                    </span>
                                </label>
                            </div>
                            @php($best_reviewed_food_sort_by_rating = \App\Models\PriorityList::where('name', 'best_reviewed_food_sort_by_rating')->where('type', 'rating')->first())
                            @php($best_reviewed_food_sort_by_rating = $best_reviewed_food_sort_by_rating ? $best_reviewed_food_sort_by_rating->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_rating" value="four_plus" {{ $best_reviewed_food_sort_by_rating == 'four_plus' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show 4+ rated foods')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_rating" value="three_half_plus" {{ $best_reviewed_food_sort_by_rating == 'three_half_plus' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show 3.5+ rated foods')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_rating" value="three_plus" {{ $best_reviewed_food_sort_by_rating == 'three_plus' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show 3+ rated foods')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_rating" value="none" {{ $best_reviewed_food_sort_by_rating == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                            @php($best_reviewed_food_sort_by_unavailable = \App\Models\PriorityList::where('name', 'best_reviewed_food_sort_by_unavailable')->where('type', 'unavailable')->first())
                            @php($best_reviewed_food_sort_by_unavailable = $best_reviewed_food_sort_by_unavailable ? $best_reviewed_food_sort_by_unavailable->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_unavailable" value="last" {{ $best_reviewed_food_sort_by_unavailable == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show unavailable foods in the last (both food & restaurant are unavailable)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_unavailable" value="remove" {{ $best_reviewed_food_sort_by_unavailable == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove unavailable foods from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_unavailable" value="none" {{ $best_reviewed_food_sort_by_unavailable == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                            @php($best_reviewed_food_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'best_reviewed_food_sort_by_temp_closed')->where('type', 'temp_closed')->first())
                            @php($best_reviewed_food_sort_by_temp_closed = $best_reviewed_food_sort_by_temp_closed ? $best_reviewed_food_sort_by_temp_closed->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_temp_closed" value="last" {{ $best_reviewed_food_sort_by_temp_closed == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show food in the last if restaurant is temporarily off')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_temp_closed" value="remove" {{ $best_reviewed_food_sort_by_temp_closed == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove food from the list if restaurant is temporarily off')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="best_reviewed_food_sort_by_temp_closed" value="none" {{ $best_reviewed_food_sort_by_temp_closed == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-body mb-20">
                <div class="mb-20">
                    <h4 class="mb-1">{{translate('Category Wise Foods')}}</h4>
                    <p class="m-0 fs-12">
                        {{ translate('Category Wise Foods means the latest food items list under a specific category') }}
                    </p>
                </div>
                @php($category_food_default_status = \App\Models\BusinessSetting::where('key', 'category_food_default_status')->first())
                @php($category_food_default_status = $category_food_default_status ? $category_food_default_status->value : 1)
                <div class="sorting-card bg-light rounded-10 p-12 p-xxl-20">
                    <div
                        class="bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="category_food_default_status" value="1" {{ $category_food_default_status == '1' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use default sorting list') }}
                                </span>
                            </label>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="category_food_default_status" value="0" {{ $category_food_default_status == '0' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use custom sorting list') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="inner-collapse-div">
                        <div class="">
                            @php($category_food_sort_by_general = \App\Models\PriorityList::where('name', 'category_food_sort_by_general')->where('type', 'general')->first())
                            @php($category_food_sort_by_general = $category_food_sort_by_general ? $category_food_sort_by_general->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="category_food_sort_by_general"
                                        value="order_count" {{ $category_food_sort_by_general == 'order_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by orders')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="category_food_sort_by_general"
                                        value="review_count" {{ $category_food_sort_by_general == 'review_count' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by reviews count')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="category_food_sort_by_general"
                                        value="rating" {{ $category_food_sort_by_general == 'rating' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by ratings')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="category_food_sort_by_general"
                                        value="nearest_first" {{ $category_food_sort_by_general == 'nearest_first' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show nearest food first')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="category_food_sort_by_general"
                                        value="a_to_z" {{ $category_food_sort_by_general == 'a_to_z' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (A to Z)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="category_food_sort_by_general"
                                        value="z_to_a" {{ $category_food_sort_by_general == 'z_to_a' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Sort by Alphabetical (Z to A)')}}
                                    </span>
                                </label>
                            </div>
                            @php($category_food_sort_by_unavailable = \App\Models\PriorityList::where('name', 'category_food_sort_by_unavailable')->where('type', 'unavailable')->first())
                            @php($category_food_sort_by_unavailable = $category_food_sort_by_unavailable ? $category_food_sort_by_unavailable->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="category_food_sort_by_unavailable" value="last" {{ $category_food_sort_by_unavailable == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show unavailable foods in the last (both food & restaurant are unavailable)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="category_food_sort_by_unavailable" value="remove" {{ $category_food_sort_by_unavailable == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove unavailable foods from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="category_food_sort_by_unavailable" value="none" {{ $category_food_sort_by_unavailable == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                            @php($category_food_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'category_food_sort_by_temp_closed')->where('type', 'temp_closed')->first())
                            @php($category_food_sort_by_temp_closed = $category_food_sort_by_temp_closed ? $category_food_sort_by_temp_closed->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="category_food_sort_by_temp_closed" value="last" {{ $category_food_sort_by_temp_closed == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show food in the last if restaurant is temporarily off')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="category_food_sort_by_temp_closed" value="remove" {{ $category_food_sort_by_temp_closed == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove food from the list if restaurant is temporarily off')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio"
                                        name="category_food_sort_by_temp_closed" value="none" {{ $category_food_sort_by_temp_closed == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-body mb-20">
                <div class="mb-20">
                    <h4 class="mb-1">{{translate('Food and Restaurant list (Search Bar)')}}</h4>
                    <p class="m-0 fs-12">
                        {{ translate('Food and Restaurant list (Search Bar) means the food and restaurant list from top search bar.') }}
                    </p>
                </div>
                @php($search_bar_default_status = \App\Models\BusinessSetting::where('key', 'search_bar_default_status')->first())
                @php($search_bar_default_status = $search_bar_default_status ? $search_bar_default_status->value : 1)
                <div class="sorting-card bg-light rounded-10 p-12 p-xxl-20">
                    <div
                        class="bg-white rounded border d-flex justify-content-between align-items-center flex-wrap gap-3 p-3">
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="search_bar_default_status" value="1" {{ $search_bar_default_status == '1' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use default sorting list') }}
                                </span>
                            </label>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check form--check">
                                <input class="form-check-input collapse-div-toggler" type="radio"
                                    name="search_bar_default_status" value="0" {{ $search_bar_default_status == '0' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ translate('Use custom sorting list') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="inner-collapse-div">
                        <div class="pt-4">
                            @php($search_bar_sort_by_unavailable = \App\Models\PriorityList::where('name', 'search_bar_sort_by_unavailable')->where('type', 'unavailable')->first())
                            @php($search_bar_sort_by_unavailable = $search_bar_sort_by_unavailable ? $search_bar_sort_by_unavailable->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="search_bar_sort_by_unavailable"
                                        value="last" {{ $search_bar_sort_by_unavailable == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show unavailable foods & restaurant in the last (both food & restaurant are unavailable)')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="search_bar_sort_by_unavailable"
                                        value="remove" {{ $search_bar_sort_by_unavailable == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove unavailable foods & restaurant from the list')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="search_bar_sort_by_unavailable"
                                        value="none" {{ $search_bar_sort_by_unavailable == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                            @php($search_bar_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'search_bar_sort_by_temp_closed')->where('type', 'temp_closed')->first())
                            @php($search_bar_sort_by_temp_closed = $search_bar_sort_by_temp_closed ? $search_bar_sort_by_temp_closed->value : '')
                            <div class="border rounded p-3 d-flex flex-column gap-2 fs-14 bg-white mt-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="search_bar_sort_by_temp_closed"
                                        value="last" {{ $search_bar_sort_by_temp_closed == 'last' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Show food & restaurant in the last if restaurant is temporarily off')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="search_bar_sort_by_temp_closed"
                                        value="remove" {{ $search_bar_sort_by_temp_closed == 'remove' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('Remove food & restaurant from the list if restaurant is temporarily off')}}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" name="search_bar_sort_by_temp_closed"
                                        value="none" {{ $search_bar_sort_by_temp_closed == 'none' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{translate('None')}}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-sticky mt-2">
            <div class="container-fluid">
                <div class="d-flex flex-wrap gap-3 justify-content-center py-3">
                    <button type="reset" id="reset_btn"
                        class="btn btn--secondary min-w-120 location-reload">{{ translate('messages.Reset') }} </button>
                    <button type="submit" class="btn btn--primary">
                        <i class="tio-save"></i>
                        {{ translate('Save_Information') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Guidline Offcanvas Btn -->
<div class="d-flexgap-2 w-40px gap-2 bg-white position-fixed end-0 translate-middle-y pointer view-guideline-btn flex-column pt-3 px-2 justify-content-center offcanvas-trigger"
    data-toggle="offcanvas" data-target="#offcanvasSetupGuide">
    <span class="arrow bg-primary py-1 px-2 text-white rounded fs-12"><i class="tio-share-vs"></i></span>
    <span class="view-guideline-btn-text text-dark font-semibold pb-2 text-nowrap">
        {{ translate('View_Guideline') }}
    </span>
</div>

<!-- Guidline Offcanvas -->
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>
<div class="custom-offcanvas" tabindex="-1" id="offcanvasSetupGuide" aria-labelledby="offcanvasSetupGuideLabel"
    style="--offcanvas-width: 500px">
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0">{{ translate('messages.Priority Setup Guideline') }}</h3>
            <button type="button"
                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                aria-label="Close">&times;</button>
        </div>
        <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                        type="button" data-toggle="collapse" data-target="#priority_setup_purpose_guide"
                        aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('Priority Setup Purpose') }}</span>
                    </button>
                    <a href="#priority_setup_purpose"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('Let’s Setup') }}</a>
                </div>
                <div class="collapse show mt-3" id="priority_setup_purpose_guide">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Priority Setup Purpose')}}</h5>
                            <p class="fs-12 mb-3">
                                {{ translate('The Priority Setup feature allows the admin or restaurant to control the display order of food items, categories, and subcategories across the system. By setting priority rules, the platform can highlight specific items or organise listings in a way that improves visibility and user experience.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                        type="button" data-toggle="collapse" data-target="#priority_setup_benefit_guide"
                        aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('Priority Setup Benefit') }}</span>
                    </button>
                    <a href="#priority_setup_benefit"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3" id="priority_setup_benefit_guide">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Priority Setup Benefit')}}</h5>
                            <ul class="mb-0 fs-12">
                                <li class="mt-2 mb-3">
                                    {{ translate('Promotes popular or high-performing items') }}
                                </li>
                                <li class="mt-2 mb-3">
                                    {{ translate('Improves menu navigation and discoverability') }}
                                </li>
                                <li class="mt-2 mb-3">
                                    {{ translate('Creates a consistent and organised listing order') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                        type="button" data-toggle="collapse" data-target="#sorting_options_guide" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('messages.Sorting Options') }}</span>
                    </button>
                    <a href="#sorting_options"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3" id="sorting_options_guide">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Use Default Sorting')}}</h5>
                            <p class="fs-12 mb-3">
                                {{ translate('messages.Items/Categories are displayed based on the system’s predefined order.') }}
                            </p>
                        </div>
                    </div>
                    <div class="card card-body mt-2">
                        <div class="">
                            <h5 class="mb-3">{{translate('Use Custom Sorting')}}</h5>
                            <p class="fs-12 mb-3">
                                {{ translate('Items are arranged based on selected criteria, including:') }}
                            </p>
                            <ul class="mb-0 fs-12">
                                <li class="font-semibold">
                                    {{ translate('Sort by latest created') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Shows the most recently added foods or restaurants first') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Sort by Orders') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Displays items or restaurants based on the total number of orders, highest first') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Sort by total restaurants') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Sorts categories or locations based on the number of restaurants associated') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Show the nearest food first') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Displays items from restaurants closest to the customer first') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Show unavailable foods in the last(both food & restaurant are unavailable)') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Place foods and restaurants that are currently unavailable at the end of the list') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Remove unavailable foods from the list') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Completely hides foods or restaurants that are unavailable') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Show the currently closed restaurants last') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Keeps closed restaurants at the end of the list while still showing them') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Remove currently closed restaurants from the list') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Hides all closed restaurants entirely') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Show temporarily off restaurants in the last') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Temporarily inactive restaurants appear at the end of the list') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Remove temporarily off restaurants from the list') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Temporarily inactive restaurants are hidden completely') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Sort new restaurants by distance') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Shows newly added restaurants closest to the customer first') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Sort new restaurants by delivery time') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Order new restaurants based on estimated delivery time, fastest first') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Show end date near Foods First') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Displays foods that are about to expire or whose availability end date is near at the top') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Alphabetical order (A–Z)') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Sorts foods or restaurants in ascending alphabetical order') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Alphabetical order (Z–A)') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Sorts foods or restaurants in descending alphabetical order') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Nearest item first') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Displays items based on proximity to the customer') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Sort by reviews count') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Order items/restaurants by the number of reviews received, highest first') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Sort by ratings') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Displays items/restaurants with the highest average rating first') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Show 4+ rated foods') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Displays only foods with ratings of 4 or higher') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Show 3.5+ rated foods') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Displays foods with ratings of 3.5 or higher') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('Show 3+ rated foods') }}
                                </li>
                                <p class="mt-2 mb-3">
                                    {{ translate('Displays foods with ratings of 3 or higher') }}
                                </p>
                            </ul>
                            <p class="fs-12 mb-3">
                                {{ translate('When Custom Sorting is selected, the system applies the chosen sorting rule dynamically to display food items, categories, and subcategories accordingly.') }}
                            </p>
                        </div>
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
        $(".collapse-div-toggler").on('change', function () {
            if ($(this).val() == '0') {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideDown();
            } else {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideUp();
            }
        });

        $(window).on('load', function () {
            $('.collapse-div-toggler').each(function () {
                if ($(this).prop('checked') == true && $(this).val() == '0') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').show();
                } else if ($(this).prop('checked') == true && $(this).val() == '1') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').hide();
                }
            });
        })

        $('#reset_btn').click(function () {
            setTimeout(function () {
                $('.collapse-div-toggler').each(function () {
                    if ($(this).prop('checked') == true && $(this).val() == '0') {
                        $(this).closest('.sorting-card').find('.inner-collapse-div').show();
                    } else if ($(this).prop('checked') == true && $(this).val() == '1') {
                        $(this).closest('.sorting-card').find('.inner-collapse-div').hide();
                    }
                });
            }, 100);
        })

        $('.offcanvas-close-btn').on('click', function (e) {
            e.preventDefault();
            $('.custom-offcanvas').removeClass('open');
            $('#offcanvasOverlay').removeClass('show');
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top - 100
            }, 500);
        });

    </script>
@endpush
