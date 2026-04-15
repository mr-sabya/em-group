<?php

namespace App\Livewire\Admin\Permissions;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ManagePermissions extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Search and UI State
    public $search = '';
    public $isEditing = false;

    // Common fields
    public $group_name;
    public $guard_name = 'admin';

    // Bulk Create fields
    public $bulkPermissions = [['name' => '']];

    // Single Edit fields
    public $permissionId;
    public $editName;

    public function resetInputs()
    {
        $this->permissionId = null;
        $this->editName = '';
        $this->group_name = '';
        $this->guard_name = 'admin';
        $this->bulkPermissions = [['name' => '']];
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    /* 
    |--------------------------------------------------------------------------
    | Bulk Logic
    |--------------------------------------------------------------------------
    */
    public function addRow()
    {
        $this->bulkPermissions[] = ['name' => ''];
    }

    public function removeRow($index)
    {
        unset($this->bulkPermissions[$index]);
        $this->bulkPermissions = array_values($this->bulkPermissions);
    }

    public function saveBulk()
    {
        $this->validate([
            'group_name' => 'required|string|max:255',
            'guard_name' => 'required|in:admin,web',
            'bulkPermissions.*.name' => [
                'required', 'string', 'max:255',
                function ($attribute, $value, $fail) {
                    if (Permission::where('name', $value)->where('guard_name', $this->guard_name)->exists()) {
                        $fail("The permission '$value' already exists.");
                    }
                }
            ],
        ]);

        DB::transaction(function () {
            foreach ($this->bulkPermissions as $item) {
                Permission::create([
                    'name' => $item['name'],
                    'group_name' => $this->group_name,
                    'guard_name' => $this->guard_name,
                ]);
            }
        });

        session()->flash('message', 'Permissions Created Successfully.');
        $this->dispatch('close-modal');
        $this->resetInputs();
    }

    /* 
    |--------------------------------------------------------------------------
    | Edit Logic
    |--------------------------------------------------------------------------
    */
    public function editPermission($id)
    {
        $this->resetInputs();
        $permission = Permission::findOrFail($id);
        $this->permissionId = $id;
        $this->editName = $permission->name;
        $this->group_name = $permission->group_name;
        $this->guard_name = $permission->guard_name;
        $this->isEditing = true;
        
        $this->dispatch('open-edit-modal');
    }

    public function updatePermission()
    {
        $this->validate([
            'editName' => [
                'required', 'string', 'max:255',
                Rule::unique('permissions', 'name')->where('guard_name', $this->guard_name)->ignore($this->permissionId)
            ],
            'group_name' => 'required|string|max:255',
            'guard_name' => 'required',
        ]);

        $permission = Permission::findOrFail($this->permissionId);
        $permission->update([
            'name' => $this->editName,
            'group_name' => $this->group_name,
            'guard_name' => $this->guard_name,
        ]);

        session()->flash('message', 'Permission Updated Successfully.');
        $this->dispatch('close-modal');
        $this->resetInputs();
    }

    public function deletePermission($id)
    {
        Permission::findOrFail($id)->delete();
        session()->flash('message', 'Permission Deleted Successfully.');
    }

    public function render()
    {
        $permissions = Permission::where(function($q){
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('group_name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('group_name', 'asc')
            ->paginate(10);

        $existingGroups = DB::table('permissions')->select('group_name')->whereNotNull('group_name')->groupBy('group_name')->pluck('group_name');

        return view('livewire.admin.permissions.manage-permissions', [
            'permissions' => $permissions,
            'existingGroups' => $existingGroups
        ]);
    }
}