<?php

namespace App\Livewire\Deal;

use App\Models\Deal;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title; // Keep Title attribute for page title


class Manage extends Component
{
    use WithFileUploads;

    public $dealId = null;

    // Deal Form Properties - now just direct properties
    public $name;
    public $type = 'percentage';
    public $value;
    public $description;
    public $banner_image_path; // This will hold the path in DB
    public $link_target;
    public $starts_at;
    public $expires_at;
    public $is_active = true;
    public $is_featured = false;
    public $display_order = 0;

    // Temporary property for file upload
    public $imageFile; // This will temporarily hold the uploaded file instance

    // Product Selection Properties
    public $selectedProducts = [];
    public $productSearch = '';
    public $productSearchResults = [];
    public $showProductSearchResults = false;

    // Validation rules are now defined as a protected property
    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:fixed,percentage',
        'value' => 'nullable|numeric|min:0',
        'description' => 'nullable|string',
        'banner_image_path' => 'nullable|string', // This will be validated when saving the path
        'imageFile' => 'nullable|image|max:1024', // Validation for the actual uploaded file
        'link_target' => 'nullable|string|max:255',
        'starts_at' => 'nullable|date',
        'expires_at' => 'nullable|date|after_or_equal:starts_at',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'display_order' => 'nullable|integer|min:0',
    ];

    protected $messages = [
        'expires_at.after_or_equal' => 'The expiry date must be after or equal to the start date.',
        'imageFile.max' => 'The banner image must not be larger than 1MB.',
        'imageFile.image' => 'The file must be an image (jpeg, png, bmp, gif, svg, webp).'
    ];

    public function mount($dealId = null)
    {
        $this->dealId = $dealId;

        if ($this->dealId) {
            $deal = Deal::findOrFail($this->dealId);
            $this->name = $deal->name;
            $this->type = $deal->type;
            $this->value = $deal->value;
            $this->description = $deal->description;
            $this->banner_image_path = $deal->banner_image_path; // Load existing path
            $this->link_target = $deal->link_target;
            $this->starts_at = $deal->starts_at?->format('Y-m-d\TH:i');
            $this->expires_at = $deal->expires_at?->format('Y-m-d\TH:i');
            $this->is_active = $deal->is_active;
            $this->is_featured = $deal->is_featured;
            $this->display_order = $deal->display_order;
            $this->selectedProducts = $deal->products->pluck('id')->toArray();
        }
    }

    // --- Product Search and Selection ---

    public function updatedProductSearch($value)
    {
        if (strlen($value) < 3) {
            $this->productSearchResults = [];
            $this->showProductSearchResults = false;
            return;
        }

        $this->productSearchResults = Product::where('name', 'like', '%' . $value . '%')
                                           ->whereNotIn('id', $this->selectedProducts)
                                           ->limit(10)
                                           ->get(['id', 'name', 'price'])
                                           ->toArray();
        $this->showProductSearchResults = !empty($this->productSearchResults);
    }

    public function selectProductForDeal($productId)
    {
        if (!in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts[] = $productId;
        }
        $this->productSearch = '';
        $this->productSearchResults = [];
        $this->showProductSearchResults = false;
    }

    public function removeProductFromDeal($productId)
    {
        $this->selectedProducts = array_diff($this->selectedProducts, [$productId]);
        $this->updatedProductSearch($this->productSearch);
    }

    public function getSelectedProductModelsProperty()
    {
        if (empty($this->selectedProducts)) {
            return collect();
        }
        return Product::whereIn('id', $this->selectedProducts)->get();
    }

    /**
     * Closes the product search results dropdown.
     * This is called from the view using wire:click.outside="hideProductSearchResults"
     */
    public function hideProductSearchResults()
    {
        // Use a slight delay to allow potential clicks on search results to register
        // before the results list disappears.
        $this->js('$wire.set(\'showProductSearchResults\', false);');
    }

    public function saveDeal()
    {
        // Validate all properties including the imageFile
        $this->validate();

        // Handle banner image upload
        if ($this->imageFile) {
            // Delete old image if it exists and a new one is uploaded
            if ($this->banner_image_path && Storage::disk('public')->exists($this->banner_image_path)) {
                Storage::disk('public')->delete($this->banner_image_path);
            }
            $this->banner_image_path = $this->imageFile->store('deals/banners', 'public');
        }

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'description' => $this->description,
            'banner_image_path' => $this->banner_image_path, // Now this holds the final path
            'link_target' => $this->link_target,
            'starts_at' => $this->starts_at ? Carbon::parse($this->starts_at) : null,
            'expires_at' => $this->expires_at ? Carbon::parse($this->expires_at) : null,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'display_order' => $this->display_order,
        ];

        if ($this->dealId) {
            $deal = Deal::findOrFail($this->dealId);
            $deal->update($data);
            session()->flash('message', 'Deal updated successfully.');
        } else {
            $deal = Deal::create($data);
            session()->flash('message', 'Deal created successfully.');
            $this->dealId = $deal->id;
        }

        $deal->products()->sync($this->selectedProducts);

        // Redirect back to the index page after saving
        return $this->redirectRoute('deal.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.deal.manage');
    }
}