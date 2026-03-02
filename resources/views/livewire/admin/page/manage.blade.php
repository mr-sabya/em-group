<div class="py-4">
    <h2 class="mb-4">{{ $page->exists ? 'Edit Page' : 'Create New Page' }}</h2>

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
                <div class="card mb-3">
                    <div class="card-header">
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
                            <label for="slug" class="form-label">Slug</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" wire:model.live="slug" placeholder="page-slug-url">
                                <button class="btn btn-outline-secondary" type="button" wire:click="generateSlug">Generate</button>
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Content (Editor) -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <!-- Assuming you have a Quill editor component similar to Product -->
                            <livewire:quill-text-editor wire:model.live="content" theme="snow" />
                            @error('content') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="card">
                    <div class="card-header">
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
                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">Meta Keywords</label>
                            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" wire:model="meta_keywords" placeholder="keyword1, keyword2">
                            @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings & Image -->
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="m-0">Publishing</h5>
                    </div>
                    <div class="card-body">
                        <!-- Is Active -->
                        <div class="form-check form-switch border p-2 rounded mb-3">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="is_active" wire:model="is_active">
                            <label class="form-check-label" for="is_active">Is Active</label>
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" wire:model="sort_order">
                            <small class="text-muted">Lower numbers appear first.</small>
                            @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Template -->
                        <div class="mb-3">
                            <label for="template" class="form-label">Template</label>
                            <select class="form-select @error('template') is-invalid @enderror" id="template" wire:model="template">
                                @foreach($templates as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('template') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Banner Image -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">Banner Image</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="image-preview mb-2 text-center border rounded p-2 bg-light">
                                @if ($new_banner_image)
                                <img src="{{ $new_banner_image->temporaryUrl() }}" class="img-fluid" style="max-height: 200px;">
                                @elseif ($banner_image_path)
                                <img src="{{ asset('storage/' . $banner_image_path) }}" class="img-fluid" style="max-height: 200px;">
                                @else
                                <span class="text-muted">No Image Selected</span>
                                @endif
                            </div>
                            <input type="file" class="form-control @error('new_banner_image') is-invalid @enderror" id="new_banner_image" wire:model="new_banner_image">
                            <div wire:loading wire:target="new_banner_image" class="text-info small mt-1">Uploading...</div>
                            @error('new_banner_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
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