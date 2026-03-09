<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0">Attribute Value Management</h2>
            <small class="text-muted">Active Store: <strong>{{ $this->currentTenant->name ?? 'Default' }}</strong></small>
        </div>
        <button class="btn btn-primary shadow-sm" wire:click="createAttributeValue">
            <i class="fas fa-plus"></i> Add New Value
        </button>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-3">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3 mb-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Search values..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-8 d-flex justify-content-md-end gap-2">
                    <select wire:model.live="perPage" class="form-select w-auto">
                        <option value="10">10 Per Page</option>
                        <option value="25">25 Per Page</option>
                        <option value="50">50 Per Page</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th wire:click="sortBy('value')" role="button">Value @if($sortField=='value')<i class="fas fa-sort-{{$sortDirection=='asc'?'up':'down'}}"></i>@endif</th>
                            <th>Parent Attribute</th>
                            <th>Slug</th>
                            <th>Visual</th>
                            <th style="width: 120px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attributeValues as $attributeValue)
                        <tr wire:key="val-{{ $attributeValue->id }}">
                            <td class="small text-muted">#{{ $attributeValue->id }}</td>
                            <td class="fw-bold">{{ $attributeValue->value }}</td>
                            <td>
                                @if ($attributeValue->attribute)
                                <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $attributeValue->attribute->name }}</span>
                                @else
                                <span class="badge bg-light text-muted border">N/A</span>
                                @endif
                            </td>
                            <td><code class="small text-secondary">{{ $attributeValue->slug }}</code></td>
                            <td>
                                @if ($attributeValue->metadata)
                                @if ($attributeValue->attribute && $attributeValue->attribute->display_type == \App\Enums\AttributeDisplayType::Color)
                                <div class="rounded shadow-sm border" style="width: 24px; height: 24px; background-color: {{ $attributeValue->metadata['color'] ?? '#eee' }}"></div>
                                @elseif ($attributeValue->attribute && $attributeValue->attribute->display_type == \App\Enums\AttributeDisplayType::Image)
                                <img src="{{ Storage::url($attributeValue->metadata['image'] ?? '') }}" class="img-thumbnail p-0" style="width: 32px; height: 32px; object-fit: cover;">
                                @endif
                                @else
                                —
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info me-1 border-0" wire:click="editAttributeValue({{ $attributeValue->id }})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger border-0" wire:click="deleteAttributeValue({{ $attributeValue->id }})" wire:confirm="Are you sure?"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No values found for this store.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
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