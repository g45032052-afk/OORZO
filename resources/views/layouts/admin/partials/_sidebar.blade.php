<?php
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Cache;

$order = Cache::rememberForever('order_stats_summary', function () {
    return \App\Models\Order::Notpos()
        ->HasSubscriptionToday()
        ->selectRaw(
            '
            COUNT(*) as total,
            COUNT(CASE WHEN order_type = "dine_in" THEN 1 END) as dine_in,
            COUNT(CASE WHEN order_status = "delivered" THEN 1 END) as delivered,
            COUNT(CASE WHEN order_status = "canceled" THEN 1 END) as canceled,
            COUNT(CASE WHEN order_status = "failed" THEN 1 END) as failed,
            COUNT(CASE WHEN order_status = "refunded" THEN 1 END) as refunded,
            COUNT(CASE WHEN order_status = "refund_requested" THEN 1 END) as refund_requested,
            COUNT(CASE WHEN order_status IN ("confirmed", "processing","handover") THEN 1 END) as processing,
            COUNT(CASE WHEN created_at <> schedule_at AND scheduled = 1 THEN 1 END) as scheduled
        ',
        )
        ->first();
});

$order_sch = Cache::rememberForever('order_scheduled_stats', function () {
    return \App\Models\Order::Notpos()
        ->HasSubscriptionToday()
        ->OrderScheduledIn(30)
        ->selectRaw(
            '
            COUNT(CASE WHEN order_status = "pending" THEN 1 END) as pending,
            COUNT(CASE WHEN order_status = "picked_up" THEN 1 END) as picked_up,
            COUNT(CASE WHEN order_status IN ("accepted", "confirmed","processing","handover","picked_up") THEN 1 END) as ongoing,
            COUNT(CASE WHEN delivery_man_id IS NULL  AND order_type = "delivery" AND order_status NOT IN ("delivered", "failed","canceled","refund_requested","refund_request_canceled","refunded") THEN 1 END) as searching_dm,
            COUNT(CASE WHEN order_status = "accepted" THEN 1 END) as accepted
        ',
        )
        ->first();
});

?>
<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar__brand-wrapper navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                @php($restaurant_logo = \App\CentralLogics\Helpers::getSettingsDataFromConfig(settings: 'logo', relations: ['storage']))
                <a class="navbar-brand d-block p-0" href="{{ route('admin.dashboard') }}" aria-label="Front">
                    <img class="navbar-brand-logo sidebar--logo-design"
                        src="{{ Helpers::get_full_url('business', $restaurant_logo?->value, $restaurant_logo?->storage[0]?->value ?? 'public', 'favicon') }}"
                        alt="image">
                    <img class="navbar-brand-logo-mini sidebar--logo-design-2"
                        src="{{ Helpers::get_full_url('business', $restaurant_logo?->value, $restaurant_logo?->storage[0]?->value ?? 'public', 'favicon') }}"
                        alt="image">
                </a>
                <!-- End Logo -->

                <!-- Navbar Vertical Toggle -->
                <button type="button"
                    class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                    <i class="tio-clear tio-lg"></i>
                </button>
                <!-- End Navbar Vertical Toggle -->

                <div class="navbar-nav-wrap-content-left d-none d-xl-block">
                    <!-- Navbar Vertical Toggle -->
                    <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                        <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                            data-placement="right" title="Collapse"></i>
                        <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                            data-template='<div class="tooltip d-none" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                            data-toggle="tooltip" data-placement="right" title="Expand"></i>
                    </button>
                    <!-- End Navbar Vertical Toggle -->
                </div>

            </div>

            <!-- Content -->
            <div class="navbar-vertical-content bg-334257" id="navbar-vertical-content">
                <!-- Search Form -->
                <form class="sidebar--search-form" autocomplete="off">
                    <input autocomplete="false" name="hidden" type="text" class="d-none">
                    <div class="search--form-group">
                        <button type="button" class="btn"><i class="tio-search"></i></button>
                        <input type="text" id="search" class="form-control form--control"
                            placeholder="{{ translate('messages.Search_Menu...') }}">
                    </div>
                </form>
                <!-- Search Form -->
                <ul class="navbar-nav navbar-nav-lg nav-tabs mt-3">
                    <!-- Dashboards -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.dashboard') }}"
                            title="{{ translate('messages.dashboard') }}">
                            <i class="tio-dashboard-vs nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('messages.dashboard') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Dashboards -->
                    @if (Helpers::module_permission_check('pos'))
                        <!-- POS -->
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/pos') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.pos.index') }}"
                                title="{{ translate('messages.pos') }}">
                                <i class="tio-receipt nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Point_of_Sale') }}</span>
                            </a>
                        </li>
                        <!-- End POS -->
                    @endif



                    <!-- Orders -->
                    @if (Helpers::module_permission_check('order'))
                        <li class="nav-item">
                            <small class="nav-subtitle">{{ translate('messages.order_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/order*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.orders') }}">
                                <i class="tio-file-text-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.orders') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/order*') && (Request::is('admin/order/subscription*') == false && Request::is('admin/order-cancel-reasons') == false) ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/all') ? 'active' : '' }} @yield('all_order')">
                                    <a class="nav-link" href="{{ route('admin.order.list', ['all']) }}"
                                        title="{{ translate('messages.all_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.all') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Notpos()->HasSubscriptionToday()->count() }} ==
                                            --}}
                                                {{ $order->total }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/scheduled') ? 'active' : '' }} @yield('scheduled')">
                                    <a class="nav-link" href="{{ route('admin.order.list', ['scheduled']) }}"
                                        title="{{ translate('messages.scheduled_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.scheduled') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Scheduled()->HasSubscriptionToday()->count() }}
                                            == --}}
                                                {{ $order->scheduled }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/pending') ? 'active' : '' }} @yield('pending')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['pending']) }}"
                                        title="{{ translate('messages.pending_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.pending') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::Pending()->HasSubscriptionToday()->OrderScheduledIn(30)->count()
                                            }} == --}}
                                                {{ $order_sch->pending }}
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="nav-item {{ Request::is('admin/order/list/accepted') ? 'active' : '' }} @yield('accepted')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['accepted']) }}"
                                        title="{{ translate('messages.accepted_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.accepted') }}
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::AccepteByDeliveryman()->HasSubscriptionToday()->OrderScheduledIn(30)->count()
                                            }} == --}}

                                                {{ $order_sch->accepted }}

                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/processing') ? 'active' : '' }} @yield('processing')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['processing']) }}"
                                        title="{{ translate('messages.processing_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.processing') }}
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::whereIn('order_status',
                                            ['confirmed','processing','handover'])->HasSubscriptionToday()->count() }}==
                                            --}}
                                                {{ $order->processing }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/food_on_the_way') ? 'active' : '' }} @yield('picked_up')">
                                    <a class="nav-link text-capitalize"
                                        href="{{ route('admin.order.list', ['food_on_the_way']) }}"
                                        title="{{ translate('messages.foodOnTheWay_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.food_On_The_Way') }}
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::FoodOnTheWay()->HasSubscriptionToday()->OrderScheduledIn(30)->count()
                                            }}== --}}
                                                {{ $order_sch->picked_up }}

                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/delivered') ? 'active' : '' }} @yield('delivered')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['delivered']) }}"
                                        title="{{ translate('messages.delivered_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.delivered') }}
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::Delivered()->HasSubscriptionToday()->Notpos()->count()
                                            }}== --}}
                                                {{ $order->delivered }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/canceled') ? 'active' : '' }} @yield('canceled')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['canceled']) }}"
                                        title="{{ translate('messages.canceled_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.canceled') }}
                                            <span class="badge badge-soft-danger badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Canceled()->HasSubscriptionToday()->count() }}==
                                            --}}
                                                {{ $order->canceled }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/failed') ? 'active' : '' }} @yield('failed')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['failed']) }}"
                                        title="{{ translate('messages.payment_failed_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container text-capitalize">
                                            {{ translate('messages.payment_failed') }}
                                            <span class="badge badge-soft-danger badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::failed()->HasSubscriptionToday()->count() }}==
                                            --}}
                                                {{ $order->failed }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/refunded') ? 'active' : '' }} @yield('refunded')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['refunded']) }}"
                                        title="{{ translate('messages.refunded_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.refunded') }}
                                            <span
                                                class="badge badge-soft-danger badge-pill ml-1">{{ $order->refunded }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/order/list/dine_in') ? 'active' : '' }} @yield('dine_in')">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['dine_in']) }}"
                                        title="{{ translate('messages.dine_in_orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.dine_in') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ $order->dine_in }}
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="nav-item {{ Request::is('admin/order/offline/payment/list*') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.order.offline_verification_list', ['all']) }}"
                                        title="{{ translate('Offline_Payments') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.Offline_Payments') }}
                                            <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                                {{ \App\Models\Order::has('offline_payments')->Notpos()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/order/subscription*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.order.subscription.index') }}"
                                title="{{ translate('messages.Subscription_orders') }} ">
                                <i class="tio-appointment nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Subscription_orders') }}</span>
                            </a>
                        </li>




                        <!-- Order dispachment -->
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/dispatch/*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.Dispatch_Management') }}">
                                <i class="tio-clock nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Dispatch_Management') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/dispatch*') ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/dispatch/list/searching_for_deliverymen') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.dispatch.list', ['searching_for_deliverymen']) }}"
                                        title="{{ translate('messages.searching_DeliveryMan') }} {{ $order_sch->searching_dm }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ translate('messages.searching_DeliveryMan') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{-- {{
                                            \App\Models\Order::SearchingForDeliveryman()->OrderScheduledIn(30)->count()
                                            }} --}}
                                                {{ $order_sch->searching_dm }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/dispatch/list/on_going') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.dispatch.list', ['on_going']) }}"
                                        title="{{ translate('messages.ongoing_Orders') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.ongoing_Orders') }}
                                            <span class="badge badge-soft-dark bg-light badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Ongoing()->OrderScheduledIn(30)->count() }}==
                                            --}}
                                                {{ $order_sch->ongoing }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- Order dispachment End-->

                        <!-- Order refund -->
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/refund/*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.Order_Refunds') }}">
                                <i class="tio-receipt nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Order_Refunds') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/refund*') ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/refund/requested') ||
                                    Request::is('admin/refund/refunded') ||
                                    Request::is('admin/refund/rejected')
                                        ? 'active'
                                        : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.refund.refund_attr', ['requested']) }}"
                                        title="{{ translate('messages.New_Refund_Requests') }} ">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('messages.New_Refund_Requests') }}
                                            <span class="badge badge-soft-danger badge-pill ml-1">
                                                {{-- {{ \App\Models\Order::Refund_requested()->count() }}= --}}
                                                {{ $order->refund_requested }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- Order refund End-->
                    @endif
                    <!-- End Orders -->



                    @if (Helpers::module_permission_check('zone') || Helpers::module_permission_check('restaurant'))
                        <!-- Restaurant -->
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.restaurant_section') }}">{{ translate('messages.restaurant_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if (Helpers::module_permission_check('zone'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/zone*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.zone.home') }}"
                                title="{{ translate('messages.zone_setup') }}">
                                <i class="tio-poi-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.zone_setup') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Helpers::module_permission_check('restaurant'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/cuisine/add') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.cuisine.add') }}"
                                title="{{ translate('messages.cuisine') }}">
                                <i class="tio-link nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.cuisine') }}
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/restaurant/*') && !Request::is('admin/restaurant/withdraw_list') && !Request::is('admin/restaurant/withdraw-view*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.restaurants') }}">
                                <i class="tio-restaurant nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.restaurants') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ (Request::is('admin/restaurant/*') && !Request::is('admin/restaurant/withdraw_list')) ||
                                stripos(Request()->fullurl(), 'pending-list', 5)
                                    ? 'block'
                                    : 'none' }}">
                                {{ stripos(Request()->fullurl(), 'pending-list', 5) }}
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/restaurant/add') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.restaurant.add') }}"
                                        title="{{ translate('messages.add_restaurant') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('messages.add_restaurant') }}
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="navbar-item @yield('restaurant_list') {{ !stripos(Request()->fullurl(), 'pending-list', 5) && (Request::is('admin/restaurant/list') || Request::is('admin/restaurant/transcation/*') || Request::is('admin/restaurant/view*')) ? 'active' : '' }} ">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.restaurant.list') }}"
                                        title="{{ translate('messages.restaurants_list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.restaurants_list') }}</span>
                                    </a>
                                </li>

                                <li
                                    class="navbar-item  @yield('restaurant_new_join') {{ stripos(Request()->fullurl(), 'pending-list', 5) || Request::is('admin/restaurant/pending/list*') || Request::is('admin/restaurant/denied/list*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.restaurant.pending') }}"
                                        title="{{ translate('messages.New_joining_request') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.New_joining_request') }}
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="nav-item {{ Request::is('admin/restaurant/bulk-import') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.restaurant.bulk-import') }}"
                                        title="{{ translate('messages.bulk_import') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bulk_import') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/restaurant/bulk-export') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.restaurant.bulk-export-index') }}"
                                        title="{{ translate('messages.bulk_export') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bulk_export') }}</span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endif
                    <!-- End Restaurant -->

                    @if (Helpers::module_permission_check('category') ||
                            Helpers::module_permission_check('addon') ||
                            Helpers::module_permission_check('food'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.food_section') }}">{{ translate('messages.food_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    <!-- Category -->
                    @if (Helpers::module_permission_check('category'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/category*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('Main Categories') }}">
                                <i class="tio-category nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Main Categories') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/category*')  ? 'block' : 'none' }}">


                                <li
                                    class="nav-item {{ Request::is('admin/category/add') || Request::is('admin/category/edit/*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.category.add') }}"
                                        title="{{ translate('Main Categories') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Main Categories') }}</span>
                                    </a>
                                </li>


                                <li
                                    class="nav-item {{ Request::is('admin/category/add-sub-category') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.category.add-sub-category') }}"
                                        title="{{ translate('Main Sub categories') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Main Sub categories') }}</span>
                                    </a>
                                </li>


                                <li class="nav-item {{ Request::is('admin/category/bulk-import') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.category.bulk-import') }}"
                                        title="{{ translate('messages.bulk_import') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bulk_import') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/category/bulk-export') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.category.bulk-export-index') }}"
                                        title="{{ translate('messages.bulk_export') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bulk_export') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Admin can always view/manage restaurant categories. The
                             restaurant_category_status setting only controls whether
                             vendors may manage their own categories, not admin access. --}}
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/restaurant-category*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.restaurant-category.index') }}"
                                title="{{ translate('messages.restaurant_categories') }}">
                                <i class="tio-print nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.restaurant_categories') }}</span>
                            </a>
                        </li>


                    @endif
                    <!-- End Category -->

                    <!-- AddOn -->
                    @if (Helpers::module_permission_check('addon'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/addon/*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.addons') }}">
                                <i class="tio-add-circle-outlined nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.addons') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/addon/*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/addon/addon-category') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.addon.addon-category') }}"
                                        title="{{ translate('messages.Addon_Category') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.Addon_Category') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/addon/add-new') || Request::is('admin/addon/edit/*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.addon.add-new') }}"
                                        title="{{ translate('messages.addon_list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.list') }}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ Request::is('admin/addon/bulk-import') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.addon.bulk-import') }}"
                                        title="{{ translate('messages.bulk_import') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bulk_import') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/addon/bulk-export') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.addon.bulk-export-index') }}"
                                        title="{{ translate('messages.bulk_export') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bulk_export') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <!-- End AddOn -->
                    <!-- Food -->
                    @if (Helpers::module_permission_check('food'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/food*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.foods') }}">
                                <i class="tio-fastfood nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.foods') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/food*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/food/add-new') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.food.add-new') }}"
                                        title="{{ translate('messages.add_new') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.add_new') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/food/list') || Request::is('admin/food/view/*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.food.list') }}"
                                        title="{{ translate('messages.food_list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.list') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/food/reviews') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.food.reviews') }}"
                                        title="{{ translate('messages.review_list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.review') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/food/bulk-import') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.food.bulk-import') }}"
                                        title="{{ translate('messages.bulk_import') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bulk_import') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/food/bulk-export') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.food.bulk-export-index') }}"
                                        title="{{ translate('messages.bulk_export') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bulk_export') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <!-- End Food -->



                    @if (Helpers::module_permission_check('campaign') ||
                            Helpers::module_permission_check('bogo_offer') ||
                            Helpers::module_permission_check('happy_hour') ||
                            Helpers::module_permission_check('coupon') ||
                            Helpers::module_permission_check('cashback') ||
                            Helpers::module_permission_check('advertisement') ||
                            Helpers::module_permission_check('notification') ||
                            Helpers::module_permission_check('banner'))
                        <!-- Marketing section -->
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('Promotion_management') }}">{{ translate('Promotions_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif


                    <!-- Campaign -->
                    @if (Helpers::module_permission_check('campaign'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/campaign*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.campaigns') }}">
                                <i class="tio-notice nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.campaigns') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/campaign*') ? 'block' : 'none' }}">

                                <li class="nav-item {{ Request::is('admin/campaign/basic/*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.campaign.list', 'basic') }}"
                                        title="{{ translate('messages.basic_campaign') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.basic_campaign') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/campaign/item/*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.campaign.list', 'item') }}"
                                        title="{{ translate('messages.food_campaign') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.food_campaign') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <!-- End Campaign -->

                    <!-- BOGO Offer -->
                    @if (Helpers::module_permission_check('bogo_offer'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/bogo-offer*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.bogo-offer.list') }}"
                                title="{{ translate('messages.BOGO_Offer') }}">
                                <i class="tio-gift nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.BOGO_Offer') }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End BOGO Offer -->

                    <!-- Happy Hour -->
                    @if (Helpers::module_permission_check('happy_hour'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/happy-hour*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.happy-hour.list') }}"
                                title="{{ translate('messages.Happy_Hour') }}">
                                <i class="tio-clock nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Happy_Hour') }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End Happy Hour -->

                    <!-- Coupon -->
                    @if (Helpers::module_permission_check('coupon'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/coupon*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.coupon.add-new') }}"
                                title="{{ translate('messages.coupons') }}">
                                <i class="tio-ticket nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.coupons') }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End Coupon -->

                    @if (Helpers::module_permission_check('cashback'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/cashback*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.cashback.add-new') }}"
                                title="{{ translate('messages.cashback') }}">
                                <i class="tio-settings-back nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.cashback') }}</span>
                            </a>
                        </li>
                    @endif


                    <!-- Banner -->
                    @if (Helpers::module_permission_check('banner'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/banner*') && !Request::is('admin/banner/promotional-banner*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.banner.add-new') }}"
                                title="{{ translate('messages.banners') }}">
                                <i class="tio-bookmark nav-icon side-nav-icon--design"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.banners') }}</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/banner/promotional-banner*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.banner.promotional_banner') }}"
                                title="{{ translate('messages.promotional_banner') }}">
                                <i class="tio-tabs nav-icon side-nav-icon--design"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.promotional_banner') }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End Banner -->



                    <!-- advertisement -->
                    @if (Helpers::module_permission_check('advertisement'))
                        <li class="navbar-vertical-aside-has-menu  @yield('advertisement')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.advertisement') }}">
                                <i class="tio-tv-old nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.advertisement') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/advertisement*') ? 'block' : 'none' }}">

                                <li class="nav-item @yield('advertisement_create')">
                                    <a class="nav-link " href="{{ route('admin.advertisement.create') }}"
                                        title="{{ translate('messages.New_Advertisement') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate">{{ translate('messages.New_Advertisement') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item @yield('advertisement_request')">
                                    <a class="nav-link " href="{{ route('admin.advertisement.requestList') }}"
                                        title="{{ translate('messages.Ad_Requests') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.Ad_Requests') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item @yield('advertisement_list')">
                                    <a class="nav-link " href="{{ route('admin.advertisement.index') }}"
                                        title="{{ translate('messages.Ads_list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.Ads_list') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <!-- End Campaign -->
                                         @if (Helpers::module_permission_check('notification'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/notification*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.notification.add-new') }}"
                                title="{{ translate('messages.push_notification') }}">
                                <i class="tio-notifications-on nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.push_notification') }}
                                </span>
                            </a>
                        </li>
                    @endif
                    <!-- End Notification -->

                    @if (  addon_published_status('ReelsModule') && Helpers::module_permission_check('reels') )
                        <li class="nav-item">
                            <small class="nav-subtitle">{{ translate('messages.Reels_Management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li class="navbar-vertical-aside-has-menu @yield('reels_create')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.reels.create') }}"
                               title="{{ translate('messages.Create_Reels') }}">
                                <i class="tio-video-camera-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Create_Reels') }}</span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu @yield('reels_list')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.reels.index') }}"
                               title="{{ translate('messages.Reels_List') }}">
                                <i class="tio-format-bullets nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Reels_List') }}</span>
                            </a>
                        </li>
                    @endif





                    @if (Helpers::module_permission_check('chat') || Helpers::module_permission_check('contact_message'))
                        <li class="nav-item">
                            <small class="nav-subtitle">{{ translate('messages.Help_&_Support') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if (Helpers::module_permission_check('chat'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/message/list') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.message.list', ['tab' => 'customer']) }}"
                                title="{{ translate('messages.Chattings') }}">
                                <i class="tio-chat nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Chattings') }}
                                </span>
                            </a>
                        </li>
                    @endif
                    @if (Helpers::module_permission_check('contact_message'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/contact/*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.contact.list') }}"
                                title="{{ translate('messages.Contact_messages') }}">
                                <i class="tio-messages nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Contact_messages') }}
                                </span>
                            </a>
                        </li>
                    @endif




                    <!-- Custommer -->
                    @if (Helpers::module_permission_check('customerList') || Helpers::module_permission_check('customer_wallet'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.customer_section') }}">{{ translate('messages.customer_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if (Helpers::module_permission_check('customerList'))
                        <li class="navbar-vertical-aside-has-menu @yield('customerDetails')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.customer.list') }}"
                                title="{{ translate('messages.Customer_List') }}">
                                <i class="tio-poi-user nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.customeres') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (Helpers::module_permission_check('customer_wallet'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ !Request::is('admin/customer/wallet/report*') && Request::is('admin/customer/wallet*') ? 'active' : '' }}">

                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.Customer_Wallet') }}">
                                <i class="tio-wallet nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate  text-capitalize">
                                    {{ translate('messages.wallet') }}
                                </span>
                            </a>

                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ !Request::is('admin/customer/wallet/report*') && Request::is('admin/customer/wallet*') ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/customer/wallet/add-fund') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.wallet.add-fund') }}"
                                        title="{{ translate('messages.add_fund') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.add_fund') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/customer/wallet/bonus*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.wallet.bonus.add-new') }}"
                                        title="{{ translate('messages.bonus') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bonus') }}</span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endif
                    @if (Helpers::module_permission_check('customerList'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/customer/loyalty-point-report*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link  nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.loyalty_point') }}">
                                <i class="tio-medal nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate  text-capitalize">
                                    {{ translate('messages.loyalty_point') }}
                                </span>
                            </a>

                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/customer/loyalty-point-report*') ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/customer/loyalty-point-report*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.loyalty-point.report') }}"
                                        title="{{ translate('messages.report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.report') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/customer/subscribed') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.customer.subscribed') }}"
                                title="{{ translate('messages.Subscribed_Emails') }}">
                                <i class="tio-email-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.subscribed_mail_list') }}
                                </span>
                            </a>
                        </li>
                        </li>
                    @endif
                    <!-- End Custommer -->

                    <!-- Pro Customer Management -->
                    @if (Helpers::get_business_settings('pro_member_status') == 1 && Helpers::module_permission_check('pro_customer'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.pro_customer_section') }}">{{ translate('messages.pro_customer_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu @yield('pro_customer_benefits_setup')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.pro-customer.benefits-setup') }}"
                                title="{{ translate('messages.Pro_Customer_Benefits_Setup') }}">
                                <i class="tio-gift nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.pro_customer_benefits_setup') }}
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu @yield('pro_customer_price_setup')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.pro-customer.price-setup') }}"
                                title="{{ translate('messages.Price_Setup') }}">
                                <i class="tio-dollar-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.price_setup') }}
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu @yield('pro_customer_list')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.pro-customer.list') }}"
                                title="{{ translate('messages.Pro_Customer_List') }}">
                                <i class="tio-poi-user nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.pro_customer_list') }}
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu @yield('pro_customer_transactions')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.pro-customer.transactions') }}"
                                title="{{ translate('messages.Pro_Customer_Transactions') }}">
                                <i class="tio-receipt-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.transactions') }}
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu @yield('pro_customer_additional_setup')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.pro-customer.additional-setup') }}"
                                title="{{ translate('messages.Additional_Setup') }}">
                                <i class="tio-settings nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.additional_setup') }}
                                </span>
                            </a>
                        </li>
                    @endif
                    <!-- End Pro Customer Management -->

                    <!-- Delivery Management -->
                    @if (Helpers::module_permission_check('deliveryman') || Helpers::module_permission_check('zone'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.delivery_section') }}">{{ translate('messages.delivery_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if (Helpers::module_permission_check('deliveryman'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/vehicle/*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.vehicle.list') }}"
                                title="{{ translate('messages.vehicle_type_setup') }}">
                                <i class="tio-car nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.vehicle_type_setup') }}
                                </span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/shift*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.shift.list') }}"
                                title="{{ translate('messages.Shift_setup') }}">
                                <i class="tio-calendar nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Shift_setup') }}
                                </span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.deliveryman') }}">
                                <i class="tio-running nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.deliveryman') }}</span>
                            </a>

                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/delivery-man*') ? 'block' : 'none' }}">

                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/pending-delivery-man-view/*') || Request::is('admin/delivery-man/pending/list') || Request::is('admin/delivery-man/denied/list') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.delivery-man.pending') }}"
                                        title="{{ translate('messages.New_joining_request') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('messages.New_join_request') }}
                                        </span>
                                    </a>
                                </li>


                                <li class="nav-item {{ Request::is('admin/delivery-man/add') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.delivery-man.add') }}"
                                        title="{{ translate('messages.add_delivery_man') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('messages.add_new_deliveryman') }}
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/edit/*') || Request::is('admin/delivery-man/list') || Request::is('admin/delivery-man/preview/*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.delivery-man.list') }}"
                                        title="{{ translate('messages.deliveryman_list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('messages.deliveryman_list') }}
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/reviews/list') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.delivery-man.reviews.list') }}"
                                        title="{{ translate('messages.reviews') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('messages.Deliveryman_Reviews') }}
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/bonus') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.delivery-man.bonus') }}"
                                        title="{{ translate('messages.bonus') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.bonus') }}</span>
                                    </a>
                                </li>


                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/incentive') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.delivery-man.incentive') }}"
                                        title=" {{ translate('messages.incentive_Requests') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('messages.incentive_Requests') }}
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/incentive-history') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.delivery-man.incentive-history') }}"
                                        title="{{ translate('messages.incentives_history') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.incentives_history') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    {{-- Surge price carries its own permission, so this section renders for a
                         role holding either one and each entry is gated on its own. --}}
                    @php($canZone = Helpers::module_permission_check('zone'))
                    @php($canSurgePrice = Helpers::module_permission_check('surge_price'))

                    @if ($canZone || $canSurgePrice)
                        @if ($canZone)
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/area*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.area.list') }}"
                                title="{{ translate('messages.Area_Setup') }}">
                                <i class="tio-map-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Area_Setup') }}</span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/zip-code*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.zip-code.list') }}"
                                title="{{ translate('messages.Zip_Code_Setup') }}">
                                <i class="tio-label-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Zip_Code_Setup') }}</span>
                            </a>
                        </li>
                        @endif

                        {{-- Collapsible parent: the toggle only opens the submenu,
                             so the group expands whenever one of its children is
                             the current page. --}}
                        @php($deliveryChargeGroupOpen = Request::is('admin/surge-price*') || Request::is('admin/delivery-rule*') || Request::is('admin/additional-charge*') || Request::is('admin/free-delivery*'))
                        <li class="navbar-vertical-aside-has-menu {{ $deliveryChargeGroupOpen ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.Delivery_Charge_Setup') }}">
                                <i class="tio-dollar-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.Delivery_Charge_Setup') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ $deliveryChargeGroupOpen ? 'block' : 'none' }}">
                                @if ($canZone)
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-rule*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.delivery-rule.list') }}"
                                        title="{{ translate('messages.Rules_Setup') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('messages.Rules_Setup') }}
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/additional-charge*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.additional-charge.list') }}"
                                        title="{{ translate('messages.Additional_Charge_Setup') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('messages.Additional_Charge_Setup') }}
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/free-delivery*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.free-delivery.list') }}"
                                        title="{{ translate('messages.Free_Delivery') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('messages.Free_Delivery') }}
                                        </span>
                                    </a>
                                </li>
                                @endif
                                @if ($canSurgePrice)
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/surge-price*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.surge-price.list') }}"
                                        title="{{ translate('messages.surge_price') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                            {{ translate('messages.surge_price') }}
                                        </span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>

                        {{-- ETA is a timing rather than a charge, so it sits beside the
                             charge group rather than inside it. --}}
                        @if ($canZone)
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/eta-configuration*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.eta-configuration.list') }}"
                                title="{{ translate('messages.ETA_Configuration') }}">
                                <i class="tio-time nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.ETA_Configuration') }}</span>
                            </a>
                        </li>
                        @endif
                    @endif
                    <!-- End Delivery Management -->







                    @if (Helpers::module_permission_check('disbursement'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.business_section') }}">{{ translate('messages.disbursement_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <!-- disbursement -->
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/restaurant-disbursement*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.restaurant-disbursement.list', ['status' => 'all']) }}"
                                title="{{ translate('messages.restaurant_disbursement') }}">
                                <i class="tio-wallet-outlined nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Restaurant_Disbursement') }}
                                    <span class="badge badge-soft-info badge-pill ml-1">
                                        {{ \App\Models\Disbursement::where('created_for', 'restaurant')->count() }}
                                    </span></span>

                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/dm-disbursement*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.dm-disbursement.list', ['status' => 'all']) }}"
                                title="{{ translate('messages.dm_disbursement') }}">
                                <i class="tio-saving-outlined nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Deliveryman_Disbursement') }}
                                    <span class="badge badge-soft-info badge-pill ml-1">
                                        {{ \App\Models\Disbursement::where('created_for', 'delivery_man')->count() }}
                                    </span></span>
                            </a>
                        </li>
                    @endif



                    <!-- Report -->
                    @if (Helpers::module_permission_check('report'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.report_and_analytics') }}">{{ translate('messages.report_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/transaction-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.day-wise-report') }}"
                                title="{{ translate('messages.transaction_report') }}">
                                <span class="tio-chart-pie-1 nav-icon"></span>
                                <span class="text-truncate">{{ translate('messages.transaction_report') }}</span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/expense-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.expense-report') }}"
                                title="{{ translate('messages.expense_report') }}">
                                <span class="tio-image  nav-icon"></span>
                                <span class="text-truncate">{{ translate('messages.expense_report') }}</span>
                            </a>
                        </li>


                        <li
                            class="navbar-vertical-aside-has-menu   {{ Request::is('admin/report/disbursement-report/restaurant') || Request::is('admin/report/disbursement-report/delivery_man') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.disbursement_report') }}">
                                <span class="tio-saving nav-icon"></span>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.disbursement_report') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/report/disbursement-report/restaurant') || Request::is('admin/report/disbursement-report/delivery_man') ? 'block' : 'none' }}">

                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/disbursement-report/restaurant') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.report.disbursement_report', ['tab' => 'restaurant']) }}"
                                        title="{{ translate('messages.restaurants') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.restaurants') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/disbursement-report/delivery_man') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.report.disbursement_report', ['tab' => 'delivery_man']) }}"
                                        title="{{ translate('messages.delivery_men') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.delivery_men') }}</span>
                                    </a>
                                </li>

                            </ul>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/food-wise-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.food-wise-report') }}"
                                title="{{ translate('messages.food_report') }}">
                                <span class="tio-fastfood nav-icon"></span>
                                <span class="text-truncate">{{ translate('messages.food_report') }}</span>
                            </a>
                        </li>




                        <li
                            class="navbar-vertical-aside-has-menu  {{ Request::is('admin/report/order-report') || Request::is('admin/report/campaign-order-report') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.Order_Report') }}">
                                <i class="tio-user nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Order_Report') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/report/order-report') || Request::is('admin/report/campaign-order-report') ? 'block' : 'none' }}">
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/order-report') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.report.order-report') }}"
                                        title="{{ translate('messages.order_report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.Regular_order_report') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/campaign-order-report') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.report.campaign_order-report') }}"
                                        title="{{ translate('messages.Campaign_Order_Report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.Campaign_Order_Report') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <li
                            class="navbar-vertical-aside-has-menu   {{ Request::is('admin/report/subscription-report') || Request::is('admin/report/restaurant-report') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.restaurant_report') }}">
                                <span class="tio-files nav-icon"></span>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.restaurant_report') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/report/subscription-report') || Request::is('admin/report/restaurant-report') ? 'block' : 'none' }}">

                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/restaurant-report') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.report.restaurant-report') }}"
                                        title="{{ translate('messages.restaurant_report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate">{{ translate('messages.restaurant_report') }}</span>
                                    </a>
                                </li>
                                @if (Helpers::subscription_check() == true)
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/subscription-report') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.report.subscription-report') }}"
                                            title="{{ translate('messages.Subscription_report') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="text-truncate">{{ translate('messages.Subscription_report') }}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu   {{ Request::is('admin/customer/wallet/report*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('messages.Customer_Report') }}">
                                <span class="tio-poi-user nav-icon"></span>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Customer_Report') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/customer/wallet/report*') || Request::is('admin/customer/overview/report*') ? 'block' : 'none' }}">

                                <li
                                    class="nav-item {{ Request::is('admin/customer/overview/report*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.overview.report') }}"
                                        title="{{ translate('messages.Customer_Overview_Report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.Customer_Overview_Report') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/customer/wallet/report*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.wallet.report') }}"
                                        title="{{ translate('messages.Customer_Wallet_Report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ translate('messages.Customer_Wallet_Report') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <li class="navbar-vertical-aside-has-menu @yield('tax_report')">
                            <a class="nav-link " href="{{ route('admin.report.getTaxReport') }}"
                                title="{{ translate('Tax_Report') }}">
                                <span class="tio-albums nav-icon"></span>
                                <span class="text-truncate text-capitalize">{{ translate('Tax_Report') }}</span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu @yield('vendor_tax_report')">
                            <a class="nav-link " href="{{ route('admin.report.vendorWiseTaxes') }}"
                                title="{{ translate('Restaurant_VAT_Report') }}">
                                <span class="tio-american-express nav-icon"></span>
                                <span
                                    class="text-truncate text-capitalize">{{ translate('Restaurant_VAT_Report') }}</span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu @yield('admin_earning_report')">
                            <a class="nav-link " href="{{ route('admin.report.admin-earning-report') }}"
                                title="{{ translate('Admin_Earning_Report') }}">
                                <span class="tio-account-circle nav-icon"></span>
                                <span
                                    class="text-truncate text-capitalize">{{ translate('Admin_Earning_Report') }}</span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu @yield('restaurant_earning_report') {{ Request::is('admin/report/restaurant-earning-report*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.restaurant-earning-report') }}"
                                title="{{ translate('Restaurant_Earning_Report') }}">
                                <span class="tio-align-to-bottom nav-icon"></span>
                                <span
                                    class="text-truncate text-capitalize">{{ translate('Restaurant_Earning_Report') }}</span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu @yield('deliveryman_earning_report') {{ Request::is('admin/report/deliveryman-earning-report*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.deliveryman-earning-report') }}"
                                title="{{ translate('Deliveryman_Earning_Report') }}">
                                <span class="tio-artboard nav-icon"></span>
                                <span
                                    class="text-truncate text-capitalize">{{ translate('Deliveryman_Earning_Report') }}</span>
                            </a>
                        </li>
                    @endif


                    @if (Helpers::module_permission_check('account') ||
                            Helpers::module_permission_check('withdraw_list') ||
                            Helpers::module_permission_check('provide_dm_earning'))
                        <!-- transaction_management -->

                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.business_section') }}">{{ translate('messages.transaction_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <!-- account -->
                    @endif

                    @if (Helpers::module_permission_check('account'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/account-transaction*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.account-transaction.index') }}"
                                title="{{ translate('messages.collect_cash') }}">
                                <i class="tio-money nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.collect_cash') }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End account -->
                    <!-- withdraw -->
                    @if (Helpers::module_permission_check('withdraw_list'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/restaurant/withdraw*') ||Request::is('admin/restaurant/withdraw-view*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.restaurant.withdraw_list') }}"
                                title="{{ translate('messages.restaurant_withdraws') }}">
                                <i class="tio-table nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.restaurant_withdraws') }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- provide_dm_earning -->
                    @if (Helpers::module_permission_check('provide_dm_earning'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/provide-deliveryman-earnings*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.provide-deliveryman-earnings.index') }}"
                                title="{{ translate('messages.DeliveryMan_Payments') }}">
                                <i class="tio-send nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.DeliveryMan_Payments') }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End provide_dm_earning -->

                    @if (Helpers::module_permission_check('withdraw_list'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/withdraw-method*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.business-settings.withdraw-method.list') }}"
                                title="{{ translate('messages.withdraw_method') }}">
                                <i class="tio-savings nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.withdraw_method') }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End withdraw -->
                    @if (Helpers::module_permission_check('custom_role') || Helpers::module_permission_check('employee'))
                        <!-- Employee-->
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.employee_handle') }}">{{ translate('messages.Employee_Management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if (Helpers::module_permission_check('custom_role'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/custom-role*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.custom-role.create') }}"
                                title="{{ translate('messages.employee_Role') }}">
                                <i class="tio-incognito nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.employee_Role') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Helpers::module_permission_check('employee'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/employee*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ translate('Employees') }}">
                                <i class="tio-user nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.employees') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/employee*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/employee/add-new') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.employee.add-new') }}"
                                        title="{{ translate('messages.add_new_Employee') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate">{{ translate('messages.Add_New_Employee') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/employee/list') || Request::is('admin/employee/update/*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.employee.list') }}"
                                        title="{{ translate('messages.Employee_list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.Employee_List') }}</span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endif
                    <!-- End Employee -->

                    <!-- Business Settings -->
                    @if (Helpers::module_permission_check('settings'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ translate('messages.business_settings') }}">{{ translate('messages.business_settings') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/business-setup*') ||
                            Request::is('admin/business-settings/refund/settings*') ||
                            Request::is('admin/business-settings/language*')
                                ? 'active'
                                : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.business-setup') }}"
                                title="{{ translate('messages.business_setup') }}">
                                <span class="tio-settings nav-icon"></span>
                                <span class="text-truncate">{{ translate('messages.business_setup') }}</span>
                            </a>
                        </li>
                        @if (addon_published_status('TaxModule'))
                            <li class="navbar-vertical-aside-has-menu @yield('taxModule')">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:">
                                    <i class="tio-wallet nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('System_Tax') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: @yield('taxModuleDisplay', 'none')">


                                    <li class="navbar-vertical-aside-has-menu @yield('tax_setup')">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('taxvat.index') }}" title="{{ translate('navbar') }}">
                                            <i class="tio-chart-line-up nav-icon"></i>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Create_Taxes') }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="navbar-vertical-aside-has-menu @yield('tax_system_setup')">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('taxvat.systemTaxvat') }}"
                                            title="{{ translate('Setup_Taxes') }}">
                                            <i class="tio-calculator nav-icon"></i>
                                            <span
                                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ translate('Setup_Taxes') }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        <!-- Subscription-->
                        @if (Helpers::subscription_check() == true && Helpers::module_permission_check('restaurant'))
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/subscription*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                    href="javascript:" title="{{ translate('messages.subscription') }}">
                                    <i class="tio-crown  nav-icon"></i>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.subscription_management') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/subscription*') ? 'block' : 'none' }}">
                                    <li
                                        class="nav-item @yield('subscription_index') {{ Request::is('admin/subscription/package/*') || Request::is('admin/subscription/search') || Request::is('admin/subscription/transcation/list/*') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.subscription.package_list') }}"
                                            title="{{ translate('messages.Package_list') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="text-truncate">{{ translate('messages.subscription_Packages') }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item @yield('subscriberList')">
                                        <a class="nav-link "
                                            href="{{ route('admin.subscription.subscription_list') }}"
                                            title="{{ translate('messages.Subscriber_list') }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate('Subscriber_list') }}</span>
                                        </a>
                                    </li>
                            </li>
                            <li class="nav-item {{ Request::is('admin/subscription/settings') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.subscription.settings') }}"
                                    title="{{ translate('messages.settings') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('messages.settings') }}</span>
                                </a>
                            </li>
                </ul>
                </li>
                @endif
                <!-- End Subscription -->

                <li
                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/email-setup*') ? 'active' : '' }}">
                    <a class="nav-link "
                        href="{{ route('admin.business-settings.email-setup', ['admin', 'forgot-password']) }}"
                        title="{{ translate('messages.email_template') }}">
                        <span class="tio-email nav-icon"></span>
                        <span class="text-truncate">{{ translate('messages.email_template') }}</span>
                    </a>
                </li>

                <li
                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/theme-settings*') ? 'active' : '' }}">
                    <a class="nav-link " href="{{ route('admin.business-settings.theme-settings') }}"
                        title="{{ translate('messages.theme_settings') }}">
                        <span class="tio-brush nav-icon"></span>
                        <span class="text-truncate">{{ translate('messages.theme_settings') }}</span>
                    </a>
                </li>

                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/file-manager*') ? 'active' : '' }}">
                    <a class="nav-link " href="{{ route('admin.file-manager.index') }}"
                        title="{{ translate('messages.gallery') }}">
                        <span class="tio-album nav-icon"></span>
                        <span class="text-truncate text-capitalize">{{ translate('messages.gallery') }}</span>
                    </a>
                </li>

                <li
                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/login-settings*') ? 'active' : '' }}">
                    <a class="nav-link " href="{{ route('admin.login-settings.index') }}"
                        title="{{ translate('messages.login_setup') }}">
                        <span class="tio-devices-apple nav-icon"></span>
                        <span class="text-truncate text-capitalize">{{ translate('messages.login_setup') }}</span>
                    </a>
                </li>

                <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/invoice-setup*') ? 'active' : '' }}">
                        <a class="nav-link " href="{{ route('admin.business-settings.invoice-setup') }}"
                            title="{{ translate('messages.invoice_setup') }}">
                            <span class="tio-receipt nav-icon"></span>
                            <span class="text-truncate">{{ translate('messages.invoice_setup') }}</span>
                        </a>
                </li>



                <!-- web & adpp Settings -->
                <li
                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/pages*') || Request::is('admin/business-settings/social-media') || Request::is('admin/business-settings/registration-page/react*') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                        title="{{ translate('messages.Pages_&_Social_Media') }}">
                        <i class="tio-pages nav-icon"></i>
                        <span
                            class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Pages_&_Social_Media') }}
                        </span>
                    </a>
                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                        style="display: {{ Request::is('admin/business-settings/pages*') || Request::is('admin/business-settings/social-media') || Request::is('admin/business-settings/registration-page/react*') ? 'block' : 'none' }}">


                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/social-media') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.social-media.index') }}"
                                title="{{ translate('messages.Social_Media') }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ translate('messages.Social_Media') }}</span>
                            </a>
                        </li>

                        <li
                            class="nav-item {{ Request::is('admin/business-settings/pages/terms-and-conditions') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.terms-and-conditions') }}"
                                title="{{ translate('messages.terms_and_condition') }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ translate('messages.terms_and_condition') }}</span>
                            </a>
                        </li>

                        <li
                            class="nav-item {{ Request::is('admin/business-settings/pages/privacy-policy') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.privacy-policy') }}"
                                title="{{ translate('messages.privacy_policy') }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ translate('messages.privacy_policy') }}</span>
                            </a>
                        </li>

                        <li
                            class="nav-item {{ Request::is('admin/business-settings/pages/about-us') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.about-us') }}"
                                title="{{ translate('messages.about_us') }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ translate('messages.about_us') }}</span>
                            </a>
                        </li>


                        <li
                            class="nav-item {{ Request::is('admin/business-settings/pages/refund-policy') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.refund-policy') }}"
                                title="{{ translate('messages.refund_policy') }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ translate('messages.refund_policy') }}</span>
                            </a>
                        </li>


                        <li
                            class="nav-item {{ Request::is('admin/business-settings/pages/shipping-policy') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.shipping-policy') }}"
                                title="{{ translate('messages.shipping_policy') }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ translate('messages.shipping_policy') }}</span>
                            </a>
                        </li>

                        <li
                            class="nav-item {{ Request::is('admin/business-settings/pages/cancellation-policy') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.cancellation-policy') }}"
                                title="{{ translate('messages.cancellation_policy') }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ translate('messages.cancellation_policy') }}</span>
                            </a>
                        </li>

                        <li
                            class="nav-item {{ Request::is('admin/business-settings/registration-page/react*') ? 'active' : '' }}">
                            <a class="nav-link "
                                href="{{ route('admin.business-settings.react-registration-page.hero') }}"
                                title="{{ translate('messages.react_registration') }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ translate('messages.react_registration') }}</span>
                            </a>
                        </li>


                    </ul>
                </li>
                @endif


                @if (Helpers::module_permission_check('system_settings'))


                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ translate('messages.system_settings') }}">{{ translate('messages.system_settings') }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <li
                        class="navbar-vertical-aside-has-menu   {{ Request::is('admin/business-settings/fcm-*') || Request::is('admin/business-settings/payment-method') || Request::is('admin/business-settings/sms-module') || Request::is('admin/business-settings/mail-config') || Request::is('admin/social-login/view') || Request::is('admin/business-settings/offline*') || Request::is('admin/business-settings/config*') || Request::is('admin/business-settings/recaptcha*') || Request::is('admin/business-settings/*') ? 'active' : '' }} @yield('3rd_party')">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            title="{{ translate('3rd_Party_&_Configurations') }}">
                            <span class="tio-plugin nav-icon"></span>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.3rd_Party_&_Configurations') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{ Request::is('admin/business-settings/deliveryman/join-us/*') || Request::is('admin/business-settings/restaurant/join-us/*') || Request::is('admin/business-settings/fcm-*') || Request::is('admin/business-settings/payment-method') || Request::is('admin/business-settings/sms-module') || Request::is('admin/business-settings/mail-config') || Request::is('admin/social-login/view') || Request::is('admin/business-settings/config*') || Request::is('admin/business-settings/recaptcha*') || Request::is('admin/business-settings/offline*') || Request::is('admin/business-settings/marketing/*') || Request::is('admin/business-settings/open-ai') || Request::is('admin/business-settings/open-ai-settings') || Request::is('admin/business-settings/firebase-otp*') || Request::is('admin/business-settings/storage-connection*') || Request::is('admin/business-settings/integration*') ? 'block' : 'none' }}  ">


                            <li
                                class="nav-item @yield('firebase_otp') @yield('storage') {{ Request::is('admin/business-settings/notification-setup*') || Request::is('admin/business-settings/payment-method') || Request::is('admin/business-settings/sms-module') || Request::is('admin/business-settings/mail-config') || Request::is('admin/social-login/view') || Request::is('admin/business-settings/config*') || Request::is('admin/business-settings/recaptcha*') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.business-settings.payment-method') }}"
                                    title="{{ translate('messages.3rd_Party') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('messages.3rd_Party') }}
                                    </span>
                                </a>
                            </li>

                            <li class="nav-item  {{ Request::is('admin/business-settings/fcm-*') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.business-settings.fcm-index') }}"
                                    title="{{ translate('messages.Firebase_Notification') }} ">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('messages.Firebase_Notification') }}
                                    </span>
                                </a>
                            </li>
                            @if (Helpers::get_mail_status('offline_payment_status'))
                                <li
                                    class="nav-item {{ Request::is('admin/business-settings/offline*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.business-settings.offline') }}"
                                        title="{{ translate('messages.Offline_Payment_Setup') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate">{{ translate('messages.Offline_Payment_Setup') }}</span>
                                    </a>
                                </li>
                            @endif

                            <li class="nav-item @yield('reg_page')">
                                <a class="nav-link "
                                    href="{{ route('admin.business-settings.restaurant_page_setup') }}"
                                    title="{{ translate('messages.Join_us_page_setup') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('messages.Join_us_page_setup') }}</span>
                                </a>
                            </li>
                            <li class="nav-item @yield('analytics_Script')">
                                <a class="nav-link "
                                    href="{{ route('admin.business-settings.marketing.analytic') }}"
                                    title="{{ translate('Analytics_Script') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Analytics_Script') }}</span>
                                </a>
                            </li>

                            <li class="nav-item @yield('openAI')">
                                <a class="nav-link " href="{{ route('admin.business-settings.openAI') }}"
                                    title="{{ translate('AI_Setup') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('AI_Setup') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/app-settings*') ? 'active' : '' }}">
                        <a class="nav-link " href="{{ route('admin.business-settings.app-settings') }}"
                            title="{{ translate('messages.App_&_Web_Settings') }}">
                            <span class="tio-android nav-icon"></span>
                            <span class="text-truncate">{{ translate('messages.App_&_Web_Settings') }}</span>
                        </a>
                    </li>

                    <li class="navbar-vertical-aside-has-menu  @yield('notification_setup')">
                        <a class="nav-link " href="{{ route('admin.business-settings.notification_setup') }}"
                            title="{{ translate('messages.Notification_Channels') }} ">
                            <span class="tio-snooze-notification  nav-icon"></span>
                            <span class="text-truncate">{{ translate('messages.Notification_Channels') }}
                            </span>
                        </a>
                    </li>


                    <li class="navbar-vertical-aside-has-menu  @yield('notification_message')">
                        <a class="nav-link " href="{{ route('admin.business-settings.notificationMessages') }}"
                            title="{{ translate('messages.Notification_Messages') }} ">
                            <span class="tio-notifications-on-outlined  nav-icon"></span>
                            <span class="text-truncate">{{ translate('messages.Notification_Messages') }}
                            </span>
                        </a>
                    </li>

                    <li
                        class="navbar-vertical-aside-has-menu   {{ Request::is('admin/react-landing-page*') || Request::is('admin/landing-page*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            title="{{ translate('landing_page_settings') }}">
                            <span class="tio-files nav-icon"></span>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.landing_page_settings') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{ Request::is('admin/react-landing-page*') || Request::is('admin/landing-page*') ? 'block' : 'none' }}">

                            <li class="nav-item {{ Request::is('admin/landing-page*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.landing_page.setup') }}"
                                    title="{{ translate('messages.Admin_landing_page') }} ">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Admin_landing_page') }}</span>
                                </a>
                            </li>

                            <li class="nav-item {{ Request::is('admin/react-landing-page*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.react_landing_page.react_header') }}"
                                    title="{{ translate('messages.React_landing_page') }} ">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span
                                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.React_landing_page') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/page-meta-data*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.pageMetaData') }}" title="{{ translate('Page_Meta_data') }}">
                            <i class="tio-share-message nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Page_Meta_data') }}
                            </span>
                        </a>
                    </li>


                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/db-index') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.business-settings.db-index') }}"
                            title="{{ translate('messages.clean_database') }}">
                            <i class="tio-cloud nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('messages.clean_database') }}
                            </span>
                        </a>
                    </li>
                @endif
                <!-- End web & adpp Settings -->

                @if (Helpers::module_permission_check('system_addon'))
                    <li class="nav-item">
                        <small class="nav-subtitle">{{ translate('messages.system_addons') }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>
                    <!-- system_addons -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/system-addon') ? 'show active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.business-settings.system-addon.index') }}"
                            title="{{ translate('messages.system_addons') }}">
                            <i class="tio-add-circle-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('messages.system_addons') }}
                            </span>
                        </a>
                    </li>
                    <!-- End system_addons -->
                @endif

                @if (count(config('addon_admin_routes')) > 0 && Helpers::module_permission_check('system_addon'))
                    <li class="nav-item">
                        <small class="nav-subtitle">{{ translate('messages.addon_menus') }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/payment/configuration/*') || Request::is('admin/sms/configuration/*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                            <i class="tio-puzzle nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Addon_Menus') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{ Request::is('admin/payment/configuration/*') || Request::is('admin/sms/configuration/*') ? 'block' : 'none' }}">
                            @foreach (config('addon_admin_routes') as $routes)
                                @foreach ($routes as $route)
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is($route['path']) ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link "
                                            href="{{ $route['url'] }}" title="{{ translate($route['name']) }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ translate($route['name']) }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            @endforeach
                        </ul>
                    </li>
                @endif
                <!-- End Business Settings -->





                <!--addon end-->

                <li class="nav-item pt-100px">

                </li>
                </ul>
            </div>
            <!-- End Content -->
        </div>
    </aside>
</div>

<div id="sidebarCompact" class="d-none">

</div>


@push('script_2')
    <script>
        "use strict";
        $(window).on('load', function() {
            if ($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 300);
            }
        });

        var $navItems = $('#navbar-vertical-content > ul > li');
        $('#search').keyup(function() {
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
            $navItems.show().filter(function() {
                var $listItem = $(this);
                var text = $listItem.text().replace(/\s+/g, ' ').toLowerCase();
                var $list = $listItem.closest('li');

                return !~text.indexOf(val) && !$list.text().toLowerCase().includes(val);
            }).hide();
        });
    </script>
@endpush
