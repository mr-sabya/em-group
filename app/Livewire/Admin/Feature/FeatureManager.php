<?php

namespace App\Livewire\Feature;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Feature;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class FeatureManager extends Component
{
    use WithPagination;
    use WithFileUploads; // For handling icon uploads

    // --- Table Properties ---
    public $search = '';
    public $sortField = 'sort_order'; // Default sort by order
    public $sortDirection = 'asc';
    public $perPage = 10;

    // --- Form Properties ---
    public $showModal = false; // Controls modal visibility
    public $featureId;         // Null for create, ID for edit
    public $title;
    public $subtitle;
    public $sort_order = 0;
    public $icon;            // Temporary file for upload
    public $currentIcon;     // Path to existing icon for display/deletion
    public $is_active = true;

    public $isEditing = false; // Flag to determine if we're editing or creating

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'sort_order'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    // Real-time validation rules
    protected $rules = [
        'title' => 'required|string|max:255',
        'subtitle' => 'nullable|string|max:255',
        'sort_order' => 'required|integer|min:0',
        'icon' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048', // Max 1MB (SVG support depends on validation config, usually 'file' or 'mimes:svg,png...')
        'is_active' => 'boolean',
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

    public function createFeature()
    {
        $this->isEditing = false;
        $this->resetForm(); // Clear all fields for a new entry
        // Default sort order could be max + 1
        $this->sort_order = Feature::max('sort_order') + 1;
        $this->openModal();
    }

    public function editFeature(Feature $feature)
    {
        $this->isEditing = true;
        $this->featureId = $feature->id;
        $this->title = $feature->title;
        $this->subtitle = $feature->subtitle;
        $this->sort_order = $feature->sort_order;
        $this->currentIcon = $feature->icon; // Set path to existing icon
        $this->is_active = $feature->is_active;
        $this->openModal();
    }

    public function saveFeature()
    {
        // Add specific rules for icon on create vs update
        $rules = $this->rules;
        if (!$this->isEditing && !$this->icon) {
            // Require icon on create
            $rules['icon'] = 'required|file|mimes:png,jpg,jpeg,svg,webp|max:2048';
        }

        $this->validate($rules);

        $data = [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        // Handle icon upload
        if ($this->icon) {
            // Delete old icon if it exists and a new one is uploaded
            if ($this->currentIcon && Storage::disk('public')->exists($this->currentIcon)) {
                Storage::disk('public')->delete($this->currentIcon);
            }
            // Store new icon
            $data['icon'] = $this->icon->store('features', 'public');
        } elseif (!$this->icon && $this->currentIcon) {
            // Keep existing icon
            $data['icon'] = $this->currentIcon;
        }

        if ($this->isEditing) {
            $feature = Feature::find($this->featureId);
            $feature->update($data);
            session()->flash('message', 'Feature updated successfully!');
        } else {
            Feature::create($data);
            session()->flash('message', 'Feature created successfully!');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function deleteFeature($featureId)
    {
        $feature = Feature::find($featureId);

        if (!$feature) {
            session()->flash('error', 'Feature not found.');
            return;
        }

        // Delete icon file if it exists
        if ($feature->icon && Storage::disk('public')->exists($feature->icon)) {
            Storage::disk('public')->delete($feature->icon);
        }

        $feature->delete();
        session()->flash('message', 'Feature deleted successfully!');
        $this->resetPage();
    }

    // --- Utility Methods ---

    private function resetForm()
    {
        $this->featureId = null;
        $this->title = '';
        $this->subtitle = '';
        $this->sort_order = 0;
        $this->icon = null;
        $this->currentIcon = null;
        $this->is_active = true;
        $this->isEditing = false;
        $this->resetValidation();
    }

    // Clear temporary icon when new one is selected
    public function updatedIcon()
    {
        $this->resetValidation('icon');
    }

    public function render()
    {
        $features = Feature::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('subtitle', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.feature.feature-manager', [
            'features' => $features,
        ]);
    }
}
