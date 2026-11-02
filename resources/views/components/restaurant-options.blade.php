@props([
    'restaurants' => [],
    'selected' => null,
    'placeholder' => null,
    'includeAll' => false,
    'allLabel' => null,
    'allValue' => 'all',
])

{{--
    Centralized restaurant <option> list. Stamps data-verified on every option so
    the global select2 renderer (verified-select2.js) can show the verified badge.
    Usage:
        <select class="js-select2-custom">
            <x-restaurant-options :restaurants="$restaurants" :selected="$id" />
        </select>
--}}

@if (!is_null($placeholder))
    <option value="" disabled {{ in_array((string) $selected, ['', null], true) ? 'selected' : '' }}>{{ $placeholder }}</option>
@endif

@if ($includeAll)
    <option value="{{ $allValue }}" {{ (string) $selected === (string) $allValue ? 'selected' : '' }}>
        {{ $allLabel ?? translate('messages.all_restaurants') }}
    </option>
@endif

@foreach ($restaurants as $restaurant)
    <option value="{{ $restaurant->id }}" data-verified="{{ (int) $restaurant->verified_seller }}"
        {{ (string) $selected === (string) $restaurant->id ? 'selected' : '' }}>
        {{ $restaurant->name }}
    </option>
@endforeach
