<?php

namespace App\Livewire\Admin\Review;

use App\Models\Review;
use App\Models\Tenant;
use App\Enums\ReviewStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class Index extends Component
{
    use WithPagination;

    // Filters & Sorting
    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Modal Properties
    public $selectedReviewId;
    public $newStatus;
    public $reviewToEdit;

    protected $paginationTheme = 'bootstrap';

    /**
     * Computed Property to get the current tenant.
     */
    #[Computed]
    public function currentTenant()
    {
        return Tenant::find(session('active_tenant_id'));
    }

    public function sortBy($field)
    {
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc') ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function editStatus($id)
    {
        // Global Scope ensures this only finds a review belonging to the active tenant
        $this->reviewToEdit = Review::with(['product', 'user'])->find($id);

        if (!$this->reviewToEdit) {
            session()->flash('error', 'Review not found or access denied.');
            return;
        }

        $this->selectedReviewId = $id;
        $this->newStatus = $this->reviewToEdit->status->value;

        $this->dispatch('open-status-modal');
    }

    public function updateStatus()
    {
        $review = Review::find($this->selectedReviewId);

        if (!$review) {
            return;
        }

        $statusEnum = ReviewStatus::from($this->newStatus);

        $review->update([
            'status' => $statusEnum,
            'is_approved' => $statusEnum === ReviewStatus::Approved
        ]);

        session()->flash('success', 'Review status updated successfully!');
        $this->dispatch('close-status-modal');
    }

    public function render()
    {
        $reviews = Review::query()
            ->with(['user', 'product'])
            ->where(function ($query) {
                /**
                 * Grouped OR conditions to maintain Tenant Global Scope integrity.
                 */
                if ($this->search) {
                    $query->where(function ($q) {
                        $q->where('comment', 'like', "%{$this->search}%")
                            ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%"))
                            ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$this->search}%"));
                    });
                }
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.review.index', compact('reviews'));
    }
}
