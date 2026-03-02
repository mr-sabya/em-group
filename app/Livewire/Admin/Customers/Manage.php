<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Country; // Import new models
use App\Models\State;
use App\Models\City;
use App\Enums\UserRole;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection; // <-- Import Collection

class Manage extends Component
{
    use WithFileUploads;

    public $userId;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $avatar;
    public $currentAvatar;
    public $phone;
    public $address; // Keep as string for street address
    public $zip_code; // Keep as string

    // Foreign key IDs for dynamic location
    public $country_id;
    public $state_id;
    public $city_id;

    public $is_active = true;
    public $role = UserRole::Customer;
    public $date_of_birth;
    public $gender;
    public $slug;

    public $isEditing = false;
    public $pageTitle = 'Create New Customer';

    // Data for dropdowns - Type-hint as Collection for consistency
    public Collection $countries;
    public Collection $states;
    public Collection $cities;

    public function mount($userId = null)
    {
        // Always load all countries on mount, ensuring it's a Collection
        $this->countries = Country::orderBy('name')->get();

        // Initialize states and cities as empty collections to prevent isEmpty() errors on first load
        $this->states = collect();
        $this->cities = collect();

        if ($userId) {
            $user = User::where('role', UserRole::Customer->value)
                ->with(['country', 'state', 'city']) // Eager load for mount data
                ->find($userId);

            if (!$user) {
                session()->flash('error', 'Customer not found or not a customer role.');
                return $this->redirect(route('customers.index'), navigate: true);
            }

            $this->isEditing = true;
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->currentAvatar = $user->avatar;
            $this->phone = $user->phone;
            $this->address = $user->address;
            $this->zip_code = $user->zip_code;

            // Load existing location IDs
            $this->country_id = $user->country_id;
            $this->state_id = $user->state_id;
            $this->city_id = $user->city_id;

            $this->is_active = $user->is_active;
            $this->date_of_birth = $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : null; // Format date for input
            $this->gender = $user->gender;
            $this->slug = $user->slug;

            $this->pageTitle = 'Edit Customer: ' . $user->name;

            // Populate states and cities based on existing selections
            if ($this->country_id) {
                $this->states = State::where('country_id', $this->country_id)->orderBy('name')->get();
            }
            // If there's an existing state_id, filter cities by it
            if ($this->state_id) {
                $this->cities = City::where('state_id', $this->state_id)->orderBy('name')->get();
            }
            // If no state_id but there's a country_id, load cities directly belonging to the country (where state_id is null)
            else if ($this->country_id) {
                $this->cities = City::where('country_id', $this->country_id)->whereNull('state_id')->orderBy('name')->get();
            }
            // Ensure $cities is always a collection, even if nothing was found
            if (!($this->cities instanceof Collection)) {
                $this->cities = collect($this->cities);
            }
        } else {
            // Default values for new customer
            $this->is_active = true;
            $this->pageTitle = 'Create New Customer';
        }
    }

    // React to country selection
    public function updatedCountryId($value)
    {
        $this->state_id = null; // Reset state and city when country changes
        $this->city_id = null;
        $this->states = collect(); // Reset to empty Collection
        $this->cities = collect(); // Reset to empty Collection

        if ($value) {
            $this->states = State::where('country_id', $value)->orderBy('name')->get();
            // Also load cities that directly belong to the country if no state is selected for that country
            $this->cities = City::where('country_id', $value)->whereNull('state_id')->orderBy('name')->get();
        }
    }

    // React to state selection
    public function updatedStateId($value)
    {
        $this->city_id = null; // Reset city when state changes
        $this->cities = collect(); // Reset to empty Collection

        if ($value) {
            $this->cities = City::where('state_id', $value)->orderBy('name')->get();
        } else if ($this->country_id) {
            // If state is unselected but country is selected, load cities directly under that country
            $this->cities = City::where('country_id', $this->country_id)->whereNull('state_id')->orderBy('name')->get();
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->userId),
            ],
            'password' => $this->isEditing ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|max:1024',
            'currentAvatar' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => [
                'nullable',
                Rule::exists('states', 'id')->where(function ($query) {
                    $query->where('country_id', $this->country_id); // Ensure state belongs to selected country
                }),
            ],
            'city_id' => [
                'nullable',
                Rule::exists('cities', 'id')->where(function ($query) {
                    if ($this->state_id) {
                        $query->where('state_id', $this->state_id); // Ensure city belongs to selected state
                    } else if ($this->country_id) {
                        $query->where('country_id', $this->country_id)->whereNull('state_id'); // Or directly to country if no state
                    }
                }),
            ],
            'is_active' => 'boolean',
            'date_of_birth' => 'nullable|date',
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('users')->ignore($this->userId),
            ],
        ];
    }

    protected $messages = [
        'email.unique' => 'This email is already registered. Please use another one.',
        'password.confirmed' => 'The password confirmation does not match.',
        'password.min' => 'The password must be at least 8 characters.',
        'slug.unique' => 'This slug is already taken. Please try another one.',
        'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
        'country_id.exists' => 'The selected country is invalid.',
        'state_id.exists' => 'The selected state is invalid for the chosen country.',
        'city_id.exists' => 'The selected city is invalid for the chosen state/country.',
    ];

    public function updatedName($value)
    {
        if (empty($this->slug) || Str::slug($value) === $this->slug) {
            $this->slug = Str::slug($value);
        }
    }

    public function saveCustomer()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'role' => UserRole::Customer,
            'is_active' => $this->is_active,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'slug' => $this->slug,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->avatar) {
            if ($this->currentAvatar && Storage::disk('public')->exists($this->currentAvatar)) {
                Storage::disk('public')->delete($this->currentAvatar);
            }
            $data['avatar'] = $this->avatar->store('avatars', 'public');
        } elseif (!$this->avatar && $this->currentAvatar) {
            $data['avatar'] = $this->currentAvatar;
        } else {
            $data['avatar'] = null;
        }

        if ($this->isEditing) {
            $user = User::find($this->userId);
            $user->update($data);
            session()->flash('message', 'Customer updated successfully!');
        } else {
            User::create($data);
            session()->flash('message', 'Customer created successfully!');
        }

        return redirect()->route('users.customers.index');
    }

    public function render()
    {
        return view('livewire.customers.manage');
    }
}
