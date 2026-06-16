<div>
    <x-page-header title="Podešavanja" subtitle="Globalne postavke aplikacije." />

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-ui.card title="Osnovne postavke">
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <x-ui.label>Naziv aplikacije</x-ui.label>
                        <x-ui.input wire:model="app_name" />
                        <x-ui.error name="app_name" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.label>Default valuta</x-ui.label>
                            <x-ui.select wire:model="default_currency" :options="$currencies" />
                            <x-ui.error name="default_currency" />
                        </div>
                        <div>
                            <x-ui.label>Default satnica</x-ui.label>
                            <x-ui.input type="number" step="0.01" wire:model="default_hourly_rate" />
                            <x-ui.error name="default_hourly_rate" />
                        </div>
                    </div>
                    <div>
                        <x-ui.label>Dozvoljeni tipovi fajlova (ekstenzije, odvojene zarezom)</x-ui.label>
                        <x-ui.input wire:model="allowed_file_types" />
                        <x-ui.error name="allowed_file_types" />
                        <p class="mt-1 text-xs text-slate-400">Npr: jpg,png,pdf,docx,xlsx,zip</p>
                    </div>
                    <div class="flex justify-end pt-2">
                        <x-ui.button type="submit">Sačuvaj postavke</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Statusi taskova">
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($taskStatuses as $k => $v)
                        <x-ui.badge :value="$k" :map="$taskStatuses" />
                    @endforeach
                </div>
            </x-ui.card>
            <x-ui.card title="Statusi projekata">
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($projectStatuses as $k => $v)
                        <x-ui.badge :value="$k" :map="$projectStatuses" />
                    @endforeach
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
