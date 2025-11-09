<?php

/* if ($cache_data != "2a725c56fc868386a88ae82de670256d84aca1f692e3c5d259a77dcfe86ad81c42c739b716829dabfbe533d7f6f3d9d5c00e140c34564557cefa20f0452822ce6680342cbe08e837d7fcf2e9bdc796c7f9edc59639bab899428e2d6c1f79873954ca082eb8ae84b1df817b35f1a3f4bf709cd84927662ad6547600e2e2e216611849586085470c919205f5d306baf6ae79733069b3d7eef88388d968cfd04b6b") {
    die;
} */

// Inject sidebar menu and links for perfshield module
hooks()->add_action('admin_init', 'perfshield_module_init_menu_items');
function perfshield_module_init_menu_items()
{
    if (has_permission('perfshield', '', 'view')) {
        get_instance()->app_menu->add_sidebar_menu_item('perfshield', [
            'slug' => 'perfshield',
            'name' => _l('perfshield'),
            'icon' => 'fa fa-solid fa-shield-halved',
            'position' => 20,
        ]);

        get_instance()->app_menu->add_sidebar_children_item('perfshield', [
            'slug' => 'perfshield_dashboard',
            'name' => _l('perfshield_dashboard'),
            'href' => admin_url('perfshield'),
            'position' => 1,
        ]);

        get_instance()->app_menu->add_sidebar_children_item('perfshield', [
            'slug' => 'perfshield_settings',
            'name' => _l('settings'),
            'href' => admin_url('perfshield/bruteForceSettings'),
            'position' => 2,
        ]);
    }
}

hooks()->add_filter('staff_permissions', function ($permissions) {
    $allPermissionsArray = [
        'view' => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    $permissions['perfshield'] = [
        'name' => _l('perfshield'),
        'capabilities' => $allPermissionsArray,
    ];

    return $permissions;
});