<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VariantsManager extends Component
{
    public Product $product;
    public $availableAttributes;
    public $selectedAttributeIds = [];
    public $attributeValuesToManage = [];
    public $newAttributeValueNames = [];

    // Form state arrays
    public $variantSku = [];
    public $variantPrice = [];
    public $variantCompareAtPrice = [];
    public $variantCostPrice = [];
    public $variantQuantity = [];
    public $variantWeight = [];
    public $variantIsActive = [];

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->availableAttributes = Attribute::active()->get();
        $this->loadExistingVariantData();
    }

    private function loadExistingVariantData()
    {
        $variants = $this->product->variants()->with('attributeValues')->get();

        $attrIds = [];
        foreach ($variants as $variant) {
            $key = (string) $variant->id;

            foreach ($variant->attributeValues as $val) {
                $attrIds[$val->attribute_id] = true;
                $this->attributeValuesToManage[$val->attribute_id][$val->id] = $val;
            }

            $this->variantSku[$key] = $variant->sku;
            $this->variantPrice[$key] = $variant->price;
            $this->variantCompareAtPrice[$key] = $variant->compare_at_price;
            $this->variantCostPrice[$key] = $variant->cost_price;
            $this->variantQuantity[$key] = $variant->quantity;
            $this->variantWeight[$key] = $variant->weight;
            $this->variantIsActive[$key] = (bool)$variant->is_active;
        }
        $this->selectedAttributeIds = array_keys($attrIds);
    }

    /**
     * Helper to generate a consistent key for any combination
     */
    private function getComboKey($combination)
    {
        $vIds = collect($combination)->pluck('id')->sort()->values()->all();
        return 'combo_' . implode('_', $vIds);
    }

    /**
     * Computed property for the Blade view
     */
    public function getComputedVariantsProperty()
    {
        $combinations = $this->getAttributeValueCombinations();
        $list = [];

        foreach ($combinations as $combination) {
            $comboHash = $this->getComboKey($combination);

            // Check if this exists in the DB already
            $existing = $this->product->variants->first(function ($v) use ($combination) {
                $dbIds = $v->attributeValues->pluck('id')->sort()->values()->all();
                $currentIds = collect($combination)->pluck('id')->sort()->values()->all();
                return $dbIds === $currentIds;
            });

            $key = $existing ? (string)$existing->id : $comboHash;

            // Initialize state safely if missing
            if (!array_key_exists($key, $this->variantSku)) {
                $this->variantSku[$key] = $this->product->sku . '-' . strtoupper(Str::random(4));
                $this->variantPrice[$key] = $this->product->price ?? 0;
                $this->variantQuantity[$key] = 0;
                $this->variantIsActive[$key] = true;
            }

            $list[] = [
                'key' => $key,
                'exists' => (bool)$existing,
                'display_name' => collect($combination)->pluck('value')->implode(' / '),
            ];
        }

        return $list;
    }

    public function saveVariants()
    {
        $this->validate([
            'variantSku.*' => 'required|string|max:255',
            'variantPrice.*' => 'required|numeric|min:0',
            'variantQuantity.*' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () {
                $currentIterationIds = [];
                $combinations = $this->getAttributeValueCombinations();

                foreach ($combinations as $combination) {
                    $vIds = collect($combination)->pluck('id')->sort()->values()->all();
                    $comboHash = $this->getComboKey($combination);

                    // Find if it exists in DB
                    $existing = $this->product->variants()->whereHas('attributeValues', function ($q) use ($vIds) {
                        $q->whereIn('attribute_value_id', $vIds);
                    }, '=', count($vIds))->first();

                    $key = $existing ? (string)$existing->id : $comboHash;

                    // SAFE ACCESS: Use null-coalescing to prevent "Undefined array key"
                    $data = [
                        'product_id' => $this->product->id,
                        'sku' => $this->variantSku[$key] ?? ($this->product->sku . '-' . strtoupper(Str::random(4))),
                        'price' => $this->variantPrice[$key] ?? ($this->product->price ?? 0),
                        'compare_at_price' => ($this->variantCompareAtPrice[$key] ?? null) ?: null,
                        'cost_price' => ($this->variantCostPrice[$key] ?? null) ?: null,
                        'quantity' => $this->variantQuantity[$key] ?? 0,
                        'weight' => ($this->variantWeight[$key] ?? null) ?: null,
                        'is_active' => (bool)($this->variantIsActive[$key] ?? true),
                    ];

                    if ($existing) {
                        $existing->update($data);
                        $currentIterationIds[] = $existing->id;
                    } else {
                        $newV = ProductVariant::create($data);
                        $newV->attributeValues()->attach($vIds);
                        $currentIterationIds[] = $newV->id;
                    }
                }

                // Delete variants no longer selected
                $this->product->variants()->whereNotIn('id', $currentIterationIds)->delete();
            });

            $this->loadExistingVariantData();
            session()->flash('message', 'All variants successfully saved.');
        } catch (\Exception $e) {
            session()->flash('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    private function getAttributeValueCombinations($index = 0, $currentCombination = [])
    {
        $selected = [];
        foreach ($this->selectedAttributeIds as $id) {
            if (!empty($this->attributeValuesToManage[$id])) {
                $selected[] = array_values($this->attributeValuesToManage[$id]);
            }
        }
        if (empty($selected)) return [];
        if ($index === count($selected)) return [$currentCombination];

        $results = [];
        foreach ($selected[$index] as $value) {
            $results = array_merge($results, $this->getAttributeValueCombinations($index + 1, array_merge($currentCombination, [$value])));
        }
        return $results;
    }

    public function updatedSelectedAttributeIds()
    {
        foreach ($this->selectedAttributeIds as $id) {
            if (!isset($this->attributeValuesToManage[$id])) {
                $attr = Attribute::find($id);
                if ($attr) $this->attributeValuesToManage[$id] = $attr->values()->get()->keyBy('id')->all();
            }
        }
    }

    public function addAttributeValue($attributeId)
    {
        $this->validate(["newAttributeValueNames.$attributeId" => 'required|string']);
        $attr = Attribute::find($attributeId);
        $val = $attr->values()->create([
            'value' => $this->newAttributeValueNames[$attributeId],
            'slug' => Str::slug($this->newAttributeValueNames[$attributeId])
        ]);
        $this->attributeValuesToManage[$attributeId][$val->id] = $val;
        $this->newAttributeValueNames[$attributeId] = '';
    }

    public function removeAttributeValue($attributeId, $attributeValueId)
    {
        unset($this->attributeValuesToManage[$attributeId][$attributeValueId]);
    }

    public function deleteVariant($id)
    {
        ProductVariant::destroy($id);
        $this->loadExistingVariantData();
    }

    public function render()
    {
        return view('livewire.product.variants-manager');
    }
}
