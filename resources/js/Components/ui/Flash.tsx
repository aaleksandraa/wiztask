import { usePage } from '@inertiajs/react';
import { CheckCircle2, X, XCircle } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { SharedProps } from '@/types';

export default function Flash() {
    const { flash } = usePage<SharedProps>().props;
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        setVisible(true);
        if (flash.success || flash.error) {
            const t = setTimeout(() => setVisible(false), 5000);
            return () => clearTimeout(t);
        }
    }, [flash.success, flash.error]);

    if (!visible) return null;

    return (
        <>
            {flash.success && (
                <div className="mb-4 flex animate-slide-up items-start gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3.5 text-sm text-emerald-300 shadow-soft">
                    <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" />
                    <span className="flex-1 font-medium">{flash.success}</span>
                    <button type="button" onClick={() => setVisible(false)} className="rounded-lg p-1 text-emerald-400 hover:bg-emerald-500/10">
                        <X className="h-4 w-4" />
                    </button>
                </div>
            )}
            {flash.error && (
                <div className="mb-4 flex animate-slide-up items-start gap-3 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3.5 text-sm text-red-300 shadow-soft">
                    <XCircle className="mt-0.5 h-4 w-4 shrink-0 text-red-400" />
                    <span className="flex-1 font-medium">{flash.error}</span>
                    <button type="button" onClick={() => setVisible(false)} className="rounded-lg p-1 text-red-400 hover:bg-red-500/10">
                        <X className="h-4 w-4" />
                    </button>
                </div>
            )}
        </>
    );
}
