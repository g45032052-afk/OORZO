@extends('layouts.admin.app')

@section('title',translate('Add_New_Campaign'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i class="tio-add-circle-outlined"></i></div>
                        {{translate('messages.Add_New_Campaign')}}
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.campaign.store-basic')}}" method="post" enctype="multipart/form-data" id="campaign-form">
                    @csrf
                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = str_replace('_', '-', app()->getLocale()))
                    @if($language)
                                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                        <ul class="nav nav-tabs mb-4">
                            <li class="nav-item">
                                <a class="nav-link lang_link active" href="#" id="default-link">{{ translate('Default') }}</a>
                            </li>
                            @foreach(json_decode($language) as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link"  href="#" id="{{$lang}}-link">{{\App\CentralLogics\Helpers::get_language_name($lang).'('.strtoupper($lang).')'}}</a>
                                </li>
                            @endforeach
                        </ul>
                                </div>
                        <div class="mb-1 lang_form" id="default-form">
                            <div class="form-group">
                                <label class="input-label" for="default_title">{{translate('messages.title')}} ({{ translate('Default') }})</label>
                                <input type="text"  name="title[]" id="default_title" class="form-control h--45px" placeholder="{{ translate('messages.Ex_:_Campaign') }}"  >
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            <div class="form-group mb-0">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.short_description')}} ({{ translate('Default') }})</label>
                                <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                            </div>
                        </div>
                        @foreach(json_decode($language) as $lang)
                            <div class="mb-1 d-none lang_form" id="{{$lang}}-form">
                                <div class="form-group">
                                    <label class="input-label" for="{{$lang}}_title">{{translate('messages.title')}} ({{strtoupper($lang)}})</label>
                                    <input type="text"  name="title[]" id="{{$lang}}_title" class="form-control h--45px" placeholder="{{ translate('messages.Ex_:_Campaign') }} "  >
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.short_description')}} ({{strtoupper($lang)}})</label>
                                    <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                                </div>
                            </div>
                        @endforeach
                    @else
                    <div class="mb-1" id="default-form">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.title')}} ({{ translate('Default') }})</label>
                            <input type="text" name="title[]" class="form-control h--45px" placeholder="{{ translate('messages.Ex_:_Campaign') }} " >
                        </div>
                        <input type="hidden" name="lang[]" value="default">
                        <div class="form-group mb-0">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.short_description')}} ({{ translate('Default') }})</label>
                            <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                        </div>
                    </div>
                    @endif
                    <div class="row mt-3">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="title">{{translate('messages.start_date')}}</label>
                                        <input type="date" id="date_from" class="form-control h--45px" required="" name="start_date">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="input-label" for="title">{{translate('messages.end_date')}}</label>
                                    <input type="date" id="date_to" class="form-control h--45px" required="" name="end_date">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label text-capitalize" for="title">{{translate('messages.daily_start_time')}}</label>
                                        <input type="time" id="start_time" class="form-control h--45px" name="start_time">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="input-label text-capitalize" for="title">{{translate('messages.daily_end_time')}}</label>
                                    <input type="time" id="end_time" class="form-control h--45px" name="end_time">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex flex-column align-items-center gap-3 mt-4">
                                <p class="mb-0">{{ translate('campaign_image') }}</p>

                                @include('admin-views.partials._image-uploader', [
                                    'id' => 'image-input',
                                    'name' => 'image',
                                    'isRequired' => true,
                                    'existingImage' => null,
                                    'ratio' => '2:1',
                                    'imageExtension' => IMAGE_EXTENSION,
                                    'imageFormat' => IMAGE_FORMAT,
                                    'maxSize' => MAX_FILE_SIZE,
                                ])
                            </div>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{dynamicAsset('assets/admin')}}/js/view-pages/basic-campaign-index.js"></script>
    <script>
        "use strict";
        $('#campaign-form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.campaign.store-basic')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{ translate('Campaign_created_successfully!') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('admin.campaign.list', 'basic')}}';
                        }, 2000);
                    }
                }
            });
        });


            $('#reset_btn').click(function(){
                $('#choice_item').val(null).trigger('change');
            })
        </script>
@endpush
