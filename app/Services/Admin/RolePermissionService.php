<?php

namespace App\Services\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Trait\HttpResponse;

class RolePermissionService
{
    use HttpResponse;

    public function addRole($request)
    {
        $role = Role::create([
            'name' => $request->name
        ]);

        $role->permissions()->sync($request->permissions);

        return $this->success(null, 'Role created successfully');
    }

    public function getRole()
    {
        $roles = Role::select(['id', 'name'])->get();

        return $this->success($roles, 'Roles');
    }

    public function addPermission($request)
    {
        Permission::create([
            'name' => $request->name
        ]);

        return $this->success(null, 'Permission created successfully');
    }

    public function getPermission()
    {
        $permissions = Permission::select(['id', 'name'])->get();

        return $this->success($permissions, 'Permissions');
    }

    public function assignPermission($request)
    {
        $role = Role::findOrFail($request->role_id);

        $role->permissions()->sync($request->permissions);

        return $this->success(null, 'Assigned successfully');
    }
}







