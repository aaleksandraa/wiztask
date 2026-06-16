import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function label(map: Record<string, string>, key?: string | null, fallback = '-'): string {
    if (!key) return fallback;
    return map[key] ?? key;
}

export function badgeClass(key: string): string {
    const map: Record<string, string> = {
        // U toku / aktivno
        u_toku: 'bg-blue-700 text-white shadow-sm',
        aktivno: 'bg-blue-700 text-white shadow-sm',
        planirano: 'bg-slate-600 text-white shadow-sm',
        // Završeno / plaćeno
        zavrseno: 'bg-emerald-700 text-white shadow-sm',
        placeno: 'bg-emerald-700 text-white shadow-sm',
        aktivan: 'bg-emerald-700 text-white shadow-sm',
        // Čekanje
        // Novo — istaknuto crveno
        novo: 'bg-red-600 text-white shadow-sm ring-2 ring-red-400/40',
        // Čekanje klijenta — neutralno sivo
        ceka_klijenta: 'bg-neutral-500 text-white shadow-sm',
        ceka_materijal: 'bg-amber-600 text-white shadow-sm',
        na_provjeri: 'bg-amber-600 text-white shadow-sm',
        pauziran: 'bg-amber-600 text-white shadow-sm',
        pauzirano: 'bg-amber-600 text-white shadow-sm',
        djelimicno_placeno: 'bg-amber-600 text-white shadow-sm',
        // Naplata
        za_naplatu: 'bg-purple-700 text-white shadow-sm',
        fakturisano: 'bg-indigo-700 text-white shadow-sm',
        // Negativno
        otkazano: 'bg-red-700 text-white shadow-sm',
        neaktivan: 'bg-red-700 text-white shadow-sm',
        arhivirano: 'bg-red-700 text-white shadow-sm',
        kriticno: 'bg-red-700 text-white shadow-sm',
        hitno: 'bg-orange-600 text-white shadow-sm',
        nije_za_naplatu: 'bg-neutral-500 text-white shadow-sm',
        normalan: 'bg-neutral-500 text-white shadow-sm',
        nizak: 'bg-neutral-400 text-white shadow-sm',
    };
    return map[key] ?? 'bg-neutral-600 text-white shadow-sm';
}

export function optionsToSelect(opts: Record<string, string>) {
    return Object.entries(opts).map(([value, label]) => ({ value, label }));
}

export function recordToSelect(record: Record<string | number, string>) {
    return Object.entries(record).map(([value, label]) => ({ value: String(value), label }));
}

export function formatMoney(amount: number, currency = 'KM'): string {
    return `${amount.toFixed(2)} ${currency}`;
}

export function minutesToHuman(minutes: number): string {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    if (h && m) return `${h}h ${m}min`;
    if (h) return `${h}h`;
    return `${m}min`;
}

export function buildQuery(params: Record<string, string | number | boolean | undefined | null>): string {
    const search = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
        if (value === undefined || value === null || value === '') return;
        search.set(key, value === true ? '1' : value === false ? '0' : String(value));
    });
    const q = search.toString();
    return q ? `?${q}` : '';
}
