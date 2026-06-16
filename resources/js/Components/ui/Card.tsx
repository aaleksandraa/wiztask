import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

type Props = {
    title?: string;
    action?: ReactNode;
    padding?: string;
    className?: string;
    children: ReactNode;
};

export default function Card({ title, action, padding = 'p-5', className, children }: Props) {
    return (
        <div className={cn('surface surface-hover overflow-hidden', className)}>
            {title && (
                <div className="flex items-center justify-between gap-3 border-b border-white/[0.06] bg-white/[0.02] px-5 py-3.5">
                    <h3 className="font-semibold tracking-tight text-neutral-100">{title}</h3>
                    {action}
                </div>
            )}
            <div className={padding}>{children}</div>
        </div>
    );
}
