<div class="py-4">
    <h2 class="mb-4">Setting Management</h2>


    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Setting List</h5>
            <button class="btn btn-primary" wire:click="createSetting">
                <i class="fas fa-plus"></i> Add New Brand
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search settings..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-2">
                    <select wire:model.live="perPage" class="form-select">
                        <option value="5">5 per page</option>
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            {{-- Display Display Name instead of raw Key --}}
                            <th wire:click="sortBy('label')" style="cursor: pointer;">
                                Setting Name {{-- Changed from 'Key' to 'Setting Name' --}}
                                @if ($sortField == 'label') {{-- Sort by label --}}
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th>Value</th>
                            <th wire:click="sortBy('type')" style="cursor: pointer;">
                                Type
                                @if ($sortField == 'type')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('group')" style="cursor: pointer;">
                                Group
                                @if ($sortField == 'group')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th>Description</th>
                            <th wire:click="sortBy('is_private')" style="cursor: pointer;">
                                Private
                                @if ($sortField == 'is_private')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($settings as $setting)
                        <tr>
                            <td>
                                <strong>{{ $setting->display_name }}</strong>
                                <br><small class="text-muted">{{ $setting->key }}</small>
                            </td>
                            <td>
                                @if ($setting->is_private)
                                <span class="text-muted">********</span>
                                @elseif ($setting->type == \App\Enums\SettingType::Boolean)
                                <span class="badge {{ $setting->value ? 'bg-success' : 'bg-danger' }}">
                                    {{ $setting->value ? 'Yes' : 'No' }}
                                </span>
                                @elseif ($setting->type == \App\Enums\SettingType::Image && $setting->value)
                                <img src="{{ asset('storage/' . $setting->value) }}" alt="Setting Image" style="max-width: 50px; max-height: 50px;">
                                @elseif ($setting->type == \App\Enums\SettingType::Color && $setting->value)
                                <span style="display:inline-block; width:20px; height:20px; background-color:{{ $setting->value }}; border:1px solid #ccc; vertical-align: middle;"></span>
                                {{ $setting->value }}
                                @elseif ($setting->type == \App\Enums\SettingType::Json)
                                <pre class="mb-0 small bg-light p-1 rounded" style="max-height: 80px; overflow-y: auto;">{{ json_encode($setting->value, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                    {{ $setting->value }}
                                </span>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ $setting->type->label() }}</span></td>
                            <td>{{ $setting->group ?? '-' }}</td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                    {{ $setting->description ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <i class="fas {{ $setting->is_private ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }}"></i>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" wire:click="editSetting({{ $setting->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="confirm('Are you sure you want to delete this setting?') || event.stopImmediatePropagation()" wire:click="deleteSetting({{ $setting->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No settings found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $settings->links() }}
        </div>
    </div>


    <!-- Setting Create/Edit Modal -->
    <div class="modal fade {{ $showSettingModal ? 'show d-block' : '' }}" id="settingModal" tabindex="-1" role="dialog" aria-labelledby="settingModalLabel" aria-hidden="{{ !$showSettingModal ? 'true' : 'false' }}" @if($showSettingModal) style="display: block;" @endif>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingModalLabel">{{ $modalTitle }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveSetting">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="displayLabel" class="form-label">Setting Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('displayLabel') is-invalid @enderror" id="displayLabel" wire:model.defer="displayLabel">
                            <small class="form-text text-muted">This is the friendly name for the setting (e.g., "Site Name"). The internal key will be generated.</small>
                            @error('displayLabel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Display the generated/actual key only if editing --}}
                        @if ($isEditing)
                        <div class="mb-3">
                            <label for="keyDisplay" class="form-label">Internal Key</label>
                            <input type="text" class="form-control" id="keyDisplay" value="{{ $key }}" disabled>
                            <small class="form-text text-muted">This internal key cannot be changed and is used for programmatic access (e.g., `Setting::get('site_name')`).</small>
                        </div>
                        @endif

                        <div class="mb-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" wire:model.live="type">
                                @foreach($settingTypes as $settingType)
                                <option value="{{ $settingType->value }}">{{ $settingType->label() }}</option>
                                @endforeach
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="value" class="form-label">Value</label>
                            @switch($type)
                            @case(\App\Enums\SettingType::Boolean->value)
                            <div class="form-check form-switch">
                                <input class="form-check-input @error('value') is-invalid @enderror" type="checkbox" id="value" wire:model.defer="value">
                                <label class="form-check-label" for="value">{{ $value ? 'Enabled' : 'Disabled' }}</label>
                            </div>
                            @break
                            @case(\App\Enums\SettingType::Text->value)
                            <textarea class="form-control @error('value') is-invalid @enderror" id="value" rows="5" wire:model.defer="value"></textarea>
                            @break
                            @case(\App\Enums\SettingType::Json->value)
                            <textarea class="form-control @error('value') is-invalid @enderror" id="value" rows="7" wire:model.defer="value"></textarea>
                            <small class="form-text text-muted">Enter valid JSON format.</small>
                            @break
                            @case(\App\Enums\SettingType::Image->value)
                            <input type="file" class="form-control @error('imageFile') is-invalid @enderror" id="imageFile" wire:model.live="imageFile">
                            <small class="form-text text-muted">Upload an image (Max 1MB).</small>
                            @error('imageFile') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            @if ($imageFile)
                            <p class="mt-2">New Image Preview:</p>
                            <img src="{{ $imageFile->temporaryUrl() }}" class="img-thumbnail" style="max-width: 150px;">
                            @elseif ($value) {{-- Show current image if editing and no new file selected --}}
                            <p class="mt-2">Current Image:</p>
                            <img src="{{ asset('storage/' . $value) }}" alt="Current Image" class="img-thumbnail" style="max-width: 150px;">
                            @endif
                            {{-- Hidden input to maintain existing value if no new file is uploaded --}}
                            <input type="hidden" wire:model.defer="value">
                            @break
                            @case(\App\Enums\SettingType::Color->value)
                            <input type="color" class="form-control form-control-color @error('value') is-invalid @enderror" id="value" wire:model.defer="value">
                            @break
                            @case(\App\Enums\SettingType::Password->value)
                            <input type="password" class="form-control @error('value') is-invalid @enderror" id="value" wire:model.defer="value">
                            <small class="form-text text-muted">This value will be stored securely (ensure your application handles sensitive data appropriately).</small>
                            @break
                            @default
                            <input type="text" class="form-control @error('value') is-invalid @enderror" id="value" wire:model.defer="value">
                            @break
                            @endswitch
                            @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" rows="3" wire:model.defer="description"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="group" class="form-label">Group</label>
                            <input type="text" class="form-control @error('group') is-invalid @enderror" id="group" wire:model.defer="group">
                            <small class="form-text text-muted">e.g., General, SEO, Social, Payment</small>
                            @error('group') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input @error('is_private') is-invalid @enderror" type="checkbox" id="is_private" wire:model.defer="is_private">
                            <label class="form-check-label" for="is_private">Is Private (e.g., API keys, sensitive info)</label>
                            @error('is_private') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading wire:target="saveSetting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Save Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Backdrop (optional, to properly dim background) -->
    @if($showSettingModal)
    <div class="modal-backdrop fade show"></div>
    @endif

    <style>
        /* Optional: Adjust modal z-index if needed for other elements */
        .modal.show {
            z-index: 1050;
            /* Ensure modal is above backdrop */
        }

        .modal-backdrop.show {
            z-index: 1040;
            /* Ensure backdrop is below modal */
        }
    </style>
</div>