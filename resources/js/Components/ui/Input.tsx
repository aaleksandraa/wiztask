import { cn } from '@/lib/utils';
import type { InputHTMLAttributes } from 'react';

type Props = InputHTMLAttributes<HTMLInputElement> & {
    label?: string;
    error?: string;
};

export default function Input({ label, error, className, id, ...props }: Props) {
    const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '-');

    return (
        <div>
            {label && (
                <label htmlFor={inputId} className="mb-1.5 block text-sm font-medium text-neutral-300">
                    {label}
                </label>
            )}
            <input
                id={inputId}
                className={cn('field-base', error && 'border-red-300 focus:border-red-500 focus:ring-red-500/10', className)}
                {...props}
            />
            {error && <p className="mt-1.5 text-xs font-medium text-red-400">{error}</p>}
        </div>
    );
}
