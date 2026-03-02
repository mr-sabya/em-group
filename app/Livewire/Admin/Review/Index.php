<?php

namespace App\Livewire\Review;

use App\Models\Review;
use App\Enums\ReviewStatus;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Filters & Sorting
    public $search = '', $statusFilter = '', $perPage = 10;
    public $sortField = 'created_at', $sortDirection = 'desc';

    // Modal Properties
    public $selectedReviewId;
    public $newStatus;
    public $reviewToEdit; // Holds the object for display in modal

    protected $paginationTheme = 'bootstrap';

    public function sortBy($field)
    {
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc') ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    // Triggered when clicking "Edit Status"
    public function editStatus($id)
    {
        $this->reviewToEdit = Review::with('product', 'user')->findOrFail($id);
        $this->selectedReviewId = $id;
        $this->newStatus = $this->reviewToEdit->status->value;

        // Dispatches event to open the Bootstrap modal via JS
        $this->dispatch('open-status-modal');
    }

    public function updateStatus()
    {
        $review = Review::findOrFail($this->selectedReviewId);
        $statusEnum = ReviewStatus::from($this->newStatus);

        $review->update([
            'status' => $statusEnum,
            'is_approved' => $statusEnum === ReviewStatus::Approved
        ]);

        session()->flash('success', 'Review status updated successfully!');

        // Dispatches event to close modal
        $this->dispatch('close-status-modal');
    }

    public function render()
    {
        $reviews = Review::with(['user', 'product'])
            ->where(fn($q) => $q->where('comment', 'like', "%{$this->search}%")
                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.review.index', compact('reviews'));
    }
}
