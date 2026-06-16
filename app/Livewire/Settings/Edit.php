<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Support\Options;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Podešavanja')]
class Edit extends Component
{
    public string $app_name = '';
    public string $default_currency = 'KM';
    public string $default_hourly_rate = '40';
    public string $allowed_file_types = '';

    public function mount(): void
    {
        $this->app_name = (string) Setting::get('app_name', 'WizTask');
        $this->default_currency = (string) Setting::get('default_currency', 'KM');
        $this->default_hourly_rate = (string) Setting::get('default_hourly_rate', '40');
        $this->allowed_file_types = (string) Setting::get('allowed_file_types', 'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip');
    }

    public function save(): void
    {
        $this->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'default_currency' => ['required', 'in:'.implode(',', Options::CURRENCIES)],
            'default_hourly_rate' => ['required', 'numeric', 'min:0'],
            'allowed_file_types' => ['required', 'string'],
        ]);

        Setting::put('app_name', $this->app_name);
        Setting::put('default_currency', $this->default_currency);
        Setting::put('default_hourly_rate', $this->default_hourly_rate);
        Setting::put('allowed_file_types', $this->allowed_file_types);

        session()->flash('success', 'Podešavanja su sačuvana.');
    }

    public function render()
    {
        return view('livewire.settings.edit', [
            'currencies' => collect(Options::CURRENCIES)->mapWithKeys(fn ($c) => [$c => $c])->all(),
            'taskStatuses' => Options::TASK_STATUSES,
            'projectStatuses' => Options::PROJECT_STATUSES,
        ]);
    }
}
