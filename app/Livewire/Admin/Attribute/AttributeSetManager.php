<?php

namespace App\Livewire\Admin\Attribute;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // Added for Computed Property
use App\Models\Attribute;
use App\Models\AttributeSet;
use App\Models\Tenant;
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
    public $selectedAttributes = [];
    public $allAttributes = [];

    public $isEditing = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    /**
     * Computed property for the current tenant.
     */
    #[Computed]
    public function currentTenant()
    {
        return Tenant::find(session('active_tenant_id'));
    }

    public function mount()
    {
        $this->loadAllAttributes();
    }

    public function loadAllAttributes()
    {
        // Global Scope in Attribute model handles the tenant filtering automatically
        $this->allAttributes = Attribute::select('id', 'name')->orderBy('name')->get();
    }

    protected function rules()
    {
        $tenantId = session('active_tenant_id');

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'selectedAttributes' => 'nullable|array',
            'selectedAttributes.*' => [
                'exists:attributes,id',
                // Security: Ensure selected attributes belong to the active tenant
                Rule::exists('attributes', 'id')->where('tenant_id', $tenantId)
            ],
        ];
    }

    public function updatingSearch()
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

    public function createAttributeSet()
    {
        $this->isEditing = false;
        $this->resetForm();
        $this->openModal();
    }

    public function editAttributeSet($id)
    {
        // find() respects Global Scope security
        $attributeSet = AttributeSet::with('attributes')->find($id);

        if (!$attributeSet) {
            session()->flash('error', 'Attribute Set not found or access denied.');
            return;
        }

        $this->isEditing = true;
        $this->attributeSetId = $attributeSet->id;
        $this->name = $attributeSet->name;
        $this->description = $attributeSet->description;
        $this->selectedAttributes = $attributeSet->attributes->pluck('id')->toArray();
        $this->openModal();
    }

    public function saveAttributeSet()
    {
        $this->validate();

        $tenantId = session('active_tenant_id');

        $data = [
            'tenant_id' => $tenantId, // Explicitly ensure tenant_id is set
            'name' => $this->name,
            'description' => $this->description,
        ];

        if ($this->isEditing) {
            $attributeSet = AttributeSet::find($this->attributeSetId);
            $attributeSet->update($data);
        } else {
            $attributeSet = AttributeSet::create($data);
        }

        // FIXED: Sync with tenant_id for the pivot table if pivot has tenant_id column
        // If your pivot table 'attribute_attribute_set' has a tenant_id, use syncWithPivotValues
        $attributeSet->attributes()->syncWithPivotValues($this->selectedAttributes, [
            'tenant_id' => $tenantId
        ]);

        session()->flash('message', 'Attribute Set saved successfully!');
        $this->closeModal();
        $this->resetPage();
    }

    public function deleteAttributeSet($id)
    {
        $attributeSet = AttributeSet::find($id);

        if (!$attributeSet) return;

        if ($attributeSet->products()->count() > 0) {
            session()->flash('error', 'Cannot delete attribute set with associated products.');
            return;
        }

        $attributeSet->attributes()->detach();
        $attributeSet->delete();
        session()->flash('message', 'Attribute Set deleted successfully!');
        $this->resetPage();
    }

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
                // Grouped OR query to maintain tenant scope security
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.attribute.attribute-set-manager', [
            'attributeSets' => $attributeSets,
        ]);
    }
}
