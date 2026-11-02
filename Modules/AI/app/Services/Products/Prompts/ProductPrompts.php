<?php

namespace Modules\AI\app\Services\Products\Prompts;

use App\CentralLogics\Helpers;
use Modules\AI\app\Services\Products\Resource\ProductResource;

class ProductPrompts
{
    protected ProductResource $ProductResource;

    public function __construct()
    {
        $this->ProductResource = new ProductResource();
    }



    public function titleAutoFill(string $name, string $langCode = "en")
    {
        $langCode = strtoupper($langCode);

        $promptText = <<<PROMPT
      You are a professional e-commerce copywriter.

      Rewrite the product name "{$name}" as a clean, concise, and professional product title for online stores.

      CRITICAL INSTRUCTION:
      - The output must be 100% in {$langCode} — this is mandatory.
      - If the original name is not in {$langCode}, fully translate it into {$langCode} while keeping the meaning.
      - Do not mix languages; use only {$langCode} characters and words.
      - Keep it short (35–70 characters), plain, and ready for listings.
      - No extra words, slogans, or punctuation.
      - Return only the translated title as plain text in {$langCode}.

      PROMPT;

        return $promptText;
    }




    public function descriptionAutoFill(string $name, string $langCode = "en")
    {
        $langCode = strtoupper($langCode);

        $promptText = <<<PROMPT
        You are a creative and professional food copywriter.

        Generate a detailed, engaging, and persuasive product description for the product named "{$name}".

        CRITICAL LANGUAGE RULES:
        - The entire description must be written 100% in {$langCode} — this is mandatory.
        - If the product name is in another language, translate and localize it naturally into {$langCode}.
        - Do not mix languages; use only {$langCode} characters and words.
        - Adapt the tone, phrasing, and examples to be natural for {$langCode} readers.

        Content & Structure:
        - Include a section with key features as separate paragraphs with its ingredients.
        - Focus on benefits, unique selling points, and appeal to the target audience.
        - Use clear, compelling, and marketing-friendly language.
        - Ensure the description is engaging and interesting.
        - Avoid any non-product-specific information.
        - Must be in 200–500 characters.
        - Keep it short and to the point, plain, simple and ready for listings.

        Formatting:
        - Output valid Product descriptions.
        - Do NOT include any markdown syntax, code fences, or triple backticks.
        - Return only plain text in the paragraph (no HTML tags, no empty lines).


        PROMPT;

        return $promptText;
    }






    public function generalSetupAutoFill(string $name, string $description, $restaurant_id)
    {
        $resource = $this->ProductResource->productGeneralSetupData($restaurant_id);
        $categories      = $resource['categories'];
        $subCategories   = $resource['sub_categories'];
        $rawSubCategories   = $resource['rawSubCategories'];
        $rawSubCategories   = json_encode($rawSubCategories);

        $nutrition   = $resource['nutrition'];
        $allergy   = $resource['allergy'];
        $addon   = $resource['addon'];

        $productTypes    = $resource['product_types'];

        $categories =  json_encode($categories);
        $subCategories = implode("', '", array_keys($subCategories));
        $nutrition = implode("', '", array_keys($nutrition));
        $allergy = implode("', '", array_keys($allergy));
        $addon = implode("', '", array_keys($addon));


        $productTypes = implode("', '", $productTypes);

        $promptText = <<<PROMPT
                 Analyze the product with these details:
                 - Name: '{$name}'
                 - Description: '{$description}'

                 Generate ONLY valid JSON with these exact fields:
                    {
                    "category_name": "Category name", // must be from '{$categories}' the key is the name and value is the id and match relevant keywords from name/description
                    "sub_category_name": "Sub-category name", // must be from '{$rawSubCategories}', must belong to the selected category_name, which must be from '{$categories}' id as the  parent_id  and relevant to name/description, suggestions are forbidden
                    "nutrition": ["Nutrition1", "Nutrition2"], // comma-separated string, prefer '{$nutrition}', if no match then suggest new based on '{$description}', must relate to keywords from name/description
                    "allergy": ["Allergy1", "Allergy2"], // comma-separated string, prefer '{$allergy}', if no match then suggest new based on '{$description}', must relate to keywords from name/description — optional
                    "addon": ["Addon1", "Addon2"], // comma-separated string, prefer '{$addon}', if no match then suggest new based on '{$description}', must relate to keywords from name/description or common  — optional
                    "product_type": "Product type", // must be from '{$productTypes}'
                    "search_tags": ["tag1", "tag2", "tag3"] // array of 3–5 relevant keywords from name/description
                    "is_halal": true, // true if halal, false if not halal
                    "available_time_starts": "00:00:00", // use your intelligence to generate available time in 12-hour format and must be at morning,AM
                    "available_time_ends": "23:59:00", // use your intelligence to generate available end time in 12-hour format and must be at night, PM
                    }

                    STRICT RULES:

                    Enforce category hierarchy (category → sub-category).
                    Sub-category must be relevant to name/description and must be form '{$subCategories}' donot suggest new.
                    Use provided options if a suitable match exists (case-sensitive).
                    For nutrition, allergy, and addon:
                        Always return as a comma-separated array.
                        If no provided option matches, generate new suggestions from description and relevant keywords except for addon.
                    Do not generate new suggestions for category_name, sub_category_name, addon, or product_type.
                    Choose the most specific valid category.
                    Generate 3–5 search tags from the product name/description.
                    All fields are required, except allergy and addon (optional).

                 === AVAILABLE OPTIONS ===
                 [MAIN CATEGORIES] '{$categories}'
                 [SUB CATEGORIES] '{$subCategories}'
                 [NUTRITION] '{$nutrition}'
                 [ALLEGY] '{$allergy}'
                 [ADDON] '{$addon}'
                 [PRODUCT TYPES] '{$productTypes}'

                 === RESPONSE FORMAT ===
                 - Output ONLY the JSON object.
                 - Do NOT include any explanations, comments, or markdown formatting.
                 - Do NOT wrap the JSON in ```json or any other code block.
                 - Ensure the JSON is syntactically valid for json_decode in PHP.
                 PROMPT;


        return $promptText;
    }

    public function PriceOthersAutoFill(string $name, $description = null)
    {
        $currency = Helpers::currency_symbol();

        $productInfo = $description
            ? "Product name: \"{$name}\". Description: \"" . addslashes($description) . "\"."
            : "Product name: \"{$name}\".";

        $promptText = <<<PROMPT
                  You are an expert pricing analyst.

                  Given the following product information:

                  {$productInfo}

                  Using the currency symbol "{$currency}", provide ONLY a JSON object with pricing details below.
                  Set realistic values based on the product info and currency.

                  The JSON must contain exactly these fields:

                  {
                    "unit_price": 100.00, // must be competitive or standard price based on {$productInfo} accordeing to currency and market demand
                    "minimum_order_quantity": 1-10, // any positive random integer
                    "discount_amount": 0.00, // this is the percentage of the unit_price, this must be less than unit_price and intiger and can be 0 or random and a required field

                  }

                  IMPORTANT: Return ONLY the pure JSON text with no markdown, no code fences, no extra text or explanation.
                  PROMPT;

        return $promptText;
    }
    public function seoSectionAutoFill(string $name, $description = null)
    {
        $productInfo = $description
            ? "Product name: \"{$name}\". Description: \"" . addslashes($description) . "\"."
            : "Product name: \"{$name}\".";

        $promptText = <<<PROMPT
                    You are an expert SEO content writer and technical SEO specialist.

                    Given the following product information:

                    {$productInfo}

                    Generate ONLY a JSON object with the following SEO meta fields:

                    {
                      "meta_title": "",                  // Concise SEO title (max 100 chars)
                      "meta_description": "",            // Compelling meta description (max 160 chars)

                      "meta_index": "index",             // Either "index" or "noindex"
                      "meta_no_follow": 0,               // 0 or 1 (boolean)
                      "meta_no_image_index": 0,          // 0 or 1
                      "meta_no_archive": 0,              // 0 or 1
                      "meta_no_snippet": 0,              // 0 or 1

                      "meta_max_snippet": 0,             // 0 or 1
                      "meta_max_snippet_value": -1,      // Number, -1 means no limit

                      "meta_max_video_preview": 0,       // 0 or 1
                      "meta_max_video_preview_value": -1,// Number, -1 means no limit

                      "meta_max_image_preview": 0,       // 0 or 1
                      "meta_max_image_preview_value": "large"  // One of "large", "medium", or "small"
                    }

                    Instructions:
                    - Use natural, clear language optimized for search engines.
                    - Choose values for index/noindex and booleans based on product info.
                    - Keep character limits for title and description.
                    - Return ONLY the pure JSON text without markdown, code fences, or explanations.
                    PROMPT;

        return $promptText;
    }


    public function variationSetupAutoFill(string $name, string $description)
    {
        $promptText = <<<PROMPT
                       You are an expert food product specialist with deep knowledge of food variations.

                        Given the following product:

                        Name: {$name}

                        Description: {$description}

                        Strict Rules:

                        Return ONLY a JSON array with the following structure for food variations (no explanations, no markdown, no text outside JSON).
                        "variation_name" must be a string // Must be based on '{$name}' and '{$description}' .
                        "required" is a boolean.
                        "selection_type" must be "multi" or "single".
                        "min" and "max" are integers and min must be grater then 0 and (min ≤ max) but selection_type: "single" both must be null .
                        Each variation must have at least 2 options.
                        "option_price" must be a positive number.
                        Generate at least one variation per food item, more if suggested by the description (e.g., rice, soup, sauces, toppings).
                        Options must be realistic and relevant to the food description.
                        JSON Schema Example (structure only, values are samples):
                        [
                        {
                        "variation_name": "Rice",
                        "required": true,
                        "selection_type": "multi",
                        "min": 1,
                        "max": 2,
                        "options": [
                        { "option_name": "Fried Rice", "option_price": 10 },
                        { "option_name": "White Rice", "option_price": 20 }
                        ]
                        },
                        {
                        "variation_name": "Soup",
                        "required": false,
                        "selection_type": "single",
                        "min": null,
                        "max": null,
                        "options": [
                        { "option_name": "Thai Soup", "option_price": 15 },
                        { "option_name": "Corn Soup", "option_price": 12 }
                        ]
                        }
                        ]

                        Output Format Rules:

                        Return ONLY the raw JSON array — no code blocks, no markdown, no explanation, no labels, no timestamps, no extra text.

                        The response must start with [ and end with ].

                        Ensure the JSON is syntactically valid for json_decode in PHP.
                PROMPT;

        return $promptText;
    }

    public function imageAnalysisAutoFill(string $langCode = "en")
    {
        $langCode = strtoupper($langCode);

        $promptText = <<<PROMPT
            You are an advanced food product analyst with strong skills in image recognition.

            Analyze the uploaded product image provided by the user.
            Your task is to generate a clean, concise, and professional product title for online stores.

            CRITICAL INSTRUCTION:
            - The output must be 100% in {$langCode} — this is mandatory.
            - Identify the main product in the image and name it clearly.
            - Do not add extra descriptions like "high quality" or "best".
            - Keep it short (35–70 characters), plain, and ready for listings.
            - Return only the translated product title as plain text in {$langCode}.

            PROMPT;

        return $promptText;
    }
    public function generateTitleSuggestions(array $keywords, string $langCode = "en")
    {
        $langCode = strtoupper($langCode);
        $keywordsText = implode(' ', $keywords);

        $promptText = <<<PROMPT
               You are an advanced e-commerce product analyst.

               Using the keywords "{$keywordsText}", generate 4 professional, clean, and concise product titles for online stores.

               CRITICAL INSTRUCTIONS:
               - The output must be 100% in {$langCode}.
               - Titles must use the keywords naturally.
               - Keep them short (35–70 characters), clear, and ready for listings.
               - Return exactly 4 titles in **plain JSON** format as shown below (do not include ```json``` or any extra markdown):

               {
                 "titles": [
                   "Title 1",
                   "Title 2",
                   "Title 3",
                   "Title 4"
                 ]
               }

               Do not include any extra explanation, only return the JSON.
               PROMPT;

        return $promptText;
    }
}
