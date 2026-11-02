
<?php $__currentLoopData = $bogo_groups['bundles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bogo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    
    <?php
        $modalId = 'bogo_details_'.\Illuminate\Support\Str::slug($bogo['group_id']);
    ?>
    <div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo e($bogo['offer_title'] ?? translate('messages.BOGO_Offer')); ?>

                        <span class="badge badge-soft-info ml-1"><?php echo e(translate('messages.BOGO')); ?></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between font-size-sm mb-3">
                        <span class="text-muted"><?php echo e(translate('qty')); ?> : <strong><?php echo e($bogo['quantity']); ?></strong></span>
                        <span class="text-muted">
                            <?php echo e(\App\CentralLogics\Helpers::format_currency($bogo['unit_price'])); ?>

                            <?php echo e(translate('messages.per_bundle')); ?>

                        </span>
                        <span><strong><?php echo e(\App\CentralLogics\Helpers::format_currency($bogo['total'])); ?></strong></span>
                    </div>

                    <?php $__currentLoopData = [
                        ['label' => translate('messages.Buying_Item'), 'rows' => $bogo['buy'], 'free' => false],
                        ['label' => translate('messages.Free_Item'), 'rows' => $bogo['free'], 'free' => true],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $side): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(count($side['rows'])): ?>
                            <div class="mb-3">
                                <div class="font-size-sm text-muted mb-2"><?php echo e($side['label']); ?></div>
                                <?php $__currentLoopData = $side['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $lineFood = \App\Models\Food::select('id', 'image')->with('storage')->find($line->food_id);
                                        $details = $line->food_details ? json_decode($line->food_details, true) : [];
                                        $variations = is_string($line->variation) ? json_decode($line->variation, true) : $line->variation;

                                        // The chosen option, not the question it answers. Printing
                                        // $variation['name'] alone rendered every line of a bundle as
                                        // "Size · color", so a Buy 2 Get 3 of one pizza in three
                                        // different sizes read as three identical lines.
                                        $chosen = collect((array) ($variations ?? []))
                                            ->map(function ($variation) {
                                                $labels = collect(data_get($variation, 'values', []))
                                                    ->pluck('label')->filter()->implode(', ');

                                                return $labels === ''
                                                    ? data_get($variation, 'name')
                                                    : data_get($variation, 'name').': '.$labels;
                                            })
                                            ->filter()->all();

                                        $lineAddons = collect(json_decode((string) $line->add_ons, true) ?: [])
                                            ->map(fn ($addon) => data_get($addon, 'name').' x'.data_get($addon, 'quantity', 1))
                                            ->filter()->all();
                                    ?>
                                    <div class="media media--sm align-items-center mb-2">
                                        <div class="avatar avatar-sm mr-2">
                                            <img class="img-fluid rounded onerror-image"
                                                 src="<?php echo e($lineFood?->image_full_url ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                                                 data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                                                 alt="<?php echo e($details['name'] ?? ''); ?>">
                                        </div>
                                        <div class="media-body">
                                            <div class="font-weight-bold"><?php echo e($details['name'] ?? translate('messages.item')); ?></div>
                                            <div class="font-size-sm text-muted">
                                                <?php echo e(translate('qty')); ?> : <?php echo e($line->quantity); ?>

                                                <?php $__currentLoopData = $chosen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    &middot; <?php echo e($label); ?>

                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                            <?php if(count($lineAddons)): ?>
                                                <div class="font-size-sm text-muted">
                                                    <?php echo e(translate('messages.addons')); ?> : <?php echo e(implode(', ', $lineAddons)); ?>

                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-right">
                                            <?php if($side['free']): ?>
                                                <span class="badge badge-soft-success"><?php echo e(translate('messages.Free')); ?></span>
                                                
                                                <?php if((float) ($line->bogo_free_value ?? 0) > 0): ?>
                                                    <div class="font-size-sm text-muted">
                                                        <del><?php echo e(\App\CentralLogics\Helpers::format_currency($line->bogo_free_value)); ?></del>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <strong><?php echo e(\App\CentralLogics\Helpers::format_currency($line->price)); ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/partials/_bogo-order-modals.blade.php ENDPATH**/ ?>