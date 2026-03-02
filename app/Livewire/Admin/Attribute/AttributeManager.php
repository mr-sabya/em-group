<?php

namespace App\Livewire\Attribute;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attribute;
use App\Enums\AttributeDisplayType;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttributeManager extends Component
{
    use WithPagination;

    // --- Table Properties ---
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap'; // Use Bootstrap theme for pagination

    // --- Form Properties ---
    public $showModal = false; // Controls modal visibility
    public $attributeId;       // Null for create, ID for edit
    public $name;
    public $slug;
    public $display_type;
    public $is_filterable = false;
    public $is_active = true;
    public $displayTypes;      // For dropdown options

    public $isEditing = false; // Flag to determine if we're editing or creating

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    // --- Lifecycle Hooks ---
    public function mount()
    {
        $this->displayTypes = AttributeDisplayType::labels();
        $this->display_type = AttributeDisplayType::Text->value; // Default for new attributes
    }

    // Define base rules
    protected $rules = [
        'name' => 'required|string|max:255',
        'display_type' => ['required', 'string'], // Will be validated against enum values dynamically
        'is_filterable' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Custom validation messages
    protected $messages = [
        'slug.unique' => 'This slug is already taken. Please try another one.',
        'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
        'display_type.in' => 'The selected display type is invalid.',
    ];

    // Dynamically get validation rules, especially for unique slug
    private function getDynamicValidationRules()
    {
        return array_merge($this->rules, [
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('attributes', 'slug')->ignore($this->attributeId),
            ],
            'display_type' => ['required', Rule::in(array_keys(AttributeDisplayType::labels()))],
        ]);
    }

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
        $this->resetForm(); // Reset form fields when modal closes
    }

    public function createAttribute()
    {
        $this->isEditing = false;
        $this->resetForm(); // Clear all fields for a new entry
        $this->openModal();
    }

    public function editAttribute(Attribute $attribute)
    {
        $this->isEditing = true;
        $this->attributeId = $attribute->id;
        $this->name = $attribute->name;
        $this->slug = $attribute->slug;
        $this->display_type = $attribute->display_type->value;
        $this->is_filterable = $attribute->is_filterable;
        $this->is_active = $attribute->is_active;
        $this->openModal();
    }

    public function saveAttribute()
    {
        $this->validate($this->getDynamicValidationRules());

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'display_type' => $this->display_type,
            'is_filterable' => $this->is_filterable,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $attribute = Attribute::find($this->attributeId);
            $attribute->update($data);
            session()->flash('message', 'Attribute updated successfully!');
        } else {
            Attribute::create($data);
            session()->flash('message', 'Attribute created successfully!');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function deleteAttribute($attributeId)
    {
        $attribute = Attribute::find($attributeId);

        if (!$attribute) {
            session()->flash('error', 'Attribute not found.');
            return;
        }

        if ($attribute->values()->count() > 0) {
            session()->flash('error', 'Cannot delete attribute with associated values. Please delete its values first.');
            return;
        }

        if ($attribute->attributeSets()->count() > 0) {
            session()->flash('error', 'Cannot delete attribute assigned to attribute sets. Please remove it from sets first.');
            return;
        }

        $attribute->delete();
        session()->flash('message', 'Attribute deleted successfully!');
        $this->resetPage();
    }

    // --- Utility Methods ---

    // Resets all form-related properties
    private function resetForm()
    {
        $this->attributeId = null;
        $this->name = '';
        $this->slug = '';
        $this->display_type = AttributeDisplayType::Text->value;
        $this->is_filterable = false;
        $this->is_active = true;
        $this->isEditing = false;
        $this->resetValidation();
    }

    // Auto-generate slug when name changes
    public function updatedName($value)
    {
        if (empty($this->slug) || Str::slug($value) === $this->slug) {
            $this->slug = Str::slug($value);
        }
    }

    public function render()
    {
        $attributes = Attribute::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.attribute.attribute-manager', [
            'attributes' => $attributes,
        ]);
    }
}
