@php($selectedId = (string) ($selectedId ?? ''))

@if($categories->count() > 0)
    <button type="button"
        class="badge border-0 badge-pill mr-2 mb-2 px-3 py-2 js-restaurant-category-badge {{ $selectedId === '' ? 'badge-soft-primary' : 'badge-soft-secondary' }}"
        data-id="">
        {{ translate('No Restaurant Category') }}
    </button>

    @foreach($categories as $restaurantCategory)
        <button type="button"
            class="badge border-0 badge-pill mr-2 mb-2 px-3 py-2 js-restaurant-category-badge {{ $selectedId === (string) $restaurantCategory->id ? 'badge-soft-primary' : 'badge-soft-secondary' }}"
            data-id="{{ $restaurantCategory->id }}">
            {{ $restaurantCategory->name }}
        </button>
    @endforeach
@else
    <span class="text-muted">{{ translate('No Restaurant Categories Found') }}</span>
@endif
