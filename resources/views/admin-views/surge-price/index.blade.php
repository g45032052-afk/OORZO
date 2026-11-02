@extends('layouts.admin.app')

@section('title', translate('messages.Surge_Price_List'))

@push('css_or_js')
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/admin-shared.css') }}?v={{ @filemtime(public_path('assets/admin/css/admin-shared.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/surge-price.css') }}?v={{ @filemtime(public_path('assets/admin/css/surge-price.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.Surge_Price') }}</h1>
                </div>
            </div>
        </div>

        {{-- The empty state replaces the whole table, but only when the zone has
             genuinely never had a surge — a search that matches nothing still
             shows the table so the search box stays reachable. --}}
        @if ($surges->total() === 0 && !request()->has('search'))
            <div class="card">
                <div class="card-body empty-state">
                    <img class="empty-state__icon" src="{{ dynamicAsset('assets/admin/img/price-emty.png') }}"
                        alt="{{ translate('messages.Surge_Price') }}">
                    <h5 class="empty-state__title">
                        {{ translate("messages.Currently_you_don't_have_any_Surge_Price") }}</h5>
                    <p class="empty-state__text">
                        {{ translate('messages.To_enable_surge_pricing,_you_must_create_at_least_one_Surge_Price._In_this_page_you_see_all_the_surge_price_you_added.') }}
                    </p>
                    <a href="{{ route('admin.surge-price.create') }}" class="btn btn--primary">
                        <i class="tio-add-circle-outlined"></i> {{ translate('messages.Create_Surge_Price') }}
                    </a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            {{ translate('messages.Surge_Price_List') }}
                            <span class="badge badge-soft-dark ml-2">{{ $surges->total() }}</span>
                        </h5>

                        <form class="search-form">
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                    placeholder="{{ translate('messages.Search_by_Title') }}"
                                    value="{{ request()->search }}" aria-label="{{ translate('messages.search') }}"
                                    required>
                                <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            </div>
                        </form>

                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle" href="javascript:;"
                                data-hs-unfold-options='{"target": "#surgeExportDropdown","type": "css-animation"}'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>
                            <div id="surgeExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a id="export-excel" class="dropdown-item"
                                    href="{{ route('admin.surge-price.export', ['type' => 'excel', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a id="export-csv" class="dropdown-item"
                                    href="{{ route('admin.surge-price.export', ['type' => 'csv', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="">
                                    {{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('admin.surge-price.create') }}" class="btn btn--primary">
                            <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New') }}
                        </a>
                    </div>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ translate('messages.SL') }}</th>
                                <th class="border-0">{{ translate('messages.Title') }}</th>
                                <th class="border-0">{{ translate('messages.Zone') }}</th>
                                <th class="border-0">
                                    <div class="min-w-135px">{{ translate('messages.Price_Increase_Rate') }}</div>
                                </th>
                                <th class="border-0">
                                    <div class="min-w-135px">{{ translate('messages.Surge_Price_Schedule') }}</div>
                                </th>
                                <th class="border-0">{{ translate('messages.Duration') }}</th>
                                <th class="border-0">{{ translate('messages.Status') }}</th>
                                <th class="border-0 text-center">{{ translate('messages.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($surges as $key => $surge)
                                <tr>
                                    <td class="pl-4">{{ $key + $surges->firstItem() }}</td>
                                    <td>
                                        <span class="line--limit-2 max-w-220px"
                                            title="{{ $surge->surge_price_name }}">{{ $surge->surge_price_name }}</span>
                                    </td>
                                    <td>
                                        <span class="line--limit-2 max-w-220px"
                                            title="{{ $surge->zone?->name }}">{{ $surge->zone?->name ?? translate('messages.N/A') }}</span>
                                    </td>
                                    <td>
                                        {{ number_format($surge->price, 2) }}{{ $surge->price_type === 'percent' ? '%' : ' ' . \App\CentralLogics\Helpers::currency_symbol() }}
                                    </td>
                                    <td class="text-capitalize">{{ translate($surge->duration_type) }}</td>
                                    <td>
                                        <span class="d-block max-w-220px min-w-176px">
                                            @if (\App\CentralLogics\Helpers::surgeTimeSummary($surge))
                                                <span
                                                    class="d-block text-title">{{ \App\CentralLogics\Helpers::surgeTimeSummary($surge) }}</span>
                                            @endif
                                            <span
                                                class="text-wrap fs-12">{{ \App\CentralLogics\Helpers::surgeDateSummary($surge) }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="status-{{ $surge->id }}">
                                            <input type="checkbox" class="toggle-switch-input dynamic-checkbox"
                                                id="status-{{ $surge->id }}" {{ $surge->status ? 'checked' : '' }}
                                                data-id="status-{{ $surge->id }}" data-type="status"
                                                data-image-on="{{ dynamicAsset('assets/admin/img') }}/status-on-off.png"
                                                data-image-off="{{ dynamicAsset('assets/admin/img') }}/status-off.png"
                                                data-title-on="{{ translate('messages.Turn_On_The_Status?') }}"
                                                data-title-off="{{ translate('messages.Turn_Off_The_Status?') }}"
                                                data-text-on="<p>{{ translate('messages.Are_you_sure,_do_you_want_to_turn_on_the_Surge_Price_status_from_your_system.') }}</p>"
                                                data-text-off="<p>{{ translate('messages.Are_you_sure,_do_you_want_to_turn_off_the_Surge_Price_status_from_your_system.') }}</p>">
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <form
                                            action="{{ route('admin.surge-price.status', [$surge->id, $surge->status ? 0 : 1]) }}"
                                            method="get" id="status-{{ $surge->id }}_form"></form>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary"
                                                href="{{ route('admin.surge-price.edit', [$surge->id]) }}"
                                                title="{{ translate('messages.edit_surge_price') }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger surge-delete-btn"
                                                href="javascript:" data-id="surge-{{ $surge->id }}"
                                                title="{{ translate('messages.delete_surge_price') }}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{ route('admin.surge-price.delete', [$surge->id]) }}"
                                                method="post" id="surge-{{ $surge->id }}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (count($surges) !== 0)
                    <hr>
                    <div class="page-area">
                        {!! $surges->withQueryString()->links() !!}
                    </div>
                @else
                    <div class="empty--data">
                        <img src="{{ dynamicAsset('assets/admin/svg/illustrations/sorry.svg') }}" alt="">
                        <h5>{{ translate('messages.no_data_found') }}</h5>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- The shared .form-alert helper renders a SweetAlert, which does not match
         the design, so deletion gets its own confirm modal here. --}}
    <div class="modal fade" id="surge-delete-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div class="text-center">
                            <img class="mb-20" src="{{ dynamicAsset('assets/admin/img/modal/delete-icon.png') }}" alt="">
                            <h5 class="modal-title mb-3">{{ translate('messages.Want_to_Delete_this_surge_Price?') }}</h5>
                        </div>
                        <div class="text-center">
                            <p>{{ translate('messages.Are_you_sure_you_want_to_delete_this_surge_Price_&_remove_it_permanently?') }}
                            </p>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" class="btn btn--reset min-w-120px"
                                data-dismiss="modal">{{ translate('messages.No') }}</button>
                            <button type="button" id="surge-delete-confirm"
                                class="btn btn--danger min-w-120px">{{ translate('messages.Delete') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        $(document).ready(function () {
            let pendingDeleteFormId = null;

            $('.surge-delete-btn').on('click', function () {
                pendingDeleteFormId = $(this).data('id');
                $('#surge-delete-modal').modal('show');
            });

            $('#surge-delete-confirm').on('click', function () {
                if (pendingDeleteFormId) {
                    $('#' + pendingDeleteFormId).submit();
                }
            });
        });
    </script>
@endpush
