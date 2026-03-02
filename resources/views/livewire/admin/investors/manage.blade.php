<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>{{ $pageTitle }}</h3>
        <a href="{{ route('users.investors.index') }}" wire:navigate class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Investors
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
            <form wire:submit.prevent="saveInvestor">
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
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" wire:model.defer="phone">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="avatar" class="form-label">User Avatar</label>
                            <div class="image-preview">
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

                <h4>Investor Profile Details</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" wire:model.defer="company_name">
                            @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="investment_focus" class="form-label">Investment Focus</label>
                            <input type="text" class="form-control @error('investment_focus') is-invalid @enderror" id="investment_focus" wire:model.defer="investment_focus">
                            <small class="form-text text-muted">e.g., Tech Startups, Real Estate, Ecommerce</small>
                            @error('investment_focus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="website" class="form-label">Website</label>
                    <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" wire:model.defer="website">
                    @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <h5>Contact Person Information</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="contact_person_name" class="form-label">Contact Person Name</label>
                            <input type="text" class="form-control @error('contact_person_name') is-invalid @enderror" id="contact_person_name" wire:model.defer="contact_person_name">
                            @error('contact_person_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="contact_person_email" class="form-label">Contact Person Email</label>
                            <input type="email" class="form-control @error('contact_person_email') is-invalid @enderror" id="contact_person_email" wire:model.defer="contact_person_email">
                            @error('contact_person_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="contact_person_phone" class="form-label">Contact Person Phone</label>
                            <input type="text" class="form-control @error('contact_person_phone') is-invalid @enderror" id="contact_person_phone" wire:model.defer="contact_person_phone">
                            @error('contact_person_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <h5>Location Information</h5>
                <div class="mb-3">
                    <label for="address" class="form-label">Street Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" wire:model.defer="address"></textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="country_id" class="form-label">Country</label>
                            <select class="form-select form-control @error('country_id') is-invalid @enderror" id="country_id" wire:model.live="country_id">
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
                            <select class="form-select form-control @error('state_id') is-invalid @enderror" id="state_id" wire:model.live="state_id" @unless($country_id) disabled @endunless>
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
                            <select class="form-select form-control @error('city_id') is-invalid @enderror" id="city_id" wire:model.defer="city_id" @unless($state_id) disabled @endunless>
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



                <h5>Investment Preferences</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="min_investment_amount" class="form-label">Minimum Investment Amount</label>
                            <input type="number" step="0.01" class="form-control @error('min_investment_amount') is-invalid @enderror" id="min_investment_amount" wire:model.defer="min_investment_amount">
                            @error('min_investment_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="max_investment_amount" class="form-label">Maximum Investment Amount</label>
                            <input type="number" step="0.01" class="form-control @error('max_investment_amount') is-invalid @enderror" id="max_investment_amount" wire:model.defer="max_investment_amount">
                            @error('max_investment_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" rows="3" wire:model.defer="notes"></textarea>
                    <small class="form-text text-muted">Internal notes about this investor.</small>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('is_approved') is-invalid @enderror" type="checkbox" id="is_approved" wire:model.defer="is_approved">
                            <label class="form-check-label ms-2" for="is_approved">Is Approved (Investor Profile)</label>
                            @error('is_approved') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active" wire:model.defer="is_active">
                            <label class="form-check-label ms-2" for="is_active">Is Active (User Account)</label>
                            @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('users.investors.index') }}" wire:navigate class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="saveInvestor" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ $isEditing ? 'Update Investor Profile' : 'Create Investor Profile' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>