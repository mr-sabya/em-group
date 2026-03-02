<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPassword extends Component
{
    public $token;

    #[Validate('required|email')]
    public $email = '';

    #[Validate('required|min:8|confirmed')]
    public $password = '';
    public $password_confirmation = '';

    public function mount($token)
    {
        $this->token = $token;
        $this->email = request()->query('email');
    }

    public function resetPassword()
    {
        $this->validate();

        $status = Password::broker('admins')->reset(
            [
                'token' => $this->token,
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ],
            function ($admin, $password) {
                $admin->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $admin->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', trans($status));
            return $this->redirect(route('login'), navigate: true);
        } else {
            $this->addError('email', trans($status));
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
