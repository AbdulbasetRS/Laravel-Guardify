<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Roles and Permissions
    |--------------------------------------------------------------------------
    |
    | This array defines the default roles and their permissions that will be
    | seeded when running the `guardify:seed` Artisan command.
    |
    * @package Abdulbaset\Guardify
    * @author Abdulbaset R. Sayed
    * @link https://github.com/AbdulbasetRS/laravel-guardify
    * @version 1.0.0
    * @license MIT
    */
    'roles' => [
        'admin' => [
            'name' => 'Administrator',
            'permissions' => [
                'create',
                'read',
                'update',
                'delete',
            ],
        ],
        'editor' => [
            'name' => 'Editor',
            'permissions' => [
                'create',
                'read',
                'update',
            ],
        ],
        'viewer' => [
            'name' => 'Viewer',
            'permissions' => [
                'read',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This is the User model used by the package. Update this if you're using
    | a custom user model.
    |
    */
    'user_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | These are the table names used by the package. You can change these
    | table names if you want to customize them.
    |
    */
    'tables' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'role_user' => 'role_user',
        'permission_role' => 'permission_role',
    ],
];
