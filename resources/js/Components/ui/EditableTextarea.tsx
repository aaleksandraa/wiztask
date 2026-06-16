import { cn } from '@/lib/utils';
import type { TextareaHTMLAttributes } from 'react';

type Props = Omit<TextareaHTMLAttributes<HTMLTextAreaElement>, 'value' | 'onChange'> & {
    label?: string;
    value: string;
    onChange: (value: string) => void;
    onBlurSave?: () => void;
    error?: string;
    hint?: string;
};

export default function EditableTextarea({
    label,
    value,
    onChange,
    onBlurSave,
    error,
    hint,
    className,
    rows = 5,
    placeholder = 'Unesite tekst...',
    ...props
}: Props) {
    return (
        <div>
            {label && <label className="mb-1.5 block text-sm font-medium text-neutral-300">{label}</label>}
            <textarea
                value={value}
                onChange={(e) => onChange(e.target.value)}
                onBlur={() => onBlurSave?.()}
                rows={rows}
                placeholder={placeholder}
                className={cn(
                    'field-base resize-y leading-relaxed',
                    error && 'border-red-300 focus:border-red-500 focus:ring-red-500/10',
                    className,
                )}
                {...props}
            />
            {hint && !error && <p className="mt-1.5 text-xs text-neutral-400">{hint}</p>}
            {error && <p className="mt-1.5 text-xs font-medium text-red-400">{error}</p>}
        </div>
    );
}
