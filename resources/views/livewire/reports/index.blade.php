<div>
    <x-page-header title="Izvještaji" subtitle="Detaljan izvještaj rada i naplate po klijentu i periodu." />

    <x-ui.card class="mb-4">
        <form wire:submit="generate" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <x-ui.label>Klijent *</x-ui.label>
                    <x-ui.select wire:model.live="client_id" :options="$clients->toArray()" placeholder="Odaberi klijenta" />
                    <x-ui.error name="client_id" />
                </div>
                <div>
                    <x-ui.label>Projekat (opcionalno)</x-ui.label>
                    <x-ui.select wire:model="project_id" :options="$projects->toArray()" placeholder="Svi projekti" />
                </div>
                <div>
                    <x-ui.label>Period od</x-ui.label>
                    <x-ui.date-input wire:model="date_from" />
                </div>
                <div>
                    <x-ui.label>Period do</x-ui.label>
                    <x-ui.date-input wire:model="date_to" />
                </div>
                <div>
                    <x-ui.label>Status taska (opcionalno)</x-ui.label>
                    <x-ui.select wire:model="status" :options="$taskStatuses" placeholder="Svi statusi" />
                </div>
                <div class="flex items-end gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="only_billable" class="rounded border-slate-300 text-neutral-900 focus:ring-neutral-900" /> Samo naplativo
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="only_unpaid" class="rounded border-slate-300 text-neutral-900 focus:ring-neutral-900" /> Samo neplaćeno
                    </label>
                </div>
            </div>
            <div class="flex justify-end">
                <x-ui.button type="submit">Generiši izvještaj</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($report && $report['client'])
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold">{{ $report['client']->name }}</h2>
                <p class="text-sm text-slate-500">
                    Period:
                    {{ $report['filters']['date_from'] ? \App\Support\Dates::formatOr($report['filters']['date_from']) : 'početak' }}
                    –
                    {{ $report['filters']['date_to'] ? \App\Support\Dates::formatOr($report['filters']['date_to']) : 'danas' }}
                </p>
            </div>
            <div class="flex gap-2">
                <x-ui.button variant="secondary" :href="route('reports.print', $exportParams)" target="_blank">Print</x-ui.button>
                <x-ui.button variant="secondary" :href="route('reports.export.pdf', $exportParams)">PDF</x-ui.button>
                <x-ui.button variant="secondary" :href="route('reports.export.excel', $exportParams)">Excel</x-ui.button>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4 lg:grid-cols-5">
            <x-ui.stat label="Taskova" :value="$report['totals']['count']" color="indigo" />
            <x-ui.stat label="Sati" :value="\App\Support\Money::minutesToHuman($report['totals']['minutes'])" color="amber" />
            <x-ui.stat label="Za naplatu" :value="\App\Support\Money::format($report['totals']['billable'], $report['client']->currency)" color="blue" />
            <x-ui.stat label="Plaćeno" :value="\App\Support\Money::format($report['totals']['paid'], $report['client']->currency)" color="green" />
            <x-ui.stat label="Neplaćeno" :value="\App\Support\Money::format($report['totals']['unpaid'], $report['client']->currency)" color="red" />
        </div>

        <x-ui.card padding="p-0">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr>
                        <th class="px-4 py-3">Datum</th><th class="px-4 py-3">Projekat</th><th class="px-4 py-3">Task</th>
                        <th class="px-4 py-3">Status</th><th class="px-4 py-3">Vrijeme</th>
                        <th class="px-4 py-3 text-right">Cijena</th><th class="px-4 py-3">Plaćanje</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse ($report['tasks'] as $t)
                            <tr>
                                <td class="px-4 py-3"><x-ui.date :value="$t->task_date" /></td>
                                <td class="px-4 py-3">{{ $t->project->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $t->title }}</div>
                                    @if ($t->description)<div class="text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($t->description, 80) }}</div>@endif
                                </td>
                                <td class="px-4 py-3"><x-ui.badge :value="$t->status" :map="$taskStatuses" /></td>
                                <td class="px-4 py-3">{{ \App\Support\Money::minutesToHuman($t->totalMinutes()) }}</td>
                                <td class="px-4 py-3 text-right">{{ \App\Support\Money::format($t->total_price, $report['client']->currency) }}</td>
                                <td class="px-4 py-3"><x-ui.badge :value="$t->payment_status" :map="$paymentStatuses" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-ui.empty text="Nema taskova za zadane filtere." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @elseif ($generated)
        <x-ui.empty text="Odaberi klijenta da bi generisao izvještaj." />
    @endif
</div>
