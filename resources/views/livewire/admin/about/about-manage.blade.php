<div class="card">
    <div class="card-header">
        <h4>Update About Us Section</h4>
    </div>
    <div class="card-body">
        @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <form wire:submit.prevent="save">
            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <!-- Main Info -->
                        <div class="col-md-6 mb-3">
                            <label>Sub Title (About Company)</label>
                            <input type="text" class="form-control" wire:model="sub_title">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Main Title</label>
                            <input type="text" class="form-control" wire:model="title">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Main Description</label>
                            <textarea class="form-control" rows="3" wire:model="description"></textarea>
                        </div>

                        <!-- Experience Box Info -->
                        <div class="col-md-6 mb-3">
                            <label>Experience Years (e.g. 12)</label>
                            <input type="text" class="form-control" wire:model="experience_year">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Author Name (e.g. John Doe)</label>
                            <input type="text" class="form-control" wire:model="author_name">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Experience Small Text</label>
                            <input type="text" class="form-control" wire:model="experience_text">
                        </div>
                    </div>
                </div>


                <!-- Image Upload -->
                <div class="col-md-4 mb-3">
                    <label>About Image</label>
                    <div class="image-preview">
                        @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" width="100" class="d-block mb-2">
                        @elseif($old_image)
                        <img src="{{ asset($old_image) }}" width="100" class="d-block mb-2">
                        @endif
                    </div>
                    <input type="file" class="form-control" wire:model="image">
                </div>

                <hr>

                <!-- Dynamic Features List -->
                <div class="col-md-12">
                    <h5>Features List (Bullet Points)</h5>
                    @foreach($features as $index => $feature)
                    <div class="card mb-2 border-secondary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" placeholder="Feature Title" wire:model="features.{{$index}}.title">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" placeholder="Feature Description" wire:model="features.{{$index}}.description">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger" wire:click="removeFeature({{$index}})">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <button type="button" class="btn btn-dark btn-sm mt-2" wire:click="addFeature">+ Add New Feature</button>
                </div>

                <div class="col-md-12 mt-4">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>