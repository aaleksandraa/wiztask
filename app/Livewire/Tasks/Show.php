<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\TimeEntry;
use App\Support\Dates;
use App\Support\Options;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Task $task;

    public bool $showTimeModal = false;
    public ?int $editingTimeId = null;
    public array $timeForm = [];

    public function mount(Task $task): void
    {
        $this->task = $task->load('client', 'project');
        $this->resetTimeForm();
    }

    protected function resetTimeForm(): void
    {
        $this->timeForm = [
            'work_date' => Dates::today(),
            'description' => '',
            'hours' => 0,
            'minutes' => 0,
            'hourly_rate' => $this->task->hourly_rate ?: ($this->task->client->default_hourly_rate ?? 0),
            'is_billable' => true,
        ];
        $this->editingTimeId = null;
    }

    protected function timeRules(): array
    {
        return [
            'timeForm.work_date' => Dates::rule(required: true),
            'timeForm.description' => ['nullable', 'string'],
            'timeForm.hours' => ['required', 'integer', 'min:0', 'max:999'],
            'timeForm.minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'timeForm.hourly_rate' => ['required', 'numeric', 'min:0'],
            'timeForm.is_billable' => ['boolean'],
        ];
    }

    public function addTime(): void
    {
        $this->resetTimeForm();
        $this->resetValidation();
        $this->showTimeModal = true;
    }

    public function editTime(int $id): void
    {
        $entry = $this->task->timeEntries()->findOrFail($id);
        $this->editingTimeId = $entry->id;
        $this->timeForm = [
            'work_date' => Dates::toInput($entry->work_date),
            'description' => $entry->description,
            'hours' => $entry->hours,
            'minutes' => $entry->minutes,
            'hourly_rate' => $entry->hourly_rate,
            'is_billable' => $entry->is_billable,
        ];
        $this->resetValidation();
        $this->showTimeModal = true;
    }

    public function saveTime(): void
    {
        $data = $this->validate($this->timeRules())['timeForm'];
        $data = Dates::fillForSave($data, ['work_date']);
        $data['client_id'] = $this->task->client_id;
        $data['project_id'] = $this->task->project_id;
        $data['task_id'] = $this->task->id;

        if ($this->editingTimeId) {
            $this->task->timeEntries()->findOrFail($this->editingTimeId)->update($data);
            session()->flash('success', 'Unos vremena je ažuriran.');
        } else {
            TimeEntry::create($data);
            session()->flash('success', 'Vrijeme je dodano.');
        }

        $this->task->refresh();
        $this->showTimeModal = false;
        $this->resetTimeForm();
    }

    public function deleteTime(int $id): void
    {
        $this->task->timeEntries()->findOrFail($id)->delete();
        $this->task->refresh();
        session()->flash('success', 'Unos vremena je obrisan.');
    }

    public function updateStatus(string $status): void
    {
        if (array_key_exists($status, Options::TASK_STATUSES)) {
            $this->task->update(['status' => $status]);
            session()->flash('success', 'Status je promijenjen.');
        }
    }

    public function updatePaymentStatus(string $status): void
    {
        if (array_key_exists($status, Options::PAYMENT_STATUSES)) {
            $this->task->update(['payment_status' => $status]);
            session()->flash('success', 'Status plaćanja je promijenjen.');
        }
    }

    public function render()
    {
        $this->task->refresh();
        $timeEntries = $this->task->timeEntries()->latest('work_date')->get();

        return view('livewire.tasks.show', [
            'timeEntries' => $timeEntries,
            'totalMinutes' => $this->task->totalMinutes(),
            'statuses' => Options::TASK_STATUSES,
            'priorities' => Options::TASK_PRIORITIES,
            'billingTypes' => Options::TASK_BILLING_TYPES,
            'paymentStatuses' => Options::PAYMENT_STATUSES,
        ]);
    }
}
