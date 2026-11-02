@extends('layouts.admin.app')

@section('title',translate('BOGO_Offer'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">{{translate('messages.BOGO_Offer')}}</h1>
        </div>

        <div class="card">
            {{-- Per design, the list header only appears once there is data; the empty state
                 owns the whole card and carries its own call to action. A search that matches
                 nothing is not that case - the header has to stay or there is no way to clear
                 the term and get back to the full list. --}}
            @if($offers->total() === 0 && ! request()->filled('search'))
                <div class="d-flex flex-column align-items-center justify-content-center text-center px-3"
                     style="min-height: 440px">
                    <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="empty" style="width:64px;height:auto">
                    <h4 class="mt-4 mb-2 font-weight-bold">{{translate('messages.No_BOGO_Offers_Yet')}}</h4>
                    <p class="opacity-75 mb-4" style="max-width: 480px">
                        {{translate('messages.bogo_empty_state_description')}}
                    </p>
                    <a href="{{route('admin.bogo-offer.add-new')}}" class="btn btn--primary">
                        <i class="tio-add-circle"></i> {{translate('messages.Add_BOGO_Offer')}}
                    </a>
                </div>
            @else
                <div class="card-header border-0 py-2 search--button-wrapper">
                    <h5 class="card-title">
                        {{translate('messages.BOGO_Offer_List')}}
                        <span class="badge badge-soft-dark ml-2">{{$offers->total()}}</span>
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
                           data-hs-unfold-options='{"target": "#bogoExportDropdown", "type": "css-animation"}'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>
                        <div id="bogoExportDropdown" class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a target="__blank" id="export-excel" class="dropdown-item"
                               href="{{ route('admin.bogo-offer.export', ['type' => 'excel', 'search' => request('search')]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="excel">
                                {{ translate('messages.excel') }}
                            </a>
                            <a target="__blank" id="export-csv" class="dropdown-item"
                               href="{{ route('admin.bogo-offer.export', ['type' => 'csv', 'search' => request('search')]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg" alt="csv">
                                {{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>

                    <a href="{{route('admin.bogo-offer.add-new')}}" class="btn btn--primary">
                        <i class="tio-add-circle"></i> {{translate('messages.Add_BOGO_Offer')}}
                    </a>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ translate('messages.sl') }}</th>
                            <th>{{ translate('messages.Offer_Name') }}</th>
                            <th>{{ translate('messages.Food_Qty') }}</th>
                            <th>{{ translate('messages.Duration') }}</th>
                            <th>{{ translate('messages.Restaurant') }}</th>
                            <th>{{ translate('messages.status') }}</th>
                            <th class="text-center">{{ translate('messages.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($offers as $key => $offer)
                            <tr>
                                <td>{{$key + $offers->firstItem()}}</td>
                                <td>
                                    <a href="{{route('admin.bogo-offer.view',$offer->id)}}" class="d-block text-body">
                                        {{Str::limit($offer->title, 25, '...')}}
                                    </a>
                                </td>
                                <td>
                                    <span class="d-block">{{translate('messages.Buy_Food_Qty')}} : <strong>{{$offer->buy_qty}}</strong></span>
                                    <span class="d-block">{{translate('messages.Get_Food_Qty')}} : <strong>{{$offer->get_qty}}</strong></span>
                                </td>
                                <td>
                                    <span class="d-block">{{translate('messages.Start_Date')}} &nbsp;:&nbsp; {{$offer->start_date ? $offer->start_date->format('d M y, h:i a') : 'N/A'}}</span>
                                    <span class="d-block">{{translate('messages.End_Date')}} &nbsp;&nbsp;&nbsp;:&nbsp; {{$offer->end_date ? $offer->end_date->format('d M y, h:i a') : 'N/A'}}</span>
                                </td>
                                <td>
                                    <span data-toggle="tooltip" data-placement="top"
                                          title="{{ $offer->approved_count }} {{ translate('messages.approved') }}, {{ $offer->enrollments_count - $offer->approved_count }} {{ translate('messages.pending') }}">
                                        {{$offer->enrollments_count}}
                                    </span>
                                </td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm" for="bogoStatus{{$offer->id}}">
                                        <input type="checkbox" class="toggle-switch-input status_change_alert"
                                               data-url="{{route('admin.bogo-offer.status',[$offer->id, $offer->status ? 0 : 1])}}"
                                               data-message="{{ $offer->status
                                                    ? translate('messages.Want_to_turn_off_this_BOGO_offer')
                                                    : translate('messages.Want_to_turn_on_this_BOGO_offer') }}"
                                               id="bogoStatus{{$offer->id}}" {{$offer->status ? 'checked' : ''}}>
                                        <span class="toggle-switch-label"><span class="toggle-switch-indicator"></span></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                           href="{{route('admin.bogo-offer.view',$offer->id)}}" title="{{translate('messages.view')}}">
                                            <i class="tio-visible"></i>
                                        </a>
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                           href="{{route('admin.bogo-offer.edit',$offer->id)}}" title="{{translate('messages.edit')}}">
                                            <i class="tio-edit"></i>
                                        </a>
                                        {{-- Named restaurants, not a bare confirm: deleting an offer
                                             ten stores are running is not the same click as deleting
                                             an empty draft, and the row already knows the count. --}}
                                        <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                           data-id="bogo-offer-{{$offer->id}}"
                                           data-message="{{ $offer->enrollments_count > 0
                                                ? $offer->enrollments_count.' '.translate('messages.restaurants_have_joined_this_offer_Deleting_it_removes_the_offer_from_all_of_them')
                                                    .' '.translate('messages.Any_items_customers_added_to_their_cart_from_this_offer_will_be_removed_too')
                                                    .' '.translate('messages.Want_to_delete_this_BOGO_offer')
                                                : translate('messages.Want_to_delete_this_BOGO_offer') }}"
                                           title="{{translate('messages.delete')}}">
                                            <i class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="{{route('admin.bogo-offer.delete',$offer->id)}}" method="post" id="bogo-offer-{{$offer->id}}">
                                            @csrf @method('delete')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">{{ translate('no_data_found') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div>{!! $offers->links() !!}</div>
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
