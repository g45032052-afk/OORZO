<?php

namespace App\Http\Controllers\Admin;

use App\Models\DataSetting;
use App\Models\Translation;
use App\Models\AdminFeature;
use App\Models\ReactService;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\AdminTestimonial;
use App\Http\Controllers\Controller;
use App\Models\FAQ;
use App\Models\PageSeoData;
use Brian2694\Toastr\Facades\Toastr;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Models\ReactPromotionalBanner;
use App\Models\ReactTestimonial;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class LandingPageController extends Controller
{
    public function testimonial()
    {
        $testimonial_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'testimonial_title')->first() ?? null;
        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;
        $testimonial = AdminTestimonial::latest()->paginate(config('default_pagination'));
        return view('admin-views.landing_page.testimonial', compact('testimonial', 'language', 'testimonial_title'));
    }

    public function testimonial_store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'designation' => 'required|max:100',
            'review' => 'required|max:1000',
            'reviewer_image' => 'required|image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
            'company_image' => 'nullable|image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
        ]);

        $testimonial = new AdminTestimonial();
        $testimonial->name = $request->name;
        $testimonial->designation = $request->designation;
        $testimonial->review = $request->review;

        if ($request->hasFile('reviewer_image')) {
            $imageFile = $request->file('reviewer_image');
            $originalExtension = 'png';

            $testimonial->reviewer_image = Helpers::upload(
                dir: 'reviewer_image/',
                format: $originalExtension,
                image: $imageFile
            );
        }

        if ($request->hasFile('company_image')) {
            $imageFile2 = $request->file('company_image');
            $originalExtension2 = $imageFile2->getClientOriginalExtension();

            $testimonial->company_image = Helpers::upload(
                dir: 'company_image/',
                format: $originalExtension2,
                image: $imageFile2
            );
        } else {
            $testimonial->company_image = null;
        }

        $testimonial->save();

        Toastr::success(translate('messages.testimonial_added_successfully'));
        return back();
    }

    public function testimonial_status(Request $request)
    {

        if (getEnvMode() == 'demo' && $request->id == 1) {
            Toastr::warning('Sorry!You can not inactive this review!');
            return back();
        }
        $review = AdminTestimonial::findOrFail($request->id);
        $review->status = $request->status;
        $review->save();
        Toastr::success(translate('messages.testimonial_status_updated'));
        return back();
    }


    public function testimonial_edit($id)
    {
        $review = AdminTestimonial::withoutGlobalScope('translate')->findOrFail($id);
        return view('admin-views.landing_page.testimonial_edit', compact('review'));
    }

    public function testimonial_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:100',
            'designation' => 'required|max:100',
            'review' => 'required|max:1000',
            'reviewer_image' => 'nullable|image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
            'company_image' => 'nullable|image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
        ]);

        $review = AdminTestimonial::findOrFail($id);
        $review->name = $request->name;
        $review->designation = $request->designation;
        $review->review = $request->review;

        if ($request->hasFile('reviewer_image')) {
            $imageFile = $request->file('reviewer_image');
            $originalExtension = 'png';

            $review->reviewer_image = Helpers::update(
                dir: 'reviewer_image/',
                old_image: $review->reviewer_image,
                format: $originalExtension,
                image: $imageFile
            );
        }

        if ($request->hasFile('company_image')) {
            $imageFile2 = $request->file('company_image');
            $originalExtension2 = $imageFile2->getClientOriginalExtension();

            $review->company_image = Helpers::update(
                dir: 'company_image/',
                old_image: $review->company_image,
                format: $originalExtension2,
                image: $imageFile2
            );
        }

        $review->save();

        Toastr::success(translate('messages.testimonial_updated_successfully'));
        return back();
    }

    public function testimonial_destroy(AdminTestimonial $testimonial)
    {
        if (getEnvMode() == 'demo' && $testimonial->id == 1) {
            Toastr::warning(translate('messages.you_can_not_delete_this_review_please_add_a_new_review_to_delete'));
            return back();
        }

        Helpers::check_and_delete('reviewer_image/', $testimonial->reviewer_image);
        Helpers::check_and_delete('reviewer_image/', $testimonial->company_image);
        $testimonial->delete();
        Toastr::success(translate('messages.testimonial_deleted_successfully'));
        return back();
    }

    public function features()
    {
        $features = AdminFeature::latest()->paginate(config('default_pagination'));
        $feature_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'feature_title')->first() ?? null;
        $feature_sub_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'feature_sub_title')->first() ?? null;
        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;
        return view('admin-views.landing_page.features', compact('features', 'feature_title', 'feature_sub_title', 'language'));
    }

    public function feature_store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'required|max:1000',
            'feature_image' => 'required|image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
        ]);

        if ($request->name[array_search('default', $request->lang)] == '' || $request->description[array_search('default', $request->lang)] == '') {
            Toastr::error(translate('default_title_and_description_is_required'));
            return back();
        }

        $imageFile = $request->file('feature_image');
        $originalExtension = 'png';

        $feature = new AdminFeature();
        $feature->title = $request->name[array_search('default', $request->lang)];
        $feature->description = $request->description[array_search('default', $request->lang)];
        $feature->image = Helpers::upload(dir: 'feature_image/', format: $originalExtension, image: $imageFile);
        $feature->save();

        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\AdminFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'feature_name'
                        ],
                        ['value' => $feature->title]
                    );
                }
            } else {
                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\AdminFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'feature_name'
                        ],
                        ['value' => $request->name[$index]]
                    );
                }
            }
            if ($default_lang == $key && !($request->description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\AdminFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'feature_description'
                        ],
                        ['value' => $feature->description]
                    );
                }
            } else {
                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\AdminFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'feature_description'
                        ],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }

        Toastr::success(translate('messages.Feature_added_successfully'));
        return back();
    }

    public function feature_status(Request $request)
    {

        if (getEnvMode() == 'demo' && $request->id == 1) {
            Toastr::warning('Sorry!You can not inactive this review!');
            return back();
        }
        $features = AdminFeature::findOrFail($request->id);
        $features->status = $request->status;
        $features->save();
        Toastr::success(translate('messages.feature_status_updated'));
        return back();
    }


    public function feature_edit($id)
    {
        $feature = AdminFeature::withoutGlobalScope('translate')->with('translations')->findOrFail($id);
        return view('admin-views.landing_page.feature_edit', compact('feature'));
    }

    public function feature_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'required|max:1000',
            'feature_image' => 'nullable|max:2048',
        ]);

        if ($request->name[array_search('default', $request->lang)] == '' || $request->description[array_search('default', $request->lang)] == '') {
            Toastr::error(translate('default_title_and_description_is_required'));
            return back();
        }
        $feature = AdminFeature::findOrFail($id);

        $feature->title = $request->name[array_search('default', $request->lang)];
        $feature->description = $request->description[array_search('default', $request->lang)];
        $feature->image = $request->has('feature_image') ? Helpers::update(dir: 'feature_image/', old_image: $feature->image, format: 'png', image: $request->file('feature_image')) : $feature->image;
        $feature->save();

        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\AdminFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'feature_name'
                        ],
                        ['value' => $feature->title]
                    );
                }
            } else {
                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\AdminFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'feature_name'
                        ],
                        ['value' => $request->name[$index]]
                    );
                }
            }
            if ($default_lang == $key && !($request->description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\AdminFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'feature_description'
                        ],
                        ['value' => $feature->description]
                    );
                }
            } else {
                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\AdminFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'feature_description'
                        ],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }
        Toastr::success(translate('messages.feature_updated_successfully'));
        return back();
    }


    public function feature_destroy(AdminFeature $feature)
    {
        if (getEnvMode() == 'demo' && $feature->id == 1) {
            Toastr::warning(translate('messages.you_can_not_delete_this_review_please_add_a_new_review_to_delete'));
            return back();
        }

        Helpers::check_and_delete('feature_image/', $feature->image);

        $feature?->translations()?->delete();
        $feature?->delete();
        Toastr::success(translate('messages.feature_deleted_successfully'));
        return back();
    }


    public function header()
    {

        $header_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'header_title')->first() ?? null;
        $header_sub_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'header_sub_title')->first() ?? null;
        $header_tag_line = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'header_tag_line')->first() ?? null;

        $header_image_content = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'header_image_content')->first() ?? null;
        $header_floating_content_data = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'header_floating_content')->first() ?? null;

        $header_app_button_name = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'header_app_button_name')->first() ?? null;
        $header_app_button_status = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'header_app_button_status')->first()?->value ?? null;
        // dd($header_app_button_status);
        $header_floating_content_data = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'header_floating_content')->first() ?? null;

        $header_button_content = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'header_button_content')->first()?->value ?? null;

        // $button_content=json_decode($header_button_content?? null ,true );
        $image_content = json_decode($header_image_content?->value ?? null, true);
        $header_floating_content = json_decode($header_floating_content_data?->value ?? null, true);
        $header_content_image_full_url = Helpers::get_full_url('header_image', $image_content['header_content_image'] ?? 'double_screen_image.png', $image_content['header_content_image_storage'] ?? 'public');
        $header_bg_image_full_url = Helpers::get_full_url('header_image', $image_content['header_bg_image'] ?? null, $image_content['header_bg_image_storage'] ?? 'public');

        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;

        return view('admin-views.landing_page.header', compact('header_title', 'header_sub_title', 'header_tag_line', 'header_image_content', 'header_floating_content_data', 'header_app_button_name', 'header_app_button_status', 'header_floating_content_data', 'header_button_content', 'image_content', 'header_floating_content', 'language', 'header_content_image_full_url', 'header_bg_image_full_url'));
    }

    public function about_us()
    {

        $header_title = \App\Models\DataSetting::with('translations')->withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'about_us_title')->first() ?? null;
        $header_sub_title = \App\Models\DataSetting::with('translations')->withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'about_us_sub_title')->first() ?? null;
        $header_tag_line = \App\Models\DataSetting::with('translations')->withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'about_us_text')->first() ?? null;
        $about_us_button_content = \App\Models\DataSetting::with('translations')->withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'about_us_button_content')->first()?->value ?? null;
        $header_image_content = \App\Models\DataSetting::with('translations')->withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'about_us_image_content')->first() ?? null;

        $about_us_app_button_name = \App\Models\DataSetting::with('translations')->withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'about_us_app_button_name')->first() ?? null;
        $about_us_app_button_status = \App\Models\DataSetting::with('translations')->withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'about_us_app_button_status')->first()?->value ?? null;
        $about_us_content_image = Helpers::get_full_url('about_us_image', $header_image_content?->value ?? null, $header_image_content?->storage[0]?->value ?? 'public');
        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;


        return view('admin-views.landing_page.about_us', compact('header_title', 'header_sub_title', 'header_tag_line', 'header_image_content', 'about_us_button_content', 'about_us_app_button_name', 'about_us_app_button_status', 'language', 'about_us_content_image'));
    }

    public function why_choose_us()
    {
        $data_1 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'why_choose_us_title_1')->first();
        $data_1_image = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'why_choose_us_image_1')->first();
        $data_2 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'why_choose_us_title_2')->first();
        $data_2_image = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'why_choose_us_image_2')->first();
        $data_3 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'why_choose_us_title_3')->first();
        $data_3_image = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'why_choose_us_image_3')->first();
        $data_4 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'why_choose_us_title_4')->first();
        $data_4_image = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'why_choose_us_image_4')->first();

        $why_choose_us_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'why_choose_us_title')->first();

        $why_choose_us_sub_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'why_choose_us_sub_title')->first();

        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;

        return view('admin-views.landing_page.why_choose_us', compact('data_1', 'data_1_image', 'data_2', 'data_2_image', 'data_3', 'data_3_image', 'data_4', 'data_4_image', 'why_choose_us_title', 'why_choose_us_sub_title', 'language'));
    }

    public function earn_money()
    {

        $earn_money_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'earn_money_title')->first() ?? null;
        $earn_money_sub_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'earn_money_sub_title')->first() ?? null;
        $earn_money_reg_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'earn_money_reg_title')->first() ?? null;
        $earn_money_reg_image = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'earn_money_reg_image')->first() ?? null;
        $earn_money_reg_image_full_url = Helpers::get_full_url('earn_money', $earn_money_reg_image?->value ?? null, $earn_money_reg_image?->storage[0]?->value ?? 'public');

        $earn_money_restaurant_req_button_name = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'earn_money_restaurant_req_button_name')->first() ?? null;
        $earn_money_restaurant_req_button_status = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'earn_money_restaurant_req_button_status')->first() ?? null;

        $earn_money_restaurant_req_button_link = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'earn_money_restaurant_req_button_link')->first()?->value ?? null;

        $earn_money_delivety_man_req_button_name = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'earn_money_delivety_man_req_button_name')->first() ?? null;
        $earn_money_delivery_man_req_button_status = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'earn_money_delivery_man_req_button_status')->first() ?? null;

        $earn_money_delivery_man_req_button_link = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'earn_money_delivery_man_req_button_link')->first()?->value ?? null;
        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;

        return view('admin-views.landing_page.earn_money', compact('earn_money_title', 'earn_money_sub_title', 'earn_money_reg_title', 'earn_money_reg_image', 'earn_money_restaurant_req_button_name', 'earn_money_restaurant_req_button_status', 'earn_money_restaurant_req_button_link', 'earn_money_delivety_man_req_button_name', 'earn_money_delivery_man_req_button_status', 'earn_money_delivery_man_req_button_link', 'language', 'earn_money_reg_image_full_url'));
    }

    public function services()
    {
        $services_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_title')->first() ?? null;
        $services_sub_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_sub_title')->first() ?? null;

        $services_order_title_1 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_order_title_1')->first() ?? null;
        $services_order_title_2 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_order_title_2')->first() ?? null;
        $services_order_description_1 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_order_description_1')->first() ?? null;
        $services_order_description_2 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_order_description_2')->first() ?? null;

        $services_order_button_name = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_order_button_name')->first() ?? null;
        $services_order_button_status = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_order_button_status')->first() ?? null;

        $services_order_button_link = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_order_button_link')->first()?->value ?? null;


        $services_manage_restaurant_title_1 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_title_1')->first() ?? null;
        $services_manage_restaurant_title_2 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_title_2')->first() ?? null;
        $services_manage_restaurant_description_1 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_description_1')->first() ?? null;
        $services_manage_restaurant_description_2 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_description_2')->first() ?? null;

        $services_manage_restaurant_button_name = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_button_name')->first() ?? null;
        $services_manage_restaurant_button_status = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_button_status')->first() ?? null;

        $services_manage_restaurant_button_link = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_button_link')->first()?->value ?? null;

        $services_manage_delivery_title_1 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_title_1')->first() ?? null;
        $services_manage_delivery_title_2 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_title_2')->first() ?? null;
        $services_manage_delivery_description_1 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_description_1')->first() ?? null;
        $services_manage_delivery_description_2 = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_description_2')->first() ?? null;

        $services_manage_delivery_button_name = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_button_name')->first() ?? null;
        $services_manage_delivery_button_status = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_button_status')->first() ?? null;

        $services_manage_delivery_button_link = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_button_link')->first()?->value ?? null;

        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;

        return view('admin-views.landing_page.services', compact(
            'services_title',
            'services_sub_title',
            'services_order_title_1',
            'services_order_title_2',
            'services_order_description_1',
            'services_order_description_2',
            'services_order_button_name',
            'services_order_button_status',
            'services_order_button_link',
            'services_manage_restaurant_title_1',
            'services_manage_restaurant_title_2',
            'services_manage_restaurant_description_1',
            'services_manage_restaurant_description_2',
            'services_manage_restaurant_button_name',
            'services_manage_restaurant_button_status',
            'services_manage_restaurant_button_link',
            'services_manage_delivery_title_1',
            'services_manage_delivery_title_2',
            'services_manage_delivery_description_1',
            'services_manage_delivery_description_2',
            'services_manage_delivery_button_name',
            'services_manage_delivery_button_status',
            'services_manage_delivery_button_link'
            ,
            'language'
        ));
    }

    public function fixed_data()
    {

        $news_letter_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'news_letter_title')->first() ?? null;
        $news_letter_sub_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'news_letter_sub_title')->first() ?? null;
        $footer_data = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'admin_landing_page')->where('key', 'footer_data')->first() ?? null;
        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;
        // $copyright_text=DataSetting::withoutGlobalScope('translate')->with('translations')->where('type','admin_landing_page')->where('key','copyright_text')->first() ?? null;

        return view('admin-views.landing_page.fixed_data', compact('news_letter_title', 'news_letter_sub_title', 'footer_data', 'language'));
    }

    public function links()
    {

        $landing_page_links = BusinessSetting::where('key', 'landing_page_links')->first();
        $landing_page_links = isset($landing_page_links->value) ? json_decode($landing_page_links->value, true) : null;

        return view('admin-views.landing_page.links', compact('landing_page_links'));
    }
    private function updateLinks(Request $request)
    {
        $data = [
            'app_url_android_status' => $request->app_url_android_status,
            'app_url_android' => $request->app_url_android,
            'app_url_ios_status' => $request->app_url_ios_status,
            'app_url_ios' => $request->app_url_ios,
            'web_app_url_status' => $request->web_app_url_status,
            'web_app_url' => $request->web_app_url,
        ];

        BusinessSetting::updateOrCreate(
            ['key' => 'landing_page_links'],
            ['value' => json_encode($data)]
        );

        return true;
    }

    public function backgroung_color()
    {
        $backgroundChange = BusinessSetting::where(['key' => 'backgroundChange'])->first();

        $backgroundChange = isset($backgroundChange->value) ? json_decode($backgroundChange->value, true) : null;

        return view('admin-views.landing_page.backgroundChange', compact('backgroundChange'));
    }

    public function react_header()
    {

        $keys = [
            'react_header_status',
            'react_header_title',
            'react_header_sub_title',
            'react_header_image',
            'header_location_picker_title',
            'floating_icon_restaurant',
            'floating_icon_average_delivery',
            'floating_icon_customer'
        ];
        $reactHeaders = $this->getLandingPageData('react_landing_page', $keys);

        $react_header_title = $reactHeaders['react_header_title'] ?? null;
        $react_header_sub_title = $reactHeaders['react_header_sub_title'] ?? null;
        $react_header_image = $reactHeaders['react_header_image'] ?? null;
        $react_header_status = $reactHeaders['react_header_status'] ?? null;
        $header_location_picker_title = $reactHeaders['header_location_picker_title'] ?? null;
        $floating_icon_restaurant = $reactHeaders['floating_icon_restaurant'] ?? null;
        $floating_icon_average_delivery = $reactHeaders['floating_icon_average_delivery'] ?? null;
        $floating_icon_customer = $reactHeaders['floating_icon_customer'] ?? null;

        $language = Helpers::get_business_settings('language');

        return view('admin-views.landing_page.react.header', compact(
            'react_header_title',
            'react_header_sub_title',
            'react_header_image',
            'react_header_status',
            'language',
            'header_location_picker_title'
            ,
            'floating_icon_restaurant',
            'floating_icon_average_delivery',
            'floating_icon_customer'
        ));
    }

    public function stepperSection()
    {

        $keys = [
            'stepper_section_status',
            'stepper_1_image',
            'stepper_2_image',
            'stepper_3_image',
            'stepper_4_image',
            'stepper_title_1',
            'stepper_title_2',
            'stepper_title_3',
            'stepper_title_4',
            'stepper_upload_image_type',
            'stapper_single_image',
            'stapper_multiple_image_1',
            'stapper_multiple_image_2',
            'stapper_multiple_image_3',
            'stapper_multiple_image_4',
        ];
        $reactHeaders = $this->getLandingPageData('react_landing_page', $keys);

        $stepper_section_status = $reactHeaders['stepper_section_status'] ?? null;

        $stepper_1_image = $reactHeaders['stepper_1_image'] ?? null;
        $stepper_2_image = $reactHeaders['stepper_2_image'] ?? null;
        $stepper_3_image = $reactHeaders['stepper_3_image'] ?? null;
        $stepper_4_image = $reactHeaders['stepper_4_image'] ?? null;

        $stepper_title_1 = $reactHeaders['stepper_title_1'] ?? null;
        $stepper_title_2 = $reactHeaders['stepper_title_2'] ?? null;
        $stepper_title_3 = $reactHeaders['stepper_title_3'] ?? null;
        $stepper_title_4 = $reactHeaders['stepper_title_4'] ?? null;

        $stepper_upload_image_type = $reactHeaders['stepper_upload_image_type'] ?? null;
        $stapper_single_image = $reactHeaders['stapper_single_image'] ?? null;

        $stapper_multiple_image_1 = $reactHeaders['stapper_multiple_image_1'] ?? null;
        $stapper_multiple_image_2 = $reactHeaders['stapper_multiple_image_2'] ?? null;
        $stapper_multiple_image_3 = $reactHeaders['stapper_multiple_image_3'] ?? null;
        $stapper_multiple_image_4 = $reactHeaders['stapper_multiple_image_4'] ?? null;


        $language = Helpers::get_business_settings('language');

        return view('admin-views.landing_page.react.stepper', compact(
            'stepper_section_status',
            'stepper_1_image',
            'stepper_2_image',
            'stepper_3_image',
            'stepper_4_image',
            'language',
            'stepper_title_1',
            'stepper_title_2',
            'stepper_title_3',
            'stepper_title_4',
            'stepper_upload_image_type',
            'stapper_single_image',
            'stapper_multiple_image_1',
            'stapper_multiple_image_2',
            'stapper_multiple_image_3',
            'stapper_multiple_image_4'
        ));
    }

    public function categories()
    {
        $keys = ['category_section_status', 'category_section_title', 'category_section_sub_title'];
        $reactHeaders = $this->getLandingPageData('react_landing_page', $keys);
        $category_section_status = $reactHeaders['category_section_status'] ?? null;
        $category_section_title = $reactHeaders['category_section_title'] ?? null;
        $category_section_sub_title = $reactHeaders['category_section_sub_title'] ?? null;
        $language = Helpers::get_business_settings('language');

        return view('admin-views.landing_page.react.category', compact('category_section_status', 'category_section_title', 'category_section_sub_title', 'language'));
    }

    public function gallery()
    {
        $keys = [
            'gallery_section_status',
            'gallery_section_title',
            'gallery_section_sub_title',
            'gallery_image_1',
            'gallery_image_2',
            'gallery_image_3',
            'gallery_image_4',
            'gallery_image_5',
            'gallery_image_6'
        ];
        $reactHeaders = $this->getLandingPageData('react_landing_page', $keys);
        $gallery_section_status = $reactHeaders['gallery_section_status'] ?? null;
        $gallery_section_title = $reactHeaders['gallery_section_title'] ?? null;
        $gallery_section_sub_title = $reactHeaders['gallery_section_sub_title'] ?? null;
        $gallery_image_1 = $reactHeaders['gallery_image_1'] ?? null;
        $gallery_image_2 = $reactHeaders['gallery_image_2'] ?? null;
        $gallery_image_3 = $reactHeaders['gallery_image_3'] ?? null;
        $gallery_image_4 = $reactHeaders['gallery_image_4'] ?? null;
        $gallery_image_5 = $reactHeaders['gallery_image_5'] ?? null;
        $gallery_image_6 = $reactHeaders['gallery_image_6'] ?? null;


        $language = Helpers::get_business_settings('language');

        return view('admin-views.landing_page.react.gallery', compact(
            'gallery_section_status',
            'gallery_section_title',
            'gallery_section_sub_title',
            'language',
            'gallery_image_1',
            'gallery_image_2',
            'gallery_image_3',
            'gallery_image_4',
            'gallery_image_5',
            'gallery_image_6'
        ));
    }

    public function testimonials(Request $request)
    {

        $keys = ['testimonial_section_status', 'testimonial_section_title',];
        $reactHeaders = $this->getLandingPageData('react_landing_page', $keys);
        $testimonial_section_status = $reactHeaders['testimonial_section_status'] ?? null;
        $testimonial_section_title = $reactHeaders['testimonial_section_title'] ?? null;

        $key = explode(' ', $request['search']);
        $testimonials = ReactTestimonial::latest()->with('storage')
            ->when(isset($request['search']), function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->paginate(config('default_pagination'));
        $language = Helpers::get_business_settings('language');

        return view('admin-views.landing_page.react.testimonials', compact('testimonial_section_status', 'testimonial_section_title', 'language', 'testimonials'));
    }

    public function faqSection(Request $request)
    {

        $keys = ['faq_section_status', 'faq_section_title', 'faq_section_sub_title'];
        $reactHeaders = $this->getLandingPageData('react_landing_page', $keys);
        $faq_section_status = $reactHeaders['faq_section_status'] ?? null;
        $faq_section_title = $reactHeaders['faq_section_title'] ?? null;
        $faq_section_sub_title = $reactHeaders['faq_section_sub_title'] ?? null;

        $key = explode(' ', $request['search']);
        $faqs = FAQ::latest()
            ->where('page_type', 'react_landing_page')
            ->when(isset($request['search']), function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('question', 'like', "%{$value}%")->orWhere('answer', 'like', "%{$value}%");
                    }
                });
            })
            ->paginate(config('default_pagination'));
        $language = Helpers::get_business_settings('language');

        return view('admin-views.landing_page.react.faq', compact('faq_section_status', 'faq_section_title', 'faq_section_sub_title', 'language', 'faqs'));
    }


    public function react_fixed_data()
    {
        $news_letter_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'news_letter_title')->first() ?? null;
        $news_letter_sub_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'news_letter_sub_title')->first() ?? null;
        $footer_data = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'footer_data')->first() ?? null;
        // $copyright_text=DataSetting::withoutGlobalScope('translate')->with('translations')->where('type','react_landing_page')->where('key','copyright_text')->first() ?? null;
        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;

        return view('admin-views.landing_page.react.fixed_data', compact('news_letter_title', 'news_letter_sub_title', 'footer_data', 'language'));
    }

    public function pageMetaData(Request $request)
    {

        $pages = Helpers::seoPageList();

        if ($request->page_name && in_array($request->page_name, $pages)) {
            $language = Helpers::get_business_settings('language');
            $pageMetaData = PageSeoData::where('page_name', $request->page_name)->first();
            return view('admin-views.landing_page.react.page_seo_data', compact('language', 'pageMetaData'));
        }

        $pageMetaData = PageSeoData::whereIn('page_name', $pages)->select(['id', 'page_name'])->get()->groupBy('page_name');
        return view('admin-views.landing_page.react.page_meta_data', compact('pages', 'pageMetaData'));
    }

    public function pageMetaDataUpdate(Request $request)
    {

        $pages = Helpers::seoPageList();

        if ($request->page_name && in_array($request->page_name, $pages)) {
            $pageMetaData = PageSeoData::firstOrNew([
                'page_name' => $request->page_name,
            ]);

            $imageFile = $request->hasFile('image') ? $request->file('image') : $pageMetaData->image;
            $originalExtension = $request->hasFile('image') ? $imageFile->getClientOriginalExtension() : 'png';

            $pageMetaData->title = $request->title[array_search('default', $request->lang)];
            $pageMetaData->description = $request->description[array_search('default', $request->lang)];
            $pageMetaData->image = $request->file('image') ? Helpers::upload(dir: 'page_meta_data/', format: $originalExtension, image: $imageFile) : $pageMetaData->image;
            $pageMetaData->meta_data = Helpers::formatMetaData($request->all(), $pageMetaData->meta_data);

            $slug = Str::slug($pageMetaData->title);
            $pageMetaData->slug = $pageMetaData->slug ?: "{$slug}{$pageMetaData->id}";
            $pageMetaData->save();

            // Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'PageSeoData', data_id: $pageMetaData->id, data_value: $pageMetaData->title);
            // Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'PageSeoData', data_id: $pageMetaData->id, data_value: $pageMetaData->description);
            Toastr::success(translate('messages.page_meta_data_updated_successfully'));
        } else {
            Toastr::error(translate('messages.invalid_page_name'));
        }

        return redirect()->route('admin.pageMetaData');

    }

    public function react_meta_data()
    {
        $keys = [
            'react_meta_data_title',
            'react_meta_data_description',
            'react_meta_data_image',
            'react_meta_index',
            'react_meta_no_follow',
            'react_meta_no_image_index',
            'react_meta_no_archive',
            'react_meta_no_snippet',
            'react_meta_max_snippet',
            'react_meta_max_snippet_value',
            'react_meta_max_video_preview',
            'react_meta_max_video_preview_value',
            'react_meta_max_image_preview',
            'react_meta_max_image_preview_value',
        ];
        $reactHeaders = $this->getLandingPageData('react_landing_page', $keys);

        $meta_data_title = $reactHeaders['react_meta_data_title'] ?? null;
        $meta_data_description = $reactHeaders['react_meta_data_description'] ?? null;
        $meta_data_image = $reactHeaders['react_meta_data_image'] ?? null;

        $meta_index = $reactHeaders['react_meta_index']->value ?? null;
        $meta_no_follow = $reactHeaders['react_meta_no_follow']->value ?? null;
        $meta_no_image_index = $reactHeaders['react_meta_no_image_index']->value ?? null;
        $meta_no_archive = $reactHeaders['react_meta_no_archive']->value ?? null;
        $meta_no_snippet = $reactHeaders['react_meta_no_snippet']->value ?? null;
        $meta_max_snippet = $reactHeaders['react_meta_max_snippet']->value ?? null;
        $meta_max_snippet_value = $reactHeaders['react_meta_max_snippet_value']->value ?? null;
        $meta_max_video_preview = $reactHeaders['react_meta_max_video_preview']->value ?? null;
        $meta_max_video_preview_value = $reactHeaders['react_meta_max_video_preview_value']->value ?? null;
        $meta_max_image_preview = $reactHeaders['react_meta_max_image_preview']->value ?? null;
        $meta_max_image_preview_value = $reactHeaders['react_meta_max_image_preview_value']->value ?? null;

        $language = Helpers::get_business_settings('language');

        $commonKeys = [
            'meta_data_title',
            'meta_data_description',
            'meta_data_image',
            'meta_index',
            'meta_no_follow',
            'meta_no_image_index',
            'meta_no_archive',
            'meta_no_snippet',
            'meta_max_snippet',
            'meta_max_snippet_value',
            'meta_max_video_preview',
            'meta_max_video_preview_value',
            'meta_max_image_preview',
            'meta_max_image_preview_value',
            'language'
        ];

        return view('admin-views.landing_page.react.meta_data', compact($commonKeys));
    }

    public function react_services(Request $request)
    {
        $key = explode(' ', $request['search']);

        $type = ['react_service_status'];
        $reactService = $this->getLandingPageData('react_landing_page', $type);
        $react_service_status = $reactService['react_service_status'] ?? null;

        $services = ReactService::latest()->with('storage')
            ->when(isset($request['search']), function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('title', 'like', "%{$value}%");
                    }
                });
            })
            ->paginate(config('default_pagination'));
        return view('admin-views.landing_page.react.services', compact('services', 'react_service_status'));
    }

    public function service_export(Request $request)
    {
        $key = explode(' ', $request['search']);

        $services = ReactService::latest()->with('translations')
            ->when(isset($request['search']), function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('title', 'like', "%{$value}%");
                    }
                });
            })
            ->get();
        if ($request->type == 'csv') {
            return (new FastExcel(Helpers::react_services_formater($services)))->download('React_services.csv');
        }
        return (new FastExcel(Helpers::react_services_formater($services)))->download('React_services.xlsx');
    }

    public function react_service_store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:100',
            'sub_title' => 'required|max:254',
            'image' => 'required|image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
        ]);


        if ($request->title[array_search('default', $request->lang)] == '' || $request->sub_title[array_search('default', $request->lang)] == '') {
            Toastr::error(translate('default_title_and_subtitle_is_required'));
            return back();
        }

        $imageFile = $request->file('image');
        $originalExtension = 'png';

        $ReactService = new ReactService();
        $ReactService->title = $request->title[array_search('default', $request->lang)];
        $ReactService->sub_title = $request->sub_title[array_search('default', $request->lang)];
        $ReactService->image = Helpers::upload(dir: 'react_service_image/', format: $originalExtension, image: $imageFile);
        $ReactService->save();

        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ReactService',
                            'translationable_id' => $ReactService->id,
                            'locale' => $key,
                            'key' => 'react_service_title'
                        ],
                        ['value' => $ReactService->title]
                    );
                }
            } else {
                if ($request->title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ReactService',
                            'translationable_id' => $ReactService->id,
                            'locale' => $key,
                            'key' => 'react_service_title'
                        ],
                        ['value' => $request->title[$index]]
                    );
                }
            }
            if ($default_lang == $key && !($request->sub_title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ReactService',
                            'translationable_id' => $ReactService->id,
                            'locale' => $key,
                            'key' => 'react_service_sub_title'
                        ],
                        ['value' => $ReactService->sub_title]
                    );
                }
            } else {
                if ($request->sub_title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ReactService',
                            'translationable_id' => $ReactService->id,
                            'locale' => $key,
                            'key' => 'react_service_sub_title'
                        ],
                        ['value' => $request->sub_title[$index]]
                    );
                }
            }

        }

        Toastr::success(translate('messages.React_service_added_successfully'));
        return back();
    }

    public function react_service_status(Request $request)
    {
        if (getEnvMode() == 'demo' && $request->id == 1) {
            Toastr::warning('Sorry!You can not inactive this review!');
            return back();
        }
        $ReactService = ReactService::findOrFail($request->id);
        $ReactService->status = $request->status;
        $ReactService->save();
        Toastr::success(translate('messages.React_service_status_updated'));
        return back();
    }

    public function react_service_edit($id)
    {
        $service = ReactService::withoutGlobalScope('translate')->with('translations')->findOrFail($id);
        return view('admin-views.landing_page.react.service_edit', compact('service'));
    }

    public function react_service_update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|max:100',
            'sub_title' => 'required|max:254',
            'image' => 'nullable|max:2048',
        ]);


        if ($request->title[array_search('default', $request->lang)] == '' || $request->sub_title[array_search('default', $request->lang)] == '') {
            Toastr::error(translate('default_title_and_subtitle_is_required'));
            return back();
        }


        $ReactService = ReactService::findOrFail($id);

        $ReactService->title = $request->title[array_search('default', $request->lang)];
        $ReactService->sub_title = $request->sub_title[array_search('default', $request->lang)];
        $ReactService->image = $request->has('image') ? Helpers::update(dir: 'react_service_image/', old_image: $ReactService->image, format: 'png', image: $request->file('image')) : $ReactService->image;
        $ReactService->save();

        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ReactService',
                            'translationable_id' => $ReactService->id,
                            'locale' => $key,
                            'key' => 'react_service_title'
                        ],
                        ['value' => $ReactService->title]
                    );
                }
            } else {
                if ($request->title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ReactService',
                            'translationable_id' => $ReactService->id,
                            'locale' => $key,
                            'key' => 'react_service_title'
                        ],
                        ['value' => $request->title[$index]]
                    );
                }
            }
            if ($default_lang == $key && !($request->sub_title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ReactService',
                            'translationable_id' => $ReactService->id,
                            'locale' => $key,
                            'key' => 'react_service_sub_title'
                        ],
                        ['value' => $ReactService->sub_title]
                    );
                }
            } else {
                if ($request->sub_title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ReactService',
                            'translationable_id' => $ReactService->id,
                            'locale' => $key,
                            'key' => 'react_service_sub_title'
                        ],
                        ['value' => $request->sub_title[$index]]
                    );
                }
            }
        }
        Toastr::success(translate('messages.React_service_updated_successfully'));
        return back();
    }

    public function react_service_destroy(ReactService $service)
    {
        if (getEnvMode() == 'demo' && $service->id == 1) {
            Toastr::warning(translate('messages.you_can_not_delete_this_review_please_add_a_new_review_to_delete'));
            return back();
        }

        Helpers::check_and_delete('react_service_image/', $service->image);

        $service?->translations()?->delete();
        $service?->delete();
        Toastr::success(translate('messages.React_service_deleted_successfully'));
        return back();
    }


    public function react_promotional_banner(Request $request)
    {
        $key = explode(' ', $request['search']);
        $type = ['react_promotional_banner_status'];
        $reactBanner = $this->getLandingPageData('react_landing_page', $type);
        $react_promotional_banner_status = $reactBanner['react_promotional_banner_status'] ?? null;

        $react_promotional_banners = ReactPromotionalBanner::latest()->with(['translations', 'storage'])
            ->when(isset($request['search']), function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('title', 'like', "%{$value}%");
                    }
                });
            })
            ->paginate(config('default_pagination'));
        return view('admin-views.landing_page.react.promotional_banner', compact('react_promotional_banners', 'react_promotional_banner_status'));
    }

    public function react_promotional_banner_store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
        ]);

        $imageFile = $request->file('image');
        $originalExtension = 'png';

        $react_promotional_banner = new ReactPromotionalBanner();
        $react_promotional_banner->image = Helpers::upload(dir: 'react_promotional_banner/', format: $originalExtension, image: $imageFile);
        $react_promotional_banner->save();

        Toastr::success(translate('messages.React_promotional_banner_added_successfully'));
        return back();
    }

    public function react_promotional_banner_status(Request $request)
    {
        if (getEnvMode() == 'demo' && $request->id == 1) {
            Toastr::warning('Sorry!You can not inactive this review!');
            return back();
        }
        $ReactPromotionalBanner = ReactPromotionalBanner::findOrFail($request->id);
        $ReactPromotionalBanner->status = $request->status;
        $ReactPromotionalBanner->save();
        Toastr::success(translate('messages.React_promotional_banner_status_updated'));
        return back();
    }

    public function react_promotional_banner_update(Request $request, $id)
    {
        $request->validate([
            'image' => 'image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
        ]);

        if ($request->has('image')) {
            $imageFile = $request->file('image');
            $originalExtension = 'png';
        }

        $ReactPromotionalBanner = ReactPromotionalBanner::findOrFail($id);
        $ReactPromotionalBanner->image = $request->has('image') ? Helpers::update(dir: 'react_promotional_banner/', old_image: $ReactPromotionalBanner->image, format: $originalExtension, image: $imageFile) : $ReactPromotionalBanner->image;
        $ReactPromotionalBanner->save();

        Toastr::success(translate('messages.React_promotional_banner_updated_successfully'));
        return back();
    }

    public function react_promotional_banner_destroy(ReactPromotionalBanner $react_promotional_banner)
    {
        if (getEnvMode() == 'demo' && $react_promotional_banner->id == 1) {
            Toastr::warning(translate('messages.you_can_not_delete_this_review_please_add_a_new_review_to_delete'));
            return back();
        }

        Helpers::check_and_delete('react_promotional_banner/', $react_promotional_banner->image);

        $react_promotional_banner?->translations()?->delete();
        $react_promotional_banner?->delete();
        Toastr::success(translate('messages.React_promotional_banner_deleted_successfully'));
        return back();
    }

    public function react_promotional_banners_export(Request $request)
    {
        $key = explode(' ', $request['search']);

        $react_promotional_banner = ReactPromotionalBanner::latest()->with('translations')
            ->when(isset($request['search']), function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('title', 'like', "%{$value}%");
                    }
                });
            })
            ->get();
        if ($request->type == 'csv') {
            return (new FastExcel(Helpers::react_react_promotional_banner_formater($react_promotional_banner)))->download('React_react_promotional_banner.csv');
        }
        return (new FastExcel(Helpers::react_react_promotional_banner_formater($react_promotional_banner)))->download('React_react_promotional_banner.xlsx');
    }


    public function registration_section()
    {
        $type = [
            'react_registration_section_status',
            'react_earn_money_section_title',
            'react_earn_money_section_description',
            'react_restaurant_registration_button_status',
            'react_restaurant_app_download_status',
            'react_restaurant_app_download_link',
            'react_restaurant_app_download_link_for_ios',
            'react_restaurant_app_download_title',
            'react_restaurant_app_download_sub_title',
            'react_delivery_registration_button_status',
            'react_delivery_app_download_status',
            'react_delivery_app_download_link',
            'react_delivery_app_download_title',
            'react_delivery_app_download_sub_title',
            'react_delivery_app_download_link_for_ios',
            'react_restaurant_section_title',
            'react_restaurant_section_sub_title',
            'react_restaurant_section_image',
            'react_restaurant_section_button_status',
            'react_restaurant_section_button_name',
            'react_restaurant_section_link_data',
            'react_delivery_section_title',
            'react_delivery_section_sub_title',
            'react_delivery_section_image',
            'react_delivery_section_button_status',
            'react_delivery_section_button_name',
            'react_delivery_section_link_data'
        ];

        $reactRegistration = $this->getLandingPageData('react_landing_page', $type);
        $react_registration_section_status = $reactRegistration['react_registration_section_status'] ?? null;
        $react_earn_money_section_title = $reactRegistration['react_earn_money_section_title'] ?? null;
        $react_earn_money_section_description = $reactRegistration['react_earn_money_section_description'] ?? null;
        $react_restaurant_registration_button_status = $reactRegistration['react_restaurant_registration_button_status'] ?? null;

        $react_restaurant_app_download_title = $reactRegistration['react_restaurant_app_download_title'] ?? null;
        $react_restaurant_app_download_sub_title = $reactRegistration['react_restaurant_app_download_sub_title'] ?? null;
        $react_restaurant_app_download_link = $reactRegistration['react_restaurant_app_download_link'] ?? null;
        $react_restaurant_app_download_link_for_ios = $reactRegistration['react_restaurant_app_download_link_for_ios'] ?? null;
        $react_restaurant_app_download_status = $reactRegistration['react_restaurant_app_download_status'] ?? null;

        $react_delivery_registration_button_status = $reactRegistration['react_delivery_registration_button_status'] ?? null;
        $react_delivery_app_download_title = $reactRegistration['react_delivery_app_download_title'] ?? null;
        $react_delivery_app_download_sub_title = $reactRegistration['react_delivery_app_download_sub_title'] ?? null;
        $react_delivery_app_download_link = $reactRegistration['react_delivery_app_download_link'] ?? null;
        $react_delivery_app_download_link_for_ios = $reactRegistration['react_delivery_app_download_link_for_ios'] ?? null;
        $react_delivery_app_download_status = $reactRegistration['react_delivery_app_download_status'] ?? null;

        $react_restaurant_section_title = $reactRegistration['react_restaurant_section_title'] ?? null;
        $react_restaurant_section_sub_title = $reactRegistration['react_restaurant_section_sub_title'] ?? null;
        $react_restaurant_section_image = $reactRegistration['react_restaurant_section_image'] ?? null;
        $react_restaurant_section_button_status = $reactRegistration['react_restaurant_section_button_status'] ?? null;
        $react_restaurant_section_button_name = $reactRegistration['react_restaurant_section_button_name'] ?? null;
        $react_restaurant_section_link_data = $reactRegistration['react_restaurant_section_link_data'] ?? null;

        $react_delivery_section_title = $reactRegistration['react_delivery_section_title'] ?? null;
        $react_delivery_section_sub_title = $reactRegistration['react_delivery_section_sub_title'] ?? null;
        $react_delivery_section_image = $reactRegistration['react_delivery_section_image'] ?? null;
        $react_delivery_section_button_status = $reactRegistration['react_delivery_section_button_status'] ?? null;
        $react_delivery_section_button_name = $reactRegistration['react_delivery_section_button_name'] ?? null;
        $react_delivery_section_link_data = $reactRegistration['react_delivery_section_link_data'] ?? null;


        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;

        return view('admin-views.landing_page.react.registration_section', compact(
            'react_restaurant_section_title',
            'react_restaurant_section_sub_title',
            'react_restaurant_section_button_name',
            'react_restaurant_section_image',
            'react_restaurant_section_button_status',
            'react_restaurant_section_link_data',
            'react_delivery_section_title',
            'react_delivery_section_sub_title',
            'language',
            'react_delivery_section_button_status',
            'react_delivery_section_button_name',
            'react_delivery_section_link_data',
            'react_registration_section_status',
            'react_earn_money_section_title',
            'react_earn_money_section_description',
            'react_restaurant_registration_button_status',
            'react_restaurant_app_download_title',
            'react_restaurant_app_download_sub_title',
            'react_restaurant_app_download_link',
            'react_restaurant_app_download_link_for_ios',
            'react_restaurant_app_download_status',
            'react_delivery_registration_button_status',
            'react_delivery_app_download_title',
            'react_delivery_app_download_sub_title',
            'react_delivery_app_download_link',
            'react_delivery_app_download_status',
            'react_delivery_section_image',
            'react_delivery_app_download_link_for_ios'
        ));
    }


    public function download_apps()
    {

        $type = ['react_download_apps_status'];
        $reactApp = $this->getLandingPageData('react_landing_page', $type);
        $react_download_apps_status = $reactApp['react_download_apps_status'] ?? null;

        $react_download_apps_banner_image = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'react_download_apps_banner_image')->first() ?? null;
        $react_download_apps_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'react_download_apps_title')->first() ?? null;
        $react_download_apps_sub_title = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'react_download_apps_sub_title')->first() ?? null;
        $react_download_apps_tag = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'react_download_apps_tag')->first() ?? null;
        $react_download_apps_image = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'react_download_apps_image')->first() ?? null;
        $react_download_apps_button_status = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'react_download_apps_button_status')->first() ?? null;
        $react_download_apps_button_name = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'react_download_apps_button_name')->first() ?? null;
        $react_download_apps_link_data = DataSetting::withoutGlobalScope('translate')->with('translations')->where('type', 'react_landing_page')->where('key', 'react_download_apps_link_data')->first() ?? null;
        $react_download_apps_link_data = json_decode($react_download_apps_link_data?->value ?? null, true);
        $language = BusinessSetting::where('key', 'language')->first()?->value ?? null;
        $app_url_android = BusinessSetting::where(['key' => 'app_url_android'])->first()?->value ?? null;
        $app_url_ios = BusinessSetting::where(['key' => 'app_url_ios'])->first()?->value ?? null;

        return view('admin-views.landing_page.react.download_apps', compact(
            'app_url_android',
            'app_url_ios',
            'react_download_apps_banner_image',
            'react_download_apps_title',
            'react_download_apps_sub_title',
            'react_download_apps_image',
            'react_download_apps_button_status',
            'react_download_apps_button_name',
            'react_download_apps_link_data',
            'react_download_apps_tag',
            'language',
            'react_download_apps_status'
        ));
    }

    public function update_react_landing_page_settings(Request $request, $tab)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));
            return back();
        } elseif ($tab == 'fixed-data-newsletter') {
            $request->validate([
                'title' => 'required|max:254',
                'sub_title' => 'required|max:254',
            ]);

            if ($request->title[array_search('default', $request->lang)] == '' || $request->sub_title[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_title_and_subtitle_is_required'));
                return back();
            }

            $this->update_data($request, $request->key, 'title', 'react_landing_page');
            $this->update_data($request, $request->key_2, 'sub_title', 'react_landing_page');

            Toastr::success(translate('messages.React_landing_page_newsletter_updated'));
            return back();
        } elseif ($tab == 'fixed-data-footer') {
            $request->validate([
                'footer_data' => 'required|max:1000',
                // 'copyright_text' => 'nullable|max:100',
            ]);


            if ($request->footer_data[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_footer_descrtption_is_required'));
                return back();
            }


            $this->update_data($request, $request->footer_key, 'footer_data', 'react_landing_page');
            // $this->update_data($request , $request->key_copyright , 'copyright_text', 'react_landing_page');

            Toastr::success(translate('messages.React_landing_page_footer_description_updated'));
            return back();
        } elseif ($tab == 'react-header') {
            $request->validate([
                'react_header_title' => 'required|max:254',
                'react_header_sub_title' => 'required|max:254',
                'react_header_image' => 'nullable|image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
            ]);

            if ($request->react_header_title[array_search('default', $request->lang)] == '' || $request->react_header_sub_title[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_title_and_subtitle_is_required'));
                return back();
            }


            $react_header_title = DataSetting::where('type', 'react_landing_page')->where('key', 'react_header_title')->first();
            if ($react_header_title == null) {
                $react_header_title = new DataSetting();
            }
            $react_header_title->key = 'react_header_title';
            $react_header_title->type = 'react_landing_page';
            $react_header_title->value = $request->react_header_title[array_search('default', $request->lang)];
            $react_header_title->save();

            $react_header_sub_title = DataSetting::where('type', 'react_landing_page')->where('key', 'react_header_sub_title')->first();
            if ($react_header_sub_title == null) {
                $react_header_sub_title = new DataSetting();
            }
            $react_header_sub_title->key = 'react_header_sub_title';
            $react_header_sub_title->type = 'react_landing_page';
            $react_header_sub_title->value = $request->react_header_sub_title[array_search('default', $request->lang)];
            $react_header_sub_title->save();


            // Handle header image update/removal on Save
            $react_header_image = DataSetting::where('type', 'react_landing_page')->where('key', 'react_header_image')->first();
            if ($request->has('react_header_image')) {
                $imageFile = $request->file('react_header_image');
                $originalExtension = 'png';
                $imageName1 = $react_header_image?->value ?? null;
                if ($react_header_image == null) {
                    $react_header_image = new DataSetting();
                }
                $react_header_image->key = 'react_header_image';
                $react_header_image->type = 'react_landing_page';
                if (empty($imageName1)) {
                    $imageName1 = Helpers::upload(dir: 'react_header/', format: $originalExtension, image: $imageFile);
                } else {
                    $imageName1 = Helpers::update(dir: 'react_header/', old_image: $imageName1, format: $originalExtension, image: $imageFile);
                }
                $react_header_image->value = $imageName1;
                $react_header_image->save();
            } elseif ($request->react_header_image_remove == '1') {
                // Marked for removal: delete on Save
                if ($react_header_image && $react_header_image->value) {
                    Helpers::check_and_delete('react_header/', $react_header_image->value);
                    $react_header_image->value = null;
                    $react_header_image->save();
                }
            }


            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->react_header_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $react_header_title->id,
                                'locale' => $key,
                                'key' => 'react_header_title'
                            ],
                            ['value' => $react_header_title->value]
                        );
                    }
                } else {
                    if ($request->react_header_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $react_header_title->id,
                                'locale' => $key,
                                'key' => 'react_header_title'
                            ],
                            ['value' => $request->react_header_title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->react_header_sub_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $react_header_sub_title->id,
                                'locale' => $key,
                                'key' => 'react_header_sub_title'
                            ],
                            ['value' => $react_header_sub_title->value]
                        );
                    }
                } else {
                    if ($request->react_header_sub_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $react_header_sub_title->id,
                                'locale' => $key,
                                'key' => 'react_header_sub_title'
                            ],
                            ['value' => $request->react_header_sub_title[$index]]
                        );
                    }
                }

            }
            Toastr::success(translate('messages.React_landing_page_header_updated'));
            return back();
        } elseif ($tab == 'react-header-location-picker') {
            $request->validate([
                'header_location_picker_title' => 'required|max:254',

            ]);

            if ($request->header_location_picker_title[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_title_is_required'));
                return back();
            }

            $header_location_picker_title = DataSetting::firstOrNew([
                'type' => 'react_landing_page',
                'key' => 'header_location_picker_title',
            ]);
            $header_location_picker_title->value = $request->header_location_picker_title[array_search('default', $request->lang)];
            $header_location_picker_title->save();


            Helpers::add_or_update_translations(request: $request, key_data: 'header_location_picker_title', name_field: 'header_location_picker_title', model_name: 'DataSetting', data_id: $header_location_picker_title->id, data_value: $header_location_picker_title->value);


            Toastr::success(translate('messages.React_landing_page_location_picker_content_updated'));
            return back();
        } elseif ($tab == 'react-header-floating-icon') {
            $floating_icon_restaurant = DataSetting::firstOrNew([
                'type' => 'react_landing_page',
                'key' => 'floating_icon_restaurant',
            ]);
            $floating_icon_restaurant->value = $request->floating_icon_restaurant ?? 0;
            $floating_icon_restaurant->save();

            $floating_icon_customer = DataSetting::firstOrNew([
                'type' => 'react_landing_page',
                'key' => 'floating_icon_customer',
            ]);
            $floating_icon_customer->value = $request->floating_icon_customer ?? 0;
            $floating_icon_customer->save();

            $floating_icon_average_delivery = DataSetting::firstOrNew([
                'type' => 'react_landing_page',
                'key' => 'floating_icon_average_delivery',
            ]);
            $floating_icon_average_delivery->value = $request->floating_icon_average_delivery ?? 0;
            $floating_icon_average_delivery->save();

            Toastr::success(translate('messages.React_landing_page_floating_icon_updated'));
            return back();
        } elseif ($tab == 'react-regisrtation-section-content') {
            $request->validate([
                'react_restaurant_section_title.0' => 'required_if:react_restaurant_registration_button_status,1|max:254',
                'react_restaurant_section_sub_title.0' => 'required_if:react_restaurant_registration_button_status,1||max:254',
                'react_restaurant_section_button_name.0' => 'required_if:react_restaurant_registration_button_status,1|max:254',

                'react_restaurant_app_download_title.0' => 'required_if:react_restaurant_app_download_status,1|max:254',
                'react_restaurant_app_download_sub_title.0' => 'required_if:react_restaurant_app_download_status,1|max:254',
                'react_restaurant_app_download_link' => 'required_if:react_restaurant_app_download_status,1',
                'react_restaurant_app_download_link_for_ios' => 'required_if:react_restaurant_app_download_status,1',

            ], [
                'react_restaurant_section_title.0.required_if' => translate('default_registration_button_title_is_required'),
                'react_restaurant_section_sub_title.0.required_if' => translate('default_registration_button_sub_title_is_required'),
                'react_restaurant_section_button_name.0.required_if' => translate('default_registration_button_button_name_is_required'),

                'react_restaurant_app_download_title.0.required_if' => translate('default_restaurant_app_download_title_is_required'),
                'react_restaurant_app_download_sub_title.0.required_if' => translate('default_restaurant_app_download_sub_title_is_required'),
                'react_restaurant_app_download_link.required_if' => translate('restaurant_app_download_link_is_required'),
                'react_restaurant_app_download_link_for_ios.required_if' => translate('restaurant_app_download_link_is_required'),
            ]);

            $this->getAddLandingPageData($request, 'react_landing_page', 'react_restaurant_section_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_restaurant_section_sub_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_restaurant_section_button_name', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_restaurant_registration_button_status', false);

            if ($request->react_restaurant_section_image_remove == '1') {
                $existing = DataSetting::where('type', 'react_landing_page')
                    ->where('key', 'react_restaurant_section_image')
                    ->first();

                if ($existing) {
                    Helpers::check_and_delete('react_restaurant_section_image', $existing->value);
                    $existing->delete();
                }
            }

            if ($request->hasFile('react_restaurant_section_image')) {
                $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_restaurant_section_image', multiLang: false, filePath: 'react_restaurant_section_image');
            }

            $this->getAddLandingPageData($request, 'react_landing_page', 'react_restaurant_app_download_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_restaurant_app_download_sub_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_restaurant_app_download_link', false);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_restaurant_app_download_link_for_ios', false);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_restaurant_app_download_status', false);


            Toastr::success(translate('messages.React_registration_section_updated'));
            return back();
        } elseif ($tab == 'react-deliveryman-section-content') {
            $request->validate([
                'react_delivery_section_title.0' => 'required_if:react_delivery_registration_button_status,1|max:254',
                'react_delivery_section_sub_title.0' => 'required_if:react_delivery_registration_button_status,1|max:254',
                'react_delivery_section_button_name.0' => 'required_if:react_delivery_registration_button_status,1|max:254',

                'react_delivery_app_download_title.0' => 'required_if:react_delivery_app_download_status,1|max:254',
                'react_delivery_app_download_sub_title.0' => 'required_if:react_delivery_app_download_status,1|max:254',
                'react_delivery_app_download_link' => 'required_if:react_delivery_app_download_status,1',
                'react_delivery_app_download_link_for_ios' => 'required_if:react_delivery_app_download_status,1',

            ], [
                'react_delivery_section_title.0.required' => translate('default_registration_button_title_is_required'),
                'react_delivery_section_sub_title.0.required' => translate('default_registration_button_sub_title_is_required'),
                'react_delivery_section_button_name.0.required' => translate('default_registration_button_button_name_is_required'),
                'react_delivery_app_download_title.0.required' => translate('default_restaurant_app_download_title_is_required'),
                'react_delivery_app_download_sub_title.0.required' => translate('default_restaurant_app_download_sub_title_is_required'),
                'react_delivery_app_download_link.required' => translate('restaurant_app_download_link_is_required'),
                'react_delivery_app_download_link_for_ios.required' => translate('restaurant_app_download_link_is_required'),
            ]);

            $this->getAddLandingPageData($request, 'react_landing_page', 'react_delivery_section_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_delivery_section_sub_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_delivery_section_button_name', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_delivery_registration_button_status', false);

            if ($request->react_delivery_section_image_remove == '1') {
                $existing = DataSetting::where('type', 'react_landing_page')
                    ->where('key', 'react_delivery_section_image')
                    ->first();

                if ($existing) {
                    Helpers::check_and_delete('react_delivery_section_image', $existing->value);
                    $existing->delete();
                }
            }

            if ($request->hasFile('react_delivery_section_image')) {
                $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_delivery_section_image', multiLang: false, filePath: 'react_delivery_section_image');
            }

            $this->getAddLandingPageData($request, 'react_landing_page', 'react_delivery_app_download_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_delivery_app_download_sub_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_delivery_app_download_link', false);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_delivery_app_download_link_for_ios', false);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_delivery_app_download_status', false);


            Toastr::success(translate('messages.React_registration_section_updated'));
            return back();
        } elseif ($tab == 'react-download-apps') {
            $request->validate([
                'react_download_apps_title' => 'required|max:254',
                'react_download_apps_sub_title' => 'required|max:254',
                //                'react_download_apps_tag' => 'required|max:254',
                'react_download_apps_image' => 'nullable|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
            ]);


            if (
                $request->react_download_apps_title[array_search('default', $request->lang)] == '' ||
                $request->react_download_apps_sub_title[array_search('default', $request->lang)] == ''
                //                || $request->react_download_apps_tag[array_search('default', $request->lang)] == ''
            ) {
                Toastr::error(translate('default_title_subtitle_and_tagline_is_required'));
                return back();
            }

            $react_download_apps_title = DataSetting::where('type', 'react_landing_page')->where('key', 'react_download_apps_title')->first();
            if ($react_download_apps_title == null) {
                $react_download_apps_title = new DataSetting();
            }
            $react_download_apps_title->key = 'react_download_apps_title';
            $react_download_apps_title->type = 'react_landing_page';
            $react_download_apps_title->value = $request->react_download_apps_title[array_search('default', $request->lang)];
            $react_download_apps_title->save();

            $react_download_apps_sub_title = DataSetting::where('type', 'react_landing_page')->where('key', 'react_download_apps_sub_title')->first();
            if ($react_download_apps_sub_title == null) {
                $react_download_apps_sub_title = new DataSetting();
            }
            $react_download_apps_sub_title->key = 'react_download_apps_sub_title';
            $react_download_apps_sub_title->type = 'react_landing_page';
            $react_download_apps_sub_title->value = $request->react_download_apps_sub_title[array_search('default', $request->lang)];
            $react_download_apps_sub_title->save();

            $react_download_apps_tag = DataSetting::where('type', 'react_landing_page')->where('key', 'react_download_apps_tag')->first();
            if ($react_download_apps_tag == null) {
                $react_download_apps_tag = new DataSetting();
            }
            $react_download_apps_tag->key = 'react_download_apps_tag';
            $react_download_apps_tag->type = 'react_landing_page';
            //            $react_download_apps_tag->value = $request->react_download_apps_tag[array_search('default', $request->lang)];
            $react_download_apps_tag->save();

            $react_download_apps_button_name = DataSetting::where('type', 'react_landing_page')->where('key', 'react_download_apps_button_name')->first();
            if ($react_download_apps_button_name == null) {
                $react_download_apps_button_name = new DataSetting();
            }
            $react_download_apps_button_name->key = 'react_download_apps_button_name';
            $react_download_apps_button_name->type = 'react_landing_page';
            $react_download_apps_button_name->value = $request->react_download_apps_button_name;
            $react_download_apps_button_name->save();

            $react_download_apps_button_status = DataSetting::where('type', 'react_landing_page')->where('key', 'react_download_apps_button_status')->first();
            if ($react_download_apps_button_status == null) {
                $react_download_apps_button_status = new DataSetting();
            }
            $react_download_apps_button_status->key = 'react_download_apps_button_status';
            $react_download_apps_button_status->type = 'react_landing_page';
            $react_download_apps_button_status->value = $request->react_download_apps_button_status ?? 0;
            $react_download_apps_button_status->save();


            if ($request->has('react_download_apps_image')) {

                $react_download_apps_image = DataSetting::where('type', 'react_landing_page')->where('key', 'react_download_apps_image')->first();
                $imageName1 = null;
                $imageFile = $request->file('react_download_apps_image');
                $originalExtension = 'png';
                if ($react_download_apps_image) {
                    $imageName1 = $react_download_apps_image?->value;
                }
                if ($react_download_apps_image == null) {
                    $react_download_apps_image = new DataSetting();
                }
                $react_download_apps_image->key = 'react_download_apps_image';
                $react_download_apps_image->type = 'react_landing_page';
                if (empty($imageName1)) {
                    $imageName1 = Helpers::upload(dir: 'react_download_apps_image/', format: $originalExtension, image: $imageFile);
                } else {
                    $imageName1 = Helpers::update(dir: 'react_download_apps_image/', old_image: $imageName1, format: $originalExtension, image: $imageFile);
                }
                $react_download_apps_image->value = $imageName1;
                $react_download_apps_image->save();
            }

            $react_download_apps_link_data = DataSetting::where('type', 'react_landing_page')->where('key', 'react_download_apps_link_data')->first();
            if ($react_download_apps_link_data == null) {
                $react_download_apps_link_data = new DataSetting();
            }

            $react_download_apps_link_data->key = 'react_download_apps_link_data';
            $react_download_apps_link_data->type = 'react_landing_page';

            $button_data = [
                'react_download_apps_link_status' => $request->react_download_apps_link_status ?? 0,
                'react_download_apps_link' => $request->react_download_apps_link,
            ];

            $react_download_apps_link_data->value = json_encode($button_data);
            $react_download_apps_link_data->save();


            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->react_download_apps_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $react_download_apps_title->id,
                                'locale' => $key,
                                'key' => 'react_download_apps_title'
                            ],
                            ['value' => $react_download_apps_title->value]
                        );
                    }
                } else {
                    if ($request->react_download_apps_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $react_download_apps_title->id,
                                'locale' => $key,
                                'key' => 'react_download_apps_title'
                            ],
                            ['value' => $request->react_download_apps_title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->react_download_apps_sub_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $react_download_apps_sub_title->id,
                                'locale' => $key,
                                'key' => 'react_download_apps_sub_title'
                            ],
                            ['value' => $react_download_apps_sub_title->value]
                        );
                    }
                } else {
                    if ($request->react_download_apps_sub_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $react_download_apps_sub_title->id,
                                'locale' => $key,
                                'key' => 'react_download_apps_sub_title'
                            ],
                            ['value' => $request->react_download_apps_sub_title[$index]]
                        );
                    }
                }
                if (
                    $default_lang == $key
                    //                    && !($request->react_download_apps_tag[$index])
                ) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $react_download_apps_tag->id,
                                'locale' => $key,
                                'key' => 'react_download_apps_tag'
                            ],
                            ['value' => $react_download_apps_tag->value]
                        );
                    }
                } else {
                    //                    if ($request->react_download_apps_tag[$index] && $key != 'default') {
//                        Translation::updateOrInsert(
//                            [
//                                'translationable_type' => 'App\Models\DataSetting',
//                                'translationable_id' => $react_download_apps_tag->id,
//                                'locale' => $key,
//                                'key' => 'react_download_apps_tag'
//                            ],
//                            ['value' => $request->react_download_apps_tag[$index]]
//                        );
//                    }
                }


            }
            Toastr::success(translate('messages.React_download_apps_section_updated'));
            return back();
        } elseif ($tab == 'react-download-apps-banner-image') {
            $request->validate([
                'react_download_apps_banner_image' => 'nullable|max:2048',
            ]);

            if ($request->has('react_download_apps_banner_image')) {

                $react_download_apps_banner_image = DataSetting::where('type', 'react_landing_page')->where('key', 'react_download_apps_banner_image')->first();
                $imageName1 = null;
                $imageFile = $request->file('react_download_apps_banner_image');
                $originalExtension = 'png';
                if ($react_download_apps_banner_image) {
                    $imageName1 = $react_download_apps_banner_image?->value;
                }
                if ($react_download_apps_banner_image == null) {
                    $react_download_apps_banner_image = new DataSetting();
                }
                $react_download_apps_banner_image->key = 'react_download_apps_banner_image';
                $react_download_apps_banner_image->type = 'react_landing_page';
                if (empty($imageName1)) {
                    $imageName1 = Helpers::upload(dir: 'react_download_apps_image/', format: $originalExtension, image: $imageFile);
                } else {
                    $imageName1 = Helpers::update(dir: 'react_download_apps_image/', old_image: $imageName1, format: $originalExtension, image: $imageFile);
                }
                $react_download_apps_banner_image->value = $imageName1;
                $react_download_apps_banner_image->save();
            }

            Toastr::success(translate('messages.React_download_apps_section_banner_image_updated'));
            return back();
        } elseif ($tab == 'stepper-section') {
            $request->validate([
                'stepper_title_1' => 'nullable|max:255',
                'stepper_title_2' => 'nullable|max:255',
                'stepper_title_3' => 'nullable|max:255',
                'stepper_title_4' => 'nullable|max:255',
                'stepper_1_image' => 'nullable|max:2048',
                'stepper_2_image' => 'nullable|max:2048',
                'stepper_3_image' => 'nullable|max:2048',
                'stepper_4_image' => 'nullable|max:2048',
            ]);

            $stepperKeys = [1, 2, 3, 4];

            foreach ($stepperKeys as $i) {
                $titleKey = "stepper_title_{$i}";
                $imageKey = "stepper_{$i}_image";
                $imageDeletedKey = "stepper_{$i}_image_deleted";

                if ($request->{$titleKey}) {
                    $this->getAddLandingPageData(
                        request: $request,
                        type: 'react_landing_page',
                        key: $titleKey,
                        multiLang: true
                    );
                }

                if ($request->hasFile($imageKey)) {
                    $this->getAddLandingPageData(
                        request: $request,
                        type: 'react_landing_page',
                        key: $imageKey,
                        multiLang: false,
                        filePath: 'react_stepper/'
                    );
                } elseif ($request->{$imageDeletedKey} == '1') {
                    // Find and delete the image record
                    $existing = DataSetting::where('type', 'react_landing_page')
                        ->where('key', $imageKey)
                        ->first();

                    if ($existing) {
                        Helpers::check_and_delete('react_stepper', $existing->value);
                        $existing->delete();
                    }
                }
            }

            Toastr::success(translate('messages.stepper_section_updated'));
            return back();
        } elseif ($tab == 'stepper-section-images') {
            $request->validate([
                'stepper_upload_image_type' => 'in:single,multiple',
                'stapper_single_image' => 'nullable|image|max:2028',
                'stapper_multiple_image_1' => 'nullable|image|max:2028',
                'stapper_multiple_image_2' => 'nullable|image|max:2028',
                'stapper_multiple_image_3' => 'nullable|image|max:2028',
                'stapper_multiple_image_4' => 'nullable|image|max:2028',
            ]);

            $filePath = 'react_stepper/';
            $type = $request->stepper_upload_image_type;

            // ✅ save image type
            $this->getAddLandingPageData(
                request: $request,
                type: 'react_landing_page',
                key: 'stepper_upload_image_type',
                multiLang: false
            );

            // ✅ single image
            if ($type === 'single') {
                if ($request->hasFile('stapper_single_image')) {
                    $this->getAddLandingPageData(
                        request: $request,
                        type: 'react_landing_page',
                        key: 'stapper_single_image',
                        multiLang: false,
                        filePath: $filePath
                    );
                } elseif ($request->stapper_single_image_deleted == '1') {
                    $existing = DataSetting::where('type', 'react_landing_page')
                        ->where('key', 'stapper_single_image')
                        ->first();
                    if ($existing) {
                        Helpers::check_and_delete($filePath, $existing->value);
                        $existing->delete();
                    }
                }
            } // ✅ multiple images
            elseif ($type === 'multiple') {
                foreach (range(1, 4) as $i) {
                    $key = "stapper_multiple_image_{$i}";
                    $deleteKey = "{$key}_deleted";

                    if ($request->hasFile($key)) {
                        $this->getAddLandingPageData(
                            request: $request,
                            type: 'react_landing_page',
                            key: $key,
                            multiLang: false,
                            filePath: $filePath
                        );
                    } elseif ($request->{$deleteKey} == '1') {
                        $existing = DataSetting::where('type', 'react_landing_page')
                            ->where('key', $key)
                            ->first();
                        if ($existing) {
                            Helpers::check_and_delete($filePath, $existing->value);
                            $existing->delete();
                        }
                    }
                }
            }

            Toastr::success(translate('messages.stepper_image_section_updated'));
            return back();
        } elseif ($tab == 'react-category') {
            $request->validate([
                'category_section_title.0' => 'required|max:254',
                'category_section_sub_title.0' => 'required|max:254',
            ], [
                'category_section_title.0.required' => translate('category_section_title_is_required'),
                'category_section_sub_title.0.required' => translate('default_category_section_sub_title_is_required'),
            ]);

            $this->getAddLandingPageData($request, 'react_landing_page', 'category_section_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'category_section_sub_title', true);
            Toastr::success(translate('messages.category_section_updated'));
            return back();
        } elseif ($tab == 'react-gallery') {
            $request->validate([
                'gallery_section_title.0' => 'required|max:254',
                'gallery_section_sub_title.0' => 'required|max:254',
            ], [
                'gallery_section_title.0.required' => translate('default_gallery_section_title_is_required'),
                'gallery_section_sub_title.0.required' => translate('default_gallery_section_sub_title_is_required'),
            ]);

            $this->getAddLandingPageData($request, 'react_landing_page', 'gallery_section_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'gallery_section_sub_title', true);
            Toastr::success(translate('messages.gallery_section_updated'));
            return back();
        } elseif ($tab == 'react-gallery-images') {
            $request->validate([
                'gallery_image_1' => 'nullable|max:2028',
                'gallery_image_2' => 'nullable|max:2028',
                'gallery_image_3' => 'nullable|max:2028',
                'gallery_image_4' => 'nullable|max:2028',
                'gallery_image_5' => 'nullable|max:2028',
                'gallery_image_6' => 'nullable|max:2028',
            ]);

            $filePath = 'react_gallery/';

            foreach (range(1, 6) as $i) {
                $key = "gallery_image_{$i}";
                $deleteKey = "gallery_image_{$i}_deleted";

                if ($request->hasFile($key)) {
                    $this->getAddLandingPageData(
                        request: $request,
                        type: 'react_landing_page',
                        key: $key,
                        multiLang: false,
                        filePath: $filePath
                    );
                } elseif ($request->{$deleteKey} == '1') {
                    $existing = DataSetting::where('type', 'react_landing_page')
                        ->where('key', $key)
                        ->first();

                    if ($existing) {
                        Helpers::check_and_delete($filePath, $existing->value);
                        $existing->delete();
                    }
                }
            }

            Toastr::success(translate('messages.gallery_section_updated_successfully'));
            return back();
        } elseif ($tab == 'react-testimonial') {
            $request->validate([
                'testimonial_section_title.0' => 'required|max:254',
            ], [
                'testimonial_section_title.0.required' => translate('default_testimonial_section_title_is_required'),
            ]);

            $this->getAddLandingPageData($request, 'react_landing_page', 'testimonial_section_title', true);

            Toastr::success(translate('messages.gallery_section_updated'));
            return back();
        } elseif ($tab == 'react-faq') {
            $request->validate([
                'faq_section_title.0' => 'required|max:254',
                'faq_section_sub_title.0' => 'required|max:254',
            ], [
                'faq_section_title.0.required' => translate('default_faq_section_title_is_required'),
                'faq_section_sub_title.0.required' => translate('default_faq_section_sub_title_is_required'),
            ]);

            $this->getAddLandingPageData($request, 'react_landing_page', 'faq_section_title', true);
            $this->getAddLandingPageData($request, 'react_landing_page', 'faq_section_sub_title', true);

            Toastr::success(translate('messages.gallery_section_updated'));
            return back();
        } elseif ($tab == 'react-meta-data') {
            $request->validate([
                'react_meta_data_title.0' => 'required|max:254',
                'react_meta_data_description.0' => 'required|max:254',
            ], [
                'react_meta_data_title.0.required' => translate('default_meta_title_is_required'),
                'react_meta_data_description.0.required' => translate('default_meta_description_is_required'),
            ]);

            $this->getAddLandingPageData($request, 'react_landing_page', 'react_meta_data_title', false);
            $this->getAddLandingPageData($request, 'react_landing_page', 'react_meta_data_description', false);

            if ($request->react_meta_data_image_remove == '1') {
                $existing = DataSetting::where('type', 'react_landing_page')
                    ->where('key', 'react_meta_data_image')
                    ->first();

                if ($existing) {
                    Helpers::check_and_delete('react_meta_data_image', $existing->value);
                    $existing->delete();
                }
            }

            if ($request->hasFile('react_meta_data_image')) {
                $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_data_image', multiLang: false, filePath: 'react_meta_data_image');
            }

            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_index', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_no_follow', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_no_image_index', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_no_archive', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_no_snippet', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_max_snippet', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_max_snippet_value', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_max_video_preview', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_max_video_preview_value', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_max_image_preview', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'react_landing_page', key: 'react_meta_max_image_preview_value', multiLang: false);


            Toastr::success(translate('messages.meta_data_updated'));
            return back();
        }

        return back();
    }


    public function update_admin_landing_page_settings(Request $request, $tab)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));
            return back();
        }

        if ($tab == 'header-data') {
            $request->validate([
                'header_title' => 'required|max:254',
                'header_sub_title' => 'required|max:254',
                'header_tag_line' => 'required|max:254',
            ]);

            if ($request->header_title[array_search('default', $request->lang)] == '' || $request->header_sub_title[array_search('default', $request->lang)] == '' || $request->header_tag_line[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_title_subtitle_and_tagline_is_required'));
                return back();
            }

            $header_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'header_title')->first();
            if ($header_title == null) {
                $header_title = new DataSetting();
            }
            $header_title->key = 'header_title';
            $header_title->type = 'admin_landing_page';
            $header_title->value = $request->header_title[array_search('default', $request->lang)];
            $header_title->save();

            $header_sub_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'header_sub_title')->first();
            if ($header_sub_title == null) {
                $header_sub_title = new DataSetting();
            }

            $header_sub_title->key = 'header_sub_title';
            $header_sub_title->type = 'admin_landing_page';
            $header_sub_title->value = $request->header_sub_title[array_search('default', $request->lang)];
            $header_sub_title->save();

            $header_tag_line = DataSetting::where('type', 'admin_landing_page')->where('key', 'header_tag_line')->first();
            if ($header_tag_line == null) {
                $header_tag_line = new DataSetting();
            }

            $header_tag_line->key = 'header_tag_line';
            $header_tag_line->type = 'admin_landing_page';
            $header_tag_line->value = $request->header_tag_line[array_search('default', $request->lang)];
            $header_tag_line->save();


            $header_app_button_name = DataSetting::where('type', 'admin_landing_page')->where('key', 'header_app_button_name')->first();
            if ($header_app_button_name == null) {
                $header_app_button_name = new DataSetting();
            }

            $header_app_button_name->key = 'header_app_button_name';
            $header_app_button_name->type = 'admin_landing_page';
            $header_app_button_name->value = $request->header_app_button_name[array_search('default', $request->lang)];
            $header_app_button_name->save();

            $header_app_button_status = DataSetting::where('type', 'admin_landing_page')->where('key', 'header_app_button_status')->first();
            if ($header_app_button_status == null) {
                $header_app_button_status = new DataSetting();
            }

            $header_app_button_status->key = 'header_app_button_status';
            $header_app_button_status->type = 'admin_landing_page';
            $header_app_button_status->value = $request->header_app_button_status;
            $header_app_button_status->save();


            $header_button_content = DataSetting::where('type', 'admin_landing_page')->where('key', 'header_button_content')->first();
            if ($header_button_content == null) {
                $header_button_content = new DataSetting();
            }

            $header_button_content->key = 'header_button_content';
            $header_button_content->type = 'admin_landing_page';
            $header_button_content->value = $request->redirect_link;
            $header_button_content->save();


            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->header_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $header_title->id,
                                'locale' => $key,
                                'key' => 'header_title'
                            ],
                            ['value' => $header_title->value]
                        );
                    }
                } else {
                    if ($request->header_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $header_title->id,
                                'locale' => $key,
                                'key' => 'header_title'
                            ],
                            ['value' => $request->header_title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->header_sub_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $header_sub_title->id,
                                'locale' => $key,
                                'key' => 'header_sub_title'
                            ],
                            ['value' => $header_sub_title->value]
                        );
                    }
                } else {
                    if ($request->header_sub_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $header_sub_title->id,
                                'locale' => $key,
                                'key' => 'header_sub_title'
                            ],
                            ['value' => $request->header_sub_title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->header_tag_line[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $header_tag_line->id,
                                'locale' => $key,
                                'key' => 'header_tag_line'
                            ],
                            ['value' => $header_tag_line->value]
                        );
                    }
                } else {
                    if ($request->header_tag_line[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $header_tag_line->id,
                                'locale' => $key,
                                'key' => 'header_tag_line'
                            ],
                            ['value' => $request->header_tag_line[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->header_app_button_name[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $header_app_button_name->id,
                                'locale' => $key,
                                'key' => 'header_app_button_name'
                            ],
                            ['value' => $header_app_button_name->value]
                        );
                    }
                } else {
                    if ($request->header_app_button_name[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $header_app_button_name->id,
                                'locale' => $key,
                                'key' => 'header_app_button_name'
                            ],
                            ['value' => $request->header_app_button_name[$index]]
                        );
                    }
                }

            }
            Toastr::success(translate('messages.landing_page_text_updated'));
            return back();
        } elseif ($tab == 'header-data-images') {
            $request->validate([
                'header_content_image' => 'nullable|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
                'header_bg_image' => 'nullable|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
            ]);

            $header_image_content = DataSetting::where('type', 'admin_landing_page')->where('key', 'header_image_content')->first();
            $data = [];
            $imageName1 = null;
            $imageName2 = null;
            $storage1 = 'public';
            $storage2 = 'public';
            $imageFile = $request->file('header_content_image');
            $imageFile2 = $request->file('header_bg_image');
            if ($header_image_content) {
                $data = json_decode($header_image_content?->value, true);
                $imageName1 = $data['header_content_image'] ?? null;
                $imageName2 = $data['header_bg_image'] ?? null;
                $storage1 = $data['header_content_image_storage'] ?? 'public';
                $storage2 = $data['header_bg_image_storage'] ?? 'public';
            }

            if ($header_image_content == null) {
                $header_image_content = new DataSetting();
            }
            $header_image_content->key = 'header_image_content';
            $header_image_content->type = 'admin_landing_page';
            if ($request->has('header_content_image')) {
                if (empty($imageName1)) {
                    $imageName1 = Helpers::upload(dir: 'header_image/', format: 'png', image: $imageFile);
                    $storage1 = Helpers::getDisk();
                } else {
                    $imageName1 = Helpers::update(dir: 'header_image/', old_image: $data['header_content_image'], format: 'png', image: $imageFile);
                    $storage1 = Helpers::getDisk();
                }
            }
            if ($request->has('header_bg_image')) {
                if (empty($imageName2)) {
                    $imageName2 = Helpers::upload(dir: 'header_image/', format: 'png', image: $imageFile2);
                    $storage2 = Helpers::getDisk();
                } else {
                    $imageName2 = Helpers::update(dir: 'header_image/', old_image: $data['header_bg_image'], format: 'png', image: $imageFile2);
                    $storage2 = Helpers::getDisk();
                }
            }
            $img_data = [
                'header_content_image' => $imageName1,
                'header_bg_image' => $imageName2,
                'header_content_image_storage' => $storage1,
                'header_bg_image_storage' => $storage2,
            ];
            $header_image_content->value = json_encode($img_data);
            $header_image_content->save();

            Toastr::success(translate('messages.landing_page_image_content_updated'));
            return back();
        } elseif ($tab == 'header-data-floating-icon') {
            $header_floating_content = DataSetting::where('type', 'admin_landing_page')->where('key', 'header_floating_content')->first();
            if ($header_floating_content == null) {
                $header_floating_content = new DataSetting();
            }
            $header_floating_content->key = 'header_floating_content';
            $header_floating_content->type = 'admin_landing_page';
            $button_data = [
                'header_floating_total_order' => $request->header_floating_total_order ?? null,
                'header_floating_total_user' => $request->header_floating_total_user ?? null,
                'header_floating_total_reviews' => $request->header_floating_total_reviews ?? null,
            ];
            $header_floating_content->value = json_encode($button_data);
            $header_floating_content->save();
            Toastr::success(translate('messages.landing_page_header_floating_content_updated'));
            return back();
        } elseif ($tab == 'about-us-data') {
            $request->validate([
                'about_us_title' => 'required|max:254',
                'about_us_sub_title' => 'required|max:254',
                'about_us_text' => 'required|max:1000',
                'about_us_content_image' => 'nullable|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',

            ]);

            if ($request->about_us_title[array_search('default', $request->lang)] == '' || $request->about_us_sub_title[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_title_and_subtitle_is_required'));
                return back();
            }

            $about_us_image_content = DataSetting::where('type', 'admin_landing_page')->where('key', 'about_us_image_content')->first();
            $imageName1 = null;
            $imageFile = $request->file('about_us_content_image');
            $originalExtension = 'png';
            if ($about_us_image_content) {
                $imageName1 = $about_us_image_content?->value ?? null;
            }

            $about_us_image_content = DataSetting::firstOrNew(
                [
                    'key' => 'about_us_image_content',
                    'type' => 'admin_landing_page'
                ],
            );

            if ($request->has('about_us_content_image')) {
                if (empty($imageName1)) {
                    $imageName1 = Helpers::upload(dir: 'about_us_image/', format: $originalExtension, image: $imageFile);
                } else {
                    $imageName1 = Helpers::update(dir: 'about_us_image/', old_image: $imageName1, format: $originalExtension, image: $imageFile);
                }
            }
            $about_us_image_content->value = $imageName1;
            $about_us_image_content->save();

            $about_us_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'about_us_title')->first();
            if ($about_us_title == null) {
                $about_us_title = new DataSetting();
            }
            $about_us_title->key = 'about_us_title';
            $about_us_title->type = 'admin_landing_page';
            $about_us_title->value = $request->about_us_title[array_search('default', $request->lang)];
            $about_us_title->save();

            $about_us_sub_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'about_us_sub_title')->first();
            if ($about_us_sub_title == null) {
                $about_us_sub_title = new DataSetting();
            }

            $about_us_sub_title->key = 'about_us_sub_title';
            $about_us_sub_title->type = 'admin_landing_page';
            $about_us_sub_title->value = $request->about_us_sub_title[array_search('default', $request->lang)];
            $about_us_sub_title->save();

            $about_us_text = DataSetting::where('type', 'admin_landing_page')->where('key', 'about_us_text')->first();
            if ($about_us_text == null) {
                $about_us_text = new DataSetting();
            }

            $about_us_text->key = 'about_us_text';
            $about_us_text->type = 'admin_landing_page';
            $about_us_text->value = $request->about_us_text[array_search('default', $request->lang)];
            $about_us_text->save();

            $about_us_app_button_name = DataSetting::where('type', 'admin_landing_page')->where('key', 'about_us_app_button_name')->first();
            if ($about_us_app_button_name == null) {
                $about_us_app_button_name = new DataSetting();
            }

            $about_us_app_button_name->key = 'about_us_app_button_name';
            $about_us_app_button_name->type = 'admin_landing_page';
            $about_us_app_button_name->value = $request->about_us_app_button_name[array_search('default', $request->lang)];
            // dd($about_us_app_button_name->value , $request->about_us_app_button_name[array_search('default', $request->lang)]);
            $about_us_app_button_name->save();

            $about_us_app_button_status = DataSetting::where('type', 'admin_landing_page')->where('key', 'about_us_app_button_status')->first();
            if ($about_us_app_button_status == null) {
                $about_us_app_button_status = new DataSetting();
            }

            $about_us_app_button_status->key = 'about_us_app_button_status';
            $about_us_app_button_status->type = 'admin_landing_page';
            $about_us_app_button_status->value = $request->about_us_app_button_status;
            $about_us_app_button_status->save();


            $about_us_button_content = DataSetting::where('type', 'admin_landing_page')->where('key', 'about_us_button_content')->first();
            if ($about_us_button_content == null) {
                $about_us_button_content = new DataSetting();
            }

            $about_us_button_content->key = 'about_us_button_content';
            $about_us_button_content->type = 'admin_landing_page';


            $about_us_button_content->value = $request->redirect_link;
            $about_us_button_content->save();


            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->about_us_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $about_us_title->id,
                                'locale' => $key,
                                'key' => 'about_us_title'
                            ],
                            ['value' => $about_us_title->value]
                        );
                    }
                } else {
                    if ($request->about_us_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $about_us_title->id,
                                'locale' => $key,
                                'key' => 'about_us_title'
                            ],
                            ['value' => $request->about_us_title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->about_us_sub_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $about_us_sub_title->id,
                                'locale' => $key,
                                'key' => 'about_us_sub_title'
                            ],
                            ['value' => $about_us_sub_title->value]
                        );
                    }
                } else {
                    if ($request->about_us_sub_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $about_us_sub_title->id,
                                'locale' => $key,
                                'key' => 'about_us_sub_title'
                            ],
                            ['value' => $request->about_us_sub_title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->about_us_text[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $about_us_text->id,
                                'locale' => $key,
                                'key' => 'about_us_text'
                            ],
                            ['value' => $about_us_text->value]
                        );
                    }
                } else {
                    if ($request->about_us_text[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $about_us_text->id,
                                'locale' => $key,
                                'key' => 'about_us_text'
                            ],
                            ['value' => $request->about_us_text[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->about_us_app_button_name[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $about_us_app_button_name->id,
                                'locale' => $key,
                                'key' => 'about_us_app_button_name'
                            ],
                            ['value' => $about_us_app_button_name->value]
                        );
                    }
                } else {
                    if ($request->about_us_app_button_name[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $about_us_app_button_name->id,
                                'locale' => $key,
                                'key' => 'about_us_app_button_name'
                            ],
                            ['value' => $request->about_us_app_button_name[$index]]
                        );
                    }
                }

            }
            Toastr::success(translate('messages.landing_page_about_us_section_updated'));
            return back();
        } elseif ($tab == 'why-choose-us-data') {

            $request->validate([
                'why_choose_us_title' => 'required|max:254',
                'why_choose_us_sub_title' => 'required|max:254',
            ]);


            if ($request->why_choose_us_title[array_search('default', $request->lang)] == '' || $request->why_choose_us_sub_title[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_title_and_subtitle_is_required'));
                return back();
            }


            $why_choose_us_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'why_choose_us_title')->first();
            if ($why_choose_us_title == null) {
                $why_choose_us_title = new DataSetting();
            }
            $why_choose_us_title->key = 'why_choose_us_title';
            $why_choose_us_title->type = 'admin_landing_page';
            $why_choose_us_title->value = $request->why_choose_us_title[array_search('default', $request->lang)];
            $why_choose_us_title->save();

            $why_choose_us_sub_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'why_choose_us_sub_title')->first();
            if ($why_choose_us_sub_title == null) {
                $why_choose_us_sub_title = new DataSetting();
            }

            $why_choose_us_sub_title->key = 'why_choose_us_sub_title';
            $why_choose_us_sub_title->type = 'admin_landing_page';
            $why_choose_us_sub_title->value = $request->why_choose_us_sub_title[array_search('default', $request->lang)];
            $why_choose_us_sub_title->save();

            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->why_choose_us_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $why_choose_us_title->id,
                                'locale' => $key,
                                'key' => 'why_choose_us_title'
                            ],
                            ['value' => $why_choose_us_title->value]
                        );
                    }
                } else {
                    if ($request->why_choose_us_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $why_choose_us_title->id,
                                'locale' => $key,
                                'key' => 'why_choose_us_title'
                            ],
                            ['value' => $request->why_choose_us_title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->why_choose_us_sub_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $why_choose_us_sub_title->id,
                                'locale' => $key,
                                'key' => 'why_choose_us_sub_title'
                            ],
                            ['value' => $why_choose_us_sub_title->value]
                        );
                    }
                } else {
                    if ($request->why_choose_us_sub_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $why_choose_us_sub_title->id,
                                'locale' => $key,
                                'key' => 'why_choose_us_sub_title'
                            ],
                            ['value' => $request->why_choose_us_sub_title[$index]]
                        );
                    }
                }
            }
            Toastr::success(translate('messages.landing_page_why_choose_us_section_updated'));
            return back();
        } elseif ($tab == 'why-choose-us-data-1') {
            $request->validate([
                'title' => 'required|max:254',
                'key' => 'required|max:100',
                'key_image' => 'required|max:100',
                'image' => 'nullable|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
            ]);

            if ($request->title[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_title_is_required'));
                return back();
            }

            $this->update_data($request, $request->key, 'title');

            if ($request->has('image')) {
                $why_choose_us_image = DataSetting::where('type', 'admin_landing_page')->where('key', $request->key_image)->first();
                $imageName1 = null;
                $imageFile = $request->file('image');
                $originalExtension = 'png';
                if ($why_choose_us_image) {
                    $imageName1 = $why_choose_us_image?->value;
                }
                if ($why_choose_us_image == null) {
                    $why_choose_us_image = new DataSetting();
                }
                $why_choose_us_image->key = $request->key_image;
                $why_choose_us_image->type = 'admin_landing_page';
                if (empty($imageName1)) {
                    $imageName1 = Helpers::upload(dir: 'why_choose_us_image/', format: $originalExtension, image: $imageFile);
                } else {
                    $imageName1 = Helpers::update(dir: 'why_choose_us_image/', old_image: $imageName1, format: $originalExtension, image: $imageFile);
                }
                $why_choose_us_image->value = $imageName1;
                $why_choose_us_image->save();
            }

            Toastr::success(translate('messages.landing_page_why_choose_us_section_data_updated'));
            return back();
        } elseif ($tab == 'earn-money-data') {

            $request->validate([
                'earn_money_title' => 'required|max:254',
                'earn_money_sub_title' => 'required|max:254',
            ]);
            $earn_money_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'earn_money_title')->first();
            if ($earn_money_title == null) {
                $earn_money_title = new DataSetting();
            }
            $earn_money_title->key = 'earn_money_title';
            $earn_money_title->type = 'admin_landing_page';
            $earn_money_title->value = $request->earn_money_title[array_search('default', $request->lang)];
            $earn_money_title->save();

            $earn_money_sub_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'earn_money_sub_title')->first();
            if ($earn_money_sub_title == null) {
                $earn_money_sub_title = new DataSetting();
            }

            $earn_money_sub_title->key = 'earn_money_sub_title';
            $earn_money_sub_title->type = 'admin_landing_page';
            $earn_money_sub_title->value = $request->earn_money_sub_title[array_search('default', $request->lang)];
            $earn_money_sub_title->save();

            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->earn_money_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $earn_money_title->id,
                                'locale' => $key,
                                'key' => 'earn_money_title'
                            ],
                            ['value' => $earn_money_title->value]
                        );
                    }
                } else {
                    if ($request->earn_money_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $earn_money_title->id,
                                'locale' => $key,
                                'key' => 'earn_money_title'
                            ],
                            ['value' => $request->earn_money_title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->earn_money_sub_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $earn_money_sub_title->id,
                                'locale' => $key,
                                'key' => 'earn_money_sub_title'
                            ],
                            ['value' => $earn_money_sub_title->value]
                        );
                    }
                } else {
                    if ($request->earn_money_sub_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $earn_money_sub_title->id,
                                'locale' => $key,
                                'key' => 'earn_money_sub_title'
                            ],
                            ['value' => $request->earn_money_sub_title[$index]]
                        );
                    }
                }
            }
            Toastr::success(translate('messages.landing_page_why_choose_us_section_updated'));
            return back();
        } elseif ($tab == 'earn-money-data-reg-section') {
            $request->validate([
                'earn_money_reg_title' => 'required',
                'earn_money_reg_image' => 'nullable|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
            ]);

            $earn_money_reg_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'earn_money_reg_title')->first();
            if ($earn_money_reg_title == null) {
                $earn_money_reg_title = new DataSetting();
            }
            $earn_money_reg_title->key = 'earn_money_reg_title';
            $earn_money_reg_title->type = 'admin_landing_page';
            $earn_money_reg_title->value = $request->earn_money_reg_title[array_search('default', $request->lang)];
            $earn_money_reg_title->save();

            // restaurant
            $earn_money_restaurant_req_button_name = DataSetting::where('type', 'admin_landing_page')->where('key', 'earn_money_restaurant_req_button_name')->first();
            if ($earn_money_restaurant_req_button_name == null) {
                $earn_money_restaurant_req_button_name = new DataSetting();
            }
            $earn_money_restaurant_req_button_name->key = 'earn_money_restaurant_req_button_name';
            $earn_money_restaurant_req_button_name->type = 'admin_landing_page';
            $earn_money_restaurant_req_button_name->value = $request->earn_money_restaurant_req_button_name[array_search('default', $request->lang)];
            $earn_money_restaurant_req_button_name->save();

            $earn_money_restaurant_req_button_status = DataSetting::where('type', 'admin_landing_page')->where('key', 'earn_money_restaurant_req_button_status')->first();
            if ($earn_money_restaurant_req_button_status == null) {
                $earn_money_restaurant_req_button_status = new DataSetting();
            }
            $earn_money_restaurant_req_button_status->key = 'earn_money_restaurant_req_button_status';
            $earn_money_restaurant_req_button_status->type = 'admin_landing_page';
            $earn_money_restaurant_req_button_status->value = $request->earn_money_restaurant_req_button_status ?? 0;
            $earn_money_restaurant_req_button_status->save();

            $earn_money_restaurant_req_button_link = DataSetting::where('type', 'admin_landing_page')->where('key', 'earn_money_restaurant_req_button_link')->first();
            if ($earn_money_restaurant_req_button_link == null) {
                $earn_money_restaurant_req_button_link = new DataSetting();
            }
            $earn_money_restaurant_req_button_link->key = 'earn_money_restaurant_req_button_link';
            $earn_money_restaurant_req_button_link->type = 'admin_landing_page';
            $earn_money_restaurant_req_button_link->value = $request->earn_money_restaurant_req_button_link;
            $earn_money_restaurant_req_button_link->save();
            // restaurant end
            // delivery
            $earn_money_delivety_man_req_button_name = DataSetting::where('type', 'admin_landing_page')->where('key', 'earn_money_delivety_man_req_button_name')->first();
            if ($earn_money_delivety_man_req_button_name == null) {
                $earn_money_delivety_man_req_button_name = new DataSetting();
            }
            $earn_money_delivety_man_req_button_name->key = 'earn_money_delivety_man_req_button_name';
            $earn_money_delivety_man_req_button_name->type = 'admin_landing_page';
            $earn_money_delivety_man_req_button_name->value = $request->earn_money_delivety_man_req_button_name[array_search('default', $request->lang)];
            $earn_money_delivety_man_req_button_name->save();

            $earn_money_delivery_man_req_button_status = DataSetting::where('type', 'admin_landing_page')->where('key', 'earn_money_delivery_man_req_button_status')->first();
            if ($earn_money_delivery_man_req_button_status == null) {
                $earn_money_delivery_man_req_button_status = new DataSetting();
            }
            $earn_money_delivery_man_req_button_status->key = 'earn_money_delivery_man_req_button_status';
            $earn_money_delivery_man_req_button_status->type = 'admin_landing_page';
            $earn_money_delivery_man_req_button_status->value = $request->earn_money_delivery_man_req_button_status ?? 0;
            $earn_money_delivery_man_req_button_status->save();

            $earn_money_delivery_man_req_button_link = DataSetting::where('type', 'admin_landing_page')->where('key', 'earn_money_delivery_man_req_button_link')->first();
            if ($earn_money_delivery_man_req_button_link == null) {
                $earn_money_delivery_man_req_button_link = new DataSetting();
            }
            $earn_money_delivery_man_req_button_link->key = 'earn_money_delivery_man_req_button_link';
            $earn_money_delivery_man_req_button_link->type = 'admin_landing_page';

            $earn_money_delivery_man_req_button_link->value = $request->earn_money_delivery_req_button_link;
            $earn_money_delivery_man_req_button_link->save();
            // delivery end
            if ($request->has('earn_money_reg_image')) {

                $earn_money_reg_image = DataSetting::where('type', 'admin_landing_page')->where('key', 'earn_money_reg_image')->first();
                $imageName1 = null;
                $imageFile = $request->file('earn_money_reg_image');
                $originalExtension = 'png';
                if ($earn_money_reg_image) {
                    $imageName1 = $earn_money_reg_image?->value;
                }
                if ($earn_money_reg_image == null) {
                    $earn_money_reg_image = new DataSetting();
                }
                $earn_money_reg_image->key = 'earn_money_reg_image';
                $earn_money_reg_image->type = 'admin_landing_page';
                if (empty($imageName1)) {
                    $imageName1 = Helpers::upload(dir: 'earn_money/', format: $originalExtension, image: $imageFile);
                } else {
                    $imageName1 = Helpers::update(dir: 'earn_money/', old_image: $imageName1, format: $originalExtension, image: $imageFile);
                }
                $earn_money_reg_image->value = $imageName1;
                $earn_money_reg_image->save();
            }

            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->earn_money_reg_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $earn_money_reg_title->id,
                                'locale' => $key,
                                'key' => 'earn_money_reg_title'
                            ],
                            ['value' => $earn_money_reg_title->value]
                        );
                    }
                } else {
                    if ($request->earn_money_reg_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $earn_money_reg_title->id,
                                'locale' => $key,
                                'key' => 'earn_money_reg_title'
                            ],
                            ['value' => $request->earn_money_reg_title[$index]]
                        );
                    }
                }


                if ($default_lang == $key && !($request->earn_money_restaurant_req_button_name[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $earn_money_restaurant_req_button_name->id,
                                'locale' => $key,
                                'key' => 'earn_money_restaurant_req_button_name'
                            ],
                            ['value' => $earn_money_restaurant_req_button_name->value]
                        );
                    }
                } else {
                    if ($request->earn_money_restaurant_req_button_name[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $earn_money_restaurant_req_button_name->id,
                                'locale' => $key,
                                'key' => 'earn_money_restaurant_req_button_name'
                            ],
                            ['value' => $request->earn_money_restaurant_req_button_name[$index]]
                        );
                    }
                }

                if ($default_lang == $key && !($request->earn_money_delivety_man_req_button_name[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $earn_money_delivety_man_req_button_name->id,
                                'locale' => $key,
                                'key' => 'earn_money_delivety_man_req_button_name'
                            ],
                            ['value' => $earn_money_delivety_man_req_button_name->value]
                        );
                    }
                } else {
                    if ($request->earn_money_delivety_man_req_button_name[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $earn_money_delivety_man_req_button_name->id,
                                'locale' => $key,
                                'key' => 'earn_money_delivety_man_req_button_name'
                            ],
                            ['value' => $request->earn_money_delivety_man_req_button_name[$index]]
                        );
                    }
                }
            }

            Toastr::success(translate('messages.Registration_section_updated_successfully'));
            return back();
        } elseif ($tab == 'feature-list') {
            $request->validate([
                'title' => 'required',
                'sub_title' => 'required',
                'image' => 'required',
            ]);

            $feature = new AdminFeature();
            $feature->title = $request->title[array_search('default', $request->lang)];
            $feature->sub_title = $request->sub_title[array_search('default', $request->lang)];
            $feature->image = Helpers::upload('admin_feature/', 'png', $request->file('image'));
            $feature->save();
            $default_lang = str_replace('_', '-', app()->getLocale());
            $data = [];
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\AdminFeature',
                                'translationable_id' => $feature->id,
                                'locale' => $key,
                                'key' => 'title'
                            ],
                            ['value' => $feature->title]
                        );
                    }
                } else {

                    if ($request->title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\AdminFeature',
                                'translationable_id' => $feature->id,
                                'locale' => $key,
                                'key' => 'title'
                            ],
                            ['value' => $request->title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->sub_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\AdminFeature',
                                'translationable_id' => $feature->id,
                                'locale' => $key,
                                'key' => 'sub_title'
                            ],
                            ['value' => $feature->sub_title]
                        );
                    }
                } else {

                    if ($request->sub_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\AdminFeature',
                                'translationable_id' => $feature->id,
                                'locale' => $key,
                                'key' => 'sub_title'
                            ],
                            ['value' => $request->sub_title[$index]]
                        );
                    }
                }
            }

            Toastr::success(translate('messages.feature_added_successfully'));
        } elseif ($tab == 'testimonial-title') {
            $testimonial_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'testimonial_title')->first();
            if ($testimonial_title == null) {
                $testimonial_title = new DataSetting();
            }

            $testimonial_title->key = 'testimonial_title';
            $testimonial_title->type = 'admin_landing_page';
            $testimonial_title->value = $request->testimonial_title[array_search('default', $request->lang)];
            $testimonial_title->save();

            $data = [];
            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->testimonial_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $testimonial_title->id,
                                'locale' => $key,
                                'key' => 'testimonial_title'
                            ],
                            ['value' => $testimonial_title->value]
                        );
                    }
                } else {
                    if ($request->testimonial_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $testimonial_title->id,
                                'locale' => $key,
                                'key' => 'testimonial_title'
                            ],
                            ['value' => $request->testimonial_title[$index]]
                        );
                    }
                }
            }

            Toastr::success(translate('messages.testimonial_section_updated'));
        } elseif ($tab == 'features-title-section') {


            if ($request->feature_title[array_search('default', $request->lang)] == '' || $request->feature_sub_title[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_title_and_subtitle_is_required'));
                return back();
            }

            $feature_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'feature_title')->first();
            if ($feature_title == null) {
                $feature_title = new DataSetting();
            }

            $feature_title->key = 'feature_title';
            $feature_title->type = 'admin_landing_page';
            $feature_title->value = $request->feature_title[array_search('default', $request->lang)];
            $feature_title->save();

            $feature_sub_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'feature_sub_title')->first();
            if ($feature_sub_title == null) {
                $feature_sub_title = new DataSetting();
            }

            $feature_sub_title->key = 'feature_sub_title';
            $feature_sub_title->type = 'admin_landing_page';
            $feature_sub_title->value = $request->feature_sub_title[array_search('default', $request->lang)];
            $feature_sub_title->save();

            $data = [];
            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->feature_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $feature_title->id,
                                'locale' => $key,
                                'key' => 'feature_title'
                            ],
                            ['value' => $feature_title->value]
                        );
                    }
                } else {
                    if ($request->feature_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $feature_title->id,
                                'locale' => $key,
                                'key' => 'feature_title'
                            ],
                            ['value' => $request->feature_title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->feature_sub_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $feature_sub_title->id,
                                'locale' => $key,
                                'key' => 'feature_sub_title'
                            ],
                            ['value' => $feature_sub_title->value]
                        );
                    }
                } else {
                    if ($request->feature_sub_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $feature_sub_title->id,
                                'locale' => $key,
                                'key' => 'feature_sub_title'
                            ],
                            ['value' => $request->feature_sub_title[$index]]
                        );
                    }
                }
            }

            Toastr::success(translate('messages.Feature_section_updated'));
        } elseif ($tab == 'services-data') {

            $request->validate([
                'services_title' => 'required|max:254',
                'services_sub_title' => 'required|max:254',
            ]);
            $services_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_title')->first();
            if ($services_title == null) {
                $services_title = new DataSetting();
            }
            $services_title->key = 'services_title';
            $services_title->type = 'admin_landing_page';
            $services_title->value = $request->services_title[array_search('default', $request->lang)];
            $services_title->save();

            $services_sub_title = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_sub_title')->first();
            if ($services_sub_title == null) {
                $services_sub_title = new DataSetting();
            }

            $services_sub_title->key = 'services_sub_title';
            $services_sub_title->type = 'admin_landing_page';
            $services_sub_title->value = $request->services_sub_title[array_search('default', $request->lang)];
            $services_sub_title->save();

            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->services_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_title->id,
                                'locale' => $key,
                                'key' => 'services_title'
                            ],
                            ['value' => $services_title->value]
                        );
                    }
                } else {
                    if ($request->services_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_title->id,
                                'locale' => $key,
                                'key' => 'services_title'
                            ],
                            ['value' => $request->services_title[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->services_sub_title[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_sub_title->id,
                                'locale' => $key,
                                'key' => 'services_sub_title'
                            ],
                            ['value' => $services_sub_title->value]
                        );
                    }
                } else {
                    if ($request->services_sub_title[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_sub_title->id,
                                'locale' => $key,
                                'key' => 'services_sub_title'
                            ],
                            ['value' => $request->services_sub_title[$index]]
                        );
                    }
                }
            }
            Toastr::success(translate('messages.Services_section_updated'));
            return back();
        } elseif ($tab == 'services-order-data') {
            $request->validate([
                'services_order_title_1' => 'required|max:100',
                'services_order_title_2' => 'required|max:100',
                'services_order_description_1' => 'required|max:100',
                'services_order_description_2' => 'required|max:100',
            ]);

            $services_order_title_1 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_order_title_1')->first();
            if ($services_order_title_1 == null) {
                $services_order_title_1 = new DataSetting();
            }
            $services_order_title_1->key = 'services_order_title_1';
            $services_order_title_1->type = 'admin_landing_page';
            $services_order_title_1->value = $request->services_order_title_1[array_search('default', $request->lang)];
            $services_order_title_1->save();

            $services_order_title_2 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_order_title_2')->first();
            if ($services_order_title_2 == null) {
                $services_order_title_2 = new DataSetting();
            }
            $services_order_title_2->key = 'services_order_title_2';
            $services_order_title_2->type = 'admin_landing_page';
            $services_order_title_2->value = $request->services_order_title_2[array_search('default', $request->lang)];
            $services_order_title_2->save();

            $services_order_description_1 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_order_description_1')->first();
            if ($services_order_description_1 == null) {
                $services_order_description_1 = new DataSetting();
            }
            $services_order_description_1->key = 'services_order_description_1';
            $services_order_description_1->type = 'admin_landing_page';
            $services_order_description_1->value = $request->services_order_description_1[array_search('default', $request->lang)];
            $services_order_description_1->save();

            $services_order_description_2 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_order_description_2')->first();
            if ($services_order_description_2 == null) {
                $services_order_description_2 = new DataSetting();
            }
            $services_order_description_2->key = 'services_order_description_2';
            $services_order_description_2->type = 'admin_landing_page';
            $services_order_description_2->value = $request->services_order_description_2[array_search('default', $request->lang)];
            $services_order_description_2->save();

            $services_order_button_name = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_order_button_name')->first();
            if ($services_order_button_name == null) {
                $services_order_button_name = new DataSetting();
            }
            $services_order_button_name->key = 'services_order_button_name';
            $services_order_button_name->type = 'admin_landing_page';
            $services_order_button_name->value = $request->services_order_button_name[array_search('default', $request->lang)];
            $services_order_button_name->save();

            $services_order_button_status = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_order_button_status')->first();
            if ($services_order_button_status == null) {
                $services_order_button_status = new DataSetting();
            }
            $services_order_button_status->key = 'services_order_button_status';
            $services_order_button_status->type = 'admin_landing_page';
            $services_order_button_status->value = $request->services_order_button_status ?? 0;
            $services_order_button_status->save();


            $services_order_button_link = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_order_button_link')->first();
            if ($services_order_button_link == null) {
                $services_order_button_link = new DataSetting();
            }
            $services_order_button_link->key = 'services_order_button_link';
            $services_order_button_link->type = 'admin_landing_page';

            $services_order_button_link->value = $request->services_order_button_link;
            $services_order_button_link->save();


            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->services_order_title_1[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_order_title_1->id,
                                'locale' => $key,
                                'key' => 'services_order_title_1'
                            ],
                            ['value' => $services_order_title_1->value]
                        );
                    }
                } else {
                    if ($request->services_order_title_1[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_order_title_1->id,
                                'locale' => $key,
                                'key' => 'services_order_title_1'
                            ],
                            ['value' => $request->services_order_title_1[$index]]
                        );
                    }
                }


                if ($default_lang == $key && !($request->services_order_title_2[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_order_title_2->id,
                                'locale' => $key,
                                'key' => 'services_order_title_2'
                            ],
                            ['value' => $services_order_title_2->value]
                        );
                    }
                } else {
                    if ($request->services_order_title_2[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_order_title_2->id,
                                'locale' => $key,
                                'key' => 'services_order_title_2'
                            ],
                            ['value' => $request->services_order_title_2[$index]]
                        );
                    }
                }

                if ($default_lang == $key && !($request->services_order_description_1[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_order_description_1->id,
                                'locale' => $key,
                                'key' => 'services_order_description_1'
                            ],
                            ['value' => $services_order_description_1->value]
                        );
                    }
                } else {
                    if ($request->services_order_description_1[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_order_description_1->id,
                                'locale' => $key,
                                'key' => 'services_order_description_1'
                            ],
                            ['value' => $request->services_order_description_1[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->services_order_description_2[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_order_description_2->id,
                                'locale' => $key,
                                'key' => 'services_order_description_2'
                            ],
                            ['value' => $services_order_description_2->value]
                        );
                    }
                } else {
                    if ($request->services_order_description_2[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_order_description_2->id,
                                'locale' => $key,
                                'key' => 'services_order_description_2'
                            ],
                            ['value' => $request->services_order_description_2[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->services_order_button_name[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_order_button_name->id,
                                'locale' => $key,
                                'key' => 'services_order_button_name'
                            ],
                            ['value' => $services_order_button_name->value]
                        );
                    }
                } else {
                    if ($request->services_order_button_name[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_order_button_name->id,
                                'locale' => $key,
                                'key' => 'services_order_button_name'
                            ],
                            ['value' => $request->services_order_button_name[$index]]
                        );
                    }
                }
            }

            Toastr::success(translate('messages.Order_section_updated_successfully'));
            return back();
        } elseif ($tab == 'services-manage-restaurant-data') {
            $request->validate([
                'services_manage_restaurant_title_1' => 'required|max:100',
                'services_manage_restaurant_title_2' => 'required|max:100',
                'services_manage_restaurant_description_1' => 'required|max:100',
                'services_manage_restaurant_description_2' => 'required|max:100',
            ]);

            $services_manage_restaurant_title_1 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_title_1')->first();
            if ($services_manage_restaurant_title_1 == null) {
                $services_manage_restaurant_title_1 = new DataSetting();
            }
            $services_manage_restaurant_title_1->key = 'services_manage_restaurant_title_1';
            $services_manage_restaurant_title_1->type = 'admin_landing_page';
            $services_manage_restaurant_title_1->value = $request->services_manage_restaurant_title_1[array_search('default', $request->lang)];
            $services_manage_restaurant_title_1->save();

            $services_manage_restaurant_title_2 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_title_2')->first();
            if ($services_manage_restaurant_title_2 == null) {
                $services_manage_restaurant_title_2 = new DataSetting();
            }
            $services_manage_restaurant_title_2->key = 'services_manage_restaurant_title_2';
            $services_manage_restaurant_title_2->type = 'admin_landing_page';
            $services_manage_restaurant_title_2->value = $request->services_manage_restaurant_title_2[array_search('default', $request->lang)];
            $services_manage_restaurant_title_2->save();

            $services_manage_restaurant_description_1 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_description_1')->first();
            if ($services_manage_restaurant_description_1 == null) {
                $services_manage_restaurant_description_1 = new DataSetting();
            }
            $services_manage_restaurant_description_1->key = 'services_manage_restaurant_description_1';
            $services_manage_restaurant_description_1->type = 'admin_landing_page';
            $services_manage_restaurant_description_1->value = $request->services_manage_restaurant_description_1[array_search('default', $request->lang)];
            $services_manage_restaurant_description_1->save();

            $services_manage_restaurant_description_2 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_description_2')->first();
            if ($services_manage_restaurant_description_2 == null) {
                $services_manage_restaurant_description_2 = new DataSetting();
            }
            $services_manage_restaurant_description_2->key = 'services_manage_restaurant_description_2';
            $services_manage_restaurant_description_2->type = 'admin_landing_page';
            $services_manage_restaurant_description_2->value = $request->services_manage_restaurant_description_2[array_search('default', $request->lang)];
            $services_manage_restaurant_description_2->save();

            $services_manage_restaurant_button_name = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_button_name')->first();
            if ($services_manage_restaurant_button_name == null) {
                $services_manage_restaurant_button_name = new DataSetting();
            }
            $services_manage_restaurant_button_name->key = 'services_manage_restaurant_button_name';
            $services_manage_restaurant_button_name->type = 'admin_landing_page';
            $services_manage_restaurant_button_name->value = $request->services_manage_restaurant_button_name[array_search('default', $request->lang)];
            $services_manage_restaurant_button_name->save();

            $services_manage_restaurant_button_status = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_button_status')->first();
            if ($services_manage_restaurant_button_status == null) {
                $services_manage_restaurant_button_status = new DataSetting();
            }
            $services_manage_restaurant_button_status->key = 'services_manage_restaurant_button_status';
            $services_manage_restaurant_button_status->type = 'admin_landing_page';
            $services_manage_restaurant_button_status->value = $request->services_manage_restaurant_button_status ?? 0;
            $services_manage_restaurant_button_status->save();


            $services_manage_restaurant_button_link = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_restaurant_button_link')->first();
            if ($services_manage_restaurant_button_link == null) {
                $services_manage_restaurant_button_link = new DataSetting();
            }
            $services_manage_restaurant_button_link->key = 'services_manage_restaurant_button_link';
            $services_manage_restaurant_button_link->type = 'admin_landing_page';
            $services_manage_restaurant_button_link->value = $request->services_manage_restaurant_button_link;
            $services_manage_restaurant_button_link->save();


            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->services_manage_restaurant_title_1[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_restaurant_title_1->id,
                                'locale' => $key,
                                'key' => 'services_manage_restaurant_title_1'
                            ],
                            ['value' => $services_manage_restaurant_title_1->value]
                        );
                    }
                } else {
                    if ($request->services_manage_restaurant_title_1[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_restaurant_title_1->id,
                                'locale' => $key,
                                'key' => 'services_manage_restaurant_title_1'
                            ],
                            ['value' => $request->services_manage_restaurant_title_1[$index]]
                        );
                    }
                }


                if ($default_lang == $key && !($request->services_manage_restaurant_title_2[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_restaurant_title_2->id,
                                'locale' => $key,
                                'key' => 'services_manage_restaurant_title_2'
                            ],
                            ['value' => $services_manage_restaurant_title_2->value]
                        );
                    }
                } else {
                    if ($request->services_manage_restaurant_title_2[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_restaurant_title_2->id,
                                'locale' => $key,
                                'key' => 'services_manage_restaurant_title_2'
                            ],
                            ['value' => $request->services_manage_restaurant_title_2[$index]]
                        );
                    }
                }

                if ($default_lang == $key && !($request->services_manage_restaurant_description_1[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_restaurant_description_1->id,
                                'locale' => $key,
                                'key' => 'services_manage_restaurant_description_1'
                            ],
                            ['value' => $services_manage_restaurant_description_1->value]
                        );
                    }
                } else {
                    if ($request->services_manage_restaurant_description_1[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_restaurant_description_1->id,
                                'locale' => $key,
                                'key' => 'services_manage_restaurant_description_1'
                            ],
                            ['value' => $request->services_manage_restaurant_description_1[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->services_manage_restaurant_description_2[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_restaurant_description_2->id,
                                'locale' => $key,
                                'key' => 'services_manage_restaurant_description_2'
                            ],
                            ['value' => $services_manage_restaurant_description_2->value]
                        );
                    }
                } else {
                    if ($request->services_manage_restaurant_description_2[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_restaurant_description_2->id,
                                'locale' => $key,
                                'key' => 'services_manage_restaurant_description_2'
                            ],
                            ['value' => $request->services_manage_restaurant_description_2[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->services_manage_restaurant_button_name[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_restaurant_button_name->id,
                                'locale' => $key,
                                'key' => 'services_manage_restaurant_button_name'
                            ],
                            ['value' => $services_manage_restaurant_button_name->value]
                        );
                    }
                } else {
                    if ($request->services_manage_restaurant_button_name[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_restaurant_button_name->id,
                                'locale' => $key,
                                'key' => 'services_manage_restaurant_button_name'
                            ],
                            ['value' => $request->services_manage_restaurant_button_name[$index]]
                        );
                    }
                }
            }

            Toastr::success(translate('messages.Manage_restaurant_section_updated_successfully'));
            return back();
        } elseif ($tab == 'services-manage-delivery-data') {
            $request->validate([
                'services_manage_delivery_title_1' => 'required|max:100',
                'services_manage_delivery_title_2' => 'required|max:100',
                'services_manage_delivery_description_1' => 'required|max:100',
                'services_manage_delivery_description_2' => 'required|max:100',
            ]);

            $services_manage_delivery_title_1 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_title_1')->first();
            if ($services_manage_delivery_title_1 == null) {
                $services_manage_delivery_title_1 = new DataSetting();
            }
            $services_manage_delivery_title_1->key = 'services_manage_delivery_title_1';
            $services_manage_delivery_title_1->type = 'admin_landing_page';
            $services_manage_delivery_title_1->value = $request->services_manage_delivery_title_1[array_search('default', $request->lang)];
            $services_manage_delivery_title_1->save();

            $services_manage_delivery_title_2 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_title_2')->first();
            if ($services_manage_delivery_title_2 == null) {
                $services_manage_delivery_title_2 = new DataSetting();
            }
            $services_manage_delivery_title_2->key = 'services_manage_delivery_title_2';
            $services_manage_delivery_title_2->type = 'admin_landing_page';
            $services_manage_delivery_title_2->value = $request->services_manage_delivery_title_2[array_search('default', $request->lang)];
            $services_manage_delivery_title_2->save();

            $services_manage_delivery_description_1 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_description_1')->first();
            if ($services_manage_delivery_description_1 == null) {
                $services_manage_delivery_description_1 = new DataSetting();
            }
            $services_manage_delivery_description_1->key = 'services_manage_delivery_description_1';
            $services_manage_delivery_description_1->type = 'admin_landing_page';
            $services_manage_delivery_description_1->value = $request->services_manage_delivery_description_1[array_search('default', $request->lang)];
            $services_manage_delivery_description_1->save();

            $services_manage_delivery_description_2 = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_description_2')->first();
            if ($services_manage_delivery_description_2 == null) {
                $services_manage_delivery_description_2 = new DataSetting();
            }
            $services_manage_delivery_description_2->key = 'services_manage_delivery_description_2';
            $services_manage_delivery_description_2->type = 'admin_landing_page';
            $services_manage_delivery_description_2->value = $request->services_manage_delivery_description_2[array_search('default', $request->lang)];
            $services_manage_delivery_description_2->save();

            $services_manage_delivery_button_name = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_button_name')->first();
            if ($services_manage_delivery_button_name == null) {
                $services_manage_delivery_button_name = new DataSetting();
            }
            $services_manage_delivery_button_name->key = 'services_manage_delivery_button_name';
            $services_manage_delivery_button_name->type = 'admin_landing_page';
            $services_manage_delivery_button_name->value = $request->services_manage_delivery_button_name[array_search('default', $request->lang)];
            $services_manage_delivery_button_name->save();

            $services_manage_delivery_button_status = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_button_status')->first();
            if ($services_manage_delivery_button_status == null) {
                $services_manage_delivery_button_status = new DataSetting();
            }
            $services_manage_delivery_button_status->key = 'services_manage_delivery_button_status';
            $services_manage_delivery_button_status->type = 'admin_landing_page';
            $services_manage_delivery_button_status->value = $request->services_manage_delivery_button_status ?? 0;
            $services_manage_delivery_button_status->save();


            $services_manage_delivery_button_link = DataSetting::where('type', 'admin_landing_page')->where('key', 'services_manage_delivery_button_link')->first();
            if ($services_manage_delivery_button_link == null) {
                $services_manage_delivery_button_link = new DataSetting();
            }
            $services_manage_delivery_button_link->key = 'services_manage_delivery_button_link';
            $services_manage_delivery_button_link->type = 'admin_landing_page';
            $services_manage_delivery_button_link->value = $request->services_manage_delivery_button_link;
            $services_manage_delivery_button_link->save();


            $default_lang = str_replace('_', '-', app()->getLocale());
            foreach ($request->lang as $index => $key) {
                if ($default_lang == $key && !($request->services_manage_delivery_title_1[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_delivery_title_1->id,
                                'locale' => $key,
                                'key' => 'services_manage_delivery_title_1'
                            ],
                            ['value' => $services_manage_delivery_title_1->value]
                        );
                    }
                } else {
                    if ($request->services_manage_delivery_title_1[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_delivery_title_1->id,
                                'locale' => $key,
                                'key' => 'services_manage_delivery_title_1'
                            ],
                            ['value' => $request->services_manage_delivery_title_1[$index]]
                        );
                    }
                }


                if ($default_lang == $key && !($request->services_manage_delivery_title_2[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_delivery_title_2->id,
                                'locale' => $key,
                                'key' => 'services_manage_delivery_title_2'
                            ],
                            ['value' => $services_manage_delivery_title_2->value]
                        );
                    }
                } else {
                    if ($request->services_manage_delivery_title_2[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_delivery_title_2->id,
                                'locale' => $key,
                                'key' => 'services_manage_delivery_title_2'
                            ],
                            ['value' => $request->services_manage_delivery_title_2[$index]]
                        );
                    }
                }

                if ($default_lang == $key && !($request->services_manage_delivery_description_1[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_delivery_description_1->id,
                                'locale' => $key,
                                'key' => 'services_manage_delivery_description_1'
                            ],
                            ['value' => $services_manage_delivery_description_1->value]
                        );
                    }
                } else {
                    if ($request->services_manage_delivery_description_1[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_delivery_description_1->id,
                                'locale' => $key,
                                'key' => 'services_manage_delivery_description_1'
                            ],
                            ['value' => $request->services_manage_delivery_description_1[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->services_manage_delivery_description_2[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_delivery_description_2->id,
                                'locale' => $key,
                                'key' => 'services_manage_delivery_description_2'
                            ],
                            ['value' => $services_manage_delivery_description_2->value]
                        );
                    }
                } else {
                    if ($request->services_manage_delivery_description_2[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_delivery_description_2->id,
                                'locale' => $key,
                                'key' => 'services_manage_delivery_description_2'
                            ],
                            ['value' => $request->services_manage_delivery_description_2[$index]]
                        );
                    }
                }
                if ($default_lang == $key && !($request->services_manage_delivery_button_name[$index])) {
                    if ($key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_delivery_button_name->id,
                                'locale' => $key,
                                'key' => 'services_manage_delivery_button_name'
                            ],
                            ['value' => $services_manage_delivery_button_name->value]
                        );
                    }
                } else {
                    if ($request->services_manage_delivery_button_name[$index] && $key != 'default') {
                        Translation::updateOrInsert(
                            [
                                'translationable_type' => 'App\Models\DataSetting',
                                'translationable_id' => $services_manage_delivery_button_name->id,
                                'locale' => $key,
                                'key' => 'services_manage_delivery_button_name'
                            ],
                            ['value' => $request->services_manage_delivery_button_name[$index]]
                        );
                    }
                }
            }

            Toastr::success(translate('messages.Delivery_section_updated_successfully'));
            return back();

        } elseif ($tab == 'fixed-data-newsletter') {
            $request->validate([
                'title' => 'required|max:254',
                'sub_title' => 'required|max:254',
            ]);


            if ($request->title[array_search('default', $request->lang)] == '' || $request->sub_title[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_title_and_subtitle_is_required'));
                return back();
            }

            $this->update_data($request, $request->key, 'title');
            $this->update_data($request, $request->key_2, 'sub_title');

            Toastr::success(translate('messages.Admin_landing_page_newsletter_updated'));
            return back();

        } elseif ($tab == 'fixed-data-footer') {
            $request->validate([
                'footer_data' => 'required|max:1000',
                // 'copyright_text' => 'nullable|max:100',

            ]);

            if ($request->footer_data[array_search('default', $request->lang)] == '') {
                Toastr::error(translate('default_footer_descrtption_is_required'));
                return back();
            }
            $this->update_data($request, $request->footer_key, 'footer_data');
            // $this->update_data($request , $request->key_copyright , 'copyright_text');

            Toastr::success(translate('messages.Admin_landing_page_footer_description_updated'));
            return back();
        } elseif ($tab == 'background-change') {
            Helpers::businessUpdateOrInsert(['key' => 'backgroundChange'], [
                'value' => json_encode([
                    'primary_1_hex' => $request['header-bg'],
                    'primary_1_rgb' => Helpers::hex_to_rbg($request['header-bg']),
                    'primary_2_hex' => $request['footer-bg'],
                    'primary_2_rgb' => Helpers::hex_to_rbg($request['footer-bg']),
                ])
            ]);
            Toastr::success(translate('messages.background_updated'));
        } elseif ($tab == 'admin-meta-data') {
            $request->validate([
                'meta_data_title.0' => 'required|max:254',
                'meta_data_description.0' => 'required|max:254',
            ], [
                'meta_data_title.0.required' => translate('default_meta_title_is_required'),
                'meta_data_description.0.required' => translate('default_meta_description_is_required'),
            ]);

            $this->getAddLandingPageData($request, 'landing_page', 'meta_data_title', false);
            $this->getAddLandingPageData($request, 'landing_page', 'meta_data_description', false);

            if ($request->meta_data_image_remove == '1') {
                $existing = DataSetting::where('type', 'landing_page')
                    ->where('key', 'meta_data_image')
                    ->first();

                if ($existing) {
                    Helpers::check_and_delete('meta_data_image', $existing->value);
                    $existing->delete();
                }
            }

            if ($request->hasFile('meta_data_image')) {
                $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'meta_data_image', multiLang: false, filePath: 'meta_data_image');
            }

            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_index', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_no_follow', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_no_image_index', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_no_archive', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_no_snippet', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_max_snippet', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_max_snippet_value', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_max_video_preview', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_max_video_preview_value', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_max_image_preview', multiLang: false);
            $this->getAddLandingPageData(request: $request, type: 'landing_page', key: 'admin_meta_max_image_preview_value', multiLang: false);

            Toastr::success(translate('messages.meta_data_updated'));
            return back();
        } elseif ($tab == 'links') {
            $this->updateLinks($request);
            Toastr::success(translate('messages.links_updated'));
            return back();
        }

        return back();
    }


    private function update_data($request, $key_data, $name_field, $type = 'admin_landing_page')
    {
        $data = DataSetting::firstOrNew(
            [
                'key' => $key_data,
                'type' => $type
            ],
        );

        $data->value = $request->{$name_field}[array_search('default', $request->lang)];
        $data->save();
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->{$name_field}[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\DataSetting',
                            'translationable_id' => $data->id,
                            'locale' => $key,
                            'key' => $key_data
                        ],
                        ['value' => $data->value]
                    );
                }
            } else {
                if ($request->{$name_field}[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\DataSetting',
                            'translationable_id' => $data->id,
                            'locale' => $key,
                            'key' => $key_data
                        ],
                        ['value' => $request->{$name_field}[$index]]
                    );
                }
            }
        }

        return true;
    }


    public function availableZone()
    {
        $type = ['react_available_zone_status'];
        $reactZone = $this->getLandingPageData('react_landing_page', $type);
        $react_available_zone_status = $reactZone['react_available_zone_status'] ?? null;

        $language = getWebConfig('language');
        $available_zone_title = DataSetting::withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'available_zone_title')->first();
        $available_zone_short_description = DataSetting::withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'available_zone_short_description')->first();
        $available_zone_image = DataSetting::withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'available_zone_image')->first();
        $available_zone_status = DataSetting::withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'available_zone_status')->first()?->value ?? 0;

        return view('admin-views.landing_page.available-zone', compact(
            'language',
            'available_zone_title',
            'available_zone_short_description',
            'available_zone_image',
            'available_zone_status'
        ));
    }

    public function reactAvailableZone()
    {

        $type = [
            'react_available_zone_status',
            'react_location_picker_status',
            'zone_location_picker_title',
            'zone_location_picker_description',
            'available_zone_title',
            'available_zone_short_description',
            'available_zone_image',
            'available_zone_status',

        ];
        $reactZone = $this->getLandingPageData('react_landing_page', $type);
        $react_available_zone_status = $reactZone['react_available_zone_status'] ?? null;
        $react_location_picker_status = $reactZone['react_location_picker_status'] ?? null;
        $zone_location_picker_title = $reactZone['zone_location_picker_title'] ?? null;
        $zone_location_picker_description = $reactZone['zone_location_picker_description'] ?? null;

        $language = Helpers::get_business_settings('language');

        $available_zone_title = $reactZone['available_zone_title'] ?? null;
        $available_zone_short_description = $reactZone['available_zone_short_description'] ?? null;
        $available_zone_image = $reactZone['available_zone_image'] ?? null;
        $available_zone_status = $reactZone['available_zone_status']?->value ?? null;

        return view('admin-views.landing_page.react.available-zone', compact(
            'language',
            'available_zone_title',
            'available_zone_short_description',
            'available_zone_image',
            'available_zone_status',
            'react_available_zone_status',
            'react_location_picker_status',
            'zone_location_picker_title',
            'zone_location_picker_description'
        ));
    }

    public function availableZoneUpdate(Request $request)
    {
        $request->validate([
            'available_zone_title.0' => 'required',

        ], [
            'available_zone_title.0.required' => translate('default_title_is_required'),
        ]);
        $page_type = $request?->page_type ?? 'admin_landing_page';

        $available_zone_title = DataSetting::where('type', $page_type)->where('key', 'available_zone_title')->first();
        if ($available_zone_title == null) {
            $available_zone_title = new DataSetting();
        }

        $available_zone_title->key = 'available_zone_title';
        $available_zone_title->type = $page_type;
        $available_zone_title->value = $request->available_zone_title[array_search('default', $request->lang)];
        $available_zone_title->save();

        $available_zone_short_description = DataSetting::where('type', $page_type)->where('key', 'available_zone_short_description')->first();
        if ($available_zone_short_description == null) {
            $available_zone_short_description = new DataSetting();
        }

        $available_zone_short_description->key = 'available_zone_short_description';
        $available_zone_short_description->type = $page_type;
        $available_zone_short_description->value = $request->available_zone_short_description[array_search('default', $request->lang)];
        $available_zone_short_description->save();

        $available_zone_image = DataSetting::where('type', $page_type)->where('key', 'available_zone_image')->first();

        if ($available_zone_image == null) {
            if ($request['available_zone_status'] && $available_zone_image?->value == null) {
                $request->validate([
                    'image' => 'required',
                ]);
            }

            $available_zone_image = new DataSetting();
        }
        $available_zone_image->key = 'available_zone_image';
        $available_zone_image->type = $page_type;
        $available_zone_image->value = $request->has('image') ? Helpers::update('available_zone_image/', $available_zone_image->value, 'png', $request->file('image')) : $available_zone_image->value ?? null;
        $available_zone_image->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'available_zone_title', name_field: 'available_zone_title', model_name: 'DataSetting', data_id: $available_zone_title->id, data_value: $available_zone_title->value);
        Helpers::add_or_update_translations(request: $request, key_data: 'available_zone_short_description', name_field: 'available_zone_short_description', model_name: 'DataSetting', data_id: $available_zone_short_description->id, data_value: $available_zone_short_description->value);

        Helpers::dataUpdateOrInsert(['type' => $page_type, 'key' => 'available_zone_status'], [
            'value' => $request['available_zone_status']
        ]);

        Toastr::success(translate('messages.available_zone_section_updated'));
        return back();
    }

    public function meta_data()
    {
        $keys = [
            'meta_data_title',
            'meta_data_description',
            'meta_data_image',
            'admin_meta_index',
            'admin_meta_no_follow',
            'admin_meta_no_image_index',
            'admin_meta_no_archive',
            'admin_meta_no_snippet',
            'admin_meta_max_snippet',
            'admin_meta_max_snippet_value',
            'admin_meta_max_video_preview',
            'admin_meta_max_video_preview_value',
            'admin_meta_max_image_preview',
            'admin_meta_max_image_preview_value'
        ];
        $reactHeaders = $this->getLandingPageData('landing_page', $keys);

        $meta_data_title = $reactHeaders['meta_data_title'] ?? null;
        $meta_data_description = $reactHeaders['meta_data_description'] ?? null;
        $meta_data_image = $reactHeaders['meta_data_image'] ?? null;

        $meta_index = $reactHeaders['admin_meta_index']->value ?? null;
        $meta_no_follow = $reactHeaders['admin_meta_no_follow']->value ?? null;
        $meta_no_image_index = $reactHeaders['admin_meta_no_image_index']->value ?? null;
        $meta_no_archive = $reactHeaders['admin_meta_no_archive']->value ?? null;
        $meta_no_snippet = $reactHeaders['admin_meta_no_snippet']->value ?? null;
        $meta_max_snippet = $reactHeaders['admin_meta_max_snippet']->value ?? null;
        $meta_max_snippet_value = $reactHeaders['admin_meta_max_snippet_value']->value ?? null;
        $meta_max_video_preview = $reactHeaders['admin_meta_max_video_preview']->value ?? null;
        $meta_max_video_preview_value = $reactHeaders['admin_meta_max_video_preview_value']->value ?? null;
        $meta_max_image_preview = $reactHeaders['admin_meta_max_image_preview']->value ?? null;
        $meta_max_image_preview_value = $reactHeaders['admin_meta_max_image_preview_value']->value ?? null;


        $language = Helpers::get_business_settings('language');

        $commonKeys = [
            'meta_data_title',
            'meta_data_description',
            'meta_data_image',
            'meta_index',
            'meta_no_follow',
            'meta_no_image_index',
            'meta_no_archive',
            'meta_no_snippet',
            'meta_max_snippet',
            'meta_max_snippet_value',
            'meta_max_video_preview',
            'meta_max_video_preview_value',
            'meta_max_image_preview',
            'meta_max_image_preview_value',
            'language'
        ];


        return view('admin-views.landing_page.meta_data', compact($commonKeys));
    }

    public function locationPickerUpdate(Request $request)
    {
        $request->validate([
            'zone_location_picker_title.0' => 'required',

        ], [
            'zone_location_picker_title.0.required' => translate('default_title_is_required'),
        ]);

        $page_type = $request?->page_type ?? 'admin_landing_page';

        $zone_location_picker_title = DataSetting::where('type', $page_type)->where('key', 'location_picku_title')->first();
        if ($zone_location_picker_title == null) {
            $zone_location_picker_title = new DataSetting();
        }

        $zone_location_picker_title->key = 'zone_location_picker_title';
        $zone_location_picker_title->type = $page_type;
        $zone_location_picker_title->value = $request->zone_location_picker_title[array_search('default', $request->lang)];
        $zone_location_picker_title->save();

        $zone_location_picker_description = DataSetting::where('type', $page_type)->where('key', 'zone_location_picker_description')->first();
        if ($zone_location_picker_description == null) {
            $zone_location_picker_description = new DataSetting();
        }

        $zone_location_picker_description->key = 'zone_location_picker_description';
        $zone_location_picker_description->type = $page_type;
        $zone_location_picker_description->value = $request->zone_location_picker_description[array_search('default', $request->lang)];
        $zone_location_picker_description->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'zone_location_picker_title', name_field: 'zone_location_picker_title', model_name: 'DataSetting', data_id: $zone_location_picker_title->id, data_value: $zone_location_picker_title->value);
        Helpers::add_or_update_translations(request: $request, key_data: 'zone_location_picker_description', name_field: 'zone_location_picker_description', model_name: 'DataSetting', data_id: $zone_location_picker_description->id, data_value: $zone_location_picker_description->value);

        Helpers::dataUpdateOrInsert(['type' => $page_type, 'key' => 'location_pickup_status'], [
            'value' => $request['location_pickup_status']
        ]);

        Toastr::success(translate('messages.location_pickup_section_updated'));
        return back();
    }

    private function getLandingPageData($type = 'react_landing_page', $keys = [])
    {
        return DataSetting::withoutGlobalScope('translate')
            ->with(['translations', 'storage'])
            ->where('type', $type)
            ->whereIn('key', $keys)
            ->get()
            ->keyBy('key');
    }

    public function statusUpdate($type, $key)
    {
        $dataSetting = DataSetting::firstOrNew([
            'type' => $type,
            'key' => $key,
        ]);
        $dataSetting->value = !$dataSetting->value;
        $dataSetting->save();
        Toastr::success(translate($key . ' ' . translate('updated_Successfully')));
        return back();
    }

    public function earn_money_section(Request $request)
    {
        $request->validate([
            'react_earn_money_section_title.0' => 'required',

        ], [
            'react_earn_money_section_title.0.required' => translate('default_title_is_required'),
        ]);

        $page_type = $request?->page_type ?? 'admin_landing_page';

        $react_earn_money_section_title = DataSetting::where('type', $page_type)->where('key', 'react_earn_money_section_title')->first();
        if ($react_earn_money_section_title == null) {
            $react_earn_money_section_title = new DataSetting();
        }

        $react_earn_money_section_title->key = 'react_earn_money_section_title';
        $react_earn_money_section_title->type = $page_type;
        $react_earn_money_section_title->value = $request->react_earn_money_section_title[array_search('default', $request->lang)];
        $react_earn_money_section_title->save();

        $react_earn_money_section_description = DataSetting::where('type', $page_type)->where('key', 'react_earn_money_section_description')->first();
        if ($react_earn_money_section_description == null) {
            $react_earn_money_section_description = new DataSetting();
        }

        $react_earn_money_section_description->key = 'react_earn_money_section_description';
        $react_earn_money_section_description->type = $page_type;
        $react_earn_money_section_description->value = $request->react_earn_money_section_description[array_search('default', $request->lang)];
        $react_earn_money_section_description->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'react_earn_money_section_title', name_field: 'react_earn_money_section_title', model_name: 'DataSetting', data_id: $react_earn_money_section_title->id, data_value: $react_earn_money_section_title->value);
        Helpers::add_or_update_translations(request: $request, key_data: 'react_earn_money_section_description', name_field: 'react_earn_money_section_description', model_name: 'DataSetting', data_id: $react_earn_money_section_description->id, data_value: $react_earn_money_section_description->value);

        //        Helpers::dataUpdateOrInsert(['type' => $page_type,'key' => 'location_pickup_status'], [
//            'value' => $request['location_pickup_status']
//        ]);

        Toastr::success(translate('messages.location_pickup_section_updated'));
        return back();
    }

    private function getAddLandingPageData($request, $type, $key, $multiLang, $filePath = '/')
    {

        $data = DataSetting::firstOrNew(['type' => $type, 'key' => $key]);

        if ($request->hasFile($key)) {
            $file = $request->file($key);
            $format = strtolower($file->getClientOriginalExtension() ?? 'png');
            $existingImage = $data->exists ? $data->value : null;
            $data->value = empty($existingImage)
                ? Helpers::upload(dir: $filePath, format: $format, image: $file)
                : Helpers::update(dir: $filePath, old_image: $existingImage, format: $format, image: $file);
        } else {

            if ($multiLang) {
                $defaultIndex = array_search('default', $request->lang);
                $data->value = $request->{$key}[$defaultIndex] ?? null;
            } else {
                $data->value = is_array($request->{$key}) ? $request->{$key}[0] : ($request->{$key} ?? null);
            }
        }
        $data->save();

        if ($multiLang) {
            Helpers::add_or_update_translations(
                request: $request,
                key_data: $key,
                name_field: $key,
                model_name: 'DataSetting',
                data_id: $data->id,
                data_value: $data->value
            );
        }

        return $data;

    }


    public function reactTestimonialStore(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'review' => 'required|max:1000',
            'image' => 'nullable|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
        ]);

        $imageFile = $request->file('image');
        $originalExtension = 'png';

        $testimonial = new ReactTestimonial();
        $testimonial->name = $request->name[array_search('default', $request->lang)];
        $testimonial->review = $request->review[array_search('default', $request->lang)];
        $testimonial->image = $request->hasFile('image') ? Helpers::upload(dir: 'reviewer_image/', format: $originalExtension, image: $imageFile) : null;
        $testimonial->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'ReactTestimonial', data_id: $testimonial->id, data_value: $testimonial->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'review', name_field: 'review', model_name: 'ReactTestimonial', data_id: $testimonial->id, data_value: $testimonial->review);

        Toastr::success(translate('messages.testimonial_added_successfully'));
        return back();
    }

    public function reactTestimonialStatus(Request $request)
    {

        if (getEnvMode() == 'demo' && $request->id == 1) {
            Toastr::warning('Sorry!You can not inactive this review!');
            return back();
        }
        $review = ReactTestimonial::findOrFail($request->id);
        $review->status = !$review->status;
        $review->save();
        Toastr::success(translate('messages.testimonial_status_updated'));
        return back();
    }

    public function reactTestimonialEdit($id)
    {
        $language = Helpers::get_business_settings('language');
        $testimonial = ReactTestimonial::withoutGlobalScope('translate')->with('translations')->findOrfail($id);

        return response()->json([
            'view' => view('admin-views.landing_page.react._testimonial_edit', compact('testimonial', 'language'))->render(),
        ]);
    }

    public function reactTestimonialUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:100',
            'review' => 'required|max:1000',
            'image' => 'nullable|max:2048',

        ]);
        $testimonial = ReactTestimonial::findOrFail($id);
        $testimonial->name = $request->name[array_search('default', $request->lang)];
        $testimonial->review = $request->review[array_search('default', $request->lang)];
        $testimonial->image = $request->hasFile('image') ? Helpers::update(dir: 'reviewer_image/', old_image: $testimonial->image, format: 'png', image: $request->file('image')) : $testimonial->image;
        $testimonial->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'ReactTestimonial', data_id: $testimonial->id, data_value: $testimonial->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'review', name_field: 'review', model_name: 'ReactTestimonial', data_id: $testimonial->id, data_value: $testimonial->review);


        Toastr::success(translate('messages.testimonial_updated_successfully'));
        return back();
    }

    public function reactTestimonialDestroy(ReactTestimonial $testimonial)
    {
        if (getEnvMode() == 'demo' && $testimonial->id == 1) {
            Toastr::warning(translate('messages.you_can_not_delete_this_review_please_add_a_new_review_to_delete'));
            return back();
        }
        Helpers::check_and_delete('reviewer_image/', $testimonial->image);
        $testimonial->delete();
        Toastr::success(translate('messages.testimonial_deleted_successfully'));
        return back();
    }


    public function reactFaqStore(Request $request)
    {
        $request->validate([
            'question' => 'required|max:100',
            'answer' => 'required|max:1000',
        ]);

        $faq = new FAQ();
        $faq->question = $request->question[array_search('default', $request->lang)];
        $faq->answer = $request->answer[array_search('default', $request->lang)];
        $faq->user_type = $request->user_type ?? 'customer';
        $faq->page_type = 'react_landing_page';
        $faq->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'question', name_field: 'question', model_name: 'FAQ', data_id: $faq->id, data_value: $faq->question);
        Helpers::add_or_update_translations(request: $request, key_data: 'answer', name_field: 'answer', model_name: 'FAQ', data_id: $faq->id, data_value: $faq->answer);

        Toastr::success(translate('messages.faq_added_successfully'));
        return back();
    }

    public function reactFaqStatus(Request $request)
    {

        if (getEnvMode() == 'demo' && $request->id == 1) {
            Toastr::warning('Sorry!You can not inactive this faq!');
            return back();
        }
        $faq = FAQ::findOrFail($request->id);
        $faq->status = !$faq->status;
        $faq->save();
        Toastr::success(translate('messages.Faq_status_updated'));
        return back();
    }

    public function reactfaqEdit($id)
    {
        $language = Helpers::get_business_settings('language');
        $faq = FAQ::withoutGlobalScope('translate')->with('translations')->findOrfail($id);

        return response()->json([
            'view' => view('admin-views.landing_page.react._faq_edit', compact('faq', 'language'))->render(),
        ]);
    }

    public function reactFaqUpdate(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|max:100',
            'answer' => 'required|max:1000',

        ]);
        $faq = FAQ::findOrFail($id);
        $faq->question = $request->question[array_search('default', $request->lang)];
        $faq->answer = $request->answer[array_search('default', $request->lang)];
        $faq->user_type = $request->user_type ?? 'customer';

        $faq->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'question', name_field: 'question', model_name: 'FAQ', data_id: $faq->id, data_value: $faq->question);
        Helpers::add_or_update_translations(request: $request, key_data: 'answer', name_field: 'answer', model_name: 'FAQ', data_id: $faq->id, data_value: $faq->answer);


        Toastr::success(translate('messages.Faq_updated_successfully'));
        return back();
    }

    public function reactFaqDestroy(FAQ $faq)
    {
        if (getEnvMode() == 'demo' && $faq->id == 1) {
            Toastr::warning(translate('messages.you_can_not_delete_this_review_please_add_a_new_review_to_delete'));
            return back();
        }
        $faq->delete();
        Toastr::success(translate('messages.faq_deleted_successfully'));
        return back();
    }

    public function landingPageSetup(Request $request)
    {
        return view('admin-views.business-settings.landing-index');
    }

    public function landingPageSettingsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'landing_integration_via' => 'required_if:choose_admin_landing,custom',
            'redirect_url' => 'required_if:landing_integration_via,url',
            'file_upload' => 'mimes:zip'
        ]);

        if (! File::exists(base_path('resources/views/layouts/landing/custom/index.blade.php')) && ($request->landing_integration_via == 'file_upload') && (! $request->file('file_upload'))) {
            $validator->getMessageBag()->add('file_upload', translate('messages.zip_file_is_required'));
        }

        if ($validator->errors()->count() > 0) {
            $error = Helpers::error_processor($validator);
            return response()->json(['status' => 'error', 'message' => $error[0]['message']]);
        }

        Helpers::businessUpdateOrInsert(['key' => 'landing_integration_type'], [
            'value' => $request['landing_integration_via']
        ]);
        $status = 'success';
        $message = translate('updated_successfully!');

        if ($request->landing_integration_via == 'file_upload') {

            $file = $request->file('file_upload');
            if ($file) {
                try {
                    Helpers::validateFile($file);
                } catch (\App\Exceptions\InvalidUploadException $e) {
                    return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
                }

                $filename = $file->getClientOriginalName();
                $tempPath = $file->storeAs('temp', $filename);
                $zip = new \ZipArchive;

                if ($zip->open(storage_path('app/'.$tempPath)) === true) {
                    // Extract the contents to a directory
                    $extractPath = base_path('resources/views/layouts/landing/custom');
                    if (!File::isWritable($extractPath)) {
                        $status = 'error';
                        $message = translate('messages.File is not writable. Please check your file permissions.');
                    }
                    $zip->extractTo($extractPath);
                    $zip->close();
                    if (File::exists($extractPath.'/index.blade.php')) {
                        if (!File::isWritable($extractPath.'/index.blade.php')) {
                            $status = 'error';
                            $message = translate('messages.File is not writable. Please check your file permissions.');
                        }
                        Toastr::success(translate('file_upload_successfully!'));
                        $status = 'success';
                        $message = translate('file_upload_successfully!');
                    } else {
                        File::deleteDirectory($extractPath);
                        $status = 'error';
                        $message = translate('invalid_file!');
                    }
                } else {
                    $status = 'error';
                    $message = translate('file_upload_fail!');
                }

                Storage::delete($tempPath);
            }
        }

        if ($request->landing_integration_via == 'url') {
            Helpers::businessUpdateOrInsert(['key' => 'landing_page_custom_url'], [
                'value' => $request['redirect_url']
            ]);

            $status = 'success';
            $message = translate('url_saved_successfully!');
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }
    public function deleteCustomLandingPage()
    {
        $filePath = base_path('resources/views/layouts/landing/custom/index.blade.php');

        if (File::exists($filePath)) {
            if (!File::isWritable($filePath)) {
                Toastr::error(translate('messages.File is not writable. Please check your file permissions.'));
                return back();
            }
            File::delete($filePath);
            Toastr::success(translate('messages.File_deleted_successfully'));
            return back();
        } else {
            Toastr::error(translate('messages.File_not_found'));
            return back();
        }
    }

    public function downloadCustomLandingPage()
    {
        $filePath = base_path('resources/views/layouts/landing/custom/index.blade.php');

        if (File::exists($filePath)) {
            return response()->download($filePath);
        } else {
            Toastr::error(translate('messages.File_not_found'));
            return back();
        }
    }


}


