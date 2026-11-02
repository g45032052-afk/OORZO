
<!-- Page Header -->
<div class="page-header pb-0">
    <div class="d-flex flex-wrap justify-content-between align-items-start">
        <h1 class="mb-0">{{ translate('messages.business_setup') }}</h1>
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center my-3 gap-3">
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <!-- Nav -->
            <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
                @if(\App\CentralLogics\Helpers::employee_module_permission_check('my_restaurant'))
                    <li class="nav-item">
                        <a class="nav-link {{Request::is('restaurant-panel/restaurant/view') || Request::is('restaurant-panel/restaurant/edit') ?'active':''}}"
                           href="{{route('vendor.shop.view')}}"
                           aria-disabled="true">{{translate('messages.My_Restaurant')}}
                        </a>
                    </li>
                @endif
                @if(\App\CentralLogics\Helpers::employee_module_permission_check('restaurant_config'))
                    <li class="nav-item">
                        <a class="nav-link {{Request::is('restaurant-panel/business-settings/restaurant-setup')?'active':''}}"
                           href="{{route('vendor.business-settings.restaurant-setup')}}"
                           aria-disabled="true">{{translate('messages.Business_Configuration')}}
                        </a>
                    </li>
                @endif
                @if(\App\CentralLogics\Helpers::employee_module_permission_check('wallet_method'))
                    <li class="nav-item">
                        <a class="nav-link {{Request::is('restaurant-panel/withdraw-method*')?'active':''}}" href="{{route('vendor.wallet-method.index')}}"
                           aria-disabled="true">{{translate('messages.Payment_Information')}}
                        </a>
                    </li>
                @endif
                @if(\App\CentralLogics\Helpers::employee_module_permission_check('my_qr_code'))
                    <li class="nav-item">
                        <a class="nav-link {{Request::is('restaurant-panel/restaurant/qr-view')?'active':''}}" href="{{route('vendor.shop.qr-view')}}"
                           aria-disabled="true">{{translate('messages.My_QR_Code')}}
                        </a>
                    </li>
                @endif
                @if(\App\CentralLogics\Helpers::employee_module_permission_check('notification_setup'))
                    <li class="nav-item">
                        <a class="nav-link {{Request::is('restaurant-panel/business-settings/notification-setup')?'active':''}}"
                           href="{{route('vendor.business-settings.notification-setup')}}" aria-disabled="true">{{translate('messages.Notification_Setup')}}
                        </a>
                    </li>
                @endif
            </ul>
            <!-- End Nav -->
        </div>
    </div>
</div>
<!-- End Page Header -->
