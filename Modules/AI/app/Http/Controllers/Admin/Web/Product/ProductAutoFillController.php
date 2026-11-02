<?php

namespace Modules\AI\app\Http\Controllers\Admin\Web\Product;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\RestaurantConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\AI\app\Services\Products\Action\ProductAutoFillService;
use Modules\AI\app\Services\Products\Response\ProductResponse;
use Illuminate\Support\Facades\Validator;
use Modules\AI\app\Traits\ConversationTrait;

class ProductAutoFillController extends Controller
{

    use ConversationTrait;
    public function __construct(
        private  ProductAutoFillService $productAutoFillService,
        private ProductResponse $productResponse,
    ) {
        if (getEnvMode() == 'demo') {
            $ip = request()->header('x-forwarded-for');
            $cacheKey = "restricted_ip_" . $ip;

            $hits = Cache::store('file')->get($cacheKey, 0);

            if ($hits >= 10) {
                abort(403, translate('Demo Mode Restriction: This feature can only be accessed 10 times in demo mode. Further attempts are disabled to maintain a fair demo experience.'));
            }

            Cache::store('file')->forever($cacheKey, $hits + 1);
        }
    }

    public function titleAutoFill(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'langCode' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Please provide a product name so the AI can generate a suitable title or description.',
            'name.max' => 'The product name may not exceed 255 characters.',
        ]);
        // Helpers::get_business_settings('image_upload_limit_for_ai');
        $increment = false;
        if ($request->requestType == 'vendor' && $request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');
            if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {
                abort(403, translate('You have reached the limit of AI usage.'));
            }
            $increment = true;
        }


        $result = $this->productAutoFillService->titleAutoFill(
            $request->name,
            $request->langCode
        );

        if ($increment == true) {
            $RestaurantConfig->increment('section_wise_ai_use_count');
        }

        return $this->productResponse->titleAutoFill($result);
    }
    public function descriptionAutoFill(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'langCode' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Please provide a default name so the AI can generate a description.',
            'name.max' => 'The product name may not exceed 255 characters.',
        ]);

        $increment = false;
        if ($request->requestType == 'vendor' && $request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');
            if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {
                abort(403, translate('You have reached the limit of AI usage.'));
            }
            $increment = true;
        }

        $result = $this->productAutoFillService->descriptionAutoFill(
            $request->name,
            $request->langCode
        );

        if ($increment == true) {
            $RestaurantConfig->increment('section_wise_ai_use_count');
        }


        return $this->productResponse->discriptionAutoFill($result);
    }
    public function GeneralSetupAutoFill(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ], [
            'name.required' => 'Please provide a default name so the AI can generate a Data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description.required' => 'Please provide a default description so the AI can generate a Data.',
        ]);
        $increment = false;
        if ($request->requestType == 'vendor' && $request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');
            if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {
                abort(403, translate('You have reached the limit of AI usage.'));
            }
            $increment = true;
        }

        $result = $this->productAutoFillService->generalSetupAutoFill(
            $request->name,
            $request->description,
            $request->restaurant_id
        );

        if ($increment == true) {
            $RestaurantConfig->increment('section_wise_ai_use_count');
        }


        return $this->productResponse->productGeneralSetupAutoFill($result, $request->restaurant_id);
    }

    public function PriceOthersAutoFill(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ],
            [
            'name.required' => 'Please provide a default name so the AI can generate a Data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description' => 'Please provide a default description so the AI can generate a Data.',
            ]);

        $increment = false;
        if ($request->requestType == 'vendor' && $request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');
            if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {
                abort(403, translate('You have reached the limit of AI usage.'));
            }
            $increment = true;
        }

        $result = $this->productAutoFillService->PriceOthersAutoFill(
            $request->name,
            $request->description,
        );


        if ($increment == true) {
            $RestaurantConfig->increment('section_wise_ai_use_count');
        }

        return $this->productResponse->productPriceOthersAutoFill($result);
    }

    public function seoSectionAutoFill(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $increment = false;
        if ($request->requestType == 'vendor' && $request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');
            if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {
                abort(403, translate('You have reached the limit of AI usage.'));
            }
            $increment = true;
        }
        $result = $this->productAutoFillService->seoSectionAutoFill(
            $request->name,
            $request->description,
        );


        if ($increment == true) {
            $RestaurantConfig->increment('section_wise_ai_use_count');
        }

        return $this->productResponse->productseoAutoFill($result);
    }


    public function variationSetupAutoFill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required',
        ],[
            'name.required' => 'Please provide a default name so the AI can generate a Data.',
            'name.max' => 'The product name may not exceed 255 characters.',
            'description.required' => 'Please provide a default description so the AI can generate a Data.',
        ]);
        $increment = false;
        if ($request->requestType == 'vendor' && $request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');
            if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {
                abort(403, translate('You have reached the limit of AI usage.'));
            }
            $increment = true;
        }

        $description = $request->input('description');
        $this->descriptionEmptyValidation($description, $validator);
        if ($validator->fails()) {
            return response()->json(
                $this->inputValidationErrors($validator->errors()->toArray()),
                422
            );
        }


        $result = $this->productAutoFillService->variationSetupAutoFill(
            $request->name,
            $request->description,
        );

        if ($increment == true) {
            $RestaurantConfig->increment('section_wise_ai_use_count');
        }
        return $this->productResponse->variationSetupAutoFill($result);
    }
    public function analyzeImageAutoFill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
        ], [
            'image.required' => 'Image is required for analysis.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'image.max' => 'Image size must not exceed 1MB.',
        ]);
        if ($validator->fails()) {
            return response()->json(
                $this->inputValidationErrors($validator->errors()->toArray()),
                422
            );
        }

        $increment = false;
        if ($request->requestType == 'image' && $request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            $image_upload_limit_for_ai =  Helpers::get_business_settings('image_upload_limit_for_ai');
            if ($image_upload_limit_for_ai && $image_upload_limit_for_ai <=  $RestaurantConfig?->image_wise_ai_use_count) {
                abort(403, translate('You have reached the limit of AI usage via Image.'));
            }
            $increment = true;
        }

        if ($increment == true) {
            $RestaurantConfig->increment('image_wise_ai_use_count');
        }


        $extension = $request->image->getClientOriginalExtension();
        $imageName = Helpers::upload(dir: 'product/ai_product_image', format: $extension, image: $request->image);

        $imageUrl = $this->ai_product_image_full_path($imageName);

        // dd($imageUrl);
        // this is for the local development purpose start

       //$imageUrl = "https://powermaccenter.com/cdn/shop/files/iPhone_16_Pink_PDP_Image_Position_1__en-WW.jpg";

        // this is for the local development purpose end

        $result = $this->productAutoFillService->imageAnalysisAutoFill(
            imageUrl: $imageUrl,
        );

        Helpers::check_and_delete(dir: 'product/ai_product_image/', old_image: $imageName);

        return $this->productResponse->analyzeImageAutoFill($result);
    }

    public function generateTitleSuggestions(Request $request)
    {
        $validated = $request->validate([
            'keywords' => 'required|string|max:255',
        ]);
        $increment = false;
        if ($request->requestType == 'vendor' && $request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');
            if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {
                abort(403, translate('You have reached the limit of AI usage.'));
            }
            $increment = true;
        }
        $keywords = array_map('trim', explode(',', $request->keywords));
        $result = $this->productAutoFillService->generateTitleSuggestions($keywords);


        if ($increment == true) {
            $RestaurantConfig->increment('section_wise_ai_use_count');
        }

        return $this->productResponse->generateTitleSuggestions($result);
    }
}
