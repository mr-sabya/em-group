<?php

namespace App\Livewire\PaymentMethod;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithoutUrlPagination;

class Index extends Component
{
    use WithPagination, WithoutUrlPagination, WithFileUploads;

    // Table Controls
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $typeFilter = '';
    public $statusFilter = '';
    public $perPage = 10;

    // Form Properties
    public $method_id;
    public $name, $type = 'manual', $instructions, $is_default = false, $status = true;
    public $image, $existingImage;

    // UI States
    public $showModal = false;

    protected $queryString = ['search', 'typeFilter', 'statusFilter', 'sortField', 'sortDirection'];

    public function updatingSearch() { $this->resetPage(); }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['image', 'existingImage']);
        $this->method_id = $id;

        if ($id) {
            $method = PaymentMethod::findOrFail($id);
            $this->name = $method->name;
            $this->type = $method->type;
            $this->instructions = $method->instructions;
            $this->is_default = $method->is_default;
            $this->status = $method->status;
            $this->existingImage = $method->image;
        } else {
            $this->reset(['name', 'type', 'instructions', 'is_default', 'status']);
            $this->type = 'manual';
        }
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:manual,direct,gateway',
            'instructions' => 'nullable|string',
            'image' => 'nullable|image|max:1024', // 1MB Max
        ]);

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'type' => $this->type,
            'instructions' => $this->instructions,
            'is_default' => $this->is_default,
            'status' => $this->status,
        ];

        if ($this->image) {
            // Delete old image if exists
            if ($this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $data['image'] = $this->image->store('payments', 'public');
        }

        if ($this->is_default) {
            PaymentMethod::where('is_default', true)->update(['is_default' => false]);
        }

        PaymentMethod::updateOrCreate(['id' => $this->method_id], $data);

        $this->showModal = false;
        session()->flash('message', 'Payment Method Saved Successfully.');
    }

    public function toggleStatus($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->update(['status' => !$method->status]);
    }

    public function render()
    {
        $methods = PaymentMethod::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.payment-method.index', [
            'methods' => $methods
        ]);
    }
}
