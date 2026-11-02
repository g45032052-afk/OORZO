@if (isset($page_data) && count($page_data) > 0)
    @php
        $all_data = data_get($page_data, 'data', []);
        $input_fields = [];
        $file_fields = [];
        foreach ($all_data as $key => $item) {
            if ($item['field_type'] == 'file') {
                $file_fields[] = ['key' => $key, 'item' => $item];
            } else {
                $input_fields[] = ['key' => $key, 'item' => $item];
            }
        }
    @endphp

    <div class="card shadow--card-2 mt-3 col-lg-12">
        <div class="card-header">
            <div>
                <h3 class="mb-1">
                    <span>{{ translate('messages.Additional_Data') }}</span>
                </h3>
                <p class="m-0 fs-12">{{ translate('messages.Setup your additional data') }}</p>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @if (count($input_fields) > 0)
                    <div class="col-lg-12">
                        <div class="p-xxl-20 p-12 global-bg-box rounded">
                            <div class="row g-3">
                                @foreach ($input_fields as $data)
                                    @php
                                        $key = $data['key'];
                                        $item = $data['item'];
                                        $value = $additional_data[$item['input_data']] ?? '';
                                    @endphp

                                    @if (!in_array($item['field_type'], ['file', 'check_box']))
                                        <div class="col-md-4 col-12">
                                            <div class="form-group m-0">
                                                <label class="form-label" for="{{ $item['input_data'] }}">
                                                    {{ translate($item['input_data']) }} {!! $item['is_required'] == 1 ? '<span class="text-danger">*</span>' : '' !!}
                                                </label>
                                                <input
                                                    id="{{ $item['input_data'] }}"
                                                    {{ $item['is_required'] == 1 ? 'required' : '' }}
                                                    type="{{ $item['field_type'] == 'phone' ? 'tel' : $item['field_type'] }}"
                                                    name="additional_data[{{ $item['input_data'] }}]"
                                                    class="form-control h--45px"
                                                    placeholder="{{ translate($item['placeholder_data']) }}"
                                                    value="{{ old('additional_data.'.$item['input_data'], $value) }}"
                                                >
                                            </div>
                                        </div>

                                    @elseif ($item['field_type'] == 'check_box' && $item['check_data'])
                                        <div class="col-md-4 col-12">
                                            <div class="form-group m-0">
                                                <label class="form-label">{{ translate($item['input_data']) }} {!! $item['is_required'] == 1 ? '<span class="text-danger">*</span>' : '' !!}</label>
                                                @foreach ($item['check_data'] as $i)
                                                    @php
                                                        $checked = in_array($i, (array)($additional_data[$item['input_data']] ?? [])) ? 'checked' : '';
                                                    @endphp
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input type="checkbox"
                                                                   name="additional_data[{{ $item['input_data'] }}][]"
                                                                   class="form-check-input"
                                                                   value="{{ $i }}"
                                                                {{ $checked }}>
                                                            {{ translate($i) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if (count($file_fields) > 0)
                    <div class="col-lg-12">
                        <div class="p-xxl-20 p-12 global-bg-box rounded">
                            <div class="row g-3">
                                @foreach ($file_fields as $data)
                                    @php
                                        $key = $data['key'];
                                        $item = $data['item'];
                                    @endphp
                                    @if ($item['field_type'] == 'file' )
                                        @if ($item['media_data'] != null)
                                                <?php
                                                $image = '';
                                                $pdf = '';
                                                $docs = '';
                                                if (data_get($item['media_data'], 'image', null)) {
                                                    $image = '.jpg,.jpeg,.png,.webp,';
                                                }
                                                if (data_get($item['media_data'], 'pdf', null)) {
                                                    $pdf = '.pdf,';
                                                }
                                                if (data_get($item['media_data'], 'docs', null)) {
                                                    $docs = '.doc,.docs,.docx,';
                                                }
                                                $accept = trim($image.$pdf.$docs, ',');
                                                ?>
                                            <div class="col-md-12 image_count_{{ $key }}" data-id="{{ $key }}" >
                                                <div class="global-bg-box rounded mt-3">
                                                    <div class="form-group m-0">
                                                        <div class="mb-20">
                                                            <label class="form-label" for="{{ $item['input_data'] }}">{{translate($item['input_data'])  }} {!! $item['is_required'] == 1 ? '<span class="text-danger">*</span>' : '' !!}</label>
                                                            <p class="m-0 fs-12">
                                                                {{ translate('messages.pdf, doc, jpg, jpeg, png, webp. File size : max 2 MB') }}
                                                            </p>
                                                        </div>
                                                        <div class="single-document-uploaderwrap multiple_doc" data-document-uploader-multiple >
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
                                                                                {{ data_get($item['media_data'],'upload_multiple_files',null) ==  1 || data_get($item['media_data'],'file_upload_quantity') >  1 || data_get($item['media_data'],'file_upload_quantity') == null ? 'multiple' : '' }}
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

                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
