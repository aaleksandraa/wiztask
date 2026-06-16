import flatpickr from 'flatpickr';
import { Bosnian } from 'flatpickr/dist/l10n/bs.js';

let datePickerRegistered = false;

export function registerDatePicker() {
    if (datePickerRegistered) {
        return;
    }

    datePickerRegistered = true;

    Alpine.data('datePicker', (model) => ({
        picker: null,

        init() {
            this.picker = flatpickr(this.$refs.input, {
                locale: Bosnian,
                dateFormat: 'd.m.Y',
                allowInput: true,
                disableMobile: true,
                appendTo: document.body,
                monthSelectorType: 'dropdown',
                prevArrow: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>',
                nextArrow: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>',
                defaultDate: model || null,
                onChange: (_dates, dateStr) => {
                    model = dateStr;
                },
            });

            this.$watch(() => model, (value) => {
                if (!this.picker) {
                    return;
                }

                const current = this.picker.input.value;

                if ((value || '') === (current || '')) {
                    return;
                }

                if (value) {
                    this.picker.setDate(value, false, 'd.m.Y');
                } else {
                    this.picker.clear(false);
                }
            });
        },

        destroy() {
            this.picker?.destroy();
        },
    }));
}

document.addEventListener('livewire:init', registerDatePicker);
