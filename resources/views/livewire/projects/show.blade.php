<div>
    <x-page-header :title="$project->name" :subtitle="'Klijent: '.$project->client->name">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('projects.index')" wire:navigate>← Nazad</x-ui.button>
            <x-ui.button :href="route('clients.show', $project->client)" wire:navigate>Klijent</x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-ui.stat label="Status" :value="\App\Support\Options::label($projectStatuses, $project->status)" color="indigo" />
        <x-ui.stat label="Taskovi" :value="$tasks->count()" color="blue" />
        <x-ui.stat label="Ukupno sati" :value="\App\Support\Money::minutesToHuman($totalMinutes)" color="amber" />
        <x-ui.stat label="Ukupna vrijednost" :value="\App\Support\Money::format($totalValue, $project->currency)" color="green" />
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <x-ui.card title="Detalji projekta" class="lg:col-span-1">
            <dl class="grid grid-cols-2 gap-y-3 text-sm">
                <dt class="text-slate-500">Tip naplate</dt><dd>{{ \App\Support\Options::label($projectBilling, $project->billing_type) }}</dd>
                <dt class="text-slate-500">Fiksna cijena</dt><dd>{{ \App\Support\Money::format($project->fixed_price, $project->currency) }}</dd>
                <dt class="text-neutral-500">Početak</dt><dd><x-ui.date :value="$project->start_date" /></dd>
                <dt class="text-neutral-500">Rok</dt><dd><x-ui.date :value="$project->due_date" /></dd>
            </dl>
            @if ($project->description)
                <div class="mt-4">
                    <div class="text-sm font-medium text-slate-700 dark:text-slate-300">Opis</div>
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-500">{{ $project->description }}</p>
                </div>
            @endif
            @if ($project->note)
                <div class="mt-4">
                    <div class="text-sm font-medium text-slate-700 dark:text-slate-300">Napomena</div>
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-500">{{ $project->note }}</p>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card title="Taskovi projekta" padding="p-0" class="lg:col-span-2">
            <table class="data-table">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr>
                    <th class="px-4 py-3">Task</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Datum</th><th class="px-4 py-3 text-right">Cijena</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($tasks as $t)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-3"><a href="{{ route('tasks.show', $t) }}" wire:navigate class="font-medium text-neutral-900 hover:underline dark:text-white">{{ $t->title }}</a></td>
                            <td class="px-4 py-3"><x-ui.badge :value="$t->status" :map="$taskStatuses" /></td>
                            <td class="px-4 py-3"><x-ui.date :value="$t->task_date" /></td>
                            <td class="px-4 py-3 text-right">{{ \App\Support\Money::format($t->total_price, $project->currency) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-ui.empty text="Nema taskova na projektu." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.card>
    </div>

    <div class="mt-4">
        <livewire:attachments.manager :attachable-type="\App\Models\Project::class" :attachable-id="$project->id" title="Fajlovi projekta" :key="'proj-att-'.$project->id" />
    </div>
</div>
