$(document).ready(function () {
    let hasFileSizeError = false;

    window.hasFileSizeError = function () {
        return hasFileSizeError;
    };

    window.validateRequiredImages = function () {
        let isValid = true;

        $(".multi_image_picker[data-required='true']").each(function () {
            const $picker = $(this);
            const hasImage = $picker.find(".spartan_item").length > 0 || $picker.find('input[type="file"]').val();

            if (!hasImage) {
                isValid = false;

                let errorMessage = $picker.data("required-msg") || $("#text-validate-translate").data("required") || "Please select an image";
                toastr.error(errorMessage);

                $("html, body").animate(
                    { scrollTop: $picker.offset().top - 120 },
                    600
                );

                return false;
            }
        });

        return isValid;
    };

    function checkNavOverflow($picker) {
        try {
            let $btnNext = $picker.find(".imageSlide_next");
            let $btnPrev = $picker.find(".imageSlide_prev");
            let isRTL = $("html").attr("dir") === "rtl";
            let navScrollWidth = $picker[0].scrollWidth;
            let navClientWidth = $picker[0].clientWidth;
            let scrollLeft = $picker.scrollLeft();

            if (isRTL) {
                let maxScrollLeft = navScrollWidth - navClientWidth;
                let scrollRight = maxScrollLeft - scrollLeft;

                $btnNext.toggle(scrollLeft > 0);
                $btnPrev.toggle(scrollRight > 1);
            } else {
                $btnNext.toggle(
                    navScrollWidth > navClientWidth &&
                    scrollLeft + navClientWidth < navScrollWidth
                );
                $btnPrev.toggle(scrollLeft > 1);
            }
        } catch (error) {
            console.error("Error checking nav overflow:", error);
        }
    }

    $(".multi_image_picker").each(function () {
        let $picker = $(this);
        let ratio = $picker.data("ratio");
        let fieldName = $picker.data("field-name");
        let maxCount = $picker.data("max-count") || Infinity;
        let maxFileSize = $picker.data("max-filesize") ?? 2;
        let maxFileSizeBytes = maxFileSize * 1024 * 1024;
        let existingCount = $picker.data("existng-count") || 0;
        maxCount = maxCount - existingCount;

        let dropFileLabel = "";
        if ($picker.hasClass("design_two")) {
            dropFileLabel = `
                <div class="drop-label text-center">
                    <p class="fs-12 text-body mb-0 mt-1">
                        Add
                    </p>
                </div>
            `;
        } else {
            dropFileLabel = `
                <div class="drop-label text-center">
                    <h6 class="mt-1 fw-medium lh-base">
                        <span class="text-info">Click to upload</span><br>
                        or drag and drop
                    </h6>
                </div>
            `;
        }
        if (maxCount > 0) {
            let accept = $picker.data("accept");
            let allowedExt = "webp|jpg|jpeg|png|gif";
            if (accept) {
                allowedExt = accept.split(',')
                    .map(function (item) {
                        return item.trim().replace(/\./g, '');
                    })
                    .filter(function (item) {
                        return item !== "";
                    })
                    .join('|');
                allowedExtText = accept.split(',')
                    .map(function (item) {
                        return item.trim().replace(/\./g, '');
                    })
                    .filter(function (item) {
                        return item !== "";
                    })
                    .join(',');
            }

            let docImage = $picker.data("doc-image") || "";

            $picker.spartanMultiImagePicker({
                fieldName: fieldName,
                maxCount: maxCount,
                rowHeight: "100px",
                groupClassName: "",
                maxFileSize: maxFileSizeBytes,
                allowedExt: allowedExt,
                dropFileLabel: dropFileLabel,
                docImage: docImage,
                placeholderImage: {
                    image: placeholderImageUrl,
                    width: "30px",
                    height: "30px",
                },
                onAddRow: function (index) {
                    checkNavOverflow($picker);
                    setAspectRatio($picker, ratio);

                    hasFileSizeError = false;
                },
                onRemoveRow: function (index) {
                    checkNavOverflow($picker);
                    setAspectRatio($picker, ratio);
                },
                onSizeErr: function (index, file) {
                    hasFileSizeError = true;
                    let errorMessage = $("#text-validate-translate").data("file-size-larger") || "File size must be less than " + maxFileSize + "MB";
                    toastr.error(errorMessage);
                },
                onExtensionErr: function (index, file) {
                    let errorMessage = $("#text-validate-translate").data("file-validate") || "Invalid file type. Allowed: " + allowedExtText; // Fallback message
                    toastr.error(errorMessage);
                }
            });
        }


        function setAspectRatio($picker, ratio) {
            if (ratio) {
                $picker.find(".file_upload").css("aspect-ratio", ratio);
            }
        }

        $picker.find(".imageSlide_next").click(function () {
            let scrollWidth = $picker
                .find(".spartan_item_wrapper")
                .outerWidth(true);
            $picker.animate(
                { scrollLeft: $picker.scrollLeft() + scrollWidth },
                300,
                function () {
                    checkNavOverflow($picker);
                }
            );
        });

        $picker.find(".imageSlide_prev").click(function () {
            let scrollWidth = $picker
                .find(".spartan_item_wrapper")
                .outerWidth(true);
            $picker.animate(
                { scrollLeft: $picker.scrollLeft() - scrollWidth },
                300,
                function () {
                    checkNavOverflow($picker);
                }
            );
        });
    });

    let resizeTimeout;
    $(window).on("resize", function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            $(".multi_image_picker").each(function () {
                checkNavOverflow($(this));
            });
        }, 200);
    });

    $(".multi_image_picker").on("scroll", function () {
        checkNavOverflow($(this));
    });

});
