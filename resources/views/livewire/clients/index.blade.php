<div>
    <x-page-header title="Klijenti" subtitle="Evidencija klijenata i osnovnih podataka.">
        <x-slot:actions>
            <x-ui.button wire:click="create">+ Novi klijent</x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <x-ui.card class="mb-4" padding="p-4">
        <div class="grid gap-3 sm:grid-cols-3">
            <div>
                <x-ui.label>Pretraga</x-ui.label>
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Naziv, kontakt, email..." />
            </div>
            <div>
                <x-ui.label>Status</x-ui.label>
                <x-ui.select wire:model.live="status" :options="$statuses" placeholder="Svi statusi" />
            </div>
            <div>
                <x-ui.label>Grad</x-ui.label>
                <x-ui.input wire:model.live.debounce.300ms="city" placeholder="Grad" />
            </div>
        </div>
    </x-ui.card>

    <x-ui.card padding="p-0">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/40 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Klijent</th>
                        <th class="px-4 py-3">Kontakt</th>
                        <th class="px-4 py-3">Lokacija</th>
                        <th class="px-4 py-3">Satnica</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Projekti</th>
                        <th class="px-4 py-3 text-center">Taskovi</th>
                        <th class="px-4 py-3 text-right">Akcije</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($clients as $client)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <a href="{{ route('clients.show', $client) }}" wire:navigate class="font-medium text-neutral-900 hover:underline dark:text-white">{{ $client->name }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $client->contact_person ?: '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $client->email }}</div>
                            </td>
                            <td class="px-4 py-3">{{ trim($client->city.', '.$client->country, ', ') ?: '-' }}</td>
                            <td class="px-4 py-3">{{ \App\Support\Money::format($client->default_hourly_rate, $client->currency) }}</td>
                            <td class="px-4 py-3"><x-ui.badge :value="$client->status" :map="$statuses" /></td>
                            <td class="px-4 py-3 text-center">{{ $client->projects_count }}</td>
                            <td class="px-4 py-3 text-center">{{ $client->tasks_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <x-ui.button variant="ghost" size="sm" wire:click="edit({{ $client->id }})">Izmijeni</x-ui.button>
                                    <x-ui.button variant="ghost" size="sm" wire:click="delete({{ $client->id }})"
                                                 wire:confirm="Obrisati klijenta i sve vezane podatke?">🗑</x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-ui.empty text="Nema klijenata. Dodaj prvog klijenta." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">{{ $clients->links() }}</div>

    {{-- Modal --}}
    <x-ui.modal wire:model="showModal" :title="$editingId ? 'Izmjena klijenta' : 'Novi klijent'" maxWidth="max-w-3xl">
        <form wire:submit="save" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label>Naziv klijenta *</x-ui.label>
                    <x-ui.input wire:model="form.name" />
                    <x-ui.error name="form.name" />
                </div>
                <div>
                    <x-ui.label>Kontakt osoba</x-ui.label>
                    <x-ui.input wire:model="form.contact_person" />
                </div>
                <div>
                    <x-ui.label>Email</x-ui.label>
                    <x-ui.input type="email" wire:model="form.email" />
                    <x-ui.error name="form.email" />
                </div>
                <div>
                    <x-ui.label>Telefon</x-ui.label>
                    <x-ui.input wire:model="form.phone" />
                </div>
                <div>
                    <x-ui.label>Web stranica</x-ui.label>
                    <x-ui.input wire:model="form.website" placeholder="https://" />
                </div>
                <div>
                    <x-ui.label>Grad</x-ui.label>
                    <x-ui.input wire:model="form.city" />
                </div>
                <div>
                    <x-ui.label>Država</x-ui.label>
                    <x-ui.input wire:model="form.country" />
                </div>
                <div>
                    <x-ui.label>Adresa</x-ui.label>
                    <x-ui.input wire:model="form.address" />
                </div>
                <div>
                    <x-ui.label>Default satnica</x-ui.label>
                    <x-ui.input type="number" step="0.01" wire:model="form.default_hourly_rate" />
                    <x-ui.error name="form.default_hourly_rate" />
                </div>
                <div>
                    <x-ui.label>Valuta</x-ui.label>
                    <x-ui.select wire:model="form.currency" :options="collect(\App\Support\Options::CURRENCIES)->mapWithKeys(fn($c) => [$c => $c])->all()" />
                </div>
                <div>
                    <x-ui.label>Status</x-ui.label>
                    <x-ui.select wire:model="form.status" :options="$statuses" />
                </div>
            </div>
            <div>
                <x-ui.label>Napomena</x-ui.label>
                <x-ui.textarea wire:model="form.note" rows="3" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" x-on:click="show = false">Otkaži</x-ui.button>
                <x-ui.button type="submit">Sačuvaj</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
