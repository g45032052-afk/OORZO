<?php ($hh = $happyHour ?? null); ?>
<?php ($language=\App\Models\BusinessSetting::where('key','language')->first()); ?>
<?php ($language = $language->value ?? null); ?>

<div class="card mb-3">
    <div class="card-body">
        <h5><?php echo e(translate('messages.Create_Happy_Hour')); ?></h5>
        <p class="opacity-75"><?php echo e(translate('messages.Enter_the_Happy_Hour_name_add_a_short_description_and_upload_the_cover_image_and_icon_to_help_customers_recognize_the_offer')); ?></p>

        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="bg-light rounded p-3 h-100">
                    <?php if($language): ?>
                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active" href="#" id="default-link"><?php echo e(translate('Default')); ?></a>
                                </li>
                                <?php $__currentLoopData = json_decode($language); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="nav-item">
                                        <a class="nav-link lang_link" href="#" id="<?php echo e($lang); ?>-link"><?php echo e(\App\CentralLogics\Helpers::get_language_name($lang).'('.strtoupper($lang).')'); ?></a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>

                        <div class="lang_form" id="default-form">
                            <div class="form-group">
                                <label class="input-label">
                                    <?php echo e(translate('messages.Happy_Hour_Title')); ?> (<?php echo e(translate('Default')); ?>) <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="<?php echo e(translate('messages.The_name_customers_will_see_for_this_Happy_Hour')); ?>">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <input type="text" name="title[]" class="form-control h--45px" maxlength="100"
                                       data-counter="count_title_default" placeholder="<?php echo e(translate('messages.Type_Title')); ?>"
                                       value="<?php echo e($hh?->getRawOriginal('title')); ?>">
                                <small class="d-block text-right opacity-75">
                                    <span id="count_title_default"><?php echo e(strlen($hh?->getRawOriginal('title') ?? '')); ?></span>/100
                                </small>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            <div class="form-group mb-0">
                                <label class="input-label">
                                    <?php echo e(translate('messages.Short_Description')); ?> (<?php echo e(translate('Default')); ?>)
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="<?php echo e(translate('messages.A_short_line_explaining_what_this_Happy_Hour_offers')); ?>">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <textarea name="short_description[]" class="form-control" maxlength="150"
                                          data-counter="count_desc_default"
                                          placeholder="<?php echo e(translate('messages.Type_about_the_description')); ?>"><?php echo e($hh?->getRawOriginal('short_description')); ?></textarea>
                                <small class="d-block text-right opacity-75">
                                    <span id="count_desc_default"><?php echo e(strlen($hh?->getRawOriginal('short_description') ?? '')); ?></span>/150
                                </small>
                            </div>
                        </div>

                        <?php $__currentLoopData = json_decode($language); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $translate = [];
                                if ($hh && count($hh['translations'])) {
                                    foreach ($hh['translations'] as $t) {
                                        if ($t->locale == $lang && $t->key == 'title') {
                                            $translate[$lang]['title'] = $t->value;
                                        }
                                        if ($t->locale == $lang && $t->key == 'short_description') {
                                            $translate[$lang]['short_description'] = $t->value;
                                        }
                                    }
                                }
                            ?>
                            <div class="d-none lang_form" id="<?php echo e($lang); ?>-form">
                                <div class="form-group">
                                    <label class="input-label">
                                        <?php echo e(translate('messages.Happy_Hour_Title')); ?> (<?php echo e(strtoupper($lang)); ?>)
                                        <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                              data-original-title="<?php echo e(translate('messages.The_name_customers_will_see_for_this_Happy_Hour')); ?>">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="text" name="title[]" class="form-control h--45px" maxlength="100"
                                           data-counter="count_title_<?php echo e($lang); ?>" placeholder="<?php echo e(translate('messages.Type_Title')); ?>"
                                           value="<?php echo e($translate[$lang]['title'] ?? ''); ?>">
                                    <small class="d-block text-right opacity-75">
                                        <span id="count_title_<?php echo e($lang); ?>"><?php echo e(strlen($translate[$lang]['title'] ?? '')); ?></span>/100
                                    </small>
                                </div>
                                <input type="hidden" name="lang[]" value="<?php echo e($lang); ?>">
                                <div class="form-group mb-0">
                                    <label class="input-label">
                                        <?php echo e(translate('messages.Short_Description')); ?> (<?php echo e(strtoupper($lang)); ?>)
                                        <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                              data-original-title="<?php echo e(translate('messages.A_short_line_explaining_what_this_Happy_Hour_offers')); ?>">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <textarea name="short_description[]" class="form-control" maxlength="150"
                                              data-counter="count_desc_<?php echo e($lang); ?>"
                                              placeholder="<?php echo e(translate('messages.Type_about_the_description')); ?>"><?php echo e($translate[$lang]['short_description'] ?? ''); ?></textarea>
                                    <small class="d-block text-right opacity-75">
                                        <span id="count_desc_<?php echo e($lang); ?>"><?php echo e(strlen($translate[$lang]['short_description'] ?? '')); ?></span>/150
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div id="default-form">
                            <div class="form-group">
                                <label class="input-label"><?php echo e(translate('messages.Happy_Hour_Title')); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="title[]" class="form-control h--45px" maxlength="100"
                                       data-counter="count_title_default" placeholder="<?php echo e(translate('messages.Type_Title')); ?>"
                                       value="<?php echo e($hh?->getRawOriginal('title')); ?>">
                                <small class="d-block text-right opacity-75">
                                    <span id="count_title_default"><?php echo e(strlen($hh?->getRawOriginal('title') ?? '')); ?></span>/100
                                </small>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            <div class="form-group mb-0">
                                <label class="input-label"><?php echo e(translate('messages.Short_Description')); ?></label>
                                <textarea name="short_description[]" class="form-control" maxlength="150"
                                          data-counter="count_desc_default"
                                          placeholder="<?php echo e(translate('messages.Type_about_the_description')); ?>"><?php echo e($hh?->getRawOriginal('short_description')); ?></textarea>
                                <small class="d-block text-right opacity-75">
                                    <span id="count_desc_default"><?php echo e(strlen($hh?->getRawOriginal('short_description') ?? '')); ?></span>/150
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <?php $__currentLoopData = [
                ['key' => 'cover_image', 'input' => 'cover-input', 'title' => 'Cover_image', 'sub' => 'Upload_Happy_Hour_Cover_image', 'ratio' => '3:1'],
                ['key' => 'icon', 'input' => 'icon-input', 'title' => 'Icon', 'sub' => 'Upload_Happy_Hour_Icon', 'ratio' => '1:1'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-3 mb-3">
                    <div class="bg-light rounded p-3 h-100 text-center d-flex flex-column">
                        <h6 class="mb-1"><?php echo e(translate('messages.'.$img['title'])); ?></h6>
                        <p class="opacity-75 font-size-sm mb-3"><?php echo e(translate('messages.'.$img['sub'])); ?></p>

                        
                        <?php ($existing = $hh && $hh->{$img['key']}
                            ? ($img['key'] === 'icon' ? $hh->icon_full_url : $hh->cover_image_full_url)
                            : null); ?>

                        <?php echo $__env->make('admin-views.partials._image-uploader', [
                            'id' => $img['input'],
                            'name' => $img['key'],
                            'isRequired' => true,
                            'existingImage' => $existing,
                            'defaultImage' => $existing,
                            'ratio' => $img['ratio'],
                            'maxSize' => MAX_FILE_SIZE,
                            'imageExtension' => IMAGE_EXTENSION,
                            'imageFormat' => IMAGE_FORMAT,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h5><?php echo e(translate('messages.Discount_Setup')); ?></h5>
        <p class="opacity-75"><?php echo e(translate('messages.Set_the_discount_percentage_and_if_needed_define_a_minimum_order_amount_required_to_apply_the_offer')); ?></p>

        <div class="bg-light rounded p-3">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="input-label">
                            <?php echo e(translate('messages.Zone')); ?> <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="<?php echo e(translate('messages.Two_happy_hours_in_the_same_zone_cannot_overlap_in_time')); ?>">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <select name="zone_id" id="zone_id" class="form-control h--45px" required>
                            <option value="" disabled <?php echo e($hh ? '' : 'selected'); ?>><?php echo e(translate('messages.Select_Zone')); ?></option>
                            <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($zone->id); ?>" <?php echo e($hh && $hh->zone_id == $zone->id ? 'selected' : ''); ?>><?php echo e($zone->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-md-0">
                        <label class="input-label">
                            <?php echo e(translate('messages.Discount')); ?> <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="<?php echo e(translate('messages.Percentage_taken_off_the_order_while_the_Happy_Hour_runs')); ?>">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" name="discount" class="form-control h--45px"
                                   placeholder="<?php echo e(translate('Ex: 5')); ?>" value="<?php echo e($hh?->discount); ?>" required>
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        
                        <div class="d-flex align-items-center justify-content-between">
                            <label class="input-label mb-0">
                                <?php echo e(translate('messages.Min_Order_Amount')); ?>(<?php echo e(\App\CentralLogics\Helpers::currency_symbol()); ?>)
                                <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                      data-original-title="<?php echo e(translate('messages.Orders_below_this_amount_will_not_get_the_discount')); ?>">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>
                            </label>
                            <label class="toggle-switch toggle-switch-sm mb-0" for="min_order_toggle">
                                <input type="checkbox" class="toggle-switch-input" id="min_order_toggle" <?php echo e($hh && $hh->min_order_amount ? 'checked' : ''); ?>>
                                <span class="toggle-switch-label"><span class="toggle-switch-indicator"></span></span>
                            </label>
                        </div>
                        
                        <input type="hidden" name="min_order_enabled" id="min_order_enabled"
                               value="<?php echo e($hh && $hh->min_order_amount ? 1 : 0); ?>">
                        <input type="number" step="0.01" min="0" name="min_order_amount" id="min_order_amount"
                               class="form-control h--45px mt-1" placeholder="<?php echo e(translate('Ex: 50')); ?>" value="<?php echo e($hh?->min_order_amount); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h5><?php echo e(translate('messages.Schedule_&_Duration_Setup')); ?></h5>
        <p class="opacity-75"><?php echo e(translate('messages.Choose_when_the_Happy_Hour_offer_will_run_by_selecting_a_schedule_type_date_range_and_time_range')); ?></p>

        <div class="form-group">
            <label class="input-label">
                <?php echo e(translate('messages.Offer_Schedule_Type')); ?> <span class="text-danger">*</span>
                <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                      data-original-title="<?php echo e(translate('messages.Daily_repeats_every_day_Weekly_repeats_on_chosen_weekdays_Custom_picks_individual_dates')); ?>">
                    <i class="tio-info text-gray1 fs-16"></i>
                </span>
            </label>
            <div class="row border rounded mx-0 py-3">
                <?php $__currentLoopData = ['daily' => 'Daily_Schedule', 'weekly' => 'Weekly_Schedule', 'custom' => 'Custom_Schedule']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input duration-type" type="radio" name="duration_type" value="<?php echo e($value); ?>"
                                   id="duration_<?php echo e($value); ?>" <?php echo e(($hh?->duration_type ?? 'daily') === $value ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="duration_<?php echo e($value); ?>"><?php echo e(translate('messages.'.$label)); ?></label>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="alert alert-soft-warning alert--note d-flex gap-2">
            <i class="tio-error"></i>
            <span><?php echo e(translate('messages.When_creating_a_Happy_Hour_the_selected_time_must_not_overlap_with_an_existing_happy_hour_in_the_same_zone')); ?></span>
        </div>

        
        <div class="bg-light rounded p-3 daily-block d-none">
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="mb-1"><?php echo e(translate('messages.Select_Date_&_Time')); ?></h6>
                    <p class="opacity-75 font-size-sm mb-0"><?php echo e(translate('messages.Select_your_suitable_time_within_a_time_range')); ?></p>
                </div>
                <div class="col-md-8">
                    <div class="bg-white rounded p-3">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="input-label">
                                    <?php echo e(translate('messages.Date_Range')); ?> <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="<?php echo e(translate('messages.The_first_and_last_day_this_Happy_Hour_runs')); ?>">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <div class="hh-field cursor-pointer">
                                    <input type="text" id="daily_range_display" class="cursor-pointer"
                                           placeholder="<?php echo e(translate('messages.Select_Date_Range')); ?>" readonly>
                                    <i class="tio-calendar"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="input-label">
                                    <?php echo e(translate('messages.Time_Range')); ?> <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="<?php echo e(translate('messages.Pick_the_start_time_the_window_always_ends_one_hour_later')); ?>">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                
                                <div class="hh-field hh-time cursor-pointer">
                                    <span class="hh-time__placeholder"><?php echo e(translate('messages.Select_Time_Range')); ?></span>
                                    <span class="hh-time__value d-none">
                                        <input type="time" max="22:59" id="daily_start_time" class="start-time">
                                        <span>-</span>
                                        <span class="hh-time__end derived-end"></span>
                                    </span>
                                    <i class="tio-time"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="bg-light rounded p-3 weekly-block d-none">
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="mb-1"><?php echo e(translate('messages.Select_Date_&_Time')); ?></h6>
                    <p class="opacity-75 font-size-sm mb-0"><?php echo e(translate('messages.Select_your_suitable_time_within_a_time_range')); ?></p>
                </div>
                <div class="col-md-8">
                    <div class="bg-white rounded p-3">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="input-label">
                                    <?php echo e(translate('messages.Date_Range')); ?> <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="<?php echo e(translate('messages.Opens_the_day_picker_where_the_weekdays_and_range_are_chosen')); ?>">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <div class="hh-field cursor-pointer" id="open_weekly_modal">
                                    <input type="text" id="weekly_range_display" class="cursor-pointer"
                                           placeholder="<?php echo e(translate('messages.Select_Date_Range')); ?>" readonly>
                                    <i class="tio-calendar"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="input-label">
                                    <?php echo e(translate('messages.Time_Range')); ?> <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="<?php echo e(translate('messages.Pick_the_start_time_the_window_always_ends_one_hour_later')); ?>">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <div class="hh-field hh-time cursor-pointer">
                                    <span class="hh-time__placeholder"><?php echo e(translate('messages.Select_Time_Range')); ?></span>
                                    <span class="hh-time__value d-none">
                                        <input type="time" max="22:59" id="weekly_start_time" class="start-time"
                                               value="<?php echo e($hh && $hh->start_time ? substr($hh->start_time, 0, 5) : ''); ?>">
                                        <span>-</span>
                                        <span class="hh-time__end derived-end"></span>
                                    </span>
                                    <i class="tio-time"></i>
                                </div>
                            </div>
                        </div>

                        <p class="text-center mb-0 mt-3" id="weekly_summary"></p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="bg-light rounded p-3 custom-block d-none">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="mb-1"><?php echo e(translate('messages.Select_Date_&_Time')); ?></h6>
                    <p class="opacity-75 font-size-sm mb-0"><?php echo e(translate('messages.Select_your_suitable_time_within_a_time_range')); ?></p>
                </div>
                <div class="col-md-8">
                    <div class="bg-white rounded p-3 mb-3">
                        <label class="input-label">
                            <?php echo e(translate('messages.Date_&_Time_Range')); ?> <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="<?php echo e(translate('messages.Opens_the_calendar_where_each_date_gets_its_own_start_time')); ?>">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <div class="hh-field cursor-pointer" id="open_custom_modal">
                            <input type="text" id="custom_range_display" class="cursor-pointer"
                                   placeholder="<?php echo e(translate('messages.Select_Date_&_Time_Range')); ?>" readonly>
                            <i class="tio-calendar"></i>
                        </div>
                    </div>

                    
                    <div id="custom_summary_wrap" class="d-none">
                        
                        <p class="mb-2">
                            <strong id="custom_count">0</strong>
                            <span class="opacity-75"><?php echo e(translate('messages.days_selected')); ?></span>
                        </p>
                        <div class="bg-white rounded-8 py-2" id="custom_summary_list"></div>
                    </div>
                </div>
            </div>
        </div>

        
        <input type="hidden" name="date_range" id="date_range_input">
        <input type="hidden" name="start_time" id="start_time_input">
        <input type="hidden" name="weekly_days" id="weekly_days_input">
        <input type="hidden" name="is_permanent" id="is_permanent_input" value="0">
        <input type="hidden" name="custom_days" id="custom_days_input">
        <input type="hidden" name="custom_times" id="custom_times_input">

        <div class="alert alert-soft-info alert--note d-flex gap-2 mt-3 mb-0">
            <img src="<?php echo e(dynamicAsset('assets/admin/img/svg/bulb.svg')); ?>" class="svg flex-shrink-0" alt="">
            <span><?php echo e(translate('messages.The_Happy_Hour_offer_will_only_be_available_during_the_selected_schedule')); ?></span>
        </div>
    </div>
</div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/happy-hour/partials/_form_body.blade.php ENDPATH**/ ?>