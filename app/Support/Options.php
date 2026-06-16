<?php

namespace App\Support;

class Options
{
    public const CURRENCIES = ['KM', 'EUR', 'CHF'];

    public const CLIENT_STATUSES = [
        'aktivan' => 'Aktivan',
        'neaktivan' => 'Neaktivan',
        'pauziran' => 'Pauziran',
    ];

    public const PROJECT_STATUSES = [
        'planirano' => 'Planirano',
        'aktivno' => 'Aktivno',
        'pauzirano' => 'Pauzirano',
        'zavrseno' => 'Završeno',
        'arhivirano' => 'Arhivirano',
    ];

    public const PROJECT_BILLING_TYPES = [
        'po_satu' => 'Po satu',
        'fiksno' => 'Fiksno',
        'mjesecno' => 'Mjesečno',
        'bez_naplate' => 'Bez naplate',
    ];

    public const TASK_STATUSES = [
        'novo' => 'Novo',
        'planirano' => 'Planirano',
        'u_toku' => 'U toku',
        'ceka_klijenta' => 'Čeka klijenta',
        'ceka_materijal' => 'Čeka materijal',
        'na_provjeri' => 'Na provjeri',
        'zavrseno' => 'Završeno',
        'za_naplatu' => 'Za naplatu',
        'placeno' => 'Plaćeno',
        'otkazano' => 'Otkazano',
    ];

    public const TASK_PRIORITIES = [
        'nizak' => 'Nizak',
        'normalan' => 'Normalan',
        'hitno' => 'Hitno',
        'kriticno' => 'Kritično',
    ];

    public const TASK_BILLING_TYPES = [
        'po_satu' => 'Po satu',
        'fiksno' => 'Fiksno',
        'ukljuceno_u_paket' => 'Uključeno u paket',
        'bez_naplate' => 'Bez naplate',
    ];

    public const PAYMENT_STATUSES = [
        'nije_za_naplatu' => 'Nije za naplatu',
        'za_naplatu' => 'Za naplatu',
        'fakturisano' => 'Fakturisano',
        'placeno' => 'Plaćeno',
        'djelimicno_placeno' => 'Djelimično plaćeno',
    ];

    public const ATTACHMENT_CATEGORIES = [
        'prije' => 'Prije',
        'poslije' => 'Poslije',
        'screenshot' => 'Screenshot',
        'dokaz' => 'Dokaz',
        'materijal' => 'Materijal',
        'dizajn' => 'Dizajn',
        'ostalo' => 'Ostalo',
    ];

    public static function label(array $map, ?string $key, string $fallback = '-'): string
    {
        if ($key === null) {
            return $fallback;
        }

        return $map[$key] ?? $key;
    }

    /**
     * Tailwind badge classes per status-like key.
     */
    public static function badge(string $key): string
    {
        return match ($key) {
            'u_toku', 'aktivno' => 'bg-blue-700 text-white shadow-sm',
            'planirano' => 'bg-slate-600 text-white shadow-sm',
            'zavrseno', 'placeno', 'aktivan' => 'bg-emerald-700 text-white shadow-sm',
            'novo' => 'bg-red-600 text-white shadow-sm ring-2 ring-red-400/40',
            'ceka_klijenta' => 'bg-neutral-500 text-white shadow-sm',
            'ceka_materijal', 'na_provjeri', 'pauziran', 'pauzirano', 'djelimicno_placeno' => 'bg-amber-600 text-white shadow-sm',
            'za_naplatu' => 'bg-purple-700 text-white shadow-sm',
            'fakturisano' => 'bg-indigo-700 text-white shadow-sm',
            'otkazano', 'neaktivan', 'arhivirano', 'kriticno' => 'bg-red-700 text-white shadow-sm',
            'hitno' => 'bg-orange-600 text-white shadow-sm',
            'nije_za_naplatu', 'normalan' => 'bg-neutral-600 text-white shadow-sm',
            'nizak' => 'bg-neutral-400 text-white shadow-sm',
            default => 'bg-neutral-600 text-white shadow-sm',
        };
    }
}
