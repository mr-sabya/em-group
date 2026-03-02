<?php

namespace App\Livewire\Categories;

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
            $category = Category::find($categoryId);
            if ($category) {
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
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('categories')->ignore($this->categoryId)],
            'description' => 'nullable|string|max:1000',
            'parent_id' => ['nullable', Rule::exists('categories', 'id')],
            'image' => 'nullable|image|max:1024',
            'icon' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:1024',
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
            'sort_order' => 'required|integer|min:0',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
        ];
    }

    // Manual Slug Generation
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
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
            'show_on_homepage' => $this->show_on_homepage,
            'sort_order' => $this->sort_order,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ];

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

        return $this->redirect(route('product.categories.index'), navigate:true);
    }

    public function render()
    {
        return view('livewire.categories.manage', [
            'parentCategories' => Category::whereNull('parent_id')->when($this->categoryId, fn($q) => $q->where('id', '!=', $this->categoryId))->orderBy('name')->get(),
        ]);
    }
}
