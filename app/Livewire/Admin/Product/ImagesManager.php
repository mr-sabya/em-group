<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ImagesManager extends Component
{
    use WithFileUploads;

    public Product $product;
    public $newImages = []; // Array for multiple file uploads
    public Collection $existingImages; // To hold ProductImage models for display/reordering
    public $imageSortOrder = []; // Array to store sorted image IDs

    protected $listeners = ['imageDeleted' => 'refreshImages'];

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->loadImages();
    }

    private function loadImages()
    {
        $this->existingImages = $this->product->images()->orderBy('sort_order')->get();
        $this->imageSortOrder = $this->existingImages->pluck('id')->toArray();
    }

    protected function rules()
    {
        return [
            'newImages.*' => ['nullable', 'image', 'max:5120'], // 5MB max for each image
        ];
    }

    protected $messages = [
        'newImages.*.image' => 'Each uploaded file must be an image.',
        'newImages.*.max' => 'Each image may not be larger than 5MB.',
    ];

    public function updatedNewImages()
    {
        $this->validateOnly('newImages.*'); // Validate new images as they are selected
    }

    public function uploadImages()
    {
        $this->validate();

        if (empty($this->newImages)) {
            session()->flash('info', 'No new images selected for upload.');
            return;
        }

        foreach ($this->newImages as $imageFile) {
            $path = $imageFile->store('products/gallery', 'public');

            // Find the highest existing sort order for this product's images
            $maxSortOrder = $this->product->images()->max('sort_order') ?? 0;

            $this->product->images()->create([
                'image_path' => $path,
                'sort_order' => $maxSortOrder + 1,
            ]);
        }

        $this->newImages = []; // Clear the upload queue
        $this->loadImages(); // Reload images including the new ones
        session()->flash('message', 'Images uploaded successfully!');
    }

    public function updateImageSortOrder($orderedIds)
    {
        // $orderedIds will be like: [15, 2, 8, 4]

        foreach ($orderedIds as $index => $id) {
            ProductImage::where('id', $id)->update([
                'sort_order' => $index + 1
            ]);
        }

        $this->existingImages = $this->product->images()->orderBy('sort_order')->get();
        session()->flash('message', 'Image order updated successfully!');
    }

    public function deleteImage($imageId)
    {
        $image = ProductImage::find($imageId);
        if ($image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
            $this->loadImages(); // Reload images
            session()->flash('message', 'Image deleted successfully!');
        }
    }

    public function render()
    {
        return view('livewire.product.images-manager');
    }
}
