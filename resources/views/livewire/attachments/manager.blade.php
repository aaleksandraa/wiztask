<div>
    <x-ui.card :title="$title" padding="p-5">
        <form wire:submit="upload" class="mb-5 space-y-3">
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="sm:col-span-1">
                    <x-ui.label>Kategorija</x-ui.label>
                    <x-ui.select wire:model="category" :options="$categories" />
                </div>
                <div class="sm:col-span-2">
                    <x-ui.label>Opis (opcionalno)</x-ui.label>
                    <x-ui.input wire:model="description" placeholder="Kratak opis fajlova" />
                </div>
            </div>
            <div>
                <x-ui.label>Fajlovi (možeš odabrati više)</x-ui.label>
                <input type="file" multiple wire:model="files"
                       class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-neutral-900 hover:file:bg-neutral-200 dark:text-slate-300 dark:file:bg-neutral-800 dark:file:text-white" />
                <x-ui.error name="files" />
                <x-ui.error name="files.*" />
                <div wire:loading wire:target="files" class="mt-1 text-xs text-slate-400">Učitavanje...</div>
            </div>
            @if (count($files))
                <div class="text-xs text-slate-500">Odabrano: {{ count($files) }} fajl(ova)</div>
                <x-ui.button type="submit" size="sm">Otpremi</x-ui.button>
            @endif
        </form>

        @if ($attachments->isEmpty())
            <x-ui.empty text="Još nema priloženih fajlova." />
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                @foreach ($attachments as $att)
                    <div class="group relative overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex h-28 items-center justify-center bg-slate-50 dark:bg-slate-900/40">
                            @if ($att->isImage())
                                <img src="{{ $att->url() }}" alt="{{ $att->original_name }}" class="h-full w-full object-cover" />
                            @else
                                <svg class="h-10 w-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        <div class="p-2">
                            <div class="truncate text-xs font-medium text-slate-700 dark:text-slate-200" title="{{ $att->original_name }}">{{ $att->original_name }}</div>
                            <div class="mt-1 flex items-center justify-between">
                                <x-ui.badge :value="$att->category" :map="$categories" />
                                <span class="text-[10px] text-slate-400">{{ $att->humanSize() }}</span>
                            </div>
                            @if ($att->description)
                                <div class="mt-1 truncate text-[11px] text-slate-400" title="{{ $att->description }}">{{ $att->description }}</div>
                            @endif
                            <div class="mt-2 flex items-center justify-between">
                                <a href="{{ route('attachments.download', $att) }}" class="text-xs text-neutral-900 hover:underline dark:text-white">Download</a>
                                <button type="button" wire:click="deleteAttachment({{ $att->id }})" wire:confirm="Obrisati fajl?" class="text-xs text-red-600 hover:underline">Obriši</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>
</div>
