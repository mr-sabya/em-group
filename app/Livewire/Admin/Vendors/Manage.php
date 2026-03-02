<?php

namespace App\Livewire\Vendors;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\VendorProfile; // Use VendorProfile model
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Enums\UserRole;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Manage extends Component
{
    use WithFileUploads;

    // User related fields
    public $userId;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $avatar;
    public $currentAvatar;
    public $user_phone; // Renamed to avoid conflict with vendor profile phone
    public $is_active = true;

    // Vendor Profile related fields
    public $vendorProfileId;
    public $shop_name;
    public $shop_slug; // For auto-generation and uniqueness
    public $shop_description;
    public $shop_logo;
    public $currentShopLogo;
    public $shop_banner;
    public $currentShopBanner;
    public $vendor_phone; // Renamed to avoid conflict with user phone
    public $vendor_email; // Renamed to avoid conflict with user email

    // Location fields
    public $address;
    public $zip_code;
    public $country_id;
    public $state_id;
    public $city_id;

    public $bank_name;
    public $bank_account_number;
    public $bank_account_holder;
    public $commission_rate = 0; // Default or null
    public $is_approved = false;

    public $isEditing = false;
    public $pageTitle = 'Create New Vendor Profile';

    // Data for dropdowns
    public $countries = [];
    public $states = [];
    public $cities = [];

    protected $listeners = ['slugGenerated'];

    public function mount($vendorProfileId = null)
    {
        $this->countries = Country::orderBy('name')->get();

        if ($vendorProfileId) {
            $vendorProfile = VendorProfile::with(['user', 'country', 'state', 'city'])->find($vendorProfileId);

            if (!$vendorProfile || $vendorProfile->user->role !== UserRole::Vendor) {
                session()->flash('error', 'Vendor Profile not found or user is not a vendor.');
                return $this->redirect(route('vendors.index'), navigate: true);
            }

            $this->isEditing = true;
            $this->vendorProfileId = $vendorProfile->id;
            $this->userId = $vendorProfile->user->id;

            // Load User data
            $this->name = $vendorProfile->user->name;
            $this->email = $vendorProfile->user->email;
            $this->currentAvatar = $vendorProfile->user->avatar;
            $this->user_phone = $vendorProfile->user->phone;
            $this->is_active = $vendorProfile->user->is_active;

            // Load Vendor Profile data
            $this->shop_name = $vendorProfile->shop_name;
            $this->shop_slug = $vendorProfile->shop_slug;
            $this->shop_description = $vendorProfile->shop_description;
            $this->currentShopLogo = $vendorProfile->shop_logo;
            $this->currentShopBanner = $vendorProfile->shop_banner;
            $this->vendor_phone = $vendorProfile->phone;
            $this->vendor_email = $vendorProfile->email;

            // Load location data
            $this->address = $vendorProfile->address;
            $this->zip_code = $vendorProfile->zip_code;
            $this->country_id = $vendorProfile->country_id;
            $this->state_id = $vendorProfile->state_id;
            $this->city_id = $vendorProfile->city_id;

            // Populate states and cities
            if ($this->country_id) {
                $this->states = State::where('country_id', $this->country_id)->orderBy('name')->get();
            }
            if ($this->state_id) {
                $this->cities = City::where('state_id', $this->state_id)->orderBy('name')->get();
            }

            $this->bank_name = $vendorProfile->bank_name;
            $this->bank_account_number = $vendorProfile->bank_account_number;
            $this->bank_account_holder = $vendorProfile->bank_account_holder;
            $this->commission_rate = $vendorProfile->commission_rate;
            $this->is_approved = $vendorProfile->is_approved;

            $this->pageTitle = 'Edit Vendor Profile: ' . $this->shop_name;
        } else {
            $this->is_active = true;
            $this->is_approved = false;
            $this->pageTitle = 'Create New Vendor Profile';
        }
    }

    public function updatedShopName($value)
    {
        $this->shop_slug = Str::slug($value);
        $this->validateOnly('shop_slug'); // Validate slug immediately
    }

    // Dynamic loading of states and cities
    public function updatedCountryId($value)
    {
        $this->state_id = null;
        $this->city_id = null;
        $this->states = $value ? State::where('country_id', $value)->orderBy('name')->get() : [];
        $this->cities = [];
    }

    public function updatedStateId($value)
    {
        $this->city_id = null;
        $this->cities = $value ? City::where('state_id', $value)->orderBy('name')->get() : [];
    }

    protected function rules()
    {
        return [
            // User Validation
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
            'user_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',

            // Vendor Profile Validation
            'shop_name' => 'required|string|max:255',
            'shop_slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vendor_profiles')->ignore($this->vendorProfileId),
            ],
            'shop_description' => 'nullable|string',
            'shop_logo' => 'nullable|image|max:1024',
            'shop_banner' => 'nullable|image|max:2048',
            'vendor_phone' => 'nullable|string|max:20',
            'vendor_email' => 'nullable|email|max:255',

            // Location Validation
            'address' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',

            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_account_holder' => 'nullable|string|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'is_approved' => 'boolean',
        ];
    }

    protected $messages = [
        'email.unique' => 'This email is already registered for a user. Please use another one.',
        'shop_slug.unique' => 'This shop URL slug is already taken. Please try another shop name.',
        'password.confirmed' => 'The password confirmation does not match.',
        'password.min' => 'The password must be at least 8 characters.',
        'shop_logo.max' => 'The shop logo must not exceed 1MB.',
        'shop_banner.max' => 'The shop banner must not exceed 2MB.',
        'commission_rate.min' => 'Commission rate cannot be negative.',
        'commission_rate.max' => 'Commission rate cannot exceed 100%.',
        'country_id.exists' => 'The selected country is invalid.',
        'state_id.exists' => 'The selected state is invalid.',
        'city_id.exists' => 'The selected city is invalid.',
    ];

    public function saveVendor()
    {
        $this->validate();

        // Prepare User data
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->user_phone,
            'role' => UserRole::Vendor,
            'is_active' => $this->is_active,
        ];

        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        // Handle avatar upload
        if ($this->avatar) {
            if ($this->currentAvatar && Storage::disk('public')->exists($this->currentAvatar)) {
                Storage::disk('public')->delete($this->currentAvatar);
            }
            $userData['avatar'] = $this->avatar->store('avatars', 'public');
        } elseif (!$this->avatar && $this->currentAvatar) {
            $userData['avatar'] = $this->currentAvatar;
        } else {
            $userData['avatar'] = null;
        }


        // Prepare Vendor Profile data
        $vendorProfileData = [
            'shop_name' => $this->shop_name,
            'shop_slug' => $this->shop_slug,
            'shop_description' => $this->shop_description,
            'phone' => $this->vendor_phone, // Using vendor_phone for vendor profile
            'email' => $this->vendor_email, // Using vendor_email for vendor profile

            // Location fields
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,

            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'bank_account_holder' => $this->bank_account_holder,
            'commission_rate' => $this->commission_rate,
            'is_approved' => $this->is_approved,
            'is_active' => $this->is_active, // Vendor profile also has an active status
        ];

        // Handle shop logo upload
        if ($this->shop_logo) {
            if ($this->currentShopLogo && Storage::disk('public')->exists($this->currentShopLogo)) {
                Storage::disk('public')->delete($this->currentShopLogo);
            }
            $vendorProfileData['shop_logo'] = $this->shop_logo->store('shop_logos', 'public');
        } elseif (!$this->shop_logo && $this->currentShopLogo) {
            $vendorProfileData['shop_logo'] = $this->currentShopLogo;
        } else {
            $vendorProfileData['shop_logo'] = null;
        }

        // Handle shop banner upload
        if ($this->shop_banner) {
            if ($this->currentShopBanner && Storage::disk('public')->exists($this->currentShopBanner)) {
                Storage::disk('public')->delete($this->currentShopBanner);
            }
            $vendorProfileData['shop_banner'] = $this->shop_banner->store('shop_banners', 'public');
        } elseif (!$this->shop_banner && $this->currentShopBanner) {
            $vendorProfileData['shop_banner'] = $this->currentShopBanner;
        } else {
            $vendorProfileData['shop_banner'] = null;
        }


        if ($this->isEditing) {
            $user = User::find($this->userId);
            $user->update($userData);

            $vendorProfile = VendorProfile::find($this->vendorProfileId);
            $vendorProfile->update($vendorProfileData);

            session()->flash('message', 'Vendor Profile updated successfully!');
        } else {
            $user = User::create($userData);
            $user->vendorProfile()->create($vendorProfileData);

            session()->flash('message', 'Vendor Profile created successfully!');
        }

        return redirect()->route('users.vendors.index');
    }

    public function render()
    {
        return view('livewire.vendors.manage');
    }
}
