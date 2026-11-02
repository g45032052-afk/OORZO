@extends('layouts.admin.app')

@section('title', translate('messages.WhatsApp Integration'))

@section('content')


<div class="content container-fluid">
    <div class="page-header mb-1">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <h1 class="page-header-title text-capitalize">
                <span>
                    {{ translate('WhatsApp Integration') }}
                </span>
            </h1>
        </div>
    </div>
    <div class="card mb-20">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-20">
                <div class="">
                    <div class="">
                        <h3 class="mb-1 fs-16">{{ translate('WhatsApp Integration') }}</h3>
                        <p class="mb-0 gray-dark fs-12">
                            {{ translate('Manage the basic settings that control how vendors operate in your platform.') }} 
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <label class="toggle-switch toggle-switch-sm">
                        <input type="checkbox" id="" class="status toggle-switch-input" checked>
                        <span class="toggle-switch-label text">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>
            </div>
            <div class="bg-light2 p-xl-20 p-3 rounded mb-20">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label class="input-label fw-400" for="default_title">
                                    {{ translate('messages.Provider Selection') }} 
                                    <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('content..l') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                            </label>
                            <select name="" id="" class="custom-select">
                                <option value="1">
                                    {{ translate('messages.WhatsApp') }}
                                </option>
                                <option value="1">
                                    {{ translate('messages.3rd party') }}
                                </option>
                            </select>
                        </div>        
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2 gap-2 flex-wrap">
                                <label class="input-label mb-0 fw-400" for="default_title">
                                    {{ translate('messages.WhatsApp API') }}
                                    <span class="text-danger">*</span>
                                        <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('content..l') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                            </div>
                            <input type="text" name="title[]" class="form-control" placeholder="{{ translate('messages.Ex: 5') }}" value="">
                        </div>        
                    </div>                        
                </div>
            </div>
            <div class="mb-20">                
                <h3 class="mb-20 fs-16">{{ translate('Select 3rd Part Provider') }}</h3>
                <div class="bg-light2 p-xl-20 p-3 rounded">
                    <div class="d-flex align-items-center justify-content-between mb-20">
                        <div class="">
                            <div class="">
                                <h3 class="mb-1 fs-16">{{ translate('TWILIO') }}</h3>
                                <p class="mb-0 gray-dark fs-12">
                                    {{ translate('Setup TWILIO as SMS gateway') }} 
                                </p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <label class="toggle-switch toggle-switch-sm">
                                <input type="checkbox" id="" class="status toggle-switch-input" checked>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="bg-white p-xl-20 p-3 rounded mb-20">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="input-label fw-400" for="default_title">
                                            {{ translate('messages.Account SID') }} 
                                            <span class="text-danger">*</span>
                                            <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('content..l') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                    </label>
                                    <input type="text" name="title[]" class="form-control" placeholder="{{ translate('messages.Ex: data') }}" value="">
                                </div>        
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group mb-0">
                                    <div class="d-flex align-items-center justify-content-between mb-2 gap-2 flex-wrap">
                                        <label class="input-label mb-0 fw-400" for="default_title">
                                            {{ translate('messages.Auth Token') }}
                                            <span class="text-danger">*</span>
                                                <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('content..l') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                    </div>
                                    <input type="text" name="title[]" class="form-control" placeholder="{{ translate('messages.Ex: data') }}" value="">
                                </div>        
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group mb-0">
                                    <div class="d-flex align-items-center justify-content-between mb-2 gap-2 flex-wrap">
                                        <label class="input-label mb-0 fw-400" for="default_title">
                                            {{ translate('messages.From Number') }}
                                            <span class="text-danger">*</span>
                                                <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('content..l') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                    </div>
                                    <input type="text" name="title[]" class="form-control" placeholder="{{ translate('messages.Ex: +88012345678') }}" value="">
                                </div>        
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group mb-0">
                                    <div class="d-flex align-items-center justify-content-between mb-2 gap-2 flex-wrap">
                                        <label class="input-label mb-0 fw-400" for="default_title">
                                            {{ translate('messages.messaginig service SID (optional)') }}
                                            <span class="text-danger">*</span>
                                                <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('content..l') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                    </div>
                                    <input type="text" name="title[]" class="form-control" placeholder="{{ translate('messages.Ex: +88012345678') }}" value="">
                                </div>        
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-light2 p-xl-20 p-3 rounded mb-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="">
                        <div class="">
                            <h3 class="mb-1 fs-16 text-uppercase">{{ translate('Massage Bird') }}</h3>
                            <p class="mb-0 gray-dark fs-12">
                                {{ translate('Setup Alphanet SMS as SMS gateway') }} 
                            </p>
                        </div>
                    </div>
                    <label class="toggle-switch toggle-switch-sm m-0">
                        <input type="checkbox" id="" class="status toggle-switch-input" checked>
                        <span class="toggle-switch-label text">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>
            </div>
            <div class="bg-light2 p-xl-20 p-3 rounded mb-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="">
                        <div class="">
                            <h3 class="mb-1 fs-16 text-uppercase">{{ translate('Watti') }}</h3>
                            <p class="mb-0 gray-dark fs-12">
                                {{ translate('Setup RELEANS as SMS gateway') }} 
                            </p>
                        </div>
                    </div>
                    <label class="toggle-switch toggle-switch-sm m-0">
                        <input type="checkbox" id="" class="status toggle-switch-input" checked>
                        <span class="toggle-switch-label text">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>
            </div>
            <div class="btn--container justify-content-end mt-4">
                <button type="reset" class="btn btn--reset min-w-120">{{translate('Reset')}}</button>
                <button type="submit"   class="btn btn--primary min-w-120">
                {{translate('Save')}}
                </button>
            </div>
        </div>
    </div>    
</div>





@endsection
