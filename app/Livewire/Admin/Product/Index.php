<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User; // Assuming User model is your vendor model
use App\Enums\ProductType; // Use the enum
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
    public $filterActive = null; // null for all, 1 for active, 0 for inactive

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

    public function mount()
    {
        $this->categories = Category::active()->orderBy('name')->get(['id', 'name']);
        $this->brands = Brand::active()->orderBy('name')->get(['id', 'name']);
        $this->productTypes = ProductType::cases();
    }

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
        $product = Product::find($productId);

        if (!$product) {
            session()->flash('error', 'Product not found.');
            return;
        }

        // Add checks for related data before deletion
        // E.g., if products are in active orders, or have reviews
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
            if (file_exists(public_path('storage/' . $image->image_path))) {
                unlink(public_path('storage/' . $image->image_path));
            }
            $image->delete();
        }

        // Delete variants and their images/pivot data
        foreach ($product->variants as $variant) {
            foreach ($variant->images as $image) {
                if (file_exists(public_path('storage/' . $image->image_path))) {
                    unlink(public_path('storage/' . $image->image_path));
                }
                $image->delete();
            }
            $variant->attributeValues()->detach(); // Detach attribute values from variant
            $variant->delete();
        }

        // Detach related tags and attribute values (for normal products)
        $product->tags()->detach();
        $product->attributeValues()->detach();

        // For digital products, also delete the file if necessary
        if ($product->isDigital() && $product->digital_file && file_exists(storage_path('app/' . $product->digital_file))) {
            unlink(storage_path('app/' . $product->digital_file));
            // Also delete any associated Download records if desired
            $product->downloads()->delete();
        }

        $product->delete();
        session()->flash('message', 'Product deleted successfully!');
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->with(['categories', 'brand', 'vendor', 'images']) // Eager load relationships for display
            ->when($this->search, function (Builder $query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%')
                      ->orWhere('short_description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterCategory, function (Builder $query) {
                $query->where('category_id', $this->filterCategory);
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

        return view('livewire.product.index', [
            'products' => $products,
        ]);
    }
}