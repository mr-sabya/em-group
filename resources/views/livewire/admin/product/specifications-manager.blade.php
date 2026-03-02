<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Technical Specifications: {{ $product->name }}</h3>
            <span class="badge bg-info">Separated from Variations</span>
        </div>

        <div class="card-body">
            @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('message') }}</div>
            @endif

            <div class="row">
                <!-- Left Column: Manage Values -->
                <div class="col-md-8">
                    <!-- Inside the form in your Blade file -->
                    <form wire:submit.prevent="saveSpecifications">
                        @foreach ($groupedKeys as $group => $keys)
                        <div class="mb-4" wire:key="group-{{ Str::slug($group) }}"> <!-- Added wire:key -->
                            <h6 class="text-uppercase text-muted border-bottom pb-2">{{ $group }}</h6>
                            <div class="row">
                                @foreach ($keys as $key)
                                <div class="col-md-6 mb-3" wire:key="spec-key-{{ $key->id }}"> <!-- Added wire:key -->
                                    <label class="form-label font-weight-bold">{{ $key->name }}</label>
                                    <input type="text"
                                        class="form-control"
                                        {{-- Use wire:model.defer or wire:model.blur to prevent jumping --}}
                                        wire:model.blur="specs.{{ $key->id }}"
                                        placeholder="Value for {{ $key->name }}">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary px-5">
                            Save All Specifications
                        </button>
                    </form>
                </div>

                <!-- Right Column: Add New Technical Keys -->
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Add New Spec Label</h6>
                            <p class="small text-muted">Create a new row label (e.g., "Cotton Grade").</p>

                            <div class="mb-2">
                                <input type="text" wire:model="newKeyName" class="form-control mb-2" placeholder="Label Name (e.g. Frame)">
                                @error('newKeyName') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <select wire:model="newKeyGroup" class="form-select">
                                    <option value="General">General</option>
                                    <option value="Dimensions">Dimensions</option>
                                    <option value="Technical">Technical</option>
                                    <option value="Material">Material</option>
                                </select>
                            </div>

                            <button type="button" wire:click="addNewKey" class="btn btn-outline-success btn-sm w-100">
                                Create Label
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>