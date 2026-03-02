<?php

namespace App\Livewire\Brand;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BrandManager extends Component
{
    use WithPagination;
    use WithFileUploads; // For handling logo uploads

    // --- Table Properties ---
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // --- Form Properties ---
    public $showModal = false; // Controls modal visibility
    public $brandId;         // Null for create, ID for edit
    public $name;
    public $slug;
    public $description;
    public $logo;            // Temporary file for upload
    public $currentLogo;     // Path to existing logo for display/deletion
    public $is_active = true;

    public $isEditing = false; // Flag to determine if we're editing or creating

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    // Real-time validation for specific fields
    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|alpha_dash',
        'description' => 'nullable|string|max:1000',
        'logo' => 'nullable|image|max:1024', // Max 1MB
        'is_active' => 'boolean',
    ];

    // Dynamic slug validation rule for uniqueness
    protected function getValidationRules()
    {
        return array_merge($this->rules, [
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('brands')->ignore($this->brandId),
            ],
        ]);
    }

    // Custom validation messages
    protected $messages = [
        'slug.unique' => 'This slug is already taken. Please try another one.',
        'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
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
        $this->resetForm(); // Reset form fields when modal closes
    }

    public function createBrand()
    {
        $this->isEditing = false;
        $this->resetForm(); // Clear all fields for a new entry
        $this->openModal();
    }

    public function editBrand(Brand $brand)
    {
        $this->isEditing = true;
        $this->brandId = $brand->id;
        $this->name = $brand->name;
        $this->slug = $brand->slug;
        $this->description = $brand->description;
        $this->currentLogo = $brand->logo; // Set path to existing logo
        $this->is_active = $brand->is_active;
        $this->openModal();
    }

    public function saveBrand()
    {
        $this->validate($this->getValidationRules());

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        // Handle logo upload
        if ($this->logo) {
            // Delete old logo if it exists and a new one is uploaded
            if ($this->currentLogo && Storage::disk('public')->exists($this->currentLogo)) {
                Storage::disk('public')->delete($this->currentLogo);
            }
            $data['logo'] = $this->logo->store('brands', 'public');
        } elseif (!$this->logo && $this->currentLogo) {
            // If no new logo but there's a current one, keep it
            $data['logo'] = $this->currentLogo;
        } else {
            // If no new logo and no current logo, set to null
            $data['logo'] = null;
        }

        if ($this->isEditing) {
            $brand = Brand::find($this->brandId);
            $brand->update($data);
            session()->flash('message', 'Brand updated successfully!');
        } else {
            Brand::create($data);
            session()->flash('message', 'Brand created successfully!');
        }

        $this->closeModal();
        $this->resetPage(); // In case a brand was added/edited on the current page
    }

    public function deleteBrand($brandId)
    {
        $brand = Brand::find($brandId);

        if (!$brand) {
            session()->flash('error', 'Brand not found.');
            return;
        }

        // Check for associated products before deleting
        if ($brand->products()->count() > 0) {
            session()->flash('error', 'Cannot delete brand with associated products. Please reassign or delete products first.');
            return;
        }

        // Delete logo file if it exists
        if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();
        session()->flash('message', 'Brand deleted successfully!');
        $this->resetPage();
    }

    // --- Utility Methods ---

    // Resets all form-related properties
    private function resetForm()
    {
        $this->brandId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->logo = null;
        $this->currentLogo = null;
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

    // Clear temporary logo when new one is selected
    public function updatedLogo()
    {
        $this->resetValidation('logo');
    }

    public function render()
    {
        $brands = Brand::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('slug', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.brand.brand-manager', [
            'brands' => $brands,
        ]);
    }
}
