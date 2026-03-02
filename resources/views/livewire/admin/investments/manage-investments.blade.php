<div class="py-4">
    <h2 class="mb-4">Investment Management</h2>

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
            <h5 class="mb-0">Investment List</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#investment-modal" wire:click="create">
                <i class="fas fa-plus"></i> Add New Investment
            </button>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4 mb-2 mb-md-0">
                    <input type="text" class="form-control" placeholder="Search by investor, project, amount, status..." wire:model.live.debounce.300ms="search">
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
                            <th wire:click="sortBy('investor_profile_id')" style="cursor: pointer;">
                                Investor
                                @if ($sortField == 'investor_profile_id')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('project_id')" style="cursor: pointer;">
                                Project
                                @if ($sortField == 'project_id')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('amount')" style="cursor: pointer;">
                                Amount
                                @if ($sortField == 'amount')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('investment_date')" style="cursor: pointer;">
                                Date
                                @if ($sortField == 'investment_date')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('status')" style="cursor: pointer;">
                                Status
                                @if ($sortField == 'status')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th>ROI</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($investments as $investment)
                        <tr>
                            <td>{{ $investment->investorProfile->user->name ?? 'N/A' }}</td>
                            <td>{{ $investment->project->title ?? 'N/A' }}</td>
                            <td>{{ $investment->formatted_amount }}</td>
                            <td>{{ $investment->investment_date?->format('Y-m-d') ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{
                                    $investment->status == 'funded' ? 'bg-success' :
                                    ($investment->status == 'committed' ? 'bg-info' :
                                    ($investment->status == 'pending' ? 'bg-warning' :
                                    ($investment->status == 'returned' ? 'bg-primary' :
                                    ($investment->status == 'failed' ? 'bg-danger' : 'bg-secondary'))))
                                }}">
                                    {{ ucfirst($investment->status) }}
                                </span>
                            </td>
                            <td>{{ $investment->return_on_investment ? $investment->return_on_investment . '%' : 'N/A' }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" wire:click="edit({{ $investment->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="confirm('Are you sure you want to delete this investment?') || event.stopImmediatePropagation()" wire:click="delete({{ $investment->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No investments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $investments->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Investment Create/Edit Modal -->
    <div class="modal fade" id="investment-modal" tabindex="-1" aria-labelledby="investmentModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="investmentModalLabel">{{ $isEditing ? 'Edit Investment' : 'Create New Investment' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="cancel"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="investor_profile_id" class="form-label">Investor <span class="text-danger">*</span></label>
                            <select class="form-select form-control @error('investor_profile_id') is-invalid @enderror" id="investor_profile_id" wire:model.defer="investor_profile_id">
                                <option value="">Select Investor</option>
                                @foreach($investorProfiles as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('investor_profile_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="project_id" class="form-label">Project (Optional)</label>
                            <select class="form-select form-control @error('project_id') is-invalid @enderror" id="project_id" wire:model.defer="project_id">
                                <option value="">Select Project</option>
                                @foreach($projects as $id => $title)
                                <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" wire:model.defer="amount">
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('currency') is-invalid @enderror" id="currency" wire:model.defer="currency">
                                @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="investment_date" class="form-label">Investment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('investment_date') is-invalid @enderror" id="investment_date" wire:model.defer="investment_date">
                            @error('investment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select form-control @error('status') is-invalid @enderror" id="status" wire:model.defer="status">
                                <option value="pending">Pending</option>
                                <option value="committed">Committed</option>
                                <option value="funded">Funded</option>
                                <option value="returned">Returned</option>
                                <option value="failed">Failed</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="return_on_investment" class="form-label">Return on Investment (%)</label>
                            <input type="number" step="0.01" class="form-control @error('return_on_investment') is-invalid @enderror" id="return_on_investment" wire:model.defer="return_on_investment">
                            @error('return_on_investment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" rows="3" wire:model.defer="notes"></textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ $isEditing ? 'Update Investment' : 'Create Investment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
