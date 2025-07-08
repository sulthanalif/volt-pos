<?php

return [
    [
        'type' => 'item',
        'title' => 'Dashboard',
        'icon' => 'fas.gauge',
        'link' => 'dashboard',
        'can' => 'dashboard'
    ],
    [
        'type' => 'sub',
        'title' => 'Master Data',
        'icon' => 'fas.database',
        'can'  => 'master-data',
        'submenu' => [
            [
                'type' => 'item',
                'title' => 'Categories',
                'icon' => 'fas.tags',
                'link' => 'categories',
                'can'  => 'manage-categories',
            ],
            [
                'type' => 'item',
                'title' => 'Units',
                'icon' => 'fas.ruler',
                'link' => 'units',
                'can'  => 'manage-units',
            ],
            [
                'type' => 'item',
                'title' => 'Suppliers',
                'icon' => 'fas.truck',
                'link' => 'suppliers',
                'can'  => 'manage-suppliers',
            ],
            [
                'type' => 'item',
                'title' => 'Products',
                'icon' => 'fas.boxes',
                'link' => 'products',
                'can'  => 'manage-products',
            ],
            [
                'type' => 'item',
                'title' => 'Tables',
                'icon' => 'fas.chair',
                'link' => 'tables',
                'can'  => 'manage-tables',
            ],
            [
                'type' => 'item',
                'title' => 'Users',
                'icon' => 'fas.users',
                'link' => 'users',
                'can'  => 'manage-users',
            ],
        ]
    ],
    [
        'type' => 'sub',
        'title' => 'Settings',
        'icon' => 'fas.gear',
        'can'  => 'settings',
        'submenu' => [
            [
                'title' => 'Roles',
                'icon' => 'fas.user-tie',
                'link' => 'roles',
                'can'  => 'manage-roles',
            ],
            [
                'title' => 'Permissions',
                'icon' => 'fas.users-line',
                'link' => 'permissions',
                'can'  => 'manage-permissions',
            ],
        ]
    ],
];