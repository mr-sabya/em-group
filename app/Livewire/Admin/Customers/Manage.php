<?php

namespace App\Livewire\Admin\Customers;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\UserInfo; // Import UserInfo
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class Manage extends Component
{
    use WithFileUploads;

    public $userId;
    public $userInfoId; // Track the UserInfo ID for validation ignore

    // User fields
    public $name, $email, $phone, $password, $password_confirmation, $avatar, $currentAvatar;

    // UserInfo fields
    public $address, $zip_code, $country_id, $state_id, $city_id, $date_of_birth, $gender, $slug;

    public $isEditing = false;
    public $pageTitle = 'Create New Customer';

    public Collection $countries, $states, $cities;

    public function mount($userId = null)
    {
        $this->countries = Country::orderBy('name')->get();
        $this->states = collect();
        $this->cities = collect();

        if ($userId) {
            $user = User::with('info')->find($userId);

            if (!$user) {
                session()->flash('error', 'Customer not found.');
                return $this->redirect(route('users.customers.index'), navigate: true);
            }

            $this->isEditing = true;
            $this->userId = $user->id;

            // Map User Data
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone;
            $this->currentAvatar = $user->avatar;

            // Map UserInfo Data
            if ($user->info) {
                $this->userInfoId = $user->info->id;
                $this->address = $user->info->address;
                $this->zip_code = $user->info->zip_code;
                $this->country_id = $user->info->country_id;
                $this->state_id = $user->info->state_id;
                $this->city_id = $user->info->city_id;
                $this->date_of_birth = $user->info->date_of_birth ? $user->info->date_of_birth->format('Y-m-d') : null;
                $this->gender = $user->info->gender;
                $this->slug = $user->info->slug;
            }

            $this->pageTitle = 'Edit Customer: ' . $user->name;

            // Load dependent dropdowns
            if ($this->country_id) {
                $this->states = State::where('country_id', $this->country_id)->orderBy('name')->get();
            }
            if ($this->state_id) {
                $this->cities = City::where('state_id', $this->state_id)->orderBy('name')->get();
            }
        }
    }

    public function updatedCountryId($value)
    {
        $this->state_id = null;
        $this->city_id = null;
        $this->states = $value ? State::where('country_id', $value)->orderBy('name')->get() : collect();
        $this->cities = collect();
    }

    public function updatedStateId($value)
    {
        $this->city_id = null;
        $this->cities = $value ? City::where('state_id', $value)->orderBy('name')->get() : collect();
    }

    protected function rules()
    {
        return [
            // User validations
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->userId)],
            'password' => $this->isEditing ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
            'avatar' => 'nullable|image|max:1024',

            // UserInfo validations
            'address' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'date_of_birth' => 'nullable|date',
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'slug' => ['nullable', 'string', Rule::unique('user_infos')->ignore($this->userInfoId)],
        ];
    }

    public function saveCustomer()
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Handle User Data
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
            ];

            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }

            if ($this->avatar) {
                if ($this->currentAvatar) Storage::disk('public')->delete($this->currentAvatar);
                $userData['avatar'] = $this->avatar->store('avatars', 'public');
            }

            $user = User::updateOrCreate(['id' => $this->userId], $userData);

            // 2. Handle UserInfo Data
            $user->info()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'slug' => $this->slug ?: Str::slug($this->name) . '-' . Str::random(5),
                    'address' => $this->address,
                    'zip_code' => $this->zip_code,
                    'country_id' => $this->country_id,
                    'state_id' => $this->state_id,
                    'city_id' => $this->city_id,
                    'date_of_birth' => $this->date_of_birth,
                    'gender' => $this->gender,
                ]
            );
        });

        session()->flash('message', $this->isEditing ? 'Customer updated!' : 'Customer created!');
        return $this->redirect(route('users.customers.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.customers.manage');
    }
}
