       <div class="col-lg-6">
           <div class="card shadow--card-2 border-0">
               <div class="card-body pb-0">
                   <?php ($language = \App\CentralLogics\Helpers::get_business_settings('language')); ?>
                   <?php ($product = isset($product) ? $product : null); ?>
                   <div class="js-nav-scroller hs-nav-scroller-horizontal">
                       <ul class="nav nav-tabs mb-4">
                           <li class="nav-item">
                               <a class="nav-link lang_link active" href="#"
                                   id="default-link"><?php echo e(translate('Default')); ?></a>
                           </li>
                           <?php $__currentLoopData = $language ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                               <li class="nav-item">
                                   <a class="nav-link lang_link " href="#"
                                       id="<?php echo e($lang); ?>-link"><?php echo e(\App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')'); ?></a>
                               </li>
                           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                       </ul>
                   </div>

               </div>

               <div class="card-body">
                   <div class="lang_form" id="default-form">
                       <div class="form-group">
                           <div class="justify-content-between d-flex">
                               <label class="input-label" for="default_name"><?php echo e(translate('messages.name')); ?>

                                   (<?php echo e(translate('Default')); ?>) <span class="form-label-secondary text-danger"
                                       data-toggle="tooltip" data-placement="right"
                                       data-original-title="<?php echo e(translate('messages.Required.')); ?>"> *
                                   </span>
                               </label>
                            <?php if(isset($openai_config) && data_get($openai_config, 'status') == 1): ?>
                            <button type="button" class="btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-2 auto_fill_title"
                                id="title-default-action-btn" data-type="default"
                                data-error="<?php echo e(translate('Please provide a product name so the AI can generate a suitable title.')); ?>"
                                data-lang="<?php echo e(\App\CentralLogics\Helpers::system_default_language()); ?>"
                                data-route="<?php echo e(route('admin.product.title-auto-fill')); ?>">
                                <div class="btn-svg-wrapper">
                                    <img width="18" height="18" class=""
                                        src="<?php echo e(dynamicAsset('assets/admin/img/svg/blink-right-small.svg')); ?>" alt="">
                                </div>
                                <span class="ai-text-animation d-none" role="status">
                                    <?php echo e(translate('Just_a_second')); ?>

                                </span>
                                <span class="btn-text"><?php echo e(translate('Generate Food Name')); ?></span>
                            </button>
                            <?php endif; ?>

                           </div>
                           <div class="outline-wrapper">
                               <input type="text" name="name[]" id="default_name" class="form-control"
                                   value="<?php echo e($product?->getRawOriginal('name') ?? old('name.0')); ?>"
                                   placeholder="<?php echo e(translate('messages.new_food')); ?>" required>
                           </div>
                       </div>
                       <input type="hidden" name="lang[]" value="default">
                       <div class="form-group mb-0 des_wrapper">

                           <div class="justify-content-between d-flex">
                               <label class="input-label"
                                   for="exampleFormControlInput1"><?php echo e(translate('messages.short_description')); ?>

                                   (<?php echo e(translate('Default')); ?>) <span class="form-label-secondary text-danger"
                                       data-toggle="tooltip" data-placement="right"
                                       data-original-title="<?php echo e(translate('messages.Required.')); ?>"> *
                                   </span></label>

                                   <?php if(isset($openai_config) && data_get($openai_config, 'status') == 1): ?>
                                   <button type="button" class="btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-2 auto_fill_description"
                                       id="description-default-action-btn" data-type="default"
                                       data-error="<?php echo e(translate('Please provide a product description so the AI can generate a description.')); ?>"
                                       data-lang="<?php echo e(\App\CentralLogics\Helpers::system_default_language()); ?>"
                                       data-route="<?php echo e(route('admin.product.description-auto-fill')); ?>">
                                       <div class="btn-svg-wrapper">
                                            <img width="18" height="18" class=""
                                                src="<?php echo e(dynamicAsset('assets/admin/img/svg/blink-right-small.svg')); ?>" alt="">
                                        </div>
                                        <span class="ai-text-animation d-none" role="status">
                                            <?php echo e(translate('Just_a_second')); ?>

                                        </span>
                                        <span class="btn-text"><?php echo e(translate('Generate Food Description')); ?></span>
                                   </button>
                                   <?php endif; ?>

                           </div>

                           <div class="outline-wrapper">
                                <textarea required type="text" name="description[]" maxlength="600" data-maxlength="600" id="description-default" class="form-control ckeditor min-height-154px"><?php echo e($product?->getRawOriginal('description') ?? old('description.0')); ?></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <span class="text-body-light text-right d-block mt-1">0/600</span>
                            </div>

                       </div>
                   </div>

                   <?php $__currentLoopData = $language ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                       <?php

                       if ($product && count($product['translations'])) {
                           $translate = [];
                           foreach ($product['translations'] as $t) {
                               if ($t->locale == $lang && $t->key == 'name') {
                                   $translate[$lang]['name'] = $t->value;
                               }
                               if ($t->locale == $lang && $t->key == 'description') {
                                   $translate[$lang]['description'] = $t->value;
                               }
                           }
                       }
                       ?>

                       <div class="d-none lang_form" id="<?php echo e($lang); ?>-form">
                           <div class="form-group">

                               <div class="justify-content-between d-flex">
                                   <label class="input-label"
                                       for="<?php echo e($lang); ?>_name"><?php echo e(translate('messages.name')); ?>

                                       (<?php echo e(strtoupper($lang)); ?>)
                                   </label>

                                <?php if(isset($openai_config) && data_get($openai_config, 'status') == 1): ?>

                                <button type="button" class="btn bg-white text-primary opacity-1 generate_btn_wrapper auto_fill_title"
                                    id="title-<?php echo e($lang); ?>-action-btn" data-lang="<?php echo e($lang); ?>"
                                    data-error="<?php echo e(translate('Please provide a product name so the AI can generate a suitable title or description.')); ?>"
                                    data-route="<?php echo e(route('admin.product.title-auto-fill')); ?>">
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


                               <input type="text" name="name[]" id="<?php echo e($lang); ?>_name"
                                   value="<?php echo e(isset($translate[$lang]['name']) ? $translate[$lang]['name'] : old('name.' . $key + 1)); ?>"
                                   class="form-control" placeholder="<?php echo e(translate('messages.new_food')); ?>">
                           </div>
                           <input type="hidden" name="lang[]" value="<?php echo e($lang); ?>">
                           <div class="form-group mb-0">
                               <div class="justify-content-between d-flex">
                                   <label class="input-label"
                                       for="exampleFormControlInput1"><?php echo e(translate('messages.short_description')); ?>


                                       (<?php echo e(strtoupper($lang)); ?>)</label>
                                      <?php if(isset($openai_config) && data_get($openai_config, 'status') == 1): ?>
                                      <button type="button" class="btn bg-white text-primary opacity-1 generate_btn_wrapper auto_fill_description"
                                          id="description-default-action-btn"
                                          data-error="<?php echo e(translate('Please provide a product description so the AI can generate a description.')); ?>"
                                          data-lang="<?php echo e($lang); ?>"
                                          data-route="<?php echo e(route('admin.product.description-auto-fill')); ?>">
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
                               <textarea type="text" name="description[]" id="description-<?php echo e($lang); ?>" maxlength="600" data-maxlength="600"
                                   class="form-control ckeditor min-height-154px"><?php echo e(isset($translate[$lang]['description']) ? $translate[$lang]['description'] : old('description.' . $key + 1)); ?></textarea>

                               <div class="d-flex justify-content-end">
                                   <span class="text-body-light text-right d-block mt-1">0/600</span>
                               </div>
                           </div>
                       </div>
                   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </div>

           </div>
       </div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/product/partials/_title_and_discription.blade.php ENDPATH**/ ?>