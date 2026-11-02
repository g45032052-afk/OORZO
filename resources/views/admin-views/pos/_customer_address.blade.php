<div class="bg-white rounded-10 border">
    <div class="pos--delivery-options p-0">
        <div class="d-flex justify-content-between border-bottom pb-3 p-lg-3 p-2">
            <h3 class="card-title text-dark mb-0">
                <span class="card-title-icon">
                    <i class="tio-user"></i>
                </span>
                <span class="fs-16 ml-1">{{ translate('Delivery_Information') }}</span>
            </h3>

            @if (count($address) == 0)
                <span class="delivery--edit-icon call-map-init text-primary"  data-toggle="modal"
                    data-target="#paymentModal">
                    <i class="tio-add-circle"></i>
                </span>
            @else
                <div class="btn-group">
                    <button type="button" class="bg-transparent p-0  border-0" data-toggle="dropdown"
                        aria-expanded="false">
                        <i class="tio-more-vertical fs-18"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right dropdown-customize-for-delivery">
                        <div class="d-flex text-nowrap align-items-center justify-content-end gap-2 fs-15 font-medium text-title call-map-init px-4 cursor-pointer"
                             data-toggle="modal" data-target="#paymentModal">
                            {{ translate('messages.Add Delivery Address') }} <i class="tio-add-circle text-primary"></i>
                        </div>
                        <div class="d-flex flex-column gap-3 p-20 max-scrolling">
                            @foreach ($address as $user_address)
                                <div class="add-delivery-box bg-light  d-flex align-items-center gap-4 rounded p-sm-3 p-2" >
                                    <div class="choose-address"
                                    data-url="{{ route('admin.pos.chooseAddress', $user_address->id) }}"
                                data-id="{{ $user_address->id }}">
                                        <h6 class="font-semibold fs-14 text-title mb-1">
                                            {{ translate($user_address->address_type) }}</h6>
                                        <p class="text-title m-0 fs-14 min-w-125 line--limit-1">
                                            {{ $user_address->address }}</p>
                                    </div>
                                    <span class="delivery--edit-icon editAddress text-primary"
                                    data-address_id="{{ $user_address->id }}"
                                        data-url="{{ route('admin.pos.editAddress', $user_address->id) }}"
                                       >
                                        <i class="tio-edit"></i>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <?php
            if(isset($selectedAddress)){
                $user_address = $selectedAddress;
            }
        ?>
        @if (isset($user_address))
            <div class="p-3 mb-3">
                <h5 class="fs-14 mb-2">{{ translate($user_address->address_type) }}</h5>
                <div class="d-flex flex-column gap-2 mb-3">
                    <div class="d-flex gap-3">
                        <span class="fs-13 min-w-50">{{ translate('Name') }}</span>
                        <span>:</span>
                        <div>
                            <span class="text-title">{{ $user_address->contact_person_name }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <span class="fs-13 min-w-50">{{ translate('Contact') }}</span>
                        <span>:</span>
                        <div>
                            <span class="text-title">{{ $user_address->contact_person_number }}</span>
                        </div>
                    </div>
                    @if ($user_address->floor )

                    <div class="d-flex gap-3">
                        <span class="fs-13 min-w-50">{{ translate('Floor') }}</span>
                        <span>:</span>
                        <div>
                            <span class="text-title">{{ $user_address->floor }}</span>
                        </div>
                    </div>
                    @endif
                    @if ( $user_address->house)

                    <div class="d-flex gap-3">
                        <span class="fs-13 min-w-50">{{ translate('House') }}</span>
                        <span>:</span>
                        <div>
                            <span class="text-title">{{ $user_address->house }}</span>
                        </div>
                    </div>
                    @endif
                    @if ($user_address->road)

                    <div class="d-flex gap-3">
                        <span class="fs-13 min-w-50">{{ translate('Road') }}</span>
                        <span>:</span>
                        <div>
                            <span class="text-title">{{ $user_address->road }}</span>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2 text-title border-top pt-2 px-lg-3 px-2">
                    <i class="tio-poi"></i> {{ $user_address->address }}
                </div>
            </div>
        @endif

    </div>
</div>
