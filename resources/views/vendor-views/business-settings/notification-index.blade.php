@extends('layouts.vendor.app')

@section('title', translate('messages.Notification_Setup'))
@section('content')
    <div class="content container-fluid">
        @include('vendor-views.partials.business_setup_header')
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
                    <div>
                        <h3 class="mb-1">{{ translate('messages.Restaurant Notification Channels') }}</h3>
                        <p class="fs-12 mb-0">
                            {{ translate('messages.From here you setup who can see what types of notification from') }} {{ $business_name }}
                        </p>
                    </div>
                    <form >
                        <!-- Search -->
                        <div class="input-group input--group flex-nowrap">
                            <input id="datatableSearch_" type="search" name="search" class="form-control w-260 w-100-mobile" placeholder="{{ translate('messages.Search by topics') }}"  value="{{ request()?->search ?? null }}" aria-label="Search">
                            <button type="submit" class="btn btn--secondary">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                        <!-- End Search -->
                    </form>
                </div>
                <div class="table-responsive datatable-custom">
                    <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="text-nowrap">
                        <tr>
                            <th class="text-dark fw-medium bg-light p-0">
                                <div class="table-custom-td gap-2 d-flex align-items-center justify-content-between p-3 py-2">
                                    <div class="d-flex sl-topics">
                                        <span class="text-dark fw-medium">{{ translate('messages.Sl') }}</span>
                                        <div class="table-cont">
                                            <span class="text-dark fw-medium fs-12">{{ translate('messages.Topics') }}</span>
                                        </div>
                                    </div>
                                    <span class="text-dark fw-medium w-120 text-center">{{ translate('messages.Push Notification') }}</span>
                                    <span class="text-dark fw-medium w-120 text-center">{{ translate('messages.Mail') }}</span>
                                    <span class="text-dark fw-medium w-120 text-center">{{ translate('messages.SMS') }}</span>
                                </div>
                            </th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td colspan="5" class="p-0">
                                @php
                                    $grouped = collect($data)->groupBy(function ($item) {
                                        $parts = explode('_', (string) $item->key);
                                        return $parts[1] ?? 'other';
                                    });
                                    $titleFromKey = function ($g) {
                                        return ucwords(str_replace('_', ' ', $g));
                                    };
                                    $sl = 0;
                                @endphp

                                @foreach ($grouped as $groupKey => $items)
                                    <div class="d-flex gap-2 p-3 py-2 table-toggle-btn cursor-pointer transition active">
                                        <span class="btn-circle text-primary bg-primary" style="--size: 18px; --bs-bg-opacity: 0.1;">
                                            <i class="tio-down-ui fs-12"></i>
                                        </span>
                                        <h5 class="fs-16 text-capitalize">{{ $titleFromKey($groupKey) }}</h5>
                                    </div>

                                    <div class="table-custom-wrap open">
                                        @foreach ($items as $key => $item)
                                            @php
                                                $sl++;
                                                $item_admin_data = \App\CentralLogics\Helpers::getNotificationStatusData('restaurant', $item->key);
                                            @endphp
                                            <div class="table-custom-td gap-2 d-flex align-items-center justify-content-between border-bottom p-3 py-2">
                                                <div class="d-flex sl-topics">
                                                    <span class="text-dark">{{ $sl }}</span>
                                                    <div class="table-cont">
                                                        <h5 class="mb-1">{{ translate($item->title) }}</h5>
                                                        <p class="fs-12 mb-0">{{ translate($item->sub_title) }}</p>
                                                    </div>
                                                </div>
                                                <div class="w-120 text-center">
                                                    <div class="d-flex justify-content-center align-content-center">
                                                        @if ($item_admin_data->push_notification_status == 'disable')
                                                            <span>{{ translate('messages.N/A') }}</span>

                                                        {{-- @elseif($item_admin_data->push_notification_status == 'inactive')
                                                            <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip"
                                                                   title="{{ translate('This_notification_turned_off_by_admin.') }}">
                                                                <input type="checkbox"
                                                                       class="status toggle-switch-input dynamic-checkbox" disabled>
                                                                <span class="toggle-switch-label text">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label> --}}
                                                        @else
                                                            <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip"
                                                                   title="{{ translate('toggle_push_notification_for').' '.translate($item->title) }}">
                                                                <input type="checkbox"
                                                                       id="push_notification_{{$item->key}}"
                                                                       data-id="push_notification_{{$item->key}}"
                                                                       data-type="toggle"
                                                                       data-image-on="{{dynamicAsset('assets/admin/img/modal/mail-success.png')}}"
                                                                       data-image-off="{{dynamicAsset('assets/admin/img/modal/mail-warning.png')}}"
                                                                       data-title-on="{{ translate('Want to enable the Push Notification For').' '.translate($item->title) }} ?"
                                                                       data-title-off="{{ translate('Want to disable the Push Notification For').' '.translate($item->title) }} ?"
                                                                       data-text-on="<p>{{ translate('Push Notification Will Be Enabled For').' '.translate($item->title) }}</p>"
                                                                       data-text-off="<p>{{ translate('Push Notification Will Be disabled For').' '.translate($item->title) }}</p>"
                                                                       class="status toggle-switch-input dynamic-checkbox"
                                                                    {{ $item->push_notification_status == 'active' ? 'checked' : '' }}>
                                                                <span class="toggle-switch-label text">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                            <form action="{{ route('vendor.business-settings.notification_status_change', ['key'=> $item->key, 'type' => 'push_notification']) }}"
                                                                  method="get" id="push_notification_{{$item->key}}_form"></form>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="w-120 text-center">
                                                    <div class="d-flex justify-content-center align-content-center">
                                                        @if ($item_admin_data->mail_status == 'disable')
                                                            <span>{{ translate('messages.N/A') }}</span>

                                                        @elseif($item_admin_data->mail_status == 'inactive')
                                                            <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip"
                                                                   title="{{ translate('This_mail_turned_off_by_admin') }}">
                                                                <input type="checkbox"
                                                                       class="status toggle-switch-input dynamic-checkbox" disabled>
                                                                <span class="toggle-switch-label text">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                        @else
                                                            <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip"
                                                                   title="{{ translate('toggle_Mail_for').' '.translate($item->title) }}">

                                                                <input type="checkbox"
                                                                       data-type="toggle"
                                                                       id="mail_{{ $item->key }}"
                                                                       data-id="mail_{{ $item->key }}"
                                                                       data-image-on="{{dynamicAsset('assets/admin/img/modal/mail-success.png')}}"
                                                                       data-image-off="{{dynamicAsset('assets/admin/img/modal/mail-warning.png')}}"
                                                                       data-title-on="{{ translate('Want to enable the Mail For').' '.translate($item->title) }} ?"
                                                                       data-title-off="{{ translate('Want to disable the Mail For').' '.translate($item->title) }} ?"
                                                                       data-text-on="<p>{{ translate('Mail Will Be Enabled For').' '.translate($item->title) }}</p>"
                                                                       data-text-off="<p>{{ translate('Mail Will Be disabled For').' '.translate($item->title) }}</p>"
                                                                       class="status toggle-switch-input dynamic-checkbox"
                                                                    {{ $item->mail_status == 'active' ? 'checked' : '' }}>
                                                                <span class="toggle-switch-label text">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                            <form action="{{ route('vendor.business-settings.notification_status_change', ['key'=> $item->key, 'type' => 'Mail']) }}"
                                                                  method="get" id="mail_{{$item->key}}_form"></form>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="w-120 text-center">
                                                    <div class="d-flex justify-content-center align-content-center">
                                                        @if ($item_admin_data->sms_status == 'disable')
                                                            <span>{{ translate('messages.N/A') }}</span>

                                                        @elseif($item_admin_data->sms_status == 'inactive')
                                                            <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip"
                                                                   title="{{ translate('This_sms_turned_off_by_admin') }}">
                                                                <input type="checkbox"
                                                                       class="status toggle-switch-input dynamic-checkbox" disabled>
                                                                <span class="toggle-switch-label text">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                        @else
                                                            <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip"
                                                                   title="{{ translate('toggle_SMS_for').' '.translate($item->title) }}">
                                                                <input type="checkbox"
                                                                       id="SMS_{{ $item->key }}"
                                                                       data-id="SMS_{{ $item->key }}"
                                                                       data-type="toggle"
                                                                       data-image-on="{{dynamicAsset('assets/admin/img/modal/mail-success.png')}}"
                                                                       data-image-off="{{dynamicAsset('assets/admin/img/modal/mail-warning.png')}}"
                                                                       data-title-on="{{ translate('Want to disable the SMS For').' '.translate($item->title) }} ?"
                                                                       data-title-off="{{ translate('Want to disable the SMS For').' '.translate($item->title) }} ?"
                                                                       data-text-on="<p>{{ translate('SMS Will Be Enabled For').' '.translate($item->title) }}</p>"
                                                                       data-text-off="<p>{{ translate('SMS Will Be disabled For').' '.translate($item->title) }}</p>"
                                                                       class="status toggle-switch-input dynamic-checkbox"
                                                                    {{ $item->sms_status == 'active' ? 'checked' : '' }}>
                                                                <span class="toggle-switch-label text">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                            <form action="{{ route('vendor.business-settings.notification_status_change', ['key'=> $item->key, 'type' => 'SMS']) }}"
                                                                  method="get" id="SMS_{{$item->key}}_form"></form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection

@push('script_2')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.table-custom-wrap.open').forEach(function (wrap) {
                wrap.style.maxHeight = wrap.scrollHeight + 'px';
                wrap.addEventListener('transitionend', function () {
                    if (wrap.classList.contains('open')) wrap.style.maxHeight = 'none';
                });
            });

            document.querySelectorAll('.table-toggle-btn').forEach(function (btn) {
                const wrap = btn.nextElementSibling;
                if (wrap && wrap.classList.contains('open')) btn.classList.add('active');
                else btn.classList.remove('active');
            });

            document.querySelectorAll('.table-toggle-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    const tableWrap = this.nextElementSibling;
                    if (!tableWrap) return;

                    const isOpen = tableWrap.classList.contains('open');

                    if (isOpen) {
                        tableWrap.style.maxHeight = tableWrap.scrollHeight + 'px';
                        requestAnimationFrame(() => {
                            tableWrap.style.maxHeight = '0px';
                        });
                        tableWrap.classList.remove('open');
                    } else {
                        tableWrap.classList.add('open');
                        tableWrap.style.maxHeight = tableWrap.scrollHeight + 'px';
                    }

                    this.classList.toggle('active');
                });
            });
        });
    </script>
@endpush

