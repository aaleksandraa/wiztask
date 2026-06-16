import { router } from '@inertiajs/react';
import type { MouseEvent, ReactNode } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    href: string;
    children: ReactNode;
    className?: string;
};

export default function ClickableTableRow({ href, children, className }: Props) {
    const handleClick = (e: MouseEvent<HTMLTableRowElement>) => {
        const target = e.target as HTMLElement;
        if (target.closest('a, button, input, select, textarea, [data-row-ignore]')) {
            return;
        }
        router.visit(href);
    };

    return (
        <tr
            onClick={handleClick}
            className={cn(
                'cursor-pointer transition hover:bg-white/[0.03]',
                className,
            )}
        >
            {children}
        </tr>
    );
}
