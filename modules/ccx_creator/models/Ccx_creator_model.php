<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ccx_creator_model extends App_Model
{
    protected $formsTable;
    protected $fieldsTable;
    protected $recordsTable;
    protected $workflowsTable;
    protected $workflowActionsTable;

    public function __construct()
    {
        parent::__construct();

        $prefix                   = db_prefix();
        $this->formsTable         = $prefix . 'ccx_creator_forms';
        $this->fieldsTable        = $prefix . 'ccx_creator_fields';
        $this->recordsTable       = $prefix . 'ccx_creator_records';
        $this->workflowsTable     = $prefix . 'ccx_creator_workflows';
        $this->workflowActionsTable = $prefix . 'ccx_creator_workflow_actions';
    }

    public function get_forms(?int $formId = null): array
    {
        if ($formId === null) {
            return $this->db
                ->order_by('created_at', 'DESC')
                ->get($this->formsTable)
                ->result_array();
        }

        $form = $this->db
            ->where('id', $formId)
            ->get($this->formsTable)
            ->row_array();

        if ($form) {
            $form['fields']   = $this->get_form_fields($formId);
            $form['workflow'] = $this->get_workflow_email_settings($formId);
        }

        return $form ?? [];
    }

    public function get_form_by_slug(string $slug): array
    {
        $form = $this->db
            ->where('slug', $slug)
            ->get($this->formsTable)
            ->row_array();

        if ($form) {
            $form['fields']   = $this->get_form_fields((int) $form['id']);
            $form['workflow'] = $this->get_workflow_email_settings((int) $form['id']);
        }

        return $form ?? [];
    }

    public function save_form(array $formData, array $fields, array $workflow, ?int $formId = null)
    {
        $name = trim($formData['name'] ?? '');
        if ($name === '') {
            return false;
        }

        $slug = $formData['slug'] ?? $name;
        $formData['slug']   = $this->generate_unique_slug($slug, $formId);
        $formData['status'] = (int) ($formData['status'] ?? 0);

        $this->db->trans_start();

        if ($formId) {
            $formData['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $formId)->update($this->formsTable, $formData);
        } else {
            $formData['created_by'] = get_staff_user_id();
            $formData['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->formsTable, $formData);
            $formId = (int) $this->db->insert_id();
        }

        $this->persist_fields($formId, $fields);
        $this->save_submit_workflow($formId, $workflow);

        $this->db->trans_complete();

        if (! $this->db->trans_status()) {
            log_activity('CCX Creator: failed saving form - DB transaction error.');

            return false;
        }

        return $formId;
    }

    public function delete_form(int $formId): bool
    {
        $this->db->trans_start();

        $workflowIds = $this->db
            ->select('id')
            ->where('form_id', $formId)
            ->get($this->workflowsTable)
            ->result_array();

        if (! empty($workflowIds)) {
            $ids = array_column($workflowIds, 'id');
            $this->db->where_in('workflow_id', $ids)->delete($this->workflowActionsTable);
        }

        $this->db->where('form_id', $formId)->delete($this->workflowsTable);
        $this->db->where('form_id', $formId)->delete($this->fieldsTable);
        $this->db->where('form_id', $formId)->delete($this->recordsTable);
        $this->db->where('id', $formId)->delete($this->formsTable);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function get_form_fields(int $formId): array
    {
        return $this->db
            ->where('form_id', $formId)
            ->order_by('position', 'ASC')
            ->get($this->fieldsTable)
            ->result_array();
    }

    public function get_records(int $formId): array
    {
        $records = $this->db
            ->where('form_id', $formId)
            ->order_by('created_at', 'DESC')
            ->get($this->recordsTable)
            ->result_array();

        foreach ($records as &$record) {
            $record['data'] = json_decode($record['data'] ?? '[]', true) ?? [];
        }

        return $records;
    }

    /**
     * Save a record and trigger workflows.
     *
     * @return array [int|false $recordId, null|string $error]
     */
    public function save_record(int $formId, array $input): array
    {
        $form = $this->get_forms($formId);
        if (empty($form)) {
            return [false, 'Form not found.'];
        }

        $fields = $form['fields'] ?? [];
        if (empty($fields)) {
            return [false, 'This form does not have any fields configured.'];
        }

        $payload = [];
        $pretty  = [];

        foreach ($fields as $field) {
            $key   = $field['field_key'];
            $value = $input[$key] ?? null;

            if ($field['type'] === 'checkbox') {
                $value = $input[$key] ?? '0';
            }

            if ($field['type'] === 'select') {
                $value = $input[$key] ?? null;
            }

            if ($field['required'] && ($value === null || $value === '')) {
                return [false, $field['label'] . ' is required.'];
            }

            $payload[$key]        = $this->sanitize_field_value($field['type'], $value);
            $pretty[$field['label']] = $payload[$key];
        }

        $record = [
            'form_id'    => $formId,
            'data'       => json_encode($payload),
            'created_by' => is_staff_logged_in() ? get_staff_user_id() : null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->recordsTable, $record);
        $recordId = (int) $this->db->insert_id();

        $this->trigger_event('on_submit', $form, $recordId, $payload, $pretty);

        return [$recordId, null];
    }

    public function get_workflow_email_settings(int $formId): array
    {
        $workflow = $this->db
            ->where('form_id', $formId)
            ->where('event', 'on_submit')
            ->get($this->workflowsTable)
            ->row_array();

        if (! $workflow) {
            return [];
        }

        $action = $this->db
            ->where('workflow_id', $workflow['id'])
            ->get($this->workflowActionsTable)
            ->row_array();

        if (! $action) {
            return [];
        }

        $settings = json_decode($action['settings'] ?? '[]', true);

        return is_array($settings) ? $settings : [];
    }

    private function persist_fields(int $formId, array $fields): void
    {
        $existing = $this->db
            ->select('id')
            ->where('form_id', $formId)
            ->get($this->fieldsTable)
            ->result_array();

        $existingIds = array_map('intval', array_column($existing, 'id'));
        $keptIds     = [];

        foreach ($fields as $position => $field) {
            $label = trim($field['label'] ?? '');
            if ($label === '') {
                continue;
            }

            $fieldKey = $this->generate_field_key($field['field_key'] ?? $label, $formId, $field['id'] ?? null);

            $record = [
                'form_id'      => $formId,
                'label'        => $label,
                'field_key'    => $fieldKey,
                'type'         => $field['type'] ?? 'text',
                'required'     => (int) ($field['required'] ?? 0),
                'configuration'=> json_encode($field['configuration'] ?? []),
                'position'     => $position + 1,
            ];

            if (! empty($field['id']) && in_array((int) $field['id'], $existingIds, true)) {
                $this->db->where('id', (int) $field['id'])->update($this->fieldsTable, $record);
                $keptIds[] = (int) $field['id'];
            } else {
                $this->db->insert($this->fieldsTable, $record);
                $keptIds[] = (int) $this->db->insert_id();
            }
        }

        if (! empty($existingIds)) {
            $this->db->where('form_id', $formId);
            if (! empty($keptIds)) {
                $this->db->where_not_in('id', $keptIds);
            }
            $this->db->delete($this->fieldsTable);
        }
    }

    private function save_submit_workflow(int $formId, array $settings): void
    {
        $existing = $this->db
            ->where('form_id', $formId)
            ->where('event', 'on_submit')
            ->get($this->workflowsTable)
            ->row_array();

        if ($existing) {
            $workflowId = (int) $existing['id'];
        } else {
            $this->db->insert($this->workflowsTable, [
                'form_id' => $formId,
                'name'    => 'On Submit',
                'event'   => 'on_submit',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $workflowId = (int) $this->db->insert_id();
        }

        $payload = [
            'recipients'   => trim($settings['recipients'] ?? ''),
            'subject'      => $settings['subject'] ?? 'New submission for {{form_name}}',
            'message'      => $settings['message'] ?? "A new record was submitted.\n\n{{record_table}}",
            'include_data' => (bool) ($settings['include_data'] ?? true),
        ];

        $action = $this->db
            ->where('workflow_id', $workflowId)
            ->get($this->workflowActionsTable)
            ->row_array();

        if ($action) {
            $this->db->where('id', $action['id'])->update($this->workflowActionsTable, [
                'action_type' => 'email',
                'settings'    => json_encode($payload),
            ]);
        } else {
            $this->db->insert($this->workflowActionsTable, [
                'workflow_id' => $workflowId,
                'action_type' => 'email',
                'settings'    => json_encode($payload),
                'position'    => 1,
            ]);
        }
    }

    private function trigger_event(string $event, array $form, int $recordId, array $payload, array $pretty): void
    {
        $workflows = $this->db
            ->where('form_id', $form['id'])
            ->where('event', $event)
            ->where('is_active', 1)
            ->get($this->workflowsTable)
            ->result_array();

        if (empty($workflows)) {
            return;
        }

        foreach ($workflows as $workflow) {
            $actions = $this->db
                ->where('workflow_id', $workflow['id'])
                ->order_by('position', 'ASC')
                ->get($this->workflowActionsTable)
                ->result_array();

            foreach ($actions as $action) {
                $settings = json_decode($action['settings'] ?? '[]', true) ?? [];
                if ($action['action_type'] === 'email') {
                    $this->execute_email_action($settings, $form, $recordId, $payload, $pretty);
                }
            }
        }
    }

    private function execute_email_action(array $settings, array $form, int $recordId, array $payload, array $pretty): void
    {
        $recipients = array_filter(array_map('trim', explode(',', $settings['recipients'] ?? '')));
        if (empty($recipients)) {
            return;
        }

        $tableHtml = $this->build_record_table_html($pretty);
        $context   = [
            'form_name'    => $form['name'],
            'record_id'    => (string) $recordId,
            'record_table' => $tableHtml,
            'record_json'  => json_encode($payload),
            'record_count' => (string) count($pretty),
        ];

        $subject = $this->replace_tokens($settings['subject'] ?? 'New submission for {{form_name}}', $context);
        $message = $settings['message'] ?? "A new record was submitted.\n\n{{record_table}}";

        if (! empty($settings['include_data'])) {
            $message .= "\n\n{{record_table}}";
        }

        $message = nl2br($this->replace_tokens($message, $context));

        $this->load->model('emails_model');

        foreach ($recipients as $email) {
            $this->emails_model->send_simple_email($email, $subject, $message);
        }
    }

    private function build_record_table_html(array $pretty): string
    {
        if (empty($pretty)) {
            return '';
        }

        $rows = '';
        foreach ($pretty as $label => $value) {
            $rows .= '<tr><th style="text-align:left;padding:4px 8px;border:1px solid #ddd;">'
                . html_escape($label)
                . '</th><td style="padding:4px 8px;border:1px solid #ddd;">'
                . html_escape(is_array($value) ? json_encode($value) : (string) $value)
                . '</td></tr>';
        }

        return '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;margin-top:10px;">'
            . $rows
            . '</table>';
    }

    private function replace_tokens(string $text, array $context): string
    {
        foreach ($context as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }

        return $text;
    }

    private function sanitize_field_value(string $type, $value)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'number':
                return is_numeric($value) ? $value : null;
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
            case 'date':
                return $value !== '' ? $value : null;
            default:
                return is_string($value) ? trim($value) : $value;
        }
    }

    private function generate_unique_slug(string $value, ?int $ignoreId = null): string
    {
        $slug = slug_it($value);
        if ($slug === '') {
            $slug = 'ccx-form-' . uniqid();
        }
        $candidate = $slug;
        $suffix    = 1;

        while ($this->slug_exists($candidate, $ignoreId)) {
            $candidate = $slug . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function slug_exists(string $slug, ?int $ignoreId = null): bool
    {
        $this->db->where('slug', $slug);
        if ($ignoreId) {
            $this->db->where('id !=', $ignoreId);
        }

        return $this->db->count_all_results($this->formsTable) > 0;
    }

    private function generate_field_key(string $value, int $formId, ?int $ignoreId = null): string
    {
        $key     = slug_it($value, ['separator' => '_']);
        $key     = $key !== '' ? $key : 'field_' . uniqid();
        $baseKey = $key;
        $suffix  = 1;

        while ($this->field_key_exists($key, $formId, $ignoreId)) {
            $key = $baseKey . '_' . $suffix;
            $suffix++;
        }

        return $key;
    }

    private function field_key_exists(string $key, int $formId, ?int $ignoreId = null): bool
    {
        $this->db->where('form_id', $formId);
        $this->db->where('field_key', $key);
        if ($ignoreId) {
            $this->db->where('id !=', $ignoreId);
        }

        return $this->db->count_all_results($this->fieldsTable) > 0;
    }
}
