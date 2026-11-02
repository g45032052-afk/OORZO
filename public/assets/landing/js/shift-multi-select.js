document.addEventListener('DOMContentLoaded', function () {
    initializeShiftMultiSelect();
});

function initializeShiftMultiSelect() {
    const container = document.querySelector('.multi-select-container');

    if (!container) {
        console.warn('Shift multi-select container not found');
        return;
    }

    const selectBox = container.querySelector('.select-box');
    const dropdownList = container.querySelector('.dropdown-list');
    const tagsContainer = container.querySelector('.tags-container');
    const checkboxes = container.querySelectorAll('input[type="checkbox"]');
    const earningSelect = document.querySelector('select[name="earning"]');
    const shiftInfoText = document.getElementById('shift-info-text');
    const shiftView = document.getElementById('shift-view');

    // Parse translations
    let translations = {};
    try {
        const translationsJson = container.getAttribute('data-translations');
        translations = JSON.parse(translationsJson);
    } catch (e) {
        console.error('Error parsing translations:', e);
        translations = {
            'full_day': 'Full Day',
            'select_shifts': 'Select Shifts',
            'salary_text': 'Salary based delivery men work according to their contract/assigned hours.',
            'full_day_text': 'You will receive delivery orders 24/7.',
            'specific_shift_text_1': 'You will only receive delivery orders during the',
            'specific_shift_text_2': 'shifts. Orders outside these time slots will not be received.',
            'no_shift_text': 'Please select a shift to see availability.'
        };
    }

    // =====================================================
    // EVENT: Click on select-box to toggle dropdown
    // =====================================================
    selectBox.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = dropdownList.classList.contains('open');

        if (isOpen) {
            dropdownList.classList.remove('open');
            selectBox.classList.remove('open');
        } else {
            dropdownList.classList.add('open');
            selectBox.classList.add('open');
        }
    });

    // =====================================================
    // EVENT: Click outside to close dropdown
    // =====================================================
    document.addEventListener('click', function (e) {
        if (!container.contains(e.target)) {
            dropdownList.classList.remove('open');
            selectBox.classList.remove('open');
        }
    });
    // =====================================================
    // EVENT: Click on dropdown list items
    // =====================================================
    dropdownList.addEventListener('click', function (e) {
        // If the user clicked the checkbox directly, let natural behavior happen
        // The 'change' handler below will take care of the logic.
        if (e.target.matches('input[type="checkbox"]')) {
            return;
        }

        const optionItem = e.target.closest('.option-item');
        if (!optionItem) return;

        e.preventDefault();

        // If clicked on the label/row, manually toggle
        const checkbox = optionItem.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
        }
    });

    // =====================================================
    // EVENT: Handle checkbox changes
    // =====================================================
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const isFullDay = this.classList.contains('full-day-checkbox');
            const isChecked = this.checked;

            if (isFullDay) {
                // If "Full Day" is checked, uncheck and disable all slots
                if (isChecked) {
                    checkboxes.forEach(cb => {
                        if (cb.classList.contains('slot-checkbox')) {
                            cb.checked = false;
                            cb.disabled = true;
                            // Add disabled class to parent label for styling
                            const label = cb.closest('.option-item');
                            if (label) {
                                label.classList.add('disabled');
                                label.classList.remove('active');
                            }
                        }
                    });
                    this.closest('.option-item').classList.add('active');
                    // **CHANGED: Remove disabled class when Full Day is checked**
                    this.closest('.option-item').classList.remove('disabled');
                    // **CHANGED: Enable Full Day checkbox**
                    this.disabled = false;
                } else {
                    // Enable other slots when Full Day is unchecked
                    checkboxes.forEach(cb => {
                        if (cb.classList.contains('slot-checkbox')) {
                            cb.disabled = false;
                            const label = cb.closest('.option-item');
                            if (label) {
                                label.classList.remove('disabled');
                            }
                        }
                    });
                    this.closest('.option-item').classList.remove('active');
                }
            } else {
                // If a slot checkbox state changes
                const fullDayCb = container.querySelector('.full-day-checkbox');
                
                if (isChecked) {
                    // When a slot is checked, uncheck and deactivate Full Day
                    if (fullDayCb && fullDayCb.checked) {
                        fullDayCb.checked = false;
                        const fullDayLabel = fullDayCb.closest('.option-item');
                        if (fullDayLabel) {
                            fullDayLabel.classList.remove('active');
                        }
                        // Enable all slot checkboxes
                        checkboxes.forEach(cb => {
                            if (cb.classList.contains('slot-checkbox')) {
                                cb.disabled = false;
                                const label = cb.closest('.option-item');
                                if (label) label.classList.remove('disabled');
                            }
                        });
                    }
                    
                    // **ADDED: Disable and add disabled class to Full Day when any slot is checked**
                    if (fullDayCb) {
                        fullDayCb.disabled = true;
                        const fullDayLabel = fullDayCb.closest('.option-item');
                        if (fullDayLabel) {
                            fullDayLabel.classList.add('disabled');
                        }
                    }
                    
                    // Add active class to checked slot
                    const slotLabel = this.closest('.option-item');
                    if (slotLabel) {
                        slotLabel.classList.add('active');
                    }
                } else {
                    // When a slot is unchecked, just remove active class
                    const slotLabel = this.closest('.option-item');
                    if (slotLabel) {
                        slotLabel.classList.remove('active');
                    }
                    
                    // **ADDED: Check if any other slots are still checked**
                    let hasAnySlotChecked = false;
                    checkboxes.forEach(cb => {
                        if (cb.classList.contains('slot-checkbox') && cb.checked) {
                            hasAnySlotChecked = true;
                        }
                    });
                    
                    // **ADDED: Enable Full Day if no slots are checked**
                    if (!hasAnySlotChecked && fullDayCb) {
                        fullDayCb.disabled = false;
                        const fullDayLabel = fullDayCb.closest('.option-item');
                        if (fullDayLabel) {
                            fullDayLabel.classList.remove('disabled');
                        }
                    }
                }
            }

            updateTagsDisplay();
            updateShiftInfo();
        });
    });

    // =====================================================
    // FUNCTION: Update tags display
    // =====================================================
    function updateTagsDisplay() {
        tagsContainer.innerHTML = '';
        const selectedShifts = [];
        let hasFullDay = false;

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                if (checkbox.classList.contains('full-day-checkbox')) {
                    hasFullDay = true;
                    selectedShifts.push({
                        id: 'full-day',
                        name: translations['full_day'] || 'Full Day',
                        isFullDay: true
                    });
                } else {
                    selectedShifts.push({
                        id: checkbox.value,
                        name: checkbox.dataset.name,
                        timeRange: checkbox.dataset.timeRange
                    });
                }
            }
        });

        if (selectedShifts.length === 0) {
            const placeholder = document.createElement('span');
            placeholder.className = 'placeholder-text';
            placeholder.textContent = translations['select_shifts'] || 'Select Shifts';
            tagsContainer.appendChild(placeholder);
        } else {
            selectedShifts.forEach(shift => {
                const badge = document.createElement('span');
                badge.className = 'tag-badge';

                let badgeText = shift.name;
                if (shift.timeRange) {
                    badgeText += ' (' + shift.timeRange + ')';
                }

                badge.innerHTML = `
                    <span>${badgeText}</span>
                    <span class="remove-tag" data-shift-id="${shift.id}">×</span>
                `;

                badge.querySelector('.remove-tag').addEventListener('click', function (e) {
                    e.stopPropagation();

                    // Find and uncheck the corresponding checkbox
                    checkboxes.forEach(cb => {
                        if (shift.isFullDay) {
                            if (cb.classList.contains('full-day-checkbox')) {
                                cb.checked = false;
                                cb.dispatchEvent(new Event('change'));
                            }
                        } else {
                            if (cb.value === shift.id) {
                                cb.checked = false;
                                cb.dispatchEvent(new Event('change'));
                            }
                        }
                    });
                });

                tagsContainer.appendChild(badge);
            });
        }
    }

    // =====================================================
    // FUNCTION: Update shift info text
    // =====================================================
    function updateShiftInfo() {
        if (!shiftInfoText) return;

        const earningType = earningSelect ? earningSelect.value : '1';
        const selectedShifts = [];
        let hasFullDay = false;

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                if (checkbox.classList.contains('full-day-checkbox')) {
                    hasFullDay = true;
                } else {
                    selectedShifts.push({
                        name: checkbox.dataset.name,
                        timeRange: checkbox.dataset.timeRange
                    });
                }
            }
        });

        let infoText = '';

        if (earningType == '0') {
            // Salary based
            infoText = translations['salary_text'] || 'Salary based delivery men work according to their contract/assigned hours.';
        } else {
            // Freelancer
            if (hasFullDay) {
                infoText = translations['full_day_text'] || 'You will receive delivery orders 24/7.';
            } else if (selectedShifts.length > 0) {
                const shiftNames = selectedShifts.map(s => s.name + ' (' + s.timeRange + ')').join(', ');
                const text1 = translations['specific_shift_text_1'] || 'You will only receive delivery orders during the';
                const text2 = translations['specific_shift_text_2'] || 'shifts. Orders outside these time slots will not be received.';
                infoText = `${text1} <strong>${shiftNames}</strong> ${text2}`;
            } else {
                infoText = translations['no_shift_text'] || 'Please select a shift to see availability.';
            }
        }

        shiftInfoText.innerHTML = infoText;
    }

    // =====================================================
    // EVENT: Handle earning type change (salary vs freelancer)
    // =====================================================
    if (earningSelect) {
        earningSelect.addEventListener('change', function () {
            const type = this.value;

            // Show/hide shift view based on type
            if (shiftView) {
                shiftView.style.display = type == '1' ? 'block' : 'none';
            }

            // Update info text
            updateShiftInfo();

            // Reset shifts if switching to salary based
            if (type == '0') {
                checkboxes.forEach(cb => {
                    if (!cb.classList.contains('full-day-checkbox')) {
                        cb.checked = false;
                    }
                });
                updateTagsDisplay();
            }
        });
    }

    // =====================================================
    // INITIALIZE: Set initial state
    // =====================================================

    // Disable slots if full day is checked by default
    const fullDayCheckbox = container.querySelector('.full-day-checkbox');
    if (fullDayCheckbox && fullDayCheckbox.checked) {
        checkboxes.forEach(cb => {
            if (cb.classList.contains('slot-checkbox')) {
                cb.disabled = true;
                // Add disabled class to parent label for styling
                const label = cb.closest('.option-item');
                if (label) {
                    label.classList.add('disabled');
                }
            }
        });
    }

    // **ADDED: Check initial state - if any slots are checked, disable Full Day**
    let hasAnySlotsChecked = false;
    checkboxes.forEach(cb => {
        if (cb.classList.contains('slot-checkbox') && cb.checked) {
            hasAnySlotsChecked = true;
        }
    });
    if (hasAnySlotsChecked && fullDayCheckbox) {
        fullDayCheckbox.disabled = true;
        const fullDayLabel = fullDayCheckbox.closest('.option-item');
        if (fullDayLabel) {
            fullDayLabel.classList.add('disabled');
        }
    }

    // Initial visibility based on earning type
    if (shiftView && earningSelect) {
        shiftView.style.display = earningSelect.value == '1' ? 'block' : 'none';
    }

    // Initial tags and info
    updateTagsDisplay();
    updateShiftInfo();
}