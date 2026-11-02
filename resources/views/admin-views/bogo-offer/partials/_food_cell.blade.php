@php
    // Everything here comes from the frozen snapshot, so an edited or deleted food cannot
    // change what this enrolment shows.
    $variationLabels = collect($item->variations ?? [])
        ->flatMap(fn ($group) => data_get($group, 'values.label', []))
        ->filter()
        ->all();

    $addOnLabels = collect($item->add_on_ids ?? [])
        ->map(fn ($id) => $addOnNames[$id] ?? null)
        ->filter()
        ->all();

    $subLine = implode(' | ', array_filter([
        implode(', ', $addOnLabels),
        implode(', ', $variationLabels),
    ]));
@endphp

<div class="d-flex align-items-center gap-2">
    <img class="avatar avatar-sm rounded onerror-image"
         data-onerror-image="{{dynamicAsset('assets/admin/img/100x100/food-default-image.png')}}"
         src="{{ \App\CentralLogics\Helpers::get_full_url('product', $item->food_image, 'public', 'product') }}" alt="food">
    <div>
        <span class="d-block text-body">{{ Str::limit($item->food_name, 20, '...') }}</span>
        @if($subLine)
            <span class="d-block font-size-sm opacity-75">{{ $subLine }}</span>
        @else
            <span class="d-block font-size-sm opacity-75">{{ translate('messages.qty') }} : {{ $item->quantity }}</span>
        @endif
    </div>
</div>
