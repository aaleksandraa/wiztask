<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Settings/Edit', [
            'form' => [
                'app_name' => (string) Setting::get('app_name', 'WizTask'),
                'default_currency' => (string) Setting::get('default_currency', 'KM'),
                'default_hourly_rate' => (string) Setting::get('default_hourly_rate', '40'),
                'allowed_file_types' => (string) Setting::get('allowed_file_types', 'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'default_currency' => ['required', 'in:'.implode(',', Options::CURRENCIES)],
            'default_hourly_rate' => ['required', 'numeric', 'min:0'],
            'allowed_file_types' => ['required', 'string'],
        ]);

        Setting::put('app_name', $data['app_name']);
        Setting::put('default_currency', $data['default_currency']);
        Setting::put('default_hourly_rate', $data['default_hourly_rate']);
        Setting::put('allowed_file_types', $data['allowed_file_types']);

        return back()->with('success', 'Podešavanja su sačuvana.');
    }
}
