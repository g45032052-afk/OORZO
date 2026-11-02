<form action="{{ route('vendor.wallet-method.update', ['id' =>   $withdrawal_method['id'] ]) }}" method="post">
    @csrf
    @method('PUT')

    <div class="custom-offcanvas-body p-20">
        <div class="d-flex flex-column gap-20px">
            <div class="global-bg-box p-10px rounded mb-3">
                <div class="bg-white rounded-8 border p-xxl-3 p-2">
                    <div class="d-flex justify-content-between gap-2 flex-wrap mb-3">
                        <div>
                            <h5 class="text-secondary fw-400 mb-1 fs-12">
                                {{ translate('Method Name') }}
                            </h5>
                            <h5 class="text-title mb-0 fs-16">
                                <span>{{ $withdrawal_method['method_name'] }}</span>
                            </h5>
                        </div>

                    </div>

                </div>
            </div>
        </div>


        <div class="d-flex flex-column gap-20px" id="">

            @php
                $old_fields = json_decode($withdrawal_method['method_fields'], true) ?? [];
                $new_fields = $withdrawal_method?->withdrawMethod?->method_fields ?? [];
            @endphp

            @foreach ($new_fields as $field_key => $field_value)
                @php
                    $input_name = $field_value['input_name'];
                    $old_value = $old_fields[$input_name] ?? '';
                @endphp

                <div class="global-bg-box p-10px rounded">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-10px">
                        <h5 class="text-title m-0 d-flex gap-2">
                            {{ translate($input_name) }}
                            @if ($field_value['is_required'] == 1)
                                <span class="text-danger">*</span>
                            @endif
                        </h5>
                    </div>

                    <div class="bg-white rounded p-10px d-flex flex-column gap-1">
                        <div class="d-flex gap-2">
                            <input {{ $field_value['is_required'] == 1 ? 'required' : '' }}
                                type="{{ $field_value['input_type'] }}" name="{{ $input_name }}" class="form-control"
                                placeholder="{{ $field_value['placeholder'] }}" value="{{ $old_value }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>



    <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
        <button type="button"
            class="btn w-100 btn--secondary offcanvas-close-1 h--40px ">{{ translate('Cancel') }}</button>
        <button type="submit" class="btn w-100 btn--primary h--40px">{{ translate('Update') }}</button>
    </div>

</form>
