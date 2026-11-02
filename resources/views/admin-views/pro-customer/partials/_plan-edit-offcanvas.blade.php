<div id="offcanvas__editplan" class="custom-offcanvas d-flex flex-column justify-content-between">
    <div class="h-100">
        <form action="" method="post" id="pro-plan-edit-form" class="d-flex flex-column h-100">
            @csrf
            <div>
                <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
                    <h3 class="mb-0 fs-18 line--limit-1">{{ translate('messages.edit_subscription_plan') }}</h3>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
                </div>
                <div class="custom-offcanvas-body p-20">
                    <div class="bg--secondary rounded pt-2 px-3 mb-20 pb-3">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="input-label">
                                        {{ translate('messages.Subscription_Plan_Type') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="bg-white max-w-540px w-100 d-flex flex-wrap gap-3 justify-content-between rounded p-3">
                                        <label class="form-check radio-sclae-1 d-flex gap-2 mb-0 flex-grow-1">
                                            <input type="radio" name="plan_type" value="free_trial"
                                                class="form-check-input radio--input pro-plan-type">
                                            <span class="user-select-none form-check-label">{{ translate('messages.Free_Trial') }}</span>
                                        </label>
                                        <label class="form-check radio-sclae-1 d-flex gap-2 mb-0 flex-grow-1">
                                            <input type="radio" name="plan_type" value="paid"
                                                class="form-check-input radio--input pro-plan-type">
                                            <span class="user-select-none form-check-label">{{ translate('messages.Paid') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @if ($language)
                                <div class="col-12">
                                    <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active" href="#" id="default-link-edit">{{ translate('messages.default') }}</a>
                                            </li>
                                            @foreach ($language as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link" href="#" id="{{ $lang }}-link-edit">
                                                        {{ \App\CentralLogics\Helpers::get_language_name($lang) . ' (' . strtoupper($lang) . ')' }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <input type="hidden" name="lang[]" value="default">
                                <div class="form-group lang_form mb-0" id="default-form-edit">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="input-label mb-0">
                                            {{ translate('messages.plan_name') }} ({{ translate('messages.default') }})
                                            <span class="text-danger">*</span>
                                        </label>
                                        <small class="text-muted pro-plan-name-counter">0 / 70</small>
                                    </div>
                                    <input type="text" name="plan_name[]" class="form-control plan-name-default"
                                        placeholder="{{ translate('messages.ex: monthly') }}" maxlength="70" required>
                                </div>
                                @if ($language)
                                    @foreach ($language as $lang)
                                        <div class="form-group d-none lang_form mb-0" id="{{ $lang }}-form-edit">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="input-label mb-0">
                                                    {{ translate('messages.plan_name') }} ({{ strtoupper($lang) }})
                                                </label>
                                                <small class="text-muted pro-plan-name-counter">0 / 70</small>
                                            </div>
                                            <input type="text" name="plan_name[]" class="form-control plan-name-lang"
                                                data-locale="{{ $lang }}"
                                                placeholder="{{ translate('messages.ex: monthly') }}" maxlength="70">
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    @endforeach
                                @endif
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="input-label">
                                        {{ translate('messages.duration_(days)') }}
                                        <span class="text-danger">*</span>
                                        <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.duration_max_10_years') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="number" name="duration" min="1" max="3650" class="form-control"
                                        placeholder="{{ translate('messages.ex: 30') }}" required>
                                </div>
                            </div>

                            <div class="col-12 pro-plan-price-wrap">
                                <div class="form-group mb-0">
                                    <label class="input-label">
                                        {{ translate('messages.Plan_Price ($)') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="price" min="{{ \App\CentralLogics\Helpers::getDecimalPlaces() }}"
                                        step="{{ \App\CentralLogics\Helpers::getDecimalPlaces() }}"
                                        class="form-control"
                                        placeholder="{{ translate('messages.ex: 500') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
                <button type="button" class="btn w-100 btn--secondary pro-plan-edit-reset">{{ translate('messages.reset') }}</button>
                <button type="submit" class="btn w-100 btn--primary">{{ translate('messages.update') }}</button>
            </div>
        </form>
    </div>
</div>
