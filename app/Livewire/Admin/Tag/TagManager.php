<?php

namespace App\Livewire\Tag;

use Livewire\Component;
use App\Models\Tag;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TagManager extends Component
{
    use WithPagination;

    // Properties for Tag Model
    public $tagId;
    public $name;
    public $slug;

    // UI State Properties
    public $showModal = false;
    public $isEditing = false;
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $listeners = ['deleteTagConfirmed' => 'deleteTag'];

    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'slug' => [
                'required',
                'string',
                'min:2',
                'max:255',
                // Ensure slug is unique, ignore current tag during edit
                ($this->tagId ? 'unique:tags,slug,' . $this->tagId : 'unique:tags,slug'),
            ],
        ];
    }

    // Real-time validation for name and slug
    public function updated($propertyName)
    {
        if ($propertyName === 'name') {
            $this->slug = Str::slug($this->name);
        }
        $this->validateOnly($propertyName);
    }

    public function createTag()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function editTag($id)
    {
        $tag = Tag::findOrFail($id);
        $this->tagId = $tag->id;
        $this->name = $tag->name;
        $this->slug = $tag->slug;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function saveTag()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
        ];

        if ($this->isEditing) {
            Tag::findOrFail($this->tagId)->update($data);
            session()->flash('message', 'Tag updated successfully!');
        } else {
            Tag::create($data);
            session()->flash('message', 'Tag created successfully!');
        }

        $this->closeModal();
        $this->dispatch('refreshComponent'); // Optional: Emit event if other components need to know
    }

    public function deleteTag($id)
    {
        Tag::findOrFail($id)->delete();
        session()->flash('message', 'Tag deleted successfully!');
    }

    // Confirmation for delete (client-side via JS usually)
    public function confirmDelete($id)
    {
        // This method can be used to emit a client-side event
        // that triggers a JS confirmation dialog (e.g., SweetAlert2).
        // For simplicity, we'll use wire:confirm directly in the Blade for now.
        $this->deleteTag($id); // Directly call deleteTag as wire:confirm handles confirmation
    }


    public function closeModal()
    {
        $this->showModal = false;
        $this->resetErrorBag();
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->tagId = null;
        $this->name = '';
        $this->slug = '';
        $this->resetValidation();
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

    public function render()
    {
        $tags = Tag::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tag.tag-manager', [
            'tags' => $tags,
        ]);
    }
}
