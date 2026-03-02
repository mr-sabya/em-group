<div class="py-4">
    <h2 class="mb-4">Manage Countries</h2>

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

    <div class="row">
        {{-- Left Column: Form --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $isEditing ? 'Edit Country' : 'Create New Country' }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="saveCountry">
                        <div class="mb-3">
                            <label for="name" class="form-label">Country Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.blur="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="code" class="form-label">Country Code (e.g., USA)</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" wire:model.blur="code" maxlength="3">
                            <small class="form-text text-muted">Max 3 characters for ISO code.</small>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            @if ($isEditing)
                            <button type="button" class="btn btn-secondary" wire:click="resetForm">Cancel Edit</button>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading wire:target="saveCountry" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ $isEditing ? 'Update Country' : 'Create Country' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right Column: Table --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Existing Countries</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-6 col-lg-4 mb-2 mb-md-0">
                            <input type="text" class="form-control" placeholder="Search by name or code..." wire:model.live.debounce.300ms="search">
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
                                    <th wire:click="sortBy('name')" style="cursor: pointer;">
                                        Name
                                        @if ($sortField == 'name')
                                        <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }} ms-1"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('code')" style="cursor: pointer;">
                                        Code
                                        @if ($sortField == 'code')
                                        <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }} ms-1"></i>
                                        @endif
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($countries as $country)
                                <tr>
                                    <td>{{ $country->name }}</td>
                                    <td>{{ $country->code ?? 'N/A' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" wire:click="editCountry({{ $country->id }})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="confirm('Are you sure you want to delete \'{{ $country->name }}\'? This will also delete any associated states and cities. This action cannot be undone.') || event.stopImmediatePropagation()" wire:click="deleteCountry({{ $country->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No countries found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $countries->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>