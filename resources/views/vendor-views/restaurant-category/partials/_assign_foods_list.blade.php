@if(count($foods) === 0)
    <div class="text-center py-4 px-3">
        <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="empty" class="mb-2" width="80">
        <p class="mb-0 text-muted fs-12">{{ translate('No uncategorized foods to assign.') }}</p>
    </div>
@else
    <ul class="assign-foods-scroll">
        @foreach($foods as $food)
            <li class="assign-food-row {{ ((int) $food->restaurant_category_id === (int) $category->id) ? 'is-selected is-locked' : '' }}">
                <label class="d-flex align-items-center gap-3 px-3 py-2"
                    for="assign_food_{{ $food->id }}">
                    <div class="fs-13 text-title fw-medium" style="min-width: 18px;">
                        {{ $loop->iteration }}
                    </div>
                    <img class="rounded onerror-image"
                        style="width: 48px; height: 48px; object-fit: cover; flex-shrink: 0;"
                        src="{{ $food->image_full_url }}"
                        data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                        alt="{{ $food->name }}">
                    <div class="flex-grow-1 min-w-0">
                        <div class="text-muted fs-11 mb-0">{{ translate('ID') }} #{{ $food->id }}</div>
                        <h6 class="mb-0 fw-bold text-truncate fs-14">{{ Str::limit($food->name, 32, '...') }}</h6>
                        <div class="d-flex align-items-center gap-3 fs-12 text-muted mt-1">
                            @if(!is_null($food->price))
                                <span>{{ translate('Price') }} {{ \App\CentralLogics\Helpers::format_currency($food->price) }}</span>
                            @endif
                            <span>{{ translate('Variation') }} {{ $food->new_variations_count ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="form-check m-0">
                        <input class="form-check-input assign-food-checkbox"
                            type="checkbox"
                            name="food_ids[]"
                            value="{{ $food->id }}"
                            id="assign_food_{{ $food->id }}"
                            {{ ((int) $food->restaurant_category_id === (int) $category->id) ? 'checked disabled' : '' }}
                            title="{{ ((int) $food->restaurant_category_id === (int) $category->id) ? translate('Already added to this category') : '' }}">
                    </div>
                </label>
            </li>
        @endforeach
    </ul>
@endif
