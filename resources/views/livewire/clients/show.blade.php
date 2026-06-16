<div>
    <x-page-header :title="$client->name" :subtitle="trim($client->city.', '.$client->country, ', ')">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('clients.index')" wire:navigate>← Nazad</x-ui.button>
            <x-ui.button variant="secondary" wire:click="openProjectModal">+ Projekat</x-ui.button>
            <x-ui.button wire:click="openTaskModal">+ Task</x-ui.button>
            <x-ui.button variant="secondary" :href="route('reports.index', ['client_id' => $client->id])" wire:navigate>Izvještaj</x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-ui.stat label="Projekti" :value="$projectsCount" color="neutral" />
        <x-ui.stat label="Ukupno sati" :value="\App\Support\Money::minutesToHuman($totalMinutes)" color="amber" />
        <x-ui.stat label="Za naplatu" :value="\App\Support\Money::format($totalUnpaid, $client->currency)" color="red" />
        <x-ui.stat label="Plaćeno" :value="\App\Support\Money::format($totalPaid, $client->currency)" color="green" />
    </div>

    @php
        $tabs = [
            'pregled' => 'Pregled', 'projekti' => 'Projekti', 'taskovi' => 'Taskovi',
            'vrijeme' => 'Vrijeme', 'naplata' => 'Naplata', 'fajlovi' => 'Fajlovi', 'izvjestaji' => 'Izvještaji',
        ];
    @endphp
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap gap-1">
            @foreach ($tabs as $key => $label)
                <button wire:click="setTab('{{ $key }}')"
                        wire:loading.attr="disabled"
                        wire:target="setTab('{{ $key }}')"
                        class="border-b-2 px-4 py-2 text-sm font-medium transition {{ $tab === $key ? 'border-neutral-900 text-neutral-900 dark:border-white dark:text-white' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        @if (in_array($tab, ['projekti', 'taskovi'], true))
            <div class="flex gap-2 pb-2">
                @if ($tab === 'projekti')
                    <x-ui.button size="sm" wire:click="openProjectModal">+ Dodaj projekat</x-ui.button>
                @endif
                @if ($tab === 'taskovi')
                    <x-ui.button size="sm" wire:click="openTaskModal">+ Dodaj task</x-ui.button>
                @endif
            </div>
        @endif
    </div>

    <div wire:loading.flex wire:target="setTab" class="mb-4 hidden items-center gap-2 text-sm text-slate-500">
        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        Učitavanje...
    </div>

    @if ($tab === 'pregled')
        <div class="grid gap-4 lg:grid-cols-2">
            <x-ui.card title="Podaci o klijentu">
                <dl class="grid grid-cols-3 gap-y-3 text-sm">
                    <dt class="text-slate-500">Kontakt osoba</dt><dd class="col-span-2">{{ $client->contact_person ?: '-' }}</dd>
                    <dt class="text-slate-500">Email</dt><dd class="col-span-2">{{ $client->email ?: '-' }}</dd>
                    <dt class="text-slate-500">Telefon</dt><dd class="col-span-2">{{ $client->phone ?: '-' }}</dd>
                    <dt class="text-slate-500">Web</dt><dd class="col-span-2">{{ $client->website ?: '-' }}</dd>
                    <dt class="text-slate-500">Adresa</dt><dd class="col-span-2">{{ $client->address ?: '-' }}</dd>
                    <dt class="text-slate-500">Grad / Država</dt><dd class="col-span-2">{{ trim($client->city.', '.$client->country, ', ') ?: '-' }}</dd>
                    <dt class="text-slate-500">Status</dt><dd class="col-span-2"><x-ui.badge :value="$client->status" :map="$clientStatuses" /></dd>
                    <dt class="text-slate-500">Satnica</dt><dd class="col-span-2">{{ \App\Support\Money::format($client->default_hourly_rate, $client->currency) }}</dd>
                </dl>
            </x-ui.card>
            <x-ui.card title="Napomena">
                <p class="whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $client->note ?: 'Nema napomene.' }}</p>
            </x-ui.card>
            <x-ui.card title="Brze akcije" class="lg:col-span-2">
                <div class="grid gap-3 sm:grid-cols-3">
                    <x-ui.quick-action wire:click="openTaskModal" sub="Dodaj posao za ovog klijenta">
                        <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></x-slot:icon>
                        Dodaj task
                    </x-ui.quick-action>
                    <x-ui.quick-action wire:click="openProjectModal" sub="Novi projekat klijenta">
                        <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg></x-slot:icon>
                        Dodaj projekat
                    </x-ui.quick-action>
                    <x-ui.quick-action :href="route('reports.index', ['client_id' => $client->id])" sub="Export za period">
                        <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot:icon>
                        Izvještaj
                    </x-ui.quick-action>
                </div>
            </x-ui.card>
        </div>
    @endif

    @if ($tab === 'projekti')
        <x-ui.card padding="p-0">
            <table class="data-table">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr>
                    <th class="px-4 py-3">Projekat</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Tip naplate</th><th class="px-4 py-3 text-center">Taskovi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($projects as $p)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-3"><a href="{{ route('projects.show', $p) }}" wire:navigate class="font-medium text-neutral-900 hover:underline dark:text-white">{{ $p->name }}</a></td>
                            <td class="px-4 py-3"><x-ui.badge :value="$p->status" :map="$projectStatuses" /></td>
                            <td class="px-4 py-3">{{ \App\Support\Options::label($projectBillingTypes, $p->billing_type) }}</td>
                            <td class="px-4 py-3 text-center">{{ $p->tasks_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-ui.empty text="Nema projekata."><x-ui.button size="sm" wire:click="openProjectModal">+ Dodaj projekat</x-ui.button></x-ui.empty></td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.card>
    @endif

    @if ($tab === 'taskovi')
        <x-ui.card padding="p-0">
            <table class="data-table">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr>
                    <th class="px-4 py-3">Task</th><th class="px-4 py-3">Projekat</th><th class="px-4 py-3">Datum</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Cijena</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($tasks as $t)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-3"><a href="{{ route('tasks.show', $t) }}" wire:navigate class="font-medium text-neutral-900 hover:underline dark:text-white">{{ $t->title }}</a></td>
                            <td class="px-4 py-3">{{ $t->project->name ?? '-' }}</td>
                            <td class="px-4 py-3"><x-ui.date :value="$t->task_date" /></td>
                            <td class="px-4 py-3"><x-ui.badge :value="$t->status" :map="$taskStatuses" /></td>
                            <td class="px-4 py-3 text-right">{{ \App\Support\Money::format($t->total_price, $client->currency) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-ui.empty text="Nema taskova."><x-ui.button size="sm" wire:click="openTaskModal">+ Dodaj task</x-ui.button></x-ui.empty></td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.card>
    @endif

    @if ($tab === 'vrijeme')
        <x-ui.card padding="p-0">
            <table class="data-table">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr>
                    <th class="px-4 py-3">Datum</th><th class="px-4 py-3">Task</th><th class="px-4 py-3">Opis</th><th class="px-4 py-3">Vrijeme</th><th class="px-4 py-3 text-right">Cijena</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($timeEntries as $te)
                        <tr>
                            <td class="px-4 py-3"><x-ui.date :value="$te->work_date" /></td>
                            <td class="px-4 py-3">{{ $te->task->title ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $te->description ?: '-' }}</td>
                            <td class="px-4 py-3">{{ \App\Support\Money::minutesToHuman($te->total_minutes) }}</td>
                            <td class="px-4 py-3 text-right">{{ \App\Support\Money::format($te->total_price, $client->currency) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-ui.empty text="Nema unosa vremena." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.card>
    @endif

    @if ($tab === 'naplata')
        <div class="grid gap-4 lg:grid-cols-3">
            <x-ui.stat label="Ukupno naplativo" :value="\App\Support\Money::format($totalBillable, $client->currency)" color="neutral" />
            <x-ui.stat label="Plaćeno" :value="\App\Support\Money::format($totalPaid, $client->currency)" color="green" />
            <x-ui.stat label="Neplaćeno" :value="\App\Support\Money::format($totalUnpaid, $client->currency)" color="red" />
        </div>
        <div class="mt-4">
            <x-ui.card title="Taskovi i status plaćanja" padding="p-0">
                <table class="data-table">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr>
                        <th class="px-4 py-3">Task</th><th class="px-4 py-3">Cijena</th><th class="px-4 py-3">Status plaćanja</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse ($tasks->where('is_billable', true) as $t)
                            <tr>
                                <td class="px-4 py-3"><a href="{{ route('tasks.show', $t) }}" wire:navigate class="font-medium text-neutral-900 hover:underline dark:text-white">{{ $t->title }}</a></td>
                                <td class="px-4 py-3">{{ \App\Support\Money::format($t->total_price, $client->currency) }}</td>
                                <td class="px-4 py-3"><x-ui.badge :value="$t->payment_status" :map="$paymentStatuses" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-ui.empty text="Nema naplativih taskova." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-ui.card>
        </div>
    @endif

    @if ($tab === 'fajlovi')
        <x-ui.card title="Svi fajlovi klijenta (projekti i taskovi)">
            @if ($attachments->isEmpty())
                <x-ui.empty text="Nema fajlova." />
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                    @foreach ($attachments as $att)
                        <div class="overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex h-24 items-center justify-center bg-slate-50 dark:bg-slate-900/40">
                                @if ($att->isImage())
                                    <img src="{{ $att->url() }}" loading="lazy" class="h-full w-full object-cover" />
                                @else
                                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                @endif
                            </div>
                            <div class="p-2">
                                <div class="truncate text-xs">{{ $att->original_name }}</div>
                                <a href="{{ route('attachments.download', $att) }}" class="text-[11px] font-medium text-neutral-900 hover:underline dark:text-white">Download</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    @endif

    @if ($tab === 'izvjestaji')
        <x-ui.card title="Izvještaj klijenta">
            <p class="mb-4 text-sm text-slate-500">Generiši detaljan izvještaj rada i naplate za ovog klijenta za odabrani period.</p>
            <x-ui.button :href="route('reports.index', ['client_id' => $client->id])" wire:navigate>Otvori izvještaj →</x-ui.button>
        </x-ui.card>
    @endif

    {{-- Modal: projekat --}}
    <x-ui.modal wire:model="showProjectModal" title="Novi projekat" maxWidth="max-w-2xl">
        <form wire:submit="saveProject" class="space-y-4">
            <div>
                <x-ui.label>Naziv projekta *</x-ui.label>
                <x-ui.input wire:model="projectForm.name" />
                <x-ui.error name="projectForm.name" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label>Status</x-ui.label>
                    <x-ui.select wire:model="projectForm.status" :options="$projectStatuses" />
                </div>
                <div>
                    <x-ui.label>Tip naplate</x-ui.label>
                    <x-ui.select wire:model="projectForm.billing_type" :options="$projectBillingTypes" />
                </div>
                <div>
                    <x-ui.label>Datum početka</x-ui.label>
                    <x-ui.date-input wire:model="projectForm.start_date" />
                </div>
                <div>
                    <x-ui.label>Rok</x-ui.label>
                    <x-ui.date-input wire:model="projectForm.due_date" />
                </div>
                <div>
                    <x-ui.label>Fiksna cijena</x-ui.label>
                    <x-ui.input type="number" step="0.01" wire:model="projectForm.fixed_price" />
                </div>
                <div>
                    <x-ui.label>Valuta</x-ui.label>
                    <x-ui.select wire:model="projectForm.currency" :options="collect(\App\Support\Options::CURRENCIES)->mapWithKeys(fn($c) => [$c => $c])->all()" />
                </div>
            </div>
            <div>
                <x-ui.label>Opis</x-ui.label>
                <x-ui.textarea wire:model="projectForm.description" rows="2" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" x-on:click="show = false">Otkaži</x-ui.button>
                <x-ui.button type="submit">Sačuvaj projekat</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal: task --}}
    <x-ui.modal wire:model="showTaskModal" title="Novi task" maxWidth="max-w-2xl">
        <form wire:submit="saveTask" class="space-y-4">
            <div>
                <x-ui.label>Naslov *</x-ui.label>
                <x-ui.input wire:model="taskForm.title" placeholder="Opis posla" />
                <x-ui.error name="taskForm.title" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label>Projekat</x-ui.label>
                    <x-ui.select wire:model="taskForm.project_id" :options="$clientProjects->all()" placeholder="Bez projekta" />
                </div>
                <div>
                    <x-ui.label>Datum</x-ui.label>
                    <x-ui.date-input wire:model="taskForm.task_date" />
                </div>
                <div>
                    <x-ui.label>Status</x-ui.label>
                    <x-ui.select wire:model="taskForm.status" :options="$taskStatuses" />
                </div>
                <div>
                    <x-ui.label>Prioritet</x-ui.label>
                    <x-ui.select wire:model="taskForm.priority" :options="$taskPriorities" />
                </div>
                <div>
                    <x-ui.label>Satnica</x-ui.label>
                    <x-ui.input type="number" step="0.01" wire:model="taskForm.hourly_rate" />
                </div>
                <div>
                    <x-ui.label>Rok</x-ui.label>
                    <x-ui.date-input wire:model="taskForm.due_date" />
                </div>
            </div>
            <div>
                <x-ui.label>Opis</x-ui.label>
                <x-ui.textarea wire:model="taskForm.description" rows="2" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" x-on:click="show = false">Otkaži</x-ui.button>
                <x-ui.button type="submit">Sačuvaj task</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
