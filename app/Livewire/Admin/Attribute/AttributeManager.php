<?php

namespace App\Livewire\Admin\Attribute;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // Added for Computed Property
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
    protected $paginationTheme = 'bootstrap';

    // --- Form Properties ---
    public $showModal = false;
    public $attributeId;
    public $name;
    public $slug;
    public $display_type;
    public $is_filterable = false;
    public $is_active = true;
    public $displayTypes;

    public $isEditing = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    /**
     * Computed Property to get the current tenant info.
     */
    #[Computed]
    public function currentTenant()
    {
        return \App\Models\Tenant::find(session('active_tenant_id'));
    }

    public function mount()
    {
        $this->displayTypes = AttributeDisplayType::labels();
        $this->display_type = AttributeDisplayType::Text->value;
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'is_filterable' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'slug.unique' => 'This slug is already taken in this store.',
        'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
    ];

    private function getDynamicValidationRules()
    {
        return array_merge($this->rules, [
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                // SCOPED UNIQUE: Unique only within the active tenant
                Rule::unique('attributes', 'slug')
                    ->where('tenant_id', session('active_tenant_id'))
                    ->ignore($this->attributeId),
            ],
            'display_type' => ['required', Rule::in(array_keys(AttributeDisplayType::labels()))],
        ]);
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

    public function createAttribute()
    {
        $this->isEditing = false;
        $this->resetForm();
        $this->openModal();
    }

    public function editAttribute($id)
    {
        // Global Scope in Attribute model ensures we only find if it belongs to current tenant
        $attribute = Attribute::find($id);

        if (!$attribute) {
            session()->flash('error', 'Attribute not found or access denied.');
            return;
        }

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
            'tenant_id' => session('active_tenant_id'), // Explicitly setting tenant_id
            'name' => $this->name,
            'slug' => $this->slug,
            'display_type' => $this->display_type,
            'is_filterable' => $this->is_filterable,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            Attribute::find($this->attributeId)->update($data);
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
            session()->flash('error', 'Cannot delete attribute with associated values.');
            return;
        }

        $attribute->delete();
        session()->flash('message', 'Attribute deleted successfully!');
        $this->resetPage();
    }

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
                /** 
                 * Grouped OR search terms.
                 * Prevents search from bypassing the Global Tenant Scope.
                 */
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.attribute.attribute-manager', [
            'attributes' => $attributes,
        ]);
    }
}
