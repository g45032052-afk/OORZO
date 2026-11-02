@extends('layouts.admin.app')
@section('title', translate('Invoice_Setup'))

@section('content')

    <div class="content">
        <form action="{{ route('admin.business-settings.invoice-setup') }}" method="POST" enctype="multipart/form-data" id="invoice_setup_form">
            @csrf
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-header-title text-capitalize">
                            {{translate('Invoice_Setup') }}
                        </h1>
                    </div>
                </div>
                <!-- End Page Header -->

                <div class="d-flex flex-wrap gap-3 justify-content-center align-items-start">
                    <div class="card flex-grow-1">
                        <div class="card-header d-block">
                            <h3 class="mb-1">{{ translate('Editor') }}</h3>
                            <p class="fs-12 mb-0">{{ translate('Manage the following template, edit and view the changes made.') }}</p>
                        </div>
                        <div class="card-body">
                            <div class="card card-body mb-20">
                                <div class="view-details-container">
                                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap flex-sm-nowrap">
                                        <div>
                                            <h4 class="mb-1">{{ translate('Logo on invoice') }}</h4>
                                            <p class="mb-0 fs-12">
                                                {{ translate('Enable the option to update the logo that appears on invoices') }}
                                            </p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <div
                                                class="view-btn active order-sm-0 order-3 fz--14px text-primary cursor-pointer text-decoration-underline font-semibold d-flex align-items-center gap-1">
                                                {{ translate('view') }}
                                                <i class="tio-chevron-down"></i>
                                            </div>
                                            <label class="toggle-switch toggle-switch-sm m-0">
                                                <input type="checkbox" class="toggle-switch-input" name="invoice_logo_status" id="logo-switch" data-target="#preview-logo" value="1" {{ isset($data['invoice_logo_status']) && $data['invoice_logo_status'] == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <div class="toggle-switch-indicator"></div>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="view-details mt-3" style="display: block;">
                                        <div class="__bg-F8F9FC-card">
                                            <div class="mb-20">
                                                <label class="input-label text-capitalize d-inline-flex align-items-center">
                                                    {{ translate('Choose how to display the logo') }}
                                                </label>
                                                <div class="resturant-type-group border bg-white">
                                                    <label class="form-check form--check mr-2 mr-md-4">
                                                        <input class="form-check-input" type="radio" value="business_logo" name="invoice_logo_type" id="business_logo" {{ (!isset($data['invoice_logo_type']) || $data['invoice_logo_type'] === 'business_logo') ? 'checked' : '' }}>
                                                        <span class="form-check-label">
                                                            {{ translate('Use Business Logo') }}
                                                        </span>
                                                    </label>
                                                    <label class="form-check form--check mr-2 mr-md-4">
                                                        <input class="form-check-input" type="radio" value="upload_new_logo" name="invoice_logo_type" id="upload_new_logo" {{ (isset($data['invoice_logo_type']) && $data['invoice_logo_type'] === 'upload_new_logo') ? 'checked' : '' }}>
                                                        <span class="form-check-label">
                                                            {{ translate('Upload New') }}
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="upload_new_logo_wrapper text-center">
                                                <div class="mb-4">
                                                    <label for="logo-input">
                                                        <h5 class="mb-1">
                                                            {{ translate('Logo') }}
                                                        </h5>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <p class="mb-0 fs-12 gray-dark">{{ translate('Upload your Business Logo') }}</p>
                                                </div>
                                                <div class="text-center">
                                                    @include('admin-views.partials._image-uploader', [
                                                    'id' => 'upload-new-logo-input',
                                                    'name' => 'invoice_logo',
                                                    'ratio' => '3:1',
                                                    'isRequired' => false ,
                                                    'existingImage' => isset($data['invoice_logo']) ? dynamicStorage('storage/app/public/business/' . $data['invoice_logo']) : null,
                                                    'imageExtension' => IMAGE_EXTENSION,
                                                    'imageFormat' => IMAGE_FORMAT,
                                                    'maxSize' => MAX_FILE_SIZE,
                                                ])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-body mb-20">
                                <div class="view-details-container">
                                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap flex-sm-nowrap">
                                        <div>
                                            <h4 class="mb-1">{{ translate('Business Identity') }}</h4>
                                            <p class="mb-0 fs-12">
                                                {{ translate('Select your business identity type from the provided options') }}
                                            </p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <div
                                                class="view-btn active order-sm-0 order-3 fz--14px text-primary cursor-pointer text-decoration-underline font-semibold d-flex align-items-center gap-1">
                                                {{ translate('view') }}
                                                <i class="tio-chevron-down"></i>
                                            </div>
                                            <label class="toggle-switch toggle-switch-sm m-0">
                                                <input type="checkbox" class="toggle-switch-input" name="business_identity_status" id="identity-switch" data-target="#tax_id_text,#bin_number_text,#musak_text" value="1" {{ isset($data['business_identity_status']) && $data['business_identity_status'] == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <div class="toggle-switch-indicator"></div>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="view-details mt-3" style="display: block;">
                                        <div class="__bg-F8F9FC-card">
                                            <div class="mb-20">
                                                <label class="input-label text-capitalize d-inline-flex align-items-center">
                                                    {{ translate('Choose Business Identity') }}
                                                </label>
                                                <div class="resturant-type-group border bg-white">
                                                    <label class="form-check form--check mr-2 mr-md-4">
                                                        <input class="form-check-input" type="radio" value="tax" name="business_identity_type" id="tex_id" {{ (!isset($data['business_identity_type']) || $data['business_identity_type'] === 'tax') ? 'checked' : '' }}>
                                                        <span class="form-check-label">
                                                            {{ translate('Tax Id') }}
                                                        </span>
                                                    </label>
                                                    <label class="form-check form--check mr-2 mr-md-4">
                                                        <input class="form-check-input" type="radio" value="bin" name="business_identity_type" id="bin_number" {{ (isset($data['business_identity_type']) && $data['business_identity_type'] === 'bin') ? 'checked' : '' }}>
                                                        <span class="form-check-label">
                                                            {{ translate('Bin Number') }}
                                                        </span>
                                                    </label>
                                                    <label class="form-check form--check mr-2 mr-md-4">
                                                        <input class="form-check-input" type="radio" value="musak" name="business_identity_type" id="musak" {{ (isset($data['business_identity_type']) && $data['business_identity_type'] === 'musak') ? 'checked' : '' }}>
                                                        <span class="form-check-label">
                                                            {{ translate('Musak') }}
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="tax_input_wrapper d--none">
                                                <label class="input-label text-capitalize d-inline-flex align-items-center gap-1">
                                                    {{ translate('Tax Number') }} <span class="text-danger">*</span>
                                                </label>
                                                <input name="tax_number" type="number" class="form-control" id="tax_number_input" placeholder="{{ translate('Type your Tax Number') }}" value="{{ $data['tax_number'] ?? '' }}">
                                            </div>
                                            <div class="bin_input_wrapper d--none">
                                                <label class="input-label text-capitalize d-inline-flex align-items-center gap-1">
                                                    {{ translate('Bin Number') }} <span class="text-danger">*</span>
                                                </label>
                                                <input name="bin_number" type="number" class="form-control" id="bin_number_input" placeholder="{{ translate('Type your Bin Number') }}" value="{{ $data['bin_number'] ?? '' }}">
                                            </div>
                                            <div class="musak_input_wrapper d--none">
                                                <label class="input-label text-capitalize d-inline-flex align-items-center gap-1">
                                                    {{ translate('Musak') }} <span class="text-danger">*</span>
                                                </label>
                                                <input name="musak_number" type="number" class="form-control" id="musak_input" placeholder="{{ translate('Type your Musak') }}" value="{{ $data['musak_number'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-body mb-20">
                                <div class="view-details-container">
                                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap flex-sm-nowrap">
                                        <div>
                                            <h4 class="mb-1">{{ translate('Terms & Condition on invoice') }}</h4>
                                            <p class="mb-0 fs-12">
                                                {{ translate('Enable the option to update the Terms & Condition that appears on invoices') }}
                                            </p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <div
                                                class="view-btn active order-sm-0 order-3 fz--14px text-primary cursor-pointer text-decoration-underline font-semibold d-flex align-items-center gap-1">
                                                {{ translate('view') }}
                                                <i class="tio-chevron-down"></i>
                                            </div>
                                            <label class="toggle-switch toggle-switch-sm m-0">
                                                <input type="checkbox" class="toggle-switch-input" name="terms&condition_status" id="condition-switch" data-target=".terms-condition-text-wrapper" value="1" {{ isset($data['terms&condition_status']) && $data['terms&condition_status'] == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <div class="toggle-switch-indicator"></div>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="view-details mt-3" style="display: block;">
                                        <div class="__bg-F8F9FC-card">
                                            <label for="" class="input-label text-capitalize d-inline-flex align-items-center">
                                                {{ translate('Write Terms & Condition') }}
                                                <span class="input-label-secondary text--title ml-1" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Provide the Terms & Conditions content that will be shown only on the invoice.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <textarea name="terms&condition_content" id="terms_condition" rows="2" class="form-control" maxlength="100" placeholder="{{ translate('Type here') }}">{{ $data['terms&condition_content'] ?? '' }}</textarea>
                                            <div class="d-flex justify-content-end mt-1">
                                                <span class="text-body-light fs-12">0/100</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-body">
                                <div class="view-details-container">
                                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap flex-sm-nowrap">
                                        <div>
                                            <h4 class="mb-1">{{ translate('Show Copyright Text on invoice') }}</h4>
                                            <p class="mb-0 fs-12">
                                                {{ translate('Enable the option to update the Copyright Text that appears on invoices') }}
                                            </p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <div
                                                class="view-btn active order-sm-0 order-3 fz--14px text-primary cursor-pointer text-decoration-underline font-semibold d-flex align-items-center gap-1">
                                                {{ translate('view') }}
                                                <i class="tio-chevron-down"></i>
                                            </div>
                                            <label class="toggle-switch toggle-switch-sm m-0">
                                                <input type="checkbox" class="toggle-switch-input" name="copyright_text_status" id="copyright-switch" value="1" data-target="#copyright-text" {{ isset($data['copyright_text_status']) && $data['copyright_text_status'] == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <div class="toggle-switch-indicator"></div>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="view-details mt-3" style="display: block;">
                                        <div class="__bg-F8F9FC-card">
                                            <label for="" class="input-label text-capitalize d-inline-flex align-items-center">
                                                {{ translate('Write Copyright Text') }}
                                                <span class="input-label-secondary text--title ml-1" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Provide the Terms & Conditions content that will be shown only on the invoice.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <textarea name="copyright_text_content" id="copyright" rows="2" class="form-control" maxlength="100" placeholder="{{ translate('Type here') }}">{{ $data['copyright_text_content'] ?? '' }}</textarea>
                                            <div class="d-flex justify-content-end mt-1">
                                                <span class="text-body-light fs-12">0/100</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card max-w-400px">
                        <div class="card-header d-block">
                            <h3 class="mb-1">{{ translate('Invoice Preview') }}</h3>
                            <p class="fs-12 mb-0">{{ translate('Preview of the invoice') }}</p>
                        </div>
                        <div class="card-body">
                            <div class="bg-light rounded-10 p-3 p-xxl-20">
                                <div class="bg-white rounded p-3 fs-10">
                                    <div class="text-center mb-3">
                                        @php($businessLogoUrl = (new \App\Models\DataSetting)->invoice_logo)
                                        <img 
                                            id="preview-logo"
                                            src="{{ $businessLogoUrl }}"
                                            data-default="{{ dynamicAsset('assets/admin/img/100x100/1.png') }}"
                                            class="w-40px aspect-1 object-cover rounded mb-1"
                                            >
                                        <div class="text--black mb-1">Hungry Puppets</div>
                                        <div class="text--black">{{ translate('House') }}: 00, Road: 00, Test City</div>
                                        <div id="tax_id_text" class="text-muted mt-1 {{ (isset($data['business_identity_type']) && $data['business_identity_type'] != 'tax') ? 'd--none' : '' }}">{{ translate('Tax Id') }} : {{ $data['tax_number'] ?? '8494646894' }}</div>
                                        <div id="bin_number_text" class="text-muted mt-1 {{ (!isset($data['business_identity_type']) || $data['business_identity_type'] != 'bin') ? 'd--none' : '' }}">{{ translate('Bin Number') }} : {{ $data['bin_number'] ?? '8494646894' }}</div>
                                        <div id="musak_text" class="text-muted mt-1 {{ (!isset($data['business_identity_type']) || $data['business_identity_type'] != 'musak') ? 'd--none' : '' }}">{{ translate('Musak') }} : {{ $data['musak_number'] ?? '8494646894' }}</div>
                                    </div>
                                    <div>
                                        <div class="d-flex justify-content-between text--black mb-2">
                                            <span>{{ translate('Order Type') }}</span>
                                            <span>{{ translate('Home Delivery') }}</span>
                                        </div>
                                        <div class="border-dashed-gray rounded px-2">
                                            <table class="table table-borderless table-sm fs-10 mb-0 overflow-wrap-anywhere">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-nowrap">{{ translate('Order ID') }}</td>
                                                        <td class="text-right text--black">100157</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-nowrap">{{ translate('Customer Name') }}</td>
                                                        <td class="text-right text--black">Victor Shoaga</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-nowrap">{{ translate('Phone') }}</td>
                                                        <td class="text-right text--black">+8**********</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-nowrap">{{ translate('Delivery Address') }}</td>
                                                        <td class="text-right text--black">
                                                            7953 Oakland St. Honolulu, HI 96815
                                                            <br>
                                                            Street no: 02, House: 23a, floor: 4
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="table-responsive pb-0">
                                        <table class="table table-borderless table-sm fs-10 mb-0 overflow-wrap-anywhere">
                                            <tbody>
                                                <tr>
                                                    <td class="text--black">{{ translate('QTY') }}</td>
                                                    <td class="text--black">{{ translate('Item') }}</td>
                                                    <td class="text--black text-right">{{ translate('Price') }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="border-top-dashed-gray"></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text--black">2x</td>
                                                    <td class="text--black">
                                                        <div>grilled lemon herb Mediterranean chicken salad</div>
                                                        <div>Variation :</div>
                                                        <div class="text-muted">Small : $ 220.00</div>
                                                        <div class="text-muted">Medium : $ 320.00</div>
                                                    </td>
                                                    <td class="text--black text-right text-nowrap">$1,720.00</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="border-top-dashed-gray"></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap" colspan="2">{{ translate('Item Price') }}</td>
                                                    <td class="text-right text--black text-nowrap">$ 1,720.00</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-nowrap">{{ translate('Addon Cost') }}</td>
                                                    <td class="text-right text--black">$ 0.00</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="border-top-dashed-gray"></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text--black fs-12 font-medium">{{ translate('Subtotal') }}</td>
                                                    <td colspan="2" class="text-right text--black fs-12 font-medium text-nowrap">$ 0.00</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="border-top-dashed-gray"></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-nowrap">{{ translate('Discount') }}</td>
                                                    <td class="text-right text--black text-nowrap">- $ 0.00</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-nowrap">{{ translate('Coupon discount') }}</td>
                                                    <td class="text-right text--black text-nowrap">- $ 0.00</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-nowrap">{{ translate('Vat/tax') }}</td>
                                                    <td class="text-right text--black text-nowrap">$ 86.00</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-nowrap">{{ translate('Delivery man tips') }}</td>
                                                    <td class="text-right text--black text-nowrap">- $ 0.00</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-nowrap">{{ translate('Delivery charge') }}</td>
                                                    <td class="text-right text--black text-nowrap">$ 583.99</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="text-nowrap">{{ translate('Service Charge') }}</td>
                                                    <td class="text-right text--black text-nowrap">+ $ 10.00</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="border-top-dashed-gray"></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text--black fs-16 font-bold">{{ translate('Total') }}</td>
                                                    <td colspan="2" class="text-right text--black fs-16 font-bold text-nowrap">$ 2,399.99</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="border-top-dashed-gray"></div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text--black fs-12 mb-2 text-center terms-condition-text-wrapper">
                                        {{ translate('Terms & Condition') }} : <span id="terms-condition-text">{{ $data['terms&condition_content'] ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,' }}</span>
                                    </div>
                                    <div class="text--black fs-12 mb-3 text-center">
                                        <h4 class="text--black fs-16 font-bold mb-0">{{ translate('Thank You') }}</h4>
                                        <div>{{ translate('for ordering food from Stackfood') }}</div>
                                    </div>
                                    <div class="text--black fs-10 pt-3 border-top-dashed-gray text-center" id="copyright-text">
                                        {{ $data['copyright_text_content'] ?? '© ' . translate('2024 StackFood. All right reserved') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-sticky mt-2">
                <div class="container-fluid">
                <div class="d-flex flex-wrap gap-3 justify-content-center py-3">
                    <button type="reset" id="reset_btn" class="btn btn--secondary min-w-120 location-reload">{{ translate('Reset') }} </button>
                    <button type="submit" class="btn btn--primary call-demo">
                        <i class="tio-save"></i>
                        {{ translate('Save_Information') }}
                    </button>
                </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
    <script>
        $(document).ready(function () {

            const defaultLogo = $('#preview-logo').data('default');
            let uploadedLogo = null;

            const logoInput = document.getElementById('upload-new-logo-input');
            if (logoInput) {
                const observer = new MutationObserver(() => {
                    const isInvalid = logoInput.classList.contains('is-invalid');
                    $(logoInput).closest('form').find('button[type="submit"], .call-demo').prop('disabled', isInvalid);
                });
                observer.observe(logoInput, { attributes: true, attributeFilter: ['class'] });
            }

            function applySwitchState(el){
                const target = $(el).data('target');
                if(!target) return;

                if($(el).is(':checked')){
                    $(target).removeClass('d--none').show();
                }else{
                    $(target).addClass('d--none').hide();
                }
            }

            $('.toggle-switch-input').on('change', function(){
                applySwitchState(this);
                updateIdentityPreview();
            });

            function updateLogoOption(){
                const input = $('#upload-new-logo-input')[0];
                if($('#upload_new_logo').is(':checked')){
                    $('.upload_new_logo_wrapper').slideDown(0);
                    if(uploadedLogo){
                        $('#preview-logo').attr('src', uploadedLogo);
                    }else if('{{ isset($data['invoice_logo']) ? $data['invoice_logo'] : '' }}') {
                        $('#preview-logo').attr('src', '{{ dynamicStorage('storage/app/public/business/' . ($data['invoice_logo'] ?? '')) }}');
                    } else {
                         $('#preview-logo').attr('src', defaultLogo);
                    }

                    if (input && input.classList.contains('is-invalid')) {
                        $('.upload_new_logo_wrapper').closest('form').find('button[type="submit"], .call-demo').prop('disabled', true);
                    }
                }else{
                    $('.upload_new_logo_wrapper').slideUp(0);
                    $('#preview-logo').attr('src', '{{ $businessLogoUrl ?? dynamicAsset('assets/admin/img/100x100/1.png') }}');
                    $('.upload_new_logo_wrapper').closest('form').find('button[type="submit"], .call-demo').prop('disabled', false);
                }
            }

            $('input[name="invoice_logo_type"]').on('change', updateLogoOption);

            $(document).on('change','#upload-new-logo-input',function(){

                if(!$('#upload_new_logo').is(':checked')) return;

                const file = this.files[0];
                if(!file || this.classList.contains('is-invalid')) return;

                const reader = new FileReader();

                reader.onload = function(e){
                    uploadedLogo = e.target.result;
                    $('#preview-logo').attr('src', uploadedLogo);
                }

                reader.readAsDataURL(file);

            });

            function updateIdentityPreview(){

                $('#tax_id_text,#bin_number_text,#musak_text').addClass('d--none');

                if(!$('#identity-switch').is(':checked')) return;

                if($('#tex_id').is(':checked')){
                    $('#tax_id_text').removeClass('d--none');
                }

                if($('#bin_number').is(':checked')){
                    $('#bin_number_text').removeClass('d--none');
                }

                if($('#musak').is(':checked')){
                    $('#musak_text').removeClass('d--none');
                }

            }

            function updateIdentityInput(){

                $('.tax_input_wrapper,.bin_input_wrapper,.musak_input_wrapper').addClass('d--none');

                if($('#tex_id').is(':checked')){
                    $('.tax_input_wrapper').removeClass('d--none');
                }

                if($('#bin_number').is(':checked')){
                    $('.bin_input_wrapper').removeClass('d--none');
                }

                if($('#musak').is(':checked')){
                    $('.musak_input_wrapper').removeClass('d--none');
                }

            }

            $('input[name="business_identity_type"]').on('change', function(){
                updateIdentityPreview();
                updateIdentityInput();
            });

            $('#terms_condition').on('input', function () {
                $('#terms-condition-text').text($(this).val());
            });

            $('#copyright').on('input', function () {
                $('#copyright-text').text($(this).val());
            });

            $('#tax_number_input').on('input', function () {
                $('#tax_id_text').text('Tax Id : ' + $(this).val());
            });

            $('#bin_number_input').on('input', function () {
                $('#bin_number_text').text('BIN : ' + $(this).val());
            });

            $('#musak_input').on('input', function () {
                $('#musak_text').text('Musak : ' + $(this).val());
            });

            function initState(){

                $('.toggle-switch-input').each(function(){
                    applySwitchState(this);
                });

                updateLogoOption();
                updateIdentityPreview();
                updateIdentityInput();

            }

            initState();

            $('#invoice_setup_form').on('submit', function (e) {
                let logoStatus = $('#logo-switch').is(':checked');
                let logoType = $('input[name="invoice_logo_type"]:checked').val();
                let logoInput = $('#upload-new-logo-input')[0];
                let existingLogo = '{{ isset($data['invoice_logo']) ? $data['invoice_logo'] : '' }}';

                if (logoStatus && logoType === 'upload_new_logo' && !existingLogo && (!logoInput || logoInput.files.length === 0)) {
                    toastr.error('{{ translate('Invoice logo is required') }}');
                    e.preventDefault();
                    return false;
                }

                let identityStatus = $('#identity-switch').is(':checked');
                if (identityStatus) {
                    let identityType = $('input[name="business_identity_type"]:checked').val();
                    if (identityType === 'tax' && !$('#tax_number_input').val()) {
                        toastr.error('{{ translate('Tax number is required') }}');
                        e.preventDefault();
                        return false;
                    }
                    if (identityType === 'bin' && !$('#bin_number_input').val()) {
                        toastr.error('{{ translate('Bin number is required') }}');
                        e.preventDefault();
                        return false;
                    }
                    if (identityType === 'musak' && !$('#musak_input').val()) {
                        toastr.error('{{ translate('Musak number is required') }}');
                        e.preventDefault();
                        return false;
                    }
                }

                let conditionStatus = $('#condition-switch').is(':checked');
                if (conditionStatus && !$('#terms_condition').val()) {
                    toastr.error('{{ translate('Terms & Condition content is required') }}');
                    e.preventDefault();
                    return false;
                }

                let copyrightStatus = $('#copyright-switch').is(':checked');
                if (copyrightStatus && !$('#copyright').val()) {
                    toastr.error('{{ translate('Copyright text is required') }}');
                    e.preventDefault();
                    return false;
                }

                if ('{{ getEnvMode() }}' === 'demo') {
                    e.preventDefault();
                    toastr.info('{{ translate('Update option is disabled for demo!') }}');
                }
            });

        });
    </script>
@endpush
