<div>
    <x-page-header title="Dashboard" :subtitle="'Pregled stanja na ' . \App\Support\Dates::formatOr(now())">
        <x-slot:actions>
            <x-ui.button wire:click="openQuickTask">+ Brzi task</x-ui.button>
        </x-slot:actions>
    </x-page-header>

    {{-- Brze precice --}}
    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.quick-action wire:click="openQuickTask" sub="Naslov, klijent, status">
            <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></x-slot:icon>
            Dodaj task
        </x-ui.quick-action>
        <x-ui.quick-action :href="route('clients.index')" sub="Novi ili postojeći klijent">
            <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87"/></svg></x-slot:icon>
            Klijenti
        </x-ui.quick-action>
        <x-ui.quick-action :href="route('time.index')" sub="Unos sati na task">
            <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon>
            Unesi vrijeme
        </x-ui.quick-action>
        <x-ui.quick-action :href="route('reports.index')" sub="PDF, Excel, print">
            <x-slot:icon><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot:icon>
            Izvještaj
        </x-ui.quick-action>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-ui.stat label="Aktivni klijenti" :value="$activeClients" color="neutral" />
        <x-ui.stat label="Taskovi u toku" :value="$tasksInProgress" color="blue" />
        <x-ui.stat label="Završeno ovaj mjesec" :value="$tasksDoneThisMonth" color="green" />
        <x-ui.stat label="Taskovi za naplatu" :value="$tasksToBill" color="purple" />
        <x-ui.stat label="Neplaćeni iznos" :value="\App\Support\Money::format($unpaidAmount)" color="red" />
        <x-ui.stat label="Sati ovaj mjesec" :value="\App\Support\Money::minutesToHuman($minutesThisMonth)" color="amber" />
        <x-ui.stat label="Vrijednost poslova (mjesec)" :value="\App\Support\Money::format($valueThisMonth)" color="green" />
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @php
            $lists = [
                ['title' => 'Najnoviji taskovi', 'items' => $latestTasks],
                ['title' => 'Taskovi u toku', 'items' => $inProgress],
                ['title' => 'Čekaju klijenta', 'items' => $waitingClient],
                ['title' => 'Za naplatu', 'items' => $toBill],
                ['title' => 'Taskovi sa rokom', 'items' => $withDueDate, 'due' => true],
            ];
        @endphp

        @foreach ($lists as $list)
            <x-ui.card :title="$list['title']" padding="p-0">
                @forelse ($list['items'] as $task)
                    <a href="{{ route('tasks.show', $task) }}" wire:navigate
                       class="flex items-center justify-between gap-3 border-b border-neutral-100 px-5 py-3.5 last:border-0 transition hover:bg-neutral-50/80 dark:border-neutral-800 dark:hover:bg-neutral-800/40">
                        <div class="min-w-0">
                            <div class="truncate font-medium text-slate-800 dark:text-slate-100">{{ $task->title }}</div>
                            <div class="text-xs text-slate-400">{{ $task->client->name ?? '-' }}</div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if (! empty($list['due']) && $task->due_date)
                                <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-500' : 'text-neutral-400' }}"><x-ui.date :value="$task->due_date" /></span>
                            @endif
                            <x-ui.badge :value="$task->status" :map="$taskStatuses" />
                        </div>
                    </a>
                @empty
                    <x-ui.empty text="Nema taskova." />
                @endforelse
            </x-ui.card>
        @endforeach
    </div>

    {{-- Brzi task modal --}}
    <x-ui.modal wire:model="showQuickTaskModal" title="Brzi unos taska" maxWidth="max-w-lg">
        <form wire:submit="saveQuickTask" class="space-y-4">
            <div>
                <x-ui.label>Klijent *</x-ui.label>
                <x-ui.select wire:model.live="quickTask.client_id" :options="$clients->pluck('name', 'id')->all()" placeholder="Odaberi klijenta" />
                <x-ui.error name="quickTask.client_id" />
            </div>
            <div>
                <x-ui.label>Projekat</x-ui.label>
                <x-ui.select wire:model="quickTask.project_id" :options="$quickProjects->all()" placeholder="Bez projekta" />
            </div>
            <div>
                <x-ui.label>Naslov *</x-ui.label>
                <x-ui.input wire:model="quickTask.title" placeholder="Šta treba uraditi?" autofocus />
                <x-ui.error name="quickTask.title" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label>Status</x-ui.label>
                    <x-ui.select wire:model="quickTask.status" :options="$taskStatuses" />
                </div>
                <div>
                    <x-ui.label>Prioritet</x-ui.label>
                    <x-ui.select wire:model="quickTask.priority" :options="$priorities" />
                </div>
                <div>
                    <x-ui.label>Datum</x-ui.label>
                    <x-ui.date-input wire:model="quickTask.task_date" />
                </div>
                <div>
                    <x-ui.label>Satnica</x-ui.label>
                    <x-ui.input type="number" step="0.01" wire:model="quickTask.hourly_rate" />
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-ui.button type="button" variant="secondary" x-on:click="show = false">Otkaži</x-ui.button>
                <x-ui.button type="submit">Sačuvaj i otvori</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
