@extends('layouts.admin.app')

@section('title',translate('messages.Shift_setup'))

@push('css_or_js')

@endpush

@section('content')

<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title"><i class="tio-calendar"></i> {{translate('messages.Shift_setup')}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$shifts->total()}}</span></h1>
            </div>

            <div class="col-sm-auto">
                <a class="btn btn--primary offcanvas-trigger"  data-target="#offcanvas__customBtn">
                    <i class="tio-add"></i> {{translate('messages.Add_Shift')}}
                </a>
            </div>
        </div>
    </div>
    <!-- End Page Header -->
    <div class="row gx-2 gx-lg-3">
        <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
            <!-- Card -->
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title"></h5>
                                <form>
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                value="{{ request()?->search ?? null }}" placeholder="{{ translate('Ex: Search here') }}"
                                aria-label="Search" required>
                            <button type="submit" class="btn btn--secondary">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                        <!-- End Search -->
                    </form>
                    </div>
                </div>
                <!-- Table -->
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable"
                            class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                            data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,
                                "paging":false
                            }'>
                        <thead class="thead-light">
                        <tr>
                            <th>{{translate('messages.sl')}}</th>
                            <th >{{translate('messages.name')}} </th>
                            <th >{{translate('messages.Start_time')}}</th>
                            <th >{{translate('messages.End_time') }}</th>
                            <th >{{translate('messages.status')}}</th>
                            <th class="text-center">{{translate('messages.action')}}</th>
                        </tr>
                        </thead>

                        <tbody id="set-rows">
                            @include('admin-views.shift.partials._table',['shifts' => $shifts])
                        </tbody>
                    </table>
                    @if(count($shifts) === 0)
                    <div class="empty--data">
                        <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                    </div>
                    @endif
                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div>
                                {!! $shifts->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Table -->
            </div>
            <!-- End Card -->
        </div>
    </div>
</div>

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
    <div id="offcanvas__customBtn3" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100">
        </div>
    </div>


    <div id="offcanvas__customBtn" class="custom-offcanvas d-flex flex-column justify-content-between">
        <form action="{{ route('admin.shift.store') }}" method="post" class="d-flex flex-column h-100">
            @csrf
            @method('post')
            <div>
                <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
                    <h3 class="mb-0">{{ translate('Add Shift') }}</h2>
                        <button type="button"
                            class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                            aria-label="Close">&times;</button>
                </div>
                <div class="custom-offcanvas-body p-20">
                    <div class="bg--secondary rounded p-20 mb-20">

                        @if ($language)
                            <ul class="nav nav-tabs mb-4 border-0">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active" href="#"
                                        id="default-link">{{ translate('messages.default') }}</a>
                                </li>
                                @foreach ($language as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link" href="#"
                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="row">
                            <div class="col-12">
                                @if ($language)
                                    <div class="form-group lang_form" id="default-form">
                                        <label class="input-label" for="exampleFormControlInput1">{{ translate('name') }}
                                            ({{ translate('messages.default') }})
                                            <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('messages.Required.') }}"> *
                                            </span>

                                        </label>
                                        <input   type="text" class="form-control" name="name[]"
                                            value="{{ old('name.0') }}" maxlength="150"
                                            placeholder="{{ translate('Ex:Enter_shift') }}">

                                        <span class="text-right text-counting color-A7A7A7 d-block mt-1">0/150</span>
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">
                                    @foreach ($language as $key => $lang)


                                        <div class="form-group d-none lang_form" id="{{ $lang }}-form">
                                            <label class="input-label" for="exampleFormControlInput1">{{ translate('name') }}
                                                ({{ strtoupper($lang) }})
                                            </label>

                                            <input id="reason{{ $lang }}" type="text" class="form-control"
                                                value="{{  old('name.' . $key + 1)  }}" name="name[]" maxlength="150"
                                                placeholder="{{ translate('Ex:Enter_shift') }}">
                                            <span class="text-right text-counting color-A7A7A7 d-block mt-1">0/150</span>
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    @endforeach

                                @endif



                                <div class="form-group">
                                    <label for="start_time" class="mb-2">{{ translate('messages.Start_Time') }}</label>
                                    <input type="time" required name="start_time" value="{{ old('start_time') }}"
                                        class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="end_time" class="mb-2">{{ translate('End_Time') }}</label>
                                    <input type="time" required name="end_time" value="{{ old('end_time') }}"
                                        class="form-control">
                                </div>


                            </div>

                        </div>

                    </div>

                </div>
            </div>
            <div
                class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
                <button type="button"
                    class="btn w-100 btn--secondary offcanvas-close h--40px">{{ translate('Cancel') }}</button>
                <button type="submit" class="btn w-100 btn--primary h--40px">{{ translate('Update') }}</button>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
  <script src="{{dynamicAsset('assets/admin/js/view-pages/offcanvas-edit.js')}}"></script>
<script>
    $('.status_change_alert').on('click', function (event) {
        let url = $(this).data('url');
        let message = $(this).data('message');
        status_change_alert(url, message, event)
    })
        function status_change_alert(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '{{ translate('Are_you_sure?') }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('no') }}',
                confirmButtonText: '{{ translate('yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href=url;
                }
            })
        }

</script>

@endpush

