<?php

namespace App\Livewire\BlogCategory;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BlogCategory; // Import the BlogCategory model
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class Index extends Component
{
    use WithPagination;

    // Form properties
    public $categoryId;
    public $name;
    public $slug;
    public $description;
    public $isEditing = false;

    // Table properties
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $listeners = ['categorySaved' => '$refresh', 'categoryDeleted' => '$refresh'];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    // Reset pagination when search or perPage changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Sort table
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // Validation rules for the form
    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blog_categories')->ignore($this->categoryId),
            ],
            'slug' => [
                'nullable', // Slug can be auto-generated or manually entered
                'string',
                'max:255',
                Rule::unique('blog_categories')->ignore($this->categoryId),
            ],
            'description' => 'nullable|string|max:1000',
        ];
    }

    // Custom validation messages
    protected $messages = [
        'name.unique' => 'A category with this name already exists.',
        'slug.unique' => 'This slug is already in use.',
    ];

    // Auto-generate slug when name changes, if slug field is empty
    public function updatedName($value)
    {
        if (!$this->isEditing || empty($this->slug)) { // Only auto-generate if not editing or slug is empty
            $this->slug = Str::slug($value);
        }
    }

    // Method to load a category for editing
    public function editCategory($id)
    {
        $category = BlogCategory::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description;
        $this->isEditing = true;
    }

    // Method to save (create or update) a category
    public function saveCategory()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug ?? Str::slug($this->name), // Ensure slug is set if not provided
            'description' => $this->description,
        ];

        // Ensure unique slug, even if manually entered. Append suffix if needed.
        $originalSlug = $data['slug'];
        $i = 1;
        while (BlogCategory::where('slug', $data['slug'])
                            ->where('id', '!=', $this->categoryId)
                            ->exists()) {
            $data['slug'] = $originalSlug . '-' . $i++;
        }

        if ($this->isEditing) {
            BlogCategory::find($this->categoryId)->update($data);
            session()->flash('message', 'Blog category updated successfully!');
        } else {
            BlogCategory::create($data);
            session()->flash('message', 'Blog category created successfully!');
        }

        $this->resetForm();
        $this->dispatch('categorySaved'); // Notify other parts of the app if needed
    }

    // Method to reset the form fields
    public function resetForm()
    {
        $this->resetValidation();
        $this->categoryId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->isEditing = false;
    }

    // Method to delete a category
    public function deleteCategory($id)
    {
        try {
            // Check if there are associated blog posts
            $category = BlogCategory::findOrFail($id);
            if ($category->blogPosts()->count() > 0) {
                session()->flash('error', 'Cannot delete category: It is currently linked to one or more blog posts. Please update or delete those posts first.');
                return;
            }

            $category->delete();
            session()->flash('message', 'Blog category deleted successfully!');
            $this->dispatch('categoryDeleted'); // Notify other parts
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting the category: ' . $e->getMessage());
        }
        $this->resetPage(); // Refresh pagination
    }

    public function render()
    {
        $categories = BlogCategory::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.blog-category.index', [
            'categories' => $categories,
        ]);
    }
}