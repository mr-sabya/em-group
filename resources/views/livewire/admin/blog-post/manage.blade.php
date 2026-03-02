<div class="py-4">
    <h2 class="mb-4">{{ $isEditing ? 'Edit Blog Post' : 'Create New Blog Post' }}</h2>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ $isEditing ? 'Edit Post Details' : 'New Post Details' }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="savePost">
                <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" wire:model.blur="title">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" wire:model.blur="slug">
                    <small class="form-text text-muted">Leave empty to auto-generate from title. Manual slugs will also be made unique.</small>
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Image Upload Field --}}
                <div class="mb-3">
                    <label for="imageFile" class="form-label">Post Image</label>
                    <div class="image-preview mb-2">
                        @if ($imageFile)
                        <img src="{{ $imageFile->temporaryUrl() }}" class="upload-image img-thumbnail" style="max-height: 150px;">
                        @elseif ($image_path)
                        <img src="{{ Storage::url($image_path) }}" alt="{{ $title ?? 'Blog Post Image' }}" class="upload-image img-thumbnail" style="max-height: 150px;">
                        @endif
                    </div>
                    @if ($image_path)
                    <button type="button" class="btn btn-sm btn-outline-danger mb-2" wire:click="removeImage">Remove Current Image</button>
                    @endif
                    <input type="file" class="form-control @error('imageFile') is-invalid @enderror" id="imageFile" wire:model.live="imageFile">
                    <small class="form-text text-muted">Max 1MB. Accepted formats: JPG, PNG, GIF, WebP.</small>
                    @error('imageFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div wire:loading wire:target="imageFile">Uploading...</div>
                </div>

                <div class="mb-3">
                    <label for="blog_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select @error('blog_category_id') is-invalid @enderror" id="blog_category_id" wire:model="blog_category_id">
                        <option value="">Select a Category</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('blog_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Tags Multi-select Field --}}
                <div class="mb-3">
                    <label for="selectedTags" class="form-label">Tags</label>
                    <select class="form-select @error('selectedTags') is-invalid @enderror" id="selectedTags" wire:model="selectedTags" multiple>
                        <option value="">Select Tags</option>
                        @foreach ($allTags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple tags.</small>
                    @error('selectedTags') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="excerpt" class="form-label">Excerpt</label>
                    <textarea class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" wire:model.blur="excerpt" rows="3"></textarea>
                    <small class="form-text text-muted">A short summary of the post.</small>
                    @error('excerpt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('content') is-invalid @enderror" id="content" wire:model.blur="content" rows="10"></textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="published_at_date" class="form-label">Publish Date</label>
                        <input type="date" class="form-control @error('published_at_date') is-invalid @enderror" id="published_at_date" wire:model.blur="published_at_date">
                        @error('published_at_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="published_at_time" class="form-label">Publish Time</label>
                        <input type="time" class="form-control @error('published_at_time') is-invalid @enderror" id="published_at_time" wire:model.blur="published_at_time">
                        @error('published_at_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_published" wire:model.live="is_published">
                    <label class="form-check-label" for="is_published">Publish Now</label>
                    <small class="form-text text-muted">If unchecked, the post will remain in draft mode.</small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('blog.post.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="savePost" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ $isEditing ? 'Update Post' : 'Create Post' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>