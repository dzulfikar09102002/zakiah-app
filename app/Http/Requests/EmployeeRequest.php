<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Services\EmployeeRoleCatalog;
use App\Services\MenuAccessService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Data kerja karyawan: nama dan penugasan (toko + role). Email dan password ada di
 * modul Akun Login, jadi sengaja tidak diterima di sini.
 */
abstract class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => trim((string) $this->input('first_name')),
            'last_name' => trim((string) $this->input('last_name')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $entityId = auth()->user()?->entity?->id;
        $employee = $this->route('employee');

        return [
            'first_name' => [
                'required',
                'max:255',
                Rule::unique('employees', 'first_name')
                    ->where('entity_id', $entityId)
                    ->where('last_name', $this->input('last_name'))
                    ->whereNull('deleted_at')
                    ->ignore($employee instanceof Employee ? $employee->id : null),
            ],
            'last_name' => ['nullable', 'max:255'],
            // Kosong = Hak Akses bawaan sesuai role utama.
            'access_role_id' => app(MenuAccessService::class)->available()
                ? ['nullable', 'integer', Rule::exists('access_roles', 'id')
                    ->where('entity_id', $entityId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')]
                : ['prohibited'],
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.location_id' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where('entity_id', $entityId)->whereNull('deleted_at'),
            ],
            // Hanya role template: role turunan per entity tidak dikenali POS.
            'assignments.*.role_id' => ['required', 'integer', Rule::in(app(EmployeeRoleCatalog::class)->templateIds())],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $pairs = collect($this->input('assignments', []))
                    ->map(fn ($row) => ($row['location_id'] ?? '').'-'.($row['role_id'] ?? ''));

                if ($pairs->count() !== $pairs->unique()->count()) {
                    $validator->errors()->add('assignments', 'Ada toko dan role yang sama lebih dari sekali.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Nama depan wajib diisi.',
            'first_name.unique' => 'Karyawan dengan nama ini sudah ada.',
            'assignments.required' => 'Tambahkan minimal satu toko dan role.',
            'assignments.min' => 'Tambahkan minimal satu toko dan role.',
            'assignments.*.location_id.exists' => 'Toko tidak ditemukan.',
            'assignments.*.role_id.in' => 'Role tidak valid.',
        ];
    }
}
