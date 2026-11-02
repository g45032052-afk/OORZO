@extends('layouts.admin.app')

@section('title',translate('Happy_Hour'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">{{translate('messages.Happy_Hour')}}</h1>
        </div>

        <div class="card">
            {{-- Per design, the list header only appears once there is data; the empty state
                 owns the whole card and carries its own call to action. A search that matches
                 nothing is not that case - the header has to stay or there is no way to clear
                 the term and get back to the full list. --}}
            @if($happyHours->total() === 0 && ! request()->filled('search'))
                <div class="d-flex flex-column align-items-center justify-content-center text-center px-3"
                     style="min-height: 440px">
                    <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="empty" style="width:64px;height:auto">
                    <h4 class="mt-4 mb-2 font-weight-bold">{{translate('messages.No_Happy_Hours_Yet')}}</h4>
                    <p class="opacity-75 mb-4" style="max-width: 480px">
                        {{translate('messages.happy_hour_empty_state_description')}}
                    </p>
                    <a href="{{route('admin.happy-hour.add-new')}}" class="btn btn--primary">
                        <i class="tio-add-circle"></i> {{translate('messages.Add_Happy_Hours')}}
                    </a>
                </div>
            @else
                <div class="card-header border-0 py-2 search--button-wrapper">
                    <h5 class="card-title">
                        {{translate('messages.Happy_Hour_List')}}
                        <span class="badge badge-soft-dark ml-2">{{$happyHours->total()}}</span>
                    </h5>
                    <form id="search-form" action="{{ url()->current() }}" method="GET">
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch_" type="search" name="search" value="{{ request('search') }}"
                                   class="form-control" placeholder="{{translate('messages.search_here')}}" aria-label="Search">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                        </div>
                    </form>
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm"
                           href="javascript:;"
                           data-hs-unfold-options='{"target": "#happyHourExportDropdown", "type": "css-animation"}'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>
                        <div id="happyHourExportDropdown" class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a target="__blank" id="export-excel" class="dropdown-item"
                               href="{{ route('admin.happy-hour.export', ['type' => 'excel', 'search' => request('search')]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="excel">
                                {{ translate('messages.excel') }}
                            </a>
                            <a target="__blank" id="export-csv" class="dropdown-item"
                               href="{{ route('admin.happy-hour.export', ['type' => 'csv', 'search' => request('search')]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg" alt="csv">
                                {{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>

                    <a href="{{route('admin.happy-hour.add-new')}}" class="btn btn--primary">
                        <i class="tio-add-circle"></i> {{translate('messages.Create_Happy_hour')}}
                    </a>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ translate('messages.sl') }}</th>
                            <th>{{ translate('messages.title') }}</th>
                            <th>{{ translate('messages.Discount') }}</th>
                            <th>{{ translate('messages.Duration') }}</th>
                            <th>{{ translate('messages.Min_Order_Amount') }}</th>
                            <th>{{ translate('messages.Offer_Schedule') }}</th>
                            <th>{{ translate('messages.Zone') }}</th>
                            <th>{{ translate('messages.status') }}</th>
                            <th class="text-center">{{ translate('messages.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($happyHours as $key => $happyHour)
                            @php($running = $happyHour->isRunningNow())
                            <tr>
                                <td>{{$key + $happyHours->firstItem()}}</td>
                                <td>
                                    <a href="{{route('admin.happy-hour.view',$happyHour->id)}}" class="d-block text-body">
                                        {{Str::limit($happyHour->title, 25, '...')}}
                                    </a>
                                    @if($running)
                                        <span class="badge badge-soft-success">{{translate('messages.Active')}}</span>
                                    @endif
                                </td>
                                <td>{{$happyHour->discount}}%</td>
                                <td>
                                    @if($happyHour->duration_type === 'custom')
                                        <span class="d-block">{{ count($happyHour->custom_days ?? []) }} {{translate('messages.days_selected')}}</span>
                                    @else
                                        <span class="d-block">
                                            {{ $happyHour->start_time ? \App\CentralLogics\Helpers::time_format($happyHour->start_time) : '' }}
                                            - {{ $happyHour->end_time ? \App\CentralLogics\Helpers::time_format($happyHour->end_time) : '' }}
                                        </span>
                                        <span class="d-block font-size-sm opacity-75">
                                            @if($happyHour->is_permanent)
                                                {{translate('messages.Permanent')}}
                                            @else
                                                {{ $happyHour->start_date ? $happyHour->start_date->format('d M Y') : '' }}
                                                - {{ $happyHour->end_date ? $happyHour->end_date->format('d M Y') : '' }}
                                            @endif
                                        </span>
                                        @if($happyHour->duration_type === 'weekly')
                                            <span class="d-block font-size-sm opacity-75">
                                                {{ implode(', ', array_map(fn($d) => substr($d, 0, 3), $happyHour->weekly_days ?? [])) }}
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $happyHour->min_order_amount ? \App\CentralLogics\Helpers::format_currency($happyHour->min_order_amount) : 'N/A' }}</td>
                                <td class="text-capitalize">{{ translate('messages.'.$happyHour->duration_type) }}</td>
                                <td>{{ $happyHour->zone ? $happyHour->zone->name : translate('messages.zone_deleted') }}</td>
                                <td>
                                    {{-- A live window cannot be switched off - see the same guard on the
                                         controller. The switch is left showing its state, disabled, and says
                                         why it will not move, rather than disappearing from the row.
                                         The tooltip sits on the label rather than the input, because a
                                         disabled control fires no events for a tooltip to open on. --}}
                                    @php($locked = $running && $happyHour->status)
                                    <label class="toggle-switch toggle-switch-sm mb-0 {{ $locked ? 'cursor-default' : '' }}"
                                           for="hhStatus{{$happyHour->id}}"
                                           @if($locked) data-toggle="tooltip" data-placement="left"
                                                title="{{translate('messages.an_ongoing_happy_hour_cannot_be_turned_off')}}" @endif>
                                        <input type="checkbox" class="toggle-switch-input {{ $locked ? '' : 'status_change_alert' }}"
                                               @if(! $locked)
                                               data-url="{{route('admin.happy-hour.status',[$happyHour->id, $happyHour->status ? 0 : 1])}}"
                                               data-message="{{ $happyHour->status
                                                    ? translate('messages.Want_to_turn_off_this_happy_hour')
                                                    : translate('messages.Want_to_turn_on_this_happy_hour') }}"
                                               @else disabled @endif
                                               id="hhStatus{{$happyHour->id}}" {{$happyHour->status ? 'checked' : ''}}>
                                        <span class="toggle-switch-label"><span class="toggle-switch-indicator"></span></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                           href="{{route('admin.happy-hour.view',$happyHour->id)}}" title="{{translate('messages.view')}}">
                                            <i class="tio-visible"></i>
                                        </a>

                                        {{-- An ongoing window has carts priced against it, so editing is blocked while it runs. --}}
                                        @if($running)
                                            <span class="btn btn-sm btn--primary btn-outline-primary action-btn disabled"
                                                  data-toggle="tooltip" title="{{translate('messages.an_ongoing_happy_hour_cannot_be_edited')}}"
                                                  style="opacity:.45;cursor:not-allowed">
                                                <i class="tio-edit"></i>
                                            </span>
                                        @else
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                               href="{{route('admin.happy-hour.edit',$happyHour->id)}}" title="{{translate('messages.edit')}}">
                                                <i class="tio-edit"></i>
                                            </a>
                                        @endif

                                        {{-- Deleting a live window would take its enrolments with it and drop every
                                             store in it back to full price mid session, so it is blocked while it runs -
                                             the same rule the edit action above follows, and the controller enforces it too. --}}
                                        @if($running)
                                            <span class="btn btn-sm btn--danger btn-outline-danger action-btn disabled"
                                                  data-toggle="tooltip" title="{{translate('messages.an_ongoing_happy_hour_cannot_be_deleted')}}"
                                                  style="opacity:.45;cursor:not-allowed">
                                                <i class="tio-delete-outlined"></i>
                                            </span>
                                        @else
                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                               data-id="happy-hour-{{$happyHour->id}}"
                                               data-message="{{translate('messages.Want_to_delete_this_happy_hour')}}"
                                               title="{{translate('messages.delete')}}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.happy-hour.delete',$happyHour->id)}}" method="post" id="happy-hour-{{$happyHour->id}}">
                                                @csrf @method('delete')
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">{{ translate('no_data_found') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div>{!! $happyHours->links() !!}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('script_2')
    @include('admin-views.partials._status_change_alert')
@endpush
