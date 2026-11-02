<?php

namespace Modules\AI\app\Http\Controllers\Api;

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



    public function getData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }



        Helpers::system_default_language();
        $language = Helpers::get_business_settings('system_language');

        foreach ($language ?? [] as $key => $langCode) {
            $langCodes[] = $langCode['code'];
        }

        $defaultTitle = null;
        $defaultDescription = null;


        foreach (['en'] as $langCode) {

            $title = $this->productAutoFillService->titleAutoFill(
                $request->name,
                $langCode ??  Helpers::system_default_language(),
            );

            $titles[$langCode] = $title;
            $description = $this->productAutoFillService->descriptionAutoFill(
                $request->name,
                $langCode ??  Helpers::system_default_language(),
            );

            $descriptions[$langCode] = $description;

            if ($langCode == Helpers::system_default_language()) {
                $defaultTitle = $title;
                $defaultDescription = $description;
            }
        }

        $generalData = $this->productAutoFillService->generalSetupAutoFill(
            $defaultTitle,
            $defaultDescription,
            2
        );
        $priceData = $this->productAutoFillService->PriceOthersAutoFill(
            $defaultTitle,
            $defaultDescription,
        );

        $seoData = $this->productAutoFillService->seoSectionAutoFill(
            $defaultTitle,
            $defaultDescription,
        );


        $variationData = $this->productAutoFillService->variationSetupAutoFill(
            $defaultTitle,
            $defaultDescription,
        );

        // dd($defaultTitle,$defaultDescription, $generalData, $priceData, $seoData, $variationData);

    }


    public function getTitleAndDescription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'langCode' => 'required'
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $increment = null;
        if ($request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            if ($request->requestType == 'image') {
                $image_upload_limit_for_ai =  Helpers::get_business_settings('image_upload_limit_for_ai');
                if ($image_upload_limit_for_ai && $image_upload_limit_for_ai <=  $RestaurantConfig?->image_wise_ai_use_count) {


                    return response()->json([
                        'errors' => [
                            ['code' => 'order', 'message' => translate('You have reached the limit of AI usage via Image.')]
                        ]
                    ], 403);
                }
            } else {
                $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');

                if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {

                    return response()->json([
                        'errors' => [
                            ['code' => 'order', 'message' => translate('You have reached the limit of AI usage.')]
                        ]
                    ], 403);
                }
                $increment = true;
            }
        }



        $title = $this->productAutoFillService->titleAutoFill(
            $request->name,
            $request->langCode ??  Helpers::system_default_language(),
        );

        $description = $this->productAutoFillService->descriptionAutoFill(
            $request->name,
            $request->langCode ??  Helpers::system_default_language(),
        );

        if ($increment == true) {
            $RestaurantConfig->increment('section_wise_ai_use_count', 2);
        }

        return response()->json([
            'title' => $title,
            'description' => $description,
        ]);
    }


    public function getOtherData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }


        $increment = false;
        if ($request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            if ($request->requestType == 'image') {
                $image_upload_limit_for_ai =  Helpers::get_business_settings('image_upload_limit_for_ai');
                if ($image_upload_limit_for_ai && $image_upload_limit_for_ai <=  $RestaurantConfig?->image_wise_ai_use_count) {
                    return response()->json([
                        'errors' => [
                            ['code' => 'order', 'message' => translate('You have reached the limit of AI usage via Image.')]
                        ]
                    ], 403);
                }
            } else {
                $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');
                if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {
                    return response()->json([
                        'errors' => [
                            ['code' => 'order', 'message' => translate('You have reached the limit of AI usage.')]
                        ]
                    ], 403);
                }
                $increment = true;
            }
        }


        $generalData = $this->productAutoFillService->generalSetupAutoFill(
            $request->name,
            $request->description,
            $request->restaurant_id
        );
        $generalData =  $this->productResponse->productGeneralSetupAutoFill($generalData, $request->restaurant_id);

        $priceData = $this->productAutoFillService->PriceOthersAutoFill(
            $request->name,
            $request->description,
        );

        $priceData = $this->productResponse->productPriceOthersAutoFillApi($priceData);

        $seoData = $this->productAutoFillService->seoSectionAutoFill(
            $request->name,
            $request->description,
        );
        $seoData = $this->productResponse->productseoAutoFillApi($seoData);

        if ($increment == true) {
            $RestaurantConfig->increment('section_wise_ai_use_count', 3);
        }


        return response()->json([
            'generalData' => $generalData,
            'priceData' => $priceData,
            'seoData' => $seoData,
        ]);
    }

    public function getVariationData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }


        $increment_image = false;
        $increment = false;
        if ($request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            if ($request->requestType == 'image') {
                $image_upload_limit_for_ai =  Helpers::get_business_settings('image_upload_limit_for_ai');
                if ($image_upload_limit_for_ai && $image_upload_limit_for_ai <=  $RestaurantConfig?->image_wise_ai_use_count) {

                    return response()->json([
                        'errors' => [
                            ['code' => 'order', 'message' => translate('You have reached the limit of AI usage via Image.')]
                        ]
                    ], 403);
                }
                $increment_image = true;
            } else {
                $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');
                if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {

                    return response()->json([
                        'errors' => [
                            ['code' => 'order', 'message' => translate('You have reached the limit of AI usage.')]
                        ]
                    ], 403);
                }
                $increment = true;
            }
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
        } elseif ($increment_image == true) {
            $RestaurantConfig->increment('image_wise_ai_use_count');
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

        $extension = $request->image->getClientOriginalExtension();
        $imageName = Helpers::upload(dir: 'product/ai_product_image', format: $extension, image: $request->image);

        $imageUrl = $this->ai_product_image_full_path($imageName);

        // dd($imageUrl);
        // this is for the local development purpose start

        // $imageUrl = "https://powermaccenter.com/cdn/shop/files/iPhone_16_Pink_PDP_Image_Position_1__en-WW.jpg";

        // this is for the local development purpose end

        $title = $this->productAutoFillService->imageAnalysisAutoFill(
            imageUrl: $imageUrl,
        );

        Helpers::check_and_delete(dir: 'product/ai_product_image/', old_image: $imageName);
        return response()->json(['title' => $title]);
    }

    public function generateTitleSuggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keywords' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }


        $increment = false;
        if ($request->restaurant_id) {
            $RestaurantConfig =  RestaurantConfig::where('restaurant_id', $request->restaurant_id)->first();
            $section_wise_ai_limit =  Helpers::get_business_settings('section_wise_ai_limit');
            if ($section_wise_ai_limit && $section_wise_ai_limit <=  $RestaurantConfig?->section_wise_ai_use_count) {
                return response()->json([
                    'errors' => [
                        ['code' => 'order', 'message' => translate('You have reached the limit of AI usage.')]
                    ]
                ], 403);
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
