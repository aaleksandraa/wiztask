import { Link } from '@inertiajs/react';

type LinkItem = { url: string | null; label: string; active: boolean };

type Props = {
    links: LinkItem[];
    meta: { current_page: number; last_page: number; total: number };
};

export default function Pagination({ links, meta }: Props) {
    if (meta.last_page <= 1) return null;

    return (
        <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
            <p className="text-sm text-neutral-500">
                Stranica {meta.current_page} od {meta.last_page} · ukupno {meta.total}
            </p>
            <div className="flex flex-wrap gap-1">
                {links.map((link, i) => {
                    const label = link.label.replace(/&laquo;|&raquo;/g, (m) => (m.includes('laquo') ? '«' : '»'));
                    if (!link.url) {
                        return (
                            <span
                                key={i}
                                className="rounded-lg px-3 py-1.5 text-sm text-neutral-400"
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        );
                    }
                    return (
                        <Link
                            key={i}
                            href={link.url}
                            preserveState
                            prefetch="hover"
                            className={`rounded-lg px-3 py-1.5 text-sm transition ${
                                link.active
                                    ? 'bg-white text-neutral-900'
                                    : 'text-neutral-400 hover:bg-white/5 hover:text-neutral-200'
                            }`}
                        >
                            {label}
                        </Link>
                    );
                })}
            </div>
        </div>
    );
}
