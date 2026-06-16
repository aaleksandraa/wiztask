type Props = {
    show: boolean;
};

export default function ListCacheBanner({ show }: Props) {
    if (!show) return null;

    return (
        <div className="mb-3 flex items-center gap-2 rounded-xl border border-brand-500/20 bg-brand-500/10 px-3 py-2 text-xs text-brand-300 shadow-sm">
            <span className="inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-brand-400" />
            Prikaz iz cache-a — osvježavam podatke u pozadini...
        </div>
    );
}
