<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Client::exists()) {
            return;
        }

        $clientsData = [
            ['name' => 'Caffe Bar Aroma', 'city' => 'Sarajevo', 'country' => 'BiH', 'rate' => 40, 'currency' => 'KM'],
            ['name' => 'Studio Lumen', 'city' => 'Mostar', 'country' => 'BiH', 'rate' => 50, 'currency' => 'EUR'],
            ['name' => 'TechNova d.o.o.', 'city' => 'Zürich', 'country' => 'Švicarska', 'rate' => 90, 'currency' => 'CHF'],
        ];

        foreach ($clientsData as $cd) {
            $client = Client::create([
                'name' => $cd['name'],
                'contact_person' => 'Kontakt osoba',
                'email' => 'info@'.\Illuminate\Support\Str::slug($cd['name']).'.ba',
                'phone' => '+387 61 000 000',
                'city' => $cd['city'],
                'country' => $cd['country'],
                'status' => 'aktivan',
                'default_hourly_rate' => $cd['rate'],
                'currency' => $cd['currency'],
            ]);

            foreach (['Web stranica', 'SEO', 'Održavanje'] as $i => $pname) {
                $project = Project::create([
                    'client_id' => $client->id,
                    'name' => $pname,
                    'description' => 'Demo projekat: '.$pname,
                    'status' => ['aktivno', 'planirano', 'zavrseno'][$i % 3],
                    'start_date' => Carbon::now()->subDays(40),
                    'billing_type' => 'po_satu',
                    'currency' => $cd['currency'],
                ]);

                foreach (range(1, 3) as $t) {
                    $statuses = ['u_toku', 'zavrseno', 'ceka_klijenta', 'za_naplatu', 'novo'];
                    $task = Task::create([
                        'client_id' => $client->id,
                        'project_id' => $project->id,
                        'title' => $pname.' - zadatak '.$t,
                        'description' => 'Demo opis posla.',
                        'status' => $statuses[array_rand($statuses)],
                        'priority' => ['nizak', 'normalan', 'hitno'][array_rand([0, 1, 2])],
                        'task_date' => Carbon::now()->subDays(rand(1, 30)),
                        'due_date' => Carbon::now()->addDays(rand(1, 20)),
                        'billing_type' => 'po_satu',
                        'hourly_rate' => $cd['rate'],
                        'is_billable' => true,
                        'payment_status' => 'za_naplatu',
                    ]);

                    TimeEntry::create([
                        'client_id' => $client->id,
                        'project_id' => $project->id,
                        'task_id' => $task->id,
                        'work_date' => Carbon::now()->subDays(rand(1, 20)),
                        'description' => 'Rad na zadatku',
                        'hours' => rand(1, 4),
                        'minutes' => [0, 15, 30, 45][array_rand([0, 1, 2, 3])],
                        'hourly_rate' => $cd['rate'],
                        'is_billable' => true,
                    ]);

                    $task->recalcTotalPrice();
                }
            }
        }
    }
}
