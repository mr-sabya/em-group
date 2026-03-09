<?php

namespace App\Livewire\Admin\Product;

use App\Models\Product;
use App\Models\Tag;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagsManager extends Component
{
    public Product $product;
    public $searchTag = '';
    public $newTag = '';
    public $suggestedTags = [];
    public $selectedTagIds = [];

    public function mount(Product $product)
    {
        $this->product = $product;
        // The Global Scope on the Tag model (via relationship) 
        // ensures we only get tags for the current tenant.
        $this->selectedTagIds = $product->tags->pluck('id')->toArray();
    }

    #[Computed]
    public function currentTenantId()
    {
        return session('active_tenant_id');
    }

    public function updatedSearchTag()
    {
        if (strlen($this->searchTag) > 2) {
            $this->suggestedTags = Tag::where('name', 'like', '%' . $this->searchTag . '%')
                ->whereNotIn('id', $this->selectedTagIds)
                ->limit(10)
                ->get();
        } else {
            $this->suggestedTags = [];
        }
    }

    public function addTag($tagId)
    {
        if (!in_array($tagId, $this->selectedTagIds)) {
            $this->selectedTagIds[] = $tagId;
            $this->syncTags();
            $this->searchTag = '';
            $this->suggestedTags = [];
            session()->flash('message', 'Tag added successfully!');
        }
    }

    public function createAndAddTag()
    {
        // SCOPED VALIDATION: Ensure tag name is unique only for this tenant
        $this->validate([
            'newTag' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->where('tenant_id', $this->currentTenantId)
            ]
        ]);

        $tag = Tag::create([
            'name' => $this->newTag,
            'slug' => Str::slug($this->newTag),
            'tenant_id' => $this->currentTenantId // Explicitly set tenant_id
        ]);

        $this->selectedTagIds[] = $tag->id;
        $this->syncTags();

        $this->newTag = '';
        $this->searchTag = '';
        $this->suggestedTags = [];
        session()->flash('message', 'New tag created and added successfully!');
    }

    public function removeTag($tagId)
    {
        $this->selectedTagIds = array_diff($this->selectedTagIds, [$tagId]);
        $this->syncTags();
        session()->flash('message', 'Tag removed successfully!');
    }

    /**
     * FIXED: This method now includes tenant_id for the pivot table
     */
    private function syncTags()
    {
        $tenantId = $this->currentTenantId;

        // Prepare the data for the sync method: [id => ['tenant_id' => X], id => [...]]
        $syncData = [];
        foreach ($this->selectedTagIds as $id) {
            $syncData[$id] = ['tenant_id' => $tenantId];
        }

        // Sync with the additional pivot data
        $this->product->tags()->sync($syncData);

        $this->product->load('tags');
    }

    public function render()
    {
        $currentTags = Tag::whereIn('id', $this->selectedTagIds)->get();

        return view('livewire.admin.product.tags-manager', [
            'currentTags' => $currentTags,
        ]);
    }
}
