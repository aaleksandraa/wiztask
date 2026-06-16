import { Calendar } from 'lucide-react';
import { useFlatpickr } from '@/hooks/useFlatpickr';
import { cn } from '@/lib/utils';
import type { ChangeEvent, InputHTMLAttributes } from 'react';

type Props = Omit<InputHTMLAttributes<HTMLInputElement>, 'type' | 'value' | 'onChange'> & {
    label?: string;
    error?: string;
    value?: string;
    onChange?: (e: ChangeEvent<HTMLInputElement>) => void;
    minDate?: string | Date;
    maxDate?: string | Date;
};

export default function DateInput({
    label,
    error,
    className,
    id,
    value = '',
    onChange,
    minDate,
    maxDate,
    disabled,
    required,
    ...props
}: Props) {
    const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '-');

    const emitChange = (dateStr: string) => {
        onChange?.({ target: { value: dateStr } } as ChangeEvent<HTMLInputElement>);
    };

    const { inputRef, instanceRef } = useFlatpickr(value, emitChange, {
        minDate,
        maxDate,
        disable: disabled ? [() => true] : [],
    });

    return (
        <div>
            {label && (
                <label htmlFor={inputId} className="mb-1.5 block text-sm font-medium text-neutral-300">
                    {label}
                </label>
            )}
            <div className="relative">
                <input
                    ref={inputRef}
                    id={inputId}
                    type="text"
                    inputMode="numeric"
                    autoComplete="off"
                    placeholder="31.01.2026"
                    disabled={disabled}
                    required={required}
                    className={cn(
                        'date-input field-base pr-10',
                        error && 'border-red-300 focus:border-red-500 focus:ring-red-500/10',
                        disabled && 'cursor-not-allowed opacity-60',
                        className,
                    )}
                    {...props}
                />
                <button
                    type="button"
                    tabIndex={-1}
                    disabled={disabled}
                    onClick={() => instanceRef.current?.open()}
                    className="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-neutral-400 transition hover:bg-white/5 hover:text-neutral-200 disabled:pointer-events-none disabled:opacity-50"
                    aria-label="Otvori kalendar"
                >
                    <Calendar className="h-4 w-4" strokeWidth={1.75} />
                </button>
            </div>
            {error && <p className="mt-1 text-xs text-red-400">{error}</p>}
        </div>
    );
}
