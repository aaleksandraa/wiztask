<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Support\Options;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        $this->project = $project->load('client');
    }

    public function render()
    {
        $tasks = $this->project->tasks()->with('client')->latest()->get();

        return view('livewire.projects.show', [
            'tasks' => $tasks,
            'totalMinutes' => $this->project->totalMinutes(),
            'totalValue' => $this->project->totalValue(),
            'projectStatuses' => Options::PROJECT_STATUSES,
            'projectBilling' => Options::PROJECT_BILLING_TYPES,
            'taskStatuses' => Options::TASK_STATUSES,
        ]);
    }
}
