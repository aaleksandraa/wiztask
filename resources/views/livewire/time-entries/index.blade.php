<div>
    <x-page-header title="Vrijeme rada" subtitle="Svi unosi vremena kroz sve taskove.">
        <x-slot:actions>
            <x-ui.button wire:click="create">+ Novi unos</x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <x-ui.card class="mb-4" padding="p-4">
        <div class="grid gap-3 sm:grid-cols-4">
            <div>
                <x-ui.label>Klijent</x-ui.label>
                <x-ui.select wire:model.live="client_id" :options="$clients->toArray()" placeholder="Svi klijenti" />
            </div>
            <div>
                <x-ui.label>Datum od</x-ui.label>
                <x-ui.date-input wire:model.live="date_from" />
            </div>
            <div>
                <x-ui.label>Datum do</x-ui.label>
                <x-ui.date-input wire:model.live="date_to" />
            </div>
            <div>
                <x-ui.label>Naplativo</x-ui.label>
                <x-ui.select wire:model.live="is_billable" :options="['1' => 'Da', '0' => 'Ne']" placeholder="Sve" />
            </div>
        </div>
    </x-ui.card>

    <div class="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-2">
        <x-ui.stat label="Ukupno vrijeme (filtrirano)" :value="\App\Support\Money::minutesToHuman($sumMinutes)" color="blue" />
        <x-ui.stat label="Ukupan iznos (filtrirano)" :value="\App\Support\Money::format($sumPrice)" color="green" />
    </div>

    <x-ui.card padding="p-0">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr>
                    <th class="px-4 py-3">Datum</th><th class="px-4 py-3">Klijent</th><th class="px-4 py-3">Task</th>
                    <th class="px-4 py-3">Opis</th><th class="px-4 py-3">Vrijeme</th><th class="px-4 py-3 text-right">Iznos</th><th class="px-4 py-3 text-right">Akcije</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($entries as $te)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-3"><x-ui.date :value="$te->work_date" /></td>
                            <td class="px-4 py-3">{{ $te->client->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($te->task)
                                    <a href="{{ route('tasks.show', $te->task) }}" wire:navigate class="text-neutral-900 hover:underline dark:text-white">{{ $te->task->title }}</a>
                                @else - @endif
                            </td>
                            <td class="px-4 py-3">{{ $te->description ?: '-' }}</td>
                            <td class="px-4 py-3">{{ \App\Support\Money::minutesToHuman($te->total_minutes) }}</td>
                            <td class="px-4 py-3 text-right">{{ \App\Support\Money::format($te->total_price, $te->client->currency ?? null) }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-ui.button variant="ghost" size="sm" wire:click="edit({{ $te->id }})">✎</x-ui.button>
                                <x-ui.button variant="ghost" size="sm" wire:click="delete({{ $te->id }})" wire:confirm="Obrisati unos?">🗑</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-ui.empty text="Nema unosa vremena." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">{{ $entries->links() }}</div>

    <x-ui.modal wire:model="showModal" :title="$editingId ? 'Izmjena vremena' : 'Novi unos vremena'">
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-ui.label>Klijent *</x-ui.label>
                <x-ui.select wire:model.live="form.client_id" :options="$clients->toArray()" placeholder="Odaberi klijenta" />
                <x-ui.error name="form.client_id" />
            </div>
            <div>
                <x-ui.label>Task *</x-ui.label>
                <x-ui.select wire:model="form.task_id" :options="$tasksForForm->toArray()" placeholder="Odaberi task" />
                <x-ui.error name="form.task_id" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label>Datum rada *</x-ui.label>
                    <x-ui.date-input wire:model="form.work_date" />
                    <x-ui.error name="form.work_date" />
                </div>
                <div>
                    <x-ui.label>Satnica</x-ui.label>
                    <x-ui.input type="number" step="0.01" wire:model="form.hourly_rate" />
                </div>
                <div>
                    <x-ui.label>Sati</x-ui.label>
                    <x-ui.input type="number" min="0" wire:model="form.hours" />
                </div>
                <div>
                    <x-ui.label>Minute</x-ui.label>
                    <x-ui.input type="number" min="0" max="59" wire:model="form.minutes" />
                </div>
            </div>
            <div>
                <x-ui.label>Opis rada</x-ui.label>
                <x-ui.textarea wire:model="form.description" rows="2" />
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="form.is_billable" id="time_billable" class="rounded border-slate-300 text-neutral-900 focus:ring-neutral-900" />
                <label for="time_billable" class="text-sm text-slate-700 dark:text-slate-300">Naplativo</label>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" x-on:click="show = false">Otkaži</x-ui.button>
                <x-ui.button type="submit">Sačuvaj</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
