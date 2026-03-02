<div class="py-4">
    <h2 class="mb-4">Coupon Management</h2>

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
            <h5 class="mb-0">Coupons List</h5>
            <a href="{{ route('product.coupons.create') }}" class="btn btn-primary" wire:navigate>
                <i class="fas fa-plus"></i> Add New Coupon
            </a>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4">
                    <input type="text" class="form-control" placeholder="Search coupons..." wire:model.live.debounce.300ms="search">
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
                <table class="table table-hover table-bordered mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th wire:click="sortBy('code')" role="button">Code
                                @if ($sortField == 'code')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th>Description</th>
                            <th wire:click="sortBy('type')" role="button">Type
                                @if ($sortField == 'type')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th>Value</th>
                            <th>Usage</th>
                            <th>Validity</th>
                            <th style="width: 100px;">Active</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->id }}</td>
                            <td>{{ $coupon->code }}</td>
                            <td>{{ Str::limit($coupon->description, 50) }}</td>
                            <td>{{ $coupon->type->label() }}</td>
                            <td>
                                @if($coupon->type == \App\Enums\CouponType::Percentage)
                                {{ number_format($coupon->value, 0) }}%
                                @elseif($coupon->type == \App\Enums\CouponType::FixedAmount)
                                ${{ number_format($coupon->value, 2) }}
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                {{ $coupon->usage_count }} / {{ $coupon->usage_limit_per_coupon ?? 'Unlimited' }}
                                <br>
                                @if($coupon->usage_limit_per_user)
                                (Per User: {{ $coupon->usage_limit_per_user }})
                                @endif
                            </td>
                            <td>
                                From: {{ $coupon->valid_from ? $coupon->valid_from->format('Y-m-d H:i') : 'N/A' }}
                                <br>
                                Until: {{ $coupon->valid_until ? $coupon->valid_until->format('Y-m-d H:i') : 'N/A' }}
                            </td>
                            <td>
                                @if ($coupon->is_active)
                                <span class="badge bg-success">Yes</span>
                                @else
                                <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton{{ $coupon->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $coupon->id }}">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('product.coupons.edit', $coupon->id) }}">
                                                <i class="fas fa-edit me-2"></i>Edit Coupon
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="#" wire:click.prevent="openProductAssignment({{ $coupon->id }})">
                                                <i class="fas fa-box-open me-2"></i>Assign Products
                                            </a></li>
                                        <li><a class="dropdown-item" href="#" wire:click.prevent="openCategoryAssignment({{ $coupon->id }})">
                                                <i class="fas fa-tags me-2"></i>Assign Categories
                                            </a></li>
                                        <li><a class="dropdown-item" href="#" wire:click.prevent="openUserAssignment({{ $coupon->id }})">
                                                <i class="fas fa-user-plus me-2"></i>Assign Users
                                            </a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><button type="button" class="dropdown-item text-danger" wire:click="deleteCoupon({{ $coupon->id }})" wire:confirm="Are you sure you want to delete this coupon? This cannot be undone.">
                                                <i class="fas fa-trash me-2"></i>Delete Coupon
                                            </button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No coupons found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $coupons->links() }}
            </div>
        </div>
    </div>


    {{-- Include the assignment modals --}}
    <livewire:coupon.product-assignment />
    <livewire:coupon.category-assignment />
    <livewire:coupon.user-assignment />
</div>