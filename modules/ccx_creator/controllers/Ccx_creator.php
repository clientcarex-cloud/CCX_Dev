<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ccx_creator extends AdminController
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected $sections = [];

    /**
     * @var Ccx_creator_model
     */
    public $creatorModel;


    public function __construct()
    {
        parent::__construct();

        if (! is_staff_logged_in()) {
            access_denied('CCX Creator');
        }

        $this->sections = ccx_creator_sections();

        $this->load->model('ccx_creator/ccx_creator_model', 'creatorModel');
        $this->load->model('roles_model');
    }

    /**
     * Simple landing page that renders "Hello World".
     */
    public function index(): void
    {
        $data['title'] = 'CCX Creator';
        $data['sections'] = $this->sections;
        $this->load->view('ccx_creator/index', $data);
    }

    public function menus(): void
    {
        $menus = $this->creatorModel->get_menus();
        $roles = $this->roles_model->get();

        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[$role['roleid']] = $role['name'];
        }

        foreach ($menus as &$menu) {
            $menu['role_labels'] = $this->format_role_labels($menu['role_access'], $roleMap);
            $menu['can_delete']  = $this->creatorModel->can_delete_menu($menu);

            foreach ($menu['submenus'] as &$submenu) {
                $submenu['role_labels'] = $this->format_role_labels($submenu['role_access'], $roleMap);
            }
        }

        $data['title']      = 'CCX Creator - Menus';
        $data['menus']      = $menus;
        $data['roles']      = $roles;
        $data['role_map']   = $roleMap;
        $data['section']    = $this->find_section('menus');

        $this->load->view('ccx_creator/menus/index', $data);
    }

    public function menu_form($id = null): void
    {
        $menuId = $id ? (int) $id : null;

        if ($this->input->post()) {
            $response = $this->creatorModel->save_menu($this->input->post(), $menuId);

            if ($response['success']) {
                $message = $menuId ? 'Menu updated successfully.' : 'Menu created successfully.';
                set_alert('success', $message);
                redirect(admin_url('ccx_creator/menus'));
            }

            set_alert('danger', $response['message'] ?? 'Unable to save menu.');
            redirect(current_full_url());
        }

        $data['title']    = $menuId ? 'Edit Menu' : 'Add Menu';
        $data['menu']     = $menuId ? $this->creatorModel->get_menus($menuId) : null;

        if ($menuId && empty($data['menu'])) {
            show_404();
        }

        $data['roles']    = $this->roles_model->get();
        $data['role_map'] = $this->build_role_map($data['roles']);

        $this->load->view('ccx_creator/menus/form', $data);
    }

    public function toggle_menu_status($id): void
    {
        $status  = (int) $this->input->post('status');
        $success = $this->creatorModel->update_menu_status((int) $id, $status);

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => $success]));

            return;
        }

        if ($success) {
            set_alert('success', 'Menu status updated.');
        } else {
            set_alert('danger', 'Unable to update menu status.');
        }

        redirect(admin_url('ccx_creator/menus'));
    }

    public function delete_menu($id): void
    {
        $response = $this->creatorModel->delete_menu((int) $id);

        if ($response['success']) {
            set_alert('success', 'Menu deleted.');
        } else {
            set_alert('warning', $response['message'] ?? 'Unable to delete menu.');
        }

        redirect(admin_url('ccx_creator/menus'));
    }

    public function menu(string $slug, ?string $submenuSlug = null): void
    {
        $menu = $this->creatorModel->get_menu_by_slug($slug);

        if (! $menu) {
            show_404();
        }

        $target = null;

        if ($submenuSlug) {
            foreach ($menu['submenus'] as $sub) {
                if ($sub['slug'] === $submenuSlug) {
                    $target = $sub;
                    break;
                }
            }

            if (! $target) {
                show_404();
            }
        }

        $data['title']         = $submenuSlug ? $target['name'] : $menu['name'];
        $data['menu']          = $menu;
        $data['submenu']       = $target;
        $data['section_label'] = $data['title'];

        $this->load->view('ccx_creator/menus/show', $data);
    }

    public function pages(): void
    {
        $this->render_section('pages');
    }

    public function forms(): void
    {
        $this->render_section('forms');
    }

    public function pop_up(): void
    {
        $this->render_section('pop_up');
    }

    public function charts(): void
    {
        $this->render_section('charts');
    }

    public function dashboard(): void
    {
        $this->render_section('dashboard');
    }

    public function master_data(): void
    {
        $this->render_section('master_data');
    }

    /**
     * Render the placeholder view for the provided section key.
     */
    protected function render_section(string $key): void
    {
        $section = $this->find_section($key);

        if (! $section) {
            show_404();
        }

        $data['title']         = 'CCX Creator - ' . $section['label'];
        $data['section_label'] = $section['label'];

        $this->load->view('ccx_creator/section', $data);
    }

    /**
     * Retrieve a single section definition by its key.
     */
    protected function find_section(string $key): ?array
    {
        foreach ($this->sections as $section) {
            if ($section['key'] === $key) {
                return $section;
            }
        }

        return null;
    }

    protected function format_role_labels(array $roleIds, array $roleMap): string
    {
        if (empty($roleIds)) {
            return 'All Staff';
        }

        $labels = [];

        foreach ($roleIds as $roleId) {
            if (isset($roleMap[$roleId])) {
                $labels[] = $roleMap[$roleId];
            }
        }

        return ! empty($labels) ? implode(', ', $labels) : 'All Staff';
    }

    protected function build_role_map(array $roles): array
    {
        $map = [];

        foreach ($roles as $role) {
            $map[$role['roleid']] = $role['name'];
        }

        return $map;
    }
}
