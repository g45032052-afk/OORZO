 @if (isset($page_data) && count($page_data) > 0)
    <div class="card card-body mb-3">
        <h4 class="text-capitalize fs-18 mb-3">
            {{ translate('messages.restaurant_info') }}
        </h4>
        <div class="row g-4 ">
            @foreach (data_get($page_data, 'data', []) as $key => $item)
                @if (!in_array($item['field_type'], ['file', 'check_box']))
                    <div class="col-sm-6">
                        <div class="form-group mb-0">
                            <label class="form-label font-regular d-flex gap-1"
                                    for="{{ $item['input_data'] }}">{{ translate($item['input_data']) }}

                                @if ($item['is_required'] == 1)
                                    <span class="text-danger">
                                        *
                                    </span>

                                @endif

                            </label>
                            <input id="{{ $item['input_data'] }}"
                                    {{ $item['is_required'] == 1 ? 'required' : '' }}
                                    data-field-name="{{ translate($item['input_data']) }}"
                                    type="{{ $item['field_type'] }}"
                                    name="additional_data[{{ $item['input_data'] }}]"
                                    class="form-control h--45px"
                                    placeholder="{{ translate($item['placeholder_data']) }}"
                                    value="">
                        </div>
                    </div>
                @elseif ($item['field_type'] == 'check_box')
                    @if ($item['check_data'] != null)
                        <div class="col-sm-6">
                            <div class="form-group mb-0">
                                <label  class="form-label font-regular d-flex gap-1" for=""> {{ translate($item['input_data']) }}
                                    @if ($item['is_required'] == 1)
                                        <span class="text-danger">
                                            *
                                        </span>
                                    @endif
                                </label>
                                @foreach ($item['check_data'] as $k => $i)
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox"
                                                    name="additional_data[{{ $item['input_data'] }}][]"
                                                    class="form-check-input"
                                                    value="{{ $i }}">
                                            {{ translate($i) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @elseif ($item['field_type'] == 'file')
                    @if ($item['media_data'] != null)
                            <?php
                            $image = '';
                            $pdf = '';
                            $docs = '';
                            if (data_get($item['media_data'], 'image', null)) {
                                $image = '.jpg, .jpeg, .png,';
                            }
                            if (data_get($item['media_data'], 'pdf', null)) {
                                $pdf = ' .pdf,';
                            }
                            if (data_get($item['media_data'], 'docs', null)) {
                                $docs = ' .doc, .docs, .docx';
                            }
                            $accept = $image.$pdf.$docs;
                            ?>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label font-semibold mb-1 d-flex gap-1"
                                        for="{{ $item['input_data'] }}">{{ translate($item['input_data']) }}
                                    @if ($item['is_required'] == 1)
                                        <span class="text-danger">
                                            *</span>

                                    @endif

                                </label>
                                <p class="fs-12 mb-0">{{ translate('messages.pdf, doc, jpg. File size : max 2 MB') }}</p>
                                {{-- <input id="{{ $item['input_data'] }}"
                                        {{ $item['is_required'] == 1 ? 'required' : '' }}
                                        data-field-name="{{ translate($item['input_data']) }}"
                                        type="{{ $item['field_type'] }}"
                                        name="additional_documents[{{ $item['input_data'] }}][]"
                                        class="form-control h--45px"
                                        placeholder="{{ translate($item['placeholder_data']) }}"
                                        {{ data_get($item['media_data'], 'upload_multiple_files', null) == 1 || data_get($item['media_data'], 'file_upload_quantity', null) > 1 ? 'multiple' : '' }}
                                        data-max-files="{{ data_get($item['media_data'], 'file_upload_quantity', 1) }}"
                                        data-max-size="{{ defined('MAX_FILE_SIZE') ? MAX_FILE_SIZE : 2 }}"
                                        accept="{{ $accept ?? '.jpg, .jpeg, .png' }}"> --}}
                               
                                <div class="single-document-uploaderwrap multiple_doc pt-3" data-document-uploader-multiple >
                                    <div>
                                        <div class="file-assets"
                                                data-picture-icon="{{ dynamicAsset('assets/admin/img/picture.svg') }}"
                                                data-document-icon="{{ dynamicAsset('assets/admin/img/document.svg') }}"
                                                data-blank-thumbnail="{{ dynamicAsset('assets/admin/img/picture.svg') }}">
                                        </div>
                                        <!-- Upload box -->
                                        <div class="doc-slider-wrapper position-relative">

                                            @php
                                                $docs_for_this_field = $additional_documents[$item['input_data']] ?? [];
                                            @endphp
                                            <div class="d-flex gap-3 pdf-container">
                                                <div class="document-upload-wrapper">
                                                    <input
                                                        type="{{ $item['field_type'] }}"
                                                        id="{{ $item['input_data'] }}"
                                                        name="additional_documents[{{ $item['input_data'] }}][]"
                                                        class="document_input"
                                                        accept="{{ $accept ??  '.jpg, .jpeg, .png, .webp'  }}"
                                                        data-max-limit="{{ data_get($item['media_data'],'file_upload_quantity') ?? 9999 }}"
                                                        data-max-filesize="2"
                                                        {{ data_get($item['media_data'], 'upload_multiple_files', null) == 1 || data_get($item['media_data'], 'file_upload_quantity', null) > 1 ? 'multiple' : '' }}
                                                    >
                                                    <div class="textbox">
                                                        <img width="40" height="40" class="svg"
                                                                src="{{ dynamicAsset('assets/admin/img/doc-uploaded.png') }}"
                                                                alt="">
                                                        <p class="fs-12 mb-0 text-center">
                                                            {{ translate('Select a file or') }}
                                                            <span class="font-semibold">{{ translate('Drag & Drop') }}</span>
                                                            {{ translate('here') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                @foreach($docs_for_this_field as $doc)
                                                    @php($path = $path ?? 'additional_documents/dm/')
                                                    @php($fileUrl = dynamicStorage('storage/app/public/' . $path . $doc['file']))
                                                    @php($fileType = strtolower(pathinfo($doc['file'], PATHINFO_EXTENSION)))
                                                    <div class="pdf-single" data-file-name="{{ $doc['file'] }}" data-file-url="{{ $fileUrl }}">
                                                        <div class="pdf-frame">
                                                            <canvas class="pdf-preview d--none"></canvas>
                                                            <img class="pdf-thumbnail" src="{{ in_array($fileType, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tif', 'tiff']) ? $fileUrl : dynamicAsset('assets/admin/img/document.svg') }}">
                                                        </div>

                                                        <div class="overlay">
                                                            <div class="pdf-info">
                                                                <img src="{{ in_array($fileType, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tif', 'tiff']) ? dynamicAsset('assets/admin/img/picture.svg') : dynamicAsset('assets/admin/img/document.svg') }}" width="34" alt="File Type Logo">
                                                                <div class="file-name-wrapper">
                                                                    <span class="file-name js-filename-truncate">{{ $doc['file'] }}</span>
                                                                    <span class="opacity-50">{{ translate('Click to view the file') }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="actions d-flex gap-2">
                                                                <button type="button" class="btn btn-circle rounded btn-danger p-0 remove-existing-doc" style="--size:26px;" data-key="{{ $item['input_data'] }}" data-file="{{ $doc['file'] }}"><i class="tio-delete-outlined"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach

                                            </div>

                                            <div>
                                                <div class="docSlide_prev">
                                                    <div class="d-flex justify-content-center align-items-center h-100">
                                                        <button type="button" class="btn btn-circle border-0 text-body bg-white shadow-sm">
                                                            <i class="tio-chevron-left fs-24"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="docSlide_next">
                                                    <div class="d-flex justify-content-center align-items-center h-100">
                                                        <button type="button" class="btn btn-circle border-0 text-body bg-white shadow-sm">
                                                            <i class="tio-chevron-right fs-24"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endforeach


        </div>
    </div>
@endif


@push('script_2')
        <script>
        $(document).on('change', 'input[name^="additional_documents"]', function() {
            const maxFiles = parseInt($(this).data('max-files')) || 1;
            const maxSize = parseInt($(this).data('max-size')) || 2; // Default 2MB
            const maxSizeBytes = maxSize * 1024 * 1024;
            const files = this.files;

            if (files.length > maxFiles) {
                toastr.error(`{{ translate('You can upload a maximum of') }} ${maxFiles} {{ translate('files') }}.`);
                $(this).val(''); // Clear the input
                return;
            }

            for (let i = 0; i < files.length; i++) {
                if (files[i].size > maxSizeBytes) {
                    toastr.error(`{{ translate('File size must be less than') }} ${maxSize}MB.`);
                    $(this).val(''); // Clear the input
                    return;
                }
            }
        });
        </script>
@endpush