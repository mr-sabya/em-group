<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 h4">{{ $collectionId ? 'Edit Collection: ' . $title : 'Create New Collection' }}</h2>
            <p class="text-muted small mb-0">Active Store: <strong>{{ $this->currentTenant->name }}</strong></p>
        </div>
        <a href="{{ route('collection.index') }}" class="btn btn-outline-secondary btn-sm" wire:navigate>
            <i class="fas fa-arrow-left me-1"></i> Back to Collections
        </a>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <form wire:submit.prevent="saveCollection">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label fw-bold">Collection Identifier (Slug/Name) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.blur="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">A unique identifier for this store, e.g., "winter-collection-2024"</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label fw-bold">Display Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" wire:model.blur="title">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-bold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" rows="3" wire:model.defer="description"></textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="featured_price" class="form-label fw-bold">Featured Price</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">৳</span>
                            <input type="number" step="0.01" class="form-control @error('featured_price') is-invalid @enderror" id="featured_price" wire:model.defer="featured_price">
                        </div>
                        @error('featured_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tag" class="form-label fw-bold">Tag (e.g., "SALE", "HOT")</label>
                        <input type="text" class="form-control @error('tag') is-invalid @enderror" id="tag" wire:model.defer="tag" placeholder="Optional">
                        @error('tag') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="category_id" class="form-label fw-bold">Store Category</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" wire:model.defer="category_id">
                            <option value="">-- No Category --</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Collection Thumbnail</label>
                        <div class="mb-3 border rounded p-3 bg-light d-flex flex-column align-items-center justify-content-center" style="min-height: 200px;">
                            @if ($imageFile)
                            <img src="{{ $imageFile->temporaryUrl() }}" class="img-thumbnail shadow-sm mb-2" style="max-height: 150px;">
                            @elseif ($image_path)
                            <img src="{{ Storage::url($image_path) }}" alt="{{ $image_alt ?? 'Collection' }}" class="img-thumbnail shadow-sm mb-2" style="max-height: 150px;">
                            @else
                            <div class="text-muted text-center">
                                <i class="fas fa-image fa-3x mb-2 opacity-25"></i>
                                <p class="small mb-0">No image uploaded yet</p>
                            </div>
                            @endif
                            <input type="file" class="form-control mt-3 @error('imageFile') is-invalid @enderror" id="imageFile" wire:model.live="imageFile">
                            @error('imageFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div wire:loading wire:target="imageFile" class="text-primary small mt-2">Uploading image...</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="image_alt" class="form-label fw-bold">Image Alt Text (SEO)</label>
                            <input type="text" class="form-control @error('image_alt') is-invalid @enderror" id="image_alt" wire:model.defer="image_alt" placeholder="Description for search engines">
                            @error('image_alt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="display_order" class="form-label fw-bold">Display Priority</label>
                            <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order" wire:model.defer="display_order" min="0">
                            @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check form-switch p-0 ms-4">
                            <input class="form-check-input" type="checkbox" id="is_active" wire:model.defer="is_active">
                            <label class="form-check-label fw-bold ms-2" for="is_active">
                                Show in Store (Active)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('collection.index') }}" class="btn btn-light border px-4" wire:navigate>Cancel</a>
                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                        <span wire:loading wire:target="saveCollection" class="spinner-border spinner-border-sm me-2"></span>
                        {{ $collectionId ? 'Update Collection' : 'Create Collection' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>