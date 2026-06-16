<div>
    <x-page-header title="Taskovi" subtitle="Svi poslovi sa filterima, vremenom i naplatom.">
        <x-slot:actions>
            <x-ui.button wire:click="create">+ Novi task</x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <x-ui.card class="filter-panel mb-4" padding="p-0">
        <div class="grid gap-3 p-4 sm:grid-cols-3 sm:p-5 lg:grid-cols-4">
            <div>
                <x-ui.label>Pretraga</x-ui.label>
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Naslov ili opis" />
            </div>
            <div>
                <x-ui.label>Klijent</x-ui.label>
                <x-ui.select wire:model.live="client_id" :options="$clients->toArray()" placeholder="Svi klijenti" />
            </div>
            <div>
                <x-ui.label>Projekat</x-ui.label>
                <x-ui.select wire:model.live="project_id" :options="$projectsForFilter->toArray()" placeholder="Svi projekti" />
            </div>
            <div>
                <x-ui.label>Status</x-ui.label>
                <x-ui.select wire:model.live="status" :options="$statuses" placeholder="Svi statusi" />
            </div>
            <div>
                <x-ui.label>Prioritet</x-ui.label>
                <x-ui.select wire:model.live="priority" :options="$priorities" placeholder="Svi prioriteti" />
            </div>
            <div>
                <x-ui.label>Tip naplate</x-ui.label>
                <x-ui.select wire:model.live="billing_type" :options="$billingTypes" placeholder="Svi" />
            </div>
            <div>
                <x-ui.label>Status plaćanja</x-ui.label>
                <x-ui.select wire:model.live="payment_status" :options="$paymentStatuses" placeholder="Svi" />
            </div>
            <div>
                <x-ui.label>Naplativo</x-ui.label>
                <x-ui.select wire:model.live="is_billable" :options="['1' => 'Da', '0' => 'Ne']" placeholder="Sve" />
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
                <x-ui.label>Mjesec</x-ui.label>
                <x-ui.select wire:model.live="month" :options="$months" placeholder="Svi mjeseci" />
            </div>
            <div>
                <x-ui.label>Godina</x-ui.label>
                <x-ui.input type="number" wire:model.live.debounce.500ms="year" placeholder="npr. {{ now()->year }}" />
            </div>
        </div>
        <div class="flex items-center justify-between border-t border-neutral-200/80 px-4 py-3 sm:px-5 dark:border-neutral-800">
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <input type="checkbox" wire:model.live="showArchived" class="rounded border-slate-300 text-neutral-900 focus:ring-neutral-900" />
                Prikaži arhivirane
            </label>
            <x-ui.button variant="ghost" size="sm" wire:click="resetFilters">Poništi filtere</x-ui.button>
        </div>
    </x-ui.card>

    <x-ui.card padding="p-0">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr>
                    <th class="px-4 py-3">Task</th><th class="px-4 py-3">Klijent / Projekat</th><th class="px-4 py-3">Prioritet</th>
                    <th class="px-4 py-3">Status</th><th class="px-4 py-3">Datum</th><th class="px-4 py-3">Vrijeme</th>
                    <th class="px-4 py-3 text-right">Cijena</th><th class="px-4 py-3">Plaćanje</th><th class="px-4 py-3 text-right">Akcije</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($tasks as $t)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 {{ $t->archived_at ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3">
                                <a href="{{ route('tasks.show', $t) }}" wire:navigate class="font-medium text-neutral-900 hover:underline dark:text-white">{{ $t->title }}</a>
                                @if ($t->archived_at)<span class="ml-1 text-[10px] text-slate-400">(arhiva)</span>@endif
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $t->client->name ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $t->project->name ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3"><x-ui.badge :value="$t->priority" :map="$priorities" /></td>
                            <td class="px-4 py-3"><x-ui.badge :value="$t->status" :map="$statuses" /></td>
                            <td class="px-4 py-3"><x-ui.date :value="$t->task_date" /></td>
                            <td class="px-4 py-3">{{ \App\Support\Money::minutesToHuman($t->totalMinutes()) }}</td>
                            <td class="px-4 py-3 text-right">{{ \App\Support\Money::format($t->total_price, $t->client->currency ?? null) }}</td>
                            <td class="px-4 py-3"><x-ui.badge :value="$t->payment_status" :map="$paymentStatuses" /></td>
                            <td class="px-4 py-3 text-right">
                                <div x-data="{ o: false }" class="relative inline-block text-left">
                                    <button @click="o=!o" class="rounded p-1 hover:bg-slate-100 dark:hover:bg-slate-700">⋯</button>
                                    <div x-show="o" @click.outside="o=false" x-transition style="display:none" class="absolute right-0 z-10 mt-1 w-40 rounded-lg border border-slate-200 bg-white py-1 text-left shadow-lg dark:border-slate-700 dark:bg-slate-800">
                                        <button wire:click="edit({{ $t->id }})" @click="o=false" class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-700">Izmijeni</button>
                                        <button wire:click="duplicate({{ $t->id }})" @click="o=false" class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-700">Dupliciraj</button>
                                        <button wire:click="toggleArchive({{ $t->id }})" @click="o=false" class="block w-full px-4 py-2 text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-700">{{ $t->archived_at ? 'Vrati iz arhive' : 'Arhiviraj' }}</button>
                                        <button wire:click="delete({{ $t->id }})" wire:confirm="Obrisati task?" @click="o=false" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-slate-100 dark:hover:bg-slate-700">Obriši</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><x-ui.empty text="Nema taskova za zadane filtere." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">{{ $tasks->links() }}</div>

    <x-ui.modal wire:model="showModal" :title="$editingId ? 'Izmjena taska' : 'Novi task'" maxWidth="max-w-4xl">
        <form wire:submit="save" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label>Klijent *</x-ui.label>
                    <x-ui.select wire:model.live="form.client_id" :options="$clients->toArray()" placeholder="Odaberi klijenta" />
                    <x-ui.error name="form.client_id" />
                </div>
                <div>
                    <x-ui.label>Projekat</x-ui.label>
                    <x-ui.select wire:model="form.project_id" :options="$projectsForForm->toArray()" placeholder="Bez projekta" />
                </div>
                <div class="sm:col-span-2">
                    <x-ui.label>Naslov *</x-ui.label>
                    <x-ui.input wire:model="form.title" />
                    <x-ui.error name="form.title" />
                </div>
                <div class="sm:col-span-2">
                    <x-ui.label>Opis</x-ui.label>
                    <x-ui.textarea wire:model="form.description" rows="2" />
                </div>
                <div>
                    <x-ui.label>Status</x-ui.label>
                    <x-ui.select wire:model="form.status" :options="$statuses" />
                </div>
                <div>
                    <x-ui.label>Prioritet</x-ui.label>
                    <x-ui.select wire:model="form.priority" :options="$priorities" />
                </div>
                <div>
                    <x-ui.label>Datum taska</x-ui.label>
                    <x-ui.date-input wire:model="form.task_date" />
                </div>
                <div>
                    <x-ui.label>Rok</x-ui.label>
                    <x-ui.date-input wire:model="form.due_date" />
                </div>
                <div>
                    <x-ui.label>Tip naplate</x-ui.label>
                    <x-ui.select wire:model="form.billing_type" :options="$billingTypes" />
                </div>
                <div>
                    <x-ui.label>Status plaćanja</x-ui.label>
                    <x-ui.select wire:model="form.payment_status" :options="$paymentStatuses" />
                </div>
                <div>
                    <x-ui.label>Satnica</x-ui.label>
                    <x-ui.input type="number" step="0.01" wire:model="form.hourly_rate" />
                    <x-ui.error name="form.hourly_rate" />
                </div>
                <div>
                    <x-ui.label>Fiksna cijena</x-ui.label>
                    <x-ui.input type="number" step="0.01" wire:model="form.fixed_price" />
                    <x-ui.error name="form.fixed_price" />
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" wire:model="form.is_billable" id="billable" class="rounded border-slate-300 text-neutral-900 focus:ring-neutral-900" />
                    <label for="billable" class="text-sm text-slate-700 dark:text-slate-300">Naplativo</label>
                </div>
            </div>
            <div>
                <x-ui.label>Interne napomene</x-ui.label>
                <x-ui.textarea wire:model="form.internal_note" rows="2" />
            </div>
            <p class="text-xs text-slate-400">Napomena: za tip "po satu" ukupna cijena se računa automatski iz unosa vremena.</p>
            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" x-on:click="show = false">Otkaži</x-ui.button>
                <x-ui.button type="submit">Sačuvaj</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
