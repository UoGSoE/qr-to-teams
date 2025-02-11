<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class UserList extends Component
{
    public $username = '';

    public $surname = '';

    public $forenames = '';

    public $email = '';

    public $error = null;

    public function render()
    {
        return view('livewire.user-list', [
            'users' => User::orderBy('surname')->get(),
        ]);
    }

    public function lookupUser()
    {
        if (! $this->username) {
            return;
        }

        $existingUser = User::where('username', '=', $this->username)->first();
        if ($existingUser) {
            $this->error = 'User already exists';

            return;
        }

        $ldapUser = \Ldap::findUser($this->username);
        if (! $ldapUser) {
            $this->error = 'User not found';

            return;
        }

        $this->username = $ldapUser->username;
        $this->surname = $ldapUser->surname;
        $this->forenames = $ldapUser->forenames;
        $this->email = $ldapUser->email;
    }

    public function createUser()
    {
        $this->validate([
            'username' => 'required|unique:users',
            'surname' => 'required',
            'forenames' => 'required',
            'email' => 'required|email|unique:users',
        ]);

        User::create([
            'username' => $this->username,
            'surname' => $this->surname,
            'forenames' => $this->forenames,
            'email' => $this->email,
            'is_staff' => true,
            'password' => bcrypt(Str::random(64)),
        ]);

        $this->reset();
    }

    public function deleteUser($userId)
    {
        if (auth()->id() == $userId) {
            return;
        }
        User::findOrFail($userId)->delete();
    }
}
