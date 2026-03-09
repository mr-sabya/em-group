<?php

namespace App\Livewire\Admin\Collection;

use App\Models\Collection;
use App\Models\Product;
use App\Models\Category;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed; // Added
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // Added

class Manage extends Component
{
    use WithFileUploads;

    public $collectionId = null;

    // Collection Form Properties
    public $name;
    public $title;
    public $description;
    public $featured_price;
    public $image_path;
    public $image_alt;
    public $tag;
    public $display_order = 0;
    public $is_active = true;
    public $category_id;

    // Temporary property for file upload
    public $imageFile;

    // List of available categories for the dropdown
    public $categories = [];

    /**
     * Computed property to access the active tenant info.
     */
    #[Computed]
    public function currentTenant()
    {
        return Tenant::find(session('active_tenant_id'));
    }

    protected function rules()
    {
        $tenantId = session('active_tenant_id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // SCOPED UNIQUE: Identifier only needs to be unique within this store
                Rule::unique('collections', 'name')
                    ->where('tenant_id', $tenantId)
                    ->ignore($this->collectionId)
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'featured_price' => 'nullable|numeric|min:0',
            'image_path' => 'nullable|string',
            'image_alt' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:255',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'category_id' => [
                'nullable',
                // Security: Ensure category belongs to this store
                Rule::exists('categories', 'id')->where('tenant_id', $tenantId)
            ],
            'imageFile' => 'nullable|image|max:1024',
        ];
    }

    protected $messages = [
        'imageFile.max' => 'The collection image must not be larger than 1MB.',
        'imageFile.image' => 'The file must be an image (jpeg, png, bmp, gif, svg, webp).',
        'category_id.exists' => 'The selected category is invalid for this store.',
    ];

    public function mount($collectionId = null)
    {
        $this->collectionId = $collectionId;

        // Category list automatically filtered by Global Tenant Scope
        $this->categories = Category::orderBy('name')->get(['id', 'name']);

        if ($this->collectionId) {
            // find() respects Global Tenant Scope - prevents loading from other stores
            $collection = Collection::find($this->collectionId);

            if (!$collection) {
                return $this->redirectRoute('collection.index', navigate: true);
            }

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
    }

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
            'tenant_id' => session('active_tenant_id'), // Explicitly set tenant_id
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
            $collection = Collection::find($this->collectionId);
            $collection->update($data);
            session()->flash('message', 'Collection updated successfully.');
        } else {
            $collection = Collection::create($data);
            session()->flash('message', 'Collection created successfully.');
            $this->collectionId = $collection->id;
        }

        return $this->redirectRoute('collection.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.collection.manage');
    }
}
