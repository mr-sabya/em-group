<div class="p-4">
    {{-- KPI Cards --}}
    <div class="row mb-4">
        @php
        $kpis = [
        ['label' => 'Total Revenue', 'value' => '৳'.number_format($totalRevenue), 'icon' => 'fa-hand-holding-usd', 'color' => 'success'],
        ['label' => 'Total Orders', 'value' => number_format($totalOrders), 'icon' => 'fa-shopping-cart', 'color' => 'info'],
        ['label' => 'Unique Customers', 'value' => number_format($totalCustomers), 'icon' => 'fa-users', 'color' => 'primary'],
        ['label' => 'Live Products', 'value' => number_format($activeProducts), 'icon' => 'fa-cubes', 'color' => 'secondary'],
        ];
        @endphp

        @foreach($kpis as $kpi)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-{{ $kpi['color'] }} bg-opacity-10 p-3 me-3">
                            <i class="fas {{ $kpi['icon'] }} text-{{ $kpi['color'] }} fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">{{ $kpi['label'] }}</p>
                            <h4 class="fw-bold mb-0">{{ $kpi['value'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row">
        {{-- Revenue Chart --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold">Revenue Trends <small class="text-muted fw-normal">(Last 30 Days)</small></h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Sales by Source --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold">Order Sources</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <tbody>
                                @foreach($salesBySource as $source)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-normal">
                                            {{ ucfirst(str_replace('_', ' ', $source->source->value)) }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">৳{{ number_format($source->total, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Recent Orders --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between">
                    <h5 class="fw-bold">Recent Orders</h5>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light small text-uppercase">
                                <tr>
                                    <th class="px-4">Order #</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th class="text-end px-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td class="px-4 fw-bold text-primary">#{{ $order->order_number }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $order->name }}</div>
                                        <small class="text-muted">{{ $order->phone }}</small>
                                    </td>
                                    <td>
                                        @php
                                        $badgeClass = match($order->status->value) {
                                        'pending' => 'bg-warning',
                                        'delivered' => 'bg-success',
                                        'confirmed' => 'bg-primary',
                                        'canceled' => 'bg-danger',
                                        default => 'bg-info'
                                        };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill px-3">{{ strtoupper($order->status->value) }}</span>
                                    </td>
                                    <td class="text-end px-4 fw-bold">৳{{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Agents Leaderboard --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold">Top Agents</h5>
                </div>
                <div class="card-body">
                    @foreach($topAgents as $agent)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            {{ substr($agent->name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold">{{ $agent->name }}</h6>
                            <small class="text-muted">{{ $agent->orders_count }} Deliveries</small>
                        </div>
                        <div class="text-end">
                            <span class="text-success fw-bold">৳{{ number_format($agent->orders_sum_total_amount, 0) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            const ctx = document.getElementById('salesChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {
                            !!json_encode($chartLabels) !!
                        },
                        datasets: [{
                            label: 'Daily Revenue',
                            data: {
                                !!json_encode($chartValues) !!
                            },
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    callback: value => '৳' + value
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</div>