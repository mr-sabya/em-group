<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>{{ $pageTitle }}</h3>
        <a href="{{ route('users.vendors.index') }}" wire:navigate class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Vendors
        </a>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form wire:submit.prevent="saveVendor">
                <h4>User Account Details</h4>
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.live="name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model.defer="email">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password @unless($isEditing)<span class="text-danger">*</span>@endunless</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" wire:model.defer="password" autocomplete="new-password">
                                    @unless($isEditing)<small class="form-text text-muted">Minimum 8 characters.</small>@endunless
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password @unless($isEditing)<span class="text-danger">*</span>@endunless</label>
                                    <input type="password" class="form-control" id="password_confirmation" wire:model.defer="password_confirmation" autocomplete="new-password">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_phone" class="form-label">Phone (User Account)</label>
                                    <input type="text" class="form-control @error('user_phone') is-invalid @enderror" id="user_phone" wire:model.defer="user_phone">
                                    @error('user_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="avatar" class="form-label">User Avatar</label>
                            <div class="image-preview mb-2">
                                @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="upload-image">
                                @elseif ($currentAvatar)
                                <img src="{{ asset('storage/' . $currentAvatar) }}" alt="Current User Avatar" class="upload-image">
                                @endif
                            </div>
                            <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" wire:model.live="avatar">
                            <small class="form-text text-muted">Max 1MB. Accepted formats: JPG, PNG, GIF. Recommended: square image.</small>
                            @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <hr class="my-4">

                <h4>Vendor Shop Details</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="shop_name" class="form-label">Shop Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('shop_name') is-invalid @enderror" id="shop_name" wire:model.live="shop_name">
                            @error('shop_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="shop_slug" class="form-label">Shop URL Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('shop_slug') is-invalid @enderror" id="shop_slug" wire:model.defer="shop_slug" placeholder="Auto-generated from shop name">
                            <small class="form-text text-muted">This will be part of your shop's URL. e.g., yourwebsite.com/shops/{{ $shop_slug ?: 'your-shop-name' }}</small>
                            @error('shop_slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="shop_description" class="form-label">Shop Description</label>
                    <textarea class="form-control @error('shop_description') is-invalid @enderror" id="shop_description" rows="3" wire:model.defer="shop_description"></textarea>
                    @error('shop_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="shop_logo" class="form-label">Shop Logo</label>
                            <div class="image-preview mb-2">
                                @if ($shop_logo)
                                <img src="{{ $shop_logo->temporaryUrl() }}" class="upload-image">
                                @elseif ($currentShopLogo)
                                <img src="{{ asset('storage/' . $currentShopLogo) }}" alt="Current Shop Logo" class="upload-image">
                                @endif
                            </div>
                            <input type="file" class="form-control @error('shop_logo') is-invalid @enderror" id="shop_logo" wire:model.live="shop_logo">
                            <small class="form-text text-muted">Max 1MB. Recommended: square image.</small>
                            @error('shop_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="shop_banner" class="form-label">Shop Banner</label>
                            <div class="image-preview mb-2">
                                @if ($shop_banner)
                                <img src="{{ $shop_banner->temporaryUrl() }}" class="upload-image-lg">
                                @elseif ($currentShopBanner)
                                <img src="{{ asset('storage/' . $currentShopBanner) }}" alt="Current Shop Banner" class="upload-image-lg">
                                @endif
                            </div>
                            <input type="file" class="form-control @error('shop_banner') is-invalid @enderror" id="shop_banner" wire:model.live="shop_banner">
                            <small class="form-text text-muted">Max 2MB. Recommended: wide rectangular image for banner.</small>
                            @error('shop_banner') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vendor_email" class="form-label">Shop Email</label>
                            <input type="email" class="form-control @error('vendor_email') is-invalid @enderror" id="vendor_email" wire:model.defer="vendor_email">
                            @error('vendor_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vendor_phone" class="form-label">Shop Phone</label>
                            <input type="text" class="form-control @error('vendor_phone') is-invalid @enderror" id="vendor_phone" wire:model.defer="vendor_phone">
                            @error('vendor_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <h5>Shop Location Information</h5>
                <div class="mb-3">
                    <label for="address" class="form-label">Street Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" wire:model.defer="address"></textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="country_id" class="form-label">Country</label>
                            <select class="form-select @error('country_id') is-invalid @enderror" id="country_id" wire:model.live="country_id">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                            @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="state_id" class="form-label">State</label>
                            <select class="form-select @error('state_id') is-invalid @enderror" id="state_id" wire:model.live="state_id" @unless($country_id) disabled @endunless>
                                <option value="">Select State</option>
                                @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                            @error('state_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="city_id" class="form-label">City</label>
                            <select class="form-select @error('city_id') is-invalid @enderror" id="city_id" wire:model.defer="city_id" @unless($state_id) disabled @endunless>
                                <option value="">Select City</option>
                                @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                            @error('city_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="zip_code" class="form-label">Zip Code</label>
                            <input type="text" class="form-control @error('zip_code') is-invalid @enderror" id="zip_code" wire:model.defer="zip_code">
                            @error('zip_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <h5 class="mt-4">Banking & Commission Details</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" wire:model.defer="bank_name">
                            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bank_account_number" class="form-label">Bank Account Number</label>
                            <input type="text" class="form-control @error('bank_account_number') is-invalid @enderror" id="bank_account_number" wire:model.defer="bank_account_number">
                            @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bank_account_holder" class="form-label">Bank Account Holder Name</label>
                            <input type="text" class="form-control @error('bank_account_holder') is-invalid @enderror" id="bank_account_holder" wire:model.defer="bank_account_holder">
                            @error('bank_account_holder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="commission_rate" class="form-label">Commission Rate (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control @error('commission_rate') is-invalid @enderror" id="commission_rate" wire:model.defer="commission_rate">
                    <small class="form-text text-muted">The percentage of each sale the platform takes from this vendor.</small>
                    @error('commission_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('is_approved') is-invalid @enderror" type="checkbox" id="is_approved" wire:model.defer="is_approved">
                            <label class="form-check-label ms-2" for="is_approved">Is Approved (Vendor Profile)</label>
                            @error('is_approved') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active" wire:model.defer="is_active">
                            <label class="form-check-label ms-2" for="is_active">Is Active (User Account & Shop)</label>
                            @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('users.vendors.index') }}" wire:navigate class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="saveVendor" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ $isEditing ? 'Update Vendor Profile' : 'Create Vendor Profile' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>