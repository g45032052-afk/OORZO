
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
<div class="custom-offcanvas" tabindex="-1" id="offcanvasSetupGuide" aria-labelledby="offcanvasSetupGuideLabel" style="--offcanvas-width: 500px">
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0">{{ translate('messages.React Landing Page Setup Guideline') }}</h3>
            <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;</button>
        </div>
        <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0 collapse show" type="button"
                            data-toggle="collapse" data-target="#collapseGeneralSetup_01" aria-expanded="true">
                        <div class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span class="font-semibold text-start fs-14 text-title">{{ translate('Suggestion') }}</span>
                    </button>
                </div>
                <div class="collapse mt-3 show" id="collapseGeneralSetup_01">
                    <div class="card card-body">
                        <div class="mb-2">
                            <h5 class="mb-2">{{translate('For Image')}}</h5>
                            <ul class="mb-0">
                                <li>{{translate('Keep the image neat and clean')}}</li>
                                <li>{{translate('Maintain the ratio mention in the image section')}}</li>
                                <li>{{translate('Keep the image under 10MB')}}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-12 p-sm-20 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0 collapsed" type="button"
                            data-toggle="collapse" data-target="#collapseGeneralSetup_02" aria-expanded="true">
                        <div class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1 collapsed">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span class="font-semibold text-start fs-14 text-title">{{ translate('Section Availability') }}</span>
                    </button>
                    {{--                    <a href="#0" class="text-underline text-primary fs-12">--}}
                    {{--                        Let’s Setup--}}
                    {{--                    </a>--}}
                </div>

                <div class="collapse mt-3" id="collapseGeneralSetup_02">
                    <div class="card card-body">
                        <div class="mb-2">
                            <h5 class="mb-2">{{translate('Purpose')}}</h5>
                            <ul class="mb-0">
                                <li>{{translate('Enable the switch to display this section to users in website. Turn it off to hide it from the landing page.')}}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-12 p-sm-20 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-3 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-3 align-items-center bg-transparent border-0 p-0 collapsed" type="button"
                            data-toggle="collapse" data-target="#collapseGeneralSetup_032" aria-expanded="true">
                        <div class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1 collapsed">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span class="font-semibold text-start fs-14 text-title">{{ translate('Content Presentation') }}</span>
                    </button>
                    {{--                    <a href="#0" class="text-underline text-primary fs-12">--}}
                    {{--                        Let’s Setup--}}
                    {{--                    </a>--}}
                </div>

                <div class="collapse" id="collapseGeneralSetup_032">
                    <div class="card card-body mt-3">
                        <div class="mb-2">
                            <h5 class="mb-2">{{translate('Promotional Banner Section')}}</h5>
                            <ul class="mb-0">
                                <li>{{translate('Showcase special offers, featured restaurants, or food categories to drive customer engagement and orders.')}}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
