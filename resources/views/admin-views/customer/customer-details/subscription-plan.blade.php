@extends('layouts.admin.app')

@section('title', translate('messages.Subscription_Plan'))

@section('customerDetails')
    active
@endsection

@section('content')
@php
    $currentPlanId = $subscription?->plan_id;
    $isActive = $subscription && $subscription->status === 'active';
    $statusBadgeMap = [
        'active' => 'text-success bg-success bg-opacity-10',
        'expired' => 'text-warning bg-warning bg-opacity-10',
        'canceled' => 'text-danger bg-danger bg-opacity-10',
    ];
    $statusBadge = $subscription ? ($statusBadgeMap[$subscription->status] ?? 'text-secondary bg-secondary bg-opacity-10') : '';
    $txnStatusBadgeMap = [
        'success' => 'text-success bg-success bg-opacity-10',
        'pending' => 'text-warning bg-warning bg-opacity-10',
        'failed' => 'text-danger bg-danger bg-opacity-10',
        'refunded' => 'text-secondary bg-secondary bg-opacity-10',
    ];
@endphp

<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <h1 class="page-header-title gap-1 flex-wrap">
                    {{ translate('messages.Customer_Details') }} <span class="gray-dark">#{{ $customer->id }}</span>
                </h1>
            </div>
        </div>
    </div>

    @include('admin-views.customer.partials._user_details_urls')

    <div class="card mb-20">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-20">
                <div>
                    <h3 class="mb-1 fs-16 text-capitalize">{{ translate('messages.Subscription_Plan') }}</h3>
                    <p class="mb-0 gray-dark fs-12">
                        {{ translate('messages.here_you_can_see_an_overview_of_subscription_plans') }}
                    </p>
                </div>
                @if ($subscription)
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if ($isActive)
                            <button type="button" class="btn btn--cancel h-40 text-nowrap px-3 form-alert"
                                data-id="pro-sub-cancel-{{ $subscription->id }}"
                                data-message="{{ translate('messages.Cancel_this_subscription') }}?">
                                {{ translate('messages.Cancel_Subscription') }}
                            </button>
                            <form action="{{ route('admin.pro-customer.subscription.cancel', $subscription->id) }}"
                                method="post" id="pro-sub-cancel-{{ $subscription->id }}">
                                @csrf
                            </form>
                        @endif
                        @if ($plans->count())
                            @if ($isCanceled)
                                <button type="button" class="btn btn--primary h-40 text-nowrap px-3" data-toggle="modal"
                                    data-target="#plan_subscribe_modal">
                                    {{ translate('messages.Subscribe_Now') }}
                                </button>
                            @else
                                <button type="button" class="btn btn--primary h-40 text-nowrap px-3" data-toggle="modal"
                                    data-target="#plan_modal_area">
                                    {{ translate('messages.Shift_or_Renew_Subscription') }}
                                </button>
                            @endif
                        @endif
                    </div>
                @endif
            </div>

            @if ($subscription)
                <div class="bg-light2 p-xxl-20 p-3 rounded">
                    <div class="row g-3">
                        <div class="col-md-12 col-lg-4">
                            <div class="subscription-plan__card">
                                <div>
                                    <div class="mb-2 d-flex gap-2 align-items-center justify-content-start">
                                        <div class="w-40px">
                                            <img width="40"
                                                src="{{ dynamicAsset('assets/admin/img/subscription-win-badge.png') }}"
                                                alt="img" class="rounded-circle">
                                        </div>
                                        <h3 class="mb-0 fs-24 fw-500 lh-1">
                                            {{ $subscription->plan_name }}
                                        </h3>
                                    </div>
                                    @if ($subscription->plan_type === 'free_trial')
                                        <p class="mb-0 fs-32 font-semibold text-dark text-capitalize">
                                            {{ translate('messages.Free_Trial') }}
                                            <span class="fs-20 fw-400 gray-dark">/ {{ optional($subscription->plan)->duration ?? '-' }} {{ translate('messages.days') }}</span>
                                        </p>
                                    @else
                                        <p class="mb-0 fs-32 font-semibold text-dark">
                                            {{ \App\CentralLogics\Helpers::format_currency((float) $subscription->plan_price) }}
                                            <span class="fs-20 fw-400 gray-dark">/ {{ optional($subscription->plan)->duration ?? '-' }} {{ translate('messages.days') }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="bg-white subscription-plan__card p-3 rounded">
                                <div class="mb-3 d-flex gap-2 align-items-center justify-content-start">
                                    <h3 class="mb-0 fs-14 fw-500 lh-1">{{ translate('messages.Plan_Validity') }}</h3>
                                    <span class="badge {{ $statusBadge }} px-2 rounded-pill fs-12 text-capitalize">
                                        {{ $subscription->status }}
                                    </span>
                                </div>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="fs-14 min-w-90">{{ translate('messages.Start_Date') }}</span>
                                        <span>:</span>
                                        <span class="fs-14 text-dark">{{ optional($subscription->start_at)->format('d M Y h:i A') ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="fs-14 min-w-90">{{ translate('messages.Expire_Date') }}</span>
                                        <span>:</span>
                                        <span class="fs-14 text-dark">{{ optional($subscription->end_at)->format('d M Y h:i A') ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="bg-white subscription-plan__card p-3 rounded">
                                <div class="mb-3 d-flex gap-2 align-items-center justify-content-start flex-wrap">
                                    <h3 class="mb-0 fs-14 fw-500 lh-1">{{ translate('messages.Transaction') }}</h3>
                                    @if ($latestTransaction)
                                        <span class="fs-14 fw-500 text-dark">
                                            #{{ $latestTransaction->id }}
                                        </span>
                                        <span class="badge {{ $txnStatusBadgeMap[$latestTransaction->payment_status] ?? 'text-secondary bg-secondary bg-opacity-10' }} px-2 rounded-pill fs-12 text-capitalize">
                                            {{ $latestTransaction->payment_status }}
                                        </span>
                                    @else
                                        <span class="badge text-secondary bg-secondary bg-opacity-10 px-2 rounded-pill fs-12 text-capitalize">
                                            {{ translate('messages.N/A') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="fs-14 min-w-90">{{ translate('messages.Payment_Date') }}</span>
                                        <span>:</span>
                                        <span class="fs-14 text-dark">
                                            @if ($latestTransaction)
                                                {{ optional($latestTransaction->paid_at ?? $latestTransaction->created_at)->format('d M Y h:i A') }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="fs-14 min-w-90">{{ translate('messages.Paid_By') }}</span>
                                        <span>:</span>
                                        <span class="fs-14 text-dark text-capitalize">
                                            @if ($latestTransaction && $latestTransaction->payment_method)
                                                {{ str_replace('_', ' ', $latestTransaction->payment_method) }}
                                            @elseif ($subscription->plan_type === 'free_trial')
                                                {{ translate('messages.Free_Trial') }}
                                            @else
                                                {{ translate('messages.N/A') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="empty--data text-center py-5 my-4">
                    <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="empty"
                        style="max-width:140px;height:auto;" class="mb-3">
                    <h5 class="fs-16 mb-1 text-capitalize">{{ translate('messages.No_Subscription_Found') }}</h5>
                    <p class="fs-12 gray-dark mb-3">
                        {{ translate('messages.this_customer_has_not_subscribed_to_any_pro_plan_yet') }}
                    </p>
                    @if ($plans->count())
                        <button type="button" class="btn btn--primary h-40 px-4 text-capitalize" data-toggle="modal"
                            data-target="#plan_subscribe_modal">
                            {{ translate('messages.Subscribe_Now') }}
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@if ($subscription && !$isCanceled && $plans->count())
    {{-- Change / Renew Subscription Modal --}}
    <div class="modal fade" id="plan_modal_area" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true">
        <div class="modal-dialog mx-auto modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close bg-light w-40px h-40px rounded-circle fs-20" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-4 px-4 pt-0">
                    <div class="mb-20">
                        <h3 class="mb-3 fs-20 text-center text-capitalize">
                            {{ translate('messages.Shift_or_Renew_Subscription') }}
                        </h3>
                        <div class="border rounded-10">
                            <div class="bg-plan rounded-10">
                                <div class="rounded-4 bg-plan-gradient p-3 text-center plan-pro-head">
                                    <div class="w-40px mx-auto mb-10px">
                                        <img width="40"
                                            src="{{ dynamicAsset('assets/admin/img/subscription-win-badge.png') }}"
                                            alt="img" class="rounded-circle">
                                    </div>
                                    <h3 class="mb-1 fs-18 fw-500 lh-1">{{ $proBrand }}</h3>
                                    <p class="mb-0 fs-14">{{ translate('messages.Save_more_on_every_order') }}</p>
                                </div>
                                @include('admin-views.pro-customer.partials._active-benefits-list', ['benefits' => $benefits])
                            </div>
                            <div class="p-20">
                                <p class="fs-14 mb-10px text-capitalize">{{ translate('messages.Select_Duration') }}</p>
                                <ul class="nav nav-tabs mb-10px plan-duration-tabs border-0 bg-global-gray rounded p-1 d-flex flex-sm-nowrap flex-wrap"
                                    id="planDurationTabs" role="tablist">
                                    @foreach ($plans as $idx => $plan)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $idx === 0 ? 'active' : '' }} border-0"
                                                id="plan-tab-{{ $plan->id }}" data-toggle="tab"
                                                data-target="#plan-tab-pane-{{ $plan->id }}" type="button" role="tab"
                                                aria-controls="plan-tab-pane-{{ $plan->id }}"
                                                aria-selected="{{ $idx === 0 ? 'true' : 'false' }}">
                                                {{ $plan->duration }} {{ translate('messages.Days') }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content" id="planDurationTabsContent">
                                    @foreach ($plans as $idx => $plan)
                                        <div class="tab-pane fade {{ $idx === 0 ? 'show active' : '' }}"
                                            id="plan-tab-pane-{{ $plan->id }}" role="tabpanel"
                                            aria-labelledby="plan-tab-{{ $plan->id }}" tabindex="0">
                                            <div class="bg-plan-monthly d-flex p-3 rounded align-items-center gap-2 justify-content-between flex-wrap">
                                                <div class="mb-0 d-flex gap-2 align-items-center justify-content-start flex-wrap">
                                                    <h3 class="mb-0 fs-14 fw-500 lh-1">{{ $plan->plan_name }}</h3>
                                                    @if ((int) $plan->id === (int) $currentPlanId)
                                                        <span class="badge {{ $statusBadge }} px-2 rounded-pill fs-12 text-capitalize">
                                                            {{ $subscription->status }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <h3 class="m-0 fs-25">
                                                    @if ($plan->plan_type === 'free_trial')
                                                        <span class="fs-16 fw-500 text-capitalize">{{ translate('messages.Free_Trial') }}</span>
                                                    @else
                                                        {{ \App\CentralLogics\Helpers::format_currency((float) $plan->price) }}
                                                    @endif
                                                </h3>
                                            </div>
                                            <div class="mt-4 text-center d-flex flex-column gap-2 align-items-center">
                                                @if ((int) $plan->id === (int) $currentPlanId)
                                                    <button type="button"
                                                        class="max-w-260px w-100 btn p-0 btn--primary renew-btn text-white py-2 px-3 open-renew-modal"
                                                        data-plan-id="{{ $plan->id }}">
                                                        {{ translate('messages.Renew_Subscription') }}
                                                    </button>
                                                @else
                                                    <button type="button"
                                                        class="max-w-260px w-100 btn p-0 btn--primary renew-btn text-white py-2 px-3 open-shift-modal"
                                                        data-plan-id="{{ $plan->id }}"
                                                        data-plan-name="{{ $plan->plan_name }}"
                                                        data-plan-price="{{ (float) $plan->price }}"
                                                        data-plan-duration="{{ $plan->duration }}"
                                                        data-plan-type="{{ $plan->plan_type }}">
                                                        {{ translate('messages.Shift_Subscription') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Renew confirm modal --}}
    <div class="modal fade" id="plan_renew_subscription" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true">
        <div class="modal-dialog mx-auto modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close bg-light w-40px h-40px rounded-circle fs-20" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-4 px-4 pt-0">
                    <form method="post" action="{{ route('admin.pro-customer.subscription.renew', $subscription->id) }}">
                        @csrf
                        <input type="hidden" name="plan_id" id="renew_plan_id" value="{{ $currentPlanId }}">
                        <h3 class="mb-3 fs-20 text-center text-capitalize">{{ translate('messages.Renew_Subscription') }}</h3>
                        <div class="border rounded-10 p-4">
                            <div class="bg-plan w-fit-content mx-auto subscription-plan__card rounded-10 mb-4">
                                <div class="rounded-4 d-flex align-items-center gap-3 bg-plan-gradient p-3 plan-pro-head">
                                    <div class="w-40px mb-0">
                                        <img width="40"
                                            src="{{ dynamicAsset('assets/admin/img/subscription-win-badge.png') }}"
                                            alt="img" class="rounded-circle">
                                    </div>
                                    <div class="text-start">
                                        <h3 class="mb-1 fs-24 fw-500 lh-1">{{ $subscription->plan_name }}</h3>
                                        <p class="mb-0 fs-32 font-semibold text-dark">
                                            @if ($subscription->plan_type === 'free_trial')
                                                <span class="fs-20 fw-500 text-capitalize">{{ translate('messages.Free_Trial') }}</span>
                                            @else
                                                {{ \App\CentralLogics\Helpers::format_currency((float) $subscription->plan_price) }}
                                            @endif
                                            <span class="fs-20 fw-400 gray-dark">/ {{ optional($subscription->plan)->duration ?? '-' }} {{ translate('messages.days') }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @if ($subscription->plan_type !== 'free_trial')
                                @include('admin-views.pro-customer.partials._payment-method-radios', [
                                    'customer' => $customer,
                                    'price' => (float) $subscription->plan_price,
                                    'idPrefix' => 'renew',
                                ])
                            @endif

                            <div class="text-center pt-3">
                                <p class="mb-3">
                                    {{ translate('messages.#Note_:_Ensure_payment_is_received_before_changing_or_renewing_the_subscription') }}
                                </p>
                                <button type="submit"
                                    class="max-w-260px w-100 btn p-0 btn--primary renew-btn text-white py-2 px-3 text-capitalize">
                                    {{ translate('messages.Confirm_Renew') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Shift confirm modal --}}
    <div class="modal fade" id="plan_shift_subscription" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true">
        <div class="modal-dialog max-w-850px mx-auto modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close bg-light w-40px h-40px rounded-circle fs-20" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-4 px-4 pt-0">
                    <form method="post" action="{{ route('admin.pro-customer.subscription.shift', $subscription->id) }}">
                        @csrf
                        <input type="hidden" name="plan_id" id="shift_plan_id" value="">
                        <h3 class="mb-3 fs-20 text-center text-capitalize">{{ translate('messages.Shift_Subscription') }}</h3>
                        <div class="border rounded-10 p-4">
                            <div class="p-xl-1 d-flex align-items-center mb-4 gap-2 justify-content-center flex-md-nowrap flex-wrap">
                                <div class="position-relative bg-global-gray subscription-plan__card shift_subs-card rounded-10">
                                    <div class="d-flex align-items-center gap-3 p-3 plan-pro-head">
                                        <div class="w-40px mb-0">
                                            <img width="40"
                                                src="{{ dynamicAsset('assets/admin/img/subscription-win-badge.png') }}"
                                                alt="img" class="rounded-circle">
                                        </div>
                                        <div class="text-start">
                                            <h3 class="mb-1 fs-24 fw-500 lh-1">{{ $subscription->plan_name }}</h3>
                                            <p class="mb-0 fs-32 font-semibold text-dark">
                                                @if ($subscription->plan_type === 'free_trial')
                                                    <span class="fs-20 fw-500 text-capitalize">{{ translate('messages.Free_Trial') }}</span>
                                                @else
                                                    {{ \App\CentralLogics\Helpers::format_currency((float) $subscription->plan_price) }}
                                                @endif
                                                <span class="fs-20 fw-400 gray-dark">/ {{ optional($subscription->plan)->duration ?? '-' }} {{ translate('messages.days') }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <img width="26" src="{{ dynamicAsset('assets/admin/img/convert-arrow.png') }}"
                                    alt="img" class="arrows d-md-block d-none">
                                <div class="position-relative subscription-plan__card shift_subs-card active rounded-10">
                                    <div class="d-flex align-items-center gap-3 p-3 plan-pro-head">
                                        <div class="w-40px mb-0">
                                            <img width="40"
                                                src="{{ dynamicAsset('assets/admin/img/subscription-win-badge.png') }}"
                                                alt="img" class="rounded-circle">
                                        </div>
                                        <div class="text-start">
                                            <h3 class="mb-1 fs-24 fw-500 lh-1" id="shift_plan_name_label">-</h3>
                                            <p class="mb-0 fs-32 font-semibold text-dark">
                                                <span id="shift_plan_price_label">-</span>
                                                <span class="fs-20 fw-400 gray-dark">/ <span id="shift_plan_duration_label">-</span> {{ translate('messages.days') }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="js-shift-payment-wrapper">
                                @include('admin-views.pro-customer.partials._payment-method-radios', [
                                    'customer' => $customer,
                                    'price' => 0,
                                    'idPrefix' => 'shift',
                                ])
                            </div>

                            <div class="text-center pb-2 pt-3">
                                <p class="mb-3">
                                    {{ translate('messages.#Note_:_Ensure_payment_is_received_before_changing_or_renewing_the_subscription') }}
                                </p>
                                <button type="submit"
                                    class="max-w-260px w-100 btn p-0 btn--primary renew-btn text-white py-2 px-3 text-capitalize">
                                    {{ translate('messages.Confirm_Shift') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@if (($isCanceled || !$subscription) && $plans->count())
    {{-- Subscribe Now Modal --}}
    <div class="modal fade" id="plan_subscribe_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true">
        <div class="modal-dialog mx-auto modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close bg-light w-40px h-40px rounded-circle fs-20" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-4 px-4 pt-0">
                    <form method="post"
                        action="{{ route('admin.pro-customer.subscription.start', $customer->id) }}">
                        @csrf
                        <input type="hidden" name="plan_id" id="subscribe_plan_id" value="{{ $plans->first()->id }}">
                        <h3 class="mb-3 fs-20 text-center text-capitalize">{{ translate('messages.Subscribe_Now') }}</h3>
                        <div class="border rounded-10">
                            <div class="bg-plan rounded-10">
                                <div class="rounded-4 bg-plan-gradient p-3 text-center plan-pro-head">
                                    <div class="w-40px mx-auto mb-10px">
                                        <img width="40"
                                            src="{{ dynamicAsset('assets/admin/img/subscription-win-badge.png') }}"
                                            alt="img" class="rounded-circle">
                                    </div>
                                    <h3 class="mb-1 fs-18 fw-500 lh-1">{{ translate('messages.StackFood_Pro') }}</h3>
                                    <p class="mb-0 fs-14">{{ translate('messages.Save_more_on_every_order') }}</p>
                                </div>
                            </div>
                            <div class="p-20">
                                <p class="fs-14 mb-10px text-capitalize">{{ translate('messages.Select_Plan') }}</p>
                                <ul class="nav nav-tabs mb-10px plan-duration-tabs border-0 bg-global-gray rounded p-1 d-flex flex-sm-nowrap flex-wrap"
                                    id="subscribePlanTabs" role="tablist">
                                    @foreach ($plans as $idx => $plan)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $idx === 0 ? 'active' : '' }} border-0 select-subscribe-plan"
                                                id="subscribe-tab-{{ $plan->id }}" data-toggle="tab"
                                                data-target="#subscribe-tab-pane-{{ $plan->id }}" type="button" role="tab"
                                                aria-controls="subscribe-tab-pane-{{ $plan->id }}"
                                                aria-selected="{{ $idx === 0 ? 'true' : 'false' }}"
                                                data-plan-id="{{ $plan->id }}"
                                                data-plan-price="{{ (float) $plan->price }}"
                                                data-plan-type="{{ $plan->plan_type }}">
                                                {{ $plan->duration }} {{ translate('messages.Days') }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content" id="subscribePlanTabsContent">
                                    @foreach ($plans as $idx => $plan)
                                        <div class="tab-pane fade {{ $idx === 0 ? 'show active' : '' }}"
                                            id="subscribe-tab-pane-{{ $plan->id }}" role="tabpanel"
                                            aria-labelledby="subscribe-tab-{{ $plan->id }}" tabindex="0">
                                            <div class="bg-plan-monthly d-flex p-3 rounded align-items-center gap-2 justify-content-between flex-wrap">
                                                <div class="mb-0 d-flex gap-2 align-items-center justify-content-start flex-wrap">
                                                    <h3 class="mb-0 fs-14 fw-500 lh-1">{{ $plan->plan_name }}</h3>
                                                    @if ($plan->plan_type === 'free_trial')
                                                        <span class="badge text-success bg-success bg-opacity-10 px-2 rounded-pill fs-12 text-capitalize">
                                                            {{ translate('messages.Free_Trial') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <h3 class="m-0 fs-25">
                                                    @if ($plan->plan_type === 'free_trial')
                                                        <span class="fs-16 fw-500 text-capitalize">{{ translate('messages.Free') }}</span>
                                                    @else
                                                        {{ \App\CentralLogics\Helpers::format_currency((float) $plan->price) }}
                                                    @endif
                                                </h3>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="js-subscribe-payment-wrapper">
                                    @include('admin-views.pro-customer.partials._payment-method-radios', [
                                        'customer' => $customer,
                                        'price' => (float) $plans->first()->price,
                                        'idPrefix' => 'subscribe',
                                    ])
                                </div>

                                <div class="text-center pt-3">
                                    <p class="mb-3">
                                        {{ translate('messages.#Note_:_Ensure_payment_is_received_before_starting_the_subscription') }}
                                    </p>
                                    <button type="submit"
                                        class="max-w-260px w-100 btn p-0 btn--primary renew-btn text-white py-2 px-3 text-capitalize">
                                        {{ translate('messages.Confirm_Subscription') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('script_2')
<script>
    "use strict";
    $(function () {
        var freeTrialLabel = "{{ translate('messages.Free_Trial') }}";

        // Re-evaluate the wallet/manual radio inside a payment-method block based on the
        // currently-selected plan price + plan type. Hides the entire block for free trials.
        function refreshPaymentMethod($wrapper, planType, planPrice) {
            var $section = $wrapper.find('.js-payment-method');
            if (!$section.length) return;

            if (planType === 'free_trial') {
                $wrapper.hide();
                $section.find('input[name="payment_method"]').prop('checked', false).prop('disabled', true);
                return;
            }
            $wrapper.show();

            var balance = parseFloat($section.data('wallet-balance')) || 0;
            var hasEnough = balance >= parseFloat(planPrice || 0);

            var $wallet = $section.find('.js-pay-wallet');
            var $walletLabel = $section.find('.js-pay-wallet-label');
            var $manual = $section.find('input[value="manual"]');
            var $lowMsg = $section.find('.js-wallet-low-msg');

            // Manual pay is always valid for a paid plan. Re-enable it in case a
            // previously-selected free-trial plan disabled every payment radio —
            // a disabled radio is checked but never submitted, which the server
            // then rejects with "payment method required".
            $manual.prop('disabled', false);

            $wallet.prop('disabled', !hasEnough);
            $walletLabel.toggleClass('opacity-50', !hasEnough);
            $lowMsg.toggleClass('d-none', hasEnough).toggleClass('d-block', !hasEnough);

            if (!hasEnough) {
                $wallet.prop('checked', false);
                $manual.prop('checked', true);
            } else if (!$wallet.is(':checked') && !$manual.is(':checked')) {
                $wallet.prop('checked', true);
            }
        }

        $(document).on('click', '.open-renew-modal', function () {
            var planId = $(this).data('plan-id');
            $('#renew_plan_id').val(planId);
            $('#plan_modal_area').modal('hide');
            $('#plan_renew_subscription').modal('show');
        });

        $(document).on('click', '.select-subscribe-plan', function () {
            var planId = $(this).data('plan-id');
            var planPrice = $(this).data('plan-price');
            var planType = $(this).data('plan-type');
            $('#subscribe_plan_id').val(planId);
            refreshPaymentMethod($('.js-subscribe-payment-wrapper'), planType, planPrice);
        });

        $(document).on('click', '.open-shift-modal', function () {
            var planId = $(this).data('plan-id');
            var planName = $(this).data('plan-name');
            var planPrice = $(this).data('plan-price');
            var planDuration = $(this).data('plan-duration');
            var planType = $(this).data('plan-type');

            $('#shift_plan_id').val(planId);
            $('#shift_plan_name_label').text(planName);
            $('#shift_plan_duration_label').text(planDuration);

            if (planType === 'free_trial') {
                $('#shift_plan_price_label').text(freeTrialLabel);
            } else {
                $('#shift_plan_price_label').text("{{ \App\CentralLogics\Helpers::currency_symbol() }}" + Number(planPrice).toFixed(2));
            }

            refreshPaymentMethod($('.js-shift-payment-wrapper'), planType, planPrice);

            $('#plan_modal_area').modal('hide');
            $('#plan_shift_subscription').modal('show');
        });

        // Initial render for the subscribe modal: pre-evaluate against the first tab.
        var $firstSubscribeTab = $('.select-subscribe-plan').first();
        if ($firstSubscribeTab.length) {
            refreshPaymentMethod($('.js-subscribe-payment-wrapper'),
                $firstSubscribeTab.data('plan-type'),
                $firstSubscribeTab.data('plan-price'));
        }
    });
</script>
@endpush
