<style>
    .pac-container {
        z-index: 100000 !important;
    }
</style>
<div class="modal fade" id="paymentModal" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header cmn__quick p-0">
                <button type="button" class="close clear-when-done" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-0 pb-0">
                <h5 class="modal-title flex-grow-1">{{ translate('Delivery_Information') }}</h5>
                <?php
                $address = isset($address) ? $address : null;
                ?>
                <form id='delivery_address_store'>
                    @csrf
                    <input type="hidden" name="address_id" value="{{ $address ? data_get($address, 'id') : '' }}">
                    <div class="mt-4" id="delivery_address">
                        <div class="bg-light rounded-10 p-sm-3 p-2 mb-20">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="address_type" class="input-label"
                                           for="">{{ translate('messages.Address Type') }}<span
                                            class="input-label-secondary text-danger">*</span></label>
                                    <select name="address_type" id="" class="custom-select">
                                        <option {{ $address && $address['address_type'] == 'home' ? 'selected' : '' }}  value="home">{{ translate('messages.home') }}</option>
                                        <option  {{ $address && $address['address_type'] == 'office' ? 'selected' : '' }} value="office">{{ translate('messages.office') }}</option>
                                        <option  {{ $address && $address['address_type'] == 'other' ? 'selected' : '' }} value="other">{{ translate('messages.other') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="contact_person_name" class="input-label"
                                           for="">{{ translate('messages.contact_person_name') }}<span
                                            class="input-label-secondary text-danger">*</span></label>
                                    <input id="contact_person_name" type="text" class="form-control" name="contact_person_name"
                                           value="{{ $address ? $address['contact_person_name'] : '' }}"
                                           placeholder="{{ translate('Ex: Jhone') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="contact_person_number" class="input-label"
                                           for="">{{ translate('Contact Number') }}<span
                                            class="input-label-secondary text-danger">*</span></label>
                                    <input id="contact_person_number" type="tel" class="form-control" name="contact_person_number"
                                           value="{{ $address ? $address['contact_person_number'] : '' }}"
                                           placeholder="{{ translate('Ex: +3264124565') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="road" class="input-label" for="">{{ translate('messages.Road') }}</label>
                                    <input id="road" type="text" class="form-control" name="road" value="{{ $address ? $address['road'] : '' }}"
                                           placeholder="{{ translate('Ex: 4th') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="house" class="input-label" for="">{{ translate('messages.House') }}</label>
                                    <input id="house" type="text" class="form-control" name="house" value="{{ $address ? $address['house'] : '' }}"
                                           placeholder="{{ translate('Ex: 45/C') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="floor" class="input-label" for="">{{ translate('messages.Floor') }}</label>
                                    <input id="floor" type="text" class="form-control" name="floor" value="{{ $address ? $address['floor'] : '' }}"
                                           placeholder="{{ translate('Ex: 1A') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="longitude" class="input-label" for="">{{ translate('messages.longitude') }}<span
                                            class="input-label-secondary text-danger">*</span></label>
                                    <input type="text" class="form-control" id="longitude" name="longitude"
                                           value="{{ $address ? $address['longitude'] : '' }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="latitude" class="input-label" for="">{{ translate('messages.latitude') }}<span
                                            class="input-label-secondary text-danger">*</span></label>
                                    <input  type="text" class="form-control" id="latitude" name="latitude"
                                           value="{{ $address ? $address['latitude'] : '' }}" readonly>
                                </div>
                                <div class="col-md-12">
                                    <label for="address" class="input-label" for="">{{ translate('messages.address') }} <span
                                            class="input-label-secondary text-danger">*</span></label>
                                    <textarea id="address" name="address" class="form-control" cols="30" rows="3"
                                              placeholder="{{ translate('Ex: address') }}">{{ $address ? $address['address'] : '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="map_custom-controls">

                            <div class="map_custom-controls">
                                <input id="pac-input"
                                    title="{{ translate('messages.search_your_location_here') }}" type="text"
                                    placeholder="{{ translate('messages.search_here') }}" >
                            </div>
                            <div class="mb-2 h-200px" id="map"></div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer border-0 py-3">
                <div class="btn--container justify-content-end">
                    <button type="button" class="btn min-w-120 clear-when-done btn--reset" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button class="btn min-w-120 btn-sm btn--primary delivery-Address-Store" type="button">
                        {{  translate('Submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
