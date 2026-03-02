<?php

namespace App\Livewire\Investors;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\InvestorProfile;
use App\Models\Country; // Import Country model
use App\Models\State;   // Import State model
use App\Models\City;    // Import City model
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
    public $phone;
    public $is_active = true;

    // Investor Profile related fields
    public $investorProfileId;
    public $company_name;
    public $investment_focus;
    public $website;
    public $contact_person_name;
    public $contact_person_email;
    public $contact_person_phone;

    // Location fields updated
    public $address; // Kept as string for street address
    public $zip_code; // Kept as string
    public $country_id; // Foreign key
    public $state_id;   // Foreign key
    public $city_id;    // Foreign key

    public $min_investment_amount;
    public $max_investment_amount;
    public $notes;
    public $is_approved = false;

    public $isEditing = false;
    public $pageTitle = 'Create New Investor Profile';

    // Data for dropdowns
    public $countries = [];
    public $states = [];
    public $cities = [];

    public function mount($investorProfileId = null)
    {
        $this->countries = Country::orderBy('name')->get(); // Load all countries

        if ($investorProfileId) {
            $investorProfile = InvestorProfile::with(['user', 'country', 'state', 'city'])->find($investorProfileId);

            if (!$investorProfile || $investorProfile->user->role !== UserRole::Investor) {
                session()->flash('error', 'Investor Profile not found or user is not an investor.');
                return $this->redirect(route('investors.index'), navigate: true);
            }

            $this->isEditing = true;
            $this->investorProfileId = $investorProfile->id;
            $this->userId = $investorProfile->user->id;

            // Load User data
            $this->name = $investorProfile->user->name;
            $this->email = $investorProfile->user->email;
            $this->currentAvatar = $investorProfile->user->avatar;
            $this->phone = $investorProfile->user->phone;
            $this->is_active = $investorProfile->user->is_active;

            // Load Investor Profile data
            $this->company_name = $investorProfile->company_name;
            $this->investment_focus = $investorProfile->investment_focus;
            $this->website = $investorProfile->website;
            $this->contact_person_name = $investorProfile->contact_person_name;
            $this->contact_person_email = $investorProfile->contact_person_email;
            $this->contact_person_phone = $investorProfile->contact_person_phone;

            // Load new location data
            $this->address = $investorProfile->address;
            $this->zip_code = $investorProfile->zip_code;
            $this->country_id = $investorProfile->country_id;
            $this->state_id = $investorProfile->state_id;
            $this->city_id = $investorProfile->city_id;

            // Populate states and cities based on loaded data
            if ($this->country_id) {
                $this->states = State::where('country_id', $this->country_id)->orderBy('name')->get();
            }
            if ($this->state_id) {
                $this->cities = City::where('state_id', $this->state_id)->orderBy('name')->get();
            }

            $this->min_investment_amount = $investorProfile->min_investment_amount;
            $this->max_investment_amount = $investorProfile->max_investment_amount;
            $this->notes = $investorProfile->notes;
            $this->is_approved = $investorProfile->is_approved;

            $this->pageTitle = 'Edit Investor Profile: ' . $this->company_name;
        } else {
            $this->is_active = true;
            $this->is_approved = false;
            $this->pageTitle = 'Create New Investor Profile';
        }
    }

    // Dynamic loading of states and cities
    public function updatedCountryId($value)
    {
        $this->state_id = null; // Reset state when country changes
        $this->city_id = null; // Reset city when country changes
        $this->states = $value ? State::where('country_id', $value)->orderBy('name')->get() : [];
        $this->cities = []; // Clear cities
    }

    public function updatedStateId($value)
    {
        $this->city_id = null; // Reset city when state changes
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
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',

            // Investor Profile Validation
            'company_name' => 'nullable|string|max:255',
            'investment_focus' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'contact_person_phone' => 'nullable|string|max:20',

            // Updated Location Validation
            'address' => 'nullable|string|max:255', // Kept this as per your model
            'zip_code' => 'nullable|string|max:20', // Kept this as per your model
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',

            'min_investment_amount' => 'nullable|numeric|min:0',
            'max_investment_amount' => 'nullable|numeric|min:0|gte:min_investment_amount',
            'notes' => 'nullable|string',
            'is_approved' => 'boolean',
        ];
    }

    protected $messages = [
        'email.unique' => 'This email is already registered. Please use another one.',
        'password.confirmed' => 'The password confirmation does not match.',
        'password.min' => 'The password must be at least 8 characters.',
        'website.url' => 'The website must be a valid URL.',
        'max_investment_amount.gte' => 'Max investment amount must be greater than or equal to min investment amount.',
        'country_id.exists' => 'The selected country is invalid.',
        'state_id.exists' => 'The selected state is invalid.',
        'city_id.exists' => 'The selected city is invalid.',
    ];

    public function saveInvestor()
    {
        $this->validate();

        // Prepare User data
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => UserRole::Investor,
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


        // Prepare Investor Profile data
        $investorProfileData = [
            'company_name' => $this->company_name,
            'investment_focus' => $this->investment_focus,
            'website' => $this->website,
            'contact_person_name' => $this->contact_person_name,
            'contact_person_email' => $this->contact_person_email,
            'contact_person_phone' => $this->contact_person_phone,

            // Updated Location fields
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,

            'min_investment_amount' => $this->min_investment_amount,
            'max_investment_amount' => $this->max_investment_amount,
            'notes' => $this->notes,
            'is_approved' => $this->is_approved,
            'is_active' => $this->is_active,
        ];


        if ($this->isEditing) {
            $user = User::find($this->userId);
            $user->update($userData);

            $investorProfile = InvestorProfile::find($this->investorProfileId);
            $investorProfile->update($investorProfileData);

            session()->flash('message', 'Investor Profile updated successfully!');
        } else {
            $user = User::create($userData);
            $user->investorProfile()->create($investorProfileData);

            session()->flash('message', 'Investor Profile created successfully!');
        }

        return redirect()->route('users.investors.index');
    }

    public function render()
    {
        return view('livewire.investors.manage');
    }
}
