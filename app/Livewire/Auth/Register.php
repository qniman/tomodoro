<?php

namespace App\Livewire\Auth;

use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Регистрация')]
class Register extends Component
{
    #[Validate('required|string|min:2|max:64')]
    public string $name = '';

    #[Validate('required|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    #[Validate('required|string|min:8')]
    public string $password_confirmation = '';

    public function submit(): void
    {
        $data = $this->validate();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $this->seedDefaults($user);

        Auth::login($user);
        request()->session()->regenerate();

        $this->redirect(route('app'), navigate: false);
    }

    /**
     * Стартовый набор проектов и тегов, чтобы новому пользователю было с чем работать.
     */
    protected function seedDefaults(User $user): void
    {
        $projects = [
            ['name' => 'Входящие',  'color' => '#5b8def', 'position' => 10],
            ['name' => 'Работа',    'color' => '#e5533a', 'position' => 20],
            ['name' => 'Личное',    'color' => '#2ea043', 'position' => 30],
            ['name' => 'Учёба',     'color' => '#a371f7', 'position' => 40],
        ];
        foreach ($projects as $row) {
            Project::firstOrCreate(
                ['user_id' => $user->id, 'name' => $row['name']],
                ['color' => $row['color'], 'position' => $row['position']],
            );
        }

        foreach (['фокус' => '#e5533a', 'идеи' => '#5b8def', 'быстро' => '#2ea043'] as $name => $color) {
            Tag::firstOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                ['color' => $color],
            );
        }
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
