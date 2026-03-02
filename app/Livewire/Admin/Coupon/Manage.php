<?php

namespace App\Livewire\Coupon;

use Livewire\Component;
use App\Models\Coupon;
use App\Enums\CouponType;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Redirector;
use Illuminate\Http\RedirectResponse;

class Manage extends Component
{
    public $couponId;
    public $code;
    public $description;
    public $type = CouponType::Percentage; // Default type, using enum instance
    public $value;
    public $min_spend;
    public $max_discount_amount;
    public $usage_limit_per_coupon;
    public $usage_limit_per_user;
    public $valid_from;
    public $valid_until;
    public $is_active = true;

    public $isEditing = false;
    public $couponTypeOptions; // To populate the dropdown

    protected array $rules = [];

    protected $messages = [
        'code.unique' => 'This coupon code is already taken. Please try another one.',
        'valid_until.after_or_equal' => 'The valid until date must be after or equal to the valid from date.',
        'value.max' => 'For percentage coupons, the value cannot exceed 100%.',
    ];

    public function mount($couponId = null)
    {
        // Initialize the base rules here
        $this->rules = [
            'code' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:' . implode(',', CouponType::values()),
            'value' => 'required|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit_per_coupon' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
            // Removed selectedProducts, selectedCategories, selectedUsers validation
        ];

        $coupon = $couponId ? Coupon::find($couponId) : new Coupon();

        // Populate coupon type options for the dropdown
        $this->couponTypeOptions = collect(CouponType::cases())->mapWithKeys(fn($type) => [$type->value => $type->label()])->toArray();

        if ($coupon->exists) { // Check if a coupon model was actually passed (for editing)
            $this->isEditing = true;
            $this->couponId = $coupon->id;
            $this->code = $coupon->code;
            $this->description = $coupon->description;
            $this->type = $coupon->type->value; // Get the string value from the enum for wire:model
            $this->value = $coupon->value;
            $this->min_spend = $coupon->min_spend;
            $this->max_discount_amount = $coupon->max_discount_amount;
            $this->usage_limit_per_coupon = $coupon->usage_limit_per_coupon;
            $this->usage_limit_per_user = $coupon->usage_limit_per_user;
            $this->valid_from = $coupon->valid_from ? $coupon->valid_from->format('Y-m-d\TH:i') : null;
            $this->valid_until = $coupon->valid_until ? $coupon->valid_until->format('Y-m-d\TH:i') : null;
            $this->is_active = $coupon->is_active;

            // Removed loading of selected relationships as they are managed separately now.
            // The form only shows core coupon details.
        } else {
            // Default values for new coupon if needed, although already set as properties
            $this->type = CouponType::Percentage->value; // Ensure default is string value
            $this->is_active = true;
        }
    }

    // Dynamic validation for code uniqueness and percentage value
    protected function getValidationRules()
    {
        return array_merge($this->rules, [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('coupons')->ignore($this->couponId),
            ],
            'value' => [
                'required',
                'numeric',
                'min:0',
                Rule::when($this->type === CouponType::Percentage->value, ['max:100']),
            ]
        ]);
    }

    public function saveCoupon(): Redirector|RedirectResponse
    {
        $this->validate($this->getValidationRules());

        $data = [
            'code' => $this->code,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'min_spend' => $this->min_spend,
            'max_discount_amount' => $this->max_discount_amount,
            'usage_limit_per_coupon' => $this->usage_limit_per_coupon,
            'usage_limit_per_user' => $this->usage_limit_per_user,
            'valid_from' => $this->valid_from ? Carbon::parse($this->valid_from) : null,
            'valid_until' => $this->valid_until ? Carbon::parse($this->valid_until) : null,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $coupon = Coupon::find($this->couponId);
            $coupon->update($data);
            session()->flash('message', 'Coupon updated successfully!');
        } else {
            $coupon = Coupon::create($data);
            session()->flash('message', 'Coupon created successfully!');
        }

        // Removed syncing relationships here as they are managed via dedicated assignment components.

        return redirect()->route('coupons.index');
    }

    public function render()
    {
        // No need to fetch any relational data here.
        return view('livewire.coupon.manage');
    }
}
