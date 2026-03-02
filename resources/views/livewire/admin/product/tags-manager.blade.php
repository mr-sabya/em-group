<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Manage Tags for "{{ $product->name }}"</h3>
        </div>
        <div class="card-body">
            @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @error('newTag')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            <h5 class="mb-3">Current Tags</h5>
            <div class="mb-4">
                @forelse ($currentTags as $tag)
                <span class="badge bg-info text-dark fs-6 p-2 me-2 mb-2">
                    {{ $tag->name }}
                    <button type="button" class="btn-close btn-close-white ms-1" wire:click="removeTag({{ $tag->id }})" aria-label="Remove"></button>
                </span>
                @empty
                <p class="text-muted">No tags currently attached to this product.</p>
                @endforelse
            </div>

            <h5 class="mb-3">Add New or Existing Tags</h5>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="searchTag" class="form-label">Search for Existing Tag</label>
                    <input type="text" class="form-control" id="searchTag" wire:model.live="searchTag" placeholder="Start typing a tag name...">
                    @if (!empty($suggestedTags) && strlen($searchTag) > 2)
                    <ul class="list-group mt-2">
                        @foreach ($suggestedTags as $tag)
                        <li class="list-group-item list-group-item-action" wire:click="addTag({{ $tag->id }})">{{ $tag->name }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="newTag" class="form-label">Create New Tag</label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('newTag') is-invalid @enderror" id="newTag" wire:model="newTag" placeholder="Enter new tag name">
                        <button class="btn btn-outline-primary" type="button" wire:click="createAndAddTag">
                            <span wire:loading wire:target="createAndAddTag" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Create & Add
                        </button>
                        @error('newTag') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('product.products.edit', $product->id) }}" class="btn btn-secondary">Back to Product Details</a>
        </div>
    </div>
</div>