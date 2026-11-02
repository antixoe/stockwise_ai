<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\WithPagination;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class UserManager extends Component
{
    use WithPagination;
    public bool $showForm = false;
    public array $form = ['name' => '', 'email' => '', 'role' => 'warehouse_staff', 'password' => ''];
    protected function rules(): array { return ['form.name' => ['required', 'max:255'], 'form.email' => ['required', 'email', 'unique:users,email'], 'form.role' => ['required', 'in:admin,warehouse_staff,manager'], 'form.password' => ['required', 'string', 'min:12']]; }
    public function mount(): void { abort_unless(auth()->user()->hasRole('admin'), 403); }
    public function create(): void { $this->reset('form'); $this->form['role'] = 'warehouse_staff'; $this->showForm = true; }
    public function save(): void { $this->validate(); User::create(['name' => $this->form['name'], 'email' => $this->form['email'], 'role' => $this->form['role'], 'password' => Hash::make($this->form['password']), 'is_active' => true]); $this->showForm = false; $this->reset('form'); $this->dispatch('notify', message: 'Pengguna berhasil dibuat.', type: 'success'); }
    public function toggle(int $id): void { $user = User::findOrFail($id); abort_if($user->is(auth()->user()), 422, 'Akun sendiri tidak dapat dinonaktifkan.'); $user->update(['is_active' => ! $user->is_active]); }
    public function render()
    {
        return view('livewire.users.user-manager', ['users' => User::orderBy('name')->paginate(10)]);
    }
}
