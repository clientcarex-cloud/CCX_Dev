<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ccx_creator_model extends App_Model
{
    /**
     * @var string
     */
    protected $menusTable;

    /**
     * @var string
     */
    protected $menuItemsTable;

    public function __construct()
    {
        parent::__construct();

        $this->menusTable     = db_prefix() . 'ccx_creator_menus';
        $this->menuItemsTable = db_prefix() . 'ccx_creator_menu_items';
    }

    /**
     * Fetch menus with their sub menus.
     *
     * @param int|null $id
     * @return array<int|string, mixed>
     */
    public function get_menus(?int $id = null): array
    {
        if ($id) {
            $this->db->where('id', $id);
            $menu = $this->db->get($this->menusTable)->row_array();

            if (! $menu) {
                return [];
            }

            $menu['submenus']    = $this->get_menu_items($menu['id']);
            $menu['role_access'] = $this->decode_roles($menu['role_access']);

            return $menu;
        }

        $menus = $this->db
            ->order_by('created_at', 'DESC')
            ->get($this->menusTable)
            ->result_array();

        foreach ($menus as &$menu) {
            $menu['submenus']    = $this->get_menu_items((int) $menu['id']);
            $menu['role_access'] = $this->decode_roles($menu['role_access']);
        }

        return $menus;
    }

    public function get_menu_by_slug(string $slug): ?array
    {
        $menu = $this->db->where('slug', $slug)->get($this->menusTable)->row_array();

        if (! $menu) {
            return null;
        }

        $menu['submenus']    = $this->get_menu_items((int) $menu['id']);
        $menu['role_access'] = $this->decode_roles($menu['role_access']);

        return $menu;
    }

    /**
     * Persist menu and sub menus.
     *
     * @param array $payload
     * @param int|null $id
     * @return array{success:bool,id?:int,message?:string}
     */
    public function save_menu(array $payload, ?int $id = null): array
    {
        $name = trim($payload['name'] ?? '');

        if ($name === '') {
            return [
                'success' => false,
                'message' => _l('problem_adding', 'Menu Name required'),
            ];
        }

        $now     = date('Y-m-d H:i:s');
        $staffId = get_staff_user_id();

        $menuData = [
            'name'        => $name,
            'icon'        => trim($payload['icon'] ?? ''),
            'description' => trim($payload['description'] ?? ''),
            'role_access' => $this->encode_roles($payload['role_access'] ?? []),
            'updated_at'  => $now,
            'updated_by'  => $staffId,
        ];

        $hasStatusInPayload = array_key_exists('status', $payload);

        if (! $id || $hasStatusInPayload) {
            $menuData['status'] = $hasStatusInPayload ? (int) $payload['status'] : 1;
        }

        if ($menuData['icon'] === '') {
            $menuData['icon'] = 'fa-regular fa-square';
        }

        if ($id) {
            $existing = $this->get_menus($id);

            if (! $existing) {
                return [
                    'success' => false,
                    'message' => _l('not_found', 'Menu not found'),
                ];
            }

            $this->db->where('id', $id);
            $this->db->update($this->menusTable, $menuData);
            $menuId = $id;
        } else {
            $menuData['slug']       = $this->generate_menu_slug($menuData['name']);
            $menuData['created_at'] = $now;
            $menuData['created_by'] = $staffId;

            $this->db->insert($this->menusTable, $menuData);
            $menuId = (int) $this->db->insert_id();
        }

        $this->sync_submenus($menuId, $payload['submenus'] ?? []);

        return [
            'success' => true,
            'id'      => $menuId,
        ];
    }

    /**
     * Toggle menu status.
     */
    public function update_menu_status(int $id, int $status): bool
    {
        $this->db->where('id', $id);

        return (bool) $this->db->update($this->menusTable, [
            'status'     => $status ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => get_staff_user_id(),
        ]);
    }

    /**
     * Remove menu if allowed.
     *
     * @return array{success:bool,message?:string}
     */
    public function delete_menu(int $id): array
    {
        $menu = $this->get_menus($id);

        if (! $menu) {
            return [
                'success' => false,
                'message' => _l('not_found', 'Menu not found'),
            ];
        }

        if (! $this->can_delete_menu($menu)) {
            return [
                'success' => false,
                'message' => _l('problem_deleting', 'Menu can only be deleted on the creation day.'),
            ];
        }

        $this->db->where('id', $id);
        $this->db->delete($this->menusTable);

        return ['success' => true];
    }

    /**
     * Can delete only on the day it was created.
     */
    public function can_delete_menu(array $menu): bool
    {
        if (empty($menu['created_at'])) {
            return true;
        }

        $createdDate = (new DateTime($menu['created_at']))->format('Y-m-d');
        $currentDate = (new DateTime())->format('Y-m-d');

        return $createdDate === $currentDate;
    }

    /**
     * Fetch active menus available for the provided staff id.
     */
    public function get_active_menus_for_staff(?int $staffId): array
    {
        if (! $staffId) {
            return [];
        }

        if (is_admin($staffId)) {
            $roleId = null;
        } else {
            $roleId = $this->get_staff_role_id($staffId);
        }

        $menus = $this->db
            ->where('status', 1)
            ->order_by('created_at', 'DESC')
            ->get($this->menusTable)
            ->result_array();

        $collection = [];

        foreach ($menus as $menu) {
            $allowedRoles = $this->decode_roles($menu['role_access']);

            if (! $this->role_has_access($allowedRoles, $roleId)) {
                continue;
            }

            $menu['role_access'] = $allowedRoles;
            $menu['submenus'] = $this->get_menu_items((int) $menu['id'], true, $roleId);

            $collection[] = $menu;
        }

        return $collection;
    }

    /**
     * Get staff role id.
     */
    public function get_staff_role_id(int $staffId): ?int
    {
        $row = $this->db->select('role')
            ->from(db_prefix() . 'staff')
            ->where('staffid', $staffId)
            ->get()
            ->row();

        return $row ? (int) $row->role : null;
    }

    /**
     * Retrieve menu items.
     *
     * @param int  $menuId
     * @param bool $activeOnly
     * @param int|null $roleId
     * @return array
     */
    public function get_menu_items(int $menuId, bool $activeOnly = false, ?int $roleId = null): array
    {
        $this->db->where('menu_id', $menuId);

        if ($activeOnly) {
            $this->db->where('status', 1);
        }

        $items = $this->db
            ->order_by('position', 'ASC')
            ->get($this->menuItemsTable)
            ->result_array();

        $filtered = [];

        foreach ($items as $item) {
            $roles = $this->decode_roles($item['role_access']);

            if ($activeOnly && ! $this->role_has_access($roles, $roleId)) {
                continue;
            }

            $item['role_access'] = $roles;
            $filtered[]          = $item;
        }

        return $filtered;
    }

    protected function sync_submenus(int $menuId, array $submenus): void
    {
        $this->db->where('menu_id', $menuId);
        $this->db->delete($this->menuItemsTable);

        if (empty($submenus)) {
            return;
        }

        $position = 1;
        $now      = date('Y-m-d H:i:s');

        foreach ($submenus as $submenu) {
            $name = trim($submenu['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $status = isset($submenu['status']) ? (int) $submenu['status'] : 1;

            $record = [
                'menu_id'     => $menuId,
                'slug'        => $this->generate_submenu_slug($name, $menuId, $position),
                'name'        => $name,
                'icon'        => trim($submenu['icon'] ?? '') ?: 'fa-regular fa-circle-dot',
                'role_access' => $this->encode_roles($submenu['role_access'] ?? []),
                'status'      => $status ? 1 : 0,
                'position'    => $position,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            $this->db->insert($this->menuItemsTable, $record);
            $position++;
        }
    }

    protected function role_has_access(array $allowedRoles, ?int $roleId): bool
    {
        if (empty($allowedRoles)) {
            return true;
        }

        if ($roleId === null) {
            return true;
        }

        return in_array($roleId, $allowedRoles);
    }

    protected function decode_roles(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $decoded)));
    }

    protected function encode_roles($roles): string
    {
        if (is_string($roles)) {
            $roles = explode(',', $roles);
        }

        if (! is_array($roles)) {
            $roles = [];
        }

        $roles = array_values(array_filter(array_map('intval', $roles)));

        return json_encode($roles);
    }

    protected function generate_menu_slug(string $name): string
    {
        $base = slug_it($name);

        if ($base === '') {
            $base = 'menu';
        }

        $slug   = $base;
        $suffix = 1;

        while ($this->slug_exists($slug)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function generate_submenu_slug(string $name, int $menuId, int $position): string
    {
        $base = slug_it($name);

        if ($base === '') {
            $base = 'submenu-' . $position;
        }

        $slug   = $base;
        $suffix = 1;

        while ($this->submenu_slug_exists($slug, $menuId)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function slug_exists(string $slug): bool
    {
        return (bool) $this->db
            ->where('slug', $slug)
            ->count_all_results($this->menusTable);
    }

    protected function submenu_slug_exists(string $slug, int $menuId): bool
    {
        return (bool) $this->db
            ->where('slug', $slug)
            ->where('menu_id', $menuId)
            ->count_all_results($this->menuItemsTable);
    }
}
