<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>{{ $pageTitle }}</h3>
        <a href="{{ route('users.customers.index') }}" wire:navigate class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Customers
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
            <form wire:submit.prevent="saveCustomer"> {{-- Changed method name --}}
                <div class="row">
                    <div class="col-lg-8">
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

                    <div class="col-lg-4">
                        <div class="mb-3">
                            <label for="avatar" class="form-label">Customer Avatar</label>
                            <div class="image-preview">
                                @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="upload-image">
                                @elseif ($currentAvatar)
                                <img src="{{ asset('storage/' . $currentAvatar) }}" alt="Current Customer Avatar" class="upload-image">
                                @endif
                            </div>
                            <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" wire:model.live="avatar">
                            <small class="form-text text-muted">Max 1MB. Accepted formats: JPG, PNG, GIF. Recommended: square image.</small>
                            @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        </div>
                    </div>



                </div>

                <hr>
                <h5>Contact & Location Information</h5>
                <div class="row">

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" wire:model.defer="address" rows="3"></textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
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
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="state_id" class="form-label">State / Province</label>
                            <select class="form-select form-control @error('state_id') is-invalid @enderror" id="state_id" wire:model.live="state_id" @if($states->isEmpty()) disabled @endif>
                                <option value="">Select State</option>
                                @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                            @error('state_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="city_id" class="form-label">City</label>
                            <select class="form-select form-control @error('city_id') is-invalid @enderror" id="city_id" wire:model.defer="city_id" @if($cities->isEmpty()) disabled @endif>
                                <option value="">Select City</option>
                                @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                            @error('city_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="zip_code" class="form-label">Zip Code</label>
                            <input type="text" class="form-control @error('zip_code') is-invalid @enderror" id="zip_code" wire:model.defer="zip_code">
                            @error('zip_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                </div>

                <hr>
                <h5>Customer Specific Settings</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" wire:model.defer="date_of_birth">
                            @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select form-control @error('gender') is-invalid @enderror" id="gender" wire:model.defer="gender">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" wire:model.defer="slug">
                            <small class="form-text text-muted">SEO-friendly URL identifier (e.g., `john-doe`).</small>
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center mb-3">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active" wire:model.defer="is_active">
                            <label class="form-check-label ms-2" for="is_active">Is Active</label>
                            @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>


                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('users.customers.index') }}" wire:navigate class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="saveCustomer" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ $isEditing ? 'Update Customer' : 'Create Customer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>