<div class="container mt-4 pb-5">
    @if (session()->has('message')) <div class="alert alert-success border-0 shadow-sm">{{ session('message') }}</div> @endif
    @if (session()->has('error')) <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div> @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Variants: {{ $product->name }}</h5>
            <button wire:click="saveVariants" wire:loading.attr="disabled" class="btn btn-sm btn-success fw-bold px-4">
                <span wire:loading.remove wire:target="saveVariants">SAVE CHANGES</span>
                <span wire:loading wire:target="saveVariants">SAVING...</span>
            </button>
        </div>

        <div class="card-body">
            <!-- 1. Attribute Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold">Step 1: Choose Attributes</label>
                <select wire:model.live="selectedAttributeIds" class="form-select" multiple size="3">
                    @foreach ($availableAttributes as $attribute)
                    <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Value Management -->
            @if(count($selectedAttributeIds) > 0)
            <div class="mb-4 p-3 bg-light rounded border">
                @foreach ($selectedAttributeIds as $attrId)
                @php $attr = $availableAttributes->find($attrId); @endphp
                @if($attr)
                <div class="mb-3 border-bottom pb-2">
                    <span class="text-uppercase small fw-bold text-muted">{{ $attr->name }}</span>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        @foreach ($attributeValuesToManage[$attrId] ?? [] as $val)
                        <span class="badge bg-white border text-dark d-flex align-items-center p-2 shadow-sm">
                            {{ is_array($val) ? $val['value'] : $val->value }}
                            <button wire:click="removeAttributeValue({{ $attrId }}, {{ is_array($val) ? $val['id'] : $val->id }})" class="btn-close ms-2" style="font-size: 0.6rem;"></button>
                        </span>
                        @endforeach
                    </div>
                    <div class="input-group input-group-sm mt-2" style="max-width: 300px;">
                        <input type="text" class="form-control" wire:model="newAttributeValueNames.{{ $attrId }}" placeholder="Add value...">
                        <button class="btn btn-success" wire:click="addAttributeValue({{ $attrId }})">Add</button>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif

            <!-- 3. Table -->
            <!-- 3. Variant Table -->
            @php $computedVariants = $this->computedVariants; @endphp
            @if(count($computedVariants) > 0)
            <div class="table-responsive mt-4">
                <table class="table table-sm table-hover align-middle border">
                    <thead class="table-dark">
                        <tr>
                            <th>Combination</th>
                            <th>Status</th> <!-- New Column -->
                            <th style="width: 220px;">SKU</th>
                            <th style="width: 120px;">Price</th>
                            <th style="width: 100px;">Qty</th>
                            <th class="text-center">Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($computedVariants as $v)
                        @php
                        // Add a light green background for items already in DB
                        $rowClass = $v['exists'] ? 'table-success-light' : '';
                        @endphp
                        <tr wire:key="row-{{ $v['key'] }}" class="{{ $rowClass }}">
                            <td>
                                <div class="fw-bold">{{ $v['display_name'] }}</div>
                            </td>
                            <td>
                                @if($v['exists'])
                                <span class="badge bg-success shadow-sm">
                                    <i class="bi bi-check-circle-fill me-1"></i> Already Added
                                </span>
                                @else
                                <span class="badge bg-warning text-dark shadow-sm">
                                    <i class="bi bi-plus-circle-fill me-1"></i> New / Pending
                                </span>
                                @endif
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" wire:model="variantSku.{{ $v['key'] }}">
                                @error('variantSku.'.$v['key']) <small class="text-danger">{{ $message }}</small> @enderror
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" wire:model="variantPrice.{{ $v['key'] }}">
                                </div>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" wire:model="variantQuantity.{{ $v['key'] }}">
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input type="checkbox" class="form-check-input" wire:model="variantIsActive.{{ $v['key'] }}">
                                </div>
                            </td>
                            <td class="text-end">
                                @if($v['exists'])
                                <button type="button" wire:click="deleteVariant({{ $v['key'] }})"
                                    wire:confirm="This variant is saved in the database. Are you sure you want to delete it permanently?"
                                    class="btn btn-sm btn-outline-danger border-0">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <style>
                /* Custom style to differentiate existing rows slightly */
                .table-success-light {
                    background-color: rgba(25, 135, 84, 0.05) !important;
                }
            </style>
            @endif
        </div>
    </div>
</div>