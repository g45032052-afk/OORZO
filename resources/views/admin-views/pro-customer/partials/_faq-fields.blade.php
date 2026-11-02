@php($_locale = $localeLabel ?? translate('messages.Default'))
@php($_localeKey = $localeKey ?? 'default')
@php($_required = $questionRequired ?? false)
@php($_showPriority = $showPriority ?? false)
@php($_max = $maxPriority ?? 1)
@php($_selected = $selectedPriority ?? $_max)
@php($_stack = $stack ?? false)
@php($_colClass = $_stack ? 'col-md-12' : 'col-md-6')

<div class="row g-3">
    <div class="{{ $_colClass }}">
        <div class="form-group mb-2">
            <label class="input-label fw-400 text-capitalize">
                {{ translate('messages.Question') }} ({{ $_locale }})
                @if($_required)<span class="text-danger">*</span>@endif
                <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.Write_a_short_question_within_50_characters') }}">
                    <i class="tio-info text-gray1 fs-16"></i>
                </span>
            </label>
            <input type="text" name="question[]" maxlength="50"
                data-locale="{{ $_localeKey }}"
                class="form-control pro-faq-question-input"
                placeholder="{{ translate('messages.How_does_pro_membership_work') }}"
                {{ $_required ? 'required' : '' }}>
            <div class="d-flex justify-content-end">
                <span class="text-body-light text-right d-block mt-1 pro-faq-question-counter">0/50</span>
            </div>
        </div>

        @if ($_showPriority)
            <div class="form-group mb-0">
                <label class="input-label fw-400 text-capitalize">
                    {{ translate('messages.Priority') }}
                    <span class="text-danger">*</span>
                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.Lower_number_appears_higher_on_the_FAQ_list') }}">
                        <i class="tio-info text-gray1 fs-16"></i>
                    </span>
                </label>
                <select name="priority" class="custom-select" required>
                    @for($i = 1; $i <= max(1, $_max); $i++)
                        <option value="{{ $i }}" {{ $i === $_selected ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        @endif
    </div>
    <div class="{{ $_colClass }}">
        <div class="form-group mb-0">
            <label class="input-label fw-400 text-capitalize">
                {{ translate('messages.Answer') }} ({{ $_locale }})
                @if($_required)<span class="text-danger">*</span>@endif
                <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.Write_a_clear_answer_within_150_characters') }}">
                    <i class="tio-info text-gray1 fs-16"></i>
                </span>
            </label>
            <textarea rows="{{ $_stack ? 4 : 6 }}" name="answer[]" maxlength="150"
                data-locale="{{ $_localeKey }}"
                class="form-control pro-faq-answer-input"
                placeholder="{{ translate('messages.Pro_members_get_exclusive_discounts_free_delivery_and_special_coupons.') }}"
                {{ $_required ? 'required' : '' }}></textarea>
            <div class="d-flex justify-content-end">
                <span class="text-body-light text-right d-block mt-1 pro-faq-answer-counter">0/150</span>
            </div>
        </div>
    </div>
</div>
