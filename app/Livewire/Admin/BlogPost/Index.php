<?php

namespace App\Livewire\BlogPost;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BlogPost;
use App\Models\BlogCategory; // To display category name
use Illuminate\Support\Str;

class Index extends Component
{
    use WithPagination;

    // Table properties
    public $search = '';
    public $sortField = 'published_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $categoryFilter = ''; // Filter by category
    public $statusFilter = ''; // Filter by published status

    protected $listeners = ['blogPostSaved' => '$refresh', 'blogPostDeleted' => '$refresh'];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'published_at'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
        'categoryFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    // Reset pagination when search, perPage, or filters change
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
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

    // Method to delete a blog post
    public function deletePost($id)
    {
        try {
            $post = BlogPost::findOrFail($id);

            // Delete associated image from storage if it exists
            if ($post->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image_path);
            }

            $post->delete();
            session()->flash('message', 'Blog post deleted successfully!');
            $this->dispatch('blogPostDeleted'); // Notify other parts
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting the blog post: ' . $e->getMessage());
        }
        $this->resetPage(); // Refresh pagination
    }

    public function render()
    {
        $blogPosts = BlogPost::query()
            ->with('category') // Eager load category for display
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('excerpt', 'like', '%' . $this->search . '%')
                    ->orWhere('content', 'like', '%' . $this->search . '%');
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('blog_category_id', $this->categoryFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_published', (bool)$this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = BlogCategory::orderBy('name')->get(['id', 'name']);

        return view('livewire.blog-post.index', [
            'blogPosts' => $blogPosts,
            'categories' => $categories,
        ]);
    }
}
