<?php

namespace App\Livewire\AdBanner;

use App\Models\AdBanner;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public $banners = [];
    public $new_images = []; // Temporary storage for uploads

    public function mount()
    {
        // Define your 3 specific slots
        $slots = ['home-banner-1', 'home-banner-2', 'home-banner-3'];

        foreach ($slots as $slot) {
            $banner = AdBanner::firstOrCreate(['slug' => $slot], [
                'title' => 'Default Promo',
                'image_path' => 'banners/default.jpg',
                'link' => '#',
                'is_active' => true
            ]);
            
            $this->banners[$slot] = $banner->toArray();
        }
    }

    public function save($slug)
    {
        $banner = AdBanner::where('slug', $slug)->first();

        if (isset($this->new_images[$slug])) {
            // Delete old image if it exists and isn't the default
            if ($banner->image_path !== 'banners/default.jpg') {
                Storage::disk('public')->delete($banner->image_path);
            }
            
            $path = $this->new_images[$slug]->store('banners', 'public');
            $banner->image_path = $path;
        }

        $banner->update([
            'title' => $this->banners[$slug]['title'],
            'link' => $this->banners[$slug]['link'],
            'is_active' => $this->banners[$slug]['is_active'],
        ]);

        $this->new_images = []; // Clear upload state
        session()->flash('success', "Banner $slug updated successfully!");
    }

    public function render()
    {
        return view('livewire.ad-banner.index');
    }
}