<option value="">{{ translate('Select_Restaurant_Category') }}</option>
@foreach($categories as $restaurantCategory)
    <option value="{{ $restaurantCategory->id }}" {{ (string) $selectedId === (string) $restaurantCategory->id ? 'selected' : '' }}>
        {{ $restaurantCategory->name }}
    </option>
@endforeach
