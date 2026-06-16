import type { ReactNode } from 'react';

type Props = {
    title: string;
    subtitle?: string;
    actions?: ReactNode;
    children?: ReactNode;
};

export default function PageHeader({ title, subtitle, actions, children }: Props) {
    const headerActions = actions ?? children;

    return (
        <div className="mb-8 flex flex-col gap-4 border-b border-white/[0.06] pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 className="text-2xl font-bold tracking-tight text-white sm:text-3xl">{title}</h1>
                {subtitle && <p className="mt-1.5 text-sm font-medium text-neutral-400">{subtitle}</p>}
            </div>
            {headerActions && <div className="flex flex-wrap items-center gap-2">{headerActions}</div>}
        </div>
    );
}
