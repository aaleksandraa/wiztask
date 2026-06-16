import { X } from 'lucide-react';
import { useEffect, type ReactNode } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    open: boolean;
    onClose: () => void;
    title?: string;
    maxWidth?: string;
    children: ReactNode;
};

export default function Modal({ open, onClose, title, maxWidth = 'max-w-2xl', children }: Props) {
    useEffect(() => {
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
        };
        if (open) document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, [open, onClose]);

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 overflow-y-auto">
            <div className="fixed inset-0 animate-fade-in bg-black/70 backdrop-blur-sm" onClick={onClose} aria-hidden />
            <div className="flex min-h-screen items-start justify-center p-4 sm:items-center sm:p-6">
                <div
                    className={cn('relative w-full animate-scale-in overflow-hidden rounded-2xl border border-white/10 bg-neutral-900 shadow-float', maxWidth)}
                    role="dialog"
                >
                    {title && (
                        <div className="flex items-center justify-between border-b border-white/[0.06] bg-white/[0.02] px-6 py-4">
                            <h3 className="text-lg font-bold tracking-tight text-white">{title}</h3>
                            <button
                                type="button"
                                onClick={onClose}
                                className="rounded-xl p-2 text-neutral-500 transition hover:bg-white/5 hover:text-neutral-200"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>
                    )}
                    <div className="px-6 py-5">{children}</div>
                </div>
            </div>
        </div>
    );
}
