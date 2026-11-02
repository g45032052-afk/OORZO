<?php
    $selectedStoreId = old('restaurant_id', $reel->restaurant_id ?? null);
    $isEdit = isset($isEdit) && $isEdit;
    $alwaysVisible = (int) old('is_always_visible', $reel->is_always_visible ?? 0) === 1;
    $defaultDescription = old('description.0', $reel->getRawOriginal('description') ?? '');
    $existingDateRange = old('dates');
    $reelVideoMaxSizeMb = max(1, (int) (\App\CentralLogics\Helpers::get_business_settings('reels_max_upload_size_mb') ?? REEL_VIDEO_MAX_SIZE));
    $reelVideoSizeText = translate('messages.Size') . ': video ' . $reelVideoMaxSizeMb . ' MB (9:16 ' . translate('messages.Recommended') . ')';
    $reelMaxDuration = max(1, (int) (\App\CentralLogics\Helpers::get_business_settings('reels_max_duration') ?? REEL_VIDEO_MAX_DURATION));
    $reelMaxDurationUnit = \App\CentralLogics\Helpers::get_business_settings('reels_max_duration_unit') ?? 'min';
    $reelMaxDurationSeconds = $reelMaxDurationUnit === 'hour' ? $reelMaxDuration * 3600 : $reelMaxDuration * 60;
    $reelDurationUnitLabel = $reelMaxDurationUnit === 'hour' ? translate('messages.Hour') : translate('messages.Minutes');
    $reelDurationText = translate('messages.Max_duration') . ': ' . $reelMaxDuration . ' ' . $reelDurationUnitLabel;

    if ($existingDateRange === null && $reel->start_date && $reel->end_date) {
        $existingDateRange = \Carbon\Carbon::parse($reel->start_date)->format('m/d/Y') . ' - ' . \Carbon\Carbon::parse($reel->end_date)->format('m/d/Y');
    }

    $previewStore = $reel->restaurant ?? $restaurants->firstWhere('id', $selectedStoreId);
    $previewStoreName = $previewStore?->name ?? '';
    $previewStoreLogo = $previewStore?->logo_full_url ?? dynamicAsset('assets/admin/img/160x160/img2.jpg');
    $storeLabel = translate('messages.Restaurant');
    $selectStoreLabel = translate('messages.select') . ' ' . $storeLabel;
    $callToActionEnabled = (int) old('order_now_button', $reel->order_now_button ?? 0) === 1;
    $selectedFoodId = old('food_id', $reel->food_id ?? '');
    $selectedFoodName = $reel->food?->name ?? '';
?>

<div class="card card-body mb-20">
    <div class="d-flex flex-wrap flex-lg-nowrap gap-3 justify-content-between">
        <div class="flex-grow-1">
            <div class="bg-light p-3 p-xxl-4 rounded mb-20">
                <label class="form-label" for="restaurant_id">
                    <?php echo e($storeLabel); ?>

                    <span class="input-label-secondary m-0" data-toggle="tooltip" data-title="<?php echo e(translate('messages.Choose a'). ' ' . $storeLabel .' '. translate('messages. from the list to proceed')); ?>">
                        <i class="tio-info"></i>
                    </span>
                </label>
                <select class="form-control w-100 js-select2-custom store-select" id="restaurant_id" name="restaurant_id" required>
                    <option value="" disabled <?php echo e(!$selectedStoreId ? 'selected' : ''); ?>><?php echo e($selectStoreLabel); ?></option>
                    <?php $__currentLoopData = $restaurants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $restaurant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($restaurant->id); ?>" data-verified="<?php echo e((int) $restaurant->verified_seller); ?>" data-logo="<?php echo e($restaurant->logo_full_url); ?>" <?php echo e((int) $selectedStoreId === (int) $restaurant->id ? 'selected' : ''); ?>>
                            <?php echo e($restaurant->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="bg-light p-3 p-xxl-4 rounded mb-20">
                <ul class="nav nav-tabs nav--tabs mt-0 mb-20">
                    <li class="nav-item">
                        <a class="nav-link lang_link active" href="#" id="default-link"><?php echo e(translate('messages.default')); ?></a>
                    </li>
                    <?php $__currentLoopData = $language; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="nav-item">
                            <a class="nav-link lang_link" href="#" id="<?php echo e($lang); ?>-link">
                                <?php echo e(\App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')'); ?>

                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                <div class="row align-items-end">
                    <div class="col-md-12 lang_form default-form" id="default-form">
                        <label for="reel_description_default" class="form-label">
                            <?php echo e(translate('messages.Short_Description')); ?> (<?php echo e(translate('messages.default')); ?>)
                            <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                data-title="<?php echo e(translate('Enter a short and engaging description (up to 200 words) that clearly highlights the key details.')); ?>">
                                <i class="tio-info text-muted"></i>
                            </span>
                        </label>
                        <textarea required id="reel_description_default" class="form-control reel-des-textarea" rows="2" maxlength="200"
                            name="description[]" placeholder="<?php echo e(translate('messages.write_short_description')); ?>"><?php echo e($defaultDescription); ?></textarea>
                        <span class="text-right text-counting color-A7A7A7 d-block mt-1"><?php echo e(strlen($defaultDescription)); ?>/200</span>
                        <input type="hidden" name="lang[]" value="default">
                    </div>

                    <?php $__currentLoopData = $language; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $translatedDescription = old('description.' . ($loop->index + 1));
                            if ($translatedDescription === null) {
                                $translatedDescription = optional(
                                    $reel->translations->first(function ($translation) use ($lang) {
                                        return $translation->locale === $lang && $translation->key === 'description';
                                    })
                                )->value;
                            }
                        ?>
                        <div class="col-md-12 d-none lang_form" id="<?php echo e($lang); ?>-form">
                            <label for="reel_description_<?php echo e($lang); ?>" class="form-label">
                                <?php echo e(translate('messages.Short_Description')); ?> (<?php echo e(strtoupper($lang)); ?>)
                            </label>
                            <textarea id="reel_description_<?php echo e($lang); ?>" class="form-control reel-des-textarea" rows="2" maxlength="200"
                                name="description[]" placeholder="<?php echo e(translate('messages.write_short_description')); ?>"><?php echo e($translatedDescription); ?></textarea>
                            <span class="text-right text-counting color-A7A7A7 d-block mt-1"><?php echo e(strlen($translatedDescription ?? '')); ?>/200</span>
                            <input type="hidden" name="lang[]" value="<?php echo e($lang); ?>">
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="bg-light p-3 p-xxl-4 rounded mb-20">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="reel-upload-box-wrapper text-center">
                            <label class="form-label fs-12 text-muted mb-20">
                                <?php echo e(translate('messages.Upload_Thumbnail_Image')); ?>

                                <span class="text-danger">*</span>
                            </label>

                            <div class="reel-upload-box <?php echo e($reel->thumbnail_full_url ? 'active' : ''); ?>" data-type="image" data-max-size="<?php echo e(MAX_FILE_SIZE); ?>" data-ratio="9:16" data-original-thumbnail="<?php echo e($reel->thumbnail_full_url ?? ''); ?>">
                                <input type="file" hidden accept="<?php echo e(IMAGE_EXTENSION); ?>" name="thumbnail">

                                <div class="upload-placeholder text-center" style="<?php echo e($reel->thumbnail_full_url ? 'display:none;' : ''); ?>">
                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/reels/img-icon.png')); ?>" alt="">
                                    <div>
                                        <span class="text-info"><?php echo e(translate('messages.Click_to_upload')); ?></span>
                                        <br>
                                        <?php echo e(translate('messages.or_drag_and_drop')); ?>

                                    </div>
                                </div>

                                <div class="upload-wrapper img-upload-wrapper" style="display: <?php echo e($reel->thumbnail_full_url ? 'block' : 'none'); ?>;">
                                    <img  src="<?php echo e($reel->thumbnail_full_url); ?>" alt="">
                                    <button type="button" class="btn remove-btn upload-again-btn" aria-label="<?php echo e(translate('messages.upload_again')); ?>">
                                        <i class="tio-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <p class="mt-3">
                                <?php echo e(translate(IMAGE_FORMAT)); ?>

                                <br>
                                  <?php echo e(translate('messages.Size_Max_')); ?> <?php echo e(MAX_FILE_SIZE); ?> <?php echo e(translate('MB_9_16')); ?>

                            </p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="reel-upload-box-wrapper text-center">
                            <label class="form-label fs-12 text-muted mb-3">
                                <?php echo e(translate('messages.Upload_a_file')); ?>

                                <span class="text-danger">*</span>
                            </label>

                            <div class="reel-upload-box <?php echo e($reel->video_full_url ? 'active' : ''); ?>" data-type="video" data-max-size="<?php echo e($reelVideoMaxSizeMb); ?>" data-max-duration-seconds="<?php echo e($reelMaxDurationSeconds); ?>" data-max-duration-label="<?php echo e($reelMaxDuration); ?> <?php echo e($reelDurationUnitLabel); ?>" data-original-thumbnail="<?php echo e($reel->thumbnail_full_url ?? $previewStoreLogo); ?>" data-original-video="<?php echo e($reel->video_full_url ?? ''); ?>" data-original-video-name="<?php echo e($reel->video ? basename($reel->video) : ''); ?>" data-original-video-type="<?php echo e($reel->video ? strtoupper(pathinfo($reel->video, PATHINFO_EXTENSION)) : ''); ?>">
                                <input type="file" hidden accept="video/*" name="video">

                                <div class="upload-placeholder text-center" style="<?php echo e($reel->video_full_url ? 'display:none;' : ''); ?>">
                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/reels/video-icon.png')); ?>" alt="">
                                    <div>
                                        <span class="text-info"><?php echo e(translate('messages.Add_Video')); ?></span>
                                    </div>
                                </div>

                                <div class="upload-wrapper reel-upload-wrapper" style="display: <?php echo e($reel->video_full_url ? 'block' : 'none'); ?>;">
                                    <div class="img-wrapper">
                                        <img src="<?php echo e($reel->thumbnail_full_url ?? $previewStoreLogo); ?>" alt="">
                                        <div class="reels-play-btn">
                                            <div class="d-flex justify-content-center align-items-center w-100 h-100">
                                                <i class="tio-play"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <h6 class="fs-10 fw-medium mb-0 reel-title"><?php echo e($reel->video ? basename($reel->video) : ''); ?></h6>
                                    <p class="fs-10 mt-2 mb-0"><span class="reel-type"><?php echo e($reel->video ? strtoupper(pathinfo($reel->video, PATHINFO_EXTENSION)) : ''); ?></span></p>
                                    <button type="button" class="btn remove-btn upload-again-btn" aria-label="<?php echo e(translate('messages.upload_again')); ?>">
                                        <i class="tio-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <p class="mt-3">
                                <?php echo e(translate('messages.Mp4_MOV_3GP_GIF')); ?>

                                <br>
                                <?php echo e($reelVideoSizeText); ?>

                                <br>
                                <?php echo e($reelDurationText); ?>

                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-light p-3 p-xxl-4 rounded">
                <div class="d-flex gap-2 justify-content-between flex-wrap mb-2">
                    <label class="form-label mb-0" for="dates">
                        <?php echo e(translate('messages.Reel_Validity')); ?>

                        <span class="input-label-secondary m-0" data-toggle="tooltip" data-title="<?php echo e(translate('messages.select_reels_duration')); ?>">
                            <i class="tio-info"></i>
                        </span>
                    </label>
                    <label class="form-label d-flex gap-2 align-items-center mb-0" for="is_always_visible">
                        <?php echo e(translate('messages.Always_Visible_to_Customers')); ?>

                        <input type="checkbox" id="is_always_visible" name="is_always_visible" value="1" <?php echo e($alwaysVisible ? 'checked' : ''); ?>>
                    </label>
                </div>
                <div class="position-relative">
                    <i class="tio-calendar fs-16 icon-absolute-on-right"></i>
                    <input required type="text" id="dates" class="form-control h-45 position-relative bg-transparent"
                        name="dates" value="<?php echo e($existingDateRange ?? ''); ?>"
                        data-initial-value="<?php echo e($existingDateRange ?? ''); ?>"
                        data-no-global-daterangepicker="true"
                        placeholder="<?php echo e(translate('messages.select_reels_duration')); ?>" autocomplete="off" <?php echo e($alwaysVisible ? 'disabled' : ''); ?>>
                </div>
            </div>

            <div class="bg-light p-3 p-xxl-4 rounded mt-20">
                <label for="call-to-action-toggle" class="d-flex gap-2 justify-content-between flex-wrap mb-0">
                    <span class="text-title font-semibold"><?php echo e(translate('Call to Action Button')); ?></span>
                    <label class="toggle-switch toggle-switch-sm">
                        <input type="checkbox" id="call-to-action-toggle" name="order_now_button" value="1" class="status toggle-switch-input" <?php echo e($callToActionEnabled ? 'checked' : ''); ?>>
                        <span class="toggle-switch-label text">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </label>
            </div>

            <div class="bg-light p-3 p-xxl-4 rounded mt-20 food-select-wrapper" id="food-select-wrapper" style="<?php echo e($callToActionEnabled ? '' : 'display:none;'); ?>">
                <label class="form-label" for="food_id">
                    <?php echo e(translate('messages.Food')); ?>

                    <span class="input-label-secondary" title="" data-toggle="tooltip" data-title="<?php echo e(translate('Selected food will be visible in the order.')); ?>">
                        <i class="tio-info"></i>
                    </span>
                    <span class="text-danger">*</span>
                </label>
                <select class="form-control w-100 js-select2-custom food-select" id="food_id" name="food_id" data-url="<?php echo e(route('admin.reels.foods')); ?>" data-selected="<?php echo e($selectedFoodId); ?>" data-placeholder="<?php echo e(translate('messages.select_food')); ?>" data-restaurant-id="<?php echo e($selectedStoreId); ?>">
                    <option value=""><?php echo e(translate('messages.select_food')); ?></option>
                    <?php if($selectedFoodId && $selectedFoodName): ?>
                        <option value="<?php echo e($selectedFoodId); ?>" selected><?php echo e($selectedFoodName); ?></option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="flex-shrink-0 max-w-100 w-353px mx-auto">
            <div class="bg-light p-3 p-xxl-4 rounded h-100">
                <h4 class="fw-medium mb-3"><?php echo e(translate('messages.Reel_Preview')); ?></h4>
                    <div class="reel-preview-box <?php echo e($reel->video_full_url ? 'active' : ''); ?>" style="<?php echo e($reel->thumbnail_full_url ? "background-image: url('{$reel->thumbnail_full_url}');" : ''); ?>">
                        <video src="<?php echo e($reel->video_full_url); ?>" controls class="reels-video" style="display:none;"></video>
                    <div class="reel-overlay">
                        <button type="button" class="btn reels-play-btn">
                            <div class="d-flex justify-content-center align-items-center w-100 h-100">
                                <i class="tio-play"></i>
                            </div>
                        </button>
                    </div>
                        <div class="reel-des-wrapper">
                            <div class="d-flex gap-2 align-items-center justify-content-between mb-2">
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="reel-preview-thumbnail" data-reel-thumbnail="<?php echo e($previewStoreLogo); ?>" style="<?php echo e($previewStoreLogo ? "background-image: url('{$previewStoreLogo}');" : ''); ?>"></div>
                                    <div class="thumbnail-placeholder" style="<?php echo e($previewStoreLogo ? 'display:none;' : ''); ?>"></div>
                                    <div class="reel-preview-title" data-reel-title="<?php echo e($previewStoreName); ?>"><?php echo e($previewStoreName); ?></div>
                                    <div class="title-placeholder" style="<?php echo e($previewStoreName ? 'display:none;' : ''); ?>"></div>
                                </div>
                                <button type="button" id="order-now-btn" class="btn px-2 py-1 fs-12 btn--warning text-white" style="<?php echo e($callToActionEnabled ? '' : 'display:none;'); ?>"><?php echo e(translate('Order_New')); ?></button>
                            </div>
                            <div class="reel-preview-des"><?php echo e($defaultDescription); ?></div>
                            <div class="des-placeholder" style="<?php echo e($defaultDescription ? 'display:none;' : ''); ?>">
                                <div class="mb-1"></div>
                                <div></div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<div class="btn--container justify-content-end">
    <button type="reset" id="resetBtn" class="btn btn--reset min-w-120"><?php echo e(translate('messages.Reset')); ?></button>
    <button type="submit" class="btn btn-primary min-w-120 call-demo">
        <?php echo e($isEdit ? translate('messages.Update') : translate('messages.Submit')); ?>

    </button>
</div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\Modules/ReelsModule\Resources/views/admin/reels/partials/_form.blade.php ENDPATH**/ ?>