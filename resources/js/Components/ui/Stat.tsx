import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

type Color = 'neutral' | 'indigo' | 'green' | 'amber' | 'red' | 'blue' | 'purple';

type Props = {
    label: string;
    value: ReactNode;
    sub?: string;
    color?: Color;
};

const accents: Record<Color, string> = {
    neutral: 'stat-accent-neutral',
    indigo: 'stat-accent-blue',
    green: 'stat-accent-green',
    amber: 'stat-accent-amber',
    red: 'stat-accent-red',
    blue: 'stat-accent-blue',
    purple: 'stat-accent-purple',
};

const values: Record<Color, string> = {
    neutral: 'text-neutral-100',
    indigo: 'text-brand-400',
    green: 'text-emerald-400',
    amber: 'text-amber-400',
    red: 'text-red-400',
    blue: 'text-brand-400',
    purple: 'text-purple-400',
};

export default function Stat({ label, value, sub, color = 'neutral' }: Props) {
    return (
        <div className={cn('surface surface-hover p-5 pl-4', accents[color])}>
            <p className="text-[11px] font-semibold uppercase tracking-[0.08em] text-neutral-500">{label}</p>
            <p className={cn('mt-2 text-2xl font-bold tracking-tight', values[color])}>{value}</p>
            {sub && <p className="mt-1 text-xs text-neutral-500">{sub}</p>}
        </div>
    );
}
