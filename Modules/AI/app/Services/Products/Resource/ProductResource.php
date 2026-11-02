<?php

namespace Modules\AI\app\Services\Products\Resource;

use App\Models\AddOn;
use App\Models\Allergy;
use App\Models\Category;
use App\Models\Nutrition;

class ProductResource
{

    private $productType = ["veg", "nonveg"];
    protected Category $category;
    protected Nutrition $nutrition;
    protected Allergy $allergy;
    protected AddOn $addon;


    public function __construct()
    {
        $this->category = new Category();
        $this->nutrition = new Nutrition();
        $this->allergy = new Allergy();
        $this->addon = new AddOn();
    }

    private function getCategoryEntitiyData($position = 0)
    {
        return $this->category
            ->where(['position' => $position, 'status' => 1])
            ->get(['id', 'name'])
            ->mapWithKeys(fn($item) => [strtolower($item->name) => $item->id])
            ->toArray();
    }
    private function getSubCategoryEntitiyData()
    {
        return $this->category
            ->where(['position' => 1, 'status' => 1])
            ->select(['id', 'name', 'parent_id'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'parent_id' => $item->parent_id,
                ];
            })
            ->toArray();
    }
    private function getAddonEntitiyData($restaurantId)
    {
        return $restaurantId ? $this->addon
            ->where(['restaurant_id' => $restaurantId, 'status' => 1])
            ->get(['id', 'name'])
            ->mapWithKeys(fn($item) => [strtolower($item->name) => $item->id])
            ->toArray() : [];
    }

    private function getNuttitionEntitiyData()
    {
        return $this->nutrition
            ->get(['id', 'nutrition'])
            ->mapWithKeys(fn($item) => [strtolower($item->nutrition) => $item->id])
            ->toArray();
    }

    private function getAllergyEntitiyData()
    {
        return $this->allergy
            ->get(['id', 'allergy'])
            ->mapWithKeys(fn($item) => [strtolower($item->allergy) => $item->id])
            ->toArray();
    }



    public function productGeneralSetupData($restaurantId): array
    {
        $data = [
            'categories' => $this->getCategoryEntitiyData(0),
            'sub_categories' => $this->getCategoryEntitiyData(1),
            'nutrition' => $this->getNuttitionEntitiyData(),
            'allergy' => $this->getAllergyEntitiyData(),
            'addon' => $this->getAddonEntitiyData($restaurantId),
            'rawSubCategories' => $this->getSubCategoryEntitiyData(),
            'product_types' => $this->productType,

        ];
        return $data;
    }
}
