<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Manage Images for "{{ $product->name }}"</h3>
        </div>
        <div class="card-body">
            @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if (session()->has('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <h5 class="mb-3">Upload New Images</h5>
            <form wire:submit.prevent="uploadImages" class="mb-4 p-3 border rounded">
                <div class="mb-3">
                    <label for="newImages" class="form-label">Select Images (multiple allowed)</label>
                    <input type="file" class="form-control @error('newImages.*') is-invalid @enderror" id="newImages" wire:model="newImages" multiple accept="image/*">
                    <div wire:loading wire:target="newImages" class="text-info mt-2">Uploading preview...</div>
                    @error('newImages.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="uploadImages" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Upload Images
                    </button>
                </div>
            </form>

            <h5 class="mb-3">Existing Images (Drag to reorder)</h5>

            @if ($existingImages->isEmpty())
            <p class="text-muted">No images uploaded yet.</p>
            @else
            <!-- Parent Container -->
            <!-- 'animation' adds a smooth slide effect when swapping -->
            <div 
    class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3"
    x-data
    x-init="
        Sortable.create($el, {
            animation: 150,
            handle: '.drag-handle', // Class selector for the handle
            onEnd: function (evt) {
                // Get the ordered IDs
                let orderedIds = Array.from($el.children).map(child => child.getAttribute('data-id'));
                
                // Send to Livewire
                $wire.updateImageSortOrder(orderedIds);
            }
        })
    "
>
    @foreach ($existingImages as $image)
        <!-- Add data-id so JS can read it -->
        <div data-id="{{ $image->id }}" wire:key="image-{{ $image->id }}" class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <!-- Add the handle class here -->
                    <button type="button" class="btn btn-secondary btn-sm drag-handle" style="cursor: move;">
                        <i class="fas fa-arrows-alt"></i> Move
                    </button>
                    
                    <!-- Other content -->
                    <img src="{{ asset('storage/' . $image->image_path) }}" class="img-fluid mt-2">
                </div>
            </div>
        </div>
    @endforeach
</div>
            @endif
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('product.products.edit', $product->id) }}" class="btn btn-secondary">Back to Product Details</a>
        </div>
    </div>
</div>

@push('scripts')
<!-- 1. Core SortableJS Library -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<!-- 2. Livewire Adapter -->
<script src="https://cdn.jsdelivr.net/npm/@nextapps-be/livewire-sortablejs@latest/dist/livewire-sortablejs.min.js"></script>
@endpush