{{--
    The promotion lists' shared furniture: one action column and one name cell, defined once
    because BOGO and Happy Hour are the same screen with different columns in the middle.

    They had a copy each - .bogo-actions and .hh-actions, identical down to the pixel - which
    is exactly the pair that drifts the next time one list is touched.
--}}
<style>
    /* The action column: a view button that is always there, and a menu holding whatever
       else the row still allows. Both are the same 38px square so the pair reads as one
       control however many options are behind the menu. */
    .promo-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .promo-actions .promo-action-icon {
        width: 38px;
        height: 38px;
        padding: 0;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        flex-shrink: 0;
    }

    /* Words, not icons: an icon in a menu row has to be learnt, and the menu has the room
       for the label the icon was standing in for. */
    .promo-actions .dropdown-menu {
        min-width: 186px;
    }

    .promo-actions .dropdown-item {
        padding: 8px 12px;
        white-space: nowrap;
    }

    /* The name cell holds a title of any length beside a warning that must not be pushed
       out of the row by it. The title takes the space that is left and ellipses; the
       warning keeps its 22px whatever the title does. The full title is on the element's
       own tooltip, so nothing is lost to the truncation.

       max-width rather than width: a short name should not reserve a column's worth of
       space, and the table is table-nowrap, so without a bound one long title would widen
       the whole table and push the action column off the screen. */
    .promo-name {
        display: flex;
        align-items: center;
        gap: 6px;
        max-width: 260px;
    }

    .promo-name__text {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .promo-name .promo-name-warning {
        flex-shrink: 0;
        width: 22px;
        height: 22px;
        min-height: 22px;
        padding: 0;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .promo-name .promo-name-warning i {
        font-size: 12px;
    }
</style>
