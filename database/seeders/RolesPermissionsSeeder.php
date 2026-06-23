<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = [
            'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
            'view-products', 'create-products', 'edit-products', 'delete-products',
            'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers',
            'view-transactions', 'create-transactions', 'edit-transactions', 'delete-transactions',
            'approve-transactions', 'reject-transactions', 'print-transactions',
            'view-stocktakes', 'create-stocktakes', 'approve-stocktakes',
            'view-inventory', 'export-inventory',
            'view-stock-ledger', 'export-stock-ledger',
            'view-reports', 'export-reports', 'print-reports',
            'manage-users', 'manage-roles', 'manage-settings', 'view-activity-logs',
        ];

        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'admin' => $allPermissions,

            'manager' => [
                'view-categories', 'view-products', 'view-suppliers',
                'view-transactions', 'approve-transactions', 'reject-transactions', 'print-transactions',
                'view-stocktakes', 'approve-stocktakes',
                'view-inventory', 'export-inventory',
                'view-stock-ledger', 'export-stock-ledger',
                'view-reports', 'export-reports', 'print-reports',
                'view-activity-logs',
            ],

            'accountant' => [
                'view-categories',
                'view-products', 'create-products', 'edit-products',
                'view-suppliers', 'create-suppliers', 'edit-suppliers',
                'view-transactions', 'create-transactions', 'edit-transactions', 'print-transactions',
                'view-stocktakes', 'create-stocktakes',
                'view-inventory', 'export-inventory',
                'view-stock-ledger', 'export-stock-ledger',
                'view-reports', 'export-reports', 'print-reports',
            ],

            'supervisor' => [
                'view-categories', 'view-products', 'view-suppliers',
                'view-transactions', 'print-transactions',
                'view-stocktakes',
                'view-inventory', 'export-inventory',
                'view-stock-ledger', 'export-stock-ledger',
                'view-reports', 'export-reports', 'print-reports',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
