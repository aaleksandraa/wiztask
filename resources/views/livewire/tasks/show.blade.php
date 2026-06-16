<div>
    <x-page-header :title="$task->title" :subtitle="$task->client->name.($task->project ? ' · '.$task->project->name : '')">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('tasks.index')" wire:navigate>← Nazad</x-ui.button>
            <x-ui.button :href="route('clients.show', $task->client)" wire:navigate>Klijent</x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-ui.stat label="Prioritet" :value="\App\Support\Options::label($priorities, $task->priority)" color="amber" />
        <x-ui.stat label="Ukupno vrijeme" :value="\App\Support\Money::minutesToHuman($totalMinutes)" color="blue" />
        <x-ui.stat label="Ukupna cijena" :value="\App\Support\Money::format($task->total_price, $task->client->currency)" color="green" />
        <x-ui.stat label="Tip naplate" :value="\App\Support\Options::label($billingTypes, $task->billing_type)" color="indigo" />
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            {{-- Status controls --}}
            <x-ui.card title="Status i naplata">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.label>Status taska</x-ui.label>
                        <x-ui.select :options="$statuses" :selected="$task->status" x-on:change="$wire.updateStatus($event.target.value)" />
                    </div>
                    <div>
                        <x-ui.label>Status plaćanja</x-ui.label>
                        <x-ui.select :options="$paymentStatuses" :selected="$task->payment_status" x-on:change="$wire.updatePaymentStatus($event.target.value)" />
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <x-ui.badge :value="$task->status" :map="$statuses" />
                    <x-ui.badge :value="$task->payment_status" :map="$paymentStatuses" />
                    @if (! $task->is_billable)<x-ui.badge value="nije_za_naplatu" :map="['nije_za_naplatu' => 'Nije naplativo']" />@endif
                </div>
            </x-ui.card>

            {{-- Time entries --}}
            <x-ui.card padding="p-0">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-slate-700">
                    <h3 class="font-semibold text-slate-800 dark:text-slate-100">Evidencija vremena</h3>
                    <x-ui.button size="sm" wire:click="addTime">+ Dodaj vrijeme</x-ui.button>
                </div>
                <table class="data-table">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr>
                        <th class="px-4 py-3">Datum</th><th class="px-4 py-3">Opis</th><th class="px-4 py-3">Vrijeme</th>
                        <th class="px-4 py-3">Satnica</th><th class="px-4 py-3 text-right">Iznos</th><th class="px-4 py-3 text-right">Akcije</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse ($timeEntries as $te)
                            <tr>
                                <td class="px-4 py-3"><x-ui.date :value="$te->work_date" /></td>
                                <td class="px-4 py-3">{{ $te->description ?: '-' }}</td>
                                <td class="px-4 py-3">{{ \App\Support\Money::minutesToHuman($te->total_minutes) }}</td>
                                <td class="px-4 py-3">{{ \App\Support\Money::format($te->hourly_rate, $task->client->currency) }}</td>
                                <td class="px-4 py-3 text-right">{{ \App\Support\Money::format($te->total_price, $task->client->currency) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <x-ui.button variant="ghost" size="sm" wire:click="editTime({{ $te->id }})">✎</x-ui.button>
                                    <x-ui.button variant="ghost" size="sm" wire:click="deleteTime({{ $te->id }})" wire:confirm="Obrisati unos?">🗑</x-ui.button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.empty text="Nema unesenog vremena." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-ui.card>

            <livewire:attachments.manager :attachable-type="\App\Models\Task::class" :attachable-id="$task->id" title="Fajlovi i dokazi rada" :key="'task-att-'.$task->id" />
        </div>

        <div class="space-y-4">
            <x-ui.card title="Detalji">
                <dl class="grid grid-cols-2 gap-y-3 text-sm">
                    <dt class="text-neutral-500">Datum</dt><dd><x-ui.date :value="$task->task_date" /></dd>
                    <dt class="text-neutral-500">Rok</dt><dd class="{{ $task->due_date && $task->due_date->isPast() ? 'text-red-500' : '' }}"><x-ui.date :value="$task->due_date" /></dd>
                    <dt class="text-slate-500">Satnica</dt><dd>{{ \App\Support\Money::format($task->hourly_rate, $task->client->currency) }}</dd>
                    <dt class="text-slate-500">Fiksna cijena</dt><dd>{{ \App\Support\Money::format($task->fixed_price, $task->client->currency) }}</dd>
                    <dt class="text-slate-500">Naplativo</dt><dd>{{ $task->is_billable ? 'Da' : 'Ne' }}</dd>
                </dl>
            </x-ui.card>
            @if ($task->description)
                <x-ui.card title="Opis"><p class="whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $task->description }}</p></x-ui.card>
            @endif
            @if ($task->internal_note)
                <x-ui.card title="Interne napomene"><p class="whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $task->internal_note }}</p></x-ui.card>
            @endif
        </div>
    </div>

    <x-ui.modal wire:model="showTimeModal" :title="$editingTimeId ? 'Izmjena vremena' : 'Unos vremena'">
        <form wire:submit="saveTime" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label>Datum rada *</x-ui.label>
                    <x-ui.date-input wire:model="timeForm.work_date" />
                    <x-ui.error name="timeForm.work_date" />
                </div>
                <div>
                    <x-ui.label>Satnica</x-ui.label>
                    <x-ui.input type="number" step="0.01" wire:model="timeForm.hourly_rate" />
                    <x-ui.error name="timeForm.hourly_rate" />
                </div>
                <div>
                    <x-ui.label>Sati</x-ui.label>
                    <x-ui.input type="number" min="0" wire:model="timeForm.hours" />
                    <x-ui.error name="timeForm.hours" />
                </div>
                <div>
                    <x-ui.label>Minute</x-ui.label>
                    <x-ui.input type="number" min="0" max="59" wire:model="timeForm.minutes" />
                    <x-ui.error name="timeForm.minutes" />
                </div>
            </div>
            <div>
                <x-ui.label>Opis rada</x-ui.label>
                <x-ui.textarea wire:model="timeForm.description" rows="2" />
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="timeForm.is_billable" id="te_billable" class="rounded border-slate-300 text-neutral-900 focus:ring-neutral-900" />
                <label for="te_billable" class="text-sm text-slate-700 dark:text-slate-300">Naplativo</label>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" x-on:click="show = false">Otkaži</x-ui.button>
                <x-ui.button type="submit">Sačuvaj</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
