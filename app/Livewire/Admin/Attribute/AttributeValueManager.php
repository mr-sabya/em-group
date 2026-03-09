<?php

namespace App\Livewire\Admin\Attribute;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Enums\AttributeDisplayType;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class AttributeValueManager extends Component
{
    use WithPagination;
    use WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    // --- Table Properties ---
    public $search = '';
    public $sortField = 'value';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // --- Form Properties ---
    public $showModal = false;
    public $attributeValueId;
    public $attribute_id;
    public $value;
    public $slug;
    public $metadata = [];
    public $metadataColor;
    public $metadataImage;
    public $currentMetadataImage;

    public $isEditing = false;
    public $availableAttributes = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'value'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    /**
     * Computed property to access current tenant.
     */
    #[Computed]
    public function currentTenant()
    {
        return \App\Models\Tenant::find(session('active_tenant_id'));
    }

    public function mount()
    {
        $this->loadAvailableAttributes();
    }

    public function loadAvailableAttributes()
    {
        // SCOPED: Only get attributes belonging to this tenant
        $this->availableAttributes = Attribute::select('id', 'name', 'display_type')
            ->orderBy('name')
            ->get();
    }

    protected $rules = [
        'value' => 'required|string|max:255',
    ];

    private function getDynamicValidationRules()
    {
        $tenantId = session('active_tenant_id');

        $dynamicRules = [
            'attribute_id' => [
                'required',
                // SCOPED: Ensure parent attribute belongs to this store
                Rule::exists('attributes', 'id')->where('tenant_id', $tenantId)
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                // SCOPED UNIQUE: Allow same slug in different stores
                Rule::unique('attribute_values', 'slug')
                    ->where('tenant_id', $tenantId)
                    ->ignore($this->attributeValueId),
            ],
        ];

        $parentAttribute = Attribute::find($this->attribute_id);
        if ($parentAttribute) {
            switch ($parentAttribute->display_type) {
                case AttributeDisplayType::Color:
                    $dynamicRules['metadataColor'] = ['required', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i'];
                    break;
                case AttributeDisplayType::Image:
                    if ($this->isEditing && !$this->metadataImage && $this->currentMetadataImage) {
                        $dynamicRules['metadataImage'] = 'nullable|image|max:1024';
                    } else {
                        $dynamicRules['metadataImage'] = 'required|image|max:1024';
                    }
                    break;
            }
        }
        return array_merge($this->rules, $dynamicRules);
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

    public function createAttributeValue()
    {
        $this->isEditing = false;
        $this->resetForm();
        $this->openModal();
    }

    public function editAttributeValue($id)
    {
        // SCOPED FIND: Global scope handles security
        $attributeValue = AttributeValue::find($id);

        if (!$attributeValue) {
            session()->flash('error', 'Value not found or access denied.');
            return;
        }

        $this->isEditing = true;
        $this->attributeValueId = $attributeValue->id;
        $this->attribute_id = $attributeValue->attribute_id;
        $this->value = $attributeValue->value;
        $this->slug = $attributeValue->slug;
        $this->metadata = $attributeValue->metadata;

        $parentAttribute = $attributeValue->attribute;
        if ($parentAttribute) {
            switch ($parentAttribute->display_type) {
                case AttributeDisplayType::Color:
                    $this->metadataColor = $attributeValue->metadata['color'] ?? null;
                    break;
                case AttributeDisplayType::Image:
                    $this->currentMetadataImage = $attributeValue->metadata['image'] ?? null;
                    break;
            }
        }
        $this->openModal();
    }

    public function saveAttributeValue()
    {
        $this->validate($this->getDynamicValidationRules());

        $metadata = [];
        $parentAttribute = Attribute::find($this->attribute_id);

        if ($parentAttribute) {
            switch ($parentAttribute->display_type) {
                case AttributeDisplayType::Color:
                    $metadata['color'] = $this->metadataColor;
                    break;
                case AttributeDisplayType::Image:
                    if ($this->metadataImage) {
                        if ($this->currentMetadataImage && Storage::disk('public')->exists($this->currentMetadataImage)) {
                            Storage::disk('public')->delete($this->currentMetadataImage);
                        }
                        $metadata['image'] = $this->metadataImage->store('attribute-values', 'public');
                    } elseif ($this->currentMetadataImage) {
                        $metadata['image'] = $this->currentMetadataImage;
                    }
                    break;
            }
        }

        $data = [
            'tenant_id' => session('active_tenant_id'),
            'attribute_id' => $this->attribute_id,
            'value' => $this->value,
            'slug' => $this->slug,
            'metadata' => $metadata,
        ];

        if ($this->isEditing) {
            AttributeValue::find($this->attributeValueId)->update($data);
            session()->flash('message', 'Value updated successfully!');
        } else {
            AttributeValue::create($data);
            session()->flash('message', 'Value created successfully!');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function deleteAttributeValue($id)
    {
        $attributeValue = AttributeValue::find($id);

        if (!$attributeValue) return;

        if ($attributeValue->products()->count() > 0 || $attributeValue->productVariants()->count() > 0) {
            session()->flash('error', 'Cannot delete value with associated products.');
            return;
        }

        if (isset($attributeValue->metadata['image']) && Storage::disk('public')->exists($attributeValue->metadata['image'])) {
            Storage::disk('public')->delete($attributeValue->metadata['image']);
        }

        $attributeValue->delete();
        session()->flash('message', 'Value deleted successfully!');
        $this->resetPage();
    }

    private function resetForm()
    {
        $this->attributeValueId = null;
        $this->attribute_id = null;
        $this->value = '';
        $this->slug = '';
        $this->metadataColor = null;
        $this->metadataImage = null;
        $this->currentMetadataImage = null;
        $this->isEditing = false;
        $this->resetValidation();
    }

    public function updatedValue($value)
    {
        if (empty($this->slug) || Str::slug($value) === $this->slug) {
            $this->slug = Str::slug($value);
        }
    }

    public function render()
    {
        $attributeValues = AttributeValue::query()
            ->with('attribute')
            ->when($this->search, function ($query) {
                /** 
                 * Grouped search ensures global tenant scope is not bypassed
                 */
                $query->where(function ($q) {
                    $q->where('value', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%')
                        ->orWhereHas('attribute', function (Builder $attrQuery) {
                            $attrQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.attribute.attribute-value-manager', [
            'attributeValues' => $attributeValues,
            'displayTypes' => AttributeDisplayType::labels(),
        ]);
    }
}
