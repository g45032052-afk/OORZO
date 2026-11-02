"use strict";
//Custom Tex & Bg COlor
$(function () {
    $("[data-bg-color]").each(function () {
        let bg = $(this).attr("data-bg-color");
        if (bg) $(this).css("background-color", bg);
    });

    $("[data-text-color]").each(function () {
        let color = $(this).attr("data-text-color");
        if (color) $(this).css("color", color);
    });
});
//Custom Tex & Bg COlor

//Text Limit slect2
function truncateSelect2Choices() {
    $('.select2-selection__choice').each(function () {
        // select2 takes the title from the option's raw text, so a label blade
        // printed on its own line arrives padded with the markup's indentation.
        // Measuring that padding spends the whole budget on spaces and leaves
        // the chip showing nothing but "...", so it is trimmed off first.
        let fullText = ($(this).attr('title') || '').trim();
        if (fullText && fullText.length > 16) {

            let shortText = fullText.substring(0, 16) + '...';

            $(this).contents().filter(function() {
                return this.nodeType === 3;
            }).first().replaceWith(shortText);
        }
    });
}
$(document).on('change', 'select', function () {
    setTimeout(truncateSelect2Choices, 50);
});
$(document).on('select2:select', function () {
    setTimeout(truncateSelect2Choices, 50);
});
//Text Limit slect2

//DropDown NotClosed
$(document).on("click", ".dropdown-menu .not-closed", function (e) {
    e.stopPropagation();
});
//DropDown NotClosed


//Text limit showing
$('.text-limit-show').each(function () {
    let $t = $(this), l = $t.data('limit');
    let tx = $t.text().trim();
    let ext = tx.split('.').pop();

    if (tx.length > l && tx.includes('.')) {
        let base = tx.slice(0, l - ext.length - 3); // reserve space for "...ext"
        $t.text(base + '...' + ext);
    }
});



//GLobal language slide
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.tabs-slide-language').forEach(wrapper => {
        const container = wrapper.querySelector('.nav');
        if (!container) return;

        const btnPrevWrap = wrapper.querySelector('.button-prev');
        const btnNextWrap = wrapper.querySelector('.button-next');
        const item = wrapper.querySelector('.nav-item');

        wrapper.querySelectorAll('.nav-item').forEach(el => {
            el.style.flex = '0 0 auto';
        });
        function updateArrows() {
            const hasOverflow = container.scrollWidth > container.clientWidth;
            if (!hasOverflow) {
                btnPrevWrap?.style.setProperty('display', 'none');
                btnNextWrap?.style.setProperty('display', 'none');
                return;
            }
            const atStart = container.scrollLeft <= 0;
            const atEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - 1;
            btnPrevWrap?.style.setProperty('display', atStart ? 'none' : 'flex');
            btnNextWrap?.style.setProperty('display', atEnd ? 'none' : 'flex');
        }
        wrapper.querySelector('.btn-click-prev')?.addEventListener('click', () => {
            const itemWidth = item?.offsetWidth || 0;
            container.scrollBy({ left: -itemWidth, behavior: 'smooth' });
        });
        wrapper.querySelector('.btn-click-next')?.addEventListener('click', () => {
            const itemWidth = item?.offsetWidth || 0;
            container.scrollBy({ left: itemWidth, behavior: 'smooth' });
        });
        container.addEventListener('scroll', updateArrows);
        window.addEventListener('resize', updateArrows);
        new MutationObserver(updateArrows).observe(container, { childList: true, subtree: true });
        new ResizeObserver(updateArrows).observe(container);
        // Initial check
        updateArrows();
    });
});
//GLobal language slide

//Text max limit
function initTextMaxLimit(selector = 'input[data-maxlength], textarea[data-maxlength], input[maxlength], textarea[maxlength]') {
    const fields = document.querySelectorAll(selector);

    fields.forEach(function (field) {
        const maxLength = parseInt(field.getAttribute('data-maxlength') || field.getAttribute('maxlength'), 10);
        const counter = field.parentElement.querySelector('.text-body-light') || field.parentElement.querySelector('.text-counting');

        const updateCounter = () => {
            if (field.value.length > maxLength) {
                field.value = field.value.slice(0, maxLength);
            }
            if (counter) {
                counter.textContent = `${field.value.length}/${maxLength}`;
            }
        };

        field.addEventListener('input', updateCounter);
        updateCounter();
    });
}
document.addEventListener('DOMContentLoaded', function () {
    initTextMaxLimit();
});
//Text max limit


//Single File Upload
$(document).ready(function () {
    if ($(".upload-file").length) {
        initFileUpload();
        checkPreExistingImages();
    }
});

function initFileUpload() {
    $(document).on("change", ".single_file_input", function (e) {
        handleFileChange($(this), e.target.files[0]);
    });

    $(document).on("click", ".remove_btn", function () {
        resetFileUpload($(this).closest(".upload-file"), true);
    });

    $(document).on("click", ".edit_btn", function (e) {
        e.stopImmediatePropagation();
        let $card = $(this).closest(".upload-file");

        $card.removeClass("input-disabled");
        let $input = $card.find(".single_file_input");
        $input.trigger("click");
    });

    $(document).on("click", "button[type=reset]", function () {
        $(this)
            .closest("form")
            .find(".upload-file")
            .each(function () {
                resetFileUpload($(this));
            });
    });
}

function checkPreExistingImages() {
    $(".upload-file").each(function () {
        var $card = $(this);
        var $textbox = $card.find(".upload-file-textbox");
        var $imgElement = $card.find(".upload-file-img");
        var $removeBtn = $card.find(".remove_btn");
        let $overlay = $card.find(".overlay");

        // If there's already a valid image source
        if (
            $imgElement.attr("src") &&
            $imgElement.attr("src") !== window.location.href &&
            $imgElement.attr("src") !== ""
        ) {
            $textbox.hide();
            $imgElement.show();
            $overlay.addClass("show");
            $removeBtn.css("opacity", 1);
            $card.addClass("input-disabled");
        }
    });
}

function handleFileChange($input, file) {
    let $card = $input.closest(".upload-file");
    let $textbox = $card.find(".upload-file-textbox");
    let $imgElement = $card.find(".upload-file-img");
    let $removeBtn = $card.find(".remove_btn");
    let $overlay = $card.find(".overlay");
    $card.addClass("input-disabled");

    if (file) {
        let reader = new FileReader();
        reader.onload = function (e) {
            $textbox.hide();
            $imgElement.attr("src", e.target.result).show();
            $removeBtn.css("opacity", 1);
            $overlay.addClass("show");
        };
        reader.readAsDataURL(file);
    }
}

function resetFileUpload($card, ignoreDefault = false) {
    let $input = $card.find(".single_file_input");
    let $imgElement = $card.find(".upload-file-img");
    let $textbox = $card.find(".upload-file-textbox");
    let $removeBtn = $card.find(".remove_btn");
    let $overlay = $card.find(".overlay");
    let defaultSrc = $imgElement.data("default-src") || "";

    $input.val("");

    if (defaultSrc && !ignoreDefault) {
        $imgElement.attr("src", defaultSrc).show();
        $textbox.hide();
        $overlay.addClass("show");
        $removeBtn.css("opacity", 1);
        $card.addClass("input-disabled");
    } else {
        $imgElement.hide().attr("src", "");
        $textbox.show();
        $overlay.removeClass("show");
        $removeBtn.css("opacity", 0);
        $card.removeClass("input-disabled");
    }
}

// Image Modal
$(document).on("click", ".view_btn", function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    console.log("View button clicked");
    let $card = $(this).closest(".upload-file, .view-img-wrap");
    let $img = $card.find("img.upload-file-img");

    let actualSrc = $img.attr("data-src") || $img.attr("src");

    if (actualSrc) {
        let $modal = $(".imageModal").first();
        let $modalImg = $modal.find("img.imageModal_img");
        let $downloadBtn = $modal.find(".download_btn");

        $modalImg.attr("src", actualSrc);
        $downloadBtn.attr("href", actualSrc);

        $modal.modal("show");
    }
});

document.addEventListener("DOMContentLoaded", function () {
    let checkboxes = document.querySelectorAll(".dynamic-checkbox");
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener("click", function (event) {
            event.preventDefault();
            const checkboxId = checkbox.getAttribute("data-id");
            const imageOn = checkbox.getAttribute("data-image-on");
            const imageOff = checkbox.getAttribute("data-image-off");
            const titleOn = checkbox.getAttribute("data-title-on");
            const titleOff = checkbox.getAttribute("data-title-off");
            const textOn = checkbox.getAttribute("data-text-on");
            const textOff = checkbox.getAttribute("data-text-off");

            const isChecked = checkbox.checked;

            if (isChecked) {
                $("#toggle-status-title").empty().append(titleOn);
                $("#toggle-status-message").empty().append(textOn);
                $("#toggle-status-image").attr("src", imageOn);
                $("#toggle-status-ok-button").attr(
                    "toggle-ok-button",
                    checkboxId
                );
                $("#toggle-ok-button").attr("toggle-ok-button", checkboxId);

                console.log("Checkbox " + checkboxId + " is checked");
            } else {
                $("#toggle-status-title").empty().append(titleOff);
                $("#toggle-status-message").empty().append(textOff);
                $("#toggle-status-image").attr("src", imageOff);
                $("#toggle-status-ok-button").attr(
                    "toggle-ok-button",
                    checkboxId
                );
                $("#toggle-ok-button").attr("toggle-ok-button", checkboxId);
                console.log("Checkbox " + checkboxId + " is unchecked");
            }

            $("#toggle-status-modal").modal("show");
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    let checkboxes = document.querySelectorAll(".dynamic-checkbox-toggle");
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener("click", function (event) {
            event.preventDefault();
            const checkboxId = checkbox.getAttribute("data-id");
            const imageOn = checkbox.getAttribute("data-image-on");
            const imageOff = checkbox.getAttribute("data-image-off");
            const titleOn = checkbox.getAttribute("data-title-on");
            const titleOff = checkbox.getAttribute("data-title-off");
            const textOn = checkbox.getAttribute("data-text-on");
            const textOff = checkbox.getAttribute("data-text-off");

            const isChecked = checkbox.checked;

            if (isChecked) {
                $("#toggle-title").empty().append(titleOn);
                $("#toggle-message").empty().append(textOn);
                $("#toggle-image").attr("src", imageOn);
                $("#toggle-ok-button").attr("toggle-ok-button", checkboxId);
            } else {
                $("#toggle-title").empty().append(titleOff);
                $("#toggle-message").empty().append(textOff);
                $("#toggle-image").attr("src", imageOff);
                $("#toggle-ok-button").attr("toggle-ok-button", checkboxId);
            }

            $("#toggle-modal").modal("show");
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    let imageData = document.querySelectorAll(".remove-image");
    imageData.forEach(function (image) {
        image.addEventListener("click", function (event) {
            event.preventDefault();
            const imageId = image.getAttribute("data-id");
            const title = image.getAttribute("data-title");
            const text = image.getAttribute("data-text");

            $("#toggle-status-title").empty().append(title);
            $("#toggle-status-message").empty().append(text);
            $("#toggle-status-ok-button").attr("toggle-ok-button", imageId);
            $("#toggle-ok-button").attr("toggle-ok-button", imageId);

            $("#toggle-status-modal").modal("show");
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const langLinks = document.querySelectorAll(".lang_link");

    langLinks.forEach(function (langLink) {
        langLink.addEventListener("click", function (e) {
            console.log('triggered')

            e.preventDefault();

            let section = this.parentElement;
            while (section && section !== document.body) {
                if (section.querySelector('.nav-tabs') && section.querySelector('.lang_form')) {
                    break;
                }
                section = section.parentElement;
            }
            section = section || document;

            section.querySelectorAll(".lang_link").forEach(function (link) {
                link.classList.remove("active");
            });
            this.classList.add("active");

            section.querySelectorAll(".lang_form").forEach(function (form) {
                form.classList.add("d-none");
            });

            let form_id = this.id;
            let lang = form_id.split('-link')[0];
            let suffix = form_id.substring(form_id.indexOf('-link') + 5);

            $(section).find("#" + lang + "-form" + suffix).removeClass("d-none");
            $(section).find("#" + lang + "-form1" + suffix).removeClass("d-none");
            $(section).find("#" + lang + "-form2" + suffix).removeClass("d-none");
            $(section).find("#" + lang + "-form3" + suffix).removeClass("d-none");
            $(section).find("#" + lang + "-form4" + suffix).removeClass("d-none");

            if (lang === "default") {
                $(section).find(".default-form").removeClass("d-none");
            }
        });
    });
});

// Function to read and display the image preview
function readImageURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $(input)
                .parents(".image-box")
                .find(".preview-image")
                .attr("src", e.target.result)
                .show();
        };

        reader.readAsDataURL(input.files[0]);
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB", "TB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
}

var $imageBox = undefined;
$(document).on("change", ".image-input6", function (e) {
    if (e.target.files[0]) {
        $imageBox = $(this).parents(".image-box");
        $imageBox.find(".upload-icon, .upload-text, .upload-text2").hide();

        if (this.files[0].type.includes("image/")) {
            readImageURL(this);
        } else {
            $imageBox.find(".upload-icon, .preview-image").hide();
            $imageBox.find(".upload-text").text(this.files[0].name).show();
            $imageBox
                .find(".upload-text2")
                .text(formatFileSize(this.files[0].size))
                .show();
        }
    }

    if ($(this).val()) {
        $(this).siblings(".delete_image").css("display", "flex");
    }
});
$(document).ready(function () {

    // Use delegated events + closest()
    $(document).on("change", ".image-input", function (e) {
        if (!e.target.files[0]) return;

        let $imageBox = $(this).closest(".image-box");
        $imageBox.find(".upload-icon, .upload-text, .upload-text2").hide();

        if (this.files[0].type.startsWith("image/")) {
            readImageURL(this, $imageBox); // pass $imageBox if needed
        } else {
            $imageBox.find(".preview-image").hide();
            $imageBox.find(".upload-text")
                .text(this.files[0].name)
                .show();
            $imageBox.find(".upload-text2")
                .text(formatFileSize(this.files[0].size))
                .show();
        }

        $imageBox.find(".delete_image").css("display", "flex");
    });

    $(document).on("click", ".delete_image", function () {
        let $imageBox = $(this).closest(".image-box");

        $imageBox.find(".preview-image").attr("src", "#").hide();
        $imageBox.find(".upload-icon, .upload-text, .upload-text2").show();
        $imageBox.find(".upload-text").text($imageBox.find(".upload-text").data("text") || "Drag & drop image");
        $imageBox.find(".upload-text2").text($imageBox.find(".upload-text2").data("text") || "");
        $imageBox.find(".image-input").val("");
        $(this).hide();
    });

    // Reset button
    $('button[type="reset"]').on("click", function () {
        $(this).closest("form").find(".image-box").each(function () {
            let $box = $(this);
            $box.find(".preview-image").attr("src", "#").hide();
            $box.find(".upload-icon, .upload-text, .upload-text2").show();
            $box.find(".image-input").val("");
            $box.find(".delete_image").hide();
        });
    });
});


$("[data-slide]").on("click", function () {
    let serial = $(this).data("slide");
    $(`.tab--content .item`).removeClass("show");
    $(`.tab--content .item:nth-child(${serial})`).addClass("show");
});
$(document).ready(function () {
    $(".add-required-attribute").on("click", function () {
        let status = $(this).attr("id");
        let name = $(this).data("textarea-name");
        if ($("#" + status).is(":checked")) {
            $("#en-form ." + name).attr("required", true);
        } else {
            $("#en-form ." + name).removeAttr("required");
        }
    });
});

$(document).on("click", ".location-reload", function () {
    location.reload();
});
$(document).on("click", ".redirect-url", function () {
    location.href = $(this).data("url");
});
function readURL(input, viewer = "viewer") {
    if (input.files && input.files[0]) {
        let reader = new FileReader();

        reader.onload = function (e) {
            $("#" + viewer).attr("src", e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}

$(document).ready(function () {
    "use strict";
    $(
        ".upload-img-3, .upload-img-4, .upload-img-2, .upload-img-5, .upload-img-1, .upload-img"
    ).each(function () {
        let targetedImage = $(this).find(".img");
        let targetedImageSrc = $(this).find(".img img");
        function proPicURL(input) {
            if (input.files && input.files[0]) {
                let uploadedFile = new FileReader();
                uploadedFile.onload = function (e) {
                    targetedImageSrc.attr("src", e.target.result);
                    targetedImage.addClass("image-loaded");
                    targetedImage.hide();
                    targetedImage.fadeIn(650);
                };
                uploadedFile.readAsDataURL(input.files[0]);
            }
        }
        $(this)
            .find("input")
            .on("change", function () {
                proPicURL(this);
            });
    });

    $(".read-url").on("change", function () {
        readURL(this);
    });
});
$(document).on("ready", function () {
    $(".js-toggle-password").each(function () {
        new HSTogglePassword(this).init();
    });

    $(".js-validate").each(function () {
        $.HSCore.components.HSValidation.init($(this), {
            rules: {
                confirmPassword: {
                    equalTo: "#signupSrPassword",
                },
            },
        });
    });
    // Chart.plugins.unregister(ChartDataLabels);

    // $('.js-chart').each(function () {
    //     $.HSCore.components.HSChartJS.init($(this));
    // });

    // let updatingChart = $.HSCore.components.HSChartJS.init($('#updatingData'));
});

$(".route-alert").on("click", function () {
    let route = $(this).data("url");
    let message = $(this).data("message");
    let title = $(this).data("title");
    route_alert(route, message, title);
});
$(".set-filter").on("change", function () {
    const id = $(this).val();
    const url = $(this).data("url");
    const filter_by = $(this).data("filter");
    let nurl = new URL(url);
    nurl.searchParams.delete("page");
    nurl.searchParams.set(filter_by, id);
    location.href = nurl;
});
$(document).ready(function () {
    $(".onerror-image").on("error", function () {
        let img = $(this).data("onerror-image");
        $(this).attr("src", img);
    });

    $(".onerror-image").each(function () {
        let defaultImage = $(this).data("onerror-image");
        if ($(this).attr("src").endsWith("/")) {
            $(this).attr("src", defaultImage);
        }
    });
});

$(document).on("click", ".confirm-Status-Toggle", function () {
    let Status_toggle = $("#toggle-status-ok-button").attr("toggle-ok-button");
    if ($("#" + Status_toggle).is(":checked")) {
        $("#" + Status_toggle)
            .prop("checked", false)
            .val(0);
    } else {
        $("#" + Status_toggle)
            .prop("checked", true)
            .val(1);
    }
    $("#" + Status_toggle + "_form").submit();
});
$(document).on("click", ".confirm-Toggle", function () {
    let toggle_id = $("#toggle-ok-button").attr("toggle-ok-button");
    if ($("#" + toggle_id).is(":checked")) {
        $("#" + toggle_id).prop("checked", false);
    } else {
        $("#" + toggle_id).prop("checked", true);
    }
    $("#toggle-modal").modal("hide");

    if (toggle_id === "product_gallery") {
        if ($("#product_gallery").is(":checked")) {
            $(".access_all_products").removeClass("d-none");
        } else {
            $(".access_all_products").addClass("d-none");
        }
    }
    if (toggle_id === "product_approval") {
        if ($("#product_approval").is(":checked")) {
            $(".access_product_approval").removeClass("d-none");
        } else {
            $(".access_product_approval").addClass("d-none");
        }
    }
    if (toggle_id === "vendor_can_upload_reels") {
        if ($("#vendor_can_upload_reels").is(":checked")) {
            $("#reels_settings_box").removeClass("d-none");
        } else {
            $("#reels_settings_box").addClass("d-none");
        }
        toggleReelsSettings();
    }
    if (toggle_id === "additional_charge_status") {
        if ($("#additional_charge_status").is(":checked")) {
            $("#additional_charge_name")
                .removeAttr("readonly")
                .attr("required", true);
            $("#additional_charge")
                .removeAttr("readonly")
                .attr("required", true);
        } else {
            $("#additional_charge_name")
                .attr("readonly", true)
                .removeAttr("required");
            $("#additional_charge")
                .attr("readonly", true)
                .removeAttr("required");
        }
    }
    if (toggle_id === "cash_in_hand_overflow") {
        if ($("#cash_in_hand_overflow").is(":checked")) {
            $("#cash_in_hand_overflow_restaurant_amount")
                .removeAttr("readonly")
                .attr("required", true);
            $("#min_amount_to_pay_restaurant")
                .removeAttr("readonly")
                .attr("required", true);
            $("#min_amount_to_pay_dm")
                .removeAttr("readonly")
                .attr("required", true);
            $("#dm_max_cash_in_hand")
                .removeAttr("readonly")
                .attr("required", true);
            $("#dm_max_cash_in_hand")
                .removeAttr("readonly")
                .attr("required", true);
        } else {
            $("#cash_in_hand_overflow_restaurant_amount")
                .attr("readonly", true)
                .removeAttr("required");
            $("#min_amount_to_pay_restaurant")
                .attr("readonly", true)
                .removeAttr("required");
            $("#min_amount_to_pay_dm")
                .attr("readonly", true)
                .removeAttr("required");
            $("#dm_max_cash_in_hand")
                .attr("readonly", true)
                .removeAttr("required");
            $("#dm_max_cash_in_hand")
                .attr("readonly", true)
                .removeAttr("required");
        }
    }
    if (toggle_id === "customer_date_order_sratus") {
        if ($("#customer_date_order_sratus").is(":checked")) {
            $("#customer_order_date")
                .removeAttr("readonly")
                .attr("required", true);
        } else {
            $("#customer_order_date")
                .attr("readonly", true)
                .removeAttr("required");
        }
    }

    if (toggle_id === "free_delivery_distance_status") {
        if ($("#free_delivery_distance_status").is(":checked")) {
            $("#free_delivery_distance").removeAttr("readonly");
        } else {
            $("#free_delivery_distance").attr("readonly", true).val(null);
        }
    }

    if (toggle_id === "app_url_android_status") {
        if ($("#app_url_android_status").is(":checked")) {
            $("#app_url_android").removeAttr("readonly");
        } else {
            $("#app_url_android").attr("readonly", true);
        }
    }
    if (toggle_id === "app_url_ios_status") {
        if ($("#app_url_ios_status").is(":checked")) {
            $("#app_url_ios").removeAttr("readonly");
        } else {
            $("#app_url_ios").attr("readonly", true);
        }
    }
    if (toggle_id === "web_app_url_status") {
        if ($("#web_app_url_status").is(":checked")) {
            $("#web_app_url").removeAttr("readonly");
        } else {
            $("#web_app_url").attr("readonly", true);
        }
    }
    if (toggle_id === "new_customer_discount_status") {
        if ($("#new_customer_discount_status").is(":checked")) {
            $("#new_customer_discount_amount")
                .removeAttr("readonly")
                .attr("required", true);
            $("#new_customer_discount_amount_validity")
                .removeAttr("readonly")
                .attr("required", true);
            $("#new_customer_discount_amount_type")
                .removeAttr("disabled")
                .attr("required", true);
            $("#new_customer_discount_validity_type")
                .removeAttr("disabled")
                .attr("required", true);
        } else {
            $("#new_customer_discount_amount")
                .attr("readonly", true)
                .removeAttr("required");
            $("#new_customer_discount_amount_validity")
                .attr("readonly", true)
                .removeAttr("required");
            $("#new_customer_discount_amount_type")
                .attr("disabled", true)
                .removeAttr("required");
            $("#new_customer_discount_validity_type")
                .attr("disabled", true)
                .removeAttr("required");
        }
    }
    if (toggle_id === "customer_loyalty_point") {
        if ($("#customer_loyalty_point").is(":checked")) {
            $("#loyalty_point_exchange_rate")
                .removeAttr("readonly")
                .attr("required", true);
            $("#item_purchase_point")
                .removeAttr("readonly")
                .attr("required", true);
            $("#minimum_transfer_point")
                .removeAttr("readonly")
                .attr("required", true);
        } else {
            $("#loyalty_point_exchange_rate")
                .attr("readonly", true)
                .removeAttr("required");
            $("#item_purchase_point")
                .attr("readonly", true)
                .removeAttr("required");
            $("#minimum_transfer_point")
                .attr("readonly", true)
                .removeAttr("required");
        }
    }
    if (toggle_id === "wallet_status") {
        if ($("#wallet_status").is(":checked")) {
            $(".text-muted").removeClass("text-muted");
            $("#new_customer_discount_status").removeAttr("disabled");
            $("#add_fund_status").removeAttr("disabled");
            $("#ref_earning_status").removeAttr("disabled");
            $("#refund_to_wallet").removeAttr("disabled");
            $("#customer_add_fund_min_amount").removeAttr("disabled");

            $("#ref_earning_exchange_rate")
                .removeAttr("readonly")
                .attr("required", true);
            $("#new_customer_discount_amount")
                .removeAttr("readonly")
                .attr("required", true);
            $("#new_customer_discount_amount_validity")
                .removeAttr("readonly")
                .attr("required", true);
            $("#new_customer_discount_amount_type")
                .removeAttr("disabled")
                .attr("required", true);
            $("#new_customer_discount_validity_type")
                .removeAttr("disabled")
                .attr("required", true);
            $("#customer_add_fund_min_amount").removeAttr("disabled");
        }
        else {
            $("#new_customer_discount_status")
                .attr("disabled", true)
                .parent("label")
                .addClass("text-muted");
            $("#add_fund_status")
                .attr("disabled", true)
                .parent("label")
                .addClass("text-muted");
            $("#ref_earning_status")
                .attr("disabled", true)
                .parent("label")
                .addClass("text-muted");
            $("#refund_to_wallet")
                .attr("disabled", true)
                .parent("label")
                .addClass("text-muted");
            $("#customer_add_fund_min_amount")
                .attr("disabled", true)
                .parent("label")
                .addClass("text-muted");

            $("#ref_earning_exchange_rate")
                .attr("readonly", true)
                .removeAttr("required");
            $("#new_customer_discount_amount")
                .attr("readonly", true)
                .removeAttr("required");
            $("#new_customer_discount_amount_validity")
                .attr("readonly", true)
                .removeAttr("required");
            $("#new_customer_discount_amount_type")
                .attr("disabled", true)
                .removeAttr("required");
            $("#new_customer_discount_validity_type")
                .attr("disabled", true)
                .removeAttr("required");
        }
    }
    if (toggle_id === "ref_earning_status") {
        if ($("#ref_earning_status").is(":checked")) {
            $("#ref_earning_exchange_rate").removeAttr("disabled").attr("required", true);
            $("#new_customer_discount_status").attr("disabled", false).removeClass("text-muted");
            $("#new_customer_discount_amount").attr("disabled", false).removeClass("text-muted");
            $("#new_customer_discount_amount_validity").attr("disabled", false).removeClass("text-muted");
            $("#new_customer_discount_amount_type").attr("disabled", false).removeClass("text-muted");
            $("#new_customer_discount_validity_type").attr("disabled", false).removeClass("text-muted");

        }
        else {
            $("#ref_earning_exchange_rate").attr("disabled", true).removeAttr("required");
            $("#new_customer_discount_status").attr("disabled", true).addClass("text-muted");
            $("#new_customer_discount_amount").attr("disabled", true).addClass("text-muted");
            $("#new_customer_discount_amount_validity").attr("disabled", true).addClass("text-muted");
            $("#new_customer_discount_amount_type").attr("disabled", true).addClass("text-muted");
            $("#new_customer_discount_validity_type").attr("disabled", true).addClass("text-muted");
        }
    }

    if (toggle_id === "extra_packaging_status") {
        if ($("#extra_packaging_status").is(":checked")) {
            $("#extra_packaging_amount")
                .removeAttr("readonly")
                .attr("required", true);
        } else {
            $("#extra_packaging_amount")
                .attr("readonly", true)
                .removeAttr("required");
        }
    }
});

document.querySelectorAll('[name="search"]').forEach(function (element) {
    element.addEventListener("input", function (event) {
        if (this.value === "" && window.location.search !== "") {
            let baseUrl = window.location.origin + window.location.pathname;
            window.location.href = baseUrl;
        }
    });
});

$(document).on("click", ".print-Div", function () {
    if ($("html").attr("dir") === "rtl") {
        $("html").attr("dir", "ltr");
        let printContents = document.getElementById("printableArea").innerHTML;
        let originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        $(".initial-38-1").attr("dir", "rtl");
        window.print();
        document.body.innerHTML = originalContents;
        $("html").attr("dir", "rtl");
        location.reload();
    } else {
        let printContents = document.getElementById("printableArea").innerHTML;
        let originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }
});

document.addEventListener("DOMContentLoaded", function () {
    let modalData = document.querySelectorAll(".new-dynamic-submit-model");
    modalData.forEach(function (data) {
        data.addEventListener("click", function (event) {
            event.preventDefault();
            const dataId = data.getAttribute("data-id");
            const title = data.getAttribute("data-title");
            const text = data.getAttribute("data-text");
            const image = data.getAttribute("data-image");
            const type = data.getAttribute("data-type");
            const btn_class = data.getAttribute("data-btn_class");
            const cancel_btn_text = data.getAttribute("data-2nd_btn_text");

            $("#get-text-note").val("");
            $("#modal-title").empty().append(title);
            $("#modal-text").empty().append(text);
            $("#image-src").attr("src", image);
            $("#new-dynamic-submit-model").modal("show");
            $("#new-dynamic-ok-button").addClass("btn-outline-danger");
            $("#new-dynamic-ok-button-show").addClass("d-none");
            $("#hide-buttons").addClass("d-none");

            if (type === "delete") {
                $("#new-dynamic-ok-button").attr("toggle-ok-button", dataId);
                $("#note-data").addClass("d-none");
                $("#hide-buttons").removeClass("d-none");
            } else if (type === "pause") {
                $("#new-dynamic-ok-button").attr("toggle-ok-button", dataId);
                $("#hide-buttons").removeClass("d-none");
                $("#note-data").removeClass("d-none");
                $("#get-text-note").attr("get-text-note-id", dataId);
            } else if (type === "deny") {
                $("#new-dynamic-ok-button").attr("toggle-ok-button", dataId);
                $("#hide-buttons").removeClass("d-none");
                $("#note-data").removeClass("d-none");
                $("#get-text-note").attr("get-text-note-id", dataId);
                $("#new-dynamic-ok-button")
                    .removeClass("btn-outline-danger")
                    .addClass(btn_class);
                $("#cancel_btn_text").text(cancel_btn_text);
            } else if (type === "resume") {
                $("#new-dynamic-ok-button").attr("toggle-ok-button", dataId);
                $("#hide-buttons").removeClass("d-none");
                $("#note-data").addClass("d-none");
                $("#new-dynamic-ok-button")
                    .removeClass("btn-outline-danger")
                    .addClass(btn_class);
            } else {
                $("#note-data").addClass("d-none");
                $("#hide-buttons").addClass("d-none");
                $("#new-dynamic-ok-button-show").removeClass("d-none");
            }
        });
    });
});

$(document).on("click", ".confirm-model", function () {
    let Status_toggle = $("#new-dynamic-ok-button").attr("toggle-ok-button");
    $("#" + Status_toggle + "_form").submit();
});
$(document).on("keyup", "#get-text-note", function () {
    let text_data = $("#get-text-note").attr("get-text-note-id");
    $("#" + text_data + "_note").val($(this).val());
});

document.addEventListener("DOMContentLoaded", function () {
    const activeLink = document.querySelector(".nav-link.active");

    if (activeLink) {
        activeLink.scrollIntoView({
            behavior: "smooth",
            block: "nearest",
            inline: "center",
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    $(function () {

    const $pickers = $(".date-range-picker");

    if (!$pickers.length) return;


        $(".date-range-picker").daterangepicker({
            // "timePicker": true,
            ranges: {
                Today: [moment(), moment()],
                Yesterday: [
                    moment().subtract(1, "days"),
                    moment().subtract(1, "days"),
                ],
                "Last 7 Days": [moment().subtract(6, "days"), moment()],
                "Last 30 Days": [moment().subtract(29, "days"), moment()],
                "This Month": [
                    moment().startOf("month"),
                    moment().endOf("month"),
                ],
                "Last Month": [
                    moment().subtract(1, "month").startOf("month"),
                    moment().subtract(1, "month").endOf("month"),
                ],
            },
            // minDate: new Date(),
            // startDate: moment().startOf('hour'),
            maxDate: moment(),
            startDate: $(this).data("startDate"),
            // endDate: moment().startOf('hour').add(10, 'day'),
            endDate: $(this).data("endDate"),
            autoUpdateInput: false,
            locale: {
                cancelLabel: "Clear",
            },
            alwaysShowCalendars: true,
        });

        $(".date-range-picker").attr("placeholder", "Select date");

        $(".date-range-picker").on(
            "apply.daterangepicker",
            function (ev, picker) {
                $(this).val(
                    picker.startDate.format("MM/DD/YYYY") +
                    " - " +
                    picker.endDate.format("MM/DD/YYYY")
                );
            }
        );

        $(".date-range-picker").on(
            "cancel.daterangepicker",
            function (ev, picker) {
                $(this).val("");
            }
        );
    });
});

$(document).on("ready", function () {

    $.fn.select2DynamicDisplay = function () {
        const limit = 50;
        function updateDisplay($element) {
            var $rendered = $element
                .siblings(".select2-container")
                .find(".select2-selection--multiple")
                .find(".select2-selection__rendered");
            var $container = $rendered.parent();
            var containerWidth = $container.width();
            var totalWidth = 0;
            var itemsToShow = [];
            var remainingCount = 0;

            // Get all selected items
            var selectedItems = $element.select2("data");

            // Create a temporary container to measure item widths
            var $tempContainer = $("<div>")
                .css({
                    display: "inline-block",
                    padding: "0 15px",
                    "white-space": "nowrap",
                    visibility: "hidden",
                })
                .appendTo($container);

            // Calculate the width of items and determine how many fit
            selectedItems.forEach(function (item) {
                var $tempItem = $("<span>")
                    .text(item.text)
                    .css({
                        display: "inline-block",
                        padding: "0 12px",
                        "white-space": "nowrap",
                    })
                    .appendTo($tempContainer);

                var itemWidth = $tempItem.outerWidth(true);

                if (totalWidth + itemWidth <= containerWidth - 40) {
                    totalWidth += itemWidth;
                    itemsToShow.push(item);
                } else {
                    remainingCount = selectedItems.length - itemsToShow.length;
                    return false;
                }
            });

            $tempContainer.remove();

            const $searchForm = $rendered.find(".select2-search");

            var html = "";
            itemsToShow.forEach(function (item) {
                html += `<li class="name">
                                        <span>${item.text}</span>
                                        <span class="close-icon" data-id="${item.id}"><i class="tio-clear"></i></span>
                                        </li>`;
            });
            if (remainingCount > 0) {
                html += `<li class="ms-auto">
                                        <div class="more">+${remainingCount}</div>
                                        </li>`;
            }

            if (selectedItems.length < limit) {
                html += $searchForm.prop("outerHTML");
            }

            $rendered.html(html);

            function debounce(func, wait) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            // Attach event listener with debouncing
            $(".select2-search input").on(
                "input",
                debounce(function () {
                    const inputValue = $(this).val().toLowerCase();

                    const $listItems = $(".select2-results__options li");

                    $listItems.each(function () {
                        const itemText = $(this).text().toLowerCase();
                        $(this).toggle(itemText.includes(inputValue));
                    });
                }, 100)
            );

            $(".select2-search input").on("keydown", function (e) {
                if (e.which === 13) {
                    e.preventDefault();

                    const inputValue = $(this).val();
                    if (
                        !inputValue ||
                        itemsToShow.find((item) => item.text === inputValue) ||
                        selectedItems.find((item) => item.text === inputValue)
                    ) {
                        $(this).val("");
                        return null;
                    }

                    if (inputValue) {
                        $element.append(
                            new Option(inputValue, inputValue, true, true)
                        );
                        $element.val([...$element.val(), inputValue]);
                        $(this).val("");
                        $(".multiple-select2").select2DynamicDisplay();
                    }
                }
            });
        }
        return this.each(function () {
            var $this = $(this);

            $this.select2({
                tags: true,
                placeholder: $this.attr("placeholder") || $this.data("placeholder"),
                maximumSelectionLength: limit,
            });

            // Bind change event to update display
            $this.on("change", function () {
                updateDisplay($this);
            });

            // Initial display update
            updateDisplay($this);

            $(window).on("resize", function () {
                updateDisplay($this);
            });
            $(window).on("load", function () {
                updateDisplay($this);
            });

            // Handle the click event for the remove icon
            $(document).on(
                "click",
                ".select2-selection__rendered .close-icon",
                function (e) {
                    e.stopPropagation();
                    var $removeIcon = $(this);
                    var itemId = $removeIcon.data("id");
                    var $this2 = $removeIcon
                        .closest(".select2")
                        .siblings(".multiple-select2");
                    $this2.val(
                        $this2.val().filter(function (id) {
                            return id != itemId;
                        })
                    );
                    $this2.trigger("change");
                }
            );
        });
    };
    $(".multiple-select2").select2DynamicDisplay();

});

$(document).ready(function () {
    if ($.fn.select2DynamicDisplay) {
        $(".multiple-select2").select2DynamicDisplay();
    }
});




/*Version 8.4*/

//card item add and focus background
$(function () {
    $('.btn-number').click(function () {
        const $btn = $(this);
        const $input = $btn.closest('.input-group').find('.input-number');
        let val = parseInt($input.val()) || 1;
        const min = parseInt($input.attr('min')) || 1;
        const max = parseInt($input.data('maximum_quantity')) || 999;

        if ($btn.find('i').hasClass('tio-add')) {
            if (val < max) val++;
        } else {
            if (val > min) val--;
        }

        $input.val(val);
    });
});



//Edit Search
$(function () {
    const $searchInput = $('.edit-search-form input[name="search"]');
    const $searchWrap = $('.search-wrap-manage');

    // Show on focus
    $searchInput.on('focus', function () {
        $searchWrap.show();
    });

    // Hide on click outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.edit-search-form').length) {
            $searchWrap.hide();
        }
    });
});


//Pos Menu
// $(function () {
//     // Open mobile order panel
//     $('.pos-mobile-menu .pos-collapse-arrow').on('click', function () {
//         $('.order__pos-right__mobile').addClass('active');
//     });

//     // Close mobile order panel
//     $('.order__pos-right__mobile .pos-cross_arrow').on('click', function () {
//         $('.order__pos-right__mobile').removeClass('active');
//     });
// });

// Pos Menu
$(function () {
    // Open mobile order panel
    $('.pos-mobile-menu .pos-collapse-arrow').on('click', function () {
        $('.order__pos-right__mobile').addClass('active');
        $('body').css('overflow', 'hidden'); // lock body scroll
        $('.card-data-scrolling').css({
            'overflow-y': 'auto',
            'height': '100%' // adjust height if needed
        });
    });

    // Close mobile order panel
    $('.order__pos-right__mobile .pos-cross_arrow').on('click', function () {
        $('.order__pos-right__mobile').removeClass('active');
        $('body').css('overflow', ''); // restore scroll
        $('.card-data-scrolling').css('overflow-y', ''); // reset
    });
});

$(".action-input-no-index-event").on("click", function () {
    $(".input-no-index-sub-element").prop("checked", true);
});


//Text hide / Showing
$(document).ready(function () {
    $('.pragraph-description').each(function () {
        var $container = $(this);
        var limit = parseInt($container.data('limit')) || 350; // fallback = 350
        var $desc = $container.find('p');
        var fullText = $desc.text().trim();

        if (fullText.length > limit) {
            var shortText = fullText.substring(0, limit) + '...';
            $desc.data('full-text', fullText).text(shortText);
            $container.find('.see-more').show();
        } else {
            $container.find('.see-more').remove();
        }
    });

    $(document).on('click', '.see-more', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $link = $(this);
        var $container = $link.closest('.pragraph-description');
        var $desc = $container.find('p');
        var fullText = $desc.data('full-text');
        var limit = parseInt($container.data('limit')) || 350;

        if ($link.text().trim().toLowerCase() === 'see more') {
            $desc.text(fullText);
            $link.text('See Less');
        } else {
            $desc.text(fullText.substring(0, limit) + '...');
            $link.text('See More');
        }
    });
});

//Copy Text
$(document).on('click', '.copy-btn', function () {
    var $btn = $(this);
    var textToCopy = $btn.closest('.find-copy-text').find('.copy-this').text().trim();

    // Copy to clipboard
    var tempInput = $("<input>");
    $("body").append(tempInput);
    tempInput.val(textToCopy).select();
    document.execCommand("copy");
    tempInput.remove();

    // Add active class
    $btn.addClass('active');

    // Remove after 1 second
    setTimeout(function () {
        $btn.removeClass('active');
    }, 1000);
    // Success toaster
    toastr.success('Copied to clipboard successfully');
});

//Checked Controller
$(document).ready(function () {
    $(".order-status_controller").each(function () {
        let controller = $(this);

        // "All" checkbox change event
        controller.on("change", ".check-all", function () {
            let isChecked = $(this).prop("checked");
            controller.find(".custom-control-input").not(this).prop("checked", isChecked);
        });

        // Single checkbox change event
        controller.on("change", ".custom-control-input:not(.check-all)", function () {
            let total = controller.find(".custom-control-input:not(.check-all)").length;
            let checked = controller.find(".custom-control-input:not(.check-all):checked").length;

            controller.find(".check-all").prop("checked", total === checked);
        });
    });
});

//Custom Searh
$(document).ready(function () {
    $(".conversation-custom-search__wrap .input-group .form-control")
        .on("focus", function () {
            $(this).closest(".input-group").addClass("active");
            $(".chat-user-info__search").addClass("active");
        })
        .on("blur", function () {
            $(this).closest(".input-group").removeClass("active");
            $(".chat-user-info__search").removeClass("active");
        });
});

//Showing See More btn
$(document).ready(function () {
    $(".more-withdraw-list").each(function () {
        const $inner = $(this).find(".more-withdraw-inner");
        const $btn = $(this).find(".see__more");

        let lineHeight = parseFloat($inner.css("line-height"));
        let maxHeight = lineHeight * 3;

        if ($inner[0].scrollHeight > maxHeight) {
            $btn.show();
        } else {
            $btn.hide();
        }
    });
});




$(".custom__select-controller").each(function () {
    const $container = $(this);
    const $items = $container.find(".col-sm-6");
    const $button = $container.find(".see__more");
    const showCount = 8; // 👈 define it here

    // Initially hide extra items
    $items.slice(showCount).hide();

    // Update button text + count
    function updateButton() {
        const hiddenCount = $items.filter(":hidden").length;
        if (hiddenCount > 0) {
            $button.html(`See More <span class="count">(${hiddenCount})</span>`);
        } else {
            $button.html(`See Less`);
        }
    }

    updateButton();

    // Toggle on button click
    $button.on("click", function () {
        const hiddenCount = $items.filter(":hidden").length;

        if (hiddenCount > 0) {
            // Show all
            $items.show();
        } else {
            // Show only first 8 again
            $items.slice(showCount).hide();
        }

        updateButton();
    });

    document.addEventListener('DOMContentLoaded', function () {
        // For every .check-all checkbox
        document.querySelectorAll('.check-all').forEach(checkAll => {
            // Find all sibling checkboxes in the same container
            const container = checkAll.closest('.order-status_controller, .custom__select-controller');
            const siblings = container.querySelectorAll('input[type="checkbox"]:not(.check-all)');

            // When "All" is toggled → check/uncheck siblings
            checkAll.addEventListener('change', function () {
                siblings.forEach(chk => chk.checked = checkAll.checked);
            });

            // When any sibling changes → update the "All" checkbox
            siblings.forEach(chk => {
                chk.addEventListener('change', function () {
                    const allChecked = Array.from(siblings).every(cb => cb.checked);
                    checkAll.checked = allChecked;
                });
            });

            // Initialize on page load — keep "All" checked if all siblings are checked
            const allChecked = Array.from(siblings).every(cb => cb.checked);
            checkAll.checked = allChecked;
        });
    });
});


function offcanvas_close() {
    $('.custom-offcanvas').removeClass('open');
    $('#offcanvasOverlay').removeClass('show');
}

// File validation
document.addEventListener('DOMContentLoaded', function () {

    document.body.addEventListener('change', function (event) {

        if (event.target.classList.contains('file_validation')) {
            validateFile(event.target);
        }
    });

    function validateFile(input) {
        const file = input.files[0];

        let textDisplay = input.nextElementSibling;
        if (!textDisplay || !textDisplay.classList.contains('file-validation-message')) {
            textDisplay = document.createElement('div');
            textDisplay.className = 'file-validation-message upload-text';
            input.parentNode.insertBefore(textDisplay, input.nextSibling);
        }

        if (!input.dataset.originalText && textDisplay) {
            input.dataset.originalText = textDisplay.innerHTML;
        }

        if (!file) {
            resetUI(input, textDisplay);
            return;
        }

        const maxSizeMB = parseFloat(input.getAttribute('data-max-size')) || 2;
        const maxSizeBytes = maxSizeMB * 1024 * 1024;

        const acceptAttr = input.getAttribute('accept');
        const allowedExtensions = acceptAttr
            ? acceptAttr.split(',').map(type => type.trim().toLowerCase().replace('.', ''))
            : ['jpg', 'jpeg', 'png', 'xls', 'xlsx', 'pdf'];

        const fileExtension = file.name.split('.').pop().toLowerCase();


        if (!allowedExtensions.includes(fileExtension)) {
            showError(input, textDisplay, `Invalid file type! Allowed: ${allowedExtensions.join(', ')}`);
            return;
        }

        if (file.size > maxSizeBytes) {
            showError(input, textDisplay, `File too large! Max size: ${maxSizeMB}MB`);
            return;
        }

        // resetUI(input, textDisplay);

        showSuccess(input, textDisplay, file.name);
    }

    function showError(input, element, message) {
        input.value = '';
        input.dataset.error = "1";
        if (element) {
            element.textContent = message;
            element.classList.add('error');
            element.classList.remove('success');
            delete input.dataset.originalText;
        } else {
            alert(message);
        }
        disableSubmitButton();
    }

    function showSuccess(input, element, filename) {
        if (element) {
            const displayName = filename.length > 25 ? filename.substring(0, 25) + '...' : filename;

            element.textContent = `${displayName}`;
            element.classList.add('success');
            element.classList.remove('error');
        }
        input.dataset.error = "0";
        delete input.dataset.originalText;
        enableSubmitButton();
    }

    function resetUI(input, element) {
        if (element && input.dataset.originalText) {
            element.innerHTML = input.dataset.originalText;
            element.classList.remove('success', 'error', 'file-validation-message upload-text');
            input.dataset.error = "0";
        }
    }

    function disableSubmitButton() {
        const submitBtn = document.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
    }

    function enableSubmitButton() {
        const submitBtn = document.querySelector('button[type="submit"], input[type="submit"]');
        if (!submitBtn) return;

        const hasError = document.querySelector('.file_validation[data-error="1"]');

        submitBtn.disabled = !!hasError;
    }
});

if (typeof FormValidation === 'undefined') {
    // Form Validation
    class FormValidation {
        constructor(formSelector = '.validate-form') {
            this.formSelector = formSelector;
            this.init();
        }

        init() {
            document.addEventListener('DOMContentLoaded', () => {
                this.attachValidators();
                this.initPasswordValidation();
                this.addRequiredAsterisks();
            });
        }

        attachValidators() {
            const forms = document.querySelectorAll(this.formSelector);
            forms.forEach(form => {
                if (form.dataset.validationInitialized === "true") return;

                form.setAttribute('novalidate', true);

                form.addEventListener('submit', (e) => {
                    if (!FormValidation.validateForm(form)) {
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }

                    // Already in flight. A second submit - double click, Enter pressed twice, an
                    // impatient click while the request is out - is dropped rather than sent.
                    if (form.dataset.submitting === 'true') {
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }

                    // Ajax forms manage their own button and restore it on response, so they are
                    // left alone; locking here would fight that and never unlock on failure.
                    if (form.classList.contains('global-ajax-form')) {
                        return;
                    }

                    // Deferred so every other submit handler on this form has run first. If one
                    // of them called preventDefault to take the request over, the page is not
                    // navigating and the button has to stay usable.
                    setTimeout(() => {
                        if (e.defaultPrevented) {
                            return;
                        }
                        FormValidation.lockSubmit(form);
                    }, 0);
                });

                // Use event delegation for dynamic inputs
                form.addEventListener('input', (e) => {
                    if (e.target.matches('input, textarea, select')) {
                        FormValidation.validateInput(e.target);
                    }
                });
                form.addEventListener('change', (e) => {
                    if (e.target.matches('input, textarea, select')) {
                        FormValidation.validateInput(e.target);
                    }
                });

                // Listen for select2 events using jQuery
                $(form).on('select2:select select2:unselect', function (e) {
                    FormValidation.validateInput(e.target);
                });

                form.dataset.validationInitialized = "true";
            });
        }

        initPasswordValidation() {
            let passwordInput = document.getElementById("signupSrPassword");
            if (!passwordInput) {
                passwordInput = document.getElementById("passwordWithRules");
            }

            if (!passwordInput) return;

            let rulesContainer = document.getElementById("password-rules");

            if (!rulesContainer) {
                // Ensure there's a hidden div that stores dynamic texts for the password rules
                let textsDiv = document.getElementById('password-rules-texts');
                if (!textsDiv) {
                    textsDiv = document.createElement('div');
                    textsDiv.id = 'password-rules-texts';
                    textsDiv.style.display = 'none';
                    textsDiv.setAttribute('data-length', '7+ characters');
                    textsDiv.setAttribute('data-lower', 'Lowercase letter');
                    textsDiv.setAttribute('data-upper', 'Uppercase letter');
                    textsDiv.setAttribute('data-number', 'Number');
                    textsDiv.setAttribute('data-symbol', 'Symbol');
                    document.body.appendChild(textsDiv);
                }

                const lengthText = textsDiv.getAttribute('data-length') || '7+ characters';
                const lowerText = textsDiv.getAttribute('data-lower') || 'Lowercase letter';
                const upperText = textsDiv.getAttribute('data-upper') || 'Uppercase letter';
                const numberText = textsDiv.getAttribute('data-number') || 'Number';
                const symbolText = textsDiv.getAttribute('data-symbol') || 'Symbol';

                rulesContainer = document.createElement('div');
                rulesContainer.id = 'password-rules';
                rulesContainer.className = 'gap-1 mt-2 small list-unstyled text-muted';
                rulesContainer.style.display = 'none';
                rulesContainer.innerHTML = `
                <ul class="fs-12 d-flex flex-wrap gap-1 list-unstyled row-gap-0">
                    <li class="mt-0" id="rule-length"><i class="text-danger">&#10060;</i> ${lengthText}</li>
                    <li class="mt-0" id="rule-lower"><i class="text-danger">&#10060;</i> ${lowerText}</li>
                    <li class="mt-0" id="rule-upper"><i class="text-danger">&#10060;</i> ${upperText}</li>
                    <li class="mt-0" id="rule-number"><i class="text-danger">&#10060;</i> ${numberText}</li>
                    <li class="mt-0" id="rule-symbol"><i class="text-danger">&#10060;</i> ${symbolText}</li>
                </ul>
            `;
                const container = passwordInput.closest('.form-group') || passwordInput.parentNode;
                container.appendChild(rulesContainer);
            }

            const rules = {
                length: rulesContainer.querySelector("#rule-length"),
                lower: rulesContainer.querySelector("#rule-lower"),
                upper: rulesContainer.querySelector("#rule-upper"),
                number: rulesContainer.querySelector("#rule-number"),
                symbol: rulesContainer.querySelector("#rule-symbol"),
            };

            passwordInput.addEventListener("input", function () {
                const val = passwordInput.value;

                if (val.length > 0) {
                    rulesContainer.style.display = "block";
                } else {
                    rulesContainer.style.display = "none";
                }

                FormValidation.updateRule(rules.length, val.length >= 8);
                FormValidation.updateRule(rules.lower, /[a-z]/.test(val));
                FormValidation.updateRule(rules.upper, /[A-Z]/.test(val));
                FormValidation.updateRule(rules.number, /\d/.test(val));
                FormValidation.updateRule(rules.symbol, /[!@#$%^&*(),.?":{}|<>]/.test(val));
            });

            passwordInput.addEventListener("blur", function () {
                if (passwordInput.value.length === 0) {
                    rulesContainer.style.display = "none";
                }
            });
        }

        static updateRule(element, isValid) {
            if (!element) return;
            const icon = element.querySelector("i");
            if (icon) {
                icon.className = isValid ? "text-success" : "text-danger";
                icon.innerHTML = isValid ? "&#10004;" : "&#10060;"; // ✓ or ✗
            }
        }

        static validateForm(form) {
            let isValid = true;
            const inputs = form.querySelectorAll('input, textarea, select');

            inputs.forEach(input => {
                if (!FormValidation.validateInput(input)) {
                    isValid = false;
                }
            });

            return isValid;
        }

        static validateInput(input) {
            if (input.type === 'hidden' || input.disabled) return true;

            let isValid = true;
            let errorMessage = '';

            FormValidation.clearError(input);

            if (input.type === 'file') {
            } else if (input.hasAttribute('required') && !input.value.trim()) {
                isValid = false;
                errorMessage = 'This field is required.';
            }

            else if (input.type === 'email' && input.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value.trim())) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address.';
                }
            }

            else if (input.hasAttribute('pattern') && input.value.trim()) {
                const pattern = new RegExp(input.getAttribute('pattern'));
                if (!pattern.test(input.value.trim())) {
                    isValid = false;
                    errorMessage = input.getAttribute('title') || 'Please enter a valid phone number.';
                }
            }

            if (!isValid) {
                FormValidation.showError(input, errorMessage);
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
                if (input.type !== 'checkbox' && input.type !== 'radio') {
                    input.classList.add('is-valid');
                }
            }

            return isValid;
        }

        addRequiredAsterisks() {
            const forms = document.querySelectorAll(this.formSelector);
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
                inputs.forEach(input => {
                    const id = input.getAttribute('id');
                    let label;
                    if (id) {
                        label = form.querySelector(`label[for="${id}"]`);
                    }
                    if (!label) {
                        const formGroup = input.closest('.form-group');
                        if (formGroup) {
                            label = formGroup.querySelector('label');
                        }
                    }

                    if (label) {
                        if (label.innerHTML.indexOf('*') === -1) {
                            label.insertAdjacentHTML('beforeend', ' <span class="text-danger">*</span>');
                        }
                    }
                });
            });
        }

        static showError(input, message) {
            const inputGroup = input.closest('.input-group');
            let targetElement = inputGroup ? inputGroup : input;

            // Check for select2
            if (input.tagName === 'SELECT' && $(input).hasClass('select2-hidden-accessible')) {
                const select2Container = $(input).next('.select2-container');
                if (select2Container.length) {
                    targetElement = select2Container[0];
                }
            }

            const formGroup = input.closest('.form-group');
            let container = formGroup ? formGroup : targetElement.closest('div');

            if (!container || container === targetElement) {
                container = targetElement.parentNode;
            }

            // Handle floating--date-inner groups
            if (container.closest('.floating--date-inner')) {
                container = container.closest('.floating--date-inner').parentNode;
                let errorWrapper = container.querySelector('.group-error-wrapper');
                if (!errorWrapper) {
                    errorWrapper = document.createElement('div');
                    errorWrapper.className = 'group-error-wrapper d-flex gap-3 flex-wrap';
                    container.appendChild(errorWrapper);
                }
                container = errorWrapper;

                const inputName = input.getAttribute('name');
                let existingErrorForThisInput = container.querySelector(`.form-validation-error[data-for="${inputName}"]`);

                if (!existingErrorForThisInput && container.children.length > 0) {
                    return;
                }
            }

            const inputName = input.getAttribute('name');
            let errorDiv = container.querySelector(`.form-validation-error[data-for="${inputName}"]`);

            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'form-validation-error text-danger small';
                errorDiv.setAttribute('data-for', inputName);
                container.appendChild(errorDiv);
            }

            errorDiv.textContent = message;
            $(errorDiv).hide().fadeIn(200);
        }

        static clearError(input) {
            const formGroup = input.closest('.form-group');
            const container = formGroup ? formGroup : input.parentNode;

            const inputName = input.getAttribute('name');
            const errorDiv = container.querySelector(`.form-validation-error[data-for="${inputName}"]`);

            if (errorDiv) {
                errorDiv.remove();
            }
        }
    }
    /**
     * Disable a form's submit control and show a spinner while the page navigates.
     *
     * A settings save is a full page POST, so without this the button stays live for the whole
     * round trip and a second click sends the whole form again.
     */
    FormValidation.lockSubmit = function (form) {
        const btn = form.querySelector('button[type="submit"], input[type="submit"]');

        if (!btn || btn.disabled) {
            return;
        }

        form.dataset.submitting = 'true';
        btn.disabled = true;

        const label = btn.dataset.loadingText || 'Processing...';
        const spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ';

        if (btn.tagName === 'INPUT') {
            btn.dataset.originalLabel = btn.value;
            btn.value = label;
        } else {
            btn.dataset.originalLabel = btn.innerHTML;
            btn.innerHTML = spinner + label;
        }
    };

    FormValidation.unlockSubmit = function (form) {
        const btn = form.querySelector('button[type="submit"], input[type="submit"]');

        delete form.dataset.submitting;

        if (!btn || btn.dataset.originalLabel === undefined) {
            return;
        }

        btn.disabled = false;

        if (btn.tagName === 'INPUT') {
            btn.value = btn.dataset.originalLabel;
        } else {
            btn.innerHTML = btn.dataset.originalLabel;
        }

        delete btn.dataset.originalLabel;
    };

    // Coming back to a locked form through the browser's back button restores it from the
    // bfcache exactly as it was left - button disabled, spinner still turning. Unlock on every
    // pageshow; a freshly loaded page has nothing locked, so this is a no-op there.
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form[data-submitting="true"]').forEach(function (form) {
            FormValidation.unlockSubmit(form);
        });
    });

    window.FormValidation = FormValidation;
    window.formValidation = new FormValidation();
}




// Global Ajax Form
$(document).on('submit', '.global-ajax-form', function (e) {
    e.preventDefault();

    const form = this;

    if (window.FormValidation && !window.FormValidation.validateForm(form)) {
        return;
    }

    const formData = new FormData(form);
    const submitBtn = $(form).find('button[type="submit"]');
    const originalBtnText = submitBtn.html();

    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

    $.ajax({
        url: $(form).attr('action'),
        method: $(form).attr('method'),
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.errors) {
                response.errors.forEach(error => {
                    toastr.error(error.message || error);
                });
            } else {
                toastr.success(response.message || 'Submitted successfully!');

                if (response.redirect) {
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1000);
                } else if (response.reload) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            }
        },
        error: function (xhr) {
            let errorMessage = 'Something went wrong!';

            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                for (const field in errors) {
                    const input = form.querySelector(`[name="${field}"]`) || form.querySelector(`[name="${field}[]"]`);
                    const errorMsg = errors[field][0];

                    if (input && window.FormValidation && !input.classList.contains('no-validation-message')) {
                        window.FormValidation.showError(input, errorMsg);
                        input.classList.add('is-invalid');
                    }

                    toastr.error(errorMsg);
                }
                return;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            toastr.error(errorMessage);
        },
        complete: function () {
            submitBtn.prop('disabled', false).html(originalBtnText);
        }
    });
});


// File Upload Validator
(function () {

    if (typeof window.FileUploadValidator !== "undefined") {
        return;
    }

    class FileUploadValidator {
        constructor(inputElement, options = {}) {
            this.config = {
                maxSize: 2,
                allowedTypes: ['webp', 'jpg', 'jpeg', 'png', 'gif'],
                errorElementId: null,
                required: true,
                ...options
            };

            this.input = inputElement;

            if (!this.input) {
                console.error('File input element not found');
                return;
            }

            this.errorElement = this.initErrorElement();
            this.attachEventListeners();
        }

        initErrorElement() {
            if (this.config.errorElementId) {
                return document.getElementById(this.config.errorElementId);
            }

            const parentDiv = this.input.parentElement;
            const outerWrapper = parentDiv.parentElement;

            if (outerWrapper && outerWrapper.classList.contains('error-wrapper')) {
                outerWrapper.classList.remove('error-wrapper');
            }

            let errorElement = parentDiv.nextElementSibling;

            if (!errorElement || !errorElement.classList.contains('file-upload-error')) {
                errorElement = document.createElement('span');
                errorElement.className = 'file-upload-error';
                errorElement.style.cssText = 'color: #dc3545; font-size: 0.875rem; display: none; margin-top: 0.25rem;';

                parentDiv.parentNode.insertBefore(errorElement, parentDiv.nextSibling);
            }

            return errorElement;
        }

        attachEventListeners() {
            this.input.addEventListener('change', (e) => {
                if (!this.validate()) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    if (!this.input.files || this.input.files.length === 0) {
                        // No file chosen (e.g. the file dialog was cancelled). Clear any
                        // stale preview so the box matches the "Please select a file" warning
                        // instead of leaving the previously previewed image on screen.
                        this.removePreview();
                    } else {
                        this.showInvalidIcon();
                        this.input.value = '';
                    }
                    return;
                }
                this.validate();
                if (this.input.files && this.input.files.length === 0) {
                    this.removePreview();
                }
                const parentDiv = this.input.closest('.upload-file');
                if (!parentDiv) return;
                const textbox = parentDiv.querySelector('.upload-file-textbox');
                if (textbox) {
                    const overlay = parentDiv.querySelector('.overlay');
                    if (overlay) overlay.style.display = 'none';
                }
                let overlay = parentDiv.querySelector('.overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'overlay';
                    parentDiv.appendChild(overlay);
                }
                overlay.style.display = 'block';


            });
        }

        showInvalidIcon() {
            const parentDiv = this.input.closest('.upload-file');
            if (!parentDiv) return;

            const textbox = parentDiv.querySelector('.upload-file-textbox');
            const imgElement = parentDiv.querySelector('.upload-file-img');
            const removeBtn = parentDiv.querySelector('.remove_btn');
            const overlay = parentDiv.querySelector('.overlay');

            if (textbox) textbox.style.display = 'none';
            if (imgElement) {
                const invalidIcon = parentDiv.dataset.invalidIcon;
                if (invalidIcon) {
                    setTimeout(() => {
                        imgElement.src = invalidIcon;
                        imgElement.style.display = 'block';
                        imgElement.style.objectFit = 'contain';
                        imgElement.style.width = '25%';
                    }, 100);
                }
            }
            if (removeBtn) removeBtn.style.opacity = 1;
            if (overlay) overlay.classList.add('show');

            parentDiv.classList.add('input-disabled');
        }

        removePreview() {
            const parentDiv = this.input.closest('.upload-file');
            if (!parentDiv) return;

            this.input.value = '';

            const textbox = parentDiv.querySelector('.upload-file-textbox');
            if (textbox) {
                textbox.style.display = 'block';
                textbox.style.visibility = 'visible';
                textbox.style.opacity = '1';
            }

            const uploadFileImg = parentDiv.querySelector('.upload-file-img');
            if (uploadFileImg) {
                uploadFileImg.src = uploadFileImg.dataset.defaultSrc || '';
                uploadFileImg.style.display = 'none';
            }


            const overlay = parentDiv.querySelector('.overlay');
            if (overlay) overlay.style.display = 'none';
            overlay.classList.remove('show');


            const label = parentDiv.querySelector('.upload-file__wrapper');
            if (label) label.style.pointerEvents = 'auto';

            const previews = parentDiv.querySelectorAll('.preview-image, .image-preview, .upload-preview, img.preview, [data-preview]:not(.overlay)');
            previews.forEach(el => el.remove());
        }

        clearError() {
            if (this.errorElement) {
                this.errorElement.textContent = '';
                this.errorElement.style.display = 'none';
            }
            this.input.classList.remove('is-invalid');
        }

        showError(message) {
            if (this.errorElement) {
                this.errorElement.textContent = message;
                this.errorElement.style.display = 'block';
            }
            this.input.classList.add('is-invalid');
            return false;
        }

        validate() {
            this.clearError();

            if (!this.input.files || this.input.files.length === 0) {
                if (this.config.required) {
                    return this.showError('Please select a file');
                }
                return true;
            }

            const file = this.input.files[0];

            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (!this.config.allowedTypes.includes(fileExtension)) {
                return this.showError(`Invalid file type. Allowed: ${this.config.allowedTypes.join(', ')}`);
            }

            const fileSizeMB = file.size / (1024 * 1024);
            if (fileSizeMB > this.config.maxSize) {
                return this.showError(`File size must be less than ${this.config.maxSize}MB. Current: ${fileSizeMB.toFixed(2)}MB`);
            }

            return true;
        }

        clear() {
            this.input.value = '';
            this.clearError();
            this.removePreview();
        }

        static initByClass(className, options = {}) {
            const inputs = document.querySelectorAll(`.${className}`);
            const validators = [];

            inputs.forEach(input => {
                if (input.dataset.fileValidatorInitialized === "true") return;

                let maxSize = options.maxSize || 2;
                if (input.dataset.maxSize) {
                    maxSize = parseFloat(input.dataset.maxSize);
                }

                let allowedTypes = options.allowedTypes || ['webp', 'jpg', 'jpeg', 'png', 'gif'];

                if (input.dataset.allowedTypes) {
                    allowedTypes = input.dataset.allowedTypes.split(',').map(t => t.trim());
                } else if (input.hasAttribute('accept')) {
                    const acceptTypes = input.getAttribute('accept')
                        .split(',')
                        .map(type => type.trim().replace(/^\./, '').toLowerCase())
                        .filter(type => type.length > 0);

                    if (acceptTypes.length > 0) {
                        allowedTypes = acceptTypes;
                    }
                }

                const elementOptions = {
                    maxSize: maxSize,
                    allowedTypes: allowedTypes,
                    required: input.hasAttribute('required')
                };

                validators.push(new FileUploadValidator(input, elementOptions));
                input.dataset.fileValidatorInitialized = "true";
            });

            return validators;
        }

        static validateAll(validators) {
            let allValid = true;
            validators.forEach(validator => {
                if (validator && !validator.validate()) {
                    allValid = false;
                }
            });
            return allValid;
        }
    }

    function initDynamicValidators() {
        const newValidators = FileUploadValidator.initByClass('single_file_input', {
            allowedTypes: ['webp', 'jpg', 'jpeg', 'png', 'gif']
        });
        if (newValidators && newValidators.length > 0) {
            window.fileValidators = (window.fileValidators || []).concat(newValidators);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initDynamicValidators();

        const observer = new MutationObserver((mutations) => {
            let hasNewInputs = false;
            mutations.forEach(mutation => {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) { // Element node
                            if (node.classList && node.classList.contains('single_file_input')) {
                                hasNewInputs = true;
                            } else if (node.querySelector && node.querySelector('.single_file_input')) {
                                hasNewInputs = true;
                            }
                        }
                    });
                }
            });
            if (hasNewInputs) {
                initDynamicValidators();
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });

        $(document).on('submit', 'form', function (e) {
            const form = this;
            if (window.fileValidators && window.fileValidators.length > 0) {
                const formValidators = window.fileValidators.filter(validator =>
                    form.contains(validator.input)
                );

                if (formValidators.length > 0 && !FileUploadValidator.validateAll(formValidators)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }
        });
    });

    $(document).on('click', '.upload-file__wrapper', function (e) {
        const input = this.parentElement.querySelector('input[type="file"]');
        if (input) {
            input.click();
        }
    });

    window.validateFileInputs = function () {
        return FileUploadValidator.validateAll(window.fileValidators || []);
    };
    window.FileUploadValidator = FileUploadValidator;
})();

$(document).ready(function () {
    //----- sticky footer
    function checkFooterState() {
        const $footer = $('.footer-sticky');
        const scrollPosition = $(window).scrollTop() + $(window).height();
        const documentHeight = $(document).height();

        if (scrollPosition >= documentHeight - 100) {
            $footer.addClass('not-active');
        } else {
            $footer.removeClass('not-active');
        }
    }

    $(window).on('scroll', checkFooterState);
    checkFooterState();
});


// --- Tooltip show on modal/offcanvas ---
$(document).on('shown.bs.modal', function (event) {
    const modal = $(event.target);
    modal.find('[data-toggle="tooltip"]').tooltip({
        container: modal
    });
});
$(document).on('shown.bs.offcanvas', function (event) {
    const offcanvas = $(event.target);
    offcanvas.find('[data-toggle="tooltip"]').tooltip({
        container: offcanvas
    });
});

// --- View Details ---
$(".view-btn").on("click", function () {
    var container = $(this).closest(".view-details-container");
    var details = container.find(".view-details");
    var icon = $(this).find("i");

    $(this).toggleClass("active");
    details.slideToggle(300);
    icon.toggleClass("rotate-180deg");
});
$(".section-toggle").on("change", function () {
    if ($(this).is(':checked')) {
        $(this).closest(".view-details-container").find(".view-details").slideDown(300);
    } else {
        $(this).closest(".view-details-container").find(".view-details").slideUp(300);
    }
});


/*-- Multiple Slect Custom --*/
$(document).ready(function () {
    $('.multi-select-container').each(function () {
        const $container = $(this);
        const $selectBox = $container.find('.select-box');
        const $dropdownList = $container.find('.dropdown-list');
        const $tagsContainer = $container.find('.tags-container');
        const $fullDayCheckbox = $container.find('.full-day-checkbox');
        const $slotCheckboxes = $container.find('.slot-checkbox');

        $selectBox.on('click', function (e) {
            $dropdownList.toggle();
            e.stopPropagation();
        });

        const createTag = (text, $checkbox) => {
            const $tag = $('<div>', { class: 'tag', text: text });
            const $removeBtn = $('<span>', { class: 'remove-tag', text: '×' });

            $removeBtn.on('click', function (e) {
                e.stopPropagation();
                $checkbox.prop('checked', false);
                updateUI();
            });

            $tag.append($removeBtn);
            $tagsContainer.append($tag);
        };

        const updateUI = () => {
            $tagsContainer.empty();

            if ($fullDayCheckbox.is(':checked')) {
                $slotCheckboxes.prop('checked', false).prop('disabled', true);
                createTag('Full Day', $fullDayCheckbox);

                $fullDayCheckbox.closest('.option-item').addClass('active');
                $slotCheckboxes.closest('.option-item').removeClass('active');
            } else {
                $slotCheckboxes.prop('disabled', false);

                $fullDayCheckbox.closest('.option-item').removeClass('active');

                $slotCheckboxes.each(function () {
                    const $checkbox = $(this);
                    const $optionItem = $checkbox.closest('.option-item');

                    if ($checkbox.is(':checked')) {
                        createTag($checkbox.attr('data-name'), $checkbox);
                        $optionItem.addClass('active'); // add active
                    } else {
                        $optionItem.removeClass('active'); // remove active
                    }
                });
            }
        };

        $fullDayCheckbox.on('change', updateUI);
        $slotCheckboxes.on('change', updateUI);

        $(document).on('click', function (e) {
            if (!$container.is(e.target) && $container.has(e.target).length === 0) {
                $dropdownList.hide();
            }
        });

        // Initial render
        updateUI();
    });
});


function initSeeMoreToggle(limit = 200) {

    $('.product-description').each(function () {
        const $desc = $(this);
        const fullText = $.trim($desc.text());

        const $link = $desc.next('.see-more');
        const moreText = $link.data('more') || 'See More';

        if (fullText.length > limit) {
            const shortText = fullText.substring(0, limit) + '...';

            $desc
                .data('full-text', fullText)
                .data('short-text', shortText)
                .text(shortText);

            $link
                .removeClass('is-expanded')
                .text(moreText)
                .show();
        } else {
            $link.remove();
        }
    });

    $(document)
        .off('click', '.see-more')
        .on('click', '.see-more', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $link = $(this);
            const $desc = $link.prev('.product-description');

            const moreText = $link.data('more') || 'See More';
            const lessText = $link.data('less') || 'See Less';

            if (!$link.hasClass('is-expanded')) {
                // Expand
                $desc.text($desc.data('full-text'));
                $link
                    .addClass('is-expanded')
                    .text(lessText);
            } else {
                // Collapse
                $desc.text($desc.data('short-text'));
                $link
                    .removeClass('is-expanded')
                    .text(moreText);
            }
        });
}



document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.custom_invalid_message').forEach(function (el) {

        el.addEventListener('invalid', function () {
            if (!el.value || (Array.isArray(el.value) && el.value.length === 0)) {
                const msg = el.dataset.invalidText || 'This field is required';
                el.setCustomValidity(msg);
            } else {
                el.setCustomValidity('');
            }
        });

        el.addEventListener('change', function () {
            el.setCustomValidity('');
        });

    });

});


//Multile select with ajax
$(document).ready(function () {
    function updateCounter($select) {
        let $wrap = $select.closest(".counter__unique-wrap");
        if (!$wrap.length) return;

        let $container = $select.next(".select2-container");
        if (!$container.length) return;

        let $rendered = $container.find(".select2-selection__rendered");
        let $choices = $rendered.find(".select2-selection__choice");

        if (!$choices.length) return;

        let parentWidth = $rendered.innerWidth();
        let usedWidth = 0;
        let hiddenCount = 0;

        $choices.show();

        $choices.each(function () {

            let $item = $(this);
            usedWidth += $item.outerWidth(true);

            if (usedWidth > parentWidth - 60) {
                hiddenCount++;
                $item.hide();
            }

        });

        // Counter remove
        $rendered.find(".select2-overflow-count").remove();

        // Counter add BEFORE search input
        if (hiddenCount > 0) {

            let $search = $rendered.find(".select2-search--inline");

            let $counter = $(`
                <li class="select2-overflow-count">
                    <span>+${hiddenCount}</span>
                </li>
            `);

            if ($search.length) {
                $search.before($counter);
            } else {
                $rendered.append($counter);
            }

        }
    }

    function initCounterSelect2($select) {

        if ($select.data("counter-init")) return;
        $select.data("counter-init", true);

        $select.on("select2:select select2:unselect change", function () {
            setTimeout(() => updateCounter($select), 50);
        });

        $(window).on("resize", function () {
            updateCounter($select);
        });

        setTimeout(() => updateCounter($select), 200);

    }

    // on select2 events
    $(document).on("select2:open select2:select select2:unselect", function () {

        $(".counter__unique-wrap select.select2-hidden-accessible").each(function () {
            initCounterSelect2($(this));
        });

    });

    // First load
    setTimeout(function () {
        $(".counter__unique-wrap select").each(function () {
            initCounterSelect2($(this));
        });
    }, 500);
});


//Select2 Inside search add custom placeholder
$(document).ready(function () {
    $(document).on('select2:open', function (e) {
        let selectElement = $(e.target);
        let searchPlaceholder = selectElement.data('search-placeholder') || '';
        // attribute
        $('.select2-container--open .select2-dropdown .select2-search__field').attr('placeholder', searchPlaceholder);
        selectElement.next('.select2-container').find('.select2-selection--multiple .select2-search__field').attr('placeholder', searchPlaceholder);
    });
});


$(document).ready(function() {
    //bulk-import-custom containers
    $(".bulk-import-custom input[type='file']").on("change", function() {

        const fileInput = this;
        const fileName = fileInput.files.length > 0 ? fileInput.files[0].name : '';

        const $container = $(fileInput).closest(".bulk-import-custom");
        const $textSpan = $container.find(".upload-text");

        if (fileName) {
            $textSpan.text(fileName);
        } else {
            $textSpan.text('');
        }
    });
});

class LinkValidator {
    constructor(selector = '[data-link-validation], .js-link-validator') {
        this.selector = selector;
        this.defaultMessage = "Please enter a valid link.";
        this.allowedProtocols = ["http:", "https:"];
        this.bindEvents();
        this.initExistingFields();
    }

    bindEvents() {
        $(document).on("change input", this.selector, (event) => {
            this.validateField(event.currentTarget);
            this.updateFormSubmitState(event.currentTarget);
        });

        $(document).on("submit", "form", (event) => {
            if (!this.validateForm(event.currentTarget)) {
                event.preventDefault();
                this.updateFormSubmitState(event.currentTarget);
            }
        });
    }

    initExistingFields() {
        document.querySelectorAll(this.selector).forEach((field) => {
            this.prepareFeedback(field);
            this.validateField(field);
            this.updateFormSubmitState(field);
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute("required");

        this.prepareFeedback(field);

        if (!value.length) {
            if (isRequired) {
                this.showError(field, this.getMessage(field));
                return false;
            }

            this.clearError(field);
            return true;
        }

        try {
            const parsedUrl = new URL(value);
            const isValidProtocol = this.allowedProtocols.includes(parsedUrl.protocol);
            const hasHost = parsedUrl.hostname && parsedUrl.hostname.includes(".");

            if (!isValidProtocol || !hasHost) {
                this.showError(field, this.getMessage(field));
                return false;
            }
        } catch (error) {
            this.showError(field, this.getMessage(field));
            return false;
        }

        this.clearError(field);
        return true;
    }

    prepareFeedback(field) {
        const nextSibling = field.nextElementSibling;

        if (nextSibling && nextSibling.classList.contains("invalid-feedback")) {
            return nextSibling;
        }

        const feedback = document.createElement("div");
        feedback.className = "invalid-feedback";
        feedback.textContent = this.getMessage(field);
        field.insertAdjacentElement("afterend", feedback);

        return feedback;
    }

    showError(field, message) {
        const feedback = this.prepareFeedback(field);
        field.classList.add("is-invalid");
        feedback.textContent = message;
    }

    clearError(field) {
        field.classList.remove("is-invalid");
    }

    getMessage(field) {
        return field.dataset.linkValidationMessage || this.defaultMessage;
    }

    validateForm(form) {
        let isValid = true;

        form.querySelectorAll(this.selector).forEach((field) => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    updateFormSubmitState(element) {
        const form = element instanceof HTMLFormElement ? element : element.closest("form");

        if (!form) {
            return;
        }

        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"], .call-demo[type="button"]');

        if (!submitButtons.length) {
            return;
        }

        const hasInvalidField = Array.from(form.querySelectorAll(this.selector)).some((field) =>
            field.classList.contains("is-invalid")
        );

        submitButtons.forEach((button) => {
            button.disabled = hasInvalidField;
        });
    }
}

document.addEventListener("DOMContentLoaded", function () {
    window.linkValidator = new LinkValidator();
});

$(document).ready(function() {
    function checkNavOverflow() {
        try {
            $(".nav--tab").each(function() {
                let $nav = $(this);
                let $btnNext = $nav
                    .closest(".position-relative")
                    .find(".nav--tab__next");
                let $btnPrev = $nav
                    .closest(".position-relative")
                    .find(".nav--tab__prev");
                let isRTL = $("html").attr("dir") === "rtl";
                let navScrollWidth = $nav[0].scrollWidth;
                let navClientWidth = $nav[0].clientWidth;
                let scrollLeft = Math.abs($nav.scrollLeft());

                if (isRTL) {
                    let maxScrollLeft = navScrollWidth - navClientWidth;
                    let scrollRight = maxScrollLeft - scrollLeft;

                    $btnNext.toggle(scrollRight > 1);
                    $btnPrev.toggle(scrollLeft > 1);
                } else {
                    $btnNext.toggle(
                        navScrollWidth > navClientWidth &&
                            scrollLeft + navClientWidth < navScrollWidth
                    );
                    $btnPrev.toggle(scrollLeft > 1);
                }
            });
        } catch (error) {
            console.error(error);
        }
    }

    $(".nav--tab").each(function() {
        let $nav = $(this);
        let $activeItem = $nav.find(".nav-link.active");

        if ($activeItem.length) {
            let isRTL = $("html").attr("dir") === "rtl";
            let nav = $nav[0];
            let activeItem = $activeItem[0];

            let navRect = nav.getBoundingClientRect();
            let itemRect = activeItem.getBoundingClientRect();

            let offset = itemRect.left - navRect.left - 60;

            nav.scrollLeft += offset;

            checkNavOverflow($nav);
        }

        $(window).on("resize", function() {
            checkNavOverflow($nav);
        });

        $nav.on("scroll", function() {
            checkNavOverflow($nav);
        });

        $nav.siblings(".nav--tab__next").on("click", function() {
            let scrollWidth = $nav.find("li").outerWidth(true);
            let isRTL = $("html").attr("dir") === "rtl";

            if (isRTL) {
                $nav.animate(
                    { scrollLeft: $nav.scrollLeft() - scrollWidth },
                    300,
                    function() {
                        checkNavOverflow($nav);
                    }
                );
            } else {
                $nav.animate(
                    { scrollLeft: $nav.scrollLeft() + scrollWidth },
                    300,
                    function() {
                        checkNavOverflow($nav);
                    }
                );
            }
        });

        $nav.siblings(".nav--tab__prev").on("click", function() {
            let scrollWidth = $nav.find("li").outerWidth(true);
            let isRTL = $("html").attr("dir") === "rtl";

            if (isRTL) {
                $nav.animate(
                    { scrollLeft: $nav.scrollLeft() + scrollWidth },
                    300,
                    function() {
                        checkNavOverflow($nav);
                    }
                );
            } else {
                $nav.animate(
                    { scrollLeft: $nav.scrollLeft() - scrollWidth },
                    300,
                    function() {
                        checkNavOverflow($nav);
                    }
                );
            }
        });
    });
});

/**
 * Global toast notification.
 * @param {string} type         success | info | warning | danger
 * @param {string} [title]       optional heading (falls back to a default per type)
 * @param {string} [description] optional body text (row omitted when empty)
 * @param {Object} [options]     optional { duration, onClose }
 * @returns {jQuery} the toast element
 */
function new_tostar(type, title, description, options) {
    var TYPES = {
        success: { icon: 'tio-checkmark-circle', title: 'Success' },
        info: { icon: 'tio-info', title: 'Info' },
        warning: { icon: 'tio-warning', title: 'Warning' },
        danger: { icon: 'tio-clear-circle', title: 'Error' }
    };

    if (type === 'error') type = 'danger';
    type = TYPES[type] ? type : 'success';
    options = options || {};
    var duration = options.duration || 6000;
    var heading = (title === undefined || title === null || title === '') ? TYPES[type].title : title;

    var container = document.getElementById('app-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'app-toast-container';
        container.className = 'app-toast-container';
        document.body.appendChild(container);
    }

    var $toast = $(
        '<div class="app-toast app-toast--' + type + '" role="alert" aria-live="polite">' +
            '<div class="app-toast__track"><div class="app-toast__progress"></div></div>' +
            '<div class="app-toast__body">' +
                '<div class="app-toast__icon"><i class="' + TYPES[type].icon + '"></i></div>' +
                '<div class="app-toast__content">' +
                    '<h3 class="app-toast__title"></h3>' +
                    (description ? '<p class="app-toast__text"></p>' : '') +
                '</div>' +
                '<button type="button" class="app-toast__close" aria-label="Close"><i class="tio-clear"></i></button>' +
            '</div>' +
        '</div>'
    );

    // set as text to avoid HTML injection
    $toast.find('.app-toast__title').text(heading);
    if (description) {
        $toast.find('.app-toast__text').text(description);
    }

    $(container).append($toast);
    $toast.hide().fadeIn(250);
    $toast.find('.app-toast__progress').css('width', '100%').animate({ width: '0%' }, duration, 'linear');

    var dismissed = false;
    var timer = setTimeout(dismiss, duration);

    function dismiss() {
        if (dismissed) return;
        dismissed = true;
        clearTimeout(timer);
        $toast.stop(true, true).fadeOut(200, function () {
            $toast.remove();
        });
        if (typeof options.onClose === 'function') {
            options.onClose();
        }
    }

    $toast.find('.app-toast__close').on('click', dismiss);

    return $toast;
}
window.new_tostar = new_tostar;
