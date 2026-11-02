        <div class="card card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Restaurant Opening & Closing Schedules') }}</h4>
                        <p class="fs-12 mb-0">{{ translate('messages.Setup when you want to open & close your restaurant. It will effect on customer app & websites.') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light rounded-10 p-3 p-sm-4 mb-20">
                        <label
                            class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                            for="always_open">
                            <span class="pr-2 d-flex">
                                <span class="line--limit-1">
                                    {{ translate('messages.Always Open') }}
                                </span>
                            </span>
                            <input type="checkbox" data-id="always_open" data-type="status" name="opening_closing_status"
                                   data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-tips-on.png') }}"
                                   data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-tips-off.png') }}"
                                   data-title-on="{{ translate('Set_restaurant_as_Always_Open?') }}"
                                   data-title-off="{{ translate('Set_opening_closing_schedule_for_this_restaurant?') }}"
                                   data-text-on="<p>{{ translate('If_enabled,_your_restaurant_will_be_available_for_orders_all_the_time.') }}</p>"
                                   data-text-off="<p>{{ translate('If_disabled,_your_restaurant_will_accept_orders_only_during_your_scheduled_opening_hours.') }}</p>"

                                   class="toggle-switch-input" id="always_open" {{ ($restaurant->restaurant_config?->opening_closing_status ?? 0) == 1 ? 'checked' : '' }}>
                            <span class="toggle-switch-label">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="bg-light rounded-10 p-3 p-sm-4 schedule_section {{ $restaurant?->restaurant_config?->opening_closing_status ? 'd-none' : ''}}">
                <div class="row g-3 mb-20 align-items-center">
                    <div class="col-lg-8">
                        <div>
                            <h4 class="mb-1">{{ translate('messages.Set specific time for your restaurant') }}</h4>
                            <p class="fs-12 mb-0">{{ translate('messages.Here you setup your restaurant individual active time or same time for every day.') }}</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <label
                            class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                            for="same_time_for_every_day">
                            <span class="pr-2 d-flex">
                                <span class="line--limit-1">
                                    {{ translate('messages.Same Time for Every Day') }}
                                </span>
                                &nbsp;
                                <span data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.If_enabled,_you_can_set_the_schedule_for_one_day_and_it_will_apply_to_all_days.') }}"
                                class="tio-info text-gray1  fs-16"></span>
                            </span>

                            <input type="checkbox"
                                   data-id="same_time_for_every_day" data-type="status" name="same_time_for_every_day"
                                   data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-tips-on.png') }}"
                                   data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-tips-off.png') }}"

                                   data-title-on="{{ translate('Apply_same_schedule_for_every_day?') }}"
                                   data-title-off="{{ translate('Set_different_schedule_for_each_day?') }}"

                                   data-text-on="<p>{{ translate('If_enabled,_you_can_set_the_schedule_for_one_day_and_it_will_apply_to_all_days.') }}</p>"
                                   data-text-off="<p>{{ translate('If_disabled,_you_can_set_opening_and_closing_time_separately_for_each_day.') }}</p>"

                                   class="toggle-switch-input dynamic-checkbox"
                                   id="same_time_for_every_day"
                                {{ ($restaurant->restaurant_config?->same_time_for_every_day ?? 0) == 1 ? 'checked' : '' }}>

                            <span class="toggle-switch-label">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="bg-white rounded border" id="schedule">
                    @include('vendor-views.business-settings.partials._schedule', $restaurant)
                </div>
            </div>
        </div>
        <!-- Guidline Offcanvas -->
        <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
        <form method="POST" action="javascript:" method="post" id="add-schedule">
            @csrf
            <input type="hidden" name="day" id="day_id_input" value="">
            <input type="hidden" name="restaurant_id" value="{{$restaurant->id}}">
            <div class="custom-offcanvas d-flex flex-column justify-content-between" tabindex="-1" id="offcanvasAddSchedule" aria-labelledby="offcanvasAddScheduleLabel" style="--offcanvas-width: 500px">
                <div>
                    <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
                        <h3 class="mb-0">{{ translate('messages.Create Schedule For Friday') }}</h3>
                        <button type="button"
                                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                                aria-label="Close">&times;</button>
                    </div>
                    <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
                        <div class="__bg-F8F9FC-card min-h-100vh-260">
                            <div class="form-group">
                                <label for="recipient-name"
                                    class="input-label text-capitalize">{{ translate('messages.Start_time') }}</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                            <div class="form-group">
                                <label for="message-text"
                                    class="input-label text-capitalize">{{ translate('messages.End_time') }}</label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="offcanvas-footer p-3 position-sticky bottom-0 bg-white  d-flex gap-3 align-items-center justify-content-center">
                    <button type="reset" class="btn btn--reset w-100">{{ translate('messages.reset') }}</button>
                    <button type="submit" class="btn btn--primary w-100">{{ translate('messages.Submit') }}</button>
                </div>
            </div>
        </form>
