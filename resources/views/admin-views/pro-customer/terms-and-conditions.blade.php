@extends('layouts.admin.app')

@section('title', translate('messages.Terms_and_Conditions'))
@section('pro_customer_additional_setup', 'active')

@section('content')
<div class="content container-fluid">
    <div class="page-header mb-2">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <h1 class="page-header-title text-capitalize fs-24">
                <span>{{ translate('messages.Terms_and_Conditions') }}</span>
            </h1>
        </div>
    </div>

    @include('admin-views.pro-customer.partials._additional-tabs')

    <form action="{{ route('admin.pro-customer.terms-and-conditions.update') }}" method="post" id="pro-terms-form">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="bg-light2 p-xl-20 p-3 rounded">
                    <div class="card-body p-0">
                        @if ($language)
                            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                <ul class="nav nav-tabs mb-4">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link active" href="#" id="default-link-terms">{{ translate('messages.Default') }}</a>
                                    </li>
                                    @foreach ($language as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link" href="#" id="{{ $lang }}-link-terms">
                                                {{ \App\CentralLogics\Helpers::get_language_name($lang) . ' (' . strtoupper($lang) . ')' }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <input type="hidden" name="lang[]" value="default">
                        <div class="lang_form" id="default-form-terms">
                            <div class="row g-1">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label fw-400 text-capitalize">
                                            {{ translate('messages.Page_Title') }} ({{ translate('messages.Default') }})
                                            <span class="text-danger">*</span>
                                            <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.Title_shown_at_the_top_of_the_terms_page') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                        <input type="text" name="page_title[]" maxlength="70"
                                            class="form-control pro-terms-title-input"
                                            placeholder="{{ translate('messages.Pro_Membership_Terms_and_Conditions') }}"
                                            value="{{ $termsTitleRow?->getRawOriginal('value') }}" required>
                                        <div class="d-flex justify-content-end">
                                            <span class="text-body-light text-right d-block mt-1 pro-terms-title-counter">0/70</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label fw-400 text-capitalize">
                                            {{ translate('messages.Page_Description') }} ({{ translate('messages.Default') }})
                                            <span class="text-danger">*</span>
                                            <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.Full_terms_text_displayed_to_pro_customers') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                        <textarea class="pro-terms-editor form-control" id="pro-terms-desc-default" name="page_description[]" required>{{ $termsDescRow?->getRawOriginal('value') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($language)
                            @php($termsTranslations = collect())
                            @if ($termsTitleRow)
                                @php($termsTranslations = $termsTranslations->merge(\App\Models\Translation::where('translationable_type', \App\Models\DataSetting::class)->where('translationable_id', $termsTitleRow->id)->get()))
                            @endif
                            @if ($termsDescRow)
                                @php($termsTranslations = $termsTranslations->merge(\App\Models\Translation::where('translationable_type', \App\Models\DataSetting::class)->where('translationable_id', $termsDescRow->id)->get()))
                            @endif

                            @foreach ($language as $lang)
                                @php($titleTrans = $termsTranslations->first(fn($t) => $t->locale === $lang && $t->key === 'pro_terms_page_title'))
                                @php($descTrans = $termsTranslations->first(fn($t) => $t->locale === $lang && $t->key === 'pro_terms_page_description'))
                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                <div class="d-none lang_form" id="{{ $lang }}-form-terms">
                                    <div class="row g-1">
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label class="input-label fw-400 text-capitalize">
                                                    {{ translate('messages.Page_Title') }} ({{ strtoupper($lang) }})
                                                </label>
                                                <input type="text" name="page_title[]" maxlength="70"
                                                    class="form-control"
                                                    placeholder="{{ translate('messages.Pro_Membership_Terms_and_Conditions') }}"
                                                    value="{{ $titleTrans?->value }}">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-0">
                                                <label class="input-label fw-400 text-capitalize">
                                                    {{ translate('messages.Page_Description') }} ({{ strtoupper($lang) }})
                                                </label>
                                                <textarea class="pro-terms-editor form-control" id="pro-terms-desc-{{ $lang }}" name="page_description[]">{{ $descTrans?->value }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="btn--container justify-content-end mt-4 px-3 pb-3">
                <button type="reset" class="btn min-w-120 btn--reset text-capitalize">{{ translate('messages.Reset') }}</button>
                <button type="submit" class="btn min-w-120 btn--primary text-capitalize">
                    <i class="tio-save"></i> {{ translate('messages.Save_Information') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('css_or_js')
<style>
    /* Keep CKEditor dialogs above any high-z-index admin layer (Select2 hits 99999999999999). */
    .cke_dialog_container,
    .cke_dialog_background_cover,
    .cke_dialog,
    .cke_panel,
    .cke_combopanel { z-index: 2147483640 !important; }
</style>
@endpush

@push('script_2')
<script src="{{ dynamicAsset('assets/admin/ckeditor/ckeditor.js') }}"></script>
<script>
    "use strict";

    // ---- Minimal CKEditor config ----
    // Use CKEditor's default toolbarGroups (so plugins like Link wire up the same way
    // they do on the rest of the admin) and just strip the buttons we don't want.
    const PRO_TERMS_CKEDITOR_CONFIG = {
        toolbarGroups: [
            { name: 'clipboard', groups: ['undo'] },
            { name: 'basicstyles', groups: ['basicstyles'] },
            { name: 'paragraph', groups: ['list', 'align'] },
            
            { name: 'insert' },
            { name: 'styles' }
        ],
        removeButtons: 'Cut,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,'
            + 'Anchor,Image,Iframe,Table,HorizontalRule,PageBreak,'
            + 'Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,'
            + 'Strike,Subscript,Superscript,RemoveFormat,Outdent,Indent,Blockquote,CreateDiv,'
            + 'BidiLtr,BidiRtl,Language,Source,Maximize,ShowBlocks,About,Templates,Preview,Print,'
            + 'NewPage,Save,Styles,Font,FontSize,TextColor,BGColor',
        removePlugins: 'elementspath',
        resize_enabled: false,
        height: 220,
        // Push CKEditor floats above the admin theme's high z-indexes. Must stay below the
        // 32-bit int max (~2.1B) — anything higher is treated as invalid and the dialog
        // disappears, which was breaking the Link button.
        baseFloatZIndex: 2000000000
    };

    // Snapshot original content keyed by element id so reset restores per-locale.
    const proTermsOriginalContent = {};

    $(document).ready(function () {
        $('.pro-terms-editor').each(function () {
            const id = this.id;
            if (!id) return;
            proTermsOriginalContent[id] = $(this).val();
            if (!CKEDITOR.instances[id]) {
                CKEDITOR.replace(id, PRO_TERMS_CKEDITOR_CONFIG);
            }
        });
    });

    // Reset: native reset clears <input>/<textarea> but CKEditor mirrors via JS,
    // so we have to push the snapshotted HTML back into each instance.
    $('#pro-terms-form').on('reset', function () {
        setTimeout(function () {
            Object.keys(proTermsOriginalContent).forEach(function (id) {
                const editor = CKEDITOR.instances[id];
                if (editor) {
                    editor.setData(proTermsOriginalContent[id] || '');
                }
            });
        }, 0);
    });

    // Title character counter.
    $('.pro-terms-title-input').on('input', function () {
        $(this).closest('.form-group').find('.pro-terms-title-counter').text($(this).val().length + '/70');
    }).trigger('input');
</script>
@endpush
