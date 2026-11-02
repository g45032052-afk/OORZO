@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))

@section('content')

<div class="content container-fluid">
    <div class="page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <h1 class="page-header-title text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{ dynamicAsset('assets/admin/img/landing-page.png') }}" class="mw-26px" alt="public">
                </div>
                <span>
                    {{ translate('React_Landing_Page') }}
                </span>
            </h1>
            <!-- <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{translate('See_how_it_works')}}</strong>
                <div>
                    <i class="tio-info text-gray1 fs-16"></i>
                </div>
            </div> -->
        </div>
    </div>
    <div class="js-nav-scroller tabs-slide-wrap tabs-slide-language hs-nav-scroller-horizontal">
        @include('admin-views.landing_page.top_menu.react_landing_menu')
        <div class="arrow-area">
            <div class="button-prev align-items-center">
                <button type="button" class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                    <i class="tio-chevron-left fs-24"></i>
                </button>
            </div>
            <div class="button-next align-items-center">
                <button type="button" class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                    <i class="tio-chevron-right fs-24"></i>
                </button>
            </div>
        </div>
    </div>



            <!-- <div class="d-flex justify-content-between __gap-12px mb-3">
                <h5 class="card-title d-flex align-items-center">
                    <span class="card-header-icon mr-2">
                        <img src="{{dynamicAsset('assets/admin/img/fixed_data1.png')}}" alt="" class="mw-100">
                    </span>
                    {{translate('messages.Newsletter_Section')}}
                </h5>
            </div> -->
            <div class="card mb-20 mt-4">
                <div class="card-header">
                    <div class="">
                        <h3 class="mb-1">{{ translate('Footer Section') }}</h3>
                        <p class="mb-0 gray-dark fs-12">{{ translate('Set the main description or tagline for your company in the footer.') }}</p>
                    </div>
                </div>
                <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'fixed-data-newsletter') }}" method="post">
                    @csrf
                    <div class="card-body pb-0">
                        <div class="card-custom-xl mb-20">
                            <div class="mb-20">
                                <h4 class="mb-1">{{ translate('Newsletter') }}</h4>
                                <p class="mb-0 gray-dark fs-12">{{ translate('Manage the title and subtitle for the email newsletter sign-up section') }}</p>
                            </div>
                            <div class="bg-light2 p-xl-20 p-3 rounded">
                                @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                @if($language)
                                    <ul class="nav nav-tabs mb-4 border-">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active"
                                            href="#"
                                            id="default-link">{{translate('messages.default')}}</a>
                                        </li>
                                        @foreach (json_decode($language) as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link"
                                                    href="#"
                                                    id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <div class="row g-3 lang_form default-form" id="default-form">
                                    <input type="hidden" name="lang[]" value="default">
                                    <div class="col-sm-12">
                                        <label for="title" class="form-label fw-400">{{translate('title')}} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('The headline for your email sign-up section. Keep it under 50 characters.') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                        </label>
                                        <input required  id="title" data-maxlength="50" type="text"  name="title[]" value="{{ $news_letter_title?->getRawOriginal('value') ?? null}}" class="form-control" placeholder="{{translate('Enter_Title')}}">
                                        <input type="hidden" name="key" value="news_letter_title" >
                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                    </div>
                                    <div class="col-sm-12">
                                        <label for="sub_title" class="form-label fw-400">{{translate('Subtitle')}} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Briefly explain the benefits of subscribing, such as receiving news, updates, or special discounts. Keep it under 100 characters.') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                        </label>
                                        <input required id="sub_title"  data-maxlength="100" type="text"  name="sub_title[]" value="{{ $news_letter_sub_title?->getRawOriginal('value') ?? null}}"  class="form-control" placeholder="{{translate('Enter_Sub_Title')}}">
                                        <input type="hidden" name="key_2" value="news_letter_sub_title" >
                                        <span class="text-body-light text-right d-block mt-1">0/100</span>
                                    </div>
                                </div>

                                @forelse(json_decode($language) as $lang)
                                <?php
                                if($news_letter_title?->translations){
                                        $news_letter_title_translate = [];
                                        foreach($news_letter_title->translations as $t)
                                        {
                                            if($t->locale == $lang && $t->key=='news_letter_title'){
                                                $news_letter_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                if($news_letter_sub_title?->translations){
                                        $news_letter_sub_title_translate = [];
                                        foreach($news_letter_sub_title->translations as $t)
                                        {
                                            if($t->locale == $lang && $t->key=='news_letter_sub_title'){
                                                $news_letter_sub_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>

                                <div class="row g-3 d-none lang_form" id="{{$lang}}-form">
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                    <div class="col-sm-12">
                                        <label for="title{{$lang}}" class="form-label fw-400">{{translate('title')}} ({{strtoupper($lang)}})

                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('The headline for your email sign-up section. Keep it under 50 characters.') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                        <input  id="title{{$lang}}" type="text" data-maxlength="50" name="title[]" value="{{ $news_letter_title_translate[$lang]['value'] ?? '' }}" class="form-control" placeholder="{{translate('Enter_Title')}}">
                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                    </div>
                                    <div class="col-sm-12">
                                        <label for="sub_title{{$lang}}" class="form-label fw-400">{{translate('Subtitle')}} ({{strtoupper($lang)}})

                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Briefly explain the benefits of subscribing, such as receiving news, updates, or special discounts. Keep it under 50 characters.') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                        <input id="sub_title{{$lang}}"  type="text" data-maxlength="100" name="sub_title[]" value="{{ $news_letter_sub_title_translate[$lang]['value'] ?? '' }}" class="form-control" placeholder="{{translate('Enter_Title')}}">
                                        <span class="text-body-light text-right d-block mt-1">0/100</span>
                                    </div>
                                </div>
                                @empty
                                @endforelse

                                <div class="btn--container justify-content-end mt-4">
                                    <button type="reset" class="btn btn--reset">{{translate('Reset')}}</button>
                                    <button type="submit"   class="btn btn--primary">{{translate('Save')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="card-body pt-0">
                    <div class="card-custom-xl">
                        <div class="mb-20">
                            <h4 class="mb-1">{{ translate('Footer Article') }}</h4>
                            <p class="mb-0 gray-dark fs-12">{{ translate('Set the main description or tagline for your company in the footer.') }}</p>
                        </div>
                        <div class="bg-light2 p-xl-20 p-3 rounded">
                            @if($language)
                                <ul class="nav nav-tabs mb-4 border-">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link1 active"
                                        href="#"
                                        id="default-link1">{{translate('messages.default')}}</a>
                                    </li>
                                    @foreach (json_decode($language) as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link1"
                                                href="#"
                                                id="{{ $lang }}-link1">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                             <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'fixed-data-footer') }}" method="post">
                                @csrf
                                <div class="card-body p-0">
                                    <div class="row g-3 lang_form-float default-form-float" >
                                        <input type="hidden"  name="lang[]" value="default">
                                        <div class="col-sm-12">
                                            <div class="form-group mb-0">
                                                <label for="footer_data" class="form-label fw-400">{{translate('messages.Footer_Description')}} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                    <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('This is your company\'s tagline or a short "About Us" text for the footer.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input type="hidden" name="footer_key" value="footer_data" >
                                                <textarea required id="footer_data" rows="1" data-maxlength="100"  class="form-control" name="footer_data[]" placeholder="{{translate('messages.Short_Description')}}">{{ $footer_data?->getRawOriginal('value') ?? null}}</textarea>
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @forelse(json_decode($language) as $lang)
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                        <?php
                                            if($footer_data?->translations){
                                                    $footer_data_translate = [];
                                                    foreach($footer_data->translations as $t)
                                                    {
                                                        if($t->locale == $lang && $t->key=='footer_data'){
                                                            $footer_data_translate[$lang]['value'] = $t->value;
                                                        }
                                                    }
                                                }
                                            ?>
                                        <div class="row g-3  d-none lang_form-float" id="{{ $lang }}-form-float">
                                            <div class="col-sm-12">
                                                <div class="form-group mb-0">
                                                    <label for="footer_data{{$lang}}" class="form-label fw-400">{{translate('messages.Footer_description')}} ({{strtoupper($lang)}})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('This is your company\'s tagline or a short "About Us" text for the footer.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <textarea id="footer_data{{$lang}}" rows="1" class="form-control"  data-maxlength="100" name="footer_data[]" placeholder="{{translate('messages.Short_Description')}}">{{ $footer_data_translate[$lang]['value'] ?? '' }}</textarea>
                                                    <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                    @endforelse
                                </div>
                                 <div class="btn--container justify-content-end mt-4">
                                     <button type="reset" class="btn btn--reset">{{translate('Reset')}}</button>
                                     <button type="submit"   class="btn btn--primary">{{translate('Save')}}</button>
                                 </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <br>
            <br>
            <div class="d-flex justify-content-between __gap-12px mb-3">
                <h5 class="card-title d-flex align-items-center">
                    <span class="card-header-icon mr-2">
                        <img src="{{dynamicAsset('assets/admin/img/fixed_data2.png')}}" alt="" class="mw-100">
                    </span>
                    {{translate('messages.Footer_Section')}}
                </h5>
            </div> -->

    @include('admin-views.landing_page.react.partials.fixed_data_guideline')

@endsection

    @push('script_2')
        <script>
            $(".lang_link1").click(function(e) {
                e.preventDefault();
                $(".lang_link1").removeClass('active');
                $(".lang_form-float").addClass('d-none');
                $(this).addClass('active');
                let form_id = this.id;
                let lang = form_id.substring(0, form_id.length - 6);
                $("#" + lang + "-form-float").removeClass('d-none');
                if (lang === 'default') {
                    $(".default-form-float").removeClass('d-none');
                }
            })
        </script>
@endpush

