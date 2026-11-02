<?php if($conversations->count() > 0): ?>
    <?php $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php ($user= $conv->sender_type == 'admin' ? $conv->receiver :  $conv->sender); ?>
        <?php if($user): ?>
        <?php ($unchecked=($conv->last_message?->sender_id == $user->id) ? $conv->unread_message_count : 0); ?>

        <div class="chat-user-info d-flex  p-3 align-items-center customer-list view-admin-conv <?php echo e($unchecked ? 'new-msg ' : ''); ?> <?php echo e($unchecked ? 'conv-active' : ''); ?>" onclick="viewAdminConvs('<?php echo e(route('admin.message.view',['conversation_id'=>$conv->id,'user_id'=>$user->id])); ?>','customer-<?php echo e($user->id); ?>','<?php echo e($conv->id); ?>','<?php echo e($user->id); ?>')"
                id="customer-<?php echo e($user->id); ?>">
                <div class="chat-user-info-img d-none d-md-block position-relative">
                    <img class="avatar-img onerror-image"
                         src="<?php echo e($user['image_full_url']); ?>"
                         data-onerror-image="<?php echo e(dynamicAsset('assets/admin')); ?>/img/160x160/img1.jpg"
                         alt="Image Description">
                    <?php if($tab == 'customer' && !empty($user->pro_status)): ?>
                        <?php echo $__env->make('partials._pro_customer_badge', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                </div>
                <div class="chat-user-info-content">
                    <h5 class="mb-0 d-flex justify-content-between">
                        <span class=" mr-3">
                            <?php echo e($user['f_name'].' '.$user['l_name']); ?>

                            <?php if($tab == 'vendor' && $user?->vendor?->restaurants[0]): ?>
                                <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $user->vendor->restaurants[0]], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endif; ?>
                        </span> <span
                            class="<?php echo e($unchecked ? 'badge badge-info' : ''); ?>"><?php echo e($unchecked ? $unchecked : ''); ?></span>
                            <small>
                            <?php echo e(Carbon\Carbon::parse($conv->last_message?->created_at)->diffForHumans()); ?>

                                </small>
                    </h5>
                    <small> <?php echo e($user['phone']); ?></small>
                    <div class="text-title line--limit-1 text-gray1 fs-12"><?php echo e($conv->last_message?->message ? Str::limit($conv->last_message?->message ??'', 35, '...') : (count($conv->last_message?->file_full_url ?? []) > 0 ?  count($conv->last_message?->file_full_url ?? []) .' '. translate('messages.Attachments') :'' )); ?></div>
                </div>
            </div>



        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
    <div class="h-100 d-flex align-items-center justify-content-center">
        <div class="chat-user-info text-center p-3 customer-list">
            <div class="chat-user-info- d-none d-md-block mx-auto">
                <?php if($tab == 'vendor'): ?>
                    <img class="avatar- rounded-1 mb-2 mx-auto"
                         src='<?php echo e(dynamicAsset('assets/admin')); ?>/img/no-admin.png'
                         alt="Image Description">
                <?php else: ?>
                    <img class="avatar- rounded-1 mb-2 mx-auto"
                         src='<?php echo e(dynamicAsset('assets/admin')); ?>/img/no-customer.png'
                         alt="Image Description">

                <?php endif; ?>
            </div>
            <div class="chat-user-info-">
                <div class="mb-0">
                    <?php if($tab == 'vendor'): ?>
                        <span class="fs-14"><?php echo e(translate('messages.No Restaurant Found')); ?></span>
                     <?php else: ?>
                        <span class="fs-14"><?php echo e(translate('messages.No Customer Found')); ?></span>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/view-pages/common.js"></script>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/messages/data.blade.php ENDPATH**/ ?>