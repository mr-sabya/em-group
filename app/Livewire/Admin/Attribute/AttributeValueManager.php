<?php

namespace App\Livewire\Attribute;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
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
    public $attribute_id; // The parent attribute for this value
    public $value;
    public $slug;
    public $metadata = [];
    public $metadataColor; // For color type attributes
    public $metadataImage; // For image type attributes (temporary upload)
    public $currentMetadataImage; // Path to existing image

    public $isEditing = false;
    public $availableAttributes = []; // For selecting parent attribute

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'value'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    // --- Lifecycle Hooks ---
    public function mount()
    {
        $this->loadAvailableAttributes();
    }

    public function loadAvailableAttributes()
    {
        $this->availableAttributes = Attribute::select('id', 'name', 'display_type')->orderBy('name')->get();
    }

    // Define base rules
    protected $rules = [
        'attribute_id' => 'required|exists:attributes,id',
        'value' => 'required|string|max:255',
        // Slug and metadata rules will be dynamic
    ];

    // Custom validation messages
    protected $messages = [
        'slug.unique' => 'This slug is already taken. Please try another one.',
        'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
        'metadataColor.regex' => 'Color must be a valid hex code (e.g., #RRGGBB or #RGB).',
        'metadataImage.required' => 'An image is required for this attribute value.',
        'metadataImage.image' => 'The metadata must be an image.',
        'metadataImage.max' => 'The metadata image may not be greater than 1MB.',
    ];

    // Dynamically get validation rules, especially for unique slug and metadata
    private function getDynamicValidationRules()
    {
        $dynamicRules = [
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('attribute_values', 'slug')->ignore($this->attributeValueId),
            ],
        ];

        // Add specific validation for metadata based on parent attribute's display type
        $parentAttribute = Attribute::find($this->attribute_id);
        if ($parentAttribute) {
            switch ($parentAttribute->display_type) {
                case AttributeDisplayType::Color:
                    $dynamicRules['metadataColor'] = ['required', 'string', 'max:7', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i'];
                    break;
                case AttributeDisplayType::Image:
                    // If editing and no new image, or creating and an image is present
                    if ($this->isEditing && !$this->metadataImage && $this->currentMetadataImage) {
                        // No new file required, keep existing
                        $dynamicRules['metadataImage'] = 'nullable|image|max:1024'; // Allow null if not uploading new
                    } else {
                        $dynamicRules['metadataImage'] = 'required|image|max:1024'; // Required for new entries of image type, or if a new image is provided
                    }
                    break;
                    // Add more cases for other display types if needed for specific metadata validation
            }
        }
        return array_merge($this->rules, $dynamicRules);
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
        $this->resetForm();
    }

    public function createAttributeValue()
    {
        $this->isEditing = false;
        $this->resetForm();
        $this->openModal();
    }

    public function editAttributeValue(AttributeValue $attributeValue)
    {
        $this->isEditing = true;
        $this->attributeValueId = $attributeValue->id;
        $this->attribute_id = $attributeValue->attribute_id;
        $this->value = $attributeValue->value;
        $this->slug = $attributeValue->slug;
        $this->metadata = $attributeValue->metadata;

        // Populate metadata specific fields if they exist
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
                    // Handle image upload
                    if ($this->metadataImage) {
                        // Delete old image if it exists and a new one is uploaded
                        if ($this->currentMetadataImage && Storage::disk('public')->exists($this->currentMetadataImage)) {
                            Storage::disk('public')->delete($this->currentMetadataImage);
                        }
                        $metadata['image'] = $this->metadataImage->store('attribute-values', 'public');
                    } elseif ($this->currentMetadataImage) {
                        // If no new image but there's a current one, keep it
                        $metadata['image'] = $this->currentMetadataImage;
                    } else {
                        $metadata['image'] = null;
                    }
                    break;
                    // Add other metadata processing based on display type
            }
        }

        $data = [
            'attribute_id' => $this->attribute_id,
            'value' => $this->value,
            'slug' => $this->slug,
            'metadata' => $metadata,
        ];

        if ($this->isEditing) {
            $attributeValue = AttributeValue::find($this->attributeValueId);
            $attributeValue->update($data);
            session()->flash('message', 'Attribute Value updated successfully!');
        } else {
            AttributeValue::create($data);
            session()->flash('message', 'Attribute Value created successfully!');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function deleteAttributeValue($attributeValueId)
    {
        $attributeValue = AttributeValue::find($attributeValueId);

        if (!$attributeValue) {
            session()->flash('error', 'Attribute Value not found.');
            return;
        }

        // Check for associations (e.g., products, variants) before deleting
        if ($attributeValue->products()->count() > 0 || $attributeValue->productVariants()->count() > 0) {
            session()->flash('error', 'Cannot delete attribute value with associated products or variants.');
            return;
        }

        // Delete metadata image if it exists
        if (isset($attributeValue->metadata['image']) && Storage::disk('public')->exists($attributeValue->metadata['image'])) {
            Storage::disk('public')->delete($attributeValue->metadata['image']);
        }

        $attributeValue->delete();
        session()->flash('message', 'Attribute Value deleted successfully!');
        $this->resetPage();
    }

    // --- Utility Methods ---

    private function resetForm()
    {
        $this->attributeValueId = null;
        $this->attribute_id = null;
        $this->value = '';
        $this->slug = '';
        $this->metadata = [];
        $this->metadataColor = null;
        $this->metadataImage = null; // Clear temporary upload
        $this->currentMetadataImage = null; // Clear existing image path
        $this->isEditing = false;
        $this->resetValidation();
    }

    // Auto-generate attribute value slug
    public function updatedValue($value)
    {
        if (empty($this->slug) || Str::slug($value) === $this->slug) {
            $this->slug = Str::slug($value);
        }
    }

    // Clear temporary image when new one is selected
    public function updatedMetadataImage()
    {
        $this->resetValidation('metadataImage');
    }

    public function render()
    {
        $attributeValues = AttributeValue::query()
            ->with('attribute')
            ->when($this->search, function ($query) {
                $query->where('value', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%')
                    ->orWhereHas('attribute', function (Builder $q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.attribute.attribute-value-manager', [
            'attributeValues' => $attributeValues,
            'displayTypes' => AttributeDisplayType::labels(), // Pass to view for dynamic fields
        ]);
    }
}
