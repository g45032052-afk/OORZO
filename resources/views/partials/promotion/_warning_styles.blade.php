{{--
    How the "not reaching customers" reasons are set, in one place because they are shown in
    three: the modal the warning icon opens, and the reason block inside both details drawers.

    The theme's .alert-soft-warning writes in #f5ca99 on a 10% amber wash, which is right for
    a passing note and wrong for this. These are the answer to "why is my offer not showing?" -
    sentences to be read, and acted on - so they are set the way the panel sets any other
    body copy: dark text on a quiet neutral card, with one amber rule down the side to say
    what kind of thing it is. The alarm is carried by the modal's own icon; repeating it in
    the text only made the text harder to read.

    Included by partials.promotion._visibility_modal, which every screen that can show a
    reason already includes, so the drawers inherit these without carrying a copy.
--}}
<style>
    :root {
        /* A true red, readable at 13px on a light card. The theme's own --danger-clr
           (#ff6d6d) and .text-danger (#ed4c78) are both pitched at badges, where the tint
           behind them carries the meaning and the text only has to be legible on it. */
        --promo-reason-clr: #d92d20;
    }

    /* Wider than the panel's stock warning dialog. That one carries a sentence; this carries
       a list of them, each naming a food and often a time window, and at 436px every row
       wrapped three deep. */
    .promo-visibility-dialog {
        max-width: 520px !important;
    }

    /* The dialog's own body colour is #a0a0a0, which is a caption grey. The sentence above
       the list is the one that explains the modal, so it is given ordinary reading contrast. */
    .promo-visibility-dialog .modal-body > .max-349 p {
        color: rgba(51, 66, 87, 0.72);
    }

    .promo-reasons {
        background: var(--section-bg2, #F9FAFC) !important;
        border: 1px solid rgba(51, 66, 87, 0.10);
        /* Red with the sentences: one accent to a block, and this one is the same failure
           the modal's own icon is announcing. */
        border-left: 3px solid var(--promo-reason-clr);
        border-radius: 6px;
        padding: 14px 16px;
        text-align: left;
    }

    /* A field label, not a headline: it names the list below it and then gets out of the way,
       the same way the panel labels a column. */
    .promo-reasons__label {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(51, 66, 87, 0.7);
    }

    .promo-reasons__list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    /* Hairlines rather than bullets: several of these sentences carry a food name and a time
       window, and a bulleted wrap read as one run-on paragraph. The marker is a flex item so
       a wrapped line keeps its indent under the first, whatever the font does. */
    .promo-reasons__list li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 9px 0;
        font-size: 13px;
        line-height: 1.6;
        color: var(--promo-reason-clr);
    }

    .promo-reasons__list li + li {
        border-top: 1px solid rgba(51, 66, 87, 0.08);
    }

    /* The line under the list is an aside, not a second instruction. */
    .promo-reasons__footnote {
        font-size: 12px;
        line-height: 1.5;
        color: rgba(51, 66, 87, 0.6);
    }

    .promo-reasons__list li::before {
        content: '';
        flex-shrink: 0;
        /* Half a line down, so it sits against the first line's centre rather than its top. */
        margin-top: 8px;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--promo-reason-clr);
    }
</style>
