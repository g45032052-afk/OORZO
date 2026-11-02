<form method="GET" action="{{ url()->current() }}">
    <div class="row g-3">
        @if (request()->routeIs('vendor.report.food-wise-report'))
        <div class="col-sm-6 col-md-3">
            <select name="category_id" id="category_id"
                class="js-select2-custom form-control" id="category_id">
                <option value="all">{{ translate('messages.All Categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category['id'] }}"
                        {{ isset($category_id) && $category_id == $category['id'] ? 'selected' : '' }}>
                        {{ $category['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-6 col-md-3">
            @php($type = request()->type)
            <!-- Veg/NonVeg filter -->
            <select name="type"
                data-placeholder="{{translate('messages.select_type')}}" class="form-control js-select2-custom">
                <option value="all" {{$type == 'all' ? 'selected' : ''}}>{{translate('messages.all_types')}}</option>
                @if ($toggle_veg_non_veg)
                    <option value="veg" {{$type == 'veg' ? 'selected' : ''}}>{{translate('messages.veg')}}</option>
                    <option value="non_veg" {{$type == 'non_veg' ? 'selected' : ''}}>{{translate('messages.non_veg')}}</option>
                @endif
            </select>
        </div>
        @endif

        @if (request()->routeIs('vendor.report.campaign_order-report'))
            <div class="col-sm-6 col-md-3">
                <select name="campaign_id" class="form-control js-select2-custom"
                    data-filter="campaign_id" id="campaign_id">
                    <option value="all">{{ translate('messages.All_Campaignes') }}</option>
                    @foreach (\App\Models\ItemCampaign::where('restaurant_id', \App\CentralLogics\Helpers::get_restaurant_id())->orderBy('title')->get(['id', 'title']) as $z)
                        <option value="{{ $z['id'] }}" {{ isset($campaign_id) && $campaign_id == $z['id'] ? 'selected' : '' }}>
                            {{ $z['title'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-sm-6 col-md-3">
            <select class="form-control" name="filter" id="filter_select">
                <option value="all_time" {{ isset($filter) && $filter == 'all_time' ? 'selected' : '' }}>
                    {{ translate('messages.All_Time') }}
                </option>
                <option value="this_year" {{ isset($filter) && $filter == 'this_year' ? 'selected' : '' }}>
                    {{ translate('messages.This_Year') }}
                </option>
                <option value="previous_year" {{ isset($filter) && $filter == 'previous_year' ? 'selected' : '' }}>
                    {{ translate('messages.Previous_Year') }}
                </option>
                <option value="this_month" {{ isset($filter) && $filter == 'this_month' ? 'selected' : '' }}>
                    {{ translate('messages.This_Month') }}
                </option>
                <option value="this_week" {{ isset($filter) && $filter == 'this_week' ? 'selected' : '' }}>
                    {{ translate('messages.This_Week') }}
                </option>
                <option value="custom" {{ isset($filter) && $filter == 'custom' ? 'selected' : '' }}>
                    {{ translate('messages.Custom') }}
                </option>
            </select>
        </div>


        <div class="col-sm-6 col-md-3 custom_date d-none">
            <input type="date" name="from" id="from_date" class="form-control"
                placeholder="{{ translate('Start_Date') }}" value={{ $from ? $from : '' }} required>
        </div>

        <div class="col-sm-6 col-md-3 custom_date d-none">
            <input type="date" name="to" id="to_date" class="form-control" placeholder="{{ translate('End_Date') }}"
                value={{ $to ? $to : '' }} required>
        </div>


        <div class="col-sm-6 col-md-3 ml-auto">
            <button type="submit" class="btn btn-primary btn-block">
                {{ translate('Filter') }}
            </button>
        </div>

    </div>
</form>

@push('script_2')

    <script>
        $(document).ready(function () {

            function toggleCustomDate(value) {
                if (value === 'custom') {
                    $('.custom_date').removeClass('d-none');
                } else {
                    $('.custom_date').addClass('d-none');
                }
            }

            $('#filter_select').on('change', function () {
                toggleCustomDate($(this).val());
            });

            toggleCustomDate($('#filter_select').val());
        });
    </script>
@endpush