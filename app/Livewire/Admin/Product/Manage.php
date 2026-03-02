<?php

namespace App\Livewire\Product;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Enums\VolumeUnit;
use App\Enums\WeightUnit;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
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

    public function mount($productId = null)
    {
        if ($productId) {
            $this->product = Product::with('categories')->findOrFail($productId);
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', Rule::unique('products', 'slug')->ignore($this->product->id)],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['nullable', 'string', Rule::unique('products', 'sku')->ignore($this->product->id)],
            'type' => ['required', Rule::enum(ProductType::class)],

            'regular_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'retail_price' => ['nullable', 'numeric', 'min:0'],
            'distributor_price' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],

            'is_manage_stock' => ['boolean'],
            'quantity' => ['required_if:is_manage_stock,true', 'numeric'],

            'selectedCategoryIds' => ['required', 'array', 'min:1'],
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
        $this->product->categories()->sync($this->selectedCategoryIds);

        session()->flash('message', 'Product saved successfully.');
        return $this->redirect(route('product.products.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.product.manage', [
            'categories_list' => Category::active()->get(),
            'brands_list' => Brand::active()->get(),
            'productTypes' => ProductType::cases(),
            'weightUnits' => WeightUnit::cases(),
            'volumeUnits' => VolumeUnit::cases(),
        ]);
    }
}
