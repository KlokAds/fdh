<?php

return [

    /*
    | URL prefix of the admin panel. Use a non-obvious value in production.
    */
    'admin_path' => env('FDH_ADMIN_PATH', 'fdh-admin'),

    /*
    | Login protection: attempts allowed per email+IP and per IP before lockout.
    */
    'login_max_attempts' => (int) env('FDH_LOGIN_MAX_ATTEMPTS', 5),
    'login_decay_seconds' => (int) env('FDH_LOGIN_DECAY_SECONDS', 900),
    'login_ip_max_attempts' => (int) env('FDH_LOGIN_IP_MAX_ATTEMPTS', 20),

    /*
    | Admins are signed out after this many minutes without activity.
    */
    'admin_idle_minutes' => (int) env('FDH_ADMIN_IDLE_MINUTES', 30),

    /*
    | Public contact form: submissions allowed per IP per hour.
    */
    'inquiry_per_hour' => (int) env('FDH_INQUIRY_PER_HOUR', 5),

    /*
    | Browser push notifications (free inquiry alerts on phones and computers, no SMS gateway).
    | Generate the keys once with `php artisan fdh:vapid` and keep them stable.
    */
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:'.env('MAIL_FROM_ADDRESS', 'admin@example.com')),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    /*
    | Website updates (Admin → System update): the built "release" branch made by GitHub Actions.
    | A token is only needed if the repository is private (fine-grained, read-only "Contents").
    */
    'updates' => [
        'repo' => env('FDH_UPDATE_REPO', 'KlokAds/fdh'),
        'branch' => env('FDH_UPDATE_BRANCH', 'release'),
        'token' => env('FDH_UPDATE_TOKEN'),
    ],

    /*
    | Meta Conversions API version used for server-side Lead events.
    */
    'meta_graph_version' => env('FDH_META_GRAPH_VERSION', 'v23.0'),

    /*
    | Languages the site is published in. The first is the fallback.
    */
    'locales' => [
        'en' => ['name' => 'English', 'short' => 'EN'],
        'bn' => ['name' => 'বাংলা', 'short' => 'বাং'],
    ],

    /*
    | Name of the role that bypasses every permission check.
    */
    'super_role' => 'Super Admin',

    /*
    | Every permission in the admin panel, grouped for the role editor.
    */
    'permissions' => [
        'Inquiries' => [
            'inquiries.view' => 'View inquiries',
            'inquiries.manage' => 'Update status, notes & assignment',
            'inquiries.delete' => 'Delete inquiries',
        ],
        'Website content' => [
            'services.manage' => 'Manage services',
            'projects.manage' => 'Manage projects',
            'team.manage' => 'Manage team members',
            'faqs.manage' => 'Manage FAQs',
            'testimonials.manage' => 'Manage testimonials',
            'content.manage' => 'Edit page texts (hero, about, sections…)',
            'media.manage' => 'Media library (see all photos, delete unused ones)',
        ],
        'Administration' => [
            'settings.manage' => 'Contact info, branding, theme & SEO',
            'users.manage' => 'Manage admin users & roles',
            'logs.view' => 'View security log',
            'system.update' => 'Install website updates',
        ],
    ],

    /*
    | Roles created by the seeder, with their default permissions. Editable later in the admin panel.
    */
    'default_roles' => [
        'Admin' => ['inquiries.view', 'inquiries.manage', 'inquiries.delete', 'services.manage', 'projects.manage', 'team.manage', 'faqs.manage', 'testimonials.manage', 'content.manage', 'media.manage', 'settings.manage', 'logs.view'],
        'Editor' => ['services.manage', 'projects.manage', 'team.manage', 'faqs.manage', 'testimonials.manage', 'content.manage', 'media.manage'],
        'Sales' => ['inquiries.view', 'inquiries.manage'],
        'Viewer' => ['inquiries.view'],
    ],

];
