<?php

namespace App\Livewire\Admin\Page;

use App\Models\Page;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Manage extends Component
{
    use WithFileUploads;

    public ?Page $page = null;

    // Form Properties mapped to your actual schema
    public $tenant_id;
    public $title;
    public $slug;
    public $page_type = 'landing';
    public $status = 'draft';

    // Content (stored as array in model, handled as string/HTML by editor)
    public $content;

    // SEO & Meta
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $meta_robots = 'index, follow';
    public $og_image_path; // Existing image
    public $new_og_image;  // New upload

    // Scheduling
    public $published_at;

    public function mount($pageId = null)
    {
        // For testing/example purposes, assigning a fixed tenant. 
        // In real world, replace with: auth()->user()->tenant_id or current tenant session.
        $this->tenant_id = 1;

        if ($pageId) {
            $this->page = Page::find($pageId);
        }

        if (!$this->page) {
            $this->page = new Page();
        }

        if ($this->page->exists) {
            $this->fill($this->page->toArray());
            $this->og_image_path = $this->page->og_image;
            $this->published_at = $this->page->published_at ? $this->page->published_at->format('Y-m-d\TH:i') : null;
        } else {
            // Defaults for new page
            $this->status = 'draft';
            $this->page_type = 'landing';
            $this->meta_robots = 'index, follow';
        }
    }

    protected function rules()
    {
        return [
            'tenant_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                // Unique per tenant
                Rule::unique('pages', 'slug')
                    ->where('tenant_id', $this->tenant_id)
                    ->ignore($this->page->id)
            ],
            'page_type' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta_robots' => ['nullable', 'string', 'max:255'],
            'new_og_image' => ['nullable', 'image', 'max:2048'], // 2MB Max
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        // Auto-generate slug if title changes and slug is empty
        if ($propertyName === 'title' && empty($this->slug)) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function generateSlug()
    {
        $this->slug = Str::slug($this->title);
        $this->validateOnly('slug');
    }

    public function save()
    {
        $this->validate();

        // Handle Image Upload
        if ($this->new_og_image) {
            if ($this->og_image_path) {
                Storage::disk('public')->delete($this->og_image_path);
            }
            $this->og_image_path = $this->new_og_image->store('pages/og_images', 'public');
        }

        // Prepare data
        $this->page->fill([
            'tenant_id' => $this->tenant_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'page_type' => $this->page_type,
            'status' => $this->status,
            'content' => $this->content, // Handled automatically as array by model casts
            'og_image' => $this->og_image_path,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'meta_robots' => $this->meta_robots,
            'published_at' => $this->published_at ? \Carbon\Carbon::parse($this->published_at) : null,
        ])->save();

        session()->flash('message', 'Page ' . ($this->page->wasRecentlyCreated ? 'created' : 'updated') . ' successfully!');

        return redirect()->route('page.index');
    }

    public function render()
    {
        return view('livewire.admin.page.manage');
    }
}
