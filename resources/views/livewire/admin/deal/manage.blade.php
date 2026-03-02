<div class="py-4">
    <h2 class="mb-4">{{ $dealId ? 'Edit Deal: ' . $name : 'Create New Deal' }}</h2>

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
            <h5 class="mb-0">{{ $dealId ? 'Edit Deal Details' : 'New Deal Details' }}</h5>
            <a href="{{ route('deal.index') }}" class="btn btn-secondary" wire:navigate>
                <i class="fas fa-arrow-left"></i> Back to Deals
            </a>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="saveDeal">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Deal Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.defer="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="type" class="form-label">Discount Type</label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" wire:model.defer="type">
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="value" class="form-label">Discount Value</label>
                        <input type="number" step="0.01" class="form-control @error('value') is-invalid @enderror" id="value" wire:model.defer="value">
                        @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <livewire:quill-text-editor
                        wire:model.live="description"
                        theme="snow" />
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="link_target" class="form-label">Link Target (e.g., /deals, /category/sale)</label>
                    <input type="text" class="form-control @error('link_target') is-invalid @enderror" id="link_target" wire:model.defer="link_target" placeholder="/deals">
                    @error('link_target') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Banner Image Upload Field (adapted from your provided snippet) --}}
                <div class="mb-3">
                    <label for="imageFile" class="form-label">Deal Banner Image</label> {{-- Changed to imageFile --}}
                    <div class="image-preview mb-2">
                        {{-- Show temporary URL for new upload, or current path for existing --}}
                        @if ($imageFile)
                        <img src="{{ $imageFile->temporaryUrl() }}" class="upload-image img-thumbnail" style="max-height: 150px;">
                        @elseif ($banner_image_path)
                        <img src="{{ asset('storage/' . $banner_image_path) }}" alt="Current Banner Image" class="upload-image img-thumbnail" style="max-height: 150px;">
                        @endif
                    </div>
                    <input type="file" class="form-control @error('imageFile') is-invalid @enderror" id="imageFile" wire:model.live="imageFile">
                    <small class="form-text text-muted">Max 1MB. Accepted formats: JPG, PNG, GIF, WebP.</small>
                    @error('imageFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div wire:loading wire:target="imageFile">Uploading...</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="starts_at" class="form-label">Starts At</label>
                        <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at" wire:model.defer="starts_at">
                        @error('starts_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="expires_at" class="form-label">Expires At</label>
                        <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" id="expires_at" wire:model.defer="expires_at">
                        @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" wire:model.defer="is_active">
                            <label class="form-check-label" for="is_active">
                                Is Active
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_featured" wire:model.defer="is_featured">
                            <label class="form-check-label" for="is_featured">
                                Is Featured (for prominent display)
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="display_order" class="form-label">Display Order</label>
                        <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order" wire:model.defer="display_order" min="0">
                        @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Product Search and Selection --}}
                <h6 class="mt-4 mb-3">Associate Products</h6>
                <div class="mb-3">
                    <label for="productSearch" class="form-label">Search & Add Products</label>
                    <div class="position-relative" wire:click.outside="hideProductSearchResults">
                        <input
                            type="text"
                            class="form-control"
                            id="productSearch"
                            wire:model.live.debounce.300ms="productSearch"
                            wire:keydown.escape="showProductSearchResults = false"
                            wire:focus="showProductSearchResults = true" {{-- Show on focus --}}
                            placeholder="Start typing product name...">
                        @if ($showProductSearchResults && !empty($productSearchResults))
                        <ul class="list-group position-absolute w-100 mt-1 z-index-1000 shadow-lg">
                            @foreach ($productSearchResults as $product)
                            <li class="list-group-item list-group-item-action cursor-pointer" wire:click="selectProductForDeal({{ $product['id'] }})">
                                {{ $product['name'] }} ({{ number_format($product['price'], 2) }})
                            </li>
                            @endforeach
                            @if (count($productSearchResults) >= 10)
                            <li class="list-group-item text-muted">More results available... keep typing.</li>
                            @endif
                        </ul>
                        @elseif ($showProductSearchResults && empty($productSearchResults) && strlen($productSearch) >= 3)
                        <ul class="list-group position-absolute w-100 mt-1 z-index-1000 shadow-lg">
                            <li class="list-group-item text-muted">No products found for "{{ $productSearch }}"</li>
                        </ul>
                        @endif
                    </div>
                </div>

                <div class="mt-3">
                    <h6>Selected Products:</h6>
                    @if (count($this->selectedProductModels) > 0)
                    <ul class="list-group">
                        @foreach ($this->selectedProductModels as $product)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $product->name }} (Price: {{ number_format($product->price, 2) }})
                            <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeProductFromDeal({{ $product->id }})">
                                <i class="fas fa-times"></i>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-muted">No products associated with this deal yet.</p>
                    @endif
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="saveDeal, imageFile" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ $dealId ? 'Update Deal' : 'Create Deal' }}
                    </button>
                    <a href="{{ route('deal.index') }}" class="btn btn-secondary" wire:navigate>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>