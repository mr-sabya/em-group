<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Setting;
use App\Enums\SettingType; // Make sure you have this Enum
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Livewire\WithoutUrlPagination;

class ManageSettings extends Component
{
    use WithPagination, WithoutUrlPagination, WithFileUploads;

    // Table state
    public $search = '';
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Form state (for create/edit modal)
    public $settingId;
    public $key;
    public $displayLabel;
    public $value;
    public $type = 'string'; // Default type
    public $description;
    public $group;
    public $imageFile;
    public $is_private = false;

    // UI state
    public $showSettingModal = false;
    public $isEditing = false; // Flag to determine if we're creating or editing
    public $modalTitle = 'Create New Setting';

    protected $queryString = ['search', 'sortField', 'sortDirection'];

    // Validation rules
    protected function rules()
    {
        return [
            'displayLabel' => 'required|string|max:255',
            'key' => [
                'nullable',
                'string',
                'max:255',
                // Ensure key is unique, except when editing the current record
                $this->isEditing
                    ? 'unique:settings,key,' . $this->settingId
                    : 'unique:settings,key',
                'regex:/^[a-z0-9_]+$/i' // Only alphanumeric and underscore
            ],
            'value' => 'nullable',
            'type' => ['required', 'string', 'in:' . implode(',', SettingType::values())],
            'description' => 'nullable|string|max:1000',
            'imageFile' => 'nullable|image|max:1024', // Max 1MB
            'group' => 'nullable|string|max:255',
            'is_private' => 'boolean',
        ];
    }

    // Custom validation messages
    protected $messages = [
        'key.unique' => 'A setting with this key already exists.', // This message might not be directly hit if key is disabled/generated
        'key.regex' => 'The key can only contain alphanumeric characters and underscores.',
        'displayLabel.required' => 'The setting name is required.',
    ];

    // Reset pagination when search or sort changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Sort table
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    // Open create modal
    public function createSetting()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->modalTitle = 'Create New Setting';
        $this->showSettingModal = true;
    }

    // Open edit modal
    public function editSetting(Setting $setting)
    {
        $this->settingId = $setting->id;
        $this->key = $setting->key;
        $this->displayLabel = $setting->label ?? Str::title(str_replace('_', ' ', $setting->key)); // Use actual label or humanize key
        $this->value = $setting->getRawOriginal('value'); // Get the raw value before casting
        $this->type = $setting->type->value; // Get the raw enum value
        $this->description = $setting->description;
        $this->imageFile = null;
        $this->group = $setting->group;
        $this->is_private = $setting->is_private;

        $this->isEditing = true;
        $this->modalTitle = 'Edit Setting: ' . $setting->key;
        $this->showSettingModal = true;
    }

    // Save or update setting
    public function saveSetting()
    {
        $this->validate();

        $data = [
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'description' => $this->description,
            'group' => $this->group,
            'is_private' => $this->is_private,
        ];

        // Generate key if creating a new setting
        if (!$this->isEditing) {
            $generatedKey = Str::snake($this->displayLabel);
            // Ensure generated key is unique before creating
            if (Setting::where('key', $generatedKey)->exists()) {
                $this->addError('displayLabel', 'A setting with a similar key already exists. Please choose a more unique name.');
                return;
            }
            $data['key'] = $generatedKey;
        }


        // Handle file upload specifically if the setting type is 'image'
        if ($this->type === SettingType::Image->value && $this->imageFile) {
            // Store the file in 'settings' directory inside storage/app/public
            $path = $this->imageFile->store('settings', 'public');
            $data['value'] = $path;

            // Delete old image if editing and a new one is uploaded
            if ($this->isEditing) {
                $oldSetting = Setting::find($this->settingId);
                if ($oldSetting && $oldSetting->type->value === SettingType::Image->value && $oldSetting->getRawOriginal('value')) {
                    \Storage::disk('public')->delete($oldSetting->getRawOriginal('value'));
                }
            }
        } elseif ($this->type === SettingType::Image->value && !$this->imageFile && $this->isEditing) {
            // If editing an image type setting, but no new file is uploaded, keep existing value
            // (or set to null if value was cleared in UI)
            $oldSetting = Setting::find($this->settingId);
            $data['value'] = $this->value; // This will come from the hidden input/current value
        } else {
            // For all other types, use the 'value' property directly
            $data['value'] = $this->value;
        }


        if ($this->isEditing) {
            $setting = Setting::find($this->settingId);
            $setting->update($data);
            session()->flash('message', 'Setting updated successfully!');
        } else {
            Setting::create($data);
            session()->flash('message', 'Setting created successfully!');
        }

        $this->closeModal();
    }

    // Delete setting
    public function deleteSetting($settingId)
    {
        Setting::destroy($settingId);
        session()->flash('message', 'Setting deleted successfully!');
        $this->resetPage(); // Reset pagination in case the last item on a page was deleted
    }

    // Close modal and reset form
    public function closeModal()
    {
        $this->showSettingModal = false;
        $this->resetForm();
    }

    // Reset form fields
    public function resetForm()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->settingId = null;
        $this->key = '';
        $this->displayLabel = '';
        $this->value = '';
        $this->type = 'string';
        $this->description = '';
        $this->group = '';
        $this->is_private = false;
    }

    public function render()
    {
        $settings = Setting::query()
            ->when($this->search, function ($query) {
                $query->where('key', 'like', '%' . $this->search . '%')
                    ->orWhere('label', 'like', '%' . $this->search . '%') // Search by label too
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('group', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.settings.manage-settings', [
            'settings' => $settings,
            'settingTypes' => SettingType::cases(), // Pass enum cases to the view
        ]);
    }
}
