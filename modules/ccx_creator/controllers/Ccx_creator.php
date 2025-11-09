<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ccx_creator extends AdminController
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected $sections = [];

    public function __construct()
    {
        parent::__construct();

        if (! is_staff_logged_in()) {
            access_denied('CCX Creator');
        }

        $this->sections = ccx_creator_sections();
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
        $this->render_section('menus');
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
}
