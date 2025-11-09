<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ccx_creator extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! (is_admin() || staff_can('view', 'ccx_creator') || staff_can('view_own', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $this->load->model('ccx_creator/ccx_creator_model');
    }

    public function index(): void
    {
        $data['title'] = 'CCX Creator';
        $data['forms'] = $this->ccx_creator_model->get_forms();

        $this->load->view('ccx_creator/forms/index', $data);
    }

    public function dashboards(): void
    {
        if (! (is_admin() || staff_can('view', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $data['title']      = 'Dashboards';
        $data['dashboards'] = $this->ccx_creator_model->get_dashboards();

        $this->load->view('ccx_creator/dashboards/index', $data);
    }

    public function dashboard($id = null): void
    {
        if (! (is_admin() || staff_can('create', 'ccx_creator') || staff_can('edit', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $dashboardId = $id ? (int) $id : null;

        if ($this->input->post()) {
            $this->handle_dashboard_save($dashboardId);

            return;
        }

        $dashboard = $dashboardId ? $this->ccx_creator_model->get_dashboards($dashboardId) : [];
        if ($dashboardId && empty($dashboard)) {
            show_404();
        }

        $forms = $this->ccx_creator_model->get_forms();
        foreach ($forms as &$form) {
            $form['fields'] = $this->ccx_creator_model->get_form_fields($form['id']);
        }

        $data['title']      = $dashboardId ? 'Edit Dashboard' : 'Create Dashboard';
        $data['dashboard']  = $dashboard;
        $data['widgets']    = $dashboard['widgets'] ?? [];
        $data['forms']      = $forms;
        $data['shareToken'] = $dashboard['share_token'] ?? null;

        $this->load->view('ccx_creator/dashboards/editor', $data);
    }

    public function dashboard_data($id): void
    {
        if (! (is_admin() || staff_can('view', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $payload = $this->ccx_creator_model->get_dashboard_data((int) $id);
        if (empty($payload)) {
            show_404();
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    public function dashboard_share($id): void
    {
        if (! (is_admin() || staff_can('edit', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $dashboard = $this->ccx_creator_model->get_dashboards((int) $id);
        if (empty($dashboard) || $dashboard['visibility'] === 'private') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false]));

            return;
        }

        $token = $this->ccx_creator_model->ensure_dashboard_share_token((int) $id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => $token !== '',
                'token'   => $token,
                'url'     => $token ? site_url('ccx_creator_public/dashboard/' . $token) : null,
            ]));
    }

    public function dashboard_delete($id): void
    {
        if (! (is_admin() || staff_can('delete', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $success = $this->ccx_creator_model->delete_dashboard((int) $id);

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => $success]));

            return;
        }

        set_alert($success ? 'success' : 'danger', $success ? 'Dashboard deleted.' : 'Unable to delete dashboard.');
        redirect(admin_url('ccx_creator/dashboards'));
    }

    public function form($id = null): void
    {
        if (! (is_admin() || staff_can('create', 'ccx_creator') || staff_can('edit', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $formId = $id ? (int) $id : null;

        if ($this->input->post()) {
            $this->handle_form_save($formId);

            return;
        }

        $form = $formId ? $this->ccx_creator_model->get_forms($formId) : [];
        if ($formId && empty($form)) {
            show_404();
        }

        if (! empty($form['fields'])) {
            $form['fields'] = $this->with_field_configuration($form['fields']);
        }

        $this->load->model('staff_model');

        $staffMembers = $this->staff_model->get('', ['active' => 1]);

        $data['title']        = $formId ? 'Edit Builder' : 'Create Builder';
        $data['form']         = $form;
        $data['fields']       = $form['fields'] ?? [];
        $data['workflow']     = $this->prepare_workflow_defaults($form['workflow'] ?? []);
        $data['logic']        = $this->prepare_logic_defaults($form['logic'] ?? []);
        $data['approvals']    = $form['approvals'] ?? [];
        $data['blocks']       = $this->ccx_creator_model->get_blocks();
        $data['staff']        = $staffMembers;
        $data['staffOptions'] = $this->prepare_staff_options($staffMembers);
        $data['webhooks']     = $form['webhooks'] ?? [];
        $data['apiTokens']    = $form['api_tokens'] ?? [];

        $this->load->view('ccx_creator/forms/editor', $data);
    }

    public function records($id): void
    {
        $formId = (int) $id;
        $form   = $this->ccx_creator_model->get_forms($formId);

        if (empty($form)) {
            show_404();
        }

        if (! empty($form['fields'])) {
            $form['fields'] = $this->with_field_configuration($form['fields']);
        }

        $records = $this->ccx_creator_model->get_records($formId);
        foreach ($records as &$record) {
            foreach ($record['steps'] as &$step) {
                $step['can_act'] = $this->ccx_creator_model->can_staff_act_on_step($step);
            }
        }

        $data['title']   = 'Submissions · ' . $form['name'];
        $data['form']    = $form;
        $data['records'] = $records;
        $data['audits']  = $this->ccx_creator_model->get_audit_logs($formId);

        $this->load->view('ccx_creator/forms/records', $data);
    }

    public function entry($id): void
    {
        $formId = (int) $id;
        $form   = $this->ccx_creator_model->get_forms($formId);

        if (empty($form)) {
            show_404();
        }

        if ($this->input->post()) {
            [$recordId, $error] = $this->ccx_creator_model->save_record($formId, $this->input->post());

            if ($recordId) {
                set_alert('success', 'Submission saved.');
                redirect(admin_url('ccx_creator/records/' . $formId));

                return;
            }

            if ($error) {
                set_alert('danger', $error);
            }
        }

        if (! empty($form['fields'])) {
            $form['fields'] = $this->with_field_configuration($form['fields']);
        }

        $data['title']  = 'Submit · ' . $form['name'];
        $data['form']   = $form;
        $data['fields'] = $form['fields'] ?? [];

        $this->load->view('ccx_creator/forms/entry', $data);
    }

    public function delete($id): void
    {
        if (! (is_admin() || staff_can('delete', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $formId = (int) $id;

        if ($this->ccx_creator_model->delete_form($formId)) {
            set_alert('success', 'Form removed.');
        } else {
            set_alert('danger', 'Unable to delete this form.');
        }

        redirect(admin_url('ccx_creator'));
    }

    private function handle_form_save(?int $formId): void
    {
        $formData = [
            'name'        => $this->input->post('name', true),
            'slug'        => $this->input->post('slug', true),
            'description' => $this->input->post('description', true),
            'status'      => $this->input->post('status') ? 1 : 0,
        ];

        $fieldsPayload     = $this->decode_json($this->input->post('fields_payload'));
        $workflowPayload   = $this->decode_json($this->input->post('workflow_payload'));
        $logicPayload      = $this->decode_json($this->input->post('logic_payload'));
        $approvalsPayload  = $this->decode_json($this->input->post('approvals_payload'));
        $webhooksPayload   = $this->decode_json($this->input->post('webhooks_payload'));

        $fields = $this->normalize_fields_payload($fieldsPayload);
        if (empty($fields)) {
            set_alert('danger', 'Add at least one field to save this form.');
            redirect($_SERVER['HTTP_REFERER'] ?? admin_url('ccx_creator'));

            return;
        }

        $workflow  = is_array($workflowPayload) ? $workflowPayload : [];
        $logic     = $this->normalize_logic_payload($logicPayload);
        $approvals = $this->normalize_approvals_payload($approvalsPayload);
        $webhooks  = $this->normalize_webhooks_payload($webhooksPayload);

        $result = $this->ccx_creator_model->save_form($formData, $fields, $workflow, $logic, $approvals, $webhooks, $formId);

        if ($result) {
            set_alert('success', 'Form saved successfully.');
            redirect(admin_url('ccx_creator/form/' . $result));

            return;
        }

        set_alert('danger', 'Unable to save the form. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('ccx_creator'));
    }

    private function handle_dashboard_save(?int $dashboardId): void
    {
        $details = [
            'name'        => $this->input->post('name', true),
            'slug'        => $this->input->post('slug', true),
            'description' => $this->input->post('description', true),
            'visibility'  => $this->input->post('visibility', true) ?: 'private',
        ];

        $widgetsPayload = $this->decode_json($this->input->post('widgets_payload'));
        $widgets        = $this->normalize_dashboard_widgets($widgetsPayload);

        if (empty($widgets)) {
            set_alert('danger', 'Add at least one widget.');
            redirect($_SERVER['HTTP_REFERER'] ?? admin_url('ccx_creator/dashboards'));

            return;
        }

        $result = $this->ccx_creator_model->save_dashboard($details, $widgets, $dashboardId);

        if ($result) {
            set_alert('success', 'Dashboard saved.');
            redirect(admin_url('ccx_creator/dashboard/' . $result));

            return;
        }

        set_alert('danger', 'Unable to save dashboard. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('ccx_creator/dashboards'));
    }

    private function decode_json(?string $raw)
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }

    private function normalize_fields_payload($fields): array
    {
        if (! is_array($fields)) {
            return [];
        }

        $normalized = [];

        foreach ($fields as $field) {
            $label = trim($field['label'] ?? '');
            if ($label === '') {
                continue;
            }

            $options = $field['options'] ?? ($field['configuration']['options'] ?? []);
            if (is_string($options)) {
                $options = preg_split("/\r\n|\r|\n/", $options);
            }

            $options = array_values(array_filter(array_map('trim', (array) $options)));

            $config = [
                'placeholder' => $field['placeholder'] ?? ($field['configuration']['placeholder'] ?? ''),
                'options'     => $options,
            ];

            $normalized[] = [
                'id'            => ! empty($field['id']) ? (int) $field['id'] : null,
                'label'         => $label,
                'field_key'     => trim($field['field_key'] ?? ''),
                'type'          => $field['type'] ?? 'text',
                'required'      => ! empty($field['required']),
                'configuration' => $config,
            ];
        }

        return $normalized;
    }

    private function prepare_workflow_defaults(array $workflow): array
    {
        return [
            'recipients'   => $workflow['recipients'] ?? get_option('smtp_email'),
            'subject'      => $workflow['subject'] ?? 'New submission for {{form_name}}',
            'message'      => $workflow['message'] ?? "A new record was submitted.\n\n{{record_table}}",
            'include_data' => isset($workflow['include_data']) ? (bool) $workflow['include_data'] : true,
        ];
    }

    private function prepare_logic_defaults(array $logic): array
    {
        return [
            'before_submit' => $logic['before_submit'] ?? '',
            'after_submit'  => $logic['after_submit'] ?? '',
        ];
    }

    private function with_field_configuration(array $fields): array
    {
        foreach ($fields as &$field) {
            if (is_string($field['configuration'] ?? null)) {
                $decoded = json_decode($field['configuration'], true);
                $field['configuration'] = is_array($decoded) ? $decoded : [];
            } elseif (! is_array($field['configuration'])) {
                $field['configuration'] = [];
            }
        }

        return $fields;
    }

    private function normalize_logic_payload($logic): array
    {
        if (! is_array($logic)) {
            return [
                'before_submit' => '',
                'after_submit'  => '',
            ];
        }

        return [
            'before_submit' => trim($logic['before_submit'] ?? ''),
            'after_submit'  => trim($logic['after_submit'] ?? ''),
        ];
    }

    private function normalize_approvals_payload($steps): array
    {
        if (! is_array($steps)) {
            return [];
        }

        $normalized = [];

        foreach ($steps as $step) {
            $label = trim($step['label'] ?? '');
            if ($label === '') {
                continue;
            }

            $normalized[] = [
                'id'             => ! empty($step['id']) ? (int) $step['id'] : null,
                'label'          => $label,
                'assignee_type'  => $step['assignee_type'] ?? 'any',
                'assignee_value' => $step['assignee_value'] ?? null,
            ];
        }

        return $normalized;
    }

    private function normalize_webhooks_payload($hooks): array
    {
        if (! is_array($hooks)) {
            return [];
        }

        $normalized = [];

        foreach ($hooks as $hook) {
            $url = trim($hook['url'] ?? '');
            if ($url === '') {
                continue;
            }

            $headers = $hook['headers'] ?? [];
            if (is_string($headers)) {
                $headers = $this->decode_json($headers);
            }

            $normalized[] = [
                'url'       => $url,
                'event'     => $hook['event'] ?? 'on_submit',
                'headers'   => is_array($headers) ? $headers : [],
                'is_active' => isset($hook['is_active']) ? (int) $hook['is_active'] : 1,
            ];
        }

        return $normalized;
    }

    private function normalize_dashboard_widgets($widgets): array
    {
        if (! is_array($widgets)) {
            return [];
        }

        $normalized = [];

        foreach ($widgets as $widget) {
            $formId = (int) ($widget['form_id'] ?? 0);
            if ($formId <= 0) {
                continue;
            }

            $type   = in_array($widget['type'] ?? 'stat', ['stat', 'table'], true) ? $widget['type'] : 'stat';
            $metric = in_array($widget['metric'] ?? 'count', ['count', 'sum', 'avg'], true) ? $widget['metric'] : 'count';

            $normalized[] = [
                'title'     => trim($widget['title'] ?? 'Widget'),
                'type'      => $type,
                'form_id'   => $formId,
                'metric'    => $metric,
                'field_key' => $widget['field_key'] ?? null,
                'color'     => $widget['color'] ?? '#4a90e2',
                'limit'     => (int) ($widget['limit'] ?? 5),
            ];
        }

        return $normalized;
    }

    public function create_token($formId): void
    {
        if (! (is_admin() || staff_can('create', 'ccx_creator') || staff_can('edit', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $label = $this->input->post('label', true);
        $token = $this->ccx_creator_model->create_api_token((int) $formId, $label, ['read']);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => (bool) $token,
                'token'   => $token,
            ]));
    }

    public function revoke_token($tokenId): void
    {
        if (! (is_admin() || staff_can('delete', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $success = $this->ccx_creator_model->revoke_api_token((int) $tokenId);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => $success]));
    }

    private function prepare_staff_options($staff): array
    {
        if (! is_array($staff)) {
            return [];
        }

        $options = [];

        foreach ($staff as $member) {
            $id = is_array($member) ? ($member['staffid'] ?? null) : ($member->staffid ?? null);
            $first = is_array($member) ? ($member['firstname'] ?? '') : ($member->firstname ?? '');
            $last  = is_array($member) ? ($member['lastname'] ?? '') : ($member->lastname ?? '');

            if (! $id) {
                continue;
            }

            $options[] = [
                'id'   => (int) $id,
                'name' => trim($first . ' ' . $last),
            ];
        }

        return $options;
    }

    public function record_step_action($stepId): void
    {
        $action  = $this->input->post('action');
        $comment = $this->input->post('comment');

        [$success, $error] = $this->ccx_creator_model->complete_step((int) $stepId, $action, $comment);

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => $success,
                    'message' => $success ? _l('approved') : $error,
                ]));
            return;
        }

        if ($success) {
            set_alert('success', 'Step updated.');
        } else {
            set_alert('danger', $error ?: 'Unable to update step.');
        }

        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('ccx_creator'));
    }

    public function blocks_save(): void
    {
        if (! (is_admin() || staff_can('create', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $name        = trim($this->input->post('name', true));
        $description = trim($this->input->post('description', true));
        $definition  = $this->decode_json($this->input->post('definition'));

        $id = $this->ccx_creator_model->save_block($name, $description, $definition);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => (bool) $id,
                'id'      => $id,
            ]));
    }

    public function blocks_load($id): void
    {
        if (! (is_admin() || staff_can('view', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $definition = $this->ccx_creator_model->get_block_definition((int) $id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'    => ! empty($definition),
                'definition' => $definition,
            ]));
    }

    public function blocks_delete($id): void
    {
        if (! (is_admin() || staff_can('delete', 'ccx_creator'))) {
            access_denied('ccx_creator');
        }

        $success = $this->ccx_creator_model->delete_block((int) $id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => $success]));
    }
}
