import flatpickr, { type Options } from 'flatpickr';
import { Bosnian } from 'flatpickr/dist/l10n/bs.js';
import { useEffect, useRef } from 'react';

export const DATE_DISPLAY_FORMAT = 'd.m.Y';

const prevArrow =
    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>';

const nextArrow =
    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>';

export function getFlatpickrDefaults(overrides?: Partial<Options>): Options {
    return {
        locale: Bosnian,
        dateFormat: DATE_DISPLAY_FORMAT,
        allowInput: true,
        disableMobile: true,
        appendTo: document.body,
        monthSelectorType: 'dropdown',
        prevArrow,
        nextArrow,
        ...overrides,
    };
}

export function useFlatpickr(
    value: string,
    onValueChange: (value: string) => void,
    options?: Partial<Options>,
) {
    const inputRef = useRef<HTMLInputElement>(null);
    const instanceRef = useRef<flatpickr.Instance | null>(null);
    const onValueChangeRef = useRef(onValueChange);
    onValueChangeRef.current = onValueChange;

    useEffect(() => {
        const input = inputRef.current;
        if (!input) return;

        instanceRef.current = flatpickr(
            input,
            getFlatpickrDefaults({
                defaultDate: value || undefined,
                onChange: (_dates, dateStr) => {
                    onValueChangeRef.current(dateStr);
                },
                onClose: (_dates, dateStr) => {
                    onValueChangeRef.current(dateStr);
                },
                ...options,
            }),
        );

        return () => {
            instanceRef.current?.destroy();
            instanceRef.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps -- init once; value synced separately
    }, []);

    useEffect(() => {
        const fp = instanceRef.current;
        if (!fp) return;

        const current = fp.input.value;
        if ((value || '') === (current || '')) return;

        if (value) {
            fp.setDate(value, false, DATE_DISPLAY_FORMAT);
        } else {
            fp.clear(false);
        }
    }, [value]);

    useEffect(() => {
        const fp = instanceRef.current;
        if (!fp) return;

        if (options?.minDate !== undefined) fp.set('minDate', options.minDate);
        if (options?.maxDate !== undefined) fp.set('maxDate', options.maxDate);
        if (options?.disable !== undefined) fp.set('disable', options.disable);
    }, [options?.minDate, options?.maxDate, options?.disable]);

    return { inputRef, instanceRef };
}
