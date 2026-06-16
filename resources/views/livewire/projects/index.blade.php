<div>
    <x-page-header title="Projekti" subtitle="Svi projekti po klijentima.">
        <x-slot:actions>
            <x-ui.button wire:click="create">+ Novi projekat</x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <x-ui.card class="mb-4" padding="p-4">
        <div class="grid gap-3 sm:grid-cols-4">
            <div>
                <x-ui.label>Pretraga</x-ui.label>
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Naziv projekta" />
            </div>
            <div>
                <x-ui.label>Klijent</x-ui.label>
                <x-ui.select wire:model.live="client_id" :options="$clients->toArray()" placeholder="Svi klijenti" />
            </div>
            <div>
                <x-ui.label>Status</x-ui.label>
                <x-ui.select wire:model.live="status" :options="$statuses" placeholder="Svi statusi" />
            </div>
            <div>
                <x-ui.label>Tip naplate</x-ui.label>
                <x-ui.select wire:model.live="billing_type" :options="$billingTypes" placeholder="Svi tipovi" />
            </div>
        </div>
    </x-ui.card>

    <x-ui.card padding="p-0">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr>
                    <th class="px-4 py-3">Projekat</th><th class="px-4 py-3">Klijent</th><th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Tip naplate</th><th class="px-4 py-3">Rok</th><th class="px-4 py-3 text-center">Taskovi</th><th class="px-4 py-3 text-right">Akcije</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($projects as $p)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-3"><a href="{{ route('projects.show', $p) }}" wire:navigate class="font-medium text-neutral-900 hover:underline dark:text-white">{{ $p->name }}</a></td>
                            <td class="px-4 py-3">{{ $p->client->name ?? '-' }}</td>
                            <td class="px-4 py-3"><x-ui.badge :value="$p->status" :map="$statuses" /></td>
                            <td class="px-4 py-3">{{ \App\Support\Options::label($billingTypes, $p->billing_type) }}</td>
                            <td class="px-4 py-3"><x-ui.date :value="$p->due_date" /></td>
                            <td class="px-4 py-3 text-center">{{ $p->tasks_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <x-ui.button variant="ghost" size="sm" wire:click="edit({{ $p->id }})">Izmijeni</x-ui.button>
                                    <x-ui.button variant="ghost" size="sm" wire:click="delete({{ $p->id }})" wire:confirm="Obrisati projekat?">🗑</x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-ui.empty text="Nema projekata." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">{{ $projects->links() }}</div>

    <x-ui.modal wire:model="showModal" :title="$editingId ? 'Izmjena projekta' : 'Novi projekat'" maxWidth="max-w-3xl">
        <form wire:submit="save" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label>Klijent *</x-ui.label>
                    <x-ui.select wire:model="form.client_id" :options="$clients->toArray()" placeholder="Odaberi klijenta" />
                    <x-ui.error name="form.client_id" />
                </div>
                <div>
                    <x-ui.label>Naziv projekta *</x-ui.label>
                    <x-ui.input wire:model="form.name" />
                    <x-ui.error name="form.name" />
                </div>
                <div>
                    <x-ui.label>Status</x-ui.label>
                    <x-ui.select wire:model="form.status" :options="$statuses" />
                </div>
                <div>
                    <x-ui.label>Tip naplate</x-ui.label>
                    <x-ui.select wire:model="form.billing_type" :options="$billingTypes" />
                </div>
                <div>
                    <x-ui.label>Datum početka</x-ui.label>
                    <x-ui.date-input wire:model="form.start_date" />
                </div>
                <div>
                    <x-ui.label>Rok</x-ui.label>
                    <x-ui.date-input wire:model="form.due_date" />
                </div>
                <div>
                    <x-ui.label>Dogovorena (fiksna) cijena</x-ui.label>
                    <x-ui.input type="number" step="0.01" wire:model="form.fixed_price" />
                    <x-ui.error name="form.fixed_price" />
                </div>
                <div>
                    <x-ui.label>Valuta</x-ui.label>
                    <x-ui.select wire:model="form.currency" :options="collect(\App\Support\Options::CURRENCIES)->mapWithKeys(fn($c) => [$c => $c])->all()" />
                </div>
            </div>
            <div>
                <x-ui.label>Opis</x-ui.label>
                <x-ui.textarea wire:model="form.description" rows="3" />
            </div>
            <div>
                <x-ui.label>Napomena</x-ui.label>
                <x-ui.textarea wire:model="form.note" rows="2" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" x-on:click="show = false">Otkaži</x-ui.button>
                <x-ui.button type="submit">Sačuvaj</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
