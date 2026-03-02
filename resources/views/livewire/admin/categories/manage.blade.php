<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">{{ $pageTitle }}</h2>
        <a href="{{ route('product.categories.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm">
            <i class="ri-arrow-left-line"></i> Back to Categories
        </a>
    </div>

    <form wire:submit.prevent="saveCategory">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Category Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" wire:model.live="name">
                                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <!-- SLUG WITH BUTTON -->
                            <div class="col-md-6 mb-3">
                                <label for="slug" class="form-label fw-bold">Slug <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="slug" wire:model="slug">
                                    <button class="btn btn-outline-secondary" type="button" wire:click="generateSlug">Auto</button>
                                </div>
                                <small class="text-muted">SEO-friendly URL identifier.</small>
                                @error('slug') <div class="text-danger small d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="description" rows="4" wire:model="description"></textarea>
                        </div>

                        <div class="row align-items-center ">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Sort Order</label>
                                <input type="number" class="form-control" wire:model="sort_order">
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch pt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" wire:model="is_active">
                                    <label class="form-check-label fw-bold" for="is_active">Active</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch pt-4">
                                    <input class="form-check-input" type="checkbox" id="show_home" wire:model="show_on_homepage">
                                    <label class="form-check-label fw-bold" for="show_home">On Homepage</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Media & Parent</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Parent Category</label>
                            <select class="form-select" wire:model="parent_id">
                                <option value="">No Parent</option>
                                @foreach ($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <x-image-preview
                                    model="image"
                                    :image="$image"
                                    :existing="$currentImage"
                                    label="Image" />
                            </div>
                            <div class="col-6">
                                <x-image-preview
                                    model="icon" {{-- FIXED: Was model="image" --}}
                                    :image="$icon"
                                    :existing="$currentIcon"
                                    label="Icon" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">SEO Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">SEO Title</label>
                            <input type="text" class="form-control" wire:model="seo_title">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">SEO Description</label>
                            <textarea class="form-control" rows="3" wire:model="seo_description"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('product.categories.index') }}" class="btn btn-light border px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <span wire:loading wire:target="saveCategory" class="spinner-border spinner-border-sm me-1"></span>
                                {{ $isEditing ? 'Update Category' : 'Create Category' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>