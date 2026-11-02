@extends('layouts.admin.app')

@section('title', translate('Settings'))

@section('content')
    <div class="content" id="food_setup">
        <form action="{{ route('admin.business-settings.update-food') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="container-fluid">
                <div class="page-header pb-0">
                    @include('admin-views.business-settings.partials._note')
                    @include('admin-views.business-settings.partials.nav-menu')
                </div>

                {{-- Food Page Design --}}
                <hr />
                {{-- <h2>Food Page Design</h2> --}}
                <div class="card card-body pb-10">
                    <div class="row g-3 mb-20">
                        <div class="col-lg-8">
                            <div>
                                <h3 class="mb-1">{{ translate('messages.Veg/Non Veg Option') }}</h3>
                                <p class="fs-12 mb-0">
                                    {{ translate('messages.If enabled customers can filter food according to their preference from the Customer App or Website.') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0"
                                data-toggle="modal" data-target="#toggle-modal">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    {{ translate('messages.Status') }}
                                </span>
                                <input type="checkbox" data-id="toggle_veg_non_veg" data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/veg-on.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/veg-off.png') }}"
                                    data-title-on="{{ translate('Want_to_enable') }} <strong>{{ translate('Veg/Non Veg Option') }}</strong> ?"
                                    data-title-off="{{ translate('Want_to_disable') }} <strong>{{ translate('Veg/Non Veg Option') }}</strong> ?"
                                    data-text-on="<p>{{ translate('If_enabled,_customers_can_filter_food_according_to_their_preference_from_the_Customer_App_or_Website.') }}</p>"
                                    data-text-off="<p>{{ translate('If_disabled,_the_Veg/Non_Veg_feature_will_be_hidden_from_the_Customer_App_or_Website.') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle" value="1" name="vnv"
                                    id="toggle_veg_non_veg"
                                    {{ $settings['toggle_veg_non_veg'] == 1 ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning mb-2"
                        style="--bs-bg-opacity: 0.1;">
                        <span class="text-warning lh-1 fs-14">
                            <i class="tio-info"></i>
                        </span>
                        <span>
                            {{ translate('messages.Check foods which are available veg or no-veg option. It will help to your customer for buy food without any confusion.') }}
                        </span>
                    </div>
                </div>
                <hr />
                {{-- Food Page Design Ends --}}
            </div>

            <div class="footer-sticky mt-2">
                <div class="container-fluid">
                    <div class="d-flex flex-wrap gap-3 justify-content-center py-3">
                        <button type="reset" id="reset_btn"
                            class="btn btn--secondary min-w-120 location-reload">{{ translate('messages.Reset') }} </button>
                        <button type="{{ getEnvMode() != 'demo' ? 'submit' : 'button' }}"
                            class="btn btn--primary call-demo">
                            <i class="tio-save"></i>
                            {{ translate('Save_Information') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>


                    <!-- Guidline Offcanvas Btn -->
<div class="d-flexgap-2 w-40px gap-2 bg-white position-fixed end-0 translate-middle-y pointer view-guideline-btn flex-column pt-3 px-2 justify-content-center offcanvas-trigger"
    data-toggle="offcanvas" data-target="#offcanvasSetupGuide">
    <span class="arrow bg-primary py-1 px-2 text-white rounded fs-12"><i class="tio-share-vs"></i></span>
    <span class="view-guideline-btn-text text-dark font-semibold pb-2 text-nowrap">
        {{ translate('View_Guideline') }}
    </span>
</div>

<!-- Guidline Offcanvas -->
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
    <div class="custom-offcanvas" tabindex="-1" id="offcanvasSetupGuide" aria-labelledby="offcanvasSetupGuideLabel"
        style="--offcanvas-width: 500px">
            <div>
                <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
                    <h3 class="mb-0">{{ translate('messages.Food Setup Guideline') }}</h3>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
                </div>
                <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
                    <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#food_setup_guide"
                                aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Food Setup') }}</span>
                            </button>
                            <a href="#food_setup"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                        </div>
                        <div class="collapse show mt-3" id="food_setup_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3">{{translate('messages.Veg/Non-Veg Option')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.The Veg / Non-Veg Option allows restaurants to categorise food items based on dietary preferences. This helps customers easily identify and filter items according to their food choices while browsing menus.') }}
                                    </p>
                                    <ul class="mb-0 fs-12">
                                        <li class="mt-2 mb-3">
                                            {{ translate('messages.Restaurants can mark each food item as Veg or Non-Veg when the admin allows these settings.') }}
                                        </li>
                                        <li class="mt-2 mb-3">
                                            {{ translate('messages.Customers can view item labels and apply filters based on their preferences.') }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('script_2')
<script>
    $(".collapse-div-toggler").on('change', function () {
            if ($(this).val() == '0') {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideDown();
            } else {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideUp();
            }
        });

        $(window).on('load', function () {
            $('.collapse-div-toggler').each(function () {
                if ($(this).prop('checked') == true && $(this).val() == '0') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').show();
                } else if ($(this).prop('checked') == true && $(this).val() == '1') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').hide();
                }
            });
        })


        $('.offcanvas-close-btn').on('click', function (e) {
            e.preventDefault();
            $('.custom-offcanvas').removeClass('open');
            $('#offcanvasOverlay').removeClass('show');
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top - 100
            }, 500);
        });
</script>
@endpush
