<?php

namespace App\Livewire\Attribute;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attribute;
use App\Models\AttributeSet;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class AttributeSetManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // --- Table Properties ---
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // --- Form Properties ---
    public $showModal = false;
    public $attributeSetId;
    public $name;
    public $description;
    public $selectedAttributes = []; // For linking attributes to a set
    public $allAttributes = []; // All available attributes for multi-select

    public $isEditing = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    // --- Lifecycle Hooks ---
    public function mount()
    {
        $this->loadAllAttributes();
    }

    public function loadAllAttributes()
    {
        $this->allAttributes = Attribute::select('id', 'name')->orderBy('name')->get();
    }

    // Define base rules
    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'selectedAttributes' => 'nullable|array',
        'selectedAttributes.*' => 'exists:attributes,id', // Ensure each ID exists
    ];

    // --- Table Methods ---
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

    // --- Form Methods ---
    public function openModal()
    {
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function createAttributeSet()
    {
        $this->isEditing = false;
        $this->resetForm();
        $this->openModal();
    }

    public function editAttributeSet(AttributeSet $attributeSet)
    {
        $this->isEditing = true;
        $this->attributeSetId = $attributeSet->id;
        $this->name = $attributeSet->name;
        $this->description = $attributeSet->description;
        $this->selectedAttributes = $attributeSet->attributes->pluck('id')->toArray(); // Populate selected attributes
        $this->openModal();
    }

    public function saveAttributeSet()
    {
        $this->validate($this->rules); // All rules are static for attribute set

        $data = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        if ($this->isEditing) {
            $attributeSet = AttributeSet::find($this->attributeSetId);
            $attributeSet->update($data);
            $attributeSet->attributes()->sync($this->selectedAttributes); // Sync relationships
            session()->flash('message', 'Attribute Set updated successfully!');
        } else {
            $attributeSet = AttributeSet::create($data);
            $attributeSet->attributes()->sync($this->selectedAttributes); // Sync relationships
            session()->flash('message', 'Attribute Set created successfully!');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function deleteAttributeSet($attributeSetId)
    {
        $attributeSet = AttributeSet::find($attributeSetId);

        if (!$attributeSet) {
            session()->flash('error', 'Attribute Set not found.');
            return;
        }

        // Check if any products are using this attribute set
        if ($attributeSet->products()->count() > 0) {
            session()->flash('error', 'Cannot delete attribute set with associated products.');
            return;
        }

        // Detach all attributes from the set before deleting the set
        $attributeSet->attributes()->detach();
        $attributeSet->delete();
        session()->flash('message', 'Attribute Set deleted successfully!');
        $this->resetPage();
    }

    // --- Utility Methods ---

    private function resetForm()
    {
        $this->attributeSetId = null;
        $this->name = '';
        $this->description = '';
        $this->selectedAttributes = [];
        $this->isEditing = false;
        $this->resetValidation();
    }

    public function render()
    {
        $attributeSets = AttributeSet::query()
            ->with('attributes')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.attribute.attribute-set-manager', [
            'attributeSets' => $attributeSets,
        ]);
    }
}