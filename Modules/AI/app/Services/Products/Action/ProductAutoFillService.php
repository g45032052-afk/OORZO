<?php

namespace Modules\AI\app\Services\Products\Action;

use Modules\AI\app\Core\Constants\AIEngineNames;
use Modules\AI\app\Core\Contracts\AIEngineInterface;
use Modules\AI\app\Core\Factory\AIEngineFactory;
use Modules\AI\app\Services\Products\Prompts\ProductPrompts;
use Modules\AI\app\Services\Products\Resource\ProductResource;
use Modules\AI\app\Traits\ConversationTrait;

class ProductAutoFillService
{
   use ConversationTrait;
   protected AIEngineInterface $engine;
   protected ProductPrompts $productPrompts;

   public function __construct()
   {
      $this->engine = AIEngineFactory::create(AIEngineNames::getDefault());
      $this->productPrompts = new ProductPrompts();
   }


   public function titleAutoFill(string $name,  $langCode): string
   {

      $prompt = $this->productPrompts->titleAutoFill($name, $langCode);

      return  $this->engine->core($prompt);
   }
   public function descriptionAutoFill(string $name,  $langCode): string
   {

      $prompt = $this->productPrompts->descriptionAutoFill($name, $langCode);

      return $this->cleanAIHtmlOutput($this->engine->core($prompt));
   }
   public function generalSetupAutoFill(string $name,  string $description,$restaurant_id): string
   {

      $prompt = $this->productPrompts->generalSetupAutoFill($name, $description,$restaurant_id);

      return  $this->engine->core($prompt);
   }
   public function PriceOthersAutoFill(string $name, $description = null): string
   {

      $prompt = $this->productPrompts->PriceOthersAutoFill($name, $description);

      return  $this->engine->core($prompt);
   }
   public function seoSectionAutoFill(string $name, $description = null): string
   {

      $prompt = $this->productPrompts->seoSectionAutoFill($name, $description);

      return  $this->engine->core($prompt);
   }
   public function variationSetupAutoFill(string $name, string $description): string
   {

      $prompt = $this->productPrompts->variationSetupAutoFill($name, $description);

      return  $this->engine->core($prompt);
   }
   public function imageAnalysisAutoFill(string $imageUrl): string
   {

      $prompt = $this->productPrompts->imageAnalysisAutoFill();

      return  $this->engine->core($prompt, $imageUrl);
   }
   public function generateTitleSuggestions(array $keywords): string
   {
      $prompt = $this->productPrompts->generateTitleSuggestions($keywords);
      return  $this->engine->core($prompt);
   }
}
