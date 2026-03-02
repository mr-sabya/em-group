<?php

namespace App\Livewire\Locations;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\City;
use App\Models\Country; // Need Country model for dropdown
use App\Models\State;   // Need State model for dropdown
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class Cities extends Component
{
    use WithPagination;

    // Form properties
    public $cityId;
    public $name;
    public $country_id;
    public $state_id;
    public $isEditing = false;

    // Table properties
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Data for dropdowns
    public Collection $countries;
    public Collection $states; // States filtered by selected country

    protected $listeners = ['citySaved' => '$refresh', 'cityDeleted' => '$refresh'];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->countries = Country::orderBy('name')->get();
        $this->states = collect(); // Initialize as empty collection
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

    // Reactive property for country selection to load states
    public function updatedCountryId($value)
    {
        $this->state_id = null; // Reset state when country changes
        $this->states = $value ? State::where('country_id', $value)->orderBy('name')->get() : collect();
    }


    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique rule for city name within its state OR within its country if no state
                Rule::unique('cities')->where(function ($query) {
                    $query->where('country_id', $this->country_id);
                    if ($this->state_id) {
                        $query->where('state_id', $this->state_id);
                    } else {
                        $query->whereNull('state_id'); // City directly under country
                    }
                })->ignore($this->cityId),
            ],
            'country_id' => 'required|exists:countries,id',
            'state_id' => [
                'nullable',
                Rule::exists('states', 'id')->where(function ($query) {
                    $query->where('country_id', $this->country_id); // Ensure state belongs to selected country
                }),
            ],
        ];
    }

    protected $messages = [
        'name.unique' => 'A city with this name already exists in the selected state/country.',
        'country_id.required' => 'Please select a country.',
        'country_id.exists' => 'The selected country is invalid.',
        'state_id.exists' => 'The selected state is invalid for the chosen country.',
    ];

    public function editCity($id)
    {
        $city = City::findOrFail($id);
        $this->cityId = $city->id;
        $this->name = $city->name;
        $this->country_id = $city->country_id;
        $this->state_id = $city->state_id; // Load existing state_id

        // Re-populate states dropdown based on loaded country_id
        $this->states = $this->country_id ? State::where('country_id', $this->country_id)->orderBy('name')->get() : collect();

        $this->isEditing = true;
    }

    public function saveCity()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id, // Can be null
        ];

        if ($this->isEditing) {
            City::find($this->cityId)->update($data);
            session()->flash('message', 'City updated successfully!');
        } else {
            City::create($data);
            session()->flash('message', 'City created successfully!');
        }

        $this->resetForm();
        $this->dispatch('citySaved');
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->cityId = null;
        $this->name = '';
        $this->country_id = null;
        $this->state_id = null;
        $this->states = collect(); // Clear states dropdown
        $this->isEditing = false;
    }

    public function deleteCity($id)
    {
        try {
            City::destroy($id);
            session()->flash('message', 'City deleted successfully!');
            $this->dispatch('cityDeleted');
        } catch (\Exception $e) {
            if (Str::contains($e->getMessage(), 'Foreign key constraint fails')) {
                session()->flash('error', 'Cannot delete city: It is currently linked to users. Please update those records first.');
            } else {
                session()->flash('error', 'An error occurred while deleting the city: ' . $e->getMessage());
            }
        }
        $this->resetPage();
    }

    public function render()
    {
        $cities = City::query()
            ->with(['country', 'state']) // Eager load relationships
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('country', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('state', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.locations.cities', [
            'cities' => $cities,
        ]);
    }
}
