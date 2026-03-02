<div class="card">
    <div class="card-body">
        <form wire:submit.prevent="save">
            <div class="mb-3">
                <label>Main Title (Use &lt;span&gt; for yellow text)</label>
                <input type="text" class="form-control" wire:model="title">
            </div>

            <div class="mb-3">
                <label>Side Image</label>
                <div class="image-preview">
                    @if ($image)
                    <!-- Shows the preview of the NEWly selected image -->
                    <img src="{{ $image->temporaryUrl() }}" width="200" class="img-thumbnail shadow-sm">
                    @elseif($old_image)
                    <!-- Shows the CURRENT image from the database -->
                    <img src="{{ asset($old_image) }}" width="200" class="img-thumbnail shadow-sm">
                    @endif
                </div>
                <input type="file" class="form-control" wire:model="image">
            </div>

            <h5>Feature Boxes</h5>
            @foreach($items as $index => $item)
            <div class="border p-3 mb-2">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Icon (e.g., fas fa-headset)" wire:model="items.{{$index}}.icon">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Title" wire:model="items.{{$index}}.title">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Description" wire:model="items.{{$index}}.description">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger" wire:click="removeItem({{$index}})">X</button>
                    </div>
                </div>
            </div>
            @endforeach

            <button type="button" class="btn btn-info btn-sm mb-3" wire:click="addItem">+ Add Box</button>
            <br>
            <button type="submit" class="btn btn-primary">Update Section</button>
        </form>
    </div>
</div>