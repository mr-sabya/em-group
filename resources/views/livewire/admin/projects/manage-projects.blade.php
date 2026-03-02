<div>
    <div class="py-4">
        <h2 class="mb-4">Manage Projects</h2>

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
                        <h5 class="mb-0">{{ $isEditing ? 'Edit Project' : 'Create New Project' }}</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveProject">
                            <div class="mb-3">
                                <label for="name" class="form-label">Project Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.blur="name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" wire:model.blur="description" rows="3"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="target_amount" class="form-label">Target Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('target_amount') is-invalid @enderror" id="target_amount" wire:model.blur="target_amount">
                                @error('target_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model.blur="status">
                                    @foreach($availableStatuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                @if ($isEditing)
                                <button type="button" class="btn btn-secondary" wire:click="resetForm">Cancel Edit</button>
                                @endif
                                <button type="submit" class="btn btn-primary">
                                    <span wire:loading wire:target="saveProject" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ $isEditing ? 'Update Project' : 'Create Project' }}
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
                        <h5 class="mb-0">Existing Projects</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6 col-lg-4 mb-2 mb-md-0">
                                <input type="text" class="form-control" placeholder="Search by name, description or status..." wire:model.live.debounce.300ms="search">
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
                                        <th wire:click="sortBy('target_amount')" style="cursor: pointer;">
                                            Target Amount
                                            @if ($sortField == 'target_amount')
                                            <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }} ms-1"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('status')" style="cursor: pointer;">
                                            Status
                                            @if ($sortField == 'status')
                                            <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }} ms-1"></i>
                                            @endif
                                        </th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($projects as $project)
                                    <tr>
                                        <td>
                                            <strong>{{ $project->name }}</strong>
                                            @if($project->description)
                                            <br><small class="text-muted">{{ Str::limit($project->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ number_format($project->target_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{
                                                $project->status == 'pending' ? 'secondary' :
                                                ($project->status == 'active' ? 'primary' :
                                                ($project->status == 'funded' ? 'success' :
                                                ($project->status == 'closed' ? 'info' :
                                                ($project->status == 'cancelled' ? 'danger' : 'light'))))
                                            }}">
                                                {{ $availableStatuses[$project->status] ?? $project->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" wire:click="editProject({{ $project->id }})" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="confirm('Are you sure you want to delete \'{{ $project->name }}\'? This action cannot be undone.') || event.stopImmediatePropagation()" wire:click="deleteProject({{ $project->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No projects found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $projects->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>