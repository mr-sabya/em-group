<?php

namespace App\Livewire\Collection;

use App\Models\Collection;
use App\Models\Product;
use App\Models\Category; // Import Category model
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;


class Manage extends Component
{
    use WithFileUploads;

    public $collectionId = null;

    // Collection Form Properties
    public $name; // Corresponds to `id`/`slug` in frontend data
    public $title;
    public $description;
    public $featured_price;
    public $image_path; // Stored image path
    public $image_alt;
    public $tag;
    public $display_order = 0;
    public $is_active = true;
    public $category_id; // Added category_id

    // Temporary property for file upload
    public $imageFile;

    // List of available categories for the dropdown
    public $categories = [];

    protected $rules = [
        'name' => 'required|string|max:255|unique:collections,name', // Unique rule needs to be adjusted for updates
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'featured_price' => 'nullable|numeric|min:0',
        'image_path' => 'nullable|string',
        'image_alt' => 'nullable|string|max:255',
        'tag' => 'nullable|string|max:255',
        'display_order' => 'nullable|integer|min:0',
        'is_active' => 'boolean',
        'category_id' => 'nullable|exists:categories,id', // Validate category_id
        'imageFile' => 'nullable|image|max:1024',
    ];

    protected $messages = [
        'imageFile.max' => 'The collection image must not be larger than 1MB.',
        'imageFile.image' => 'The file must be an image (jpeg, png, bmp, gif, svg, webp).',
        'category_id.exists' => 'The selected category is invalid.',
    ];

    public function mount($collectionId = null)
    {
        $this->collectionId = $collectionId;
        $this->categories = Category::orderBy('name')->get(['id', 'name']); // Fetch categories for dropdown

        if ($this->collectionId) {
            $collection = Collection::findOrFail($this->collectionId); // No `with('products')` needed
            $this->name = $collection->name;
            $this->title = $collection->title;
            $this->description = $collection->description;
            $this->featured_price = $collection->featured_price;
            $this->image_path = $collection->image_path;
            $this->image_alt = $collection->image_alt;
            $this->tag = $collection->tag;
            $this->display_order = $collection->display_order;
            $this->is_active = $collection->is_active;
            $this->category_id = $collection->category_id;
        }

        // Adjust unique rule for 'name' when editing
        if ($this->collectionId) {
            $this->rules['name'] = 'required|string|max:255|unique:collections,name,' . $this->collectionId;
        }
    }

    // Removed all product-related methods: updatedProductSearch, selectProductForDeal,
    // removeProductFromDeal, getSelectedProductModelsProperty, hideProductSearchResults

    public function saveCollection()
    {
        $this->validate();

        // Handle image upload
        if ($this->imageFile) {
            if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
                Storage::disk('public')->delete($this->image_path);
            }
            $this->image_path = $this->imageFile->store('collections/images', 'public');
        }

        $data = [
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'featured_price' => $this->featured_price,
            'image_path' => $this->image_path,
            'image_alt' => $this->image_alt,
            'tag' => $this->tag,
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
            'category_id' => $this->category_id,
        ];

        if ($this->collectionId) {
            $collection = Collection::findOrFail($this->collectionId);
            $collection->update($data);
            session()->flash('message', 'Collection updated successfully.');
        } else {
            $collection = Collection::create($data);
            session()->flash('message', 'Collection created successfully.');
            $this->collectionId = $collection->id; // Keep if user might want to continue editing
        }

        // Removed product sync logic

        return $this->redirectRoute('collection.index', navigate: true);
    }


    public function render()
    {
        return view('livewire.collection.manage');
    }
}
