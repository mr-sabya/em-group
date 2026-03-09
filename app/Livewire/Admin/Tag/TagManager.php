<?php

namespace App\Livewire\Admin\Tag;

use Livewire\Component;
use App\Models\Tag;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // Added for Computed Property
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

    /**
     * BEST WAY: Helper to get the current tenant for the UI
     */
    #[Computed]
    public function currentTenant()
    {
        return \App\Models\Tenant::find(session('active_tenant_id'));
    }

    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'slug' => [
                'required',
                'string',
                'min:2',
                'max:255',
                // SCOPED UNIQUE: Ensures slug is only unique for the active tenant
                Rule::unique('tags')
                    ->where('tenant_id', session('active_tenant_id'))
                    ->ignore($this->tagId),
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
        // Global Scope handles security: find() only returns if tag belongs to tenant
        $tag = Tag::find($id);

        if (!$tag) {
            session()->flash('error', 'Tag not found or access denied.');
            return;
        }

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
            'tenant_id' => session('active_tenant_id'), // Explicitly saving tenant_id
        ];

        if ($this->isEditing) {
            Tag::find($this->tagId)->update($data);
            session()->flash('message', 'Tag updated successfully!');
        } else {
            Tag::create($data);
            session()->flash('message', 'Tag created successfully!');
        }

        $this->closeModal();
    }

    public function deleteTag($id)
    {
        $tag = Tag::find($id);

        if ($tag) {
            $tag->delete();
            session()->flash('message', 'Tag deleted successfully!');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->tagId = null;
        $this->name = '';
        $this->slug = '';
        $this->resetValidation();
    }

    public function updatingSearch()
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

    public function render()
    {
        $tags = Tag::query()
            ->when($this->search, function ($query) {
                /** 
                 * BEST WAY: Group the search terms.
                 * Prevents OR from breaking the tenant global scope.
                 */
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.tag.tag-manager', [
            'tags' => $tags,
        ]);
    }
}
