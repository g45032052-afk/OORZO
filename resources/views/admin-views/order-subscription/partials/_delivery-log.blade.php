@php
$logs = $subscription->logs()->WhereIn('order_status',['delivered','failed','canceled', 'refund_requested','refund_request_canceled', 'refunded'])->latest()->get();

$ongoingOrder = $subscription->log()->WhereNotIn('order_status',['delivered','failed','canceled', 'refund_requested','refund_request_canceled', 'refunded'])->first();
$schedules= $subscription->schedules ?? [];
$pauselogs= $subscription->pause ?? [];
$scheduleType= $subscription->type;

                foreach ($schedules as $schedule) {
                    if($schedule->type != 'daily'){
                        $scheduleDates[] = $schedule->day;
                        $scheduleTime[$schedule->day] = $schedule->time;
                    } else{
                        $scheduleDates =['daily'];
                        $scheduleTime = [$schedule->time];
                    }
                }
                $start_at =$subscription->start_at;
                $end_at =$subscription->end_at;

                // Every remaining scheduled date, pauses included. The generator drops paused
                // dates when it is given the pause windows, which is right for the customer app
                // but wrong here: the admin was left looking at a list that quietly skipped the
                // days the customer had paused, with no sign a pause existed at all. The dates
                // are therefore generated whole and marked below, so a pause is visible as a
                // pause rather than as an absence.
                $dates = \App\CentralLogics\Helpers::generateDatesForSubscriptionOrders($start_at, $end_at, $scheduleDates ?? [], $scheduleTime ?? [], [], $scheduleType);

                // A date is paused when it falls inside one of the customer's pause windows.
                // Compared by date only: a pause is booked as whole days, while a scheduled
                // entry carries the delivery time as well.
                $pausedOn = function ($datetime) use ($pauselogs) {
                    $day = \Illuminate\Support\Carbon::parse($datetime)->toDateString();

                    foreach ($pauselogs as $pause_log) {
                        if ($day >= \Illuminate\Support\Carbon::parse($pause_log->from)->toDateString()
                            && $day <= \Illuminate\Support\Carbon::parse($pause_log->to)->toDateString()) {
                            return $pause_log;
                        }
                    }

                    return null;
                };
@endphp

@if($logs->count() == 0 && !$ongoingOrder && count($dates))
<div class="py-5 text-center">
    <div class="py-sm-5">
        <img src="{{dynamicAsset('assets/admin/img/empty-log.png')}}" alt="">
        <div class="mt-3">{{translate('messages.No delivery logs found!')}}</div>
    </div>
</div>

@else

<div class="delivery-logs-area">
    @if ($subscription->status === 'paused')
        {{-- Said once at the top as well as per row: the ongoing order below keeps whatever
             status it already had, and on its own that read as though the subscription were
             still running today. --}}
        <div class="alert-soft-warning rounded p-3 mb-3 fs-14">
            {{ translate('messages.This_subscription_is_paused_right_now._No_order_is_created_on_a_paused_day.') }}
        </div>
    @endif
    @if ($ongoingOrder)

    <div class="p-3 rounded bg--F7F9FD">
        <h5 class="mb-3">{{ translate('Ongoing') }}</h5>
        <div class="item">
            <div class="inner">
                <div>
                    <h6>{{ translate('Order_Id_#') }}<a href="{{route('admin.order.details',['id'=>$subscription->order->id])}}">{{ $ongoingOrder->order_id }}</a> </h6>
                    <small> {{ \App\CentralLogics\Helpers::time_date_format($ongoingOrder->{$ongoingOrder->order_status} ?? $ongoingOrder->updated_at) }}</small>
                </div>
                <div class="text-right">
                    <h6>{{  \App\CentralLogics\Helpers::format_currency($subscription->order->order_amount)  }}</h6>
                    <span class="badge badge--info">{{  translate($ongoingOrder->order_status)  }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif
        @if (count($logs) > 0)
        <div>
            <h5 class="mb-3 pt-2">{{ translate('Delivered') }}</h5>
            <div class="h-25 overflow-hidden scroll-bar">
            @foreach($logs as $key=>$log)

            <div class="item">
                <div>#{{ $key+1 }}</div>
                <div class="inner">
                    <div>
                        <h6>{{ translate('Order_Id_#') }}<a href="{{route('admin.order.details',['id'=> $log->order_id])}}">{{ $log->order_id }}</a> </h6>

                        <small>  {{  \App\CentralLogics\Helpers::time_date_format($log?->{$log?->order_status} ?? $log?->updated_at) }}</small>
                    </div>
                    <div class="text-right">
                        <h6>{{  \App\CentralLogics\Helpers::format_currency($subscription->order->order_amount)  }}</h6>
                        <span class="badge {{ $log->order_status == 'delivered'  ? 'badge--info' : 'badge-soft-danger'}}">{{  translate($log->order_status)  }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        </div>
        @endif
        @if (count($dates) > 0)
        <div>
            <h5 class="mb-3 pt-2"> {{ translate('Upcoming') }} </h5>
            <div class="h-25 overflow-hidden scroll-bar">
                @foreach ($dates as $key => $date)
                @php($pause = $pausedOn($date))
                <div class="item" >
                    <div>#{{ $key+1 }}</div>
                    <div class="inner">
                        {{-- The tooltip used to promise "the order will be created at <date>"
                             on every upcoming row, paused days included - which is the promise
                             QA read as a bluff, because on a paused day no order is created at
                             all. What the row is waiting on is said in the badge beside it
                             instead, where it can differ per day. --}}
                        <div>
                            <h6>{{ translate('Order_Id_#') }}{{ $subscription->order->id }}</h6>
                            <small>{{  \App\CentralLogics\Helpers::date_format($date) }}</small>
                        </div>
                        <div class="text-right">
                            <h6>{{  \App\CentralLogics\Helpers::format_currency($subscription->order->order_amount)  }}</h6>
                            @if ($pause)
                                <span class="badge badge-soft-warning"
                                      data-toggle="tooltip" data-placement="top"
                                      title="{{ translate('messages.Paused_by_the_customer_from') }} {{ \App\CentralLogics\Helpers::date_format($pause->from) }} {{ translate('messages.to') }} {{ \App\CentralLogics\Helpers::date_format($pause->to) }}">
                                    {{ translate('messages.Paused') }}
                                </span>
                            @else
                                <span class="badge badge--success"
                                      data-toggle="tooltip" data-placement="top"
                                      title="{{ translate('messages.The_order_for_this_day_has_not_been_created_yet.') }}">
                                    {{ translate('Pending') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
    @endif
