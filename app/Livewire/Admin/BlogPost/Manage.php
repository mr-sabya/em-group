<?php

namespace App\Livewire\BlogPost;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag; // Import BlogTag model
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Manage extends Component
{
    use WithFileUploads;

    public $blogPostId;
    public $title;
    public $slug;
    public $imageFile;
    public $image_path;
    public $excerpt;
    public $content;
    public $blog_category_id;
    public $published_at_date;
    public $published_at_time;
    public $is_published = false;

    public $selectedTags = []; // Array to hold selected tag IDs
    public $allTags; // Collection of all available tags

    public $isEditing = false;
    public $categories;

    protected $listeners = ['refreshEditor' => '$refresh'];

    // Validation rules
    protected function rules()
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blog_posts')->ignore($this->blogPostId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('blog_posts')->ignore($this->blogPostId),
            ],
            'imageFile' => 'nullable|image|max:1024|mimes:jpeg,png,jpg,gif,webp', // Max 1MB
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'published_at_date' => 'nullable|date',
            'published_at_time' => 'nullable|date_format:H:i',
            'is_published' => 'boolean',
            'selectedTags' => 'nullable|array', // Tags can be an array of IDs
            'selectedTags.*' => 'exists:blog_tags,id', // Each tag ID must exist
        ];
    }

    // Custom validation messages
    protected $messages = [
        'title.unique' => 'A blog post with this title already exists.',
        'slug.unique' => 'This slug is already in use.',
        'blog_category_id.required' => 'Please select a category.',
        'content.required' => 'The content field is required.',
        'imageFile.max' => 'The image must not be larger than 1MB.',
        'imageFile.mimes' => 'The image must be a JPG, PNG, GIF, or WebP file.',
        'selectedTags.*.exists' => 'One or more selected tags are invalid.',
    ];

    // Auto-generate slug when title changes, if slug field is empty
    public function updatedTitle($value)
    {
        if (empty($this->slug) || Str::slug($this->title) === $this->slug) {
            $this->slug = Str::slug($value);
        }
    }

    public function mount($blogPostId = null)
    {
        $this->categories = BlogCategory::orderBy('name')->get();
        $this->allTags = BlogTag::orderBy('name')->get(); // Load all tags for the multi-select

        if ($blogPostId) {
            $this->isEditing = true;
            $post = BlogPost::with('tags')->findOrFail($blogPostId); // Eager load tags
            $this->blogPostId = $post->id;
            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->image_path = $post->image_path;
            $this->excerpt = $post->excerpt;
            $this->content = $post->content;
            $this->blog_category_id = $post->blog_category_id;
            $this->is_published = $post->is_published;

            if ($post->published_at) {
                $this->published_at_date = $post->published_at->format('Y-m-d');
                $this->published_at_time = $post->published_at->format('H:i');
            } else {
                $this->published_at_date = now()->format('Y-m-d');
                $this->published_at_time = now()->format('H:i');
            }

            $this->selectedTags = $post->tags->pluck('id')->toArray(); // Populate selected tags
        } else {
            $this->is_published = false;
            $this->published_at_date = now()->format('Y-m-d');
            $this->published_at_time = now()->format('H:i');
        }
    }

    public function savePost()
    {
        $this->validate();

        $publishedAt = null;
        if ($this->published_at_date && $this->published_at_time) {
            try {
                $publishedAt = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $this->published_at_date . ' ' . $this->published_at_time
                );
            } catch (\Exception $e) {
                $this->addError('published_at_date', 'Invalid date or time format.');
                $this->addError('published_at_time', 'Invalid date or time format.');
                return;
            }
        }

        $data = [
            'title' => $this->title,
            'slug' => $this->slug ?? Str::slug($this->title),
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'blog_category_id' => $this->blog_category_id,
            'published_at' => $publishedAt,
            'is_published' => $this->is_published,
        ];

        // Handle image upload
        if ($this->imageFile) {
            if ($this->isEditing && $this->image_path) {
                Storage::disk('public')->delete($this->image_path);
            }
            $data['image_path'] = $this->imageFile->store('blog_images', 'public');
        }

        // Ensure unique slug, even if manually entered.
        $originalSlug = $data['slug'];
        $i = 1;
        while (BlogPost::where('slug', $data['slug'])
            ->where('id', '!=', $this->blogPostId)
            ->exists()
        ) {
            $data['slug'] = $originalSlug . '-' . $i++;
        }

        if ($this->isEditing) {
            $post = BlogPost::find($this->blogPostId);
            $post->update($data);
            $post->tags()->sync($this->selectedTags); // Sync tags
            session()->flash('message', 'Blog post updated successfully!');
        } else {
            $post = BlogPost::create($data);
            $post->tags()->sync($this->selectedTags); // Sync tags
            session()->flash('message', 'Blog post created successfully!');
        }

        $this->dispatch('blogPostSaved');
        return $this->redirect(route('blog.post.index'), navigate: true);
    }

    public function removeImage()
    {
        if ($this->image_path) {
            Storage::disk('public')->delete($this->image_path);
            $this->image_path = null;
            $this->imageFile = null;
            session()->flash('message', 'Image removed successfully!');
        }
    }

    public function render()
    {
        return view('livewire.blog-post.manage');
    }
}
