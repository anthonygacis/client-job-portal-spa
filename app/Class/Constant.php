<?php

namespace App\Class;

class Constant
{
    const ROLES = [
        'Super Admin',
        'User'
    ];

    public static function getPermissionDescriptions()
    {
        return [
            'create' => 'The user can create data',
            'read' => 'The user can read data',
            'update' => 'The user can update data',
            'delete' => 'The user can delete data',
            'bulk-upload' => 'The user can create data by batches',
            'export' => 'The user can export data to pdf, excel, etc.'
        ];
    }

    public static function getPermissions()
    {
        $basicCrud = ['all', 'create', 'read', 'update', 'delete'];
        $bulkCreate = ['bulk-upload'];

        return [
            'user-management' => [
                ...$basicCrud
            ]
        ];
    }

    public static function getRolesWithPermissions()
    {
        return [
            'Super Admin' => '*',
            'User' => []
        ];
    }
}
