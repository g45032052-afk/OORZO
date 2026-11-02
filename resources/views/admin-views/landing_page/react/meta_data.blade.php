@php use App\CentralLogics\Helpers; @endphp
@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))

@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h1 class="page-header-title fs-24 text-capitalize">
                    <div class="card-header-icon d-inline-flex mr-2 img">
                        <img src="{{ dynamicAsset('assets/admin/img/landing-page.png') }}" class="mw-26px"
                             alt="public">
                    </div>
                    <span>
                        {{ translate('React_Landing_Page') }}
                    </span>
                </h1>
            </div>
        </div>
        <div class="mb-15">
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
        </div>

        <div class="card">
            <div class="card-header">
                <div class="">
                    <h3 class="mb-1">{{ translate('Meta Data Setup') }}</h3>
                    <p class="mb-0 gray-dark fs-12">{{translate('Include meta title, description, and image to improve search engine visibility and social media sharing.')}}</p>
                </div>
            </div>
            <div class="card-body p-xl-20 p-3">
                <div class="">
                    <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'react-meta-data') }}" method="POST"
                          enctype="multipart/form-data">

                        @include('admin-views.partials._meta-section',['type' =>'react'])

                        <div class="btn--container justify-content-end mt-4">
                            <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                            <button type="submit" class="btn btn--primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin-views.landing_page.react.partials.header_guideline')

@endsection

@push('script_2')
    <script>
        "use strict";
        document.addEventListener('DOMContentLoaded', function() {
            var removeBtn = document.getElementById('remove_meta_data_image_btn');
            var removeFlag = document.getElementById('react_meta_data_image_remove');
            var fileInput = document.querySelector('input[name="react_meta_data_image"]');
            var previewImg = document.querySelector('.upload-file-img');
            var uploadText = document.querySelector('.upload-file-textbox');
            var form = fileInput ? fileInput.closest('form') : null;

            if (removeBtn && removeFlag && previewImg) {
                removeBtn.addEventListener('click', function () {
                    removeFlag.value = '1';
                    fileInput.value = '';

                    previewImg.style.display = 'none';
                    previewImg.removeAttribute('src');
                    previewImg.removeAttribute('data-default-src');

                    if (uploadText) uploadText.style.display = 'block';
                });
            }

            if (form && removeFlag) {
                form.addEventListener('reset', function () {
                    removeFlag.value = '0';
                    if (previewImg && previewImg.dataset.defaultSrc) {
                        previewImg.src = previewImg.dataset.defaultSrc;
                        previewImg.style.display = 'block';
                    }
                });
            }

            if (fileInput && removeFlag) {
                fileInput.addEventListener('change', function () {
                    removeFlag.value = '0';
                    if (previewImg) previewImg.style.display = 'block';
                    if (uploadText) uploadText.style.display = 'none';
                });
            }
        });

        $(document).ready(function () {
            $('input[name="react_meta_index"][value="0"]').on('change', function () {
                if ($(this).is(':checked')) {
                    $('input[name="react_meta_no_follow"]').prop('checked', true);
                    $('input[name="react_meta_no_image_index"]').prop('checked', true);
                    $('input[name="react_meta_no_archive"]').prop('checked', true);
                    $('input[name="react_meta_no_snippet"]').prop('checked', true);
                }
            });

            $('input[name="react_meta_index"][value="1"]').on('change', function () {
                if ($(this).is(':checked')) {
                    $('input[name="react_meta_no_follow"]').prop('checked', false);
                    $('input[name="react_meta_no_image_index"]').prop('checked', false);
                    $('input[name="react_meta_no_archive"]').prop('checked', false);
                    $('input[name="react_meta_no_snippet"]').prop('checked', false);
                }
            });
        });

    </script>


@endpush

