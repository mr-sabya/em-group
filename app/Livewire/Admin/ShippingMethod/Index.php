<?php

namespace App\Livewire\ShippingMethod;

use App\Models\City;
use App\Models\State;
use App\Models\Country;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\ShippingRule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Illuminate\Support\Str;

class Index extends Component
{
    use WithPagination, WithoutUrlPagination;

    // Table Controls
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $statusFilter = '';
    public $perPage = 10;

    // Method Form
    public $method_id, $name, $is_default = false, $status = true;

    // Payment Assignment
    public $selected_payment_methods = [];

    // Rule Form
    public $rule_id, $selected_method_id;
    public $country_id, $state_id, $city_id, $cost;
    public $states = [], $cities = [];

    // UI States
    public $showMethodModal = false;
    public $showPaymentModal = false;
    public $showRuleModal = false;

    protected $queryString = ['search', 'statusFilter', 'sortField', 'sortDirection'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // --- 1. Shipping Method CRUD ---

    public function openMethodModal($id = null)
    {
        $this->resetValidation();
        $this->method_id = $id;
        if ($id) {
            $method = ShippingMethod::findOrFail($id);
            $this->name = $method->name;
            $this->is_default = $method->is_default;
            $this->status = $method->status;
        } else {
            $this->reset(['name', 'is_default', 'status']);
        }
        $this->showMethodModal = true;
    }

    public function saveMethod()
    {
        $this->validate(['name' => 'required|string|max:255']);

        if ($this->is_default) {
            ShippingMethod::where('is_default', true)->update(['is_default' => false]);
        }

        ShippingMethod::updateOrCreate(
            ['id' => $this->method_id],
            ['name' => $this->name, 'slug' => Str::slug($this->name), 'is_default' => $this->is_default, 'status' => $this->status]
        );

        $this->showMethodModal = false;
        session()->flash('message', 'Shipping Method Saved.');
    }

    // --- 2. Payment Assignment Logic ---

    public function openPaymentModal($id)
    {
        $this->selected_method_id = $id;
        $method = ShippingMethod::with('paymentMethods')->findOrFail($id);
        $this->selected_payment_methods = $method->paymentMethods->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->showPaymentModal = true;
    }

    public function savePaymentMethods()
    {
        $method = ShippingMethod::findOrFail($this->selected_method_id);
        $method->paymentMethods()->sync($this->selected_payment_methods);

        $this->showPaymentModal = false;
        session()->flash('message', 'Payment Methods Updated.');
    }

    // --- 3. Shipping Rules Logic ---

    public function openRuleModal($methodId, $ruleId = null)
    {
        $this->resetValidation();
        $this->selected_method_id = $methodId;
        $this->rule_id = $ruleId;

        if ($ruleId) {
            $rule = ShippingRule::findOrFail($ruleId);
            $this->country_id = $rule->country_id;
            $this->state_id = $rule->state_id;
            $this->city_id = $rule->city_id;
            $this->cost = $rule->cost;

            // FIX: Pre-populate arrays so select options appear
            $this->states = State::where('country_id', $this->country_id)->get();
            $this->cities = City::where('state_id', $this->state_id)->get();
        } else {
            $this->reset(['country_id', 'state_id', 'city_id', 'cost', 'states', 'cities']);
        }
        $this->showRuleModal = true;
    }

    public function updatedCountryId($id)
    {
        $this->states = State::where('country_id', $id)->get();
        $this->cities = [];
        $this->state_id = null;
        $this->city_id = null;
    }

    public function updatedStateId($id)
    {
        $this->cities = City::where('state_id', $id)->get();
        $this->city_id = null;
    }

    public function saveRule()
    {
        $this->validate(['country_id' => 'required', 'cost' => 'required|numeric|min:0']);

        ShippingRule::updateOrCreate(
            ['id' => $this->rule_id],
            [
                'shipping_method_id' => $this->selected_method_id,
                'country_id' => $this->country_id,
                'state_id' => $this->state_id,
                'city_id' => $this->city_id,
                'cost' => $this->cost,
            ]
        );

        $this->showRuleModal = false;
        session()->flash('message', 'Shipping Rule Saved.');
    }

    public function toggleStatus($id)
    {
        $method = ShippingMethod::findOrFail($id);
        $method->update(['status' => !$method->status]);
    }

    public function deleteRule($id)
    {
        ShippingRule::find($id)->delete();
    }

    public function render()
    {
        return view('livewire.shipping-method.index', [
            'methods' => ShippingMethod::with(['rules.city', 'rules.state', 'rules.country', 'paymentMethods'])
                ->where('name', 'like', '%' . $this->search . '%')
                ->when($this->statusFilter !== '', fn($q) => $q->where('status', $this->statusFilter))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage),
            'countries' => Country::all(),
            'all_payment_methods' => PaymentMethod::active()->get()
        ]);
    }
}
