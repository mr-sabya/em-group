<?php

namespace App\Livewire\Coupon;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Coupon;
use App\Models\Category;

class CategoryAssignment extends Component
{
    use WithPagination;

    public $couponId;
    public $search = '';
    public $showModal = false;
    public $assignedCategoryIds = [];

    protected $listeners = ['openCategoryAssignmentModal' => 'openModal'];

    public function openModal($couponId)
    {
        $this->couponId = $couponId;
        $this->loadAssignedCategories();
        $this->resetPage();
        $this->reset('search');
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->dispatch('couponAssignmentsUpdated');
    }

    public function loadAssignedCategories()
    {
        if ($this->couponId) {
            $coupon = Coupon::find($this->couponId);
            $this->assignedCategoryIds = $coupon ? $coupon->categories->pluck('id')->toArray() : [];
        } else {
            $this->assignedCategoryIds = [];
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function assignCategory($categoryId)
    {
        $coupon = Coupon::find($this->couponId);
        if ($coupon && !in_array($categoryId, $this->assignedCategoryIds)) {
            $coupon->categories()->attach($categoryId);
            $this->assignedCategoryIds[] = $categoryId;
            session()->flash('category_assignment_message', 'Category assigned successfully!');
        }
    }

    public function unassignCategory($categoryId)
    {
        $coupon = Coupon::find($this->couponId);
        if ($coupon && in_array($categoryId, $this->assignedCategoryIds)) {
            $coupon->categories()->detach($categoryId);
            $this->assignedCategoryIds = array_diff($this->assignedCategoryIds, [$categoryId]);
            session()->flash('category_assignment_message', 'Category unassigned successfully!');
        }
    }

    public function render()
    {
        $categories = Category::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.coupon.category-assignment', [
            'categories' => $categories,
        ]);
    }
}
