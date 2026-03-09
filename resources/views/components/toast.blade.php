<div
    x-data="{ 
        show: false, 
        message: '', 
        type: 'success',
        close() {
            this.show = false;
        }
    }"
    x-on:notify.window="
        message = $event.detail.message; 
        type = $event.detail.type || 'success'; 
        show = true; 
        setTimeout(() => show = false, 2000)
    "
    x-init="
        @if(session()->has('message'))
            $nextTick(() => { $dispatch('notify', { message: '{{ session('message') }}', type: 'success' }) })
        @endif
        @if(session()->has('error'))
            $nextTick(() => { $dispatch('notify', { message: '{{ session('error') }}', type: 'danger' }) })
        @endif
    "
    x-show="show"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-x-8"
    x-transition:enter-end="opacity-100 transform translate-x-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 transform translate-x-0"
    x-transition:leave-end="opacity-0 transform translate-x-8"
    class="position-fixed top-0 end-0 p-3"
    style="z-index: 9999; pointer-events: none;">
    <div
        class="border-0 shadow-lg rounded-3 overflow-hidden"
        :class="type === 'success' ? 'bg-success' : 'bg-danger'"
        style="pointer-events: auto; min-width: 300px; color: white;">
        <div class="d-flex align-items-center p-3">
            <div class="me-2">
                <i x-show="type === 'success'" class="ri-checkbox-circle-fill fs-4"></i>
                <i x-show="type === 'danger'" class="ri-error-warning-fill fs-4"></i>
            </div>
            <div class="flex-grow-1 fw-medium" x-text="message"></div>
            <button type="button" class="btn-close btn-close-white ms-2" @click="show = false"></button>
        </div>

        <!-- Progress bar (Visual timer) -->
        <div class="bg-white opacity-25" style="height: 3px; width: 100%;">
            <div
                class="bg-white h-100"
                x-show="show"
                x-transition:enter="transition-all linear duration-[2000ms]"
                x-transition:enter-start="w-full"
                x-transition:enter-end="w-0"></div>
        </div>
    </div>
</div>

{{-- Add this CSS to your app.css or layout head once --}}
<style>
    [x-cloak] {
        display: none !important;
    }
</style>