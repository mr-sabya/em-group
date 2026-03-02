<?php

namespace App\Livewire\Page;

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

    // Form Properties
    public $title;
    public $slug;
    public $content;
    public $banner_image_path; // Existing image
    public $new_banner_image;  // New upload
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $template = 'default';
    public $is_active = true;
    public $sort_order = 0;

    // Template Options (You can add more later)
    public $templates = [
        'default' => 'Default Template',
        'full-width' => 'Full Width',
        'contact' => 'Contact Page',
        'about' => 'About Us',
    ];

    public function mount($pageId = null)
    {
        // 1. Find existing or create new
        if ($pageId) {
            $this->page = Page::find($pageId);
        }

        if (!$this->page) {
            $this->page = new Page();
        }

        // 2. Fill properties
        if ($this->page->exists) {
            $this->fill($this->page->toArray());
            $this->banner_image_path = $this->page->banner_image;
        } else {
            // Defaults for new page
            $this->is_active = true;
            $this->sort_order = 0;
            $this->template = 'default';
        }
    }

    protected function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($this->page->id)
            ],
            'content' => ['nullable', 'string'],
            'new_banner_image' => ['nullable', 'image', 'max:2048'], // 2MB Max
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'template' => ['required', 'string', 'in:' . implode(',', array_keys($this->templates))],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
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
        if ($this->new_banner_image) {
            if ($this->page->banner_image) {
                Storage::disk('public')->delete($this->page->banner_image);
            }
            $this->banner_image_path = $this->new_banner_image->store('pages/banners', 'public');
        }

        // Prepare data
        $this->page->fill([
            'title' => $this->title,
            'slug' => $this->slug ?? Str::slug($this->title),
            'content' => $this->content,
            'banner_image' => $this->banner_image_path,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'template' => $this->template,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ])->save();

        session()->flash('message', 'Page ' . ($this->page->wasRecentlyCreated ? 'created' : 'updated') . ' successfully!');

        // Redirect to Index or keep on edit
        return redirect()->route('page.index');
    }

    public function render()
    {
        return view('livewire.page.manage');
    }
}
