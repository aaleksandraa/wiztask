import { badgeClass, cn, label } from '@/lib/utils';

type Props = {
    value?: string | null;
    map?: Record<string, string>;
    className?: string;
};

export default function Badge({ value, map = {}, className }: Props) {
    const text = label(map, value, value ?? '-');
    const colors = value ? badgeClass(value) : 'bg-neutral-700 text-neutral-200';

    return (
        <span
            className={cn(
                'inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold tracking-wide shadow-sm',
                colors,
                className,
            )}
        >
            {text}
        </span>
    );
}
