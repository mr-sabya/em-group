<?php

namespace App\Livewire\Categories;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithPagination;

    // Table state
    public $search = '';
    public $sortField = 'sort_order';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Property to track which category is selected for deletion
    public $categoryIdBeingDeleted = null;

    // Listeners for events from the form component
    protected $listeners = ['categorySaved' => '$refresh', 'categoryCreated' => '$refresh'];

    protected $queryString = ['search', 'sortField', 'sortDirection', 'page'];

    public function updatingSearch()
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

    // Emit event to open create form in CategoryForm component
    public function createCategory()
    {
        $this->dispatch('openCategoryFormModal');
    }

    // Emit event to open edit form in CategoryForm component
    public function editCategory($categoryId)
    {
        return $this->redirect(route('product.categories.edit', ['category' => $categoryId]), navigate: true);
    }

    /**
     * Set the category ID and open the confirmation modal
     */
    public function confirmDelete($categoryId)
    {
        $this->categoryIdBeingDeleted = $categoryId;
        $this->dispatch('show-delete-modal');
    }

    /**
     * Perform the actual deletion
     */
    public function deleteCategory()
    {
        if (!$this->categoryIdBeingDeleted) {
            return;
        }

        $category = Category::find($this->categoryIdBeingDeleted);

        if (!$category) {
            $this->dispatch('hide-delete-modal');
            $this->dispatch('notify', message: 'Category not found.', type: 'danger');
            return;
        }

        // Validation: Check for subcategories
        if ($category->children()->count() > 0) {
            $this->dispatch('hide-delete-modal');
            $this->dispatch('notify', message: 'Cannot delete category with subcategories. Please move or delete subcategories first.', type: 'danger');
            return;
        }

        // Validation: Check for associated products
        if ($category->products()->count() > 0) {
            $this->dispatch('hide-delete-modal');
            $this->dispatch('notify', message: 'Cannot delete category with associated products. Please reassign products first.', type: 'danger');
            return;
        }

        // Delete associated image file if it exists
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        // Perform delete
        $category->delete();

        // Cleanup state
        $this->categoryIdBeingDeleted = null;

        // Close modal and show success notification
        $this->dispatch('hide-delete-modal');
        $this->dispatch(
            'notify',
            message: 'Category deleted successfully!',
            type: 'success'
        );

        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::query()
            ->with('parent')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.categories.index', [
            'categories' => $categories,
        ]);
    }
}
