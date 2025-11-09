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

        $data['title']    = $formId ? 'Edit Builder' : 'Create Builder';
        $data['form']     = $form;
        $data['fields']   = $form['fields'] ?? [];
        $data['workflow'] = $this->prepare_workflow_defaults($form['workflow'] ?? []);

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

        $data['title']   = 'Submissions · ' . $form['name'];
        $data['form']    = $form;
        $data['records'] = $this->ccx_creator_model->get_records($formId);

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

        $fieldsPayload   = $this->decode_json($this->input->post('fields_payload'));
        $workflowPayload = $this->decode_json($this->input->post('workflow_payload'));

        $fields = $this->normalize_fields_payload($fieldsPayload);
        if (empty($fields)) {
            set_alert('danger', 'Add at least one field to save this form.');
            redirect($_SERVER['HTTP_REFERER'] ?? admin_url('ccx_creator'));

            return;
        }

        $workflow = is_array($workflowPayload) ? $workflowPayload : [];

        $result = $this->ccx_creator_model->save_form($formData, $fields, $workflow, $formId);

        if ($result) {
            set_alert('success', 'Form saved successfully.');
            redirect(admin_url('ccx_creator/form/' . $result));

            return;
        }

        set_alert('danger', 'Unable to save the form. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('ccx_creator'));
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
}
