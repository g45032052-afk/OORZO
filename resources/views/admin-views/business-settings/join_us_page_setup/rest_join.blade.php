@extends('layouts.admin.app')
@section('title', translate('Join_Us_Form_Setup'))

@section('3rd_party')
    active
@endsection
@section('reg_page')
    active
@endsection
@section('content')
    @php(  $page_data =  isset($page_data) ? json_decode($page_data ,true)  :[])
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header pb-0">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h1 class="mb-0">{{ translate('messages.Join_Us_Page_Setup') }}</h1>
                <div class="d-flex flex-wrap justify-content-end align-items-center flex-grow-1">
                    <div class="blinkings active">
                        <i class="tio-info text-gray1 fs-16"></i>
                        <div class="business-notes">
                            <h6><img src="{{dynamicAsset('assets/admin/img/notes.png')}}" alt=""> {{translate('Note')}}</h6>
                            <div>
                                {{translate('Don’t_forget_to_click_the_respective_‘Save_Information’_buttons_below_to_save_changes')}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center my-3 gap-3">
                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                    <!-- Nav -->
                    <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
                        <li class="nav-item">
                            <a class="nav-link {{  Request::is('admin/business-settings/restaurant/join-us/*') ? 'active' : '' }} " href="{{ route('admin.business-settings.restaurant_page_setup') }}"   aria-disabled="true">{{translate('messages.Restaurant_Registration_Form')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link  {{  Request::is('admin/business-settings/delivery-man/join-us/*') ? 'active' : '' }}" href="{{  route('admin.business-settings.delivery_man_page_setup') }}"  aria-disabled="true">{{translate('messages.DeliveryMan_Registration_Form')}}</a>
                        </li>
                    </ul>
                    <!-- End Nav -->
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning mb-20" style="--bs-bg-opacity: 0.1;">
            <span class="text-warning lh-1 fs-14">
                <i class="tio-info"></i>
            </span>
            <span>
                {{ translate('messages.All field data displayed on the') }}
                <a href="#" class="font-semibold text-primary text-underline">{{ translate('messages.Restaurant Registration Page') }} </a>
            </span>
        </div>

        <div class="card card-body mb-20">
            <div class="mb-20">
                <h3 class="mb-1">{{ translate('messages.Default_Input_Fields') }}</h3>
                <p class="fs-12 mb-0">{{ translate('messages.These are the required standard fields that must be collected during restaurant registration.') }}</p>
            </div>
            <div class="bg-light rounded">
                <ul  class="requirements-info-list mb-0">
                    <li > {{translate('Restaurant_Name')}} </li>
                    <li > {{translate('Delivery_Address')}} </li>
                    <li > {{translate('Min_Delivery_TIme')}} </li>
                    <li > {{translate('Max_Delivery_Time')}} </li>
                    <li > {{translate('Restaurant_Cover')}} </li>
                    <li > {{translate('Restaurant_Logo')}} </li>
                    <li > {{translate('Cuisine')}} </li>
                    <li > {{translate('Zone')}} </li>
                    <li > {{translate('Latitude_&_Longitude')}} </li>
                    <li > {{translate('Map_Location')}} </li>
                    <li > {{translate('Owner_First_Name')}} </li>
                    <li > {{translate('Owner_Last_Name')}} </li>
                    <li > {{translate('Phone_Number')}} </li>
                    <li > {{translate('Email')}} </li>
                    <li > {{translate('Password')}} </li>
                </ul>
            </div>
        </div>

        <form class="validate-form" action="{{ route('admin.business-settings.restaurant_page_setup_update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-20">
                    <h3 class="mb-0">{{ translate('Custom_Input_Fields') }}</h3>
                    <a href="javascript:"  class="btn btn--primary add-input-data-fields-group"><i class="tio-add-circle mr-1"></i> {{ translate('Add_New_Field') }} </a>
                </div>
                @include('admin-views.business-settings.join_us_page_setup.partials._custom-fields')

                <div class="btn--container justify-content-end mt-2">
                    <button type="reset" class="btn btn--reset min-w-120">{{translate('Reset')}}</button>
                    <button type="submit" class="btn btn--primary min-w-120">{{translate('Save')}}</button>
                </div>
            </div>

        </form>
    </div>
@endsection

