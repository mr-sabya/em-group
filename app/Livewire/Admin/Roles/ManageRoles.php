<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;

class ManageRoles extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $roleId, $name, $guard_name = 'admin';
    public $selectedPermissions = []; // Holds names of selected permissions
    public $search = '';
    public $isEditing = false;

    // To prevent re-querying groups constantly
    protected $permissionGroups;

    public function resetInputs()
    {
        $this->roleId = null;
        $this->name = '';
        $this->guard_name = 'admin';
        $this->selectedPermissions = [];
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    /**
     * Toggles all permissions within a specific group
     */
    public function toggleGroup($groupName)
    {
        $groupPermissions = Permission::where('group_name', $groupName)
            ->where('guard_name', $this->guard_name)
            ->pluck('name')
            ->toArray();

        $allSelected = true;
        foreach ($groupPermissions as $p) {
            if (!in_array($p, $this->selectedPermissions)) {
                $allSelected = false;
                break;
            }
        }

        if ($allSelected) {
            // Remove all in this group
            $this->selectedPermissions = array_diff($this->selectedPermissions, $groupPermissions);
        } else {
            // Add all in this group (unique)
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $groupPermissions));
        }
    }

    public function saveRole()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $this->roleId,
            'guard_name' => 'required',
            'selectedPermissions' => 'required|array|min:1'
        ]);

        DB::transaction(function () {
            $role = Role::updateOrCreate(
                ['id' => $this->roleId],
                ['name' => $this->name, 'guard_name' => $this->guard_name]
            );

            $role->syncPermissions($this->selectedPermissions);
        });

        session()->flash('message', $this->roleId ? 'Role Updated' : 'Role Created');
        $this->dispatch('close-modal');
        $this->resetInputs();
    }

    public function editRole($id)
    {
        $this->resetInputs();
        $role = Role::findById($id, $this->guard_name);
        $this->roleId = $id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->isEditing = true;
        $this->dispatch('open-role-modal');
    }

    public function deleteRole($id)
    {
        Role::findById($id)->delete();
        session()->flash('message', 'Role Deleted Successfully.');
    }

    public function render()
    {
        $roles = Role::where('name', 'like', '%' . $this->search . '%')->paginate(10);

        // Fetch permissions grouped by group_name for the UI
        $groupedPermissions = Permission::where('guard_name', $this->guard_name)
            ->get()
            ->groupBy('group_name');

        return view('livewire.admin.roles.manage-roles', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions
        ]);
    }
}
