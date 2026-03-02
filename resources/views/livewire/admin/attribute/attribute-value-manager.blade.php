<div class="py-4">
    <h2 class="mb-4">Attribute Value Management</h2>

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
            <h5 class="mb-0">Attribute Values List</h5>
            <button class="btn btn-primary" wire:click="createAttributeValue">
                <i class="fas fa-plus"></i> Add New Attribute Value
            </button>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4">
                    <input type="text" class="form-control" placeholder="Search attribute values..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-6 col-lg-8 d-flex justify-content-md-end justify-content-start mt-2 mt-md-0">
                    <div class="d-flex align-items-center gap-2">
                        <select wire:model.live="perPage" class="form-select w-auto">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-nowrap">Per Page</span>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th wire:click="sortBy('value')" role="button">Value
                                @if ($sortField == 'value')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th>Parent Attribute</th>
                            <th>Slug</th>
                            <th>Metadata</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attributeValues as $attributeValue)
                        <tr>
                            <td>{{ $attributeValue->id }}</td>
                            <td>{{ $attributeValue->value }}</td>
                            <td>
                                @if ($attributeValue->attribute)
                                <span class="badge bg-info">{{ $attributeValue->attribute->name }}</span>
                                @else
                                <span class="badge bg-warning">N/A</span>
                                @endif
                            </td>
                            <td>{{ $attributeValue->slug }}</td>
                            <td>
                                @if ($attributeValue->metadata)
                                @if ($attributeValue->attribute && $attributeValue->attribute->display_type == \App\Enums\AttributeDisplayType::Color && isset($attributeValue->metadata['color']))
                                <div style="width: 25px; height: 25px; background-color: {{ $attributeValue->metadata['color'] }}; border: 1px solid #ccc; display: inline-block; vertical-align: middle;" title="{{ $attributeValue->metadata['color'] }}"></div>
                                @elseif ($attributeValue->attribute && $attributeValue->attribute->display_type == \App\Enums\AttributeDisplayType::Image && isset($attributeValue->metadata['image']))
                                <img src="{{ Storage::url($attributeValue->metadata['image']) }}" alt="Image" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;">
                                @else
                                {{-- Fallback for other metadata types or if display type is not recognized --}}
                                <pre class="mb-0 small" style="max-height: 80px; overflow-y: auto;">{{ json_encode($attributeValue->metadata, JSON_PRETTY_PRINT) }}</pre>
                                @endif
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info me-1" wire:click="editAttributeValue({{ $attributeValue->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" wire:click="deleteAttributeValue({{ $attributeValue->id }})" wire:confirm="Are you sure you want to delete this attribute value?" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No attribute values found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $attributeValues->links() }}
            </div>
        </div>
    </div>

    <!-- Attribute Value Create/Edit Modal -->
    <div class="modal fade {{ $showModal ? 'show d-block' : '' }}" id="attributeValueModal" tabindex="-1" role="dialog" aria-labelledby="attributeValueModalLabel" aria-hidden="{{ !$showModal }}" @if($showModal) style="background-color: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attributeValueModalLabel">{{ $isEditing ? 'Edit Attribute Value' : 'Create New Attribute Value' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveAttributeValue">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="attribute_id" class="form-label">Parent Attribute <span class="text-danger">*</span></label>
                            <select class="form-select form-control @error('attribute_id') is-invalid @enderror" id="attribute_id" wire:model.live="attribute_id">
                                <option value="">Select an Attribute</option>
                                @foreach($availableAttributes as $attr)
                                <option value="{{ $attr->id }}">{{ $attr->name }} ({{ $attr->display_type->label() }})</option>
                                @endforeach
                            </select>
                            @error('attribute_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="value" class="form-label">Value <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('value') is-invalid @enderror" id="value" wire:model.live="value">
                            @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" wire:model.defer="slug">
                            <small class="form-text text-muted">Unique URL-friendly identifier for this value (e.g., `red`, `large`).</small>
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Dynamic Metadata Fields based on selected Parent Attribute's display type --}}
                        @php
                        $selectedAttribute = $attribute_id ? \App\Models\Attribute::find($attribute_id) : null;
                        @endphp

                        @if($selectedAttribute && $selectedAttribute->display_type == \App\Enums\AttributeDisplayType::Color)
                        <div class="mb-3">
                            <label for="metadataColor" class="form-label">Color Code</label>
                            <input type="color" class="form-control form-control-color @error('metadataColor') is-invalid @enderror" id="metadataColor" wire:model.defer="metadataColor" title="Choose your color">
                            <small class="form-text text-muted">Enter hex color code (e.g., #FF0000).</small>
                            @error('metadataColor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @elseif($selectedAttribute && $selectedAttribute->display_type == \App\Enums\AttributeDisplayType::Image)
                        <div class="mb-3">
                            <label for="metadataImage" class="form-label">Image Upload</label>
                            <input type="file" class="form-control @error('metadataImage') is-invalid @enderror" id="metadataImage" wire:model.live="metadataImage">
                            <small class="form-text text-muted">Max 1MB. Accepted formats: JPG, PNG, GIF.</small>
                            @error('metadataImage') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            @if ($metadataImage)
                            <p class="mt-2">New Image Preview:</p>
                            <img src="{{ $metadataImage->temporaryUrl() }}" class="img-thumbnail" style="max-width: 150px;">
                            @elseif ($currentMetadataImage)
                            <p class="mt-2">Current Image:</p>
                            <img src="{{ Storage::url($currentMetadataImage) }}" alt="Current Image" class="img-thumbnail" style="max-width: 150px;">
                            @endif
                        </div>
                        @endif

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading wire:target="saveAttributeValue" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ $isEditing ? 'Update Value' : 'Create Value' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>