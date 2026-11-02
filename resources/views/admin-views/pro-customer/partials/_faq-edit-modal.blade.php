<div class="modal fade" id="edit_faq" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog max-w-650px mx-auto modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="" method="post" id="pro-faq-edit-form">
                @csrf
                <div class="modal-header mb-20">
                    <h3 class="mb-0 fs-20 text-center text-capitalize">{{ translate('messages.Edit_FAQ') }}</h3>
                    <button type="button" class="close bg-light w-40px h-40px rounded-circle fs-20" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-4 px-4 pt-0">
                    <div class="bg-light2 p-xl-20 p-3 rounded mb-20">
                        <div class="card-body p-0">
                            @if ($language)
                                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                    <ul class="nav nav-tabs mb-4">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active" href="#" id="default-link-faq-edit">{{ translate('messages.default') }}</a>
                                        </li>
                                        @foreach ($language as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link" href="#" id="{{ $lang }}-link-faq-edit">
                                                    {{ \App\CentralLogics\Helpers::get_language_name($lang) . ' (' . strtoupper($lang) . ')' }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <input type="hidden" name="lang[]" value="default">
                            <div class="lang_form" id="default-form-faq-edit">
                                @include('admin-views.pro-customer.partials._faq-fields', [
                                    'localeLabel' => translate('messages.Default'),
                                    'localeKey' => 'default',
                                    'questionRequired' => true,
                                    'showPriority' => false,
                                    'stack' => true,
                                ])
                            </div>

                            @if ($language)
                                @foreach ($language as $lang)
                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    <div class="d-none lang_form" id="{{ $lang }}-form-faq-edit">
                                        @include('admin-views.pro-customer.partials._faq-fields', [
                                            'localeLabel' => strtoupper($lang),
                                            'localeKey' => $lang,
                                            'questionRequired' => false,
                                            'showPriority' => false,
                                            'stack' => true,
                                        ])
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="bg-light2 p-xl-20 p-3 rounded">
                        <div class="form-group mb-0">
                            <label class="input-label fw-400 text-capitalize">
                                {{ translate('messages.Priority') }}
                                <span class="text-danger">*</span>
                                <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.Lower_number_appears_higher_on_the_FAQ_list') }}">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>
                            </label>
                            <select name="priority" class="custom-select" required>
                                <option value="1">1</option>
                            </select>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end mt-4">
                        <button type="button" class="btn min-w-120 btn--reset text-capitalize" data-dismiss="modal">{{ translate('messages.Cancel') }}</button>
                        <button type="submit" class="btn min-w-120 btn--primary text-capitalize">{{ translate('messages.Update') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
