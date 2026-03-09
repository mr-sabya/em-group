<div class="">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 fw-bold mb-0">Orders</h2>
            <!-- Added Tenant Indicator -->
            <small class="text-muted">Managing for: <strong class="text-info">{{ $this->currentTenant->name ?? 'Unknown Store' }}</strong></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Avatars -->
            <div class="avatar-group d-flex me-3">
                <span class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center border-white" style="width:32px; height:32px; font-size:12px; border:2px solid #fff;">J</span>
                <span class="avatar bg-danger text-white rounded-circle d-flex align-items-center justify-content-center border-white" style="width:32px; height:32px; font-size:12px; margin-left:-10px; border:2px solid #fff;">R</span>
                <span class="avatar bg-info text-white rounded-circle d-flex align-items-center justify-content-center border-white" style="width:32px; height:32px; font-size:12px; margin-left:-10px; border:2px solid #fff;">S</span>
                <span class="avatar bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center border-white" style="width:32px; height:32px; font-size:12px; margin-left:-10px; border:2px solid #fff;">+1</span>
            </div>
            <button class="btn btn-white border shadow-sm btn-sm px-2"><i class="fas fa-sliders-h text-info"></i></button>

            <!-- ACTION DROPDOWN -->
            <div class="dropdown">
                <button class="btn btn-outline-info btn-sm px-4 dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">Action</button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 mt-2" style="min-width: 240px; font-size: 14px;">
                    <li><a class="dropdown-item py-2 text-muted opacity-50" href="#"><i class="fas fa-exchange-alt me-2"></i> Change Status</a></li>
                    <li><button class="dropdown-item py-2" wire:click="exportCSV"><i class="fas fa-print me-2"></i> Print Invoice</button></li>
                    <li><button class="dropdown-item py-2" wire:click="exportCSV"><i class="fas fa-file-csv me-2"></i> Export As CSV</button></li>
                    <li><a class="dropdown-item py-2" href="#"><i class="fas fa-file-export me-2"></i> Export Summary</a></li>
                    <li><a class="dropdown-item py-2" href="#"><i class="fas fa-upload me-2"></i> Upload Orders</a></li>
                    <li class="border-bottom pb-2 mb-2"><a class="dropdown-item py-2 text-muted opacity-50" href="#"><i class="far fa-calendar-alt me-2"></i> Update Shipping Date</a></li>

                    <li class="px-3 fw-bold text-dark d-flex align-items-center"><i class="fas fa-angle-double-right me-2 text-secondary" style="font-size: 18px;"></i> Fast Action</li>
                    <li class="px-2 mt-1">
                        <button class="btn btn-secondary w-100 text-white py-2 shadow-sm border-0 fw-bold d-flex align-items-center justify-content-center"
                            wire:click="approveSelected" wire:confirm="Approve all selected orders?" style="background-color: #ccc;">
                            <i class="far fa-check-circle me-2 fs-5"></i> Approve Order(s)
                        </button>
                    </li>
                </ul>
            </div>
            <a href="#" wire:navigate class="btn btn-info text-white shadow-sm btn-sm px-3 fw-bold">Create Order</a>
        </div>
    </div>

    <!-- 10 Status Tabs -->
    <div class="d-flex flex-nowrap gap-3 mb-3 border-bottom pb-2 overflow-auto no-scrollbar bg-white p-2 rounded shadow-sm">
        <div class="d-flex align-items-center cursor-pointer px-2" wire:click="setTab('all')">
            <span class="{{ $activeTab == 'all' ? 'text-info fw-bold border-bottom border-info border-2 pb-1' : 'text-secondary' }}">All Orders</span>
            <span class="badge bg-secondary bg-opacity-10 text-dark ms-2">{{ $counts['all'] }}</span>
        </div>
        @foreach($orderStatuses as $status)
        <div class="d-flex align-items-center cursor-pointer px-2" wire:click="setTab('{{ $status->value }}')">
            <span class="{{ $activeTab == $status->value ? 'text-info fw-bold border-bottom border-info border-2 pb-1' : 'text-secondary' }}">{{ $status->label() }}</span>
            <span class="badge {{ $activeTab == $status->value ? 'bg-info text-white' : 'bg-secondary bg-opacity-10 text-dark' }} ms-2">{{ $counts[$status->value] }}</span>
        </div>
        @endforeach
    </div>

    <!-- Duplicate Alert -->
    <div class="alert bg-warning bg-opacity-10 border-warning border-opacity-25 d-flex align-items-center py-2 mb-3 shadow-sm">
        <input type="checkbox" wire:model.live="filterDuplicatePhones" class="form-check-input me-2">
        <span class="small text-dark">Multiple orders <span class="badge bg-info">0</span> with the same phone number <span class="badge bg-warning text-dark">0</span></span>
    </div>

    <!-- Toolbar -->
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2 align-items-center">
                <button class="btn btn-light border btn-sm shadow-sm" wire:click="$refresh"><i class="fas fa-sync-alt"></i></button>

                <!-- FILTER COLUMN DROPDOWN -->
                <div class="dropdown">
                    <button class="btn btn-light border btn-sm dropdown-toggle px-3 shadow-sm" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="fas fa-sliders-h me-1 text-info"></i> Filter Column
                    </button>
                    <div class="dropdown-menu shadow border-0 p-3" style="min-width: 220px;">
                        <h6 class="dropdown-header ps-0 text-dark fw-bold mb-2">Columns</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input custom-check" type="checkbox" id="col_all" wire:model.live="selectAllColumns">
                            <label class="form-check-label fw-bold" for="col_all">Select All</label>
                        </div>
                        @foreach(['invoice_no' => 'Invoice No', 'date' => 'Date', 'customer' => 'Customer', 'pickup_address' => 'Pick Up Address', 'payment_info' => 'Payments Info', 'delivery_partner' => 'Delivery Partner', 'delivery_fee' => 'Delivery Fee', 'internal_notes' => 'Internal Notes'] as $key => $label)
                        <div class="form-check mb-1">
                            <input class="form-check-input custom-check" type="checkbox" id="col_{{ $key }}" wire:model.live="columns.{{ $key }}">
                            <label class="form-check-label" for="col_{{ $key }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <button class="btn btn-info text-white btn-sm px-3 fw-bold shadow-sm">Picking</button>
                <span class="small text-muted ms-2">Show</span>
                <select wire:model.live="perPage" class="form-select form-select-sm w-auto">
                    <option value="10">10</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="small">{{ $orders->links() }}</div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th width="40" class="ps-3"><input type="checkbox" class="form-check-input" wire:model.live="selectAll"></th>
                        @if($columns['invoice_no']) <th>Invoice No</th> @endif
                        @if($columns['date']) <th>Date</th> @endif
                        @if($columns['customer']) <th>Customer</th> @endif
                        @if($columns['pickup_address']) <th>Pick Up Address</th> @endif
                        @if($columns['payment_info']) <th>Payments Info</th> @endif
                        @if($columns['delivery_partner']) <th>Delivery Partner</th> @endif
                        @if($columns['delivery_fee']) <th>Delivery Fee</th> @endif
                        @if($columns['internal_notes']) <th class="text-center">Notes</th> @endif
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse ($orders as $order)
                    <tr class="border-bottom {{ in_array($order->id, $selectedOrders) ? 'table-warning bg-opacity-10' : '' }}">
                        <td class="ps-3"><input type="checkbox" class="form-check-input" value="{{ $order->id }}" wire:model.live="selectedOrders"></td>

                        @if($columns['invoice_no'])
                        <td>
                            <div class="d-flex gap-2 mb-1 text-muted opacity-50">
                                <i class="fas fa-info-circle cursor-pointer" title="Details"></i>
                                <i class="fas fa-copy cursor-pointer" onclick="navigator.clipboard.writeText('{{ $order->order_number }}')"></i>
                                <i class="fas fa-print"></i>
                                <i class="fas fa-edit cursor-pointer" data-bs-toggle="modal" data-bs-target="#status-update-modal" wire:click="openStatusUpdateModal({{ $order->id }})"></i>
                            </div>
                            <div class="fw-bold text-info">{{ $order->order_number }}</div>
                            <div class="d-flex gap-1 mt-1">
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 py-0" style="font-size: 10px;">Website</span>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-0" style="font-size: 10px;">Woo</span>
                            </div>
                        </td>
                        @endif

                        @if($columns['date'])
                        <td>
                            <div><span class="fw-bold text-dark">Created</span> {{ $order->placed_at?->format('M d, Y h:i A') }}</div>
                            <div class="text-muted"><span class="fw-bold">Shipping</span> {{ $order->shipped_at ? $order->shipped_at->format('M d, Y h:i A') : '---' }}</div>
                        </td>
                        @endif

                        @if($columns['customer'])
                        <td>
                            <div class="fw-bold text-info fs-6">{{ $order->getCustomerNameAttribute() }}</div>
                            <div class="d-flex align-items-center gap-1 my-1">
                                <span class="badge bg-warning text-dark px-1" style="font-size: 10px;">NEW</span>
                                <i class="fas fa-info-circle text-muted" style="font-size: 11px;"></i>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-bold text-dark">{{ $order->billing_phone }}</span>
                                <i class="fas fa-copy text-muted cursor-pointer" style="font-size: 11px;"></i>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->billing_phone) }}" target="_blank" class="text-success">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                            <div class="text-muted small lh-sm" style="max-width: 180px;">{{ $order->billing_address_line_1 }}, {{ $order->shippingCity?->name }}</div>
                        </td>
                        @endif

                        @if($columns['pickup_address'])
                        <td>
                            <span class="badge bg-light text-muted border px-2 mb-1" style="font-size: 10px;">Warehouse</span>
                            <div class="text-info fw-bold">{{ $order->vendor->name ?? 'M3Food' }}</div>
                        </td>
                        @endif

                        @if($columns['payment_info'])
                        <td>
                            <div class="text-muted">Sales Amount: BDT {{ number_format($order->total_amount, 2) }}</div>
                            <div class="text-muted">Paid Amount: BDT 0.00</div>
                            <div class="fw-bold text-dark">Due Amount: BDT {{ number_format($order->total_amount, 2) }}</div>
                        </td>
                        @endif

                        @if($columns['delivery_partner'])
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width:22px; height:22px;">
                                    <i class="fas fa-caret-down text-white" style="font-size: 10px;"></i>
                                </div>
                                <span class="fw-bold text-dark">Steadfast</span>
                            </div>
                        </td>
                        @endif

                        @if($columns['delivery_fee'])
                        <td><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 10px;">Regular</span></td>
                        @endif

                        @if($columns['internal_notes'])
                        <td class="text-center">
                            <div class="d-flex gap-3 justify-content-center">
                                <i class="far fa-file-alt text-info cursor-pointer fs-5" title="View Note"></i>
                                <i class="fas fa-plus text-muted cursor-pointer fs-5" title="Add Note"></i>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-5 text-center text-muted">No orders found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals (Preserved from original) -->
    <div class="modal fade" id="status-update-modal" tabindex="-1" wire:ignore.self x-data="{ bootstrapModal: null }" x-init="bootstrapModal = new bootstrap.Modal($el)" @close-modal-now.window="bootstrapModal.hide()">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="updateOrderStatus">
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Order Status</label>
                            <select class="form-select" wire:model="newOrderStatus">
                                @foreach($orderStatuses as $status) <option value="{{ $status->value }}">{{ $status->label() }}</option> @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">Payment Status</label>
                            <select class="form-select" wire:model="newPaymentStatus">
                                @foreach($paymentStatuses as $status) <option value="{{ $status->value }}">{{ $status->label() }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white px-4 fw-bold">Update Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .table thead th {
            font-weight: 600;
            background: #f8f9fa;
            color: #666;
            border-bottom: 1px solid #eee;
        }

        .btn-info {
            background-color: #00bcd4;
            border-color: #00bcd4;
        }

        .text-info {
            color: #00bcd4 !important;
        }

        .custom-check:checked {
            background-color: #00bcd4;
            border-color: #00bcd4;
        }

        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .form-check-label {
            font-size: 14px;
            cursor: pointer;
        }
    </style>

</div>