"use strict";
$(document).on('ready', function () {


    $('#min_purchase').data('previous-value', $('#min_purchase').val());
    $('#discount').data('previous-value', $('#discount').val());


    $('#discount_type').on('change', function() {
        discount_check();
    });
    $('#discount').on('click', function() {
        discount_check();
    });
    $('#min_purchase').on('click', function() {
         discount_check();

    });
    function discount_check(){
        if($('#discount_type').val() == 'amount')
        {
            $('#max_discount').attr("readonly",true).attr("required",false).val(0);
            $('#discount').attr('max', $('#min_purchase').val() || 0);
            validateDiscount();
        }
        else
        {
            if($('#discount_type').val() == 'percent'){
                $('#max_discount').removeAttr("readonly").attr("required", true);
            }
            $('#discount').attr('max', 100);
        }
    }

    $('#date_from').attr('min',(new Date()).toISOString().split('T')[0]);
    $('#date_to').attr('min',(new Date()).toISOString().split('T')[0]);



});

$("#date_from").on("change", function () {
    $('#date_to').attr('min',$(this).val());
});

$("#date_to").on("change", function () {
    $('#date_from').attr('max',$(this).val());
});
$('#zone_wise').hide();
$('#coupon_type').on('change',function () {
    let coupon_type = $(this).val();
    coupon_type_change(coupon_type)
}).trigger('change');





function coupon_type_change(coupon_type) {
    $('#zone_wise, #restaurant_wise, #customer_wise').hide();
    $('#select_restaurant').attr("required", false);
    $('#choice_zones').attr("required", false);
    $('#select_customer').attr("required", false);

    $('#coupon_limit').attr("readonly", false).attr("required", true);
    $('#limit_for_same_user').removeClass('d-none').attr("required", true);
    $('#select_customer').val(null).trigger('change');
    switch (coupon_type) {
        case 'zone_wise':
            $('#zone_wise').show();
            $('#choice_zones').attr("required", true);
            break;

        case 'restaurant_wise':
            $('#restaurant_wise').show();
            $('#select_restaurant').attr("required", true);
            $('#customer_wise').show();
            $('#select_customer').attr("required", true);
             $('#choice_zones').attr("required", false);
            break;

        case 'first_order':
            $('#coupon_limit').val(1).attr("readonly", true).attr("required", false);
            $('#limit_for_same_user').addClass('d-none').attr("required", false);
             $('#choice_zones').attr("required", false);
            break;

        case 'pro_customer':
            $('#choice_zones').attr("required", false);
            $('#select_restaurant').attr("required", false);
            $('#select_customer').attr("required", false);
            break;

        default:
            $('#customer_wise').show();
            $('#select_customer').attr("required", true);
            $('#choice_zones').attr("required", false);
            $('#coupon_limit').val($('#coupon_limit').data('value')).attr("readonly", false).attr("required", true);
            $('#limit_for_same_user').removeClass('d-none').attr("required", true);
            break;
    }

    if (coupon_type === 'free_delivery') {
        $('#discount_type').attr("disabled", true).val("amount").trigger("change");
        $('#max_discount, #discount').val(0).attr("readonly", true).attr("required", false);
    } else {
        $('#discount_type').removeAttr("disabled").attr("required", true);
        $('#max_discount, #discount').removeAttr("readonly");
    }

    if ($('#discount_type').val() === 'amount') {
        $('#max_discount').val(0).attr("readonly", true).attr("required", false);
    } else if($('#discount_type').val() === 'percent') {
        $('#max_discount').removeAttr("readonly").attr("required", true);
    }
}

    $('#reset_btn').click(function(){

        $('#coupon_title').val('');
        $('input[name="title[]"]').val('');
        $('#coupon_type').val('restaurant_wise');
        $('#restaurant_wise').show();
        $('#zone_wise').hide();
        $('#coupon_code').val(null);
        $('#coupon_limit').val(null);
        $('#date_from').val(null);
        $('#date_to').val(null);
        $('#discount_type').val('amount');
        $('#discount').val(null);
        $('#max_discount').val(0);
        $('#min_purchase').val(0);
        $('#select_restaurant').val(null).trigger('change');
        $('#select_customer').val(null).trigger('change');
    $('#choice_zones').val(null).trigger('change');
    })


    function validateDiscount() {
        let discountType = $('#discount_type').val();
        let discountInput = $('#discount');
        let minPurchase = parseFloat($('#min_purchase').val()) || 0;
        let discountValue = parseFloat(discountInput.val()) || 0;

        if (discountType === 'amount' && discountValue > minPurchase) {
            discountInput.val(discountValue);
        }
    }
