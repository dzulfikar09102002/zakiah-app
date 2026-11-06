<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Akun login milik karyawan. Setiap karyawan sudah punya satu baris users (tanpa email
 * bila belum bisa login), jadi "membuat akun" = mengisi email dan password di baris itu.
 *
 * Tabel users tidak punya entity_id dan juga dipakai pelanggan, jadi akun dianggap milik
 * entity ini hanya bila dipakai karyawan aktif di entity ini.
 *
 * Hapus akun: NewZakicaPOS dan pos-secaca login hanya dengan mencari users.email tanpa
 * melihat deleted_at, jadi email dipindah ke deleted_email lalu dikosongkan. Login gagal
 * di ketiga aplikasi tanpa mengubah kode POS. Pulihkan mengembalikan email; password lama
 * berlaku lagi.
 */
class UserAccountService
{
    public function getAccounts(bool $onlyTrashed): LengthAwarePaginator
    {
        $entityId = $this->entityId();

        $pagination = User::query()
            ->when($onlyTrashed, fn (Builder $query) => $query->onlyTrashed(), fn (Builder $query) => $query->whereNotNull('email'))
            ->whereHas('employees', fn (Builder $query) => $query->where('entity_id', $entityId))
            ->with(['employees' => fn ($query) => $query
                ->where('entity_id', $entityId)
                ->select('id', 'user_id', 'first_name', 'last_name')
                ->orderBy('id')])
            ->when(request('search'), fn (Builder $query, $search) => $query->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('deleted_email', 'like', "%{$search}%")
                    ->orWhereHas('employees', fn (Builder $employee) => $employee
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%"));
            }))
            ->orderBy('name')
            ->paginate(request('per_page', 10), ['id', 'name', 'email', 'deleted_email', 'deleted_at'])
            ->withQueryString();

        $pagination->setCollection($pagination->getCollection()->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'deleted_email' => $user->deleted_email,
            'deleted_at' => $user->deleted_at?->toIso8601String(),
            'is_self' => $user->id === auth()->id(),
            'employees' => $user->employees->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'name' => $this->employeeName($employee),
            ])->values(),
        ]));

        return $pagination;
    }

    /**
     * Karyawan aktif yang belum punya akun login.
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function employeeOptions(): Collection
    {
        return $this->employeesWithoutLogin()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn (Employee $employee) => [
                'value' => (int) $employee->id,
                'label' => $this->employeeName($employee),
            ]);
    }

    /**
     * @param  array{employee_id: int, email: string, password: string}  $data
     */
    public function store(array $data): User
    {
        $employee = $this->employeesWithoutLogin()->find($data['employee_id']);

        if (! $employee) {
            throw ValidationException::withMessages(['employee_id' => 'Karyawan ini sudah punya akun login atau tidak ditemukan.']);
        }

        $user = $employee->user;
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->save();

        return $user;
    }

    /**
     * @param  array{email: string, password?: string|null}  $data
     */
    public function update(User $user, array $data): void
    {
        $user->email = $data['email'];

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }

        $user->save();
    }

    public function delete(User $user): void
    {
        if ($user->id === auth()->id()) {
            throw ValidationException::withMessages(['account' => 'Akun Anda sendiri tidak bisa dihapus.']);
        }

        DB::transaction(function () use ($user) {
            $user->deleted_email = $user->email;
            $user->email = null;
            $user->remember_token = Str::random(60);
            $user->save();
            $user->delete();

            // Keluarkan dari sesi zakiah-app yang sedang berjalan.
            DB::table('sessions')->where('user_id', $user->id)->delete();
        });
    }

    public function restore(int $id, ?string $email): void
    {
        $user = User::onlyTrashed()
            ->whereHas('employees', fn (Builder $query) => $query->where('entity_id', $this->entityId()))
            ->findOrFail($id);

        $email = $email ?: $user->deleted_email;

        if (blank($email)) {
            throw ValidationException::withMessages(['email' => 'Isi email untuk memulihkan akun ini.']);
        }

        if (User::withTrashed()->where('email', $email)->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages(['email' => 'Email ini sudah dipakai akun lain. Gunakan email lain.']);
        }

        $user->restore();
        $user->email = $email;
        $user->deleted_email = null;
        $user->save();
    }

    public function find(int $id): User
    {
        return User::query()
            ->whereNotNull('email')
            ->whereHas('employees', fn (Builder $query) => $query->where('entity_id', $this->entityId()))
            ->findOrFail($id);
    }

    /**
     * @return Builder<Employee>
     */
    private function employeesWithoutLogin(): Builder
    {
        return Employee::query()
            ->where('entity_id', $this->entityId())
            ->whereHas('user', fn (Builder $query) => $query->whereNull('email'));
    }

    private function employeeName(Employee $employee): string
    {
        return trim($employee->first_name.' '.$employee->last_name);
    }

    private function entityId(): ?int
    {
        return auth()->user()?->entity?->id;
    }
}
