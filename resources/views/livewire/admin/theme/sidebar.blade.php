<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="{{ route('dashboard.index') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ url('assets/images/logo-sm.png') }}" alt="" height="26">
            </span>
            <span class="logo-lg">
                <img src="{{ isset($settings['dark_logo']) ? asset('storage/' . $settings['dark_logo']) : url('assets/images/logo-dark.png') }}" alt="" height="26">
            </span>
        </a>
        <a href="{{ route('dashboard.index') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ url('assets/images/logo-sm.png') }}" alt="" height="26">
            </span>
            <span class="logo-lg">
                <img src="{{ isset($settings['white_logo']) ? asset('storage/' . $settings['white_logo']) : url('assets/images/logo-light.png') }}" alt="" height="26">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">

                <li class="menu-title"><span data-key="t-menu">Menu</span></li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-system">System</span></li>

                <!-- dashboard -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('dashboard.*') ? 'active' : 'collapsed' }}" href="{{ route('dashboard.index') }}" wire:navigate>
                        <i class="ri-dashboard-line"></i> <span data-key="t-dashboard">Dashboard</span>
                    </a>
                </li>

                <!-- Users -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('users.*') ? 'active' : 'collapsed' }}" href="#userManage" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('users.*') ? 'true' : 'false' }}">
                        <i class="bi bi-people"></i> <span data-key="t-users">Users</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('users.*') ? 'show' : '' }}" id="userManage">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('users.customers.index') }}" class="nav-link {{ request()->routeIs('users.customers.*') ? 'active' : '' }}" wire:navigate> Customers </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.admins.index') }}" class="nav-link {{ request()->routeIs('users.admins.*') ? 'active' : '' }}" wire:navigate> Admins </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.roles.index') }}" class="nav-link {{ request()->routeIs('users.roles.*') ? 'active' : '' }}" wire:navigate> Roles </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.permissions.index') }}" class="nav-link {{ request()->routeIs('users.permissions.*') ? 'active' : '' }}" wire:navigate> Permissions </a>
                            </li>
                        </ul>
                    </div>
                </li>


                <!-- Locations -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('locations.*') ? 'active' : 'collapsed' }}" href="#locationManage" data-bs-toggle="collapse" role="button">
                        <i class="bi bi-geo-alt"></i> <span data-key="t-locations">Locations</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('locations.*') ? 'show' : '' }}" id="locationManage">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('locations.countries') }}" class="nav-link {{ request()->routeIs('locations.countries') ? 'active' : '' }}" wire:navigate> Countries </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('locations.states') }}" class="nav-link {{ request()->routeIs('locations.states') ? 'active' : '' }}" wire:navigate> States </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('locations.cities') }}" class="nav-link {{ request()->routeIs('locations.cities') ? 'active' : '' }}" wire:navigate> Cities </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- for current tenant -->

                @if($this->currentTenant)
                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-management">Store: {{ $this->currentTenant->name }}</span></li>
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard.tenant') }}" class="nav-link menu-link {{ Route::is('dashboard.tenant') ? 'active' : '' }}" wire:navigate>
                        <i class="bi bi-speedometer2"></i> <span data-key="t-dashboard">Dashboard ({{ $this->currentTenant->name }})</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-management">Management</span></li>

                <!-- Product Catalog -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('product.*') ? 'active' : 'collapsed' }}" href="#productManage" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('product.*') ? 'true' : 'false' }}" aria-controls="productManage">
                        <i class="bi bi-box-seam"></i> <span data-key="t-products">Product Catalog</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('product.*') ? 'show' : '' }}" id="productManage">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('product.products.index') }}" class="nav-link {{ request()->routeIs('product.products.*') ? 'active' : '' }}" wire:navigate> Products </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('product.categories.index') }}" class="nav-link {{ request()->routeIs('product.categories.*') ? 'active' : '' }}" wire:navigate> Categories </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('product.brands.index') }}" class="nav-link {{ request()->routeIs('product.brands.*') ? 'active' : '' }}" wire:navigate> Brands </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('product.coupons.index') }}" class="nav-link {{ request()->routeIs('product.coupons.*') ? 'active' : '' }}" wire:navigate> Coupons </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('product.tags.index') }}" class="nav-link {{ request()->routeIs('product.tags.*') ? 'active' : '' }}" wire:navigate> Tags </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('product.reviews.index') }}" class="nav-link {{ request()->routeIs('product.reviews.*') ? 'active' : '' }}" wire:navigate> Reviews </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Attributes -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('attribute.*') ? 'active' : 'collapsed' }}" href="#attributeManage" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('attribute.*') ? 'true' : 'false' }}" aria-controls="attributeManage">
                        <i class="bi bi-tags"></i> <span data-key="t-attributes">Attributes</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('attribute.*') ? 'show' : '' }}" id="attributeManage">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('attribute.attributes.index') }}" class="nav-link {{ request()->routeIs('attribute.attributes.index') ? 'active' : '' }}" wire:navigate> Attributes </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attribute.attribute-values.index') }}" class="nav-link {{ request()->routeIs('attribute.attribute-values.index') ? 'active' : '' }}" wire:navigate> Attribute Values </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attribute.attribute-sets.index') }}" class="nav-link {{ request()->routeIs('attribute.attribute-sets.index') ? 'active' : '' }}" wire:navigate> Attribute Sets </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Orders and cancel reasons -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('orders.*') ? 'active' : 'collapsed' }}" href="#orderManage" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('orders.*') ? 'true' : 'false' }}" aria-controls="orderManage">
                        <i class="bi bi-cart-check"></i> <span data-key="t-orders">Orders</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('orders.*') ? 'show' : '' }}" id="orderManage">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.index') ? 'active' : '' }}" wire:navigate> All Orders </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('orders.cancel-reasons') }}" class="nav-link {{ request()->routeIs('orders.cancel-reasons') ? 'active' : '' }}" wire:navigate> Cancel Reasons </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a href="{{ route('deal.index') }}" class="nav-link menu-link {{ request()->routeIs('deal.index') ? 'active' : '' }}" wire:navigate>
                        <i class="bi bi-lightning-charge"></i> <span data-key="t-deals">Deals</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('collection.index') }}" class="nav-link menu-link {{ request()->routeIs('collection.index') ? 'active' : '' }}" wire:navigate>
                        <i class="bi bi-collection"></i> <span data-key="t-collections">Collections</span>
                    </a>
                </li>

                <!-- Blog -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('blog.*') ? 'active' : 'collapsed' }}" href="#blogManage" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('blog.*') ? 'true' : 'false' }}">
                        <i class="bi bi-journal-text"></i> <span data-key="t-blog">Blog</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('blog.*') ? 'show' : '' }}" id="blogManage">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('blog.category.index') }}" class="nav-link {{ request()->routeIs('blog.category.index') ? 'active' : '' }}" wire:navigate> Category </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('blog.tag.index') }}" class="nav-link {{ request()->routeIs('blog.tag.index') ? 'active' : '' }}" wire:navigate> Tag </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('blog.post.index') }}" class="nav-link {{ request()->routeIs('blog.post.index') ? 'active' : '' }}" wire:navigate> Post </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Shipping & Payment -->
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('shipping-method.*', 'payment-method.*') ? 'active' : 'collapsed' }}" href="#shippingPaymentManage" data-bs-toggle="collapse" role="button">
                        <i class="bi bi-truck"></i> <span data-key="t-shipping">Shipping & Payment</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('shipping-method.*', 'payment-method.*') ? 'show' : '' }}" id="shippingPaymentManage">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('shipping-method.index') }}" class="nav-link {{ request()->routeIs('shipping-method.index') ? 'active' : '' }}" wire:navigate> Shipping Methods </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('payment-method.index') }}" class="nav-link {{ request()->routeIs('payment-method.index') ? 'active' : '' }}" wire:navigate> Payment Methods </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a href="{{ route('settings.index') }}" class="nav-link menu-link {{ request()->routeIs('settings.index') ? 'active' : '' }}" wire:navigate>
                        <i class="bi bi-gear"></i> <span data-key="t-settings">Settings</span>
                    </a>
                </li>

                <!-- pages -->
                <li class="nav-item">
                    <a href="{{ route('page.index') }}" class="nav-link menu-link {{ request()->routeIs('page.*') ? 'active' : '' }}" wire:navigate>
                        <i class="bi bi-file-earmark-text"></i> <span data-key="t-pages">Pages</span>
                    </a>
                </li>

                <!-- couriers -->
                <li class="nav-item">
                    <a href="{{ route('courier.index') }}" class="nav-link menu-link {{ request()->routeIs('courier.index') ? 'active' : '' }}" wire:navigate>
                        <i class="bi bi-truck"></i> <span data-key="t-couriers">Couriers</span>
                    </a>
                </li>
                @endif
                <!-- for all tenant -->



            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>