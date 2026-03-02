<?php

namespace App\Livewire\About;

use App\Models\Counter;
use Livewire\Component;
use Livewire\WithFileUploads;

class CounterSection extends Component
{
    use WithFileUploads;

    public $counterId;
    public $items = []; // Array to store text data
    public $new_icons = []; // Array to store newly uploaded files

    public function mount()
    {
        $data = Counter::first();
        if ($data) {
            $this->counterId = $data->id;
            // Ensure we always have an array even if DB is empty
            $this->items = $data->items ?? [];
        } else {
            // Optional: Create an empty row if nothing exists in DB
            $this->items = [];
        }
    }

    public function addItem()
    {
        $this->items[] = ['icon' => '', 'number' => '', 'suffix' => '', 'title' => ''];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $counter = Counter::find($this->counterId);
        $updatedItems = $this->items;

        foreach ($this->new_icons as $index => $file) {
            if ($file) {
                $imageName = "counter_{$index}_" . time() . '.' . $file->extension();
                $file->move(public_path('uploads/counter'), $imageName);
                $updatedItems[$index]['icon'] = 'uploads/counter/' . $imageName;
            }
        }

        $counter->update(['items' => $updatedItems]);
        $this->new_icons = []; // Clear upload inputs
        session()->flash('message', 'Counters updated successfully!');
    }
    public function render()
    {
        return view('livewire.about.counter-section');
    }
}
