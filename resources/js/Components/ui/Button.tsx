import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { ButtonHTMLAttributes, ReactNode } from 'react';

type Variant = 'primary' | 'secondary' | 'danger' | 'ghost';
type Size = 'sm' | 'md';

type Props = ButtonHTMLAttributes<HTMLButtonElement> & {
    variant?: Variant;
    size?: Size;
    href?: string;
    children: ReactNode;
};

const variants: Record<Variant, string> = {
    primary:
        'bg-white text-neutral-900 shadow-soft hover:bg-neutral-100 active:scale-[0.98] focus:ring-white/20',
    secondary:
        'border border-white/10 bg-neutral-900/80 text-neutral-200 shadow-sm hover:border-white/15 hover:bg-neutral-800 active:scale-[0.98] focus:ring-brand-500/15',
    danger: 'bg-red-600 text-white shadow-sm hover:bg-red-500 active:scale-[0.98] focus:ring-red-500/20',
    ghost: 'text-neutral-400 hover:bg-white/5 hover:text-neutral-100',
};

const sizes: Record<Size, string> = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-4 py-2.5 text-sm',
};

export default function Button({
    variant = 'primary',
    size = 'md',
    href,
    className,
    children,
    type = 'button',
    ...props
}: Props) {
    const classes = cn(
        'inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition-all duration-150 focus:outline-none focus:ring-4 disabled:pointer-events-none disabled:opacity-50',
        sizes[size],
        variants[variant],
        className,
    );

    if (href) {
        return (
            <Link href={href} className={classes}>
                {children}
            </Link>
        );
    }

    return (
        <button type={type} className={classes} {...props}>
            {children}
        </button>
    );
}
