<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Order Management</h2>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Order #, TxID, Customer, Vendor..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-8 text-md-end mt-2 mt-md-0">
                    <select wire:model.live="perPage" class="form-select w-auto d-inline-block">
                        <option value="10">10 Per Page</option>
                        <option value="25">25 Per Page</option>
                        <option value="50">50 Per Page</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th wire:click="sortBy('order_number')" style="cursor:pointer">Order #</th>
                            <th>Customer</th>
                            <th>Vendor</th>
                            <th>Payment Info</th>
                            <th wire:click="sortBy('total_amount')" style="cursor:pointer">Amount</th>
                            <th>Status</th>
                            <th wire:click="sortBy('placed_at')" style="cursor:pointer">Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                        <tr>
                            <td class="fw-bold text-primary">{{ $order->order_number }}</td>
                            <td>
                                <div>{{ $order->user->name ?? $order->billing_first_name . ' ' . $order->billing_last_name . ' (Guest)' }}</div>
                                <small class="text-muted">{{ $order->billing_phone }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <i class="fas fa-store me-1"></i> {{ $order->vendor->name ?? 'System' }}
                                </span>
                            </td>
                            <td>
                                <div class="small fw-bold text-dark">{{ $order->payment_method_name }}</div>
                                @if($order->payment_phone_number)
                                <div class="small text-muted"><i class="fas fa-phone-alt me-1"></i>{{ $order->payment_phone_number }}</div>
                                @endif
                                @if($order->transaction_id)
                                <code class="text-danger border px-1 rounded" style="font-size: 0.75rem;">{{ $order->transaction_id }}</code>
                                @else
                                <small class="text-muted fst-italic">No TxID</small>
                                @endif
                            </td>
                            <td>৳{{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $order->order_status->badgeColor() }} d-block mb-1">
                                    Order: {{ $order->order_status->label() }}
                                </span>
                                <span class="badge {{ $order->payment_status->badgeColor() }} d-block">
                                    Payment: {{ $order->payment_status->label() }}
                                </span>
                            </td>
                            <td class="small">{{ $order->placed_at?->format('d M, Y h:i A') }}</td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#order-details-modal"
                                        wire:click="viewOrderDetails({{ $order->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-dark"
                                        data-bs-toggle="modal" data-bs-target="#status-update-modal"
                                        wire:click="openStatusUpdateModal({{ $order->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="{{ route('orders.manage', $order->id) }}" wire:navigate class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-cogs"></i>
                                    </a>
                                    <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No orders found matching your criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $orders->links() }}</div>
    </div>

    {{-- ORDER DETAILS MODAL --}}
    <div class="modal fade" id="order-details-modal" tabindex="-1" wire:ignore.self
        x-data="{ bootstrapModal: null }"
        x-init="bootstrapModal = new bootstrap.Modal($el)"
        @hidden-bs-modal.window="if($event.target.id === 'order-details-modal') $wire.closeOrderDetailsModal()">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div wire:loading wire:target="viewOrderDetails" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading data...</p>
                    </div>
                    <div wire:loading.remove wire:target="viewOrderDetails">
                        @if ($showOrderDetailsModal && $selectedOrderId)
                        <livewire:orders.manage :orderId="$selectedOrderId" :key="'order-'.$selectedOrderId" />
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- STATUS UPDATE MODAL --}}
    <div class="modal fade" id="status-update-modal" tabindex="-1" wire:ignore.self
        x-data="{ bootstrapModal: null }"
        x-init="bootstrapModal = new bootstrap.Modal($el)"
        @close-modal-now.window="bootstrapModal.hide()"
        @hidden-bs-modal.window="if($event.target.id === 'status-update-modal') $wire.closeStatusUpdateModal()">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="updateOrderStatus">
                    <div class="modal-body">
                        <div wire:loading wire:target="openStatusUpdateModal" class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                        <div wire:loading.remove wire:target="openStatusUpdateModal">
                            @if ($updateOrderId)
                            <div class="mb-3">
                                <label class="form-label">Order Status</label>
                                <select class="form-select @error('newOrderStatus') is-invalid @enderror" wire:model="newOrderStatus">
                                    @foreach($orderStatuses as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                @error('newOrderStatus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Status</label>
                                <select class="form-select @error('newPaymentStatus') is-invalid @enderror" wire:model="newPaymentStatus">
                                    @foreach($paymentStatuses as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                @error('newPaymentStatus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading wire:target="updateOrderStatus" class="spinner-border spinner-border-sm" role="status"></span>
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>