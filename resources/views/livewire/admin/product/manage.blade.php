<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Product Management</h2>
        <a href="{{ route('product.products.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm">
            <i class="ri-arrow-left-line"></i> Back to List
        </a>
    </div>

    <form wire:submit.prevent="save">
        <div class="row">
            <!-- Left Column: Primary Content -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card mb-4 ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model.live="name" placeholder="Enter product name">
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Slug</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" wire:model="slug">
                                    <button class="btn btn-outline-secondary" type="button" wire:click="generateSlug">Auto</button>
                                </div>
                                @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">SKU</label>
                                <input type="text" class="form-control" wire:model="sku" placeholder="Unique SKU Code">
                                @error('sku') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Short Description</label>
                            <textarea class="form-control" wire:model="short_description" rows="3"></textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Long Description</label>
                            <livewire:quill-text-editor wire:model.live="long_description" />
                        </div>
                    </div>
                </div>

                <!-- Pricing Section -->
                <div class="card mb-4 ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0">Pricing Detail (৳)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Regular Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control border-primary" wire:model="regular_price">
                                @error('regular_price') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Sale Price</label>
                                <input type="number" step="0.01" class="form-control" wire:model="sale_price">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Purchase Price</label>
                                <input type="number" step="0.01" class="form-control" wire:model="purchase_price">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-0">
                                <label class="form-label fw-bold">Retail Price</label>
                                <input type="number" step="0.01" class="form-control" wire:model="retail_price">
                            </div>
                            <div class="col-md-6 mb-0">
                                <label class="form-label fw-bold">Distributor Price</label>
                                <input type="number" step="0.01" class="form-control" wire:model="distributor_price">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Specifications & Inventory -->
                <div class="card mb-4 ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0">Inventory & Shipping</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Manage Stock</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model.live="is_manage_stock">
                                    <label class="form-check-label ms-2">Enable stock tracking</label>
                                </div>
                            </div>
                            @if($is_manage_stock)
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Current Quantity</label>
                                <input type="number" class="form-control" wire:model="quantity">
                                @error('quantity') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Weight</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" wire:model="weight">
                                    <select class="form-select" wire:model="weight_unit" style="max-width: 150px;">
                                        @foreach($weightUnits as $unit)
                                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('weight') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Volume</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" wire:model="volume">
                                    <select class="form-select" wire:model="volume_unit" style="max-width: 150px;">
                                        @foreach($volumeUnits as $unit)
                                        <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('volume') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Min Order Qty</label>
                                <input type="number" class="form-control" wire:model="min_order_quantity">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Max Order Qty</label>
                                <input type="number" class="form-control" wire:model="max_order_quantity" placeholder="∞">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar -->
            <div class="col-lg-4">
                <!-- Organization & Status -->
                <div class="card mb-4 ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0">Status & Organization</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Product Type</label>
                            <select class="form-select" wire:model.live="type">
                                @foreach($productTypes as $typeCase)
                                <option value="{{ $typeCase->value }}">{{ $typeCase->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4 d-flex flex-column gap-3 border p-3 rounded bg-light">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="is_active" id="active">
                                <label class="form-check-label fw-bold" for="active">Active (Visible)</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="is_featured" id="featured">
                                <label class="form-check-label fw-bold" for="featured">Featured Product</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="is_new" id="newArrival">
                                <label class="form-check-label fw-bold" for="newArrival">New Arrival Badge</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Brand</label>
                            <select class="form-select" wire:model="brand_id">
                                <option value="">No Brand</option>
                                @foreach($brands_list as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        
                    </div>
                </div>

                <!-- Media -->
                <div class="card mb-4 ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0">Product Image</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <x-image-preview
                            model="new_thumbnail_image"
                            :image="$new_thumbnail_image"
                            :existing="$thumbnail_image_path" />
                    </div>
                </div>

                <!-- Categories -->
                <div class="card ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0">Categories <span class="text-danger small">*</span></h5>
                    </div>
                    <div class="card-body">
                        <div class="overflow-auto border rounded p-2" style="max-height: 250px;">
                            @foreach($categories_list as $category)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    value="{{ $category->id }}"
                                    id="cat-{{ $category->id }}"
                                    wire:model="selectedCategoryIds">
                                <label class="form-check-label" for="cat-{{ $category->id }}">
                                    {{ $category->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('selectedCategoryIds') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Bar -->
        <div class="mt-4 p-3 bg-white border rounded  d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light border px-4">Cancel</button>
            <button type="submit" class="btn btn-primary px-5">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                Save Product
            </button>
        </div>
    </form>
</div>