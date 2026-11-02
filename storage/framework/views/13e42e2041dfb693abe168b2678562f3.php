<?php use App\CentralLogics\Helpers; ?>
<div class="product-card card cursor-pointer quick-View" data-id="<?php echo e($product->id); ?>">
    <div class="card-header inline_product clickable p-0 initial-34 position-relative">
        <div class="thumb position-relative">
            <img class="w-100 rounded onerror-image"
                src="<?php echo e($product?->image_full_url ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                alt="Product image">
        </div>
        
    </div>

    <div class="card-body inline_product text-center px-2 py-2 clickable">
        <div class="product-title1 position-relative text-dark font-weight-bold text-capitalize">
            <?php echo e(Str::limit($product['name'], 12,'...')); ?>

        </div>
        <div class="justify-content-between text-center">
            <div class="product-price text-center">
                <div class="justify-content-between text-center">
                    <div class="product-price text-center">
                        <span class="text-accent font-weight-bold color-f8923b">
                            <?php echo e(Helpers::format_currency($product['price']-Helpers::display_discount_amount($product, $product['price'], $restaurant_data))); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/vendor-views/pos/_single_product.blade.php ENDPATH**/ ?>