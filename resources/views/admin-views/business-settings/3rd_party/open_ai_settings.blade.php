@extends('layouts.admin.app')

@section('title', translate('messages.settings'))

@section('3rd_party')
    active
@endsection
@section('openAI')
    active
@endsection

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-robot"></i>
                </span>
                <span>{{ translate('OpenAI_Configuration') }}
                </span>
            </h1>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 mt-4 __gap-12px">
                <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
                    <!-- Nav -->
                    <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
                        <li class="nav-item">
                            <a class="nav-link   {{ Request::is('admin/business-settings/open-ai') ? 'active' : '' }}"
                                href="{{ route('admin.business-settings.openAI') }}"
                                aria-disabled="true">{{ translate('AI Configuration') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/business-settings/open-ai-settings') ? 'active' : '' }}"
                                href="{{ route('admin.business-settings.openAISettings') }}"
                                aria-disabled="true">{{ translate('AI Settings') }}</a>
                        </li>
                    </ul>
                    <!-- End Nav -->
                </div>
            </div>
        </div>
        <!-- End Page Header -->




        <div class="col-12">

            <div class="card mt-2">
                <div class="card-header card-header-shadow">
                    <h5 class="card-title">
                        <span>
                            <span class="page-header-icon">
                                <i class="tio-robot"></i>
                            </span>
                            {{ translate('Restaurant_limits_on_using_AI') }}
                        </span>
                        {{-- <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ translate('messages.Existing_Customers_can_share_a_referral_code_with_others_to_earn_a_referral_bonus._For_this,_the_new_user_MUST_sign_up_using_the_referral_code_and_make_their_first_purchase.') }}">
                            <img src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}"
                                alt="{{ translate('messages.show_hide_food_menu') }}">
                        </span> --}}
                    </h5>
                </div>

                <form action="{{ route('admin.business-settings.openAISettingsUpdate') }}" method="post">
                    @csrf
                    @method('put')

                    @if (\Nwidart\Modules\Facades\Module::find('AI')?->isEnabled())
                        @php($ai_chat_status = $data['ai_chat_status'] ?? 0)
                        <div class="card-body border-bottom">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-sm-nowrap flex-wrap">
                                <div>
                                    <h4 class="mb-1">
                                        <span class="page-header-icon mr-1"><i class="tio-android"></i></span>
                                        {{ translate('AI Chat Agent') }}
                                    </h4>
                                    <p class="fs-12 m-0">
                                        {{ translate('Allow customers to use the AI chat assistant from the customer app and web. A valid OpenAI configuration is required.') }}
                                    </p>
                                </div>
                                <label class="toggle-switch toggle-switch-sm mb-0">
                                    <input type="checkbox" data-id="ai_chat_status" data-type="toggle"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-tips-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-tips-off.png') }}"
                                        data-title-on="{{ translate('Want to enable the AI Chat Agent') }} ?"
                                        data-title-off="{{ translate('Want to disable the AI Chat Agent') }} ?"
                                        data-text-on="<p>{{ translate('Customers will be able to use the AI chat assistant from the app and web. Remember to Save to apply.') }}</p>"
                                        data-text-off="<p>{{ translate('The AI chat assistant will be hidden from customers. Remember to Save to apply.') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox-toggle" value="1"
                                        name="ai_chat_status" id="ai_chat_status"
                                        {{ $ai_chat_status == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label"><span class="toggle-switch-indicator"></span></span>
                                </label>
                            </div>
                        </div>
                    @endif

                    <div class="card-body">
                        <div class="py-2">
                            <div class="row g-3 align-items-end">

                                <div class="align-self-center  col-4">
                                    <div class="text-left">
                                        <h4 class="align-items-center">
                                            <span>
                                                {{ translate('Section_wise_data_generation') }}
                                            </span>
                                        </h4>
                                        <p>
                                            {{ translate('Set how many times  AI can generate data for each element of the restaurant panel or app') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="card __bg-F8F9FC-card text-left">
                                        <div class="card-body">
                                            <div class="form-group mb-0">
                                                <label class="input-label" for="section_wise_ai_limit">
                                                    {{ translate('Section_wise_data_generation_limit') }}
                                                </label>
                                                <input id="section_wise_ai_limit" type="number" min="0"
                                                    max="99999999999" class="form-control" name="section_wise_ai_limit"
                                                    value="{{ $data['section_wise_ai_limit'] ?? '0' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="align-self-center  col-4">
                                    <div class="text-left">
                                        <h4 class="align-items-center">
                                            <span>
                                                {{ translate('Image_based_data_generation') }}
                                            </span>
                                        </h4>
                                        <p>
                                            {{ translate('Set how many times AI can generate data from an image upload ') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="card __bg-F8F9FC-card text-left">
                                        <div class="card-body">
                                            <div class="form-group mb-0">
                                                <label class="input-label" for="image_upload_limit_for_ai">
                                                    {{ translate('Image_upload_generation_limit') }}
                                                </label>
                                                <input id="image_upload_limit_for_ai" type="number" min="0"
                                                    max="99999999999" class="form-control" name="image_upload_limit_for_ai"
                                                    value="{{ $data['image_upload_limit_for_ai'] ?? '0' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="mb-4 mt-4 col-12">
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn"
                                    class="btn btn--reset location-reload">{{ translate('Reset') }}</button>
                                <button type="{{ getEnvMode() != 'demo' ? 'submit' : 'button' }}" id="submit"
                                    class="btn btn--primary call-demo">{{ translate('Save_Information') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>










    </div>




@endsection
@push('script_2')
@endpush
