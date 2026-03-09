<?php

namespace App\Livewire\Admin\Product;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Enums\ProductType;
use App\Enums\VolumeUnit;
use App\Enums\WeightUnit;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed; // Added for Computed Property
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Manage extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    // Basic Info
    public $brand_id;
    public $name;
    public $slug;
    public $sku;
    public $short_description;
    public $long_description;
    public $thumbnail_image_path;
    public $new_thumbnail_image;
    public $type = 'normal';

    // Pricing Fields
    public $regular_price;
    public $sale_price;
    public $retail_price;
    public $distributor_price;
    public $purchase_price;

    // Specifications
    public $weight;
    public $weight_unit = 'kg';
    public $volume;
    public $volume_unit = 'l';

    // Status
    public $is_active = true;
    public $is_featured = false;
    public $is_new = false;

    // Stock & Limits
    public $is_manage_stock = false;
    public $quantity = 0;
    public $min_order_quantity = 1;
    public $max_order_quantity;

    // SEO
    public $meta_title;
    public $meta_description;

    // Categories
    public $selectedCategoryIds = [];

    /**
     * Computed Property to get current tenant info.
     */
    #[Computed]
    public function currentTenant()
    {
        return \App\Models\Tenant::find(session('active_tenant_id'));
    }

    public function mount($productId = null)
    {
        if ($productId) {
            // Global Scope in Product model ensures we only find if it belongs to current tenant
            $this->product = Product::with('categories')->find($productId);

            if (!$this->product) {
                return $this->redirect(route('product.products.index'), navigate: true);
            }

            $this->fill($this->product->toArray());
            $this->type = $this->product->type->value;
            $this->selectedCategoryIds = $this->product->categories->pluck('id')->toArray();
        } else {
            $this->product = new Product();
            $this->type = ProductType::Normal->value;
        }
    }

    protected function rules()
    {
        $tenantId = session('active_tenant_id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                // SCOPED UNIQUE: Allow same slug in different stores
                Rule::unique('products', 'slug')
                    ->where('tenant_id', $tenantId)
                    ->ignore($this->product->id)
            ],
            'sku' => [
                'nullable',
                'string',
                // SCOPED UNIQUE: Allow same SKU in different stores
                Rule::unique('products', 'sku')
                    ->where('tenant_id', $tenantId)
                    ->ignore($this->product->id)
            ],
            'brand_id' => [
                'nullable',
                // Ensure brand belongs to this tenant
                Rule::exists('brands', 'id')->where('tenant_id', $tenantId)
            ],
            'type' => ['required', Rule::enum(ProductType::class)],
            'regular_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'retail_price' => ['nullable', 'numeric', 'min:0'],
            'distributor_price' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'is_manage_stock' => ['boolean'],
            'quantity' => ['required_if:is_manage_stock,true', 'numeric'],
            'selectedCategoryIds' => ['required', 'array', 'min:1'],
            'selectedCategoryIds.*' => [
                // Ensure linked categories belong to this tenant
                Rule::exists('categories', 'id')->where('tenant_id', $tenantId)
            ],
            'new_thumbnail_image' => ['nullable', 'image', 'max:2048'],
            'weight' => ['nullable', 'numeric'],
            'volume' => ['nullable', 'numeric'],
            'min_order_quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function updatedName()
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function generateSlug()
    {
        $this->slug = Str::slug($this->name);
    }

    public function save()
    {
        $this->validate();

        if ($this->new_thumbnail_image) {
            if ($this->product->thumbnail_image_path) {
                Storage::disk('public')->delete($this->product->thumbnail_image_path);
            }
            $this->thumbnail_image_path = $this->new_thumbnail_image->store('products/thumbnails', 'public');
        }

        $data = [
            'tenant_id' => session('active_tenant_id'), // Explicitly ensure tenant_id is set
            'brand_id' => $this->brand_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'thumbnail_image_path' => $this->thumbnail_image_path,
            'type' => $this->type,
            'regular_price' => $this->regular_price,
            'sale_price' => $this->sale_price,
            'retail_price' => $this->retail_price,
            'distributor_price' => $this->distributor_price,
            'purchase_price' => $this->purchase_price,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'volume' => $this->volume,
            'volume_unit' => $this->volume_unit,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'is_new' => $this->is_new,
            'is_manage_stock' => $this->is_manage_stock,
            'quantity' => $this->is_manage_stock ? $this->quantity : 0,
            'min_order_quantity' => $this->min_order_quantity,
            'max_order_quantity' => $this->max_order_quantity,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
        ];

        $this->product->fill($data)->save();

        // Sync Categories
        $this->product->syncCategoriesWithTenant($this->selectedCategoryIds);

        session()->flash('message', 'Product saved successfully.');
        return $this->redirect(route('product.products.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.product.manage', [
            // Model Global Scopes automatically filter these by tenant
            'categories_list' => Category::active()->get(),
            'brands_list' => Brand::active()->get(),
            'productTypes' => ProductType::cases(),
            'weightUnits' => WeightUnit::cases(),
            'volumeUnits' => VolumeUnit::cases(),
        ]);
    }
}
