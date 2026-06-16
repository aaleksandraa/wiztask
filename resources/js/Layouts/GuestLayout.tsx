import type { ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import type { SharedProps } from '@/types';

type Props = {
    children: ReactNode;
};

export default function GuestLayout({ children }: Props) {
    const { appName } = usePage<SharedProps>().props;

    return (
        <div className="relative flex min-h-screen flex-col items-center justify-center px-4 py-12">
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_70%_50%_at_50%_-10%,rgba(79,110,247,0.18),transparent)]" />
            <div className="relative w-full max-w-md animate-slide-up">
                <div className="mb-8 text-center">
                    <div className="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-xl font-bold text-white shadow-float">
                        W
                    </div>
                    <h1 className="text-3xl font-bold tracking-tight text-white">{appName}</h1>
                    <p className="mt-2 text-sm text-neutral-400">Interni task manager za tim</p>
                </div>
                <div className="surface p-8 shadow-float">{children}</div>
                <p className="mt-6 text-center text-xs text-neutral-600">Sigurna prijava · enkriptovana sesija</p>
            </div>
        </div>
    );
}
