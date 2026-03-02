<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Livewire\Component;

class SeoManager extends Component
{
    public Product $product;
    public $seo_title;
    public $seo_description;

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->seo_title = $product->seo_title;
        $this->seo_description = $product->seo_description;
    }

    protected function rules()
    {
        return [
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function saveSeo()
    {
        $this->validate();

        $this->product->update([
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ]);

        session()->flash('message', 'SEO details updated successfully!');
    }

    public function render()
    {
        return view('livewire.product.seo-manager');
    }
}
