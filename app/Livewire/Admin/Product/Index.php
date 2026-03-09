<?php

namespace App\Livewire\Admin\Product;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // Added for Computed Property
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Builder;

class Index extends Component
{
    use WithPagination;

    // --- Table Properties ---
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $filterCategory = null;
    public $filterBrand = null;
    public $filterType = null;
    public $filterActive = null;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
        'filterCategory' => ['except' => null],
        'filterBrand' => ['except' => null],
        'filterType' => ['except' => null],
        'filterActive' => ['except' => null],
    ];

    // --- Filter Options ---
    public $categories = [];
    public $brands = [];
    public $productTypes = [];

    /**
     * BEST WAY: Computed Property to get the current tenant.
     * Accessible in Class/Blade via $this->currentTenant
     */
    #[Computed]
    public function currentTenant()
    {
        return \App\Models\Tenant::find(session('active_tenant_id'));
    }

    public function mount()
    {
        // Global Scopes on Category/Brand models ensure these are tenant-specific
        $this->categories = Category::active()->orderBy('name')->get(['id', 'name']);
        $this->brands = Brand::active()->orderBy('name')->get(['id', 'name']);
        $this->productTypes = ProductType::cases();
    }

    // --- Table Methods ---
    // --- Table Methods ---
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    public function updatingFilterBrand()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterActive()
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

    public function deleteProduct($productId)
    {
        // Global Scope in Product model ensures we only find product for active tenant
        $product = Product::find($productId);

        if (!$product) {
            session()->flash('error', 'Product not found or access denied.');
            return;
        }

        if ($product->orderItems()->count() > 0) {
            session()->flash('error', 'Cannot delete product with associated order items.');
            return;
        }

        if ($product->reviews()->count() > 0) {
            session()->flash('error', 'Cannot delete product with associated reviews.');
            return;
        }

        // Delete associated images
        foreach ($product->images as $image) {
            if ($image->image_path && file_exists(public_path('storage/' . $image->image_path))) {
                unlink(public_path('storage/' . $image->image_path));
            }
            $image->delete();
        }

        // Delete variants and their images/pivot data
        foreach ($product->variants as $variant) {
            foreach ($variant->images as $image) {
                if ($image->image_path && file_exists(public_path('storage/' . $image->image_path))) {
                    unlink(public_path('storage/' . $image->image_path));
                }
                $image->delete();
            }
            $variant->attributeValues()->detach();
            $variant->delete();
        }

        $product->tags()->detach();
        $product->attributeValues()->detach();

        if ($product->isDigital() && $product->digital_file && file_exists(storage_path('app/' . $product->digital_file))) {
            unlink(storage_path('app/' . $product->digital_file));
            $product->downloads()->delete();
        }

        $product->delete();
        session()->flash('message', 'Product deleted successfully!');
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->with(['categories', 'brand', 'images'])
            ->when($this->search, function (Builder $query) {
                /** 
                 * BEST WAY: Wrap OR conditions in a closure.
                 * Prevents search from bypassing the Global Tenant Scope.
                 */
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('short_description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterCategory, function (Builder $query) {
                // Assuming Product has a category_id or is linked via a pivot
                $query->whereHas('categories', function ($q) {
                    $q->where('categories.id', $this->filterCategory);
                });
            })
            ->when($this->filterBrand, function (Builder $query) {
                $query->where('brand_id', $this->filterBrand);
            })
            ->when($this->filterType, function (Builder $query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->filterActive !== null, function (Builder $query) {
                $query->where('is_active', (bool)$this->filterActive);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.product.index', [
            'products' => $products,
        ]);
    }
}
