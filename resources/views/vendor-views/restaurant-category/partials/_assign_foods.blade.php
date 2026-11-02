<style>
    .assign-foods-scroll { list-style: none; padding-left: 0; margin-bottom: 0; }
    .assign-foods-scroll .assign-food-row { border-radius: 10px; transition: background-color 0.15s ease; }
    .assign-foods-scroll .assign-food-row + .assign-food-row { margin-top: 4px; }
    .assign-foods-scroll .assign-food-row > label { cursor: pointer; margin: 0; width: 100%; }
    .assign-foods-scroll .assign-food-row:hover { background-color: #F5F7FA; }
    .assign-foods-scroll .assign-food-row.is-selected,
    .assign-foods-scroll .assign-food-row:has(.assign-food-checkbox:checked) {
        background-color: #E6F0FE;
    }
    /* Locked rows (already saved to this category) — user can't uncheck. */
    .assign-foods-scroll .assign-food-row.is-locked > label { cursor: not-allowed; }
    .assign-foods-scroll .assign-food-row.is-locked .assign-food-checkbox {
        opacity: 1; /* keep the primary tick visible — Bootstrap dims disabled */
        background-color: var(--bs-primary, #0d6efd);
        border-color: var(--bs-primary, #0d6efd);
    }
    #assignFoodsListContainer { min-height: 0; overflow-y: auto; }
</style>
<form id="assignFoodsForm"
      action="{{ route('vendor.restaurant-category.foods.assign', $category->id) }}"
      method="post"
      class="d-flex flex-column h-100"
      data-search-url="{{ route('vendor.restaurant-category.foods.search', $category->id) }}">
    @csrf

    <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
        <h3 class="mb-0">{{ translate('Select Foods For Category') }}</h3>
        <button type="button"
            class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
            aria-label="Close">&times;</button>
    </div>

    <div class="custom-offcanvas-body p-20 d-flex flex-column flex-grow-1"  style="min-height: 0;">
        <div class="alert alert-warning d-inline-flex align-items-start gap-2 text-left mx-auto py-2 px-3 mb-0"
            style="max-width: 640px; background-color: #FFF8E5; border: 1px solid #FFE6A8;" role="alert">
            <i class="tio-info text-warning mt-1" style="color: #F2A93B !important;"></i>
            <div class="fs-12 text-body">
                {{ translate('Once you create restaurant categories, you must add your foods to those categories. Without assigning foods, they will not appear on your restaurant details page. If you want to proceed with the main category, you can skip adding any restaurant categories.') }}
            </div>
        </div>

        @if($unassignedCount > 0)
            <div class="alert d-flex align-items-start gap-2 mb-0 mt-3 py-2 px-3" role="alert"
                style="background-color: #FDECEC; border: 1px solid #F8C5C5; border-radius: 8px;">
                <i class="tio-warning-outlined mt-1" style="color: #E53935;"></i>
                <div class="fs-12 text-body">
                    {{ translate('There are') }}
                    <strong>{{ $unassignedCount }} {{ translate('foods') }}</strong>
                    {{ translate('that are unassigned to any category. Please assign them to a category so they can be visible on the restaurant details page.') }}
                </div>
            </div>
        @endif

        <div class="form-group mt-3 mb-3">
            <input type="text" name="assign_search" class="form-control h--40px"
                placeholder="{{ translate('Search here') }}"
                autocomplete="off">
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <h6 class="mb-0">{{ translate('Food List') }}</h6>
            <span class="badge badge-soft-primary fs-12">
                <span id="assignFoodsSelectedCount">0</span> {{ translate('Selected') }}
            </span>
        </div>

        <div id="assignFoodsListContainer" class="flex-grow-1">
            @include('vendor-views.restaurant-category.partials._assign_foods_list', [
                'foods' => $foods,
                'category' => $category,
            ])
        </div>
    </div>

    <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3">
        <button type="button" class="btn w-100 btn--secondary h--40px reset-assign-btn">{{ translate('Reset') }}</button>
        <button type="submit" class="btn w-100 btn--primary h--40px">{{ translate('Save') }}</button>
    </div>
</form>
