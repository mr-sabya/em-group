<div class="py-4">
    <h2 class="mb-4">{{ $collectionId ? 'Edit Collection: ' . $title : 'Create New Collection' }}</h2>

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

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $collectionId ? 'Edit Collection Details' : 'New Collection Details' }}</h5>
            <a href="{{ route('collection.index') }}" class="btn btn-secondary" wire:navigate>
                <i class="fas fa-arrow-left"></i> Back to Collections
            </a>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="saveCollection">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Collection Identifier (Slug/Name)</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.defer="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">A unique identifier, e.g., "sunglass-deals"</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Display Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" wire:model.defer="title">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" rows="3" wire:model.defer="description"></textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="featured_price" class="form-label">Featured Price</label>
                        <input type="number" step="0.01" class="form-control @error('featured_price') is-invalid @enderror" id="featured_price" wire:model.defer="featured_price">
                        @error('featured_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">A representative price for the collection.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tag" class="form-label">Tag (e.g., "SALE", "NEW")</label>
                        <input type="text" class="form-control @error('tag') is-invalid @enderror" id="tag" wire:model.defer="tag">
                        @error('tag') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select form-control @error('category_id') is-invalid @enderror" id="category_id" wire:model.defer="category_id">
                            <option value="">-- Select Category --</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Image Upload Field --}}
                <div class="mb-3">
                    <label for="imageFile" class="form-label">Collection Image</label>
                    <div class="image-preview">
                        @if ($imageFile)
                        <img src="{{ $imageFile->temporaryUrl() }}" class="upload-image img-thumbnail" style="max-height: 150px;">
                        @elseif ($image_path)
                        <img src="{{ Storage::url($image_path) }}" alt="{{ $image_alt ?? 'Collection Image' }}" class="upload-image img-thumbnail" style="max-height: 150px;">
                        @endif
                    </div>
                    <input type="file" class="form-control @error('imageFile') is-invalid @enderror" id="imageFile" wire:model.live="imageFile">
                    <small class="form-text text-muted">Max 1MB. Accepted formats: JPG, PNG, GIF, WebP.</small>
                    @error('imageFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div wire:loading wire:target="imageFile">Uploading...</div>
                </div>

                <div class="mb-3">
                    <label for="image_alt" class="form-label">Image Alt Text</label>
                    <input type="text" class="form-control @error('image_alt') is-invalid @enderror" id="image_alt" wire:model.defer="image_alt" placeholder="Descriptive text for the image">
                    @error('image_alt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" wire:model.defer="is_active">
                            <label class="form-check-label" for="is_active">
                                Is Active
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="display_order" class="form-label">Display Order</label>
                        <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order" wire:model.defer="display_order" min="0">
                        @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="saveCollection, imageFile" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ $collectionId ? 'Update Collection' : 'Create Collection' }}
                    </button>
                    <a href="{{ route('collection.index') }}" class="btn btn-secondary" wire:navigate>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>