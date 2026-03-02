<div class="card">
    <div class="card-header">
        <h4>Update Counters</h4>
    </div>
    <div class="card-body">
        @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <form wire:submit.prevent="save">
            @foreach($items as $index => $item)
            <div class="card mb-3 border">
                <div class="card-body bg-light">
                    <div class="row align-items-center">
                        <!-- Icon Preview & Upload -->
                        <div class="col-md-2 text-center">
                            <label class="small d-block">Icon</label>
                            <div class="image-preview">
                                @if (isset($new_icons[$index]))
                                <img src="{{ $new_icons[$index]->temporaryUrl() }}" width="50" class="mb-2">
                                @elseif(!empty($item['icon']))
                                <img src="{{ asset($item['icon']) }}" width="50" class="mb-2">
                                @endif
                            </div>
                            <input type="file" class="form-control form-control-sm" wire:model="new_icons.{{$index}}">
                        </div>

                        <div class="col-lg-10">
                            <div class="row">
                                <!-- Remove -->
                                <div class="col-md-12 mt-4 text-end">
                                    <button type="button" class="btn btn-danger btn-sm" wire:click="removeItem({{$index}})">Remove</button>
                                </div>
                                <!-- Number -->
                                <div class="col-md-12">
                                    <label class="small">Number</label>
                                    <input type="text" class="form-control" wire:model="items.{{$index}}.number">
                                </div>

                                <!-- Suffix -->
                                <div class="col-md-12">
                                    <label class="small">Suffix (+, % etc)</label>
                                    <input type="text" class="form-control" wire:model="items.{{$index}}.suffix">
                                </div>

                                <!-- Title -->
                                <div class="col-md-12">
                                    <label class="small">Title</label>
                                    <input type="text" class="form-control" wire:model="items.{{$index}}.title">
                                </div>

                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <button type="button" class="btn btn-dark btn-sm mb-3" wire:click="addItem">+ Add Counter</button>
            <hr>
            <button type="submit" class="btn btn-primary">Save All Counters</button>
        </form>
    </div>
</div>