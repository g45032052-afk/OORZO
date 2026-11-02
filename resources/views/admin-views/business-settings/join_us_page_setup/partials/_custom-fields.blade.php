<div class="customer-input-fields-section" id="customer-input-fields-section">

                    @if( isset($page_data)  &&  count($page_data)  > 0)

                        @foreach ( data_get($page_data,'data',[])  as $key=>$item)
                            @php($cRandomNumber = rand())
                            @php($count = $key)

                            <div class="row g-3 bg-light rounded p-12 p-xxl-20 mb-20" id="{{ $key }}">
                                <div class="col-12 border-bottom mb-2">
                                    <div class="d-flex align-items-center justify-content-end gap-3">
                                        <div class="flex-grow-1">{{ translate('messages.Field') }}: {{ $key + 1 }}</div>
                                        <div class="form-check text-start mb-0">
                                            <label class="form-check-label text-dark" for="is_required_{{ $key }}">
                                                <input type="checkbox" class="form-check-input" id="is_required_{{ $key }}" value="{{ $count }}" name="is_required[{{ $key }}]" {{ (isset($item['is_required']) && $item['is_required']) == 1 ? 'checked':'' }}> {{ translate('is_Required') }} ?
                                            </label>
                                        </div>
                                        <a class="btn action-btn btn--danger remove-input-fields-group" data-id="{{ $key }}" title="Delete" >
                                            <i class="tio-delete-outlined"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="fieldTypeSelect_{{$key}}"> {{ translate('messages.Type')}}
                                            <small class="text-danger">*</small>
                                        </label>
                                        <select  id="fieldTypeSelect_{{$key}}" class="form-control fieldTypeSelect" name="field_type[]"  data-key="{{ $key }}" required>
                                            <option  {{ $item['field_type'] ==  'text' ?  'selected' : '' }}  value="text">{{ translate('messages.Text')}}</option>
                                            <option  {{ $item['field_type'] ==  'number' ?  'selected' : '' }} value="number">{{ translate('messages.Number')}}</option>
                                            <option  {{ $item['field_type'] ==  'date' ?  'selected' : '' }} value="date">{{ translate('messages.Date')}}</option>
                                            <option {{ $item['field_type'] ==  'email' ?  'selected' : '' }}  value="email">{{ translate('messages.Email')}}</option>
                                            <option   {{ $item['field_type'] ==  'phone' ?  'selected' : '' }} value="phone">{{ translate('messages.Phone')}}</option>
                                            <option   {{ $item['field_type'] ==  'file' ?  'selected' : '' }} value="file">{{ translate('messages.File_Upload')}}</option>
                                            <option   {{ $item['field_type'] ==  'check_box' ?  'selected' : '' }} value="check_box">{{ translate('messages.Check_Box')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="input_data_{{$key}}" class="title_color">{{ translate('Input_Field_Title') }}
                                            <small class="text-danger">*</small>
                                        </label>
                                        <input  id="input_data_{{$key}}" type="text" name="input_data[]" class="form-control" placeholder="{{ translate('Ex:Enter_Input_Field_Title') }}" required value="{{ ucwords(str_replace('_',' ',$item['input_data'])) }}">
                                    </div>
                                </div>
                                <div class=" hide_place_Holder_{{ $key }}  {{ $item['field_type'] ==  'check_box' || $item['field_type'] ==  'file' ? 'd-none': "" }} col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="placeholder_data{{$key}}"  class="title_color">{{ translate('place_Holder') }}
                                            <small class="text-danger">*</small>
                                        </label>
                                        <input id="placeholder_data{{$key}}" type="text" name="placeholder_data[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('enter_name') }}"  value="{{ $item['placeholder_data'] }}">
                                    </div>
                                </div>
                            @if ($item['field_type'] == 'file' )
                                <div class="col-md-4 file_rows_data_{{ $key }}">
                                    <div class="form-group mb-0">
                                        <label class="title_color" for=""> {{ translate('File_Format') }}
                                            <small class="text-danger">*</small>
                                        </label>
                                        <div class="border rounded d-flex align-items-center flex-wrap bg-white px-3 py-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input file-format-check" {{  data_get($item['media_data'],'image')  == 1 ? 'checked' : '' }}  type="checkbox" id="inlineCheckbox1_{{ $key }}" value="{{ $key }}" name="image[{{ $key }}]">
                                                <label class="form-check-label" for="inlineCheckbox1_{{ $key }}">{{ translate('Jpg, Jpeg_or_Png') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input file-format-check" {{  data_get($item['media_data'],'pdf')  == 1 ? 'checked' : '' }}  type="checkbox" id="inlineCheckbox2_{{ $key }}" value="{{ $key }}" name="pdf[{{ $key }}]">
                                                <label class="form-check-label" for="inlineCheckbox2_{{ $key }}">{{ translate('Pdf') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input file-format-check" {{  data_get($item['media_data'],'docs')  == 1 ? 'checked' : '' }}  type="checkbox" id="inlineCheckbox3_{{ $key }}" value="{{ $key }}" name="docs[{{ $key }}]">
                                                <label class="form-check-label" for="inlineCheckbox3_{{ $key }}">{{ translate('Docs') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 file_rows_data_{{ $key }}">
                                    <div class="form-group mb-0">
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                            <label class="title_color" for=""> {{ translate('messages.Upload Limit') }}
                                                <small class="text-danger">*</small>
                                            </label>
                                            <div class="form-check mb-0">
                                                <input class="form-check-input unlimited_check" data-id="{{ $key }}" {{  data_get($item['media_data'],'upload_multiple_files')  == 1 ? 'checked' : '' }} name="upload_multiple_files[{{ $key }}]" type="checkbox" value="{{ $key }}" id="upload_multiple_files_{{ $key }}" >
                                                <label class="form-check-label" for="upload_multiple_files_{{ $key }}">
                                                    {{ translate('messages.Unlimited') }}
                                                </label>
                                            </div>
                                        </div>
                                        <input {{  data_get($item['media_data'],'upload_multiple_files')  == 1 ? '' : 'required' }} type="number" min="1" class="form-control file_upload_quantity_{{ $key }}" name="file_upload_quantity[{{ $key }}]"
                                        value="{{ data_get($item['media_data'],'file_upload_quantity') }}" placeholder="{{ translate('messages.Unlimited') }}"
                                        {{  data_get($item['media_data'],'upload_multiple_files')  == 1 ? 'disabled' : '' }} >
                                    </div>
                                </div>


                            @elseif ($item['field_type'] == 'check_box' )

                                @foreach ($item['check_data'] ?? []  as $k => $check_data)
                                    <div class="delete_{{ $key }}">
                                        <div class="row g-3 bg-light rounded p-12 p-xxl-20 mb-20" id="check_box_data_{{ $key }}_{{ $k }}">
                                            <div class="col-md-3" >
                                                <label class="form-check-label" for="">
                                                    <h6>  {{ translate('Add_Checkmark_Option') }} </h6>
                                                </label>
                                            </div>
                                            <div class="col-md-3" >
                                                <h6> {{ translate('messages.Option_Name') }}</h6>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group mb-0">
                                                    <label>
                                                        <input type="text" name="check_box_input[{{ $key }}][]" class="form-control" placeholder="{{ translate('Ex:Enter_option') }}" value="{{ $check_data }}">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-0">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        @if ($k == 0 )
                                                            <a class="btn btn-sm btn-outline-primary add-check-box"  data-id="{{ $key }}" title="{{ translate('add_new') }}" >
                                                                {{ translate("messages.Add") }} +
                                                                @else
                                                                    <a class="btn action-btn btn--danger remove-check-box" data-parent-id="{{ $key }}" data-child-id="{{ $k }}"  title="Delete" >
                                                                        <i class="tio-delete-outlined"></i>
                                                                    </a>
                                                                @endif
                                                            </a>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div id="check_box_data_{{ $key }}_{{ $key+3 *99 }}"> </div>
                            @endif
                            </div>





                            <div id="check_box_data_main_{{$key }}"></div>
                        @endforeach
                    @endif
                </div>

                @push('script_2')
    <script src="{{dynamicAsset('assets/admin/js/view-pages/join-us-page.js')}}"></script>
    <script>
        "use strict";
        let count= {{$count ?? -1 }};

        $(document).on('click', '.add-input-data-fields-group', function () {
            count++;
            let new_field = `<div class="row mb-2 mt-2 bg-light rounded p-12 p-xxl-20 mb-20" id="`+count+`" style="display: none;">
                        <div class="col-12 border-bottom mb-2">
                            <div class="d-flex align-items-center justify-content-end gap-3">
                                <div class="flex-grow-1">{{ translate('messages.Field') }}: ${count + 1}</div>
                                <div class="form-check text-start mb-0">
                                    <label class="form-check-label text-dark" for="is_required${count + 1}">
                                        <input type="checkbox" class="form-check-input" id="is_required${count + 1}" value="${count}" name="is_required[${count}]"> {{ translate('is_Required') }} ?
                                    </label>
                                </div>

                                <a class="btn action-btn btn--danger remove-input-fields-group" data-id="${count}"  title="Delete">
                                        <i class="tio-delete-outlined"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>{{ translate('messages.Type')}}
                                    <small class="text-danger">*</small>
                                </label>
                                <select class="form-control fieldTypeSelect" data-key="${count}"  name="field_type[]"  required>

                                    <option value="text">{{ translate('messages.Text')}}</option>
                                    <option value="number">{{ translate('messages.Number')}}</option>
                                    <option value="date">{{ translate('messages.Date')}}</option>
                                    <option value="email">{{ translate('messages.Email')}}</option>
                                    <option value="phone">{{ translate('messages.Phone')}}</option>
                                    <option value="check_box">{{ translate('messages.Check_Box')}}</option>
                                    <option value="file">{{ translate('messages.File_Upload')}}</option>
                                </select>
                            </div>
                        </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="title_color">{{ translate('Input_Field_Title') }}
                                <small class="text-danger">*</small>
                            </label>
                            <input type="text" name="input_data[]" class="form-control" placeholder="{{ translate('Ex:Enter_Input_Field_Title') }}" required>
                        </div>
                    </div>
                    <div class=" hide_place_Holder_${count} col-md-4">
                        <div class="form-group mb-0">
                            <label for="placeholder_data" class="title_color">{{ translate('place_Holder') }}
                                <small class="text-danger">*</small>
                            </label>
                            <input type="text" name="placeholder_data[]" class="form-control" placeholder="{{ translate('ex') }}: {{ translate('enter_name') }}" >
                        </div>
                    </div>
                </div>


                <div id="check_box_data_main_${count}"></div>
                `;

            $('#customer-input-fields-section').append(new_field);
            $('#'+count).fadeIn();
        });

        function optionSelected(data ,key) {
            let id=key;
            if(data === 'file'){
                $('#check_box_data_'+id).remove();
                $('.delete_'+id).remove();
                $('.hide_place_Holder_'+id).hide();
                $('.file_rows_data_'+id).remove();
                let new_field =
                    `
                <div class="col-md-4 file_rows_data_${id}">
                    <div class="form-group mb-0">
                        <label class="title_color" for=""> {{ translate('File_Format') }}
                            <small class="text-danger">*</small>
                        </label>
                        <div class="border rounded d-flex align-items-center flex-wrap gap-3 bg-white px-3 py-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input file-format-check"  type="checkbox" id="inlineCheckbox1_${id}" value="${id}" name="image[${id}]" checked>
                                <label class="form-check-label" for="inlineCheckbox1_${id}">{{ translate('Jpg, Jpeg_or_Png') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input file-format-check"  type="checkbox" id="inlineCheckbox2_${id}" value="${id}" name="pdf[${id}]">
                                <label class="form-check-label" for="inlineCheckbox2_${id}">{{ translate('Pdf') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input file-format-check"  type="checkbox" id="inlineCheckbox3_${id}" value="${id}" name="docs[${id}]">
                                <label class="form-check-label" for="inlineCheckbox3_${id}">{{ translate('Docs') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 file_rows_data_${id}">
                    <div class="form-group mb-0 mt-4">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <label class="title_color" for=""> {{ translate('messages.Upload Limit') }}
                                <small class="text-danger">*</small>
                            </label>
                            <div class="form-check mb-0">
                                <input class="form-check-input unlimited_check" data-id="${id}"  name="upload_multiple_files[${id}]" type="checkbox" value="${id}" id="upload_multiple_files_${id}" >
                                <label class="form-check-label" for="upload_multiple_files_${id}">
                                    {{ translate('messages.Unlimited') }}
                                </label>
                            </div>
                        </div>
                        <input required type="number" min="1" class="form-control file_upload_quantity_${id}" name="file_upload_quantity[${id}]"
                        value="" placeholder="{{ translate('messages.Unlimited') }}">
                    </div>
                </div>
            `

                $('#'+id).append(new_field);
            }else if(data === 'check_box'){

                let rand = Math.floor((Math.random() + 11 )* 999);
                let new_check_box_field =
                    `<div class="row g-3 bg-light rounded p-12 p-xxl-20 mb-20" id="check_box_data_${id}">
                    <div class="col-md-3" >
                            <label class="form-check-label" for="">
                                <h6>  {{ translate('Add_Checkmark_Option') }} </h6>
                            </label>
                    </div>
                        <div class="col-md-3" >
                            <h6> {{ translate('messages.Option_Name') }}</h6>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label>
                                <input type="text" name="check_box_input[${id}][]" class="form-control" placeholder="{{ translate('Ex:Enter_option') }}" required value="">
                                   </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-sm btn-outline-primary add-check-box"  data-id="${id}" title="{{ translate('messages.add_new') }}">
                                    {{ translate("messages.Add") }} +
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="check_box_data_${id}_${rand}"> </div>
                    `
                $('#check_box_data_main_'+id).append(new_check_box_field);
                $('.file_rows_data_'+id).remove();
                $('.hide_place_Holder_'+id).hide();
            }
            else{
                $('.file_rows_data_'+id).remove();
                $('#check_box_data_'+id).remove();
                $('.delete_'+id).remove();
                $('.hide_place_Holder_'+id).show().removeClass('d-none');
            }
        }


        function add_check_box(parent_id){
            let rand = Math.floor((Math.random() + 11 )* 999);
            let  new_check_box_field=
                `<div class="row g-3 bg-light rounded p-12 p-xxl-20 mb-20 delete_${parent_id}" id="check_box_data_${parent_id}_${rand}">
            <div class="col-md-3" >
                    <label class="form-check-label" for="">
                        <h6>  {{ translate('Add_Checkmark_Option') }} </h6>
                    </label>
            </div>
                <div class="col-md-3" >
                    <h6> {{ translate('messages.Option_Name') }}  </h6>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>
                        <input type="text" name="check_box_input[${parent_id}][]" class="form-control" placeholder="{{ translate('Ex:Enter_option') }}" required value="">
                       </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="d-flex justify-content-end gap-2">
                            <a class="btn action-btn btn--danger remove-check-box" data-parent-id="${parent_id}" data-child-id="${rand}"  title="Delete">
                                <i class="tio-delete-outlined"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            `
            $('#check_box_data_main_'+parent_id).append(new_check_box_field);
        }
        $(document).on('change', '.unlimited_check', function () {
            let id = $(this).data('id');
            if ($(this).is(':checked')) {
                $('.file_upload_quantity_'+id).attr('disabled', true);
                $('.file_upload_quantity_'+id).val('');
                $('.file_upload_quantity_'+id).attr('required', false);
            } else {
                $('.file_upload_quantity_'+id).removeAttr('disabled');
                $('.file_upload_quantity_'+id).attr('required', true);
            }
        });

        $(document).on('change', '.file-format-check', function () {
            let key = $(this).val();
            let is_checked = $(this).is(':checked');
            if (!is_checked) {
                let checked_count = 0;
                $(`input[name="image[${key}]"], input[name="pdf[${key}]"], input[name="docs[${key}]"]`).each(function() {
                    if($(this).is(':checked')) {
                        checked_count++;
                    }
                });
                if (checked_count === 0) {
                    toastr.error("{{ translate('messages.At_least_one_file_format_is_required') }}");
                    $(this).prop('checked', true);
                }
            }
        });
    </script>

@endpush
