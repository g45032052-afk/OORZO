"use strict";
$(document).ready(function () {

    $('[data-document-uploader-multiple]').each(function (index, wrapper) {
        const $wrapper = $(wrapper);

        const pdfContainer = $wrapper.find(".pdf-container");
        const documentUploadWrapper = $wrapper.find(".document-upload-wrapper");
        const fileAssets = $wrapper.find(".file-assets");

        const pictureIcon = fileAssets.data("picture-icon");
        const documentIcon = fileAssets.data("document-icon");
        const blankThumbnail = fileAssets.data("blank-thumbnail");

        const input = $wrapper.find(".document_input");

        const isMultiple = input.is("[multiple]");
        const isArrayName = input.attr("name").endsWith("[]");

        const maxLimit = parseInt(input.data("max-limit")) ?? 1;
        let maxFileSize = parseInt(input.data("max-filesize")) ?? 2;
        const uploadedFiles = new Map();
        console.log(input.attr("accept"));
        const acceptAttr = input.attr("accept") || ".pdf,.jpg,.png,.jpeg,.doc";
        const editInput = $(`<input type="file" accept="${acceptAttr}" class="d-none">`);
        if (isMultiple) editInput.attr("multiple", false);
        $("body").append(editInput);

        const $btnNext = $wrapper.find(".docSlide_next");
        const $btnPrev = $wrapper.find(".docSlide_prev");


        input.on("change", function () {
            const files = Array.from(this.files);
            const currentCount = pdfContainer.find(".pdf-single").length;
            const maxSizeInBytes = maxFileSize * 1024 * 1024;

            if (currentCount + files.length > maxLimit) {
                toastr.error("Maximum " + maxLimit + " files allowed.");
                return;
            }

            if (!isMultiple && !isArrayName) {
                uploadedFiles.clear();
                pdfContainer.empty();
                documentUploadWrapper.hide();
            }

            files.forEach((file) => {
                if (file.size > maxSizeInBytes) {
                    toastr.error("File size must be less than " + (maxSizeInBytes / 1024 / 1024) + "MB");
                    return;
                }

                if (!validateFileExtension(file, acceptAttr)) {
                    toastr.error(
                        "Invalid file type. Allowed: " + acceptAttr
                    );
                    return;
                }

                if (!uploadedFiles.has(file.name)) {
                    uploadedFiles.set(file.name, file);
                    createPdfItem(file);
                }
            });

            updateInputFiles();
            toggleUploadWrapper();
            checkNavOverflow(pdfContainer);
        });

        function updateInputFiles() {
            const dataTransfer = new DataTransfer();
            uploadedFiles.forEach((file) => {
                dataTransfer.items.add(file);
            });
            input[0].files = dataTransfer.files;
        }


        function createPdfItem(file) {
            const fileURL = URL.createObjectURL(file);
            const isImage = file.type.startsWith("image/");
            const iconSrc = isImage ? pictureIcon : documentIcon;

            const item = $(`
                <div class="pdf-single" data-file-name="${file.name}">
                    <div class="pdf-frame">
                        <canvas class="pdf-preview d--none"></canvas>
                        <img class="pdf-thumbnail" src="${blankThumbnail}">
                    </div>

                    <div class="overlay">
                        <div class="pdf-info">
                            <img src="${iconSrc}" width="34" alt="File Type Logo">
                            <div class="file-name-wrapper">
                                <span class="file-name js-filename-truncate">${file.name}</span>
                                <span class="opacity-50">Click to view the file</span>
                            </div>
                        </div>

                        <div class="actions d-flex gap-2">
                            <button class="btn btn-circle rounded btn--primary p-0 edit-one" style="--size:26px;"><i class="tio-edit"></i></button>
                            <button class="btn btn-circle rounded btn-danger p-0 remove-one" style="--size:26px;"><i class="tio-delete-outlined"></i></button>
                        </div>
                    </div>
                </div>
            `);

            item.data("file-url", fileURL);

            pdfContainer.append(item);
            renderFileThumbnail(item, file.type);

            checkNavOverflow(pdfContainer);
        }

        pdfContainer.on("click", ".remove-one", function (e) {
            e.stopPropagation();

            const item = $(this).closest(".pdf-single");
            const fileName = item.data("file-name");

            uploadedFiles.delete(fileName);
            item.remove();

            updateInputFiles();
            toggleUploadWrapper();
            checkNavOverflow(pdfContainer);
        });

        pdfContainer.on("click", ".edit-one", function (e) {
            e.stopPropagation();

            const item = $(this).closest(".pdf-single");
            const oldName = item.data("file-name");

            editInput.val("");
            editInput.off("change");

            editInput.one("change", function () {
                const file = this.files[0];
                if (!file) return;

                if (!validateFileExtension(file, acceptAttr)) {
                    toastr.error(
                        "Invalid file type. Allowed: " + acceptAttr
                    );
                    return;
                }

                uploadedFiles.delete(oldName);
                uploadedFiles.set(file.name, file);

                updatePdfItem(item, file);
                updateInputFiles();
                checkNavOverflow(pdfContainer);
            });

            editInput.click();
        });

        function updatePdfItem(item, file) {
            item.attr("data-file-name", file.name);
            item.data("file-url", URL.createObjectURL(file));
            item.find(".file-name").text(file.name);
            renderFileThumbnail(item, file.type);
        }

        async function renderFileThumbnail(element, fileType) {
            const fileUrl = element.data("file-url");
            const canvas = element.find(".pdf-preview")[0];
            const thumbnail = element.find(".pdf-thumbnail")[0];

            try {
                if (fileType.startsWith("image/")) {
                    thumbnail.src = fileUrl;
                } else if (fileType === "application/pdf") {
                    const ctx = canvas.getContext("2d");
                    const loadingTask = pdfjsLib.getDocument(fileUrl);
                    const pdf = await loadingTask.promise;
                    const page = await pdf.getPage(1);
                    const viewport = page.getViewport({ scale: 0.5 });

                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    await page.render({ canvasContext: ctx, viewport }).promise;
                    thumbnail.src = canvas.toDataURL();
                } else {
                    thumbnail.src = blankThumbnail;
                }
            } catch (err) {
                thumbnail.src = blankThumbnail;
            }

            $(canvas).hide();
        }


        function toggleUploadWrapper() {
            const currentCount = pdfContainer.find(".pdf-single").length;
            documentUploadWrapper.toggle(currentCount < maxLimit);
        }


        $btnNext.on("click", function () {
            let step = pdfContainer.find(".pdf-single").outerWidth(true);
            pdfContainer.animate(
                { scrollLeft: pdfContainer.scrollLeft() + step },
                300,
                () => checkNavOverflow(pdfContainer)
            );
        });

        $btnPrev.on("click", function () {
            let step = pdfContainer.find(".pdf-single").outerWidth(true);
            pdfContainer.animate(
                { scrollLeft: pdfContainer.scrollLeft() - step },
                300,
                () => checkNavOverflow(pdfContainer)
            );
        });


        pdfContainer.on("scroll", function () {
            checkNavOverflow(pdfContainer);
        });


        checkNavOverflow(pdfContainer);
        toggleUploadWrapper();

        const observer = new MutationObserver(function () {
            toggleUploadWrapper();
            checkNavOverflow(pdfContainer);
        });

        observer.observe(pdfContainer[0], { childList: true });
    });

    function checkNavOverflow($slider) {
        try {
            let $btnNext = $slider.closest(".doc-slider-wrapper").find(".docSlide_next");
            let $btnPrev = $slider.closest(".doc-slider-wrapper").find(".docSlide_prev");

            let isRTL = $("html").attr("dir") === "rtl";
            let scrollWidth = $slider[0].scrollWidth;
            let clientWidth = $slider[0].clientWidth;
            let scrollLeft = $slider.scrollLeft();

            if (isRTL) {
                let maxScrollLeft = scrollWidth - clientWidth;
                let scrollRight = maxScrollLeft - scrollLeft;

                $btnNext.toggle(scrollLeft > 0);
                $btnPrev.toggle(scrollRight > 1);
            } else {
                $btnNext.toggle(scrollLeft + clientWidth < scrollWidth - 1);
                $btnPrev.toggle(scrollLeft > 1);
            }
        } catch (e) {
            console.error("checkNavOverflow error:", e);
        }
    }

    let resizeTimer;
    $(window).on("resize", function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            $(".pdf-container").each(function () {
                checkNavOverflow($(this));
            });
        }, 200);
    });

    function validateFileExtension(file, acceptAttr) {
        if (!acceptAttr) return true;
        const allowedExtensions = acceptAttr
            .split(",")
            .map((ext) => ext.trim().toLowerCase());
        const fileName = file.name.toLowerCase();
        const fileType = file.type.toLowerCase();

        return allowedExtensions.some((allowed) => {
            if (allowed.startsWith(".")) {
                return fileName.endsWith(allowed);
            }
            if (allowed.endsWith("/*")) {
                const typeGroup = allowed.split("/")[0];
                return fileType.startsWith(typeGroup + "/");
            }
            return fileType === allowed;
        });
    }

});