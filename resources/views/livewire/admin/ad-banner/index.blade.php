<div class="row">
    @foreach(['home-banner-1', 'home-banner-2', 'home-banner-3'] as $slug)
    <div class="col-md-4">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-title text-uppercase">{{ str_replace('-', ' ', $slug) }}</div>
            </div>
            <div class="card-body">
                <div class="mb-3 text-center">
                    @if(isset($new_images[$slug]))
                    <img src="{{ $new_images[$slug]->temporaryUrl() }}" class="img-fluid rounded" style="height: 150px; object-fit: cover;">
                    @else
                    <img src="{{ asset('storage/' . $banners[$slug]['image_path']) }}" class="img-fluid rounded" style="height: 150px; object-fit: cover;">
                    @endif
                </div>

                <div class="form-group mb-2">
                    <label>Change Image</label>
                    <input type="file" wire:model="new_images.{{ $slug }}" class="form-control">
                </div>

                <div class="form-group mb-2">
                    <label>Title</label>
                    <input type="text" wire:model="banners.{{ $slug }}.title" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label>Link (URL)</label>
                    <input type="text" wire:model="banners.{{ $slug }}.link" class="form-control">
                </div>

                <button wire:click="save('{{ $slug }}')" class="btn btn-primary w-100" wire:loading.attr="disabled">
                    <span wire:loading.remove>Update Banner</span>
                    <span wire:loading><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>