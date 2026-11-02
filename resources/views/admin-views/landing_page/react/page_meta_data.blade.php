@php use App\CentralLogics\Helpers; @endphp
@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))

@section('content')

    <div class="content container-fluid">

    <div class="d-flex align-items-center gap-2 mb-20">
        <img width="24" class="" src="{{ dynamicAsset('assets/admin/img/page-seo.png') }}"  alt="img">
        <h2 class="fs-20 m-0">{{ translate('Manage Page SEO') }}</h2>
    </div>

        <div class="card mb-3">
            <div class="card">
                <div class="card-header pb-2 pt-3 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title d-flex align-items-center">{{ translate('SEO_Setup_List') }}
                         </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive datatable-custom">
                        <table
                            class="table table-borderless table-thead-bordered table-align-middle table-nowrap card-table ">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-top-0 text-center w-200px text-center">{{ translate('sl') }}</th>

                                    <th class="border-top-0">{{ translate('Pages') }}</th>

                                    <th class="text-center border-top-0">{{ translate('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pages as $key=> $page)
                                    <tr>
                                        <td class="w-200px text-center">{{ $key + 1 }}</td>
                                        <td>
                                            {{$page == 'vendor_list'? translate('restaurant_list') :translate($page) }}
                                        </td>


                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a href="{{ route('admin.pageMetaData', ['page_name' => $page]) }}"
                                                    @if (isset($pageMetaData[$page][0])) class="btn btn--primary btn-outline-primary min-h-41 fw-500 d-flex align-items-center justify-content-center gap-1 px-3 py-2"  >
                                            <i class="tio-edit"> </i>{{ translate('Edit Content') }}
                                            @else
                                            class="btn btn-outline-success"  >
                                            <i class="tio-add-circle">  </i>{{ translate('Add Content') }} @endif
                                                    </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>

        {{-- @include('admin-views.landing_page.react.partials.header_guideline') --}}

    @endsection

    @push('script_2')
    @endpush
