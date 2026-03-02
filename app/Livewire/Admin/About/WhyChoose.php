<?php

namespace App\Livewire\About;

use App\Models\WhyChoose as ModelsWhyChoose;
use Livewire\Component;
use Livewire\WithFileUploads;

class WhyChoose extends Component
{
    use WithFileUploads;

    public $whyId, $title, $image, $old_image;
    public $items = [];

    public function mount()
    {
        $data = ModelsWhyChoose::first();
        if ($data) {
            $this->whyId = $data->id;
            $this->title = $data->title;
            $this->old_image = $data->image;
            $this->items = $data->items ?? [];
        }
    }

    public function addItem()
    {
        $this->items[] = ['icon' => '', 'title' => '', 'description' => ''];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $data = ModelsWhyChoose::find($this->whyId);

        $updateData = [
            'title' => $this->title,
            'items' => $this->items,
        ];

        if ($this->image) {
            if ($data->image && file_exists(public_path($data->image))) {
                @unlink(public_path($data->image));
            }
            $imageName = 'why_' . time() . '.' . $this->image->extension();
            $this->image->move(public_path('uploads/why'), $imageName);
            $updateData['image'] = 'uploads/why/' . $imageName;
        }

        $data->update($updateData);
        session()->flash('message', 'Section updated!');
    }

    public function render()
    {
        return view('livewire.about.why-choose');
    }
}
