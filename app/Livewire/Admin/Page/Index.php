<?php

namespace App\Livewire\Page;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithPagination;

    // --- Table Properties ---
    public $search = '';
    public $sortField = 'sort_order'; // Default sort by order
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $filterActive = null; // null for all, 1 for active, 0 for inactive

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'sort_order'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
        'filterActive' => ['except' => null],
    ];

    // --- Table Methods ---
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterActive()
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

    public function deletePage($pageId)
    {
        $page = Page::find($pageId);

        if (!$page) {
            session()->flash('error', 'Page not found.');
            return;
        }

        // Delete banner image if exists
        if ($page->banner_image && Storage::disk('public')->exists($page->banner_image)) {
            Storage::disk('public')->delete($page->banner_image);
        }

        $page->delete();
        session()->flash('message', 'Page deleted successfully!');

        // Reset page if empty to prevent 404 behavior on pagination
        $this->resetPage();
    }

    public function render()
    {
        $pages = Page::query()
            ->when($this->search, function (Builder $query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterActive !== null, function (Builder $query) {
                $query->where('is_active', (bool)$this->filterActive);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.page.index', [
            'pages' => $pages,
        ]);
    }
}
