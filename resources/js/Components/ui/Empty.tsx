import { Inbox } from 'lucide-react';
import type { ReactNode } from 'react';

type Props = {
    text?: string;
    children?: ReactNode;
};

export default function Empty({ text = 'Nema podataka.', children }: Props) {
    return (
        <div className="flex flex-col items-center justify-center gap-3 px-6 py-14 text-center">
            <div className="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.03] text-neutral-500">
                <Inbox className="h-6 w-6" strokeWidth={1.5} />
            </div>
            <p className="max-w-xs text-sm font-medium text-neutral-400">{text}</p>
            {children}
        </div>
    );
}
