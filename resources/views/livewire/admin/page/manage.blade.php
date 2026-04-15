<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $page->exists ? 'Edit Page' : 'Create New Page' }}</h2>
        <a href="{{ route('page.index') }}" class="btn btn-outline-secondary" wire:navigate>
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="row">
            <!-- Left Column: Main Content -->
            <div class="col-md-8">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="m-0">Page Content</h5>
                    </div>
                    <div class="card-body">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Page Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" wire:model.live="title" placeholder="Enter page title">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Slug -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" wire:model.live="slug" placeholder="page-slug-url">
                                <button class="btn btn-outline-secondary" type="button" wire:click="generateSlug">Generate</button>
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <small class="text-muted">Unique identifier in URL.</small>
                        </div>

                        <!-- Content (Editor) -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <livewire:quill-text-editor wire:model.live="content" theme="snow" />
                            @error('content') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="m-0">SEO Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input type="text" class="form-control @error('meta_title') is-invalid @enderror" id="meta_title" wire:model="meta_title">
                            @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" wire:model="meta_description" rows="3"></textarea>
                            @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" wire:model="meta_keywords" placeholder="keyword1, keyword2">
                                @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="meta_robots" class="form-label">Meta Robots</label>
                                <input type="text" class="form-control @error('meta_robots') is-invalid @enderror" id="meta_robots" wire:model="meta_robots" placeholder="index, follow">
                                @error('meta_robots') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings & Image -->
            <div class="col-md-4">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="m-0">Publishing</h5>
                    </div>
                    <div class="card-body">
                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model="status">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Published At -->
                        <div class="mb-3">
                            <label for="published_at" class="form-label">Published At</label>
                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" id="published_at" wire:model="published_at">
                            <small class="text-muted">Fill only to schedule for future.</small>
                            @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Page Type -->
                        <div class="mb-3">
                            <label for="page_type" class="form-label">Page Type</label>
                            <select class="form-select @error('page_type') is-invalid @enderror" id="page_type" wire:model="page_type">
                                <option value="landing">Landing Page</option>
                                <option value="blog">Blog Post</option>
                                <option value="custom">Custom Page</option>
                            </select>
                            @error('page_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- OG Image -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="m-0">OG Image (Social Share)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="image-preview mb-2 text-center border rounded p-2 bg-light">
                                @if ($new_og_image)
                                <img src="{{ $new_og_image->temporaryUrl() }}" class="img-fluid" style="max-height: 200px;">
                                @elseif ($og_image_path)
                                <img src="{{ asset('storage/' . $og_image_path) }}" class="img-fluid" style="max-height: 200px;">
                                @else
                                <span class="text-muted">No Image Selected</span>
                                @endif
                            </div>
                            <input type="file" class="form-control @error('new_og_image') is-invalid @enderror" id="new_og_image" wire:model="new_og_image">
                            <div wire:loading wire:target="new_og_image" class="text-info small mt-1">Uploading...</div>
                            @error('new_og_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ $page->exists ? 'Update Page' : 'Create Page' }}
                    </button>
                    <a href="{{ route('page.index') }}" class="btn btn-outline-secondary" wire:navigate>Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>