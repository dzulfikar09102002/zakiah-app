<?php

namespace App\Helpers\Services\Role;

use App\Models\Employee;
use App\Models\Role;

class RoleValidatorService
{
    private Role $role;
    private string $menu, $action;
    /**
     * Create a new class instance.
     */
    public function __construct(Employee $employee, string $menu, string $action)
    {
        //
        $this->role = $employee->role()->first();
        $this->menu = $menu;
        $this->action = $action;
    }

    public function authorize(): bool
    {
        $permission = $this->role->entity_permission;
        if (!array_key_exists($this->menu, $permission)) {
            return false;
        }

        $actions = $permission[$this->menu];
        if (!array_key_exists($this->action, $actions)) {
            return false;
        }

        return $actions[$this->action];
    }
}
