<?php

namespace App\Livewire\Locations;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Country;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class Countries extends Component
{
    use WithPagination;

    // Form properties
    public $countryId;
    public $name;
    public $code; // e.g., USA, CAN
    public $isEditing = false;

    // Table properties
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $listeners = ['countrySaved' => '$refresh', 'countryDeleted' => '$refresh'];

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
                Rule::unique('countries')->ignore($this->countryId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:3',
                Rule::unique('countries')->ignore($this->countryId),
            ],
        ];
    }

    // Custom validation messages
    protected $messages = [
        'name.unique' => 'A country with this name already exists.',
        'code.unique' => 'A country with this code already exists.',
    ];

    // Method to load a country for editing
    public function editCountry($id)
    {
        $country = Country::findOrFail($id);
        $this->countryId = $country->id;
        $this->name = $country->name;
        $this->code = $country->code;
        $this->isEditing = true;
    }

    // Method to save (create or update) a country
    public function saveCountry()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => Str::upper($this->code), // Store code as uppercase
        ];

        if ($this->isEditing) {
            Country::find($this->countryId)->update($data);
            session()->flash('message', 'Country updated successfully!');
        } else {
            Country::create($data);
            session()->flash('message', 'Country created successfully!');
        }

        $this->resetForm();
        $this->dispatch('countrySaved'); // Notify other parts of the app if needed
    }

    // Method to reset the form fields
    public function resetForm()
    {
        $this->resetValidation();
        $this->countryId = null;
        $this->name = '';
        $this->code = '';
        $this->isEditing = false;
    }

    // Method to delete a country
    public function deleteCountry($id)
    {
        try {
            Country::destroy($id);
            session()->flash('message', 'Country deleted successfully!');
            $this->dispatch('countryDeleted'); // Notify other parts
        } catch (\Exception $e) {
            // Check for foreign key constraint violation
            if (Str::contains($e->getMessage(), 'Foreign key constraint fails')) {
                session()->flash('error', 'Cannot delete country: It is currently linked to states or users. Please update those records first.');
            } else {
                session()->flash('error', 'An error occurred while deleting the country: ' . $e->getMessage());
            }
        }
        $this->resetPage(); // Refresh pagination
    }

    public function render()
    {
        $countries = Country::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.locations.countries', [
            'countries' => $countries,
        ]); // Assuming you have a default app layout
    }
}
