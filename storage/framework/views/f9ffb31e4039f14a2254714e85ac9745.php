<div class="col-lg-12">
    <div class="variation_wrapper">
        <div class="outline-wrapper">
            <div class="card shadow--card-2 border-0 bg-animate">
                <div class="card-header flex-wrap">
                    <h5 class="card-title">
                        <span class="card-header-icon mr-2">
                            <i class="tio-canvas-text"></i>
                        </span>
                        <span><?php echo e(translate('messages.food_variations')); ?></span>
                    </h5>

                    <div>
                        <a class="btn text--primary-2" id="add_new_option_button">
                            <?php echo e(translate('add_new_variation')); ?>

                            <i class="tio-add"></i>
                        </a>
                        <?php if(isset($openai_config) && data_get($openai_config, 'status') == 1): ?>
                        <button type="button" class="btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-2 variation_setup_auto_fill"
                            id="variation_setup_auto_fill" data-route="<?php echo e(route('admin.product.variation-setup-auto-fill')); ?>"
                            data-lang="en">
                            <div class="btn-svg-wrapper">
                                <img width="18" height="18" class=""
                                     src="<?php echo e(dynamicAsset('assets/admin/img/svg/blink-right-small.svg')); ?>" alt="">
                            </div>
                            <span class="ai-text-animation d-none" role="status">
                                <?php echo e(translate('Just_a_second')); ?>

                            </span>
                            <span class="btn-text"><?php echo e(translate('Generate')); ?></span>
                        </button>
                        <?php endif; ?>
                    </div>

                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-12">
                            <div id="add_new_option">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/product/partials/_food_variations.blade.php ENDPATH**/ ?>