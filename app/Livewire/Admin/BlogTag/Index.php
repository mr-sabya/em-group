<?php

namespace App\Livewire\BlogTag;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BlogTag; // Import the BlogTag model
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class Index extends Component
{
    use WithPagination;

    // Form properties
    public $tagId;
    public $name;
    public $slug;
    public $isEditing = false;

    // Table properties
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $listeners = ['tagSaved' => '$refresh', 'tagDeleted' => '$refresh'];

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
                Rule::unique('blog_tags')->ignore($this->tagId),
            ],
            'slug' => [
                'nullable', // Slug can be auto-generated or manually entered
                'string',
                'max:255',
                Rule::unique('blog_tags')->ignore($this->tagId),
            ],
        ];
    }

    // Custom validation messages
    protected $messages = [
        'name.unique' => 'A tag with this name already exists.',
        'slug.unique' => 'This slug is already in use.',
    ];

    // Auto-generate slug when name changes, if slug field is empty
    public function updatedName($value)
    {
        if (!$this->isEditing || empty($this->slug)) { // Only auto-generate if not editing or slug is empty
            $this->slug = Str::slug($value);
        }
    }

    // Method to load a tag for editing
    public function editTag($id)
    {
        $tag = BlogTag::findOrFail($id);
        $this->tagId = $tag->id;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
        $this->isEditing = true;
    }

    // Method to save (create or update) a tag
    public function saveTag()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug ?? Str::slug($this->name), // Ensure slug is set if not provided
        ];

        // Ensure unique slug, even if manually entered. Append suffix if needed.
        $originalSlug = $data['slug'];
        $i = 1;
        while (BlogTag::where('slug', $data['slug'])
                        ->where('id', '!=', $this->tagId)
                        ->exists()) {
            $data['slug'] = $originalSlug . '-' . $i++;
        }

        if ($this->isEditing) {
            BlogTag::find($this->tagId)->update($data);
            session()->flash('message', 'Blog tag updated successfully!');
        } else {
            BlogTag::create($data);
            session()->flash('message', 'Blog tag created successfully!');
        }

        $this->resetForm();
        $this->dispatch('tagSaved'); // Notify other parts of the app if needed
    }

    // Method to reset the form fields
    public function resetForm()
    {
        $this->resetValidation();
        $this->tagId = null;
        $this->name = '';
        $this->slug = '';
        $this->isEditing = false;
    }

    // Method to delete a tag
    public function deleteTag($id)
    {
        try {
            // Check if there are associated blog posts
            $tag = BlogTag::findOrFail($id);
            if ($tag->blogPosts()->count() > 0) {
                session()->flash('error', 'Cannot delete tag: It is currently linked to one or more blog posts. Please update or detach those posts first.');
                return;
            }

            $tag->delete();
            session()->flash('message', 'Blog tag deleted successfully!');
            $this->dispatch('tagDeleted'); // Notify other parts
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting the tag: ' . $e->getMessage());
        }
        $this->resetPage(); // Refresh pagination
    }

    public function render()
    {
        $tags = BlogTag::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.blog-tag.index', [
            'tags' => $tags,
        ]);
    }
}