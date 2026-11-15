<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    |
    | The initial roles shipped with Solve CMS. Roles flagged as `system`
    | cannot be renamed or deleted from the administration dashboard.
    |
    */

    'roles' => [
        'super-admin' => [
            'label' => 'Super Admin',
            'description' => 'Full, unrestricted access to every part of Solve CMS.',
        ],
        'administrator' => [
            'label' => 'Administrator',
            'description' => 'Manages content, website, SEO and users.',
        ],
        'editor' => [
            'label' => 'Editor',
            'description' => 'Publishes and manages all content.',
        ],
        'author' => [
            'label' => 'Author',
            'description' => 'Creates and manages their own content.',
        ],
        'support-agent' => [
            'label' => 'Support Agent',
            'description' => 'Handles live chat conversations with visitors.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Grouped by the area they belong to. The keys are dotted permission names
    | checked via `$user->hasPermission('posts.create')`.
    |
    */

    'permissions' => [
        'Content' => [
            'posts.view' => 'View posts',
            'posts.create' => 'Create posts',
            'posts.update' => 'Edit posts',
            'posts.update.own' => 'Edit own posts',
            'posts.delete' => 'Delete posts',
            'posts.publish' => 'Publish posts',
            'pages.view' => 'View pages',
            'pages.create' => 'Create pages',
            'pages.update' => 'Edit pages',
            'pages.delete' => 'Delete pages',
            'categories.manage' => 'Manage categories',
            'tags.manage' => 'Manage tags',
            'comments.moderate' => 'Moderate comments',
            'media.view' => 'View media',
            'media.upload' => 'Upload media',
            'media.delete' => 'Delete media',
            'menus.manage' => 'Manage navigation menus',
        ],
        'SEO' => [
            'seo.view' => 'View SEO settings',
            'seo.manage' => 'Manage SEO metadata',
            'seo.redirects' => 'Manage redirects',
            'seo.technical' => 'Manage technical SEO',
        ],
        'Website' => [
            'themes.view' => 'View themes',
            'themes.manage' => 'Activate and install themes',
            'appearance.customize' => 'Customize appearance',
            'settings.manage' => 'Manage website settings',
        ],
        'Communication' => [
            'chat.view' => 'View conversations',
            'chat.reply' => 'Reply to conversations',
            'chat.assign' => 'Assign conversations',
            'chat.manage' => 'Manage chat agents',
        ],
        'Administration' => [
            'users.view' => 'View users',
            'users.manage' => 'Manage users',
            'roles.manage' => 'Manage roles and permissions',
            'notifications.view' => 'View notifications',
            'activity.view' => 'View activity logs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role permissions
    |--------------------------------------------------------------------------
    |
    | The `*` wildcard grants every permission in the application.
    |
    */

    'role_permissions' => [
        'super-admin' => ['*'],

        'administrator' => [
            'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',
            'pages.view', 'pages.create', 'pages.update', 'pages.delete',
            'categories.manage', 'tags.manage', 'comments.moderate',
            'media.view', 'media.upload', 'media.delete', 'menus.manage',
            'seo.view', 'seo.manage', 'seo.redirects', 'seo.technical',
            'themes.view', 'themes.manage', 'appearance.customize', 'settings.manage',
            'chat.view', 'chat.reply', 'chat.assign', 'chat.manage',
            'users.view', 'users.manage', 'notifications.view', 'activity.view',
        ],

        'editor' => [
            'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',
            'pages.view', 'pages.create', 'pages.update', 'pages.delete',
            'categories.manage', 'tags.manage', 'comments.moderate',
            'media.view', 'media.upload', 'menus.manage',
            'seo.view', 'seo.manage', 'seo.redirects',
            'themes.view', 'notifications.view',
        ],

        'author' => [
            'posts.view', 'posts.create', 'posts.update.own',
            'pages.view', 'pages.create', 'pages.update',
            'media.view', 'media.upload',
            'seo.view', 'notifications.view',
        ],

        'support-agent' => [
            'chat.view', 'chat.reply',
            'notifications.view',
        ],
    ],

];
