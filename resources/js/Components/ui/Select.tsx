import { cn } from '@/lib/utils';
import type { SelectHTMLAttributes } from 'react';

export type SelectOption = { value: string; label: string };

type Props = SelectHTMLAttributes<HTMLSelectElement> & {
    label?: string;
    error?: string;
    options?: SelectOption[];
    placeholder?: string;
};

export default function Select({
    label,
    error,
    options = [],
    placeholder,
    className,
    id,
    children,
    ...props
}: Props) {
    const selectId = id ?? label?.toLowerCase().replace(/\s+/g, '-');

    return (
        <div>
            {label && (
                <label htmlFor={selectId} className="mb-1.5 block text-sm font-medium text-neutral-300">
                    {label}
                </label>
            )}
            <select
                id={selectId}
                className={cn('field-base', error && 'border-red-300 focus:border-red-500 focus:ring-red-500/10', className)}
                {...props}
            >
                {placeholder !== undefined && <option value="">{placeholder}</option>}
                {options.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                        {opt.label}
                    </option>
                ))}
                {children}
            </select>
            {error && <p className="mt-1.5 text-xs font-medium text-red-400">{error}</p>}
        </div>
    );
}
