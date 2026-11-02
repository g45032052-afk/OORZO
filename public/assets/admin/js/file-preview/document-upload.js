
"use strict";
$(document).ready(function () {
    $('[data-document-uploader]').each(function (index, wrapper) {
        const $wrapper = $(wrapper);
        const pdfContainer = $wrapper.find(".pdf-container");
        const documentUploadWrapper = $wrapper.find(".document-upload-wrapper");
        const fileAssets = $wrapper.find(".file-assets");
        const pictureIcon = fileAssets.data("picture-icon");
        const documentIcon = fileAssets.data("document-icon");
        const blankThumbnail = fileAssets.data("blank-thumbnail");
        const input = $wrapper.find(".document_input");
        const editBtn = $wrapper.find(".doc_edit_btn");
        const uploadedFiles = new Map();

        input.on("change", function () {
            const files = Array.from(this.files);
            const isMultiple = this.hasAttribute("multiple");
            const isArrayName = this.name.endsWith("[]");
            const MAX_FILES = isMultiple || isArrayName ? 5 : 1;
            const currentFiles = pdfContainer.find(".pdf-single").length;

            if (currentFiles + files.length > MAX_FILES) return;

            if (!isMultiple && !isArrayName && files.length > 0) {
                uploadedFiles.clear();
                pdfContainer.find(".pdf-single").remove();
                documentUploadWrapper.hide();
            }

            files.forEach((file) => {
                if (!uploadedFiles.has(file.name)) {
                    uploadedFiles.set(file.name, file);

                    const fileURL = URL.createObjectURL(file);
                    const fileType = file.type;
                    const iconSrc = fileType.startsWith("image/") ? pictureIcon : documentIcon;

                    const pdfSingle = $(`
                        <div class="pdf-single" data-file-name="${file.name}" data-file-url="${fileURL}">
                            <div class="pdf-frame">
                                <canvas class="pdf-preview d--none"></canvas>
                                <img class="pdf-thumbnail" src="${blankThumbnail}" alt="File Thumbnail">
                            </div>
                            <div class="overlay">
                                <div class="pdf-info">
                                    <img src="${iconSrc}" width="34" alt="File Type Logo">
                                    <div class="file-name-wrapper">
                                        <span class="file-name js-filename-truncate">${file.name}</span>
                                        <span class="opacity-50">Click to view the file</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);

                    pdfContainer.append(pdfSingle);
                    renderFileThumbnail(pdfSingle, fileType);
                }
            });

            toggleUploadWrapper(MAX_FILES);
        });

        pdfContainer.on("click", ".pdf-single", function () {
            const fileURL = $(this).data("file-url");
            window.open(fileURL, "_blank");
        });

        function toggleUploadWrapper(max = 5) {
            const currentFiles = pdfContainer.find(".pdf-single").length;
            documentUploadWrapper.toggle(currentFiles < max);
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

                $(thumbnail).show();
                $(canvas).hide();
            } catch (error) {
                console.error("Error rendering file thumbnail:", error);
            }
        }

        // Edit Button Logic
        editBtn.on("click", function () {
            input.one("change", function (e) {
                const files = Array.from(this.files);
                if (files.length > 0) {
                    pdfContainer.find(".pdf-single").remove();
                    uploadedFiles.clear();
                    documentUploadWrapper.hide();
                    $(this).trigger("change");
                } else {
                    this.value = "";
                }
            });

            input.val("");
            input[0].click();
        });

        // Optional form submission example
        $wrapper.closest("form").on("submit", function (e) {
            const formData = new FormData(this);
            uploadedFiles.forEach((file, fileName) => {
                const isArray = input.attr("name").endsWith("[]");
                const fieldName = isArray ? "documents[]" : "document";
                formData.append(fieldName, file, fileName);
            });
        });
    });
});
