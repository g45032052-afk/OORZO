@extends('layouts.admin.app')

@section('title',translate('messages.promotional_banner'))


@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title"><i class="tio-edit"></i>{{translate('messages.promotional_banner')}}</h1>
            </div>
        </div>
    </div>

        <div class="col-lg-12 mb-3 mb-lg-12">
            <div class="card h-100">
                <div class="card-body">
                    <form action="{{route('admin.banner.promotional_banner_update')}}" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                                    @php($language = $language->value ?? null)
                                    @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                    @if($language)
                                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                    <ul class="nav nav-tabs mb-4">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active" href="#"
                                                id="default-link">{{translate('messages.default')}}</a>
                                        </li>
                                        @foreach (json_decode($language) as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link" href="#" id="{{ $lang }}-link">{{
                                                \App\CentralLogics\Helpers::get_language_name($lang) . '(' .
                                                strtoupper($lang) . ')' }}</a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                    <div class="lang_form" id="default-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="default_title">{{translate('messages.title')}}
                                                ({{translate('messages.default')}})
                                            <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="promotional_banner_title[]" id="default_title" class="form-control"
                                                placeholder="{{translate('messages.new_banner')}}" value="{{ $banner_title?->getRawOriginal('value') ?? null }}"
                                                 >
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                    @foreach(json_decode($language) as $lang)

                                    <?php
                                                    if($banner_title?->translations){
                                                        $translate = [];
                                                        foreach($banner_title['translations'] as $t)
                                                        {
                                                            if($t->locale == $lang && $t->key=="promotional_banner_title"){
                                                                $translate[$lang]['promotional_banner_title'] = $t->value;
                                                            }
                                                        }
                                                    }
                                                ?>
                                    <div class="d-none lang_form" id="{{$lang}}-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="{{$lang}}_title">{{translate('messages.title')}}
                                                ({{strtoupper($lang)}})</label>
                                            <input type="text" name="promotional_banner_title[]" id="{{$lang}}_title" class="form-control"
                                                placeholder="{{translate('messages.new_banner')}}"
                                                value="{{$translate[$lang]['promotional_banner_title']??''}}"
                                                 >
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                    </div>
                                    @endforeach
                                    @else
                                    <div id="default-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{translate('messages.title')}} ({{
                                                translate('messages.default') }})</label>
                                            <input type="text" name="promotional_banner_title[]" class="form-control"
                                                placeholder="{{translate('messages.new_banner')}}"
                                                  value="{{$banner_title?->value}}"
                                                  maxlength="100">
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                    @endif
                                </div>

                            </div>

                        </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 mb-3 mb-lg-12">
            <div class="card h-100">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 d-flex justify-content-between">
                                <span class="d-flex g-1">
                                    <img src="{{dynamicAsset('assets/admin/img/other-banner.png')}}" class="h-85"
                                        alt="">
                                </span>
                                <div>
                                    <div class="blinkings">
                                        <div>
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </div>
                                        <div class="business-notes">
                                            <h6><img src="{{dynamicAsset('assets/admin/img/notes.png')}}" alt="">
                                                {{translate('Note')}}</h6>
                                            <div>
                                                {{translate('messages.This banner will be displayed on the user website and app.')}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <h3 class="form-label d-block mb-5 text-center">
                                    {{translate('Upload_Banner')}} <span class="text-danger">*</span>
                                </h3>

                                <div class="text-center">
                                                @include('admin-views.partials._image-uploader', [
                                                        'id' => 'image-input2',
                                                        'name' => 'promotional_banner_image',
                                                        'ratio' => '9:1',
                                                        'isRequired' => true,
                                                        'existingImage' =>  \App\CentralLogics\Helpers::get_full_url('banner',$banner_image?->value,$banner_image?->storage[0]?->value ?? 'public','upload_image') ,
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
        <div class="btn--container justify-content-end mt-3">
            <button type="submit" class="btn btn--primary">{{translate('messages.Save')}}</button>
        </div>
        </form>

    </div>

</div>


@endsection

@push('script_2')

@endpush
