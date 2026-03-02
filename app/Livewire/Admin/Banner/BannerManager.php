<?php

namespace App\Livewire\Banner;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Banner; // Ensure this is correctly imported
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BannerManager extends Component
{
    use WithPagination;
    use WithFileUploads; // For handling image uploads

    // --- Table Properties ---
    public $search = '';
    public $sortField = 'order'; // Default sort by order
    public $sortDirection = 'asc';
    public $perPage = 10;

    // --- Form Properties ---
    public $showModal = false; // Controls modal visibility
    public $bannerId;          // Null for create, ID for edit
    public $image;             // Temporary file for upload
    public $currentImage;      // Path to existing image for display/deletion
    public $title;
    public $subtitle;
    public $link;
    public $button;
    public $is_active = true;
    public $order = 0;

    public $isEditing = false; // Flag to determine if we're editing or creating

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'order'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    // Real-time validation for specific fields
    protected $rules = [
        'title' => 'required|string|max:500', // Max 500 characters as defined in migration
        'subtitle' => 'nullable|string|max:255',
        'link' => 'nullable|url|max:255',
        'button' => 'nullable|string|max:100',
        'image' => 'nullable|image|max:2048', // Max 2MB for banner images
        'is_active' => 'boolean',
        'order' => 'required|integer|min:0',
    ];

    // No dynamic slug validation needed for banners based on your model/migration,
    // but keeping `getValidationRules` for consistency if you add more complex rules later.
    protected function getValidationRules()
    {
        return $this->rules;
    }

    // Custom validation messages
    protected $messages = [
        'link.url' => 'The link must be a valid URL.',
        'image.max' => 'The banner image may not be greater than 2MB.',
        'order.required' => 'The order field is required.',
        'order.integer' => 'The order must be an integer.',
        'order.min' => 'The order must be at least 0.',
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

    public function createBanner()
    {
        $this->isEditing = false;
        $this->resetForm(); // Clear all fields for a new entry
        $this->openModal();
    }

    public function editBanner(Banner $banner)
    {
        $this->isEditing = true;
        $this->bannerId = $banner->id;
        $this->title = $banner->title;
        $this->subtitle = $banner->subtitle;
        $this->link = $banner->link;
        $this->button = $banner->button;
        $this->currentImage = $banner->image; // Set path to existing image
        $this->is_active = $banner->is_active;
        $this->order = $banner->order;
        $this->openModal();
    }

    public function saveBanner()
    {
        $this->validate($this->getValidationRules());

        $data = [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'link' => $this->link,
            'button' => $this->button,
            'is_active' => $this->is_active,
            'order' => $this->order,
        ];

        // Handle image upload
        if ($this->image) {
            // Delete old image if it exists and a new one is uploaded
            if ($this->currentImage && Storage::disk('public')->exists($this->currentImage)) {
                Storage::disk('public')->delete($this->currentImage);
            }
            $data['image'] = $this->image->store('banners', 'public');
        } elseif (!$this->image && $this->currentImage) {
            // If no new image but there's a current one, keep its path
            $data['image'] = $this->currentImage;
        } else {
            // If no new image and no current image, set to null
            $data['image'] = null;
        }

        if ($this->isEditing) {
            $banner = Banner::find($this->bannerId);
            $banner->update($data);
            session()->flash('message', 'Banner updated successfully!');
        } else {
            Banner::create($data);
            session()->flash('message', 'Banner created successfully!');
        }

        $this->closeModal();
        $this->resetPage(); // In case a banner was added/edited on the current page
    }

    public function deleteBanner($bannerId)
    {
        $banner = Banner::find($bannerId);

        if (!$banner) {
            session()->flash('error', 'Banner not found.');
            return;
        }

        // Delete image file if it exists
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();
        session()->flash('message', 'Banner deleted successfully!');
        $this->resetPage();
    }

    // --- Utility Methods ---

    // Resets all form-related properties
    private function resetForm()
    {
        $this->bannerId = null;
        $this->title = '';
        $this->subtitle = '';
        $this->link = '';
        $this->button = '';
        $this->image = null;
        $this->currentImage = null;
        $this->is_active = true;
        $this->order = 0; // Reset to default order
        $this->isEditing = false;
        $this->resetValidation();
    }

    // Clear temporary image when new one is selected
    public function updatedImage()
    {
        $this->resetValidation('image');
    }

    public function render()
    {
        $banners = Banner::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('subtitle', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.banner.banner-manager', [
            'banners' => $banners,
        ]);
    }
}
