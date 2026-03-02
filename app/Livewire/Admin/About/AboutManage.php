<?php

namespace App\Livewire\About;

use Livewire\Component;
use App\Models\About;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class AboutManage extends Component
{
    use WithFileUploads;

    public $aboutId;
    public $title, $sub_title, $description, $experience_year, $experience_text, $author_name;
    public $image, $old_image;

    // This will hold our dynamic list of features
    public $features = [];

    public function mount()
    {
        $about = About::first();
        if ($about) {
            $this->aboutId = $about->id;
            $this->title = $about->title;
            $this->sub_title = $about->sub_title;
            $this->description = $about->description;
            $this->experience_year = $about->experience_year;
            $this->experience_text = $about->experience_text;
            $this->author_name = $about->author_name;
            $this->old_image = $about->image;
            $this->features = $about->features ?? [];
        }
    }

    public function addFeature()
    {
        $this->features[] = ['title' => '', 'description' => ''];
    }

    public function removeFeature($index)
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features); // Reset array keys
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string',
            'sub_title' => 'required|string',
            'description' => 'required',
            'experience_year' => 'required|numeric',
            'author_name' => 'required',
            'image' => 'nullable|image|max:2048', // 2MB Max
            'features.*.title' => 'required',
            'features.*.description' => 'required',
        ]);

        $about = About::find($this->aboutId);

        $data = [
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'description' => $this->description,
            'experience_year' => $this->experience_year,
            'experience_text' => $this->experience_text,
            'author_name' => $this->author_name,
            'features' => $this->features,
        ];

        if ($this->image) {
            // Delete old image if it exists
            if ($about->image && file_exists(public_path($about->image))) {
                @unlink(public_path($about->image));
            }

            $imageName = time() . '.' . $this->image->extension();
            $this->image->move(public_path('uploads/about'), $imageName);
            $data['image'] = 'uploads/about/' . $imageName;
        }

        $about->update($data);

        session()->flash('message', 'About section updated successfully!');
    }

    public function render()
    {
        return view('livewire.about.about-manage');
    }
}
