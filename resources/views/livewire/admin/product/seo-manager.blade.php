<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Manage SEO for "{{ $product->name }}"</h3>
        </div>
        <div class="card-body">
            @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form wire:submit.prevent="saveSeo">
                <div class="mb-3">
                    <label for="seo_title" class="form-label">SEO Title (Meta Title)</label>
                    <input type="text" class="form-control @error('seo_title') is-invalid @enderror" id="seo_title" wire:model.live="seo_title" placeholder="Catchy title for search engines">
                    <div class="form-text">Character count: {{ strlen($seo_title ?? '') }} / 160</div>
                    @error('seo_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="seo_description" class="form-label">SEO Description (Meta Description)</label>
                    <textarea class="form-control @error('seo_description') is-invalid @enderror" id="seo_description" wire:model.live="seo_description" rows="4" placeholder="Brief, compelling summary for search results"></textarea>
                    <div class="form-text">Character count: {{ strlen($seo_description ?? '') }} / 300</div>
                    @error('seo_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="saveSeo" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Update SEO
                    </button>
                </div>
            </form>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('product.products.edit', $product->id) }}" class="btn btn-secondary">Back to Product Details</a>
        </div>
    </div>
</div>