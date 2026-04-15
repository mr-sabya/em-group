<?php

namespace App\Livewire\Admin\Admin;

use App\Models\Admin;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManageAdmins extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Form properties
    public $adminId, $name, $email, $password, $password_confirmation;
    
    // Change Password specific properties
    public $new_password, $new_password_confirmation;
    
    // Role properties
    public $selectedRoles = []; 

    public $search = '';
    public $isEditing = false;

    // Reset pagination when searching
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetInputs()
    {
        $this->adminId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->selectedRoles = [];
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    /* 
    |--------------------------------------------------------------------------
    | Save / Update Admin Logic
    |--------------------------------------------------------------------------
    */
    public function saveAdmin()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'email', 
                Rule::unique('admins', 'email')->ignore($this->adminId)
            ],
            'password' => $this->isEditing ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
            'selectedRoles' => 'required|array|min:1'
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $admin = Admin::updateOrCreate(['id' => $this->adminId], $data);
        
        // Sync Roles for 'admin' guard
        $admin->syncRoles($this->selectedRoles);

        session()->flash('message', $this->isEditing ? 'Admin updated successfully.' : 'Admin created successfully.');
        $this->dispatch('close-modal');
        $this->resetInputs();
    }

    public function editAdmin($id)
    {
        $this->resetInputs();
        $admin = Admin::findOrFail($id);
        $this->adminId = $id;
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->selectedRoles = $admin->getRoleNames()->toArray();
        $this->isEditing = true;
        
        $this->dispatch('open-admin-modal');
    }

    /* 
    |--------------------------------------------------------------------------
    | Change Password Logic
    |--------------------------------------------------------------------------
    */
    public function editPassword($id)
    {
        $this->resetInputs();
        $admin = Admin::findOrFail($id);
        $this->adminId = $id;
        $this->name = $admin->name;
        $this->dispatch('open-password-modal');
    }

    public function updatePassword()
    {
        $this->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Admin::findOrFail($this->adminId);
        $admin->update([
            'password' => Hash::make($this->new_password)
        ]);

        session()->flash('message', "Password for {$admin->name} updated successfully.");
        $this->dispatch('close-modal');
        $this->resetInputs();
    }

    /* 
    |--------------------------------------------------------------------------
    | Delete Logic
    |--------------------------------------------------------------------------
    */
    public function deleteAdmin($id)
    {
        if ($id == auth('admin')->id()) {
            session()->flash('error', 'You cannot delete yourself.');
            return;
        }

        Admin::findOrFail($id)->delete();
        session()->flash('message', 'Admin deleted successfully.');
    }

    public function render()
    {
        $admins = Admin::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate(10);

        $roles = Role::where('guard_name', 'admin')->get();

        return view('livewire.admin.admin.manage-admins', [
            'admins' => $admins,
            'roles' => $roles
        ]);
    }
}