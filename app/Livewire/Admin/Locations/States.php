<?php

namespace App\Livewire\Locations;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\State;
use App\Models\Country; // Need Country model for dropdown
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Collection; // For type-hinting

class States extends Component
{
    use WithPagination;

    // Form properties
    public $stateId;
    public $name;
    public $code; // e.g., CA, NY, ON
    public $country_id; // Foreign key for country
    public $isEditing = false;

    // Table properties
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Data for dropdowns
    public Collection $countries;

    protected $listeners = ['stateSaved' => '$refresh', 'stateDeleted' => '$refresh'];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->countries = Country::orderBy('name')->get(); // Load all countries for dropdown
    }

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

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('states')->where(fn($query) => $query->where('country_id', $this->country_id))->ignore($this->stateId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:5',
                // Optional: Make code unique per country if desired
                // Rule::unique('states')->where(fn ($query) => $query->where('country_id', $this->country_id))->ignore($this->stateId),
            ],
            'country_id' => 'required|exists:countries,id',
        ];
    }

    protected $messages = [
        'name.unique' => 'A state with this name already exists in the selected country.',
        'country_id.required' => 'Please select a country.',
        'country_id.exists' => 'The selected country is invalid.',
    ];

    public function editState($id)
    {
        $state = State::findOrFail($id);
        $this->stateId = $state->id;
        $this->name = $state->name;
        $this->code = $state->code;
        $this->country_id = $state->country_id;
        $this->isEditing = true;
    }

    public function saveState()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => Str::upper($this->code),
            'country_id' => $this->country_id,
        ];

        if ($this->isEditing) {
            State::find($this->stateId)->update($data);
            session()->flash('message', 'State updated successfully!');
        } else {
            State::create($data);
            session()->flash('message', 'State created successfully!');
        }

        $this->resetForm();
        $this->dispatch('stateSaved');
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->stateId = null;
        $this->name = '';
        $this->code = '';
        $this->country_id = null; // Reset country selection too
        $this->isEditing = false;
    }

    public function deleteState($id)
    {
        try {
            State::destroy($id);
            session()->flash('message', 'State deleted successfully!');
            $this->dispatch('stateDeleted');
        } catch (\Exception $e) {
            if (Str::contains($e->getMessage(), 'Foreign key constraint fails')) {
                session()->flash('error', 'Cannot delete state: It is currently linked to cities or users. Please update those records first.');
            } else {
                session()->flash('error', 'An error occurred while deleting the state: ' . $e->getMessage());
            }
        }
        $this->resetPage();
    }

    public function render()
    {
        $states = State::query()
            ->with('country') // Eager load country name for display
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('country', fn($q) => $q->where('name', 'like', '%' . $this->search . '%')); // Search by country name
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.locations.states', [
            'states' => $states,
        ]);
    }
}
