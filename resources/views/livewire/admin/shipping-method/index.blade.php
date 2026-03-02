<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Shipping Management</h3>
        <button wire:click="openMethodModal" class="btn btn-primary">Add New Method</button>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search methods...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive bg-white shadow-sm rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th wire:click="sortBy('name')" style="cursor:pointer">Method Name</th>
                    <th>Rules (Cost)</th> <!-- Match the width here -->
                    <th>Payments</th>
                    <th>Default</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($methods as $method)
                <tr>
                    <td>
                        <strong>{{ $method->name }}</strong><br>
                        <small class="text-muted">{{ $method->slug }}</small>
                    </td>
                    <td>
                        @foreach($method->rules as $rule)
                        <div class="badge bg-light text-dark border mb-1 d-inline-block w-100 d-flex justify-content-between align-items-center" style="font-size: 13px;">
                            {{ $rule->city?->name ?? $rule->state?->name ?? $rule->country->name }}: ৳{{ $rule->cost }}
                            <div>
                                <i wire:click="openRuleModal({{ $method->id }}, {{ $rule->id }})" class="fas fa-edit ms-1 text-primary cursor-pointer" style="cursor: pointer;"></i>
                                <i wire:click="deleteRule({{ $rule->id }})" wire:confirm="Delete rule?" class="fas fa-times ms-1 text-danger cursor-pointer" style="cursor: pointer;"></i>
                            </div>
                        </div>
                        @endforeach
                        <button wire:click="openRuleModal({{ $method->id }})" class="btn btn-sm btn-link p-0 d-block">+ Add Rule</button>
                    </td>
                    <td>
                        @forelse($method->paymentMethods as $pm)
                        <span class="badge bg-info text-white">{{ $pm->name }}</span>
                        @empty
                        <span class="text-muted small">None Assigned</span>
                        @endforelse
                        <button wire:click="openPaymentModal({{ $method->id }})" class="btn btn-sm btn-link p-0 d-block">Manage Payments</button>
                    </td>
                    <td>{!! $method->is_default ? '<span class="badge bg-success">Yes</span>' : '-' !!}</td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:click="toggleStatus({{ $method->id }})" {{ $method->status ? 'checked' : '' }}>
                        </div>
                    </td>
                    <td class="text-end">
                        <button wire:click="openMethodModal({{ $method->id }})" class="btn btn-sm btn-outline-secondary">Edit</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center p-4">No methods found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 1. Method Detail Modal -->
    @if($showMethodModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Shipping Method Details</h5>
                    <button wire:click="$set('showMethodModal', false)" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Method Name</label>
                        <input type="text" wire:model="name" class="form-control">
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" wire:model="is_default" class="form-check-input" id="def">
                        <label for="def">Set as Default</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" wire:model="status" class="form-check-input" id="sta">
                        <label for="sta">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="saveMethod" class="btn btn-primary w-100">Save Method</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 2. Payment Assignment Modal -->
    @if($showPaymentModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Payment Methods</h5>
                    <button wire:click="$set('showPaymentModal', false)" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Select which payment options are available for this shipping method:</p>
                    <div class="list-group">
                        @foreach($all_payment_methods as $pm)
                        <label class="list-group-item">
                            <input class="form-check-input me-1" type="checkbox" value="{{ $pm->id }}" wire:model="selected_payment_methods">
                            {{ $pm->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="savePaymentMethods" class="btn btn-success w-100">Update Assignments</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 3. Shipping Rule Modal -->
    @if($showRuleModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Shipping Rule</h5>
                    <button wire:click="$set('showRuleModal', false)" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Country</label>
                        <select wire:model.live="country_id" class="form-select">
                            <option value="">Select Country</option>
                            @foreach($countries as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>State (Optional)</label>
                        <select wire:model.live="state_id" class="form-select" wire:key="state-{{ $country_id }}">
                            <option value="">All States</option>
                            @foreach($states as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>City (Optional)</label>
                        <select wire:model.live="city_id" class="form-select" wire:key="city-{{ $state_id }}">
                            <option value="">All Cities</option>
                            @foreach($cities as $ct) <option value="{{ $ct->id }}">{{ $ct->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Cost (৳)</label>
                        <input type="number" wire:model="cost" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="saveRule" class="btn btn-primary w-100">Save Rule</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>