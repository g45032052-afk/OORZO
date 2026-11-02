<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                <div class="sidebar-logo-container">
                    @php($restaurant_data=\App\CentralLogics\Helpers::get_restaurant_data())
                    <a class="navbar-brand pt-0 pb-0" href="{{route('vendor.dashboard')}}" aria-label="Front">
                        <img class="navbar-brand-logo"
                             src="{{ $restaurant_data?->logo_full_url }}"
                             alt="image">
                        <img class="navbar-brand-logo-mini"
                             src="{{ $restaurant_data?->logo_full_url }}"
                             alt="image">

                        <div class="ps-2">
                            <h6>
                                {{\Illuminate\Support\Str::limit($restaurant_data->name,15)}}
                            </h6>
                        </div>
                    </a>
                    <!-- End Logo -->

                    <!-- Navbar Vertical Toggle -->
                    <button type="button"
                            class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                    <!-- End Navbar Vertical Toggle -->
                </div>
                <div class="navbar-nav-wrap-content-left ml-auto d-none d-xl-block">
                    <!-- Navbar Vertical Toggle -->
                    <button type="button" class="js-navbar-vertical-aside-toggle-invoker close hide-default-close">
                        <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                           data-placement="right" title="Collapse"></i>
                        <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                           data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'></i>
                    </button>
                    <!-- End Navbar Vertical Toggle -->
                </div>

            </div>

            <!-- Content -->
            <div class="navbar-vertical-content text-capitalize bg-334257">
                <ul id="default-nav" class="navbar-nav navbar-nav-lg nav-tabs mt-3">
                    <!-- Dashboards -->

                    <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel')?'active':''}}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                           href="{{route('vendor.dashboard')}}" title="{{translate('messages.dashboard')}}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{translate('messages.dashboard')}}
                            </span>
                        </a>
                    </li>

                    <!-- End Dashboards -->

                    <!-- POS -->

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('pos'))
                        <!-- POS -->
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/pos')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{route('vendor.pos.index')}}" title="{{translate('Point Of Sale')}}"
                            >
                                <i class="tio-shopping nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('Point Of Sale')}}</span>
                            </a>
                        </li>
                        <!-- End POS -->
                    @endif

                    <!-- End POS -->

                    <!--Order Management -->

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('regular_order') || \App\CentralLogics\Helpers::employee_module_permission_check('subscription_order'))
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{translate('messages.order_section')}}">{{translate('messages.order_management')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('regular_order'))
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/order*') && (Request::is('restaurant-panel/order/subscription*') == false ) ?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                               title="{{translate('messages.regular_orders')}}">
                                <i class="tio-shopping-cart nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('messages.regular_orders')}}
                                </span>
                            </a>
                            @php($data =0)
                            @php($restaurant =\App\CentralLogics\Helpers::get_restaurant_data())
                            @if (($restaurant->restaurant_model == 'subscription' && isset($restaurant->restaurant_sub) && $restaurant->restaurant_sub->self_delivery == 1)  || ($restaurant->restaurant_model == 'commission' && $restaurant->self_delivery_system == 1) )
                                @php($data =1)
                            @endif
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:  {{Request::is('restaurant-panel/order*') && (Request::is('restaurant-panel/order/subscription*') == false )?'block':'none'}}">
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/all')?'active':''}} @yield('all_order') ">
                                    <a class="nav-link" href="{{route('vendor.order.list',['all'])}}" title="{{translate('messages.all_order')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.all')}}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{\App\Models\Order::where('restaurant_id', \App\CentralLogics\Helpers::get_restaurant_id())
                                                    ->where(function($query) use($data){
                                                        return $query->whereNotIn('order_status',(config('order_confirmation_model') == 'restaurant'|| $data)?['failed','canceled', 'refund_requested', 'refunded']:['pending','failed','canceled', 'refund_requested', 'refunded'])
                                                        ->orWhere(function($query){
                                                            return $query->where('order_status','pending')->whereIn('order_type', ['take_away','dine_in']);
                                                        });
                                                })->Notpos()->HasSubscriptionToday()->NotDigitalOrder()
                                                ->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/pending')?'active':'' }} @yield('pending')">
                                    <a class="nav-link " href="{{route('vendor.order.list',['pending'])}}" title="{{translate('messages.pending')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.pending')}}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                @if(config('order_confirmation_model') == 'restaurant' || $data)
                                                        {{\App\Models\Order::where(['order_status'=>'pending','restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->Notpos()->NotDigitalOrder()->HasSubscriptionToday()->OrderScheduledIn(30)->count()}}
                                                    @else
                                                        {{\App\Models\Order::where(['order_status'=>'pending','restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->whereIn('order_type',['take_away','dine_in'])->NotDigitalOrder()->Notpos()->HasSubscriptionToday()->OrderScheduledIn(30)->count()}}
                                                    @endif
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                <li class="nav-item {{Request::is('restaurant-panel/order/list/confirmed')?'active':''}} @yield('confirmed') ">
                                    <a class="nav-link " href="{{route('vendor.order.list',['confirmed'])}}" title="{{translate('messages.confirmed')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.confirmed')}}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                {{\App\Models\Order::whereIn('order_status',['confirmed',])->NotDigitalOrder()->Notpos()->whereNotNull('confirmed')->where('restaurant_id', \App\CentralLogics\Helpers::get_restaurant_id())->HasSubscriptionToday()->OrderScheduledIn(30)->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/accepted')?'active':''}} @yield('accepted')">
                                    <a class="nav-link " href="{{route('vendor.order.list',['accepted'])}}"  title="{{translate('accepted')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.accepted')}}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                {{\App\Models\Order::whereIn('order_status',['accepted'])->NotDigitalOrder()->hasSubscriptionToday()->where(['restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->Notpos()->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                <li class="nav-item {{Request::is('restaurant-panel/order/list/cooking')?'active':''}} @yield('processing')">
                                    <a class="nav-link" href="{{route('vendor.order.list',['cooking'])}}" title="{{translate('messages.cooking')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.cooking')}}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{\App\Models\Order::where(['order_status'=>'processing', 'restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/ready_for_delivery')?'active':''}} @yield('handover')">
                                    <a class="nav-link" href="{{route('vendor.order.list',['ready_for_delivery'])}}" title="{{translate('Ready For Delivery')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.ready_for_delivery')}}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{\App\Models\Order::where(['order_status'=>'handover', 'restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/food_on_the_way')?'active':''}} @yield('picked_up')">
                                    <a class="nav-link" href="{{route('vendor.order.list',['food_on_the_way'])}}" title="{{translate('Food On The Way')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.food_on_the_way')}}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{\App\Models\Order::where(['order_status'=>'picked_up', 'restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/delivered')?'active':''}} @yield('delivered')">
                                    <a class="nav-link " href="{{route('vendor.order.list',['delivered'])}}"  title="{{translate('Delivered')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.delivered')}}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                {{\App\Models\Order::where(['order_status'=>'delivered','restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/dine_in')?'active':''}} @yield('dine_in')">
                                    <a class="nav-link " href="{{route('vendor.order.list',['dine_in'])}}"  title="{{translate('dine_in')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.dine_in')}}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                {{\App\Models\Order::where(['order_type'=>'dine_in','restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/refunded')?'active':''}} @yield('refunded')">
                                    <a class="nav-link " href="{{route('vendor.order.list',['refunded'])}}"  title="{{translate('Refunded')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.refunded')}}
                                                <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                                {{\App\Models\Order::Refunded()->where(['restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/refund_requested')?'active':''}} @yield('refund_requested')">
                                    <a class="nav-link " href="{{route('vendor.order.list',['refund_requested'])}}"  title="{{translate('refund_requested')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.refund_requested')}}
                                                <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                                {{\App\Models\Order::Refund_requested()->NotDigitalOrder()->where(['restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->Notpos()->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/scheduled')?'active':''}} @yield('scheduled')">
                                    <a class="nav-link" href="{{route('vendor.order.list',['scheduled'])}}" title="{{translate('messages.scheduled')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.scheduled')}}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{\App\Models\Order::where('restaurant_id',\App\CentralLogics\Helpers::get_restaurant_id())->NotDigitalOrder()->Notpos()->HasSubscriptionToday()->Scheduled()->where(function($q) use($data){
                                                    if(config('order_confirmation_model') == 'restaurant' || $data)
                                                    {
                                                        $q->whereNotIn('order_status',['failed','canceled', 'refund_requested', 'refunded']);
                                                    }
                                                    else
                                                    {
                                                        $q->whereNotIn('order_status',['pending','failed','canceled', 'refund_requested', 'refunded'])->orWhere(function($query){
                                                            $query->where('order_status','pending')->whereIn('order_type', ['take_away','dine_in']);
                                                        });
                                                    }

                                                })->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>


                                <li class="nav-item {{Request::is('restaurant-panel/order/list/payment_failed')?'active':''}} @yield('failed')">
                                    <a class="nav-link " href="{{route('vendor.order.list',['payment_failed'])}}"  title="{{translate('payment_failed')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.payment_failed')}}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                {{\App\Models\Order::where('order_status','failed')->NotDigitalOrder()->where(['restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->Notpos()->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/order/list/canceled')?'active':''}} @yield('canceled')">
                                    <a class="nav-link " href="{{route('vendor.order.list',['canceled'])}}"  title="{{translate('canceled')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{translate('messages.canceled')}}
                                                <span class="badge badge-soft-success badge-pill ml-1">
                                                {{\App\Models\Order::where('order_status','canceled')->NotDigitalOrder()->where(['restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->Notpos()->count()}}
                                            </span>
                                        </span>
                                    </a>
                                </li>



                            </ul>
                        </li>

                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('subscription_order'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/order/subscription*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('vendor.order.subscription.index') }}" title="{{ translate('messages.subscription_orders') }}">
                                <i class="tio-appointment nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('messages.subscription_orders') }}</span>
                            </a>
                        </li>
                    @endif

                    <!-- End Order Management -->

                    <!-- Food Management -->

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('food') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('category') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('addon') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('reviews'))
                        <li class="nav-item">
                            <small class="nav-subtitle">{{translate('messages.food_management')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('food'))
                        <!-- Food -->
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/food*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{translate('Food')}}"
                            >
                                <i class="tio-premium-outlined nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('messages.foods')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{Request::is('restaurant-panel/food*')?'block':'none'}}">
                                <li class="nav-item {{Request::is('restaurant-panel/food/add-new')?'active':''}}">
                                    <a class="nav-link " href="{{route('vendor.food.add-new')}}"
                                       title="{{translate('Add New Food')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate">{{translate('messages.add_new')}}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/food/list')?'active':''}}">
                                    <a class="nav-link " href="{{route('vendor.food.list')}}"  title="{{translate('Food List')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('messages.list')}}</span>
                                    </a>
                                </li>
                                @if(\App\CentralLogics\Helpers::get_restaurant_data()->food_section)
                                    <li class="nav-item {{Request::is('restaurant-panel/food/bulk-import')?'active':''}}">
                                        <a class="nav-link " href="{{route('vendor.food.bulk-import')}}"
                                           title="{{translate('Bulk Import')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">{{translate('messages.bulk_import')}}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{Request::is('restaurant-panel/food/bulk-export')?'active':''}}">
                                        <a class="nav-link " href="{{route('vendor.food.bulk-export-index')}}"
                                           title="{{translate('Bulk Export')}}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">{{translate('messages.bulk_export')}}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        <!-- End Food -->
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('category'))
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/category*') ? 'active' : ''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                               href="javascript:" title="{{translate('Main Categories')}}"
                            >
                                <i class="tio-category nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('Main Categories')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{Request::is('restaurant-panel/category*')  ? 'block':'none'}}">
                                <li class="nav-item {{Request::is('restaurant-panel/category/list')?'active':''}}">
                                    <a class="nav-link " href="{{route('vendor.category.add')}}"
                                       title="{{translate('Main Categories')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('Main Categories')}}</span>
                                    </a>
                                </li>


                                <li class="nav-item {{Request::is('restaurant-panel/category/sub-category-list')?'active':''}}">
                                    <a class="nav-link " href="{{route('vendor.category.add-sub-category')}}"
                                       title="{{translate('Main Sub categories')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('Main Sub categories')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                            @if(\App\CentralLogics\Helpers::restaurantCategoryStatus())
                                <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/restaurant-category*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('vendor.restaurant-category.index') }}" title="{{ translate('My Category') }}">
                                        <i class="tio-print nav-icon"></i>
                                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('My Category') }}</span>
                                    </a>
                                </li>

                            @endif

                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('addon'))
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/addon*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('vendor.addon.add-new')}}" title="{{translate('messages.addons')}}">
                                <i class="tio-add-circle-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{translate('messages.addons')}}
                            </span>
                            </a>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('reviews'))
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/reviews')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('vendor.reviews')}}" title="{{translate('messages.reviews')}}">
                                <i class="tio-star-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{translate('messages.reviews')}}
                            </span>
                            </a>
                        </li>
                    @endif


                    <!-- End Food Management -->

                    <!-- Promotion Management -->

                    @if (\App\CentralLogics\Helpers::employee_module_permission_check('campaign') || \App\CentralLogics\Helpers::employee_module_permission_check('coupon'))
                        <li class="nav-item">
                            <small class="nav-subtitle">{{translate('Promotions Management')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('campaign'))
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/campaign*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                               title="{{translate('Campaign')}}">
                                <i class="tio-image nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('Campaign')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{Request::is('restaurant-panel/campaign*') ? 'block' : 'none' }}">
                                <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/campaign/list') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('vendor.campaign.list') }}" title="{{ translate('messages.basic_campaign') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate text-capitalize">{{ translate('messages.basic_campaign') }}</span>
                                    </a>
                                </li>
                                <li class="navbar-vertical-aside-has-menu @yield('campaign_view') {{ Request::is('restaurant-panel/campaign/item/list') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('vendor.campaign.itemlist') }}" title="{{ translate('messages.food_campaign') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate text-capitalize">{{ translate('messages.food_campaign') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('bogo_offer'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/bogo-offer*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{ route('vendor.bogo-offer.list') }}"
                               title="{{ translate('messages.BOGO_Offer') }}">
                                <i class="tio-gift nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.BOGO_Offer') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('happy_hour'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/happy-hour*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{ route('vendor.happy-hour.list') }}"
                               title="{{ translate('messages.Happy_Hour') }}">
                                <i class="tio-clock nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Happy_Hour') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::employee_module_permission_check('coupon'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/coupon*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{ route('vendor.coupon.add-new') }}"
                               title="{{ translate('messages.coupons') }}">
                                <i class="tio-ticket nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.coupons') }}</span>
                            </a>
                        </li>
                    @endif

                    <!-- End Promotion Management -->

                    <!-- Help & Support -->

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('chat'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                   title="{{translate('messages.help_&_support')}}">{{translate('messages.help_&_support')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('chat'))
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/message*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('vendor.message.list', ['tab' => 'customer'])}}" title="{{translate('messages.chat')}}">
                                <i class="tio-chat nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{translate('messages.chat')}}
                            </span>
                            </a>
                        </li>
                    @endif

                    <!-- End Help & Support -->

                    <!-- Ads Management -->

                    @if (\App\CentralLogics\Helpers::employee_module_permission_check('new_ads') || \App\CentralLogics\Helpers::employee_module_permission_check('ads_list'))
                        <li class="nav-item">
                            <small class="nav-subtitle">{{translate('Ads Management')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::employee_module_permission_check('new_ads'))
                        <li class="navbar-vertical-aside-has-menu @yield('advertisement_create')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{ route('vendor.advertisement.create') }}"
                               title="{{ translate('messages.New_Ads') }}">
                                <i class="tio-tv-old nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.New_Ads') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::employee_module_permission_check('ads_list'))
                        <li class="navbar-vertical-aside-has-menu @yield('advertisement')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                               href="javascript:" title="{{translate('messages.Ads_List')}}">
                                <i class="tio-format-bullets nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('messages.Ads_List')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ !Request::is('restaurant-panel/advertisement/create*') && Request::is('restaurant-panel/advertisement*')?'block':'none'}}">
                                <li class="nav-item @yield('advertisement_pending_list')">
                                    <a class="nav-link " href="{{route('vendor.advertisement.index',['type'=> 'pending'])}}"
                                       title="{{translate('messages.Pending')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('messages.Pending')}}</span>
                                    </a>
                                </li>

                                <li class="nav-item @yield('advertisement_list')">
                                    <a class="nav-link " href="{{route('vendor.advertisement.index')}}"
                                       title="{{translate('messages.Ad_List')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('messages.List')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    <!-- End Ads Management -->


                @if (addon_published_status('ReelsModule') && \App\CentralLogics\Helpers::get_business_settings('vendor_can_upload_reels') && \App\CentralLogics\Helpers::employee_module_permission_check('reels') )
                        <li class="nav-item">
                            <small class="nav-subtitle">{{ translate('messages.Reels_Management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu @yield('vendor_reels_create')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('vendor.reels.create') }}"
                                title="{{ translate('messages.Create_Reels') }}">
                                <i class="tio-video-camera-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Create_Reels') }}</span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu @yield('vendor_reels')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('vendor.reels.index') }}"
                                title="{{ translate('messages.Reels_List') }}">
                                <i class="tio-format-bullets nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Reels_List') }}</span>
                            </a>
                        </li>
                 @endif
                    <!-- Wallet Management -->

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('wallet_method') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('my_wallet'))
                        <!-- Business Section-->
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                   title="{{translate('messages.wallet_management')}}">{{translate('messages.wallet_management')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('my_wallet'))
                        <!-- RestaurantWallet -->
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/wallet*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{route('vendor.wallet.index')}}" title="{{translate('messages.my_wallet')}}">
                                <i class="tio-table nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('messages.my_wallet')}}</span>
                            </a>
                        </li>
                    @endif

                    <!-- End Wallet Management -->

                    <!-- Deliveryman Management-->

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('deliveryman'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                   title="{{translate('messages.deliveryman_section')}}">{{translate('messages.deliveryman_management')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/delivery-man/add')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('vendor.delivery-man.add')}}"
                               title="{{translate('messages.add_delivery_man')}}">
                                <i class="tio-running nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('messages.add_delivery_man')}}
                                </span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/delivery-man/list') || Request::is('restaurant-panel/delivery-man/edit/*') || Request::is('restaurant-panel/delivery-man/preview/*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('vendor.delivery-man.list')}}"
                               title="{{translate('messages.deliveryman_list')}}">
                                <i class="tio-filter-list nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('messages.deliverymen_list')}}
                                </span>
                            </a>
                        </li>

                        {{--<li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/delivery-man/reviews/list')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('vendor.delivery-man.reviews.list')}}" title="{{translate('messages.reviews')}}"
                            >
                                <i class="tio-star-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('messages.reviews')}}
                                </span>
                            </a>
                        </li>--}}
                    @endif

                    <!-- End Deliveryman Management -->

                    <!-- Reports -->

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('expense_report') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('transaction') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('disbursement') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('order_report') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('food_report'))
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{translate('messages.Reports')}}">{{translate('messages.Reports')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('expense_report'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/report/expense-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('vendor.report.expense-report') }}" title="{{ translate('messages.expense_report') }}">
                                <span class="tio-money nav-icon"></span>
                                <span class="text-truncate">{{ translate('messages.expense_report') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('transaction'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/report/transaction-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('vendor.report.day-wise-report') }}"
                               title="{{ translate('messages.transaction_report') }}">
                                <span class="tio-chart-pie-1 nav-icon"></span>
                                <span class="text-truncate">{{ translate('messages.transaction_report') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('disbursement'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/report/disbursement-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('vendor.report.disbursement-report') }}"
                               title="{{ translate('messages.disbursement_report') }}">
                                <span class="tio-saving nav-icon"></span>
                                <span class="text-truncate">{{ translate('messages.disbursement_report') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('order_report'))
                        <li class="navbar-vertical-aside-has-menu  {{Request::is('restaurant-panel/report/order-report') || Request::is('restaurant-panel/report/campaign-order-report') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                               title="{{ translate('messages.Order_Report') }}">
                                <i class="tio-user nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.Order_Report') }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{Request::is('restaurant-panel/report/order-report') || Request::is('restaurant-panel/report/campaign-order-report') ? 'block' : 'none' }}">
                                <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/report/order-report') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('vendor.report.order-report') }}" title="{{ translate('messages.order_report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate text-capitalize">{{ translate('messages.Regular_order_report') }}</span>
                                    </a>
                                </li>
                                <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/report/campaign-order-report') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('vendor.report.campaign_order-report') }}" title="{{ translate('messages.Campaign_Order_Report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate text-capitalize">{{ translate('messages.Campaign_Order_Report') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('food_report'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('restaurant-panel/report/food-wise-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('vendor.report.food-wise-report') }}"
                               title="{{ translate('messages.food_report') }}">
                                <span class="tio-fastfood nav-icon"></span>
                                <span class="text-truncate">{{ translate('messages.food_report') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('tax_report'))
                        <li class="navbar-vertical-aside-has-menu @yield('vendor_tax_report')">
                            <a class="nav-link " href="{{ route('vendor.report.vendorTax') }}"
                               title="{{ translate('messages.Tax_Report') }}">
                                <span class="tio-saving nav-icon"></span>
                                <span class="text-truncate">{{ translate('messages.Tax_Report') }}</span>
                            </a>
                        </li>
                    @endif
                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('earning_report'))
                        <li class="navbar-vertical-aside-has-menu @yield('restaurant_earning_report')">
                            <a class="nav-link " href="{{ route('vendor.report.restaurant-earning-report') }}"
                               title="{{ translate('messages.Restaurant_Earning_Report') }}">
                                <span class="tio-money nav-icon"></span>
                                <span class="text-truncate">{{ translate('Earning Report') }}</span>
                            </a>
                        </li>
                    @endif

                    <!-- End Reports -->

                    <!-- Business Management -->
                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('business_plan') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('my_qr_code') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('my_restaurant') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('notification_setup') ||
                        \App\CentralLogics\Helpers::employee_module_permission_check('restaurant_config'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                   title="{{translate('messages.business_section')}}">{{translate('messages.business_management')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    <?php
                        $restaurantActive = request()->is([
                            'restaurant-panel/restaurant/view',
                            'restaurant-panel/business-settings/restaurant-setup',
                            'restaurant-panel/withdraw-method',
                            'restaurant-panel/restaurant/qr-view',
                            'restaurant-panel/business-settings/notification-setup',
                        ]);
                    ?>

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('my_restaurant'))
                        <li class="navbar-vertical-aside-has-menu {{ $restaurantActive ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{ route('vendor.shop.view') }}"
                               title="{{ translate('My Restaurant') }}">
                                <i class="tio-home nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('restaurant_configuration') }}
                                </span>
                            </a>
                        </li>
                    @endif


                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('business_plan'))
                        <li class="navbar-vertical-aside-has-menu @yield('subscriberList')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('vendor.subscriptionackage.subscriberDetail')}}"
                               title="{{translate('messages.My_Business_Plan')}}">
                                <i class="tio-crown nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{translate('messages.My_Business_Plan')}}
                            </span>
                            </a>
                        </li>
                    @endif

                    <!-- End Business Management -->

                    <!-- Employee Management -->

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('role_management') || \App\CentralLogics\Helpers::employee_module_permission_check('all_employee'))
                        <!-- Employee-->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{translate('messages.employee_management')}}">{{translate('messages.employee_management')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('role_management'))
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/custom-role*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{route('vendor.custom-role.create')}}"
                               title="{{translate('messages.employee_Role')}}">
                                <i class="tio-incognito nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('messages.employee_Role')}}</span>
                            </a>
                        </li>
                    @endif

                    @if(\App\CentralLogics\Helpers::employee_module_permission_check('all_employee'))
                        <li class="navbar-vertical-aside-has-menu {{Request::is('restaurant-panel/employee*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                               title="{{translate('messages.employees')}}">
                                <i class="tio-user nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('messages.all_employee')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{Request::is('restaurant-panel/employee*')?'block':'none'}}">
                                <li class="nav-item {{Request::is('restaurant-panel/employee/add-new')?'active':''}}">
                                    <a class="nav-link " href="{{route('vendor.employee.add-new')}}" title="{{translate('messages.add_new_Employee')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('messages.add_new_employee')}}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('restaurant-panel/employee/list')?'active':''}}">
                                    <a class="nav-link " href="{{route('vendor.employee.list')}}" title="{{translate('messages.Employee_list')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('messages.list')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    <!-- End Employee Management -->

                    <!-- Ads -->

                    @if (\App\CentralLogics\Helpers::employee_module_permission_check('new_ads'))
                        <li class="nav-item px-20 pb-5">
                            <div class="promo-card">
                                <div class="position-relative">
                                    <img src="{{dynamicAsset('assets/admin/img/promo.png')}}" class="mw-100" alt="">
                                    <h4 class="mb-2 mt-3">{{ translate('Want_to_get_highlighted?') }}</h4>
                                    <p class="mb-4">
                                        {{ translate('Create_ads_to_get_highlighted_on_the_app_and_web_browser') }}
                                    </p>
                                    <a href="{{ route('vendor.advertisement.create') }}" class="btn btn--primary">{{ translate('Create_Ads') }}</a>
                                </div>
                            </div>
                        </li>
                    @endif

                    <!-- End Ads -->
                </ul>
            </div>
        </div>
    </aside>
</div>

<div id="sidebarCompact" class="d-none">

</div>



@push('script_2')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script>
        "use strict";
        $(window).on('load' , function() {
            if($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 100);
            }
        });
    </script>
@endpush
