<?php

namespace App\Livewire\Projects;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project; // Import the Project model
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ManageProjects extends Component
{
    use WithPagination;

    // Form properties
    public $projectId;
    public $name;
    public $description;
    public $target_amount;
    public $status = 'pending'; // Default status for new projects
    public $isEditing = false;

    // Table properties
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Define available statuses for a project
    public $availableStatuses = [
        'pending' => 'Pending',
        'active' => 'Active',
        'funded' => 'Funded',
        'closed' => 'Closed',
        'cancelled' => 'Cancelled',
    ];

    protected $listeners = ['projectSaved' => '$refresh', 'projectDeleted' => '$refresh'];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    // Reset pagination when search or perPage changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Sort table
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // Validation rules for the form
    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects')->ignore($this->projectId),
            ],
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:0.01',
            'status' => ['required', 'string', Rule::in(array_keys($this->availableStatuses))],
        ];
    }

    // Custom validation messages
    protected $messages = [
        'name.unique' => 'A project with this name already exists.',
        'target_amount.required' => 'The target amount is required.',
        'target_amount.numeric' => 'The target amount must be a number.',
        'target_amount.min' => 'The target amount must be at least 0.01.',
        'status.in' => 'The selected status is invalid.',
    ];

    // Method to load a project for editing
    public function editProject($id)
    {
        $project = Project::findOrFail($id);
        $this->projectId = $project->id;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->target_amount = $project->target_amount;
        $this->status = $project->status;
        $this->isEditing = true;
    }

    // Method to save (create or update) a project
    public function saveProject()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'target_amount' => $this->target_amount,
            'status' => $this->status,
        ];

        if ($this->isEditing) {
            Project::find($this->projectId)->update($data);
            session()->flash('message', 'Project updated successfully!');
        } else {
            Project::create($data);
            session()->flash('message', 'Project created successfully!');
        }

        $this->resetForm();
        $this->dispatch('projectSaved'); // Notify other parts of the app if needed
    }

    // Method to reset the form fields
    public function resetForm()
    {
        $this->resetValidation();
        $this->projectId = null;
        $this->name = '';
        $this->description = '';
        $this->target_amount = null; // Reset to null or 0
        $this->status = 'pending'; // Reset to default status
        $this->isEditing = false;
    }

    // Method to delete a project
    public function deleteProject($id)
    {
        try {
            Project::destroy($id); // Assuming cascade delete is handled if investments are related
            session()->flash('message', 'Project deleted successfully!');
            $this->dispatch('projectDeleted'); // Notify other parts
        } catch (\Exception $e) {
            // Check for foreign key constraint violation if investments are linked
            if (Str::contains($e->getMessage(), 'Foreign key constraint fails')) {
                session()->flash('error', 'Cannot delete project: It has associated investments. Please delete investments first.');
            } else {
                session()->flash('error', 'An error occurred while deleting the project: ' . $e->getMessage());
            }
        }
        $this->resetPage(); // Refresh pagination
    }

    public function render()
    {
        $projects = Project::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%'); // Allow searching by status text
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.projects.manage-projects', [
            'projects' => $projects,
        ]);
    }
}
