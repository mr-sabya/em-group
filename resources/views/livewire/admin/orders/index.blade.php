<div>
    <!-- Header Section -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item text-muted small"><i class="fas fa-store me-1"></i> {{ $this->currentTenant->name ?? 'Store' }}</li>
                    <li class="breadcrumb-item active small" aria-current="page">Orders</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold  mb-0">Order Management</h1>
        </div>
        <div class="col-auto d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-white border shadow-sm dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown">
                    Bulk Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                    <li><button class="dropdown-item py-2" wire:click="bulkConfirm"><i class="fas fa-check-circle me-2 text-success opacity-75"></i> Mark as Confirmed</button></li>
                    <li><button class="dropdown-item py-2" wire:click="exportCSV"><i class="fas fa-file-export me-2 text-primary opacity-75"></i> Export Selected (CSV)</button></li>
                </ul>
            </div>
            <a class="btn btn-primary px-4 fw-bold shadow-sm border-0" href="{{ route('orders.create') }}" wire:navigate>
                <i class="fas fa-plus me-1"></i> Create Order
            </a>
        </div>
    </div>

    <!-- Status Navigation Tabs -->
    <div class="order-tabs-container mb-4">
        <div class="d-flex overflow-auto gap-2 pb-2">
            <button wire:click="setTab('all')"
                class="tab-pill {{ $activeTab == 'all' ? 'active' : '' }}">
                All <span class="count">{{ $counts['all'] }}</span>
            </button>
            @foreach($orderStatuses as $status)
            <button wire:click="setTab('{{ $status->value }}')"
                class="tab-pill {{ $activeTab == $status->value ? 'active' : '' }}">
                {{ $status->label() }}
                <span class="count">{{ $counts[$status->value] ?? 0 }}</span>
            </button>
            @endforeach
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group search-group">
                        <span class="input-group-text border-end-0 bg-transparent text-dark"><i class="fas fa-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="form-control border-start-0 ps-0 shadow-none"
                            placeholder="Search by name, phone or #order id...">
                    </div>
                </div>
                <div class="col-md-7 d-flex justify-content-md-end align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted text-nowrap font-medium">Items per page:</span>
                        <select wire:model.live="perPage" class="form-select form-select-sm border-light-subtle shadow-none w-auto">
                            <option value="10">10</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="40" class="ps-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input custom-check" wire:model.live="selectAll">
                            </div>
                        </th>
                        <th>Order Details</th>
                        <th>Customer</th>
                        <th>Source</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                    <tr class="{{ in_array($order->id, $selectedOrders) ? 'row-selected' : '' }}">
                        <td class="ps-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input custom-check" value="{{ $order->id }}" wire:model.live="selectedOrders">
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-primary mb-0" style="font-family: 'Monaco', 'Consolas', monospace;">#{{ $order->order_number }}</div>
                            <div class="text-muted small-text">{{ $order->placed_at->format('M d, Y • h:i A') }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $order->name }}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="text-muted small">{{ $order->phone }}</span>
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $order->phone) }}" target="_blank" class="whatsapp-link">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </td>
                        <td>
                            @php
                            $sourceConfig = match($order->source->value) {
                            'whatsapp' => ['icon' => 'fab fa-whatsapp', 'class' => 'source-whatsapp'],
                            'facebook' => ['icon' => 'fab fa-facebook', 'class' => 'source-facebook'],
                            'landing_page' => ['icon' => 'fas fa-desktop', 'class' => 'source-web'],
                            default => ['icon' => 'fas fa-globe', 'class' => 'source-default']
                            };
                            @endphp
                            <span class="source-badge {{ $sourceConfig['class'] }}">
                                <i class="{{ $sourceConfig['icon'] }} me-1"></i> {{ ucfirst(str_replace('_', ' ', $order->source->value)) }}
                            </span>
                        </td>
                        <td>
                            <div class="small">
                                <div class="fw-bold text-dark">৳{{ number_format($order->total_amount, 2) }}</div>
                                <div class="text-success small-text">Paid: ৳{{ number_format($order->paid_amount, 2) }}</div>
                            </div>
                        </td>
                        <td>
                            @php
                            $statusClass = match($order->status->value) {
                            'pending' => 'status-pending',
                            'confirmed' => 'status-confirmed',
                            'shipped' => 'status-shipped',
                            'delivered' => 'status-delivered',
                            'canceled', 'returned' => 'status-danger',
                            default => 'status-default'
                            };
                            @endphp
                            <span class="status-pill {{ $statusClass }}">
                                {{ $order->status->label() }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <button wire:click="openStatusUpdateModal({{ $order->id }})"
                                class="btn btn-icon btn-light"
                                data-bs-toggle="modal" data-bs-target="#statusModal">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-5 text-center">
                            <div class="py-4">
                                <i class="fas fa-box-open fa-3x text-light-emphasis mb-3"></i>
                                <h5 class="text-muted fw-normal">No orders found matching your criteria</h5>
                                <button wire:click="$set('search', '')" class="btn btn-link btn-sm text-decoration-none">Clear search filters</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    <!-- Status Modal (Customized) -->
    <div wire:ignore.self class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold px-2">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <small class="text-muted d-block mb-1">Changing status for</small>
                        <span class="fw-bold text-primary">Order #{{ $updateOrderId }}</span>
                    </div>
                    <label class="form-label small fw-bold text-uppercase text-muted mb-2">New Status</label>
                    <select class="form-select border-2 py-2 shadow-none" wire:model="newOrderStatus">
                        @foreach($orderStatuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light px-4 fw-semibold" data-bs-dismiss="modal">Close</button>
                    <button type="button" wire:click="updateOrderStatus" class="btn btn-primary px-4 fw-bold">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Professional CSS Overrides -->
    <style>
        /* Tabs Styling */
        .tab-pill {
            padding: 8px 16px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 100px;
            white-space: nowrap;
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-pill:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .tab-pill.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .tab-pill .count {
            background: rgba(0, 0, 0, 0.05);
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
        }

        .tab-pill.active .count {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</div>