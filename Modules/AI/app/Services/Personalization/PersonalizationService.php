<?php

namespace Modules\AI\app\Services\Personalization;

use App\Models\Category;
use App\Models\Food;
use Modules\AI\app\Models\CustomerPreference;
use Modules\AI\app\Models\CustomerPreferenceSummary;
use Modules\AI\app\Jobs\ComputeUserPreferencesJob;
use Modules\AI\app\Core\Engines\OpenAIEngine;
use Illuminate\Support\Facades\DB;

/**
 * Customer personalization engine (ported from 6amMart, adapted to StackFood).
 *
 * StackFood is single-module (food): all `module_id` plumbing is removed,
 * `Item`→`Food` (table `food`, column `restaurant_id`), `Store`→`Restaurant`
 * (table `restaurants`). The public "store" naming is kept on the API surface
 * (recordStoreAction / applyStorePersonalization / top_stores) but always maps
 * to restaurants underneath. AI keyword generation uses StackFood's OpenAIEngine.
 */
class PersonalizationService
{
    const WEIGHTS = [
        'order'          => 10,
        'review'         => 8,
        'cart'           => 7,
        'item_wishlist'  => 5,
        'store_wishlist' => 5,
        'item_search'    => 4,
        'item_view'      => 2,
        'store_view'     => 2,
    ];

    const REBUILD_THRESHOLD = 5;

    /**
     * Add a raw ORDER BY clause as the PRIMARY (first) sort key.
     *
     * The apply* methods run at the end of a call-site's query building, after
     * the listing's own orderBy()/latest()/popular() calls are already queued,
     * so a plain orderByRaw() would only act as a least-significant tiebreaker.
     * We append the clause, then hoist it to the front of the builder's order
     * list so the customer's preferences outrank the default ordering
     * ("For You" — favorites first, default ordering breaks ties).
     */
    private static function primaryOrderByRaw($query, string $sql)
    {
        $query = $query->orderByRaw($sql);

        $base = method_exists($query, 'getQuery') ? $query->getQuery() : $query;
        $bucket = !empty($base->unions) ? 'unionOrders' : 'orders';

        if (!empty($base->{$bucket})) {
            $clause = array_pop($base->{$bucket});
            array_unshift($base->{$bucket}, $clause);
        }

        return $query;
    }

    /**
     * Record an item-based action (view, wishlist, cart, order).
     */
    public static function recordItemAction(int $userId, int $itemId, string $signal): void
    {
        $item = DB::table('food')
            ->where('id', $itemId)
            ->select('category_id', 'restaurant_id')
            ->first();

        if (!$item) return;

        $weight = self::WEIGHTS[$signal] ?? 0;
        if ($weight <= 0) return;

        self::upsertScore($userId, 'item', $itemId, $weight);

        if ($item->category_id) {
            self::upsertScore($userId, 'category', $item->category_id, $weight);
        }

        if ($item->restaurant_id) {
            self::upsertScore($userId, 'store', $item->restaurant_id, $weight);
        }

        self::markSummaryDirty($userId);
    }

    /**
     * Record a store-based (restaurant) action (view, wishlist).
     */
    public static function recordStoreAction(int $userId, int $storeId, string $signal): void
    {
        $store = DB::table('restaurants')->where('id', $storeId)->first();
        if (!$store) return;

        $weight = self::WEIGHTS[$signal] ?? 0;
        if ($weight <= 0) return;

        self::upsertScore($userId, 'store', $storeId, $weight);
        self::markSummaryDirty($userId);
    }

    /**
     * Record a search action — maps keyword to categories.
     */
    public static function recordSearchAction(int $userId, string $keyword): void
    {
        if (!$keyword) return;

        $weight = self::WEIGHTS['item_search'];

        $categories = Category::where('name', 'LIKE', "%{$keyword}%")
            ->select('id')
            ->limit(5)
            ->get();

        $dirty = false;
        foreach ($categories as $cat) {
            self::upsertScore($userId, 'category', $cat->id, $weight);
            $dirty = true;
        }

        if ($dirty) {
            self::markSummaryDirty($userId);
        }
    }

    /**
     * Increment score for a single preference row.
     */
    private static function upsertScore(int $userId, string $type, int $referenceId, float $weight): void
    {
        DB::table('customer_preferences')->updateOrInsert(
            [
                'user_id' => $userId,
                'preference_type' => $type,
                'reference_id' => $referenceId,
            ],
            [
                'score' => DB::raw("COALESCE(score, 0) + {$weight}"),
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );
    }

    /**
     * Mark the user's summary as dirty. Dispatch rebuild job if threshold hit.
     */
    public static function markSummaryDirty(int $userId): void
    {
        $summary = CustomerPreferenceSummary::firstOrCreate(
            ['user_id' => $userId],
            ['update_count' => 0]
        );

        $summary->increment('update_count');

        if ($summary->update_count >= self::REBUILD_THRESHOLD) {
            ComputeUserPreferencesJob::dispatch($userId);
        }
    }

    /**
     * Rebuild the summary table for a user.
     */
    public static function rebuildSummary(int $userId): void
    {
        $topItems = CustomerPreference::where('user_id', $userId)
            ->where('preference_type', 'item')
            ->orderByDesc('score')
            ->limit(20)
            ->pluck('reference_id')
            ->toArray();

        $topCategories = CustomerPreference::where('user_id', $userId)
            ->where('preference_type', 'category')
            ->orderByDesc('score')
            ->limit(20)
            ->pluck('reference_id')
            ->toArray();

        $topStores = CustomerPreference::where('user_id', $userId)
            ->where('preference_type', 'store')
            ->orderByDesc('score')
            ->limit(20)
            ->pluck('reference_id')
            ->toArray();

        // AI keywords + persona need a live OpenAI call. Skip them when the
        // provider is off (openai_config.status != 1) — the behavioural
        // top items/categories/stores computed above still apply, so
        // personalization keeps working without the AI enrichment.
        if (\Modules\AI\app\Core\AiModule::isOpenAiConfigured()) {
            $aiKeywords = self::getAiKeywords($userId, $topItems, $topCategories, $topStores);
        } else {
            $aiKeywords = [];
        }

        // Resolve keywords to actual IDs (heavy queries run here in job, not at API time)
        $resolvedIds = self::resolveKeywordsToIds($aiKeywords, $topItems, $topCategories, $topStores);

        CustomerPreferenceSummary::updateOrCreate(
            ['user_id' => $userId],
            [
                'top_items' => $topItems,
                'top_categories' => $topCategories,
                'top_stores' => $topStores,
                'ai_keywords' => $aiKeywords,
                'keyword_item_ids' => $resolvedIds['items'],
                'keyword_category_ids' => $resolvedIds['categories'],
                'keyword_store_ids' => $resolvedIds['stores'],
                'update_count' => 0,
                'last_rebuilt_at' => now(),
            ]
        );
    }

    /**
     * Resolve AI keywords to actual food/category/restaurant IDs.
     * Runs ONCE in the queued job — heavy LIKE queries happen here, not at API time.
     */
    private static function resolveKeywordsToIds(array $keywords, array $excludeItemIds, array $excludeCategoryIds, array $excludeStoreIds): array
    {
        $itemIds = [];
        $categoryIds = [];
        $storeIds = [];

        if (empty($keywords)) {
            return ['items' => [], 'categories' => [], 'stores' => []];
        }

        foreach ($keywords as $keyword) {
            if (!is_string($keyword) || empty(trim($keyword))) continue;
            $kw = trim($keyword);

            // Match food by: name, tags, translations
            $matchedItems = Food::where('status', 1)
                ->where(function ($q) use ($kw) {
                    $q->where('name', 'LIKE', "%{$kw}%")
                      ->orWhereHas('tags', fn($t) => $t->where('tag', 'LIKE', "%{$kw}%"))
                      ->orWhereHas('translations', fn($t) => $t->where('key', 'name')->where('value', 'LIKE', "%{$kw}%"));
                })
                ->whereNotIn('id', $excludeItemIds)
                ->whereNotIn('id', $itemIds)
                ->limit(5)
                ->pluck('id')
                ->toArray();

            $itemIds = array_merge($itemIds, $matchedItems);

            // Match categories by: name, translations
            $matchedCategories = Category::where('status', 1)
                ->where(function ($q) use ($kw) {
                    $q->where('name', 'LIKE', "%{$kw}%")
                      ->orWhereHas('translations', fn($t) => $t->where('key', 'name')->where('value', 'LIKE', "%{$kw}%"));
                })
                ->whereNotIn('id', $excludeCategoryIds)
                ->whereNotIn('id', $categoryIds)
                ->limit(3)
                ->pluck('id')
                ->toArray();

            $categoryIds = array_merge($categoryIds, $matchedCategories);

            // Match restaurants by: name, address, translations
            $matchedStores = DB::table('restaurants')
                ->where('status', 1)
                ->where(function ($q) use ($kw) {
                    $q->where('name', 'LIKE', "%{$kw}%")
                      ->orWhere('address', 'LIKE', "%{$kw}%")
                      ->orWhereExists(function ($sub) use ($kw) {
                          $sub->select(DB::raw(1))
                              ->from('translations')
                              ->whereColumn('translations.translationable_id', 'restaurants.id')
                              ->where('translations.translationable_type', 'App\\Models\\Restaurant')
                              ->where('translations.key', 'name')
                              ->where('translations.value', 'LIKE', "%{$kw}%");
                      });
                })
                ->whereNotIn('id', $excludeStoreIds)
                ->whereNotIn('id', $storeIds)
                ->limit(3)
                ->pluck('id')
                ->toArray();

            $storeIds = array_merge($storeIds, $matchedStores);
        }

        return [
            'items' => array_slice(array_unique($itemIds), 0, 30),
            'categories' => array_slice(array_unique($categoryIds), 0, 15),
            'stores' => array_slice(array_unique($storeIds), 0, 15),
        ];
    }

    /**
     * Use OpenAI to analyze full user behavior and suggest search keywords.
     */
    public static function getAiKeywords(int $userId, array $topItemIds, array $topCategoryIds, array $topStoreIds): array
    {
        if (empty($topItemIds) && empty($topCategoryIds)) return [];

        try {
            $user = DB::table('users')->where('id', $userId)->select('interest', 'user_context')->first();
            if (!$user) return [];

            $itemContext = '';
            if (!empty($topItemIds)) {
                $items = Food::whereIn('id', array_slice($topItemIds, 0, 15))
                    ->with(['category:id,name', 'tags:id,tag'])
                    ->select('id', 'name', 'price', 'category_id', 'avg_rating', 'veg', 'is_halal')
                    ->get();

                if ($items->isNotEmpty()) {
                    $itemLines = $items->map(function ($item) {
                        $cat = $item->category?->name ?? 'Unknown';
                        $tags = $item->tags->pluck('tag')->implode(', ');
                        $rating = $item->avg_rating ? round($item->avg_rating, 1) : 'N/A';
                        $flags = collect([
                            $item->veg ? 'Veg' : null,
                            $item->is_halal ? 'Halal' : null,
                        ])->filter()->implode(', ');

                        $line = "- {$item->name} | Price: {$item->price} | Category: {$cat} | Rating: {$rating}/5";
                        if ($tags) $line .= " | Tags: {$tags}";
                        if ($flags) $line .= " | Flags: {$flags}";
                        return $line;
                    })->implode("\n");

                    $prices = $items->pluck('price')->filter();
                    $avgPrice = $prices->isNotEmpty() ? round($prices->avg(), 2) : 0;
                    $minPrice = $prices->min() ?? 0;
                    $maxPrice = $prices->max() ?? 0;

                    $itemContext = "CUSTOMER'S TOP PRODUCTS (by interaction score):\n{$itemLines}\nPrice range: {$minPrice} - {$maxPrice} (Avg: {$avgPrice})";
                }
            }

            $categoryContext = '';
            if (!empty($topCategoryIds)) {
                $categoryNames = Category::whereIn('id', array_slice($topCategoryIds, 0, 10))
                    ->pluck('name')->toArray();
                if (!empty($categoryNames)) {
                    $categoryContext = "\nTOP PREFERRED CATEGORIES (ranked): " . implode(', ', $categoryNames);
                }
            }

            $storeContext = '';
            if (!empty($topStoreIds)) {
                $stores = DB::table('restaurants')
                    ->whereIn('id', array_slice($topStoreIds, 0, 10))
                    ->select('name', 'rating')
                    ->get();
                if ($stores->isNotEmpty()) {
                    $storeLines = $stores->map(function ($s) {
                        $r = $s->rating ? json_decode($s->rating, true) : null;
                        $avgR = (is_array($r) && count($r)) ? round(array_sum($r) / max(count($r), 1), 1) : 'N/A';
                        return "{$s->name} (Rating: {$avgR}/5)";
                    })->implode(', ');
                    $storeContext = "\nTOP PREFERRED RESTAURANTS: {$storeLines}";
                }
            }

            $interestContext = '';
            if ($user->interest) {
                $interestIds = json_decode($user->interest, true);
                if (!empty($interestIds) && is_array($interestIds)) {
                    $interestNames = Category::whereIn('id', $interestIds)->pluck('name')->toArray();
                    if (!empty($interestNames)) {
                        $interestContext = "\nCUSTOMER'S SELF-SELECTED INTERESTS: " . implode(', ', $interestNames);
                    }
                }
            }

            $personaContext = '';
            if ($user->user_context) {
                $personaContext = "\nPREVIOUS PERSONA ASSESSMENT: {$user->user_context}";
            }

            $scoreContext = '';
            $scoreBreakdown = CustomerPreference::where('user_id', $userId)
                ->orderByDesc('score')
                ->limit(10)
                ->select('preference_type', 'reference_id', 'score')
                ->get();
            if ($scoreBreakdown->isNotEmpty()) {
                $lines = $scoreBreakdown->map(fn($p) => "{$p->preference_type}#{$p->reference_id}: {$p->score} pts");
                $scoreContext = "\nPREFERENCE SCORES (higher = stronger signal): " . $lines->implode(', ');
            }

            // StackFood is a single food-delivery platform — fixed domain rules.
            $moduleInstructions = "MODULE: Food Delivery
DOMAIN RULES:
- Pair by meal logic: if main dish → suggest sides, drinks, desserts, sauces
- Respect cuisine patterns: if user orders Italian → suggest within Italian + adjacent cuisines (Mediterranean, Spanish)
- Time-aware pairing: breakfast items → coffee, juice; dinner items → appetizers
- Dietary consistency: veg user → ONLY suggest veg; halal user → ONLY suggest halal
- Store type: restaurant names, cuisine-specific eateries, cloud kitchens";

            $prompt = "You are a personalization engine for a food-delivery platform. Your output directly controls what foods, restaurants, and categories a customer sees first. Accuracy matters — bad keywords waste the customer's attention.

CUSTOMER DATA:
{$itemContext}
{$categoryContext}
{$storeContext}
{$interestContext}
{$personaContext}
{$scoreContext}

{$moduleInstructions}

TASK 1 — PERSONA (2-3 sentences, be specific not generic):
Analyze the data and describe THIS customer. Include:
- Spending tier: budget (lowest prices) / value (mid-range) / premium (highest prices) — based on ACTUAL price data above
- Core preferences: what specific types of food they consistently choose
- Behavioral pattern: loyal to few restaurants or exploring many? bulk buyer or frequent small orders?
- Any dietary/lifestyle signals from product flags and tags
Do NOT write generic descriptions. Use the actual product names and categories from the data.

TASK 2 — KEYWORDS (these will be searched against our food database):

product_keywords (12-15): Single words or 2-word phrases that would appear in FOOD NAMES the customer would want next.
Think: what would this customer type into a search bar? What complementary foods pair with what they already buy?
GOOD: \"biryani\", \"garlic naan\", \"mango lassi\", \"spring roll\"
BAD: \"healthy food\", \"good stuff\", \"recommended\" (too vague, won't match real food names)

store_keywords (5-8): Words that would appear in RESTAURANT NAMES this customer would like.
GOOD: \"grill\", \"bakery\", \"pizza\", \"kitchen\", \"biryani house\"
BAD: \"good restaurant\", \"nice place\" (too vague)

category_keywords (5-8): Words that would appear in CATEGORY NAMES adjacent to current preferences.
GOOD: \"desserts\", \"beverages\", \"breakfast\", \"fast food\", \"snacks\"
BAD: \"food items\", \"products\" (too generic)

CRITICAL RULES:
- Keywords must match real food/restaurant/category names in a database — be practical, not aspirational
- DO NOT repeat foods, restaurants, or categories the customer already has
- 1-2 words per keyword, lowercase
- Respect dietary constraints absolutely (veg/halal user = only matching keywords)
- Match the customer's price tier

Return ONLY this JSON, nothing else:
{\"persona\": \"...\", \"product_keywords\": [...], \"store_keywords\": [...], \"category_keywords\": [...]}";

            $engine = new OpenAIEngine();
            $response = $engine->core($prompt);

            $response = preg_replace('/```json\s*/', '', $response);
            $response = preg_replace('/```\s*/', '', $response);
            $response = trim($response);

            $parsed = json_decode($response, true);
            if (!is_array($parsed)) return [];

            if (!empty($parsed['persona']) && is_string($parsed['persona'])) {
                DB::table('users')->where('id', $userId)->update([
                    'user_context' => $parsed['persona'],
                ]);
            }

            $allKeywords = [];
            foreach (['product_keywords', 'store_keywords', 'category_keywords'] as $type) {
                $kws = $parsed[$type] ?? [];
                if (is_array($kws)) {
                    foreach ($kws as $kw) {
                        if (is_string($kw) && !empty(trim($kw))) {
                            $allKeywords[] = trim($kw);
                        }
                    }
                }
            }

            return array_slice(array_unique($allKeywords), 0, 25);

        } catch (\Throwable $e) {
            info('Personalization AI keyword generation failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Apply personalization to food listing queries.
     */
    public static function applyItemPersonalization($query, ?int $userId, $filter = null)
    {
        if (!$userId) return $query;

        $summary = CustomerPreferenceSummary::where('user_id', $userId)->first();
        if (!$summary) return $query;

        $itemIds = $summary->top_items ?? [];
        $categoryIds = $summary->top_categories ?? [];
        $storeIds = $summary->top_stores ?? [];
        $kwItemIds = $summary->keyword_item_ids ?? [];
        $kwCategoryIds = $summary->keyword_category_ids ?? [];
        $kwStoreIds = $summary->keyword_store_ids ?? [];

        $allItemIds = array_unique(array_merge($itemIds, $kwItemIds));
        $allCategoryIds = array_unique(array_merge($categoryIds, $kwCategoryIds));
        $allStoreIds = array_unique(array_merge($storeIds, $kwStoreIds));

        if (empty($allItemIds) && empty($allCategoryIds) && empty($allStoreIds)) {
            return $query;
        }

        $scoreParts = [];

        // Food match: direct preference 50pts (ranked), keyword-resolved 20pts.
        if (!empty($allItemIds)) {
            $cases = [];
            foreach (array_slice($itemIds, 0, 20) as $i => $id) {
                $score = 50 - ($i * 2);
                $cases[] = "WHEN food.id = " . intval($id) . " THEN {$score}";
            }
            foreach (array_slice($kwItemIds, 0, 30) as $id) {
                if (!in_array($id, $itemIds)) {
                    $cases[] = "WHEN food.id = " . intval($id) . " THEN 20";
                }
            }
            $scoreParts[] = "(CASE " . implode(' ', $cases) . " ELSE 0 END)";
        }

        // Category match: direct 30pts (ranked), keyword-resolved 15pts.
        if (!empty($allCategoryIds)) {
            $cases = [];
            foreach (array_slice($categoryIds, 0, 20) as $i => $id) {
                $score = 30 - ($i * 1);
                $cases[] = "WHEN food.category_id = " . intval($id) . " THEN {$score}";
            }
            foreach (array_slice($kwCategoryIds, 0, 15) as $id) {
                if (!in_array($id, $categoryIds)) {
                    $cases[] = "WHEN food.category_id = " . intval($id) . " THEN 15";
                }
            }
            $scoreParts[] = "(CASE " . implode(' ', $cases) . " ELSE 0 END)";
        }

        // Restaurant match: direct 15pts (ranked), keyword-resolved 8pts.
        if (!empty($allStoreIds)) {
            $cases = [];
            foreach (array_slice($storeIds, 0, 20) as $i => $id) {
                $score = 15 - ($i * 0.5);
                $cases[] = "WHEN food.restaurant_id = " . intval($id) . " THEN {$score}";
            }
            foreach (array_slice($kwStoreIds, 0, 15) as $id) {
                if (!in_array($id, $storeIds)) {
                    $cases[] = "WHEN food.restaurant_id = " . intval($id) . " THEN 8";
                }
            }
            $scoreParts[] = "(CASE " . implode(' ', $cases) . " ELSE 0 END)";
        }

        $scoreExpr = implode(' + ', $scoreParts);

        return self::primaryOrderByRaw($query, "({$scoreExpr}) DESC");
    }

    /**
     * Apply personalization to restaurant listing queries.
     */
    public static function applyStorePersonalization($query, ?int $userId, $filter = null)
    {
        if (!$userId) return $query;

        $storeIds = [];
        $kwStoreIds = [];

        $summary = CustomerPreferenceSummary::where('user_id', $userId)->first();
        if ($summary) {
            $storeIds = $summary->top_stores ?? [];
            $kwStoreIds = $summary->keyword_store_ids ?? [];
        }

        // Fallback: no aggregated summary yet → read raw preferences so the
        // user's first restaurant-view / wishlist / order influences ranking.
        if (empty($storeIds) && empty($kwStoreIds)) {
            $storeIds = CustomerPreference::where('user_id', $userId)
                ->where('preference_type', 'store')
                ->orderByDesc('score')
                ->limit(20)
                ->pluck('reference_id')
                ->toArray();
        }

        $allStoreIds = array_unique(array_merge($storeIds, $kwStoreIds));
        if (empty($allStoreIds)) return $query;

        $cases = [];
        foreach (array_slice($storeIds, 0, 20) as $i => $id) {
            $score = 30 - ($i * 1);
            $cases[] = "WHEN restaurants.id = " . intval($id) . " THEN {$score}";
        }
        foreach (array_slice($kwStoreIds, 0, 15) as $id) {
            if (!in_array($id, $storeIds)) {
                $cases[] = "WHEN restaurants.id = " . intval($id) . " THEN 12";
            }
        }
        if (empty($cases)) return $query;

        $scoreExpr = "(CASE " . implode(' ', $cases) . " ELSE 0 END)";

        return self::primaryOrderByRaw($query, "{$scoreExpr} DESC");
    }

    /**
     * Apply personalization to category listing queries.
     *
     * Prefers the pre-aggregated summary; falls back to raw preferences so a
     * user's first search/view already lifts the relevant category. Preferred
     * sub-categories are resolved up to their top-level parent (the listing
     * endpoint shows parents only).
     */
    public static function applyCategoryPersonalization($query, ?int $userId)
    {
        if (!$userId) return $query;

        $categoryIds = [];
        $kwCategoryIds = [];

        $summary = CustomerPreferenceSummary::where('user_id', $userId)->first();
        if ($summary) {
            $categoryIds   = $summary->top_categories ?? [];
            $kwCategoryIds = $summary->keyword_category_ids ?? [];
        }

        if (empty($categoryIds) && empty($kwCategoryIds)) {
            $categoryIds = CustomerPreference::where('user_id', $userId)
                ->where('preference_type', 'category')
                ->orderByDesc('score')
                ->limit(20)
                ->pluck('reference_id')
                ->toArray();
        }

        $allCategoryIds = array_unique(array_merge($categoryIds, $kwCategoryIds));
        if (empty($allCategoryIds)) return $query;

        $idsToResolve = array_unique(array_merge(
            array_slice($categoryIds, 0, 20),
            array_slice($kwCategoryIds, 0, 15)
        ));

        $idToParent = Category::whereIn('id', $idsToResolve)
            ->pluck('parent_id', 'id')
            ->toArray();

        $rankedParents = [];
        foreach (array_slice($categoryIds, 0, 20) as $id) {
            $parentId = !empty($idToParent[$id]) ? (int) $idToParent[$id] : (int) $id;
            if (!in_array($parentId, $rankedParents, true)) {
                $rankedParents[] = $parentId;
            }
        }
        foreach (array_slice($kwCategoryIds, 0, 15) as $id) {
            $parentId = !empty($idToParent[$id]) ? (int) $idToParent[$id] : (int) $id;
            if (!in_array($parentId, $rankedParents, true)) {
                $rankedParents[] = $parentId;
            }
        }

        $cases = [];
        foreach ($rankedParents as $i => $parentId) {
            $score = 30 - $i;
            if ($score < 1) break;
            $cases[] = "WHEN categories.id = " . intval($parentId) . " THEN {$score}";
        }
        if (empty($cases)) return $query;

        $scoreExpr = "(CASE " . implode(' ', $cases) . " ELSE 0 END)";

        return self::primaryOrderByRaw($query, "{$scoreExpr} DESC");
    }

    /**
     * Apply personalization to food-campaign queries.
     */
    public static function applyCampaignPersonalization($query, ?int $userId)
    {
        if (!$userId) return $query;

        $summary = CustomerPreferenceSummary::where('user_id', $userId)->first();
        if (!$summary) return $query;

        $categoryIds = $summary->top_categories ?? [];
        $kwCategoryIds = $summary->keyword_category_ids ?? [];
        $allCategoryIds = array_unique(array_merge($categoryIds, $kwCategoryIds));

        if (empty($allCategoryIds)) return $query;

        $cases = [];
        foreach (array_slice($categoryIds, 0, 20) as $i => $id) {
            $score = 30 - ($i * 1);
            $cases[] = "WHEN item_campaigns.category_id = " . intval($id) . " THEN {$score}";
        }
        foreach (array_slice($kwCategoryIds, 0, 15) as $id) {
            if (!in_array($id, $categoryIds)) {
                $cases[] = "WHEN item_campaigns.category_id = " . intval($id) . " THEN 15";
            }
        }
        if (empty($cases)) return $query;

        $scoreExpr = "(CASE " . implode(' ', $cases) . " ELSE 0 END)";

        return self::primaryOrderByRaw($query, "{$scoreExpr} DESC");
    }

    /**
     * Reorder a collection by user preferences (post-query).
     */
    public static function reorderByPreference($collection, ?int $userId, string $matchField, string $preferenceType)
    {
        if (!$userId || $collection->isEmpty()) return $collection;

        $summary = CustomerPreferenceSummary::where('user_id', $userId)->first();
        if (!$summary) return $collection;

        $preferredIds = match($preferenceType) {
            'store' => $summary->top_stores ?? [],
            'item' => $summary->top_items ?? [],
            'category' => $summary->top_categories ?? [],
            default => [],
        };

        if (empty($preferredIds)) return $collection;

        $preferredFlipped = array_flip($preferredIds);

        return $collection->sortBy(function ($item) use ($matchField, $preferredFlipped) {
            $id = data_get($item, $matchField);
            return isset($preferredFlipped[$id]) ? $preferredFlipped[$id] : 9999;
        })->values();
    }
}
