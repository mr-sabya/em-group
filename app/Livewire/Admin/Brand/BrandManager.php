<?php

namespace App\Livewire\Admin\Brand;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed; // Added this
use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BrandManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- Table Properties ---
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // --- Form Properties ---
    public $showModal = false;
    public $brandId;
    public $name;
    public $slug;
    public $description;
    public $logo;
    public $currentLogo;
    public $is_active = true;

    public $isEditing = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    /**
     * Computed property to access current tenant info in class or blade
     */
    #[Computed]
    public function currentTenant()
    {
        return \App\Models\Tenant::find(session('active_tenant_id'));
    }

    // Real-time validation for specific fields
    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|alpha_dash',
        'description' => 'nullable|string|max:1000',
        'logo' => 'nullable|image|max:1024',
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
                // UPDATED: Slug is now only unique within the current tenant
                Rule::unique('brands')
                    ->where('tenant_id', session('active_tenant_id'))
                    ->ignore($this->brandId),
            ],
        ]);
    }

    protected $messages = [
        'slug.unique' => 'This slug is already taken. Please try another one.',
        'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
    ];

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

    public function createBrand()
    {
        $this->isEditing = false;
        $this->resetForm();
        $this->openModal();
    }

    public function editBrand(Brand $brand)
    {
        $this->isEditing = true;
        $this->brandId = $brand->id;
        $this->name = $brand->name;
        $this->slug = $brand->slug;
        $this->description = $brand->description;
        $this->currentLogo = $brand->logo;
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
            'tenant_id' => session('active_tenant_id'), // Explicitly ensuring tenant is saved
        ];

        if ($this->logo) {
            if ($this->currentLogo && Storage::disk('public')->exists($this->currentLogo)) {
                Storage::disk('public')->delete($this->currentLogo);
            }
            $data['logo'] = $this->logo->store('brands', 'public');
        } elseif (!$this->logo && $this->currentLogo) {
            $data['logo'] = $this->currentLogo;
        } else {
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
        $this->resetPage();
    }

    public function deleteBrand($brandId)
    {
        $brand = Brand::find($brandId);

        if (!$brand) {
            session()->flash('error', 'Brand not found.');
            return;
        }

        if ($brand->products()->count() > 0) {
            session()->flash('error', 'Cannot delete brand with associated products.');
            return;
        }

        if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();
        session()->flash('message', 'Brand deleted successfully!');
        $this->resetPage();
    }

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

    public function updatedName($value)
    {
        if (empty($this->slug) || Str::slug($value) === $this->slug) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedLogo()
    {
        $this->resetValidation('logo');
    }

    public function render()
    {
        $brands = Brand::query()
            ->when($this->search, function ($query) {
                // Grouped search to maintain global tenant scope security
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.brand.brand-manager', [
            'brands' => $brands,
        ]);
    }
}
