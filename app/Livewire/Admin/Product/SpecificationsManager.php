<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\SpecificationKey;
use App\Models\ProductSpecification;
use Livewire\Component;
use Illuminate\Support\Str;

class SpecificationsManager extends Component
{
    public Product $product;

    // We store current values as [key_id => value_string]
    public $specs = [];

    // For adding a brand new key (e.g., "New Tech Label")
    public $newKeyName;
    public $newKeyGroup = 'General';

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->loadCurrentSpecifications();
    }

    private function loadCurrentSpecifications()
    {
        // Load existing specs into the array
        $this->specs = $this->product->specifications()
            ->get()
            ->pluck('value', 'specification_key_id')
            ->toArray();
    }

    /**
     * Create a new specification label on the fly
     */
    public function addNewKey()
    {
        $this->validate([
            'newKeyName' => 'required|string|max:255|unique:specification_keys,name',
            'newKeyGroup' => 'required|string|max:100',
        ]);

        $key = SpecificationKey::create([
            'name' => $this->newKeyName,
            'slug' => \Illuminate\Support\Str::slug($this->newKeyName),
            'group' => $this->newKeyGroup,
        ]);

        // IMPORTANT: Explicitly set the new ID in the array to an empty string
        // This prevents Livewire from "guessing" the value from the last input
        $this->specs[$key->id] = '';

        $this->newKeyName = '';
        session()->flash('message', "Technical Key '{$key->name}' added.");
    }

    public function saveSpecifications()
    {
        // Remove empty values before saving
        $filteredSpecs = array_filter($this->specs, fn($value) => !is_null($value) && $value !== '');

        foreach ($filteredSpecs as $keyId => $value) {
            ProductSpecification::updateOrCreate(
                ['product_id' => $this->product->id, 'specification_key_id' => $keyId],
                ['value' => $value]
            );
        }

        // Optional: Remove specs that were cleared out
        $this->product->specifications()
            ->whereNotIn('specification_key_id', array_keys($filteredSpecs))
            ->delete();

        session()->flash('message', 'Technical specifications updated successfully!');
    }

    public function render()
    {
        // Group existing keys by their group for a cleaner UI
        $availableKeys = SpecificationKey::orderBy('group')->orderBy('id')->get()->groupBy('group');

        return view('livewire.product.specifications-manager', [
            'groupedKeys' => $availableKeys,
        ]);
    }
}
