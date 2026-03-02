<div class="py-4">
    <h2 class="mb-4">{{ $isEditing ? 'Edit Coupon' : 'Create New Coupon' }}</h2>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $isEditing ? 'Edit Coupon Details' : 'New Coupon Details' }}</h5>
            <a href="{{ route('product.coupons.index') }}" class="btn btn-secondary" wire:navigate>
                <i class="fas fa-arrow-left"></i> Back to Coupons
            </a>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="saveCoupon">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" wire:model.live="code">
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Coupon Type <span class="text-danger">*</span></label>
                        <select class="form-select form-control @error('type') is-invalid @enderror" id="type" wire:model.defer="type">
                            @foreach($couponTypeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" rows="2" wire:model.defer="description"></textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="value" class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('value') is-invalid @enderror" id="value" wire:model.defer="value">
                        <small class="form-text text-muted">
                            @if($type === \App\Enums\CouponType::Percentage->value) Percentage (e.g., 10 for 10%) @else Fixed amount (e.g., 5.00) @endif
                        </small>
                        @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="min_spend" class="form-label">Minimum Spend</label>
                        <input type="number" step="0.01" class="form-control @error('min_spend') is-invalid @enderror" id="min_spend" wire:model.defer="min_spend">
                        <small class="form-text text-muted">Minimum amount required to use coupon.</small>
                        @error('min_spend') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="max_discount_amount" class="form-label">Maximum Discount Amount</label>
                        <input type="number" step="0.01" class="form-control @error('max_discount_amount') is-invalid @enderror" id="max_discount_amount" wire:model.defer="max_discount_amount">
                        <small class="form-text text-muted">Only for percentage coupons.</small>
                        @error('max_discount_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="usage_limit_per_coupon" class="form-label">Usage Limit Per Coupon</label>
                        <input type="number" class="form-control @error('usage_limit_per_coupon') is-invalid @enderror" id="usage_limit_per_coupon" wire:model.defer="usage_limit_per_coupon" min="1">
                        <small class="form-text text-muted">Total number of times this coupon can be used across all users (leave blank for unlimited).</small>
                        @error('usage_limit_per_coupon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="usage_limit_per_user" class="form-label">Usage Limit Per User</label>
                        <input type="number" class="form-control @error('usage_limit_per_user') is-invalid @enderror" id="usage_limit_per_user" wire:model.defer="usage_limit_per_user" min="1">
                        <small class="form-text text-muted">Number of times a single user can use this coupon (leave blank for unlimited).</small>
                        @error('usage_limit_per_user') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="valid_from" class="form-label">Valid From</label>
                        <input type="datetime-local" class="form-control @error('valid_from') is-invalid @enderror" id="valid_from" wire:model.defer="valid_from">
                        <small class="form-text text-muted">Date and time when the coupon becomes active.</small>
                        @error('valid_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="valid_until" class="form-label">Valid Until</label>
                        <input type="datetime-local" class="form-control @error('valid_until') is-invalid @enderror" id="valid_until" wire:model.defer="valid_until">
                        <small class="form-text text-muted">Date and time when the coupon expires (leave blank for no expiration).</small>
                        @error('valid_until') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3 form-check form-switch d-flex align-items-center">
                    <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active" wire:model.defer="is_active">
                    <label class="form-check-label ms-2" for="is_active">Is Active</label>
                    @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Removed the Applicability section entirely --}}

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('product.coupons.index') }}" wire:navigate class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="saveCoupon" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ $isEditing ? 'Update Coupon' : 'Create Coupon' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>