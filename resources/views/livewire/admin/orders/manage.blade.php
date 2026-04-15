<div>
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item small"><a href="{{ route('orders.index') }}" class="text-decoration-none text-muted">Orders</a></li>
                    <li class="breadcrumb-item active small">{{ $isEditMode ? 'Edit Order #'.$data['order_number'] : 'Create New Order' }}</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0">{{ $isEditMode ? 'Modify Order' : 'New Order Placement' }}</h1>
        </div>
    </div>

    <form wire:submit.prevent="save">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Customer Selection Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-user-circle me-2"></i> Customer Details</h5>
                        @if($showCustomerForm)
                        <button type="button" wire:click="$set('showCustomerForm', false)" class="btn btn-sm btn-outline-secondary border-0">
                            <i class="fas fa-search me-1"></i> Switch Customer
                        </button>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        @if(!$showCustomerForm)
                        <div class="text-center py-4">
                            <p class="text-muted mb-3">Search for a registered customer or enter details manually</p>
                            <div class="position-relative mx-auto" style="max-width: 500px;">
                                <!-- Search Input Group -->
                                <div class="input-group border rounded-pill overflow-hidden bg-light shadow-sm">
                                    <span class="input-group-text border-0 bg-transparent ps-3">
                                        <i class="fas fa-search text-dark"></i>
                                    </span>
                                    <input type="text"
                                        wire:model.live.debounce.300ms="customerSearch"
                                        class="form-control border-0 shadow-none py-2"
                                        placeholder="Search by name or phone number...">
                                </div>

                                <!-- 1. Show Results Dropdown if count > 0 -->
                                @if(count($searchResults) > 0)
                                <div class="position-absolute w-100 mt-2 shadow-lg bg-light border rounded-3 text-start overflow-hidden" style="z-index: 1050;">
                                    @foreach($searchResults as $user)
                                    <button type="button"
                                        wire:click="selectCustomer({{ $user->id }})"
                                        class="btn btn-white w-100 text-start border-bottom p-3 hover-light transition border-0">
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="small text-muted"><i class="fas fa-phone-alt me-1"></i> {{ $user->phone }}</div>
                                    </button>
                                    @endforeach
                                </div>

                                <!-- 2. If search string is long enough but results are empty -->
                                @elseif(strlen($customerSearch) >= 3)
                                <div class="card mt-3 border-dashed bg-light">
                                    <div class="card-body text-center py-3">
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-user-slash me-1"></i> No registered customer found for "{{ $customerSearch }}"
                                        </p>
                                        <button type="button"
                                            wire:click="createNewCustomer"
                                            class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">
                                            <i class="fas fa-user-plus me-1"></i> Create Manual Customer
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="row g-3">
                            @if($selectedCustomerId)
                            <div class="col-12">
                                <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill"><i class="fas fa-link me-1"></i> Linked to User ID: #{{ $selectedCustomerId }}</div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone*</label>
                                <input type="text" wire:model.blur="data.phone" class="form-control shadow-none border-light-subtle">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name*</label>
                                <input type="text" wire:model="data.name" class="form-control shadow-none border-light-subtle">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Shipping Address*</label>
                                <textarea wire:model="data.address" class="form-control shadow-none border-light-subtle" rows="2"></textarea>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Order Items Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-shopping-basket me-2"></i> Order Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 border-0 small text-uppercase text-muted">Product</th>
                                        <th class="border-0 small text-uppercase text-muted">Variant</th>
                                        <th class="border-0 small text-uppercase text-muted" style="width: 120px;">Price</th>
                                        <th class="border-0 small text-uppercase text-muted" style="width: 100px;">Qty</th>
                                        <th class="border-0 small text-uppercase text-muted" style="width: 120px;">Total</th>
                                        <th class="pe-4 border-0"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orderItems as $index => $item)
                                    <tr>
                                        <td class="ps-4">
                                            <select wire:model.live="orderItems.{{ $index }}.product_id" class="form-select border-light-subtle shadow-none">
                                                <option value="">Select Product</option>
                                                @foreach($products as $p) <option value="{{ $p->id }}">{{ $p->name }}</option> @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select wire:model.live="orderItems.{{ $index }}.variant_id" class="form-select border-light-subtle shadow-none" {{ empty($orderItems[$index]['product_id']) ? 'disabled' : '' }}>
                                                <option value="">Default</option>
                                                @if(!empty($orderItems[$index]['product_id']))
                                                @foreach($products->find($orderItems[$index]['product_id'])->variants as $v)
                                                <option value="{{ $v->id }}">{{ $v->display_name }}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="number" wire:model.blur="orderItems.{{ $index }}.price" class="form-control shadow-none border-light-subtle"></td>
                                        <td><input type="number" wire:model.live="orderItems.{{ $index }}.quantity" class="form-control shadow-none border-light-subtle"></td>
                                        <td>
                                            <div class="fw-bold text-dark">৳{{ number_format($orderItems[$index]['total'], 2) }}</div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-link text-danger p-0"><i class="fas fa-times-circle"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 bg-light border-top">
                            <button type="button" wire:click="addItem" class="btn btn-white btn-sm border fw-bold px-3 shadow-sm text-primary">
                                <i class="fas fa-plus me-1"></i> Add Line Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-light border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-primary">Summary</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Courier Service</label>
                            <select wire:model="data.courier_id" class="form-select shadow-none border-light-subtle mb-3">
                                <option value="">Select Courier</option>
                                @foreach($couriers as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
                            </select>

                            <div class="d-flex justify-content-between mb-2 small text-muted">
                                <span>Subtotal</span>
                                <span>৳{{ number_format($data['subtotal'], 2) }}</span>
                            </div>
                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-7 small text-muted">Delivery Fee</div>
                                <div class="col-5"><input type="number" wire:model.live="data.delivery_fee" class="form-control form-control-sm text-end border-light-subtle shadow-none"></div>
                            </div>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-7 small text-muted">Manual Discount</div>
                                <div class="col-5"><input type="number" wire:model.live="data.discount" class="form-control form-control-sm text-end border-light-subtle shadow-none"></div>
                            </div>
                            <hr class="opacity-50">
                            <div class="d-flex justify-content-between mb-4">
                                <h5 class="fw-bold mb-0">Total</h5>
                                <h5 class="fw-bold text-primary mb-0">৳{{ number_format($data['total_amount'], 2) }}</h5>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Amount Paid (Advance)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle small">৳</span>
                                    <input type="number" wire:model.live="data.paid_amount" class="form-control border-light-subtle shadow-none fw-bold text-success">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted mb-2">Order Status</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($statuses as $status)
                                <button type="button" wire:click="$set('data.status', '{{ $status->value }}')"
                                    class="status-btn {{ $data['status'] == $status->value ? 'active' : '' }}">
                                    {{ $status->label() }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow">
                            <i class="fas fa-check-circle me-1"></i> {{ $isEditMode ? 'Update Order' : 'Place Order' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <style>
        .hover-light:hover {
            background-color: #f8fafc;
        }

        .transition {
            transition: all 0.2s ease;
        }

        .status-btn {
            border: 1px solid #dee2e6;
            background: #fff;
            padding: 5px 12px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
            color: #6c757d;
            text-transform: uppercase;
        }

        .status-btn.active {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #fff;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }

        .form-select,
        .form-control {
            border-radius: 8px;
            font-size: 14px;
        }

        .card {
            border-radius: 12px;
        }
    </style>
</div>