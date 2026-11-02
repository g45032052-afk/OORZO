<?php

namespace Modules\AI\app\Traits;

trait ConversationTrait
{
    public static function productGeneralSetconvertNamesToIds(array $data, array $resources): array
    {
        if (isset($data['category_name'])) {
            $categoryName = strtolower(trim($data['category_name']));
            if (isset($resources['categories'][$categoryName])) {
                $data['category_id'] = $resources['categories'][$categoryName];
            } else {
                $errors[] = "Invalid category name: {$data['category_name']}";
            }
        }

        if (isset($data['sub_category_name'])) {
            $subCategoryName = strtolower(trim($data['sub_category_name']));
            if (isset($resources['sub_categories'][$subCategoryName])) {
                $data['sub_category_id'] = $resources['sub_categories'][$subCategoryName];
            }
        }

        if (isset($data['addon']) && is_array($data['addon'])) {
            $addonsMapped = [];
            $addonsIds = [];
            foreach ($data['addon'] as $addon) {
                $key = strtolower(trim($addon));
                if (isset($resources['addon'][$key])) {
                    $addonsMapped[$key] = $resources['addon'][$key];
                    $addonsIds[] = $resources['addon'][$key];
                }
            }
            $data['addonsNames'] =  isset($data['addon']) && is_array($data['addon']) ? $data['addon'] : [];
            $data['addon'] = $addonsMapped;
            $data['addonsIds'] =  $addonsIds;
        }



        if (!empty($errors)) {
            return [
                'success' => false,
                'error' => implode(', ', $errors),
                'invalid_fields' => $errors
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    private function formatAIGenerationValidationErrors(array $errors): array
    {
        $messages = [];

        foreach ($errors as $field => $message) {
            $messages[] = $message;
        }

        return [
            'message' => 'AI failed to generate: ' . implode(' ', $messages)
        ];
    }

    private function inputValidationErrors(array $errors): array
    {
        $messages = [];

        foreach ($errors as $fieldMessages) {
            if (is_array($fieldMessages)) {
                $messages = array_merge($messages, $fieldMessages);
            } else {
                $messages[] = $fieldMessages;
            }
        }

        return [
            'message' =>  implode(' ', $messages)
        ];
    }

    private function descriptionEmptyValidation(string $description, $validator)
    {
        if(!$description){
            $validator->after(function ($validator) {
                $validator->errors()->add('description', translate('Product_description_is_required') . '!');
            });
        }
        if (is_array($description)) {
            $first = reset($description);
            $rawDescription = is_string($first) ? $first : '';
        } else {
            $rawDescription = is_string($description) ? $description : '';
        }
        $cleanedDescription = trim(preg_replace('/\s+/', ' ', strip_tags($rawDescription)));

        if (empty($cleanedDescription)) {
            $validator->after(function ($validator) {
                $validator->errors()->add('description', translate('Product_description_is_required') . '!');
            });
        }
    }

    private function cleanAIHtmlOutput(string $aiOutput): string
    {
        $aiOutput = preg_replace('/^```(?:html)?\s*/i', '', $aiOutput);
        $aiOutput = preg_replace('/\s*```$/', '', $aiOutput);
        return trim($aiOutput);
    }

    public static function ai_product_image_full_path($image_name)
    {
        $path = dynamicStorage('storage/app/public/product/ai_product_image/' . $image_name);
        return $path;
    }
}
