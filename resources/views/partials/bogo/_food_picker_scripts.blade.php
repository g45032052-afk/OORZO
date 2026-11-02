{{-- BOGO food selection engine, shared by the admin and vendor panels.

     The including page must define these first:
       requiredQty  {buy, get}   how many units each side needs
       selected     {buy: [], get: []}   the working selection
       foodCatalog  []           filled from the restaurant-foods endpoint
       drawer       jQuery el    container select2 anchors its dropdown to
       foodsEndpoint string      url of the restaurant-foods endpoint, used to reopen an
                                 enrolled line read-only

     It provides initFoodSelect2(), openFoodConfig(), render(), resetSelections() and all
     the modal wiring, so neither panel has to restate the picker rules. --}}
<script>
    "use strict";

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        // Food rows carry a thumbnail, name and category, so select2's default text-only
        // rendering is replaced. Data comes from foodCatalog rather than the option markup
        // to keep the <option> list plain.
        function initFoodSelect2($el, placeholder) {
            $el.select2({
                width: '100%',
                placeholder: placeholder,
                dropdownParent: drawer,
                dropdownCssClass: 'food-select2',
                templateResult: function (state) {
                    if (!state.id) return state.text;

                    const food = foodCatalog.find(f => String(f.id) === String(state.id));
                    if (!food) return state.text;

                    return $(
                        '<div class="food-option' + (food.is_available ? '' : ' food-option--off') + '">' +
                            '<div class="food-option__thumb">' +
                                '<img src="' + escapeHtml(food.image_full_url || '') + '" alt="">' +
                                (food.is_available ? '' :
                                    '<span class="food-option__flag">{{ translate('messages.Unavailable') }}</span>') +
                            '</div>' +
                            '<div style="min-width:0">' +
                                '<span class="d-block food-option__name">' + escapeHtml(food.name) + '</span>' +
                                (food.category_name
                                    ? '<span class="d-block food-option__meta">' + escapeHtml(food.category_name) + '</span>'
                                    : '') +
                            '</div>' +
                        '</div>'
                    );
                },
            });

            // select2 rebuilds the dropdown on every open, so the icon is added each time.
            // It doubles as the clear control: once something has been typed the magnifier
            // turns into an X, because a search box that only ever shows a magnifier gives the
            // user nothing to click to get the whole menu back.
            $el.on('select2:open', function () {
                const $search = $('.food-select2 .select2-search--dropdown');
                if ($search.length && !$search.find('.food-search-icon').length) {
                    $search.append('<span class="food-search-icon"><i class="tio-search"></i></span>');
                }
                syncFoodSearchIcon($search.find('.select2-search__field'));
            });
        }



        // Magnifier while the field is empty, X once it is not - and the X has to be clickable,
        // so pointer-events are turned back on only in that state.
        function syncFoodSearchIcon($field) {
            const $icon = $('.food-select2 .food-search-icon');
            if (!$icon.length) return;

            const filled = String($field.val() || '').length > 0;

            $icon.toggleClass('is-clearable', filled)
                 .find('i').attr('class', filled ? 'tio-clear' : 'tio-search');
        }

        $(document).on('input', '.food-select2 .select2-search__field', function () {
            syncFoodSearchIcon($(this));
        });

        // Clearing has to go through the field's own input event: that is what select2 listens
        // to, so anything else empties the box and leaves the filtered list behind it.
        $(document).on('click', '.food-select2 .food-search-icon.is-clearable', function () {
            const $field = $('.food-select2 .select2-search--dropdown .select2-search__field');

            // select2 listens on 'keyup input', so both are raised rather than betting on which.
            $field.val('').trigger('input').trigger('keyup').focus();
            syncFoodSearchIcon($field);
        });

        $(document).on('change', '.food-picker', function () {
            const type = $(this).data('type');
            const food = foodCatalog.find(f => f.id == $(this).val());

            $(this).val('').trigger('change.select2');

            if (food) {
                openFoodConfig(type, null, food);
            }
        });

        function variationText(item) {
            return (item.selected_variations || [])
                .map(v => `${v.name} : ${v.values.label.join(', ')}`)
                .join('  |  ');
        }

        function addOnText(item) {
            return (item.add_on_ids || [])
                .map(id => (item.add_ons.find(a => a.id == id) || {}).name)
                .filter(Boolean)
                .join(', ');
        }

        function render(type) {
            normaliseSelection(type);

            const container = $(`.selected-items[data-type="${type}"]`);
            container.empty();

            selected[type].forEach((item, index) => {
                const variations = variationText(item);
                const addOns = addOnText(item);

                if (index > 0) {
                    container.append(`
                        <div class="d-flex align-items-center gap-2 my-2">
                            <span class="flex-grow-1 border-top"></span>
                            <span class="font-size-sm opacity-75">{{ translate('messages.AND') }}</span>
                            <span class="flex-grow-1 border-top"></span>
                        </div>`);
                }

                container.append(`
                    <div class="bg-white rounded p-3">
                        <div class="d-flex align-items-center gap-3">
                            <img class="rounded onerror-image" style="width:56px;height:56px;object-fit:cover"
                                 data-onerror-image="{{dynamicAsset('assets/admin/img/100x100/food-default-image.png')}}"
                                 src="${item.image || ''}" alt="food">
                            <div class="flex-grow-1">
                                <strong class="d-block">${item.name}
                                    ${Number(item.quantity) > 1
                                        ? `<span class="badge badge-soft-dark ml-1">x${Number(item.quantity)}</span>`
                                        : ''}
                                </strong>
                                ${variations ? `<span class="d-block font-size-sm opacity-75">${variations}</span>` : ''}
                                ${addOns ? `<span class="d-block font-size-sm opacity-75">{{ translate('messages.Add_Ons') }} : ${addOns}</span>` : ''}
                                <a href="javascript:" class="font-size-sm edit-food" data-type="${type}" data-index="${index}">
                                    <i class="tio-edit"></i> {{ translate('messages.Edit') }}</a>
                            </div>
                            <button type="button" class="btn btn-sm btn--danger btn-outline-danger remove-item"
                                    data-type="${type}" data-index="${index}"><i class="tio-delete"></i></button>
                        </div>
                    </div>`);
            });

            const total = selected[type].reduce((sum, i) => sum + Number(i.quantity), 0);
            $(`.${type}-total`).text(total);
            $(`.${type}-total-wrap`).toggleClass('d-none', selected[type].length === 0)
                .css('color', total === requiredQty[type] ? '#019463' : '#dc3545');
        }

        // Two rows are the same row when they name the same food configured the same way: same
        // variation options, same add-ons in the same counts. Anything else is a different
        // thing to buy even though it is the same food, which is why a Medium and a Large of
        // one pizza stay two rows.
        function lineSignature(foodId, variations, addOnIds, addOnQtys) {
            const options = (variations || [])
                .map(v => v.name + ':' + ((v.values || {}).label || []).slice().sort().join(','))
                .sort().join('|');

            const addons = (addOnIds || [])
                .map((id, i) => String(id) + 'x' + ((addOnQtys || [])[i] || 1))
                .sort().join(',');

            return [foodId, options, addons].join('#');
        }

        // Folds identical rows together. Used on prefill: an enrolment saved before rows were
        // merged holds the same food twice, and reopening it would show the duplicate joined
        // by AND that the picker no longer creates.
        function normaliseSelection(type) {
            const merged = [];
            const seen = {};

            selected[type].forEach(function (item) {
                const key = lineSignature(item.food_id, item.selected_variations, item.add_on_ids, item.add_on_qtys);

                if (seen[key] === undefined) {
                    seen[key] = merged.push(item) - 1;
                    return;
                }

                merged[seen[key]].quantity = Number(merged[seen[key]].quantity || 1) + Number(item.quantity || 1);
            });

            selected[type] = merged;
        }

        // Units on one side, optionally ignoring the row that is about to be replaced.
        function sideQuantity(type, ignoreIndex) {
            return selected[type].reduce(function (sum, item, index) {
                return index === ignoreIndex ? sum : sum + Number(item.quantity || 0);
            }, 0);
        }

        /* ---------------- food config modal ---------------- */

        let fc = null;

        function openFoodConfig(type, index, food) {
            const existing = index !== null ? selected[type][index] : null;

            fc = {
                type: type,
                index: index,
                food: food,
                variations: existing ? JSON.parse(JSON.stringify(existing.selected_variations)) : [],
                addOnIds: existing ? existing.add_on_ids.map(String) : [],
                // Keyed by add-on id so a card can carry its own count, matching the
                // add_on_qtys the server already prices against.
                addOnQtys: {},
            };

            if (existing) {
                existing.add_on_ids.forEach(function (id, i) {
                    fc.addOnQtys[String(id)] = (existing.add_on_qtys || [])[i] || 1;
                });
            }

            $('#fc_image').attr('src', food.image_full_url || '');
            $('#fc_name').text(food.name);
            $('#fc_veg_badge')
                .text(food.veg ? '{{ translate('messages.Veg') }}' : '{{ translate('messages.Non_Veg') }}')
                .attr('class', 'badge position-absolute ' + (food.veg ? 'badge-success' : 'badge-danger'));

            // A bundle is valued at the food's BASE price - resolveUnitPrice() reads
            // $food->price and the item's own discount plays no part in it, because the
            // bundle carries its own fixed price instead. Showing the discounted figure
            // here told the vendor a Meat Pizza was worth 80 while the total beneath it,
            // and the bundle price the platform actually stores, both said 100. Whoever
            // sized a bundle against the smaller number priced it wrong.
            $('#fc_old_price').hide();
            $('#fc_price').text(currency(food.price));
            $('#fc_discount').hide();

            renderDescription(food.description || '');
            setFoodConfigReadOnly(false);
            renderVariations();
            renderAddons();
            recalcTotal();

            $('#foodConfigModal').modal('show');
        }

        // The same modal, opened to read rather than to choose. An enrolment is a record of what
        // the restaurant agreed to, so its options are shown ticked but inert and there is
        // nothing to submit - the editing path stays behind Edit & Approve.
        function setFoodConfigReadOnly(on) {
            const modal = $('#foodConfigModal');

            modal.toggleClass('fc-readonly', !!on);
            modal.find('#fc_submit').toggle(!on);
            modal.find('.variation-option').prop('disabled', !!on);
            modal.find('.addon-step').toggle(!on);
            modal.find('.addon-card').css('pointer-events', on ? 'none' : '');
        }

        /**
         * Reopen one enrolled line in the food config modal, read-only.
         *
         * The food is fetched rather than rebuilt from the enrolment, so the panel shows every
         * option the food carries with the agreed ones ticked - the enrolment stores only what
         * was chosen, which on its own would render a list of one.
         */
        function openEnrolledItemView(item) {
            const show = function (foods) {
                const food = (foods || []).find(f => String(f.id) === String(item.food_id));

                if (!food) {
                    toastr.warning('{{ translate('messages.Food_not_found') }}');
                    return;
                }

                fc = {
                    type: item.is_free ? 'get' : 'buy',
                    index: null,
                    food: food,
                    variations: JSON.parse(JSON.stringify(item.selected_variations || [])),
                    addOnIds: (item.add_on_ids || []).map(String),
                    addOnQtys: {},
                };

                (item.add_on_ids || []).forEach(function (id, i) {
                    fc.addOnQtys[String(id)] = (item.add_on_qtys || [])[i] || 1;
                });

                $('#fc_image').attr('src', food.image_full_url || '');
                $('#fc_name').text(food.name);
                $('#fc_veg_badge')
                    .text(food.veg ? '{{ translate('messages.Veg') }}' : '{{ translate('messages.Non_Veg') }}')
                    .attr('class', 'badge position-absolute ' + (food.veg ? 'badge-success' : 'badge-danger'));

                // The frozen price of the line, not today's menu price: what the enrolment agreed.
                $('#fc_old_price').hide();
                $('#fc_price').text(item.price);
                $('#fc_discount').hide();

                renderDescription(food.description || '');
                renderVariations();
                renderAddons();

                $('#fc_total').text(item.price);
                setFoodConfigReadOnly(true);

                $('#foodConfigModal').modal('show');
            };

            if (enrolledFoodCache[item.restaurant_id]) {
                show(enrolledFoodCache[item.restaurant_id]);
                return;
            }

            $.get(foodsEndpoint, {restaurant_id: item.restaurant_id}, function (foods) {
                enrolledFoodCache[item.restaurant_id] = foods;
                show(foods);
            });
        }

        // Kept per restaurant: a drawer is reopened often and the list does not change between
        // opens. foodCatalog is not reused because the picker overwrites it per selection.
        const enrolledFoodCache = {};

        // Delegated: the rows arrive with the drawer, long after this runs.
        $(document).on('click', '.bogo-item-row', function () {
            openEnrolledItemView($(this).data('item') || {});
        });

        function currency(value) {
            return '{{ \App\CentralLogics\Helpers::currency_symbol() }}' + Number(value || 0).toFixed(2);
        }

        function renderDescription(text) {
            $('#fc_description_wrap').toggle(!!text);
            if (!text) return;

            if (text.length > 150) {
                $('#fc_description_short').text(text.slice(0, 150) + '...');
                $('#fc_description_full').text(text).addClass('d-none');
                $('#fc_description_toggle').removeClass('d-none').text('{{ translate('messages.See_More') }}');
            } else {
                $('#fc_description_short').text(text);
                $('#fc_description_full').text('').addClass('d-none');
                $('#fc_description_toggle').addClass('d-none');
            }
        }

        $('#fc_description_toggle').on('click', function () {
            $('#fc_description_short, #fc_description_full').toggleClass('d-none');
            $(this).text($(this).text() === '{{ translate('messages.See_More') }}'
                ? '{{ translate('messages.See_Less') }}'
                : '{{ translate('messages.See_More') }}');
        });

        function selectedLabels(groupName) {
            const group = fc.variations.find(v => v.name === groupName);
            return group ? group.values.label : [];
        }

        function renderVariations() {
            const wrap = $('#fc_variations').empty();
            // Re-rendered on every change, so the inert state has to be reapplied afterwards.
            const readOnly = $('#foodConfigModal').hasClass('fc-readonly');

            (fc.food.variations || []).forEach((group, gi) => {
                const required = group.required === 'on' || group.required === true;
                const min = parseInt(group.min || 0, 10);
                const max = parseInt(group.max || 0, 10);
                const single = group.type === 'single';
                const picked = selectedLabels(group.name);

                const rule = single
                    ? '{{ translate('messages.Select_one_option') }}'
                    : '{{ translate('messages.You_need_to_select_minimum') }} ' + min +
                      ' {{ translate('messages.To_maximum') }} ' + max + ' {{ translate('messages.Options') }}';

                wrap.append(`
                    <div class="bg-light rounded p-3 mb-3">
                        <span class="fs-18 font-bold">${group.name}</span>
                        <span class="fs-16 opacity-75">${required ? ' ({{ translate('messages.Required') }})' : ''}</span>
                        <div class="fs-14 opacity-75 mb-3">${rule}</div>
                        ${(group.values || []).map(v => `
                            <div class="d-flex align-items-center justify-content-between py-2">
                                <div class="form-check mb-0">
                                    <input class="form-check-input variation-option" type="${single ? 'radio' : 'checkbox'}"
                                           name="fc_group_${gi}" id="fc_${gi}_${v.label.replace(/\W/g, '')}"
                                           data-group="${gi}" value="${v.label}" ${picked.includes(v.label) ? 'checked' : ''}>
                                    <label class="form-check-label" for="fc_${gi}_${v.label.replace(/\W/g, '')}">${v.label}</label>
                                </div>
                                <span class="${picked.includes(v.label) ? 'font-bold text-dark' : 'opacity-75'}">${currency(v.optionPrice)}</span>
                            </div>`).join('')}
                    </div>`);
            });

            if (readOnly) {
                wrap.find('.variation-option').prop('disabled', true);
            }
        }

        $(document).on('change', '.variation-option', function () {
            const gi = $(this).data('group');
            const group = fc.food.variations[gi];
            const single = group.type === 'single';
            const max = parseInt(group.max || 0, 10);
            const label = $(this).val();

            let picked = selectedLabels(group.name).slice();

            if (single) {
                picked = [label];
            } else if ($(this).is(':checked')) {
                if (max && picked.length >= max) {
                    $(this).prop('checked', false);
                    toastr.warning('{{ translate('messages.You_can_select_maximum') }} ' + max + ' {{ translate('messages.Options') }}');
                    return;
                }
                picked.push(label);
            } else {
                picked = picked.filter(l => l !== label);
            }

            fc.variations = fc.variations.filter(v => v.name !== group.name);
            if (picked.length) {
                fc.variations.push({name: group.name, values: {label: picked}});
            }

            renderVariations();
            recalcTotal();
        });

        function addOnQty(id) {
            return fc.addOnQtys[String(id)] || 1;
        }

        function renderAddons() {
            const addons = fc.food.add_ons || [];
            const readOnly = $('#foodConfigModal').hasClass('fc-readonly');
            // Read-only shows only what was agreed; the rest of the menu's add-ons are noise on
            // a record of one line.
            const visible = readOnly ? addons.filter(a => fc.addOnIds.includes(String(a.id))) : addons;
            $('#fc_addon_wrap').toggle(visible.length > 0);

            $('#fc_addons').html(visible.map(function (a) {
                const on = fc.addOnIds.includes(String(a.id));

                return '<div class="addon-card' + (on ? ' is-selected' : '') + '" data-id="' + a.id + '">' +
                    '<span class="d-block addon-card__name">' + escapeHtml(a.name) + '</span>' +
                    '<span class="d-block addon-card__price">' + currency(a.price) + '</span>' +
                    (on
                        ? '<div class="addon-card__qty">' +
                              (readOnly ? '' : '<button type="button" class="addon-step" data-step="-1">&minus;</button>') +
                              '<span class="addon-card__count">' + addOnQty(a.id) + '</span>' +
                              (readOnly ? '' : '<button type="button" class="addon-step" data-step="1">+</button>') +
                          '</div>'
                        : '') +
                    '</div>';
            }).join(''));
        }

        // The stepper lives inside the card, so its clicks must not toggle the card off.
        $(document).on('click', '.addon-step', function (e) {
            e.stopPropagation();

            const id = String($(this).closest('.addon-card').data('id'));
            const next = addOnQty(id) + parseInt($(this).data('step'), 10);

            if (next < 1) return;

            fc.addOnQtys[id] = next;
            renderAddons();
            recalcTotal();
        });

        $(document).on('click', '.addon-card', function (e) {
            if ($(e.target).closest('.addon-step').length) return;

            const id = String($(this).data('id'));

            if (fc.addOnIds.includes(id)) {
                fc.addOnIds = fc.addOnIds.filter(a => a !== id);
                delete fc.addOnQtys[id];
            } else {
                fc.addOnIds = fc.addOnIds.concat(id);
                fc.addOnQtys[id] = 1;
            }

            renderAddons();
            recalcTotal();
        });

        // Mirrors resolveUnitPrice() server side: full price plus variation and add-on cost.
        // BOGO suppresses the item's own discount, so discounted_price is display only.
        function unitPrice() {
            let price = Number(fc.food.price || 0);

            (fc.food.variations || []).forEach(group => {
                const picked = selectedLabels(group.name);
                (group.values || []).forEach(v => {
                    if (picked.includes(v.label)) price += Number(v.optionPrice || 0);
                });
            });

            (fc.food.add_ons || []).forEach(a => {
                if (fc.addOnIds.includes(String(a.id))) {
                    price += Number(a.price || 0) * addOnQty(a.id);
                }
            });

            return price;
        }

        function recalcTotal() {
            $('#fc_total').text(currency(unitPrice()));
        }

        $('#fc_submit').on('click', function () {
            // Enforce each group's own required/min rule before accepting.
            for (const group of (fc.food.variations || [])) {
                const required = group.required === 'on' || group.required === true;
                const min = parseInt(group.min || 0, 10);
                const picked = selectedLabels(group.name);

                if (required && picked.length === 0) {
                    toastr.error(group.name + ' {{ translate('messages.is_required') }}');
                    return;
                }
                if (min && picked.length && picked.length < min) {
                    toastr.error('{{ translate('messages.You_need_to_select_minimum') }} ' + min +
                        ' {{ translate('messages.Options') }} - ' + group.name);
                    return;
                }
            }

            const editing = fc.index !== null;
            // Editing carries the row's own count across; a fresh pick is always one unit.
            const addedQty = editing ? Number(selected[fc.type][fc.index].quantity || 1) : 1;

            // The offer's own quantity is the hard cap. The server already refuses a third buy
            // food on a Buy 2 offer, but only after Join, which left the vendor building a
            // selection the panel had let them build and then rejected wholesale.
            const required = Number(requiredQty[fc.type] || 0);

            if (required && sideQuantity(fc.type, fc.index) + addedQty > required) {
                toastr.warning('{{ translate('messages.You_can_add_a_maximum_of') }} ' + required +
                    ' {{ translate('messages.quantities_for_this_BOGO_offer') }}');
                return;
            }

            // The food's own cart cap, counted in units rather than rows: one row may now hold
            // several units of the same food.
            const max = fc.food.maximum_cart_quantity || 0;

            if (max) {
                const alreadyOn = selected[fc.type].reduce(function (sum, i, idx) {
                    return (idx !== fc.index && i.food_id === fc.food.id)
                        ? sum + Number(i.quantity || 0)
                        : sum;
                }, 0);

                if (alreadyOn + addedQty > max) {
                    toastr.warning('{{ translate('messages.You_can_add_a_maximum_of') }} ' + max +
                        ' {{ translate('messages.quantities_for_this_food') }}');
                    return;
                }
            }

            const entry = {
                food_id: fc.food.id,
                name: fc.food.name,
                image: fc.food.image_full_url,
                quantity: 1,
                max: fc.food.maximum_cart_quantity || 0,
                variations: fc.food.variations || [],
                selected_variations: fc.variations,
                add_ons: fc.food.add_ons || [],
                add_on_ids: fc.addOnIds,
                // Positional, matching add_on_ids - that is the shape resolveUnitPrice reads.
                add_on_qtys: fc.addOnIds.map(id => addOnQty(id))
            };

            // Adding what is already on the list is one more of it, not a second identical
            // row. The list used to grow a duplicate joined by AND, which read as two different
            // foods to buy when it was one food twice.
            const signature = lineSignature(entry.food_id, entry.selected_variations, entry.add_on_ids, entry.add_on_qtys);
            const twin = selected[fc.type].findIndex((i, idx) =>
                idx !== fc.index &&
                lineSignature(i.food_id, i.selected_variations, i.add_on_ids, i.add_on_qtys) === signature);

            if (twin > -1) {
                selected[fc.type][twin].quantity = Number(selected[fc.type][twin].quantity || 1) + addedQty;
                // Editing a row into an existing one leaves the row it came from behind. The
                // twin was mutated by reference above, so the splice cannot disturb it.
                if (editing) selected[fc.type].splice(fc.index, 1);
            } else if (editing) {
                selected[fc.type][fc.index] = Object.assign(entry, {quantity: addedQty});
            } else {
                selected[fc.type].push(entry);
            }

            $('#foodConfigModal').modal('hide');
            render(fc.type);
        });

        $(document).on('click', '.edit-food', function () {
            const type = $(this).data('type'), index = $(this).data('index');
            const item = selected[type][index];
            const food = foodCatalog.find(f => f.id == item.food_id);

            if (food) openFoodConfig(type, index, food);
        });

        $(document).on('click', '.remove-item', function () {
            const type = $(this).data('type');
            selected[type].splice($(this).data('index'), 1);
            render(type);
        });

        function resetSelections() {
            selected.buy = [];
            selected.get = [];
            render('buy');
            render('get');
        }

</script>
