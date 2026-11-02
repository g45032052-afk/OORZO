<?php

namespace App\Builder;

use App\Builder\Resources\ItemCardResource;
use App\Builder\Support\CardContext;
use App\CentralLogics\CategoryLogic;
use App\Models\Category;
use App\Models\Food;
use Illuminate\Database\Eloquent\Builder;
use Modules\Builder\Contracts\CategoryProvider as CategoryProviderContract;
use Modules\Builder\ValueObjects\Storefront\CategoryDTO;
use Modules\Builder\ValueObjects\StorefrontScope;

/**
 * StackFood is food-only / single-module, so the 6amMart multi-module axes
 * collapse: there is no `module_id` on categories, and items are `App\Models\Food`
 * scoped by `restaurant_id` (+ the storefront zone) rather than Store/module.
 */
class CategoryProvider implements CategoryProviderContract
{
    public function forScope(?StorefrontScope $scope): array
    {
        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'priority', 'image'])
            ->where('parent_id', 0)
            ->where('position', 0)
            ->where('status', 1)
            ->with(['childes' => function ($query) {
                $query->select(['id', 'name', 'slug', 'parent_id', 'priority'])
                    ->where('status', 1)
                    ->orderByDesc('priority')
                    ->orderBy('name');
            }])
            ->orderByDesc('priority')
            ->orderBy('name')
            ->get();

        // Every direct child (any status) of the top-level categories, mapped
        // childId => parentId. The product-by-category page counts items whose
        // category is the parent OR a direct child of it regardless of the
        // child's own status, so disabled children must feed the parent's
        // aggregate even though they're left out of the chip list below.
        $childParentMap = Category::query()
            ->whereIn('parent_id', $categories->pluck('id')->all())
            ->pluck('parent_id', 'id');

        $countableIds = $categories->pluck('id')
            ->merge($childParentMap->keys())
            ->unique()
            ->values()
            ->all();

        $directCounts = $this->scopedItemBaseQuery($scope)
            ->when($countableIds !== [], fn (Builder $query) => $query->whereIn('category_id', $countableIds))
            ->selectRaw('category_id, COUNT(*) as item_count')
            ->groupBy('category_id')
            ->pluck('item_count', 'category_id');

        // Map host rows into the canonical CategoryDTO (the module owns the
        // shape; the typed constructor validates), then emit toArray() for the
        // Inertia wire. Leaf children carry image=null, children=[].
        return $categories->map(function (Category $category) use ($directCounts, $childParentMap) {
            $children = $category->childes
                ->map(fn (Category $child) => new CategoryDTO(
                    id: (int) $child->id,
                    name: (string) $child->name,
                    slug: $child->slug,
                    image: null,
                    itemCount: (int) ($directCounts[$child->id] ?? 0),
                ))
                ->all();

            $childItemCount = $childParentMap
                ->filter(fn ($parentId) => (int) $parentId === (int) $category->id)
                ->keys()
                ->sum(fn ($childId) => (int) ($directCounts[$childId] ?? 0));

            return new CategoryDTO(
                id: (int) $category->id,
                name: (string) $category->name,
                slug: $category->slug,
                image: $category->image_full_url ?? null,
                itemCount: (int) ($directCounts[$category->id] ?? 0) + $childItemCount,
                children: $children,
            );
        })->map(fn (CategoryDTO $dto) => $dto->toArray())->values()->all();
    }

    public function normalizeActiveCategoryIds(array $categoryIds = [], mixed $categoryId = null): array
    {
        return collect($categoryIds)
            ->when($categoryId !== null && $categoryId !== '', fn ($collection) => $collection->push($categoryId))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function resolveActiveCategory(array $categories, array $activeCategoryIds): ?array
    {
        return collect($categories)
            ->map(function (array $category) {
                return [
                    ...$category,
                    'children' => $category['children'] ?? [],
                ];
            })
            ->flatMap(function (array $category) {
                return collect([$category])->merge($category['children']);
            })
            ->first(fn (array $category) => \in_array((int) $category['id'], $activeCategoryIds, true));
    }

    public function categoryProducts(
        ?StorefrontScope $scope,
        array $categoryIds = [],
        int $limit = 12,
        int $offset = 1,
        string $type = 'all',
    ): array {
        $zoneId = $scope?->regionId;
        $limit = \max(1, \min($limit, 100));
        $offset = \max(1, $offset);

        if (! $zoneId || empty($categoryIds)) {
            return [
                'total_size' => 0,
                'limit' => $limit,
                'offset' => $offset,
                'products' => [],
                'categories' => [],
            ];
        }

        // StackFood's CategoryLogic::products() takes a single category (and
        // also matches its direct children) plus the zone. It internally honours
        // the food sort/availability business settings.
        $data = CategoryLogic::products([
            'category_id' => $categoryIds[0],
            'zone_id' => [(int) $zoneId],
            'type' => $type,
            'veg' => 0,
            'non_veg' => 0,
            'limit' => $limit,
            'offset' => $offset,
            'longitude' => 0,
            'latitude' => 0,
            'sort_by' => null,
            'request' => null,
        ]);

        $data['products'] = ItemCardResource::fromCollection(
            $data['products'] ?? [],
            CardContext::default()
        );
        $data['categories'] = [];

        return $data;
    }

    private function scopedItemBaseQuery(?StorefrontScope $scope, string $type = 'all'): Builder
    {
        $zoneId = $scope?->regionId;
        $restaurantId = $scope?->subTenantId;

        return Food::query()
            ->when($zoneId, function (Builder $query) use ($zoneId) {
                $query->whereHas('restaurant', fn (Builder $restaurantQuery) => $restaurantQuery->where('zone_id', $zoneId));
            })
            // Single-restaurant storefronts scope every listing to their own
            // restaurant (see ItemProvider::baseQuery). The category card counts
            // must do the same, otherwise they include other restaurants' items
            // and read higher than what the product-by-category page lists.
            ->when($restaurantId, fn (Builder $query) => $query->where('restaurant_id', $restaurantId))
            ->active()
            ->type($type);
    }

    /**
     * Locale-translated category names keyed by id, unfiltered by product count.
     *
     * Category's `translate` global scope eager-loads current-locale translations
     * and the `name` accessor returns the translated value (falling back to the
     * base name), so reading ->name here yields the localized name directly.
     *
     * @param  int[] $ids
     * @return array<int, string>
     */
    public function translatedNamesByIds(array $ids): array
    {
        $ids = collect($ids)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return [];
        }

        return Category::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name'])
            ->mapWithKeys(fn (Category $category) => [(int) $category->id => (string) $category->name])
            ->all();
    }

    public function findIdBySlug(?StorefrontScope $scope, string $slug): ?int
    {
        $slug = \trim($slug);
        if ($slug === '') {
            return null;
        }

        $id = Category::query()
            ->where('slug', $slug)
            ->where('status', 1)
            ->value('id');

        return $id ? (int) $id : null;
    }

    public function search(?StorefrontScope $scope, ?string $query, int $limit = 30): array
    {
        $query = \trim((string) $query);
        $limit = \max(1, \min($limit, 100));

        return Category::with('translations')
            ->where('status', 1)
            ->where('parent_id', 0)
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', "%{$query}%")
                        ->orWhereHas('translations', fn ($t) => $t->where('value', 'like', "%{$query}%")
                        );
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'image'])
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug ?? null,
                'image' => $cat->image_full_url ?? null,
            ])
            ->values()
            ->all();
    }
}
