@extends('layouts.admin.app')

@section('title', translate('messages.New_joining_request'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')

    <div class="content container-fluid">
        <div class="card card-from-sm">
            <div class="card-body">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div class="page--header-title">
                            <h1 class="page-header-title"> {{ translate('Restaurant_Details') }} </h1>
                            <p class="page-header-text"> {{ translate('Requested_to_join_at') }}
                                {{ \App\CentralLogics\Helpers::time_date_format($restaurant->created_at) }}</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{route('admin.restaurant.edit',[$restaurant->id,'type' => 'new_join'])}}" class="btn btn-primary px-3"><i class="tio-open-in-new"></i>
                                {{ translate('Edit') }}</a>
                            @if($restaurant->vendor->status === null)
                                <a  data-url="{{route('admin.restaurant.application',[$restaurant['id'],0])}}" data-message="{{translate('messages.you_want_to_reject_this_application')}}"
                                href="javascript:"  class="btn btn--danger font-regular request_alert"> {{ translate('Reject') }} </a>
                            @endif

                            @if($restaurant->vendor->status === null || $restaurant->vendor->status == 0 )
                                <a  data-url="{{route('admin.restaurant.application',[$restaurant['id'],1])}}" data-message="{{translate('messages.you_want_to_approve_this_application')}}"
                                href="javascript:" class="btn btn-success request_alert"> {{ translate('Approve') }} </a>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- End Page Header -->
                <!-- Banner -->
                <section class="shop-details-banner">
                    <div class="card">
                        <div class="card-body px-0 pt-0">
                            <img
                                data-onerror-image="{{ dynamicAsset('assets/admin/img/900x400/img1.jpg') }}"
                                 src="{{ $restaurant->cover_photo_full_url ?? dynamicAsset('assets/admin/img/900x400/img1.jpg') }}"
                                class="shop-details-banner-img onerror-image" alt="">
                            <div class="shop-details-banner-content">
                                <div class="shop-details-banner-content-thumbnail">
                                    <img
                                         data-onerror-image="{{ dynamicAsset('assets/admin/img/160x160/img1.jpg') }}"
                                         src="{{ $restaurant->logo_full_url ?? dynamicAsset('assets/admin/img/160x160/img1.jpg')}}"
                                        class="thumbnail onerror-image" alt="">
                                    <h3 class="mt-4 pt-3 mb-4 d-sm-none">{{ $restaurant->name }} @include('partials._verified_restaurant_badge', ['restaurant' => $restaurant])</h3>
                                </div>
                                <div class="shop-details-banner-content-content">
                                    <h3 class="mt-sm-4 pt-sm-3 mb-4 d-none d-sm-block">{{ $restaurant->name }} @include('partials._verified_restaurant_badge', ['restaurant' => $restaurant])</h3>
                                    <div class="shop-details-model">
                                        <div class="shop-details-model-item">
                                            <img src="{{ dynamicAsset('assets/admin/new-img/icon-1.png') }}"
                                                alt="">
                                            <div class="shop-details-model-item-content">
                                                <h6> {{ translate('Business_Model') }} </h6>
                                                @if ($restaurant->restaurant_model == 'commission')
                                                    <div>{{ translate('Commission_Base') }}</div>
                                                @else
                                                    <div>{{ translate('Subscription_Base') }}</div>
                                                @endif
                                            </div>
                                        </div>


                                        @if ($restaurant->restaurant_model == 'commission')
                                        <div class="shop-details-model-item">
                                            <img src="{{ dynamicAsset('assets/admin/img/transactions/image_pending.png') }}"
                                                alt="">
                                            <div class="shop-details-model-item-content">
                                                <h6> {{ translate('Admin Comission') }} </h6>
                                                <div> {{ $admin_commission }} %</div>
                                            </div>
                                        </div>
                                        @elseif($restaurant?->restaurant_sub_update_application)
                                        <div class="shop-details-model-item">
                                            <img src="{{ dynamicAsset('assets/admin/img/transactions/image_pending.png') }}"
                                                alt="">
                                            <div class="shop-details-model-item-content">

                                                <h6> {{ translate('Package_Name') }} </h6>
                                                <div> {{  Str::limit( $restaurant?->restaurant_sub_update_application?->package?->package_name, 10, '...') }}
                                                    @if ($restaurant?->restaurant_sub_update_application?->is_trial == 1)
                                                    <span class="badge badge-soft-success">{{ translate('messages.Free_Trial') }}</span>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                        @elseif($restaurant->restaurant_model == 'none' && $restaurant->package_id )
                                        <div class="shop-details-model-item">
                                            <img src="{{ dynamicAsset('assets/admin/img/transactions/image_pending.png') }}"
                                                alt="">
                                            <div class="shop-details-model-item-content">

                                                <h6> {{ translate('Package_Name') }} </h6>
                                                <div> {{  Str::limit( App\Models\SubscriptionPackage::where('id',$restaurant->package_id)->first()?->package_name, 10, '...') }}

                                                    <small class="badge badge-soft-danger">{{ translate('messages.payment_failed') }}</small>


                                                </div>
                                            </div>
                                        </div>



                                        @endif





                                        <div class="shop-details-model-item">
                                            <img src="{{ dynamicAsset('assets/admin/new-img/icon-4.png') }}"
                                                alt="">
                                            <div class="shop-details-model-item-content">
                                                <h6> {{ translate('Address') }} </h6>
                                                <div>{{ $restaurant->address }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-header justify-content-between align-items-center">
                            <label class="input-label text-capitalize d-inline-flex align-items-center m-0">
                                <span class="line--limit-1"><img src="{{ dynamicAsset('assets/admin/img/company.png') }}"
                                        alt=""> {{ translate('Registration_Information') }} </span>
                                <span data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('See_the_general_information_a_restaurant_provides_during_registration.') }}"
                                    class="input-label-secondary">
                                    <img src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}"
                                        alt="info"></span>
                            </label>
                        </div>
                        <div class="card-body">
                            <div class="__registration-information">
                                <div class="item">
                                    <h5> {{ translate('General_Information') }}</h5>
                                    <ul>
                                        <li>
                                            <span class="left"> {{ translate('Restaurant_Name') }} </span>
                                            <span class="right">{{ $restaurant->name }} @include('partials._verified_restaurant_badge', ['restaurant' => $restaurant])</span>
                                        </li>
                                        <li>
                                            <span class="left">{{ translate('Zone') }} </span>
                                            <span class="right">{{ $restaurant->zone->name }}</span>
                                        </li>


                                        <?php
                                            $delivery_time_start = explode('-', $restaurant->delivery_time)[0] ?? 10;
                                            $delivery_time_end = explode('-', $restaurant->delivery_time)[1] ?? 30;
                                            $delivery_time_type = explode('-', $restaurant->delivery_time)[2] ?? 'min';
                                        ?>
                                        <li>
                                            <span class="left"> {{ translate('Delivery_Time_Within') }} </span>
                                            <span class="right">{{ $delivery_time_start }}-{{ $delivery_time_end }}
                                                {{ $delivery_time_type }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="item ">
                                    <h5> {{ translate('Owner_Information') }} </h5>
                                    <ul>
                                        <li>
                                            <span class="left"> {{ translate('First_Name') }}</span>
                                            <span class="right">{{ $restaurant->vendor->f_name }}</span>
                                        </li>
                                        <li>
                                            <span class="left"> {{ translate('Last_Name') }}</span>
                                            <span class="right">{{ $restaurant->vendor->l_name }}</span>
                                        </li>
                                        <li>
                                            <span class="left">{{ translate('Phone') }}</span>
                                            <span class="right">{{ $restaurant->phone }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="item">
                                    <h5>{{ translate('Login_Information') }}</h5>
                                    <ul>
                                        <li>
                                            <span class="left">{{ translate('Email') }}</span>
                                            <span class="right">{{ $restaurant->email }}</span>
                                        </li>
                                        <li>
                                            <span class="left">{{ translate('Password') }} </span>
                                            <span class="right">*************</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="item">
                                    <h5>{{ translate('Cuisines') }} </h5>
                                    <div>
                                        @foreach ($restaurant->cuisine as $key => $cuisine)
                                            {{ $cuisine->name }} ,
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                        @if ($restaurant->tin || $restaurant->tin_certificate_image)

        <div class="row pt-3 g-3">
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title m-0 d-flex align-items-center">
                            <span class="card-header-icon mr-2">
                                <i class="tio-user"></i>
                            </span>
                            <span class="ml-1">{{ translate('Business TIN') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">

                        <div class="resturant--info-address flex-sm-nowrap flex-wrap gap-2 single-document-uploaderwrap" data-document-uploader>
                            <div>
                                <div class="file-assets"
                                        data-picture-icon="{{ dynamicAsset('assets/admin/img/picture.svg') }}"
                                        data-document-icon="{{ dynamicAsset('assets/admin/img/document.svg') }}"
                                        data-blank-thumbnail="{{ dynamicAsset('assets/admin/img/picture.svg') }}">
                                </div>
                                <!-- Upload box -->
                                <div class="d-flex align-items-center flex-md-nowrap flex-wrap gap-2 justify-content-center pdf-container">
                                    <div class="document-upload-wrapper d-none">
                                        <input type="file" name="tin_certificate_image" class="document_input" accept=".doc, .pdf, .jpg, .png, .jpeg">
                                        <div class="textbox">
                                            <img width="40" height="40" class="svg"
                                                    src="{{ dynamicAsset('assets/admin/img/doc-uploaded.png') }}"
                                                    alt="">
                                            <p class="fs-12 mb-0">{{ translate('Select a file or') }} <span class="font-semibold">{{ translate('Drag & Drop') }}</span>
                                                {{ translate('here') }}</p>
                                        </div>
                                    </div>
                                    <div class="pdf-single pdf-single-reponsive" data-file-name="${file.name}" data-file-url="{{ $restaurant->tin_certificate_image_full_url ?? dynamicAsset('assets/admin/img/upload-cloud.png') }}">
                                        <div class="pdf-frame">
                                            @php($imgPath =  $restaurant->tin_certificate_image_full_url ?? dynamicAsset('assets/admin/img/upload-cloud.png'))
                                            @if(Str::endsWith($imgPath, ['.pdf', '.doc', '.docx']))
                                                @php($imgPath =  dynamicAsset('assets/admin/img/document.svg'))
                                            @endif
                                            <img class="pdf-thumbnail-alt" src="{{ $imgPath }}" alt="File Thumbnail">
                                        </div>
                                        <div class="overlay">
                                            <div class="pdf-info">
                                                @if(Str::endsWith($imgPath, ['.pdf', '.doc', '.docx']))
                                                    <img src="{{ dynamicAsset('assets/admin/img/document.svg') }}" width="34" alt="File Type Logo">
                                                @else
                                                    <img src="{{ dynamicAsset('assets/admin/img/picture.svg') }}" width="34" alt="File Type Logo">
                                                @endif
                                                <div class="file-name-wrapper">
                                                    <span class="file-name js-filename-truncate text-limit-show" data-limit="15">{{ $restaurant->tin_certificate_image }}</span>
                                                    <span class="opacity-50">{{ translate('Click to view the file') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($restaurant->tin)
                                    <div class="d-flex-column address-info address-info-2 list-unstyled list-unstyled-py-3">
                                        <div class=" d-flex justify-content-start gap-1">
                                            <span class="text-custom-nowrap text-wrap"><strong class=" text-dark">
                                                    {{ translate('Taxpayer Identification Number(TIN)') }}: </strong></span>
                                            <span class="pl-1">{{ $restaurant->tin }}</span>
                                        </div>
                                        <div class=" d-flex justify-content-start gap-1">
                                            <span class="text-custom-nowrap text-wrap"><strong
                                                    class=" text-dark">{{ translate('Expire Date') }}: </strong></span>
                                            <span class="pl-1">{{ $restaurant->tin_expire_date }}</span>
                                        </div>
                                    </div>

                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- <div class="resturant--info-address flex-sm-nowrap flex-wrap gap-2">
                            <div class="pdf-single  cus-document-responsive"
                                data-pdf-url="{{ $restaurant->tin_certificate_image_full_url ?? dynamicAsset('assets/admin/img/upload-cloud.png') }}">
                                <div class="pdf-frame">
                                    @php($imgPath = $restaurant->tin_certificate_image_full_url ?? dynamicAsset('assets/admin/img/upload-cloud.png'))
                                    @if (Str::endsWith($imgPath, ['.pdf', '.doc', '.docx']))
                                        @php($imgPath = dynamicAsset('assets/admin/img/document.svg'))
                                    @endif
                                    <img class="pdf-thumbnail-alt" src="{{ $imgPath }}" alt="File Thumbnail">
                                </div>
                                <div class="overlay">
                                    <a href="javascript:void(0);" class="download-btn" title="">
                                        <i class="tio-download-to"></i>
                                    </a>
                                    <div class="pdf-info d-flex gap-10px align-items-center">
                                        @if (Str::endsWith($imgPath, ['.pdf', '.doc', '.docx']))
                                            <img src="{{ dynamicAsset('assets/admin/img/document.svg') }}" width="34"
                                                alt="File Type Logo">
                                        @else
                                            <img src="{{ dynamicAsset('assets/admin/img/picture.svg') }}" width="34"
                                                alt="File Type Logo">
                                        @endif
                                        <div class="fs-13 text--title d-flex flex-column">
                                            <span class="file-name js-filename-truncate"></span>
                                            <span class="opacity-50">{{ translate('Click to view the file') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex-column address-info address-info-2 list-unstyled list-unstyled-py-3">

                                <div class=" d-flex justify-content-start gap-1">
                                    <span class="text-custom-nowrap text-wrap"><strong class=" text-dark">
                                            {{ translate('Taxpayer Identification Number(TIN)') }}: </strong></span>
                                    <span class="pl-1">{{ $restaurant->tin }}</span>
                                </div>

                                <div class=" d-flex justify-content-start gap-1">
                                    <span class="text-custom-nowrap text-wrap"><strong
                                            class=" text-dark">{{ translate('Expire Date') }}: </strong></span>
                                    <span class="pl-1">{{ $restaurant->tin_expire_date }}</span>
                                </div>
                            </div>

                        </div> -->
                    </div>
                </div>
            </div>
        </div>
        @endif

                        @if($restaurant->additional_data && count(json_decode($restaurant->additional_data, true)) > 0 )
                        <div class="row mb-2">

                        <div class="col-lg-12  mt-2">
                            <div class="card ">

                                <div class="card-header justify-content-between align-items-center">
                                    <label class="input-label text-capitalize d-inline-flex align-items-center m-0">
                                        <span class="line--limit-1"><img src="{{ dynamicAsset('assets/admin/img/company.png') }}"
                                                alt=""> {{ translate('Additional_Information') }} </span>
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('Additional_Information') }}"
                                            class="input-label-secondary">
                                            <img src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}" alt="info"></span>
                                    </label>
                                </div>
                                <div class="card-body">
                                    <div class="__registration-information">
                                        <div class="item">
                                            <ul>
                                                @foreach (json_decode($restaurant->additional_data, true) as $key => $item)
                                                    @if (is_array($item))


                                                    @foreach ($item as $k => $data)
                                                    <li>
                                                        <span class="left"> {{ $k == 0 ? translate($key) : ''}} </span>
                                                        <span class="right">{{ $data }} </span>
                                                    </li>
                                                    @endforeach
                                                    @else
                                                    <li>
                                                        <span class="left"> {{ translate($key) }} </span>
                                                        <span class="right">{{ $item ?? translate('messages.N/A')}} </span>
                                                    </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        @endif

                        @include('admin-views.partials._additional_documents', ['documents' => $restaurant->additional_documents, 'prefix' => 'additional_documents'])
                    <!-- Card -->
                </section>
                <!-- Banner -->
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/document-upload.js"></script>
    <script>
        "use strict";


        $('.request_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            request_alert(url, message)
        })
        function request_alert(url, message) {
            Swal.fire({
                title: "{{ translate('messages.are_you_sure_?') }}",
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: "{{ translate('messages.no') }}",
                confirmButtonText: "{{ translate('messages.yes') }}",
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    </script>
@endpush
