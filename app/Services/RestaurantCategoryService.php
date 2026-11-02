<?php

namespace App\Services;

use App\CentralLogics\Helpers;
use App\Models\RestaurantCategory;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Builder;

class RestaurantCategoryService
{
    public function getCategoryQueryForRestaurant(int $restaurantId): Builder
    {
        return RestaurantCategory::query()->where('restaurant_id', $restaurantId);
    }

    public function getCategory(
        int|string $id,
        ?int $restaurantId = null,
        bool $withoutGlobalScope = false,
        array $with = []
    ): RestaurantCategory {
        $query = $withoutGlobalScope
            ? RestaurantCategory::withoutGlobalScope('translate')
            : RestaurantCategory::query();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query
            ->when($restaurantId, fn ($builder) => $builder->where('restaurant_id', $restaurantId))
            ->where('id', $id)
            ->firstOrFail();
    }

    public function create(array $data): RestaurantCategory
    {
        $restaurantCategory = new RestaurantCategory();
        $restaurantCategory->restaurant_id = $data['restaurant_id'];
        $restaurantCategory->name = $this->resolveDefaultName($data);
        $restaurantCategory->priority = $data['priority'] ?? 0;
        $restaurantCategory->image = !empty($data['image'])
            ? Helpers::upload(dir: 'category/', format: 'png', image: $data['image'])
            : 'def.png';
        $restaurantCategory->save();

        $this->syncTranslations($restaurantCategory, $data);

        return $restaurantCategory;
    }

    public function update(RestaurantCategory $restaurantCategory, array $data): RestaurantCategory
    {
        if (isset($data['restaurant_id'])) {
            $restaurantCategory->restaurant_id = $data['restaurant_id'];
        }

        $restaurantCategory->name = $this->resolveDefaultName($data, $restaurantCategory->getRawOriginal('name'));
        $restaurantCategory->priority = $data['priority'] ?? 0;

        if (($data['image_remove'] ?? '0') === '1' && $restaurantCategory->image) {
            Helpers::check_and_delete('category/', $restaurantCategory->image);
            $restaurantCategory->image = null;
        }

        if (!empty($data['image'])) {
            $restaurantCategory->image = Helpers::update(
                dir: 'category/',
                old_image: $restaurantCategory->image,
                format: 'png',
                image: $data['image']
            );
        }

        $restaurantCategory->save();
        $this->syncTranslations($restaurantCategory, $data);

        return $restaurantCategory;
    }

    public function updateStatus(RestaurantCategory $restaurantCategory, int|bool $status): RestaurantCategory
    {
        $restaurantCategory->status = $status;
        $restaurantCategory->save();

        return $restaurantCategory;
    }

    public function updatePriority(RestaurantCategory $restaurantCategory, int $priority): RestaurantCategory
    {
        $restaurantCategory->priority = $priority;
        $restaurantCategory->save();

        return $restaurantCategory;
    }

    public function delete(RestaurantCategory $restaurantCategory): void
    {
        if ($restaurantCategory->image) {
            Helpers::check_and_delete('category/', $restaurantCategory->image);
        }

        $restaurantCategory->translations()->delete();
        $restaurantCategory->storage()->delete();
        $restaurantCategory->delete();
    }

    private function resolveDefaultName(array $data, ?string $fallback = null): string
    {
        if (!empty($data['name']) && is_array($data['name']) && !empty($data['lang']) && is_array($data['lang'])) {
            $defaultIndex = array_search('default', $data['lang']);

            if ($defaultIndex !== false && isset($data['name'][$defaultIndex])) {
                return $data['name'][$defaultIndex];
            }

            return $data['name'][0] ?? $fallback ?? '';
        }

        if (!empty($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $translation) {
                if (($translation['key'] ?? null) === 'name' && in_array($translation['locale'] ?? '', ['en', 'default'])) {
                    return $translation['value'] ?? $fallback ?? '';
                }
            }

            foreach ($data['translations'] as $translation) {
                if (($translation['key'] ?? null) === 'name' && !empty($translation['value'])) {
                    return $translation['value'];
                }
            }
        }

        return $data['name'] ?? $fallback ?? '';
    }

    private function syncTranslations(RestaurantCategory $restaurantCategory, array $data): void
    {
        if (!empty($data['lang']) && is_array($data['lang']) && !empty($data['name']) && is_array($data['name'])) {
            $request = request();
            $request->merge([
                'lang' => $data['lang'],
                'name' => $data['name'],
            ]);

            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'name',
                name_field: 'name',
                model_name: 'RestaurantCategory',
                data_id: $restaurantCategory->id,
                data_value: $restaurantCategory->name
            );

            return;
        }

        if (!empty($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $translation) {
                if (($translation['key'] ?? null) !== 'name') {
                    continue;
                }

                Translation::updateOrInsert(
                    [
                        'translationable_type' => RestaurantCategory::class,
                        'translationable_id' => $restaurantCategory->id,
                        'locale' => $translation['locale'],
                        'key' => $translation['key'],
                    ],
                    [
                        'value' => $translation['value'],
                    ]
                );
            }
        }
    }
}
