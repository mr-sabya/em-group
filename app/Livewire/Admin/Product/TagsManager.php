<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\Tag;
use Livewire\Component;
use Illuminate\Support\Str;

class TagsManager extends Component
{
    public Product $product;
    public $searchTag = '';
    public $newTag = '';
    public $suggestedTags = [];
    public $selectedTagIds = []; // Current product tags

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->selectedTagIds = $product->tags->pluck('id')->toArray();
    }

    public function updatedSearchTag()
    {
        if (strlen($this->searchTag) > 2) { // Only search if more than 2 characters
            $this->suggestedTags = Tag::where('name', 'like', '%' . $this->searchTag . '%')
                                        ->whereNotIn('id', $this->selectedTagIds) // Don't suggest already added tags
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
        $this->validate(['newTag' => 'required|string|max:255|unique:tags,name']);

        $tag = Tag::create([
            'name' => $this->newTag,
            'slug' => Str::slug($this->newTag)
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

    private function syncTags()
    {
        $this->product->tags()->sync($this->selectedTagIds);
        // Reload tags to ensure relationships are fresh
        $this->product->load('tags');
    }

    public function render()
    {
        $currentTags = Tag::whereIn('id', $this->selectedTagIds)->get();

        return view('livewire.product.tags-manager', [
            'currentTags' => $currentTags,
        ]);
    }
}