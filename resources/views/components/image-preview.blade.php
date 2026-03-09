@props([
'image' => null, // The Livewire temporary file
'existing' => null, // The existing path from the database
'model' => 'image', // The wire:model property name
'label' => null // Optional label
])

<div class="image-preview-wrapper">
    @if($label)
    <label class="form-label">{{ $label }}</label>
    @endif

    <div class="image-preview-container">
        <div class="image-preview">
            @if ($image && method_exists($image, 'temporaryUrl'))
            {{-- New Temporary Upload --}}
            <img src="{{ $image->temporaryUrl() }}">
            <button type="button" class="remove-image-btn" wire:click="$set('{{ $model }}', null)" title="Remove upload">
                <i class="ri-close-line"></i>
            </button>

            @elseif ($existing)
            {{-- Existing Image from Storage --}}
            <img src="{{ asset('storage/' . $existing) }}" alt="Current Image">
            <button type="button" class="remove-image-btn" wire:click="$set('{{ $model }}', null)" title="Remove current">
                <i class="ri-close-line"></i>
            </button>

            @else
            {{-- Placeholder State --}}
            <div class="icon">
                <i class="ri-file-image-line"></i>
                <span class="text-muted small">No image uploaded</span>
            </div>
            @endif
        </div>

        {{-- Hidden File Input Trigger --}}
        <div class="mt-2">
            <input type="file" wire:model="{{ $model }}" class="d-none" id="input_{{ $model }}" accept="image/*">

            <label for="input_{{ $model }}" class="btn btn-upload-custom w-100">
                <i class="ri-upload-2-line"></i> {{ $existing || $image ? 'Change Image' : 'Upload Image' }}
            </label>
        </div>

        {{-- Loading Indicator --}}
        <div wire:loading wire:target="{{ $model }}" class="text-primary small mt-1">
            <i class="ri-loader-4-line rotate"></i> Uploading...
        </div>
    </div>
</div>