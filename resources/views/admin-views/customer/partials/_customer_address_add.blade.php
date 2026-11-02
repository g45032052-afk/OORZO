
<div class="modal fade" id="addressAdd__modal" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-20">
            <div class="modal-header cmn__quick p-0">
                <button type="button" class="close w-35px h-35px min-h-35px clear-when-done" data-dismiss="modal"
                    aria-label="Close">
                    <span class="top-0 m-0" aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body pt-0 pb-0">

                <form action="{{ route('admin.customer.update-customer-address', $customer->id) }}" class="mt-lg-4 mt-3 pb-2" method="post">
                    @csrf
                    @method('put')
                    <div class="scrolling-area-modal">
                        <div>
                            <h3 class="modal-title text-capitalize mb-2 flex-grow-1">{{ translate('Add New Address') }}  </h3>
                            <div class="bg-light rounded-10 p-sm-3 p-2 mb-20">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label for="" class="input-label" for="">
                                            {{ translate('messages.Address Type') }}
                                            <span class="input-label-secondary text-danger">*</span>
                                        </label>
    
                                        <select required name="address_type" id="" class="custom-select">
                                            <option  value="home">{{ translate('messages.home') }}</option>
                                            <option value="office">{{ translate('messages.office') }}</option>
                                            <option value="other">{{ translate('messages.other') }}</option>
                                        </select>
    
    
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact_person_name" class="input-label"
                                            for="">{{ translate('messages.contact_person_name') }}<span
                                                class="input-label-secondary text-danger">*</span></label>
                                        <input id="contact_person_name" type="text" class="form-control"
                                            name="contact_person_name" placeholder="{{ translate('Ex: Jhone') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact_person_number" class="input-label"
                                            for="">{{ translate('Contact Number') }}<span
                                                class="input-label-secondary text-danger">*</span></label>
                                        <input pattern="^\+[1-9][0-9]{6,14}$" id="contact_person_number" type="tel" class="form-control"
                                            name="contact_person_number"
                                            placeholder="{{ translate('Ex: +3264124565') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="road" class="input-label"
                                            for="">{{ translate('messages.Road') }}</label>
                                        <input id="road" type="text" class="form-control" name="road"  placeholder="{{ translate('Ex: 4th') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="house" class="input-label"
                                            for="">{{ translate('messages.House') }}</label>
                                        <input id="house" type="text" class="form-control" name="house" placeholder="{{ translate('Ex: 45/C') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="floor" class="input-label"
                                            for="">{{ translate('messages.Floor') }}</label>
                                        <input id="floor" type="text" class="form-control" name="floor"  placeholder="{{ translate('Ex: 1A') }}">
                                    </div>
    
    
                                    <div class="col-md-12">
                                        <label for="address" class="input-label"
                                            for="">{{ translate('messages.address') }}
                                            <span class="input-label-secondary text-danger">*</span></label>
                                        <textarea id="address" name="address" class="form-control" cols="30" rows="3"
                                            placeholder="{{ translate('Ex: address') }}" required></textarea>
                                    </div>
                                </div>
    
                            </div>
                            <div class="maps pac-input-xl-400 position-relative">
                                <input id="pac-input" class="controls rounded overflow-hidden border-0 pac-input-Controls"
                                    title="{{ translate('messages.search_your_location_here') }}" type="text"
                                    placeholder="{{ translate('messages.search_here') }}" />
                                <div id="location_map_canvas" class="overflow-hidden rounded height-285px"></div>
    
                                <div
                                    class="position-absolute bottom-0 px-2 m-2 py-1 lat-long-box d-inline-flex align-items-center bg-white rounded shadow">
                                    <div class="d-flex align-items-center gap-1">
                                        <label for="longitude" class="input-label text-title fs-10 mb-0" for="">
                                            {{ translate('messages.long') }}
                                        </label>
                                        <input type="text"
                                            class="w-auto border-0 py-1 bg-transparent text-title fs-10 outline-0 p-0"
                                            id="longitude" name="longitude" readonly>
                                    </div>
                                    <div class="border-end"></div>
                                    <div class="d-flex align-items-center gap-1">
                                        <label for="latitude" class="input-label text-title fs-10 mb-0" for="">
                                            {{ translate('messages.lat') }}
                                        </label>
                                        <input type="text"
                                            class="w-auto border-0 py-1 bg-transparent text-title fs-10 outline-0 p-0"
                                            id="latitude" name="latitude" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 py-3 px-0">
                        <div class="btn--container justify-content-end">
                            <button type="button" class="btn min-w-120 clear-when-done btn--reset"
                                data-dismiss="modal">{{ translate('Cancel') }}</button>
                            <button class="btn min-w-120 btn-sm btn--primary " type="submit">
                                {{ translate('Submit') }}
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
