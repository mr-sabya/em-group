<?php

namespace App\Livewire\Admin\Categories;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class Manage extends Component
{
    use WithFileUploads;

    public $categoryId;
    public $name;
    public $slug;
    public $description;
    public $parent_id;
    public $image;
    public $currentImage;
    public $icon;
    public $currentIcon;
    public $is_active = true;
    public $show_on_homepage = false;
    public $sort_order = 0;
    public $seo_title;
    public $seo_description;

    public $isEditing = false;
    public $pageTitle = 'Create New Category';

    public function mount($categoryId = null)
    {
        if ($categoryId) {
            // Because of the Global Scope in the Category model, 
            // find() will only return a category if it belongs to the active tenant.
            $category = Category::find($categoryId);

            if (!$category) {
                return $this->redirect(route('product.categories.index'), navigate: true);
            }

            $this->isEditing = true;
            $this->categoryId = $category->id;
            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->description = $category->description;
            $this->parent_id = $category->parent_id;
            $this->currentImage = $category->image;
            $this->currentIcon = $category->icon;
            $this->is_active = $category->is_active;
            $this->show_on_homepage = $category->show_on_homepage;
            $this->sort_order = $category->sort_order;
            $this->seo_title = $category->seo_title;
            $this->seo_description = $category->seo_description;
            $this->pageTitle = 'Edit Category: ' . $category->name;
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                // IMPORTANT: Slug must be unique ONLY within the current tenant's categories
                Rule::unique('categories')
                    ->where('tenant_id', session('active_tenant_id'))
                    ->ignore($this->categoryId)
            ],
            'description' => 'nullable|string|max:1000',
            'parent_id' => [
                'nullable',
                // Ensure parent category also belongs to the current tenant
                Rule::exists('categories', 'id')->where('tenant_id', session('active_tenant_id'))
            ],
            'image' => 'nullable|image|max:1024',
            'icon' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:1024',
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
            'sort_order' => 'required|integer|min:0',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
        ];
    }

    public function generateSlug()
    {
        $this->slug = Str::slug($this->name);
    }

    public function saveCategory()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id ?: null,
            'is_active' => $this->is_active,
            'show_on_homepage' => $this->show_on_homepage,
            'sort_order' => $this->sort_order,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ];

        // Ensure tenant_id is set if not using the Trait's 'creating' hook
        if (!$this->isEditing) {
            $data['tenant_id'] = session('active_tenant_id');
        }

        if ($this->image) {
            if ($this->currentImage) Storage::disk('public')->delete($this->currentImage);
            $data['image'] = $this->image->store('categories', 'public');
        }

        if ($this->icon) {
            if ($this->currentIcon) Storage::disk('public')->delete($this->currentIcon);
            $data['icon'] = $this->icon->store('categories/icons', 'public');
        }

        if ($this->isEditing) {
            Category::find($this->categoryId)->update($data);
            session()->flash('message', 'Category updated successfully!');
        } else {
            Category::create($data);
            session()->flash('message', 'Category created successfully!');
        }

        return $this->redirect(route('product.categories.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.categories.manage', [
            // Global Scope automatically filters this list to the current tenant
            'parentCategories' => Category::whereNull('parent_id')
                ->when($this->categoryId, fn($q) => $q->where('id', '!=', $this->categoryId))
                ->orderBy('name')
                ->get(),
        ]);
    }
}
