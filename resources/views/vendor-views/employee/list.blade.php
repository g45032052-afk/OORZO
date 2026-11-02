@extends('layouts.vendor.app')
@section('title',translate('Employee List'))
@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">


    <!-- Page Header -->
     <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h2 class="page-header-title text-capitalize">
                    <div class="card-header-icon d-inline-flex mr-2 img">
                        <img src="{{dynamicAsset('assets/admin/img/resturant-panel/page-title/employee-role.png')}}" alt="public">
                    </div>
                    <span>
                        {{translate('messages.Employee_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$em->total()}}</span>
                    </span>
                </h2>
            </div>
            {{--<div class="col-sm">
                <a href="{{route('vendor.employee.add-new')}}" class="btn btn--primary  float-right">
                    <i class="tio-add-circle"></i>
                    <span class="text">
                        {{translate('Add New Employee')}}
                    </span>
                </a>
            </div>--}}
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Card -->
    <div class="card">
        <div class="card-header flex-wrap gap-2">
            <form >
                <!-- Search -->
                <div class="input-group input--group rounded border">
                    <input id="datatableSearch_" type="search" name="search" class="form-control border-0" placeholder="{{ translate('Ex : Search by Employee Name, Email or Phone No') }}"  value="{{ request()?->search ?? null }}" aria-label="Search">
                    <button type="submit" class="btn btn--reset py-1 px-2">
                        <i class="tio-search"></i>
                    </button>
                </div>
                <!-- End Search -->
            </form>
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <a href="{{route('vendor.employee.add-new')}}" class="btn btn--primary py-2 h-40 px-3 d-flex align-items-center justify-content-center fs-12 float-right">
                    <i class="tio-add-circle mr-1"></i>
                    <span class="text">
                        {{translate('Add New Employee')}}
                    </span>
                </a>
                <!-- Export Button -->
                <div class="hs-unfold">
                    <a class="js-hs-unfold-invoker btn btn-sm btn--reset dropdown-toggle export-btn font--sm" href="javascript:;"
                        data-hs-unfold-options='{
                            "target": "#usersExportDropdown",
                            "type": "css-animation"
                        }'>
                        <i class="tio-download-from-cloud mr-1 fs-16"></i> {{translate('messages.export')}}
                    </a>

                    <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                        <span class="dropdown-header">{{translate('messages.download_options')}}</span>
                        <a id="export-excel" class="dropdown-item" href="{{route('vendor.employee.export-employee', ['type'=>'excel',request()->getQueryString()])}}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{dynamicAsset('assets/admin')}}/svg/components/excel.svg"
                                    alt="Image Description">
                            {{translate('messages.excel')}}
                        </a>
                        <a id="export-csv" class="dropdown-item" href="{{route('vendor.employee.export-employee', ['type'=>'csv',request()->getQueryString()])}}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{dynamicAsset('assets/admin')}}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                            {{translate('messages.csv')}}
                        </a>
                    </div>
                </div>
                <!-- Export Button -->
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive pt-0">
                <table id="datatable"
                        class="table table-hover table-borderless table-thead-borderedless table-nowrap table-align-middle card-table" data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true,
                            "paging":false
                        }'>
                    <thead class="global-bg-box">
                    <tr>
                        <th>{{ translate('messages.sl') }}</th>
                        <th>{{translate('messages.name')}}</th>
                        <th>{{translate('messages.email')}}</th>
                        <th>{{translate('messages.phone')}}</th>
                        <th>{{translate('messages.Role')}}</th>
                        <th class="w-100px text-center">{{translate('messages.action')}}</th>
                    </tr>
                    </thead>
                    <tbody id="set-rows">
                    @foreach($em as $k=>$e)
                        <tr>
                            <th class="align-middle" scope="row">{{$k+$em->firstItem()}}</th>
                            <td class="text-capitalize text-break text-hover-primary">
                                <div class="d-flex align-items-center gap-12">
                                    <div class="min-w-58 w-58 h-58 rounded overflow-hidden">
                                        <img src="{{ $e->image_full_url }}" alt="{{translate('public')}}">
                                    </div>
                                    <div class="content">
                                        <h4 class="text-title m-0 font-regular">{{$e['f_name']}} {{$e['l_name']}}</h4>
                                    </div>
                                </div>
                            </td>
                            <td >
                                {{$e['email']}}
                            </td>
                            <td>{{$e['phone']}}</td>
                            <td>{{$e->role?$e->role['name']:translate('messages.role_deleted')}}</td>
                            <td>
                                @if (auth('vendor_employee')->id()  != $e['id'])
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--primary btn-outline-primary offcanvas-trigger"
                                       data-target="#employee-offcanvas"
                                       href="javascript:"
                                       data-id="{{$e['id']}}"
                                       data-image="{{$e['image_full_url']}}"
                                       data-permitted_modules='{{$e?->role?->modules}}'
                                       data-name="{{ucwords($e['f_name'] . ' ' . $e['l_name'])}}"
                                       data-email="{{$e['email']}}"
                                       data-phone="{{$e['phone']}}"
                                       data-role="{{$e->role ? ucwords($e->role['name']) : translate('messages.role_deleted')}}"
                                       data-created-at="{{$e['created_at']}}"
                                       data-updated-at="{{$e['updated_at']}}">
                                        <i class="tio-invisible"></i>
                                    </a>
                                    <a class="btn action-btn btn--primary active-primary btn-outline-primary"
                                        href="{{route('vendor.employee.edit',[$e['id']])}}" title="{{translate('messages.edit_Employee')}}"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                        data-id="employee-{{$e['id']}}" data-message="{{translate('messages.Want_to_delete_this_role')}}" title="{{translate('messages.delete_Employee')}}"><i class="tio-delete-outlined"></i>
                                    </a>
                                    <form action="{{route('vendor.employee.delete',[$e['id']])}}"
                                            method="post" id="employee-{{$e['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($em) === 0)
                <div class="empty--data">
                    <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                    <h5>
                        {{translate('no_data_found')}}
                    </h5>
                </div>
                @endif
            </div>
        </div>
        <div class="card-footer border-0">
            <div class="page-area">
                <table>
                    <tfoot>
                    {!! $em->links() !!}
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <!-- Card -->
</div>

<!-- Employee Management Offcanvas -->
<div id="employee-offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between" style="--offcanvas-width: 500px">
    <div>
        <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
            <div class="px-3 py-3 d-flex justify-content-between w-100">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Employee  Details') }}</h2>

                </div>
                <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;
                </button>
            </div>
        </div>
        <div class="custom-offcanvas-body p-20">
            <div class="d-flex flex-column gap-20px">
                <div class="global-bg-box p-xxl-4 p-3 rounded">
                    <div class="d-flex align-items-center gap-12 mb-20">
                        <div class="min-w-58 w-58 h-58 rounded overflow-hidden">
                            <img src="{{dynamicAsset('assets/admin/img/160x160/img2.jpg')}}" alt="public">
                        </div>
                        <div class="content p-0">
                            <h4 class="text-title m-0 fw-500"></h4>
                            <h5 class="gray-dark mb-0 mt-1 font-regular"></h5>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex gap-2">
                            <span class="before-info w-130 min-w-90 gray-dark fs-14">
                                {{translate('Phone no')}}
                            </span>
                            <span class="fs-14 text-title"></span>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="before-info w-130 min-w-90 gray-dark fs-14">
                                {{translate('Email')}}
                            </span>
                            <span class="fs-14 text-title"></span>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="before-info w-130 min-w-90 gray-dark fs-14">
                                {{translate('Created Date')}}
                            </span>
                            <span class="fs-14 text-title d-flex align-items-center gap-2"> <span class="line-gray d-lg-block d-none"></span>
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="before-info w-130 min-w-90 gray-dark fs-14">
                                {{translate('Last Modified Date')}}
                            </span>
                            <span class="fs-14 text-title d-flex align-items-center gap-2">
                                 <span class="line-gray d-lg-block d-none"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="global-bg-box p-xxl-20 p--15 rounded">
                    <h5 class="text-title mb-10px fs-16 fw-500">{{translate('Permitted Modules')}}</h5>
                    <p class="text-title m-0 d-flex flex-wrap gap-10px" id="permitted_modules">

                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">

        <button type="button"
                class="btn custom-close-btn-color btn-close min-w-120 w-100 btn--reset d-center offcanvas-close"
                aria-label="Close">
            {{translate('Cancel')}}
        </button>
        <button type="submit" class="btn min-w-120 w-100 btn--primary edit-employee-btn">
            {{translate('Edit Details')}}
        </button>
    </div>
</div>
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>

@endsection

@push('script_2')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.offcanvas-trigger', function() {

                const employeeId = $(this).data('id');
                const employeeName = $(this).data('name');
                const employeeEmail = $(this).data('email');
                const employeePhone = $(this).data('phone');
                const employeeRole = $(this).data('role');
                const employeeImage = $(this).data('image');
                const createdAt = new Date($(this).data('created-at'));
                const updatedAt = new Date($(this).data('updated-at'));

                // ✅ Date format
                const dateOptions = { day: 'numeric', month: 'short', year: 'numeric' };
                const timeOptions = { hour: '2-digit', minute: '2-digit' };
                const createdDate = createdAt.toLocaleDateString('en-US', dateOptions);
                const createdTime = createdAt.toLocaleTimeString('en-US', timeOptions);
                const updatedDate = updatedAt.toLocaleDateString('en-US', dateOptions);
                const updatedTime = updatedAt.toLocaleTimeString('en-US', timeOptions);

                // ✅ Image
                const imageSrc = employeeImage && employeeImage !== ''
                    ? employeeImage
                    : "{{ dynamicAsset('assets/admin/img/160x160/img2.jpg') }}";
                $('#employee-offcanvas img').attr('src', imageSrc);

                // ✅ Basic Info
                $('#employee-offcanvas .content h4').text(employeeName);
                $('#employee-offcanvas .content h5').text(employeeRole);
                $('#employee-offcanvas .d-flex.gap-2 span.fs-14.text-title').eq(0).text(employeePhone);
                $('#employee-offcanvas .d-flex.gap-2 span.fs-14.text-title').eq(1).text(employeeEmail);
                $('#employee-offcanvas .d-flex.gap-2 span.fs-14.text-title').eq(2).html(
                    `${createdDate} <span class="line-gray d-lg-block d-none"></span> ${createdTime}`
                );
                $('#employee-offcanvas .d-flex.gap-2 span.fs-14.text-title').eq(3).html(
                    `${updatedDate} <span class="line-gray d-lg-block d-none"></span> ${updatedTime}`
                );

                let permittedRaw = $(this).attr('data-permitted_modules');
                let modulesList = '';
                try {
                    if (permittedRaw && permittedRaw.trim() !== '') {
                        const modules = JSON.parse(permittedRaw);
                        if (Array.isArray(modules) && modules.length > 0) {
                             modulesList = modules.map(m => {
                                                const formatted = m
                                                    .replace(/_/g, ' ')
                                                    .split(' ')
                                                    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                                                    .join(' ');

                                                return `<span class="fs-14 text-title bg-white py-2 px-10px rounded-pill me-1 mb-1 d-inline-block">
                                                            ${formatted}
                                                        </span>`;
                                            }).join('');
                        } else {
                            modulesList = `<span class="text-muted">{{ translate('No modules assigned') }}</span>`;
                        }
                    } else {
                        modulesList = `<span class="text-muted">{{ translate('No modules assigned') }}</span>`;
                    }
                } catch (err) {
                    console.error('permitted_modules parse error:', err, permittedRaw);
                    modulesList = `<span class="text-muted">{{ translate('Invalid data') }}</span>`;
                }

                $('#employee-offcanvas #permitted_modules').html(modulesList);

                // ✅ Show offcanvas
                $('#employee-offcanvas').addClass('active');
                $('#offcanvasOverlay').addClass('active');

                // ✅ Highlight current trigger
                $('.offcanvas-trigger').removeClass('active');
                $(this).addClass('active');
            });

            // ✅ Close actions
            $('.offcanvas-close, #offcanvasOverlay, .offcanvas-footer .btn--reset').on('click', function() {
                $('#employee-offcanvas').removeClass('active');
                $('#offcanvasOverlay').removeClass('active');
            });

            // ✅ Edit button
            $('.offcanvas-footer .btn--primary').on('click', function() {
                const employeeId = $('.offcanvas-trigger.active').data('id');
                if (employeeId) {
                    window.location.href = '{{ route("vendor.employee.edit", ":id") }}'.replace(':id', employeeId);
                }
            });

            $('#edit-employee-btn').on('click', function(e) {
                const employeeId = $('.offcanvas-trigger.active').data('id');
                if (employeeId) {
                    const url = '{{ route("vendor.employee.edit", ":id") }}'.replace(':id', employeeId);
                    // JS থেকে redirect
                    window.location.href = url;
                }
            });
        });
    </script>
@endpush
