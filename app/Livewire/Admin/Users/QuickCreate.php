<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Role;
use App\Actions\GetInitialsAction;
use App\Mail\Users\SendInviteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class QuickCreate extends Component
{
    public $name = '';
    public $email = '';
    public array $rolesSelected = [];

    public bool $created = false;
    public ?string $createdId = null;
    public string $createdLabel = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'rolesSelected' => ['required', 'array', 'min:1'],
        ];
    }

    public function render()
    {
        $roles = Role::orderBy('name')->get();
        return view('livewire.admin.users.quick-create', compact('roles'));
    }

    public function store(GetInitialsAction $getInitialsAction)
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'email' => $this->email,
            'is_active' => 0,
            'is_office_login_only' => 0,
            'invite_token' => Str::random(32),
            'invited_by' => auth()->id(),
            'invited_at' => now(),
        ]);

        // generate image
        $initials = $getInitialsAction($user->name);
        $filename = $user->id.'.png';
        $path = 'users/';
        $imagePath = create_avatar($initials, $filename, $path);

        // save image
        $user->image = $imagePath;
        $user->save();

        foreach ($this->rolesSelected as $role) {
            $user->assignRole($role);
        }

        try {
            Mail::send(new SendInviteMail($user));
        } catch (\Exception $e) {
            // Log mail failure but don't stop the process
        }

        add_user_log([
            'title' => 'invited '.$user->name,
            'reference_id' => $user->id,
            'section' => 'Auth',
            'type' => 'Join',
        ]);

        $this->dispatch('user-created', id: $user->id);
        $this->js("Livewire.dispatch('user-created', { id: '{$user->id}' })");
        $this->dispatch('toast', message: __('users.created'), type: 'success');

        $this->created = true;
        $this->createdId = $user->id;
        $this->createdLabel = $user->name;

        $this->reset(['name', 'email', 'rolesSelected']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }
}
