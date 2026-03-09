<?php

namespace App\Livewire\Admin\Deal;

use App\Models\Deal;
use App\Models\Product;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed; // Added
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;

class Manage extends Component
{
    use WithFileUploads;

    public $dealId = null;

    // Deal Form Properties
    public $name;
    public $type = 'percentage';
    public $value;
    public $description;
    public $banner_image_path;
    public $link_target;
    public $starts_at;
    public $expires_at;
    public $is_active = true;
    public $is_featured = false;
    public $display_order = 0;

    // Temporary property for file upload
    public $imageFile;

    // Product Selection Properties
    public $selectedProducts = [];
    public $productSearch = '';
    public $productSearchResults = [];
    public $showProductSearchResults = false;

    /**
     * Computed property to access current tenant context
     */
    #[Computed]
    public function currentTenant()
    {
        return Tenant::find(session('active_tenant_id'));
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:fixed,percentage',
        'value' => 'nullable|numeric|min:0',
        'description' => 'nullable|string',
        'banner_image_path' => 'nullable|string',
        'imageFile' => 'nullable|image|max:1024',
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
            // find() respects Global Tenant Scope - ensures security
            $deal = Deal::find($this->dealId);

            if (!$deal) {
                return $this->redirectRoute('deal.index', navigate: true);
            }

            $this->name = $deal->name;
            $this->type = $deal->type;
            $this->value = $deal->value;
            $this->description = $deal->description;
            $this->banner_image_path = $deal->banner_image_path;
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

        // Grouped where to maintain tenant scope integrity
        $this->productSearchResults = Product::query()
            ->where(function ($q) use ($value) {
                $q->where('name', 'like', '%' . $value . '%');
            })
            ->whereNotIn('id', $this->selectedProducts)
            ->limit(10)
            ->get(['id', 'name', 'regular_price'])
            ->toArray();

        $this->showProductSearchResults = !empty($this->productSearchResults);
    }

    public function selectProductForDeal($productId)
    {
        // Double check product belongs to tenant before adding
        $exists = Product::where('id', $productId)->exists();

        if ($exists && !in_array($productId, $this->selectedProducts)) {
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

    public function hideProductSearchResults()
    {
        $this->js('$wire.set(\'showProductSearchResults\', false);');
    }

    public function saveDeal()
    {
        $this->validate();

        if ($this->imageFile) {
            if ($this->banner_image_path && Storage::disk('public')->exists($this->banner_image_path)) {
                Storage::disk('public')->delete($this->banner_image_path);
            }
            $this->banner_image_path = $this->imageFile->store('deals/banners', 'public');
        }

        $tenantId = session('active_tenant_id');

        $data = [
            'tenant_id' => $tenantId, // Explicitly set tenant_id
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'description' => $this->description,
            'banner_image_path' => $this->banner_image_path,
            'link_target' => $this->link_target,
            'starts_at' => $this->starts_at ? Carbon::parse($this->starts_at) : null,
            'expires_at' => $this->expires_at ? Carbon::parse($this->expires_at) : null,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'display_order' => $this->display_order,
        ];

        if ($this->dealId) {
            $deal = Deal::find($this->dealId);
            $deal->update($data);
            session()->flash('message', 'Deal updated successfully.');
        } else {
            $deal = Deal::create($data);
            session()->flash('message', 'Deal created successfully.');
            $this->dealId = $deal->id;
        }

        /**
         * FIXED: Sync pivot table with tenant_id to avoid "Field doesn't have a default value"
         */
        $syncData = [];
        foreach ($this->selectedProducts as $id) {
            $syncData[$id] = ['tenant_id' => $tenantId];
        }
        $deal->products()->sync($syncData);

        return $this->redirectRoute('deal.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.deal.manage');
    }
}
