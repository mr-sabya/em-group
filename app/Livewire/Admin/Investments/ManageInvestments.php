<?php

namespace App\Livewire\Investments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Investment;
use App\Models\InvestorProfile;
use App\Models\Project; // Assuming Project model exists
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ManageInvestments extends Component
{
    use WithPagination;

    // --- List/Table State ---
    public $search = '';
    public $sortField = 'investment_date';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search',
        'sortField',
        'sortDirection',
        'page',
    ];

    // --- Form State ---
    public $investmentId;
    public $investor_profile_id;
    public $project_id;
    public $amount;
    public $currency = 'USD'; // Default currency
    public $investment_date;
    public $status = 'pending'; // Default status
    public $return_on_investment;
    public $notes;

    public $isEditing = false;
    public $showForm = false; // To toggle create/edit form visibility

    // Dropdown data
    public $investorProfiles = [];
    public $projects = [];

    // --- Lifecycle Hooks ---
    public function mount()
    {
        $this->loadDropdownData();
    }

    public function loadDropdownData()
    {
        $this->investorProfiles = InvestorProfile::with('user')->get()->mapWithKeys(function ($profile) {
            return [$profile->id => $profile->user->name . ' (' . ($profile->company_name ?? 'N/A') . ')'];
        })->toArray();
        $this->projects = Project::pluck('name', 'id')->toArray(); // Assuming Project has a 'title' field
    }

    // --- Table Actions ---
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // --- Form Actions ---
    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showForm = true;
    }

    public function edit($id)
    {
        $investment = Investment::find($id);

        if (!$investment) {
            session()->flash('error', 'Investment not found.');
            return;
        }

        $this->investmentId = $investment->id;
        $this->investor_profile_id = $investment->investor_profile_id;
        $this->project_id = $investment->project_id;
        $this->amount = $investment->amount;
        $this->currency = $investment->currency;
        $this->investment_date = $investment->investment_date ? $investment->investment_date->format('Y-m-d') : null;
        $this->status = $investment->status;
        $this->return_on_investment = $investment->return_on_investment;
        $this->notes = $investment->notes;

        $this->isEditing = true;
        $this->showForm = true;
    }

    protected function rules()
    {
        return [
            'investor_profile_id' => ['required', 'exists:investor_profiles,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'max:10'],
            'investment_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(['pending', 'committed', 'funded', 'returned', 'failed'])],
            'return_on_investment' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'investor_profile_id' => $this->investor_profile_id,
            'project_id' => $this->project_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'investment_date' => $this->investment_date,
            'status' => $this->status,
            'return_on_investment' => $this->return_on_investment,
            'notes' => $this->notes,
        ];

        if ($this->isEditing) {
            Investment::find($this->investmentId)->update($data);
            session()->flash('message', 'Investment updated successfully.');
        } else {
            Investment::create($data);
            session()->flash('message', 'Investment created successfully.');
        }

        $this->resetForm();
        $this->showForm = false;
        $this->dispatch('message', 'Investment saved successfully!');
    }

    public function delete($id)
    {
        $investment = Investment::find($id);

        if (!$investment) {
            session()->flash('error', 'Investment not found.');
            return;
        }

        $investment->delete();
        session()->flash('message', 'Investment deleted successfully.');
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->investmentId = null;
        $this->investor_profile_id = null;
        $this->project_id = null;
        $this->amount = null;
        $this->currency = 'USD'; // Or your default currency
        $this->investment_date = now()->toDateString(); // Or desired default
        $this->status = 'pending';
        $this->return_on_investment = null;
        $this->notes = null;

        // Clear validation errors
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function render()
    {
        $investments = Investment::query()
            ->with(['investorProfile.user', 'project'])
            ->when($this->search, function ($query) {
                $query->whereHas('investorProfile.user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                    ->orWhereHas('project', function ($q) {
                        $q->where('title', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('amount', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.investments.manage-investments', [
            'investments' => $investments,
        ]);
    }
}
