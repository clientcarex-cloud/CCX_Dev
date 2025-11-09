<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ccx_creator_model extends App_Model
{
    protected $formsTable;
    protected $fieldsTable;
    protected $recordsTable;
    protected $workflowsTable;
    protected $workflowActionsTable;
    protected $logicTable;
    protected $approvalStepsTable;
    protected $recordStepsTable;
    protected $auditTable;
    protected $blocksTable;
    protected $dashboardsTable;
    protected $webhooksTable;
    protected $apiTokensTable;
    protected $versionsTable;
    protected $templatesTable;

    public function __construct()
    {
        parent::__construct();

        $prefix                     = db_prefix();
        $this->formsTable           = $prefix . 'ccx_creator_forms';
        $this->fieldsTable          = $prefix . 'ccx_creator_fields';
        $this->recordsTable         = $prefix . 'ccx_creator_records';
        $this->workflowsTable       = $prefix . 'ccx_creator_workflows';
        $this->workflowActionsTable = $prefix . 'ccx_creator_workflow_actions';
        $this->logicTable           = $prefix . 'ccx_creator_logic';
        $this->approvalStepsTable   = $prefix . 'ccx_creator_approval_steps';
        $this->recordStepsTable     = $prefix . 'ccx_creator_record_steps';
        $this->auditTable           = $prefix . 'ccx_creator_audit_logs';
        $this->blocksTable          = $prefix . 'ccx_creator_blocks';
        $this->dashboardsTable      = $prefix . 'ccx_creator_dashboards';
        $this->webhooksTable        = $prefix . 'ccx_creator_webhooks';
        $this->apiTokensTable       = $prefix . 'ccx_creator_api_tokens';
        $this->versionsTable        = $prefix . 'ccx_creator_form_versions';
        $this->templatesTable       = $prefix . 'ccx_creator_templates';
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
            $form['fields']     = $this->get_form_fields($formId);
            $form['workflow']   = $this->get_workflow_email_settings($formId);
            $form['logic']      = $this->get_form_logic($formId);
            $form['approvals']  = $this->get_approval_steps($formId);
            $form['webhooks']   = $this->get_webhooks($formId);
            $form['api_tokens'] = $this->get_api_tokens($formId);
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
            $form['fields']    = $this->get_form_fields((int) $form['id']);
            $form['workflow']  = $this->get_workflow_email_settings((int) $form['id']);
            $form['logic']     = $this->get_form_logic((int) $form['id']);
            $form['approvals'] = $this->get_approval_steps((int) $form['id']);
            $form['webhooks']  = $this->get_webhooks((int) $form['id']);
        }

        return $form ?? [];
    }

    public function save_form(
        array $formData,
        array $fields,
        array $workflow,
        array $logic,
        array $approvalSteps,
        array $webhooks,
        ?int $formId = null
    ) {
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
        $this->save_form_logic($formId, $logic);
        $this->save_approval_steps($formId, $approvalSteps);
        $this->save_webhooks($formId, $webhooks);
        $this->create_form_version($formId, $this->get_form_snapshot($formId), 'Auto snapshot');
        $this->log_audit($formId, null, 'form_saved', [
            'name'          => $formData['name'],
            'fields_count'  => count($fields),
            'webhooks'      => count($webhooks),
        ]);

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

        $records = $this->db->select('id')->where('form_id', $formId)->get($this->recordsTable)->result_array();
        if (! empty($records)) {
            $ids = array_column($records, 'id');
            $this->db->where_in('record_id', $ids)->delete($this->recordStepsTable);
            $this->db->where_in('record_id', $ids)->delete($this->auditTable);
        }

        $this->db->where('form_id', $formId)->delete($this->workflowsTable);
        $this->db->where('form_id', $formId)->delete($this->workflowActionsTable);
        $this->db->where('form_id', $formId)->delete($this->fieldsTable);
        $this->db->where('form_id', $formId)->delete($this->recordsTable);
        $this->db->where('form_id', $formId)->delete($this->logicTable);
        $this->db->where('form_id', $formId)->delete($this->approvalStepsTable);
        $this->db->where('form_id', $formId)->delete($this->recordStepsTable);
        $this->db->where('form_id', $formId)->delete($this->webhooksTable);
        $this->db->where('form_id', $formId)->delete($this->apiTokensTable);
        $this->db->where('id', $formId)->delete($this->formsTable);

        $this->db->trans_complete();

        if (! $this->db->trans_status()) {
            return false;
        }

        $this->log_audit($formId, null, 'form_deleted', ['form_id' => $formId]);

        return true;
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

        if (empty($records)) {
            return [];
        }

        $steps = $this->get_record_steps(array_column($records, 'id'));

        foreach ($records as &$record) {
            $record['data']  = json_decode($record['data'] ?? '[]', true) ?? [];
            $record['steps'] = $steps[$record['id']] ?? [];
        }

        return $records;
    }

    /**
     * Save a record and trigger workflows, logic, approvals, and integrations.
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

            $payload[$key]           = $this->sanitize_field_value($field['type'], $value);
            $pretty[$field['label']] = $payload[$key];
        }

        $beforeContext = [
            'form'   => $form,
            'record' => null,
        ];

        $logicResult = $this->execute_logic('before_submit', $formId, $payload, $beforeContext);
        if ($logicResult !== true) {
            return [false, $logicResult];
        }

        $record = [
            'form_id'    => $formId,
            'data'       => json_encode($payload),
            'status'     => 'pending',
            'created_by' => is_staff_logged_in() ? get_staff_user_id() : null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->recordsTable, $record);
        $recordId = (int) $this->db->insert_id();

        $afterContext = [
            'form'   => $form,
            'record' => ['id' => $recordId],
        ];

        $afterResult = $this->execute_logic('after_submit', $formId, $payload, $afterContext);
        if ($afterResult !== true) {
            $this->db->where('id', $recordId)->delete($this->recordsTable);

            return [false, $afterResult];
        }

        $this->log_audit($formId, $recordId, 'record_created', ['data' => $payload]);

        $approvalSteps = $form['approvals'] ?? [];
        if (! empty($approvalSteps)) {
            $this->initialize_record_steps($recordId, $approvalSteps);
            $this->update_record_status($recordId, 'awaiting_approval');
        }

        $this->trigger_event('on_submit', $form, $recordId, $payload, $pretty);
        $this->dispatch_webhooks('on_submit', $form, $recordId, $payload);

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

    public function get_form_logic(int $formId): array
    {
        $rows = $this->db
            ->where('form_id', $formId)
            ->get($this->logicTable)
            ->result_array();

        $logic = [];
        foreach ($rows as $row) {
            $logic[$row['event']] = $row['script'];
        }

        return $logic;
    }

    public function get_approval_steps(int $formId): array
    {
        return $this->db
            ->where('form_id', $formId)
            ->order_by('position', 'ASC')
            ->get($this->approvalStepsTable)
            ->result_array();
    }

    public function get_blocks(): array
    {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->get($this->blocksTable)
            ->result_array();
    }

    public function save_block(string $name, string $description, array $definition)
    {
        if ($name === '' || empty($definition)) {
            return false;
        }

        $payload = [
            'name'        => $name,
            'description' => $description,
            'definition'  => json_encode(array_values($definition)),
            'created_by'  => get_staff_user_id(),
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->blocksTable, $payload);

        return (int) $this->db->insert_id();
    }

    public function delete_block(int $blockId): bool
    {
        return $this->db->where('id', $blockId)->delete($this->blocksTable);
    }

    public function get_block_definition(int $blockId): array
    {
        $block = $this->db
            ->where('id', $blockId)
            ->get($this->blocksTable)
            ->row_array();

        if (! $block) {
            return [];
        }

        $definition = json_decode($block['definition'] ?? '[]', true);

        return is_array($definition) ? $definition : [];
    }

    public function get_audit_logs(int $formId, int $limit = 50): array
    {
        return $this->db
            ->where('form_id', $formId)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get($this->auditTable)
            ->result_array();
    }

    public function get_webhooks(int $formId): array
    {
        return $this->db
            ->where('form_id', $formId)
            ->order_by('id', 'ASC')
            ->get($this->webhooksTable)
            ->result_array();
    }

    public function get_api_tokens(int $formId): array
    {
        return $this->db
            ->where('form_id', $formId)
            ->order_by('created_at', 'DESC')
            ->get($this->apiTokensTable)
            ->result_array();
    }

    public function create_api_token(int $formId, string $label, array $scopes = ['read'])
    {
        $token = bin2hex(random_bytes(20));
        $payload = [
            'form_id'    => $formId,
            'label'      => $label !== '' ? $label : 'API token',
            'token'      => $token,
            'scopes'     => json_encode($scopes),
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->apiTokensTable, $payload);
        $id = (int) $this->db->insert_id();

        return $this->db
            ->where('id', $id)
            ->get($this->apiTokensTable)
            ->row_array();
    }

    public function revoke_api_token(int $tokenId): bool
    {
        return $this->db->where('id', $tokenId)->delete($this->apiTokensTable);
    }

    public function validate_api_token(string $token, string $scope = 'read')
    {
        $row = $this->db
            ->where('token', $token)
            ->get($this->apiTokensTable)
            ->row_array();

        if (! $row) {
            return false;
        }

        $scopes = json_decode($row['scopes'] ?? '[]', true);
        if (! in_array($scope, (array) $scopes, true)) {
            return false;
        }

        return $row;
    }

    public function create_form_version(int $formId, array $snapshot, ?string $note = null): void
    {
        $this->db->insert($this->versionsTable, [
            'form_id'    => $formId,
            'note'       => $note,
            'snapshot'   => json_encode($snapshot),
            'created_by' => is_staff_logged_in() ? get_staff_user_id() : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_form_versions(int $formId): array
    {
        $versions = $this->db
            ->where('form_id', $formId)
            ->order_by('created_at', 'DESC')
            ->get($this->versionsTable)
            ->result_array();

        foreach ($versions as &$version) {
            $version['snapshot'] = null;
        }

        return $versions;
    }

    public function get_form_version(int $formId, int $versionId): array
    {
        $version = $this->db
            ->where('form_id', $formId)
            ->where('id', $versionId)
            ->get($this->versionsTable)
            ->row_array();

        if (! $version) {
            return [];
        }

        $snapshot = json_decode($version['snapshot'] ?? '[]', true);

        return is_array($snapshot) ? $snapshot : [];
    }

    public function get_templates(?int $templateId = null): array
    {
        if ($templateId === null) {
            return $this->db
                ->order_by('created_at', 'DESC')
                ->get($this->templatesTable)
                ->result_array();
        }

        $template = $this->db
            ->where('id', $templateId)
            ->get($this->templatesTable)
            ->row_array();

        if (! $template) {
            return [];
        }

        $template['payload'] = json_decode($template['payload'] ?? '[]', true) ?? [];

        return $template;
    }

    public function save_template_from_form(int $formId, string $name, string $description = '', string $category = '', array $tags = [], array $snapshot = []): bool
    {
        if ($name === '' || empty($snapshot)) {
            return false;
        }

        $this->db->insert($this->templatesTable, [
            'name'        => $name,
            'description' => $description,
            'category'    => $category,
            'tags'        => implode(',', $tags),
            'payload'     => json_encode($snapshot),
            'created_by'  => is_staff_logged_in() ? get_staff_user_id() : null,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function apply_template(int $templateId): array
    {
        $template = $this->get_templates($templateId);

        return $template['payload'] ?? [];
    }

    public function delete_template(int $templateId): bool
    {
        return $this->db->where('id', $templateId)->delete($this->templatesTable);
    }

    public function get_help_topics(): array
    {
        return [
            [
                'title' => 'Builder basics',
                'body'  => 'Create fields, drag them to reorder, and enable submissions by toggling the Active checkbox.',
            ],
            [
                'title' => 'Automation',
                'body'  => 'Use workflows for email alerts, custom PHP snippets for validation, and webhooks/API tokens for downstream systems.',
            ],
            [
                'title' => 'Approvals',
                'body'  => 'Add multi-step approvals with staff assignments. Each step can approve or reject and leaves an audit trail.',
            ],
            [
                'title' => 'Dashboards',
                'body'  => 'Design stat or table widgets from any builder, then share or embed with a secure token.',
            ],
        ];
    }

    public function save_dashboard(array $dashboard, array $widgets, ?int $dashboardId = null)
    {
        $name = trim($dashboard['name'] ?? '');
        if ($name === '' || empty($widgets)) {
            return false;
        }

        $dashboard['slug']        = $this->generate_unique_slug($dashboard['slug'] ?? $name, $dashboardId, $this->dashboardsTable);
        $dashboard['visibility']  = $dashboard['visibility'] ?? 'private';
        $dashboard['layout']      = json_encode(array_values($widgets));

        $this->db->trans_start();

        if ($dashboardId) {
            $dashboard['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $dashboardId)->update($this->dashboardsTable, $dashboard);
        } else {
            $dashboard['created_by'] = get_staff_user_id();
            $dashboard['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->dashboardsTable, $dashboard);
            $dashboardId = (int) $this->db->insert_id();
        }

        if ($dashboard['visibility'] !== 'private') {
            $this->ensure_dashboard_share_token($dashboardId);
        } else {
            $this->db->where('id', $dashboardId)->update($this->dashboardsTable, ['share_token' => null]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $dashboardId : false;
    }

    public function get_dashboards(?int $dashboardId = null): array
    {
        if ($dashboardId === null) {
            return $this->db
                ->order_by('created_at', 'DESC')
                ->get($this->dashboardsTable)
                ->result_array();
        }

        $dashboard = $this->db
            ->where('id', $dashboardId)
            ->get($this->dashboardsTable)
            ->row_array();

        if ($dashboard) {
            $dashboard['widgets'] = json_decode($dashboard['layout'] ?? '[]', true) ?? [];
        }

        return $dashboard ?? [];
    }

    public function delete_dashboard(int $dashboardId): bool
    {
        return $this->db->where('id', $dashboardId)->delete($this->dashboardsTable);
    }

    public function get_dashboard_data(int $dashboardId): array
    {
        $dashboard = $this->get_dashboards($dashboardId);
        if (empty($dashboard)) {
            return [];
        }

        return [
            'dashboard' => $dashboard,
            'widgets'   => $this->evaluate_dashboard_widgets($dashboard['widgets'] ?? []),
        ];
    }

    public function get_dashboard_by_token(string $token): array
    {
        $dashboard = $this->db
            ->where('share_token', $token)
            ->get($this->dashboardsTable)
            ->row_array();

        if ($dashboard) {
            $dashboard['widgets'] = json_decode($dashboard['layout'] ?? '[]', true) ?? [];
        }

        return $dashboard ?? [];
    }

    public function get_dashboard_data_by_token(string $token): array
    {
        $dashboard = $this->get_dashboard_by_token($token);
        if (empty($dashboard) || $dashboard['visibility'] === 'private') {
            return [];
        }

        return [
            'dashboard' => $dashboard,
            'widgets'   => $this->evaluate_dashboard_widgets($dashboard['widgets'] ?? []),
        ];
    }

    public function ensure_dashboard_share_token(int $dashboardId): string
    {
        $dashboard = $this->db
            ->select('share_token')
            ->where('id', $dashboardId)
            ->get($this->dashboardsTable)
            ->row_array();

        if (! $dashboard) {
            return '';
        }

        if (empty($dashboard['share_token'])) {
            $token = bin2hex(random_bytes(16));
            $this->db->where('id', $dashboardId)->update($this->dashboardsTable, ['share_token' => $token]);

            return $token;
        }

        return $dashboard['share_token'];
    }

    public function get_form_records_for_api(int $formId, int $limit = 50): array
    {
        $records = $this->db
            ->where('form_id', $formId)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get($this->recordsTable)
            ->result_array();

        foreach ($records as &$record) {
            $record['data'] = json_decode($record['data'] ?? '[]', true) ?? [];
        }

        return $records;
    }

    public function get_form_snapshot(int $formId): array
    {
        $form = $this->db
            ->where('id', $formId)
            ->get($this->formsTable)
            ->row_array();

        return [
            'meta'      => [
                'name'        => $form['name'] ?? '',
                'slug'        => $form['slug'] ?? '',
                'description' => $form['description'] ?? '',
                'status'      => $form['status'] ?? 1,
            ],
            'fields'    => $this->get_form_fields($formId),
            'workflow'  => $this->get_workflow_email_settings($formId),
            'logic'     => $this->get_form_logic($formId),
            'approvals' => $this->get_approval_steps($formId),
            'webhooks'  => $this->get_webhooks($formId),
        ];
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
                'form_id'    => $formId,
                'name'       => 'On Submit',
                'event'      => 'on_submit',
                'is_active'  => 1,
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

    private function save_form_logic(int $formId, array $logic): void
    {
        $allowedEvents = ['before_submit', 'after_submit'];

        foreach ($allowedEvents as $event) {
            $script = trim($logic[$event] ?? '');

            $existing = $this->db
                ->where('form_id', $formId)
                ->where('event', $event)
                ->get($this->logicTable)
                ->row_array();

            if ($script === '') {
                if ($existing) {
                    $this->db->where('id', $existing['id'])->delete($this->logicTable);
                }
                continue;
            }

            if ($existing) {
                $this->db->where('id', $existing['id'])->update($this->logicTable, [
                    'script'     => $script,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->db->insert($this->logicTable, [
                    'form_id'    => $formId,
                    'event'      => $event,
                    'script'     => $script,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function save_approval_steps(int $formId, array $steps): void
    {
        $existing = $this->db
            ->select('id')
            ->where('form_id', $formId)
            ->get($this->approvalStepsTable)
            ->result_array();

        $existingIds = array_map('intval', array_column($existing, 'id'));
        $keptIds     = [];

        foreach ($steps as $position => $step) {
            $label = trim($step['label'] ?? '');
            if ($label === '') {
                continue;
            }

            $record = [
                'form_id'        => $formId,
                'label'          => $label,
                'assignee_type'  => $step['assignee_type'] ?? 'any',
                'assignee_value' => $step['assignee_value'] ?? null,
                'position'       => $position + 1,
            ];

            if (! empty($step['id']) && in_array((int) $step['id'], $existingIds, true)) {
                $this->db->where('id', (int) $step['id'])->update($this->approvalStepsTable, $record);
                $keptIds[] = (int) $step['id'];
            } else {
                $this->db->insert($this->approvalStepsTable, $record);
                $keptIds[] = (int) $this->db->insert_id();
            }
        }

        if (! empty($existingIds)) {
            $this->db->where('form_id', $formId);
            if (! empty($keptIds)) {
                $this->db->where_not_in('id', $keptIds);
            }
            $this->db->delete($this->approvalStepsTable);
        }
    }

    private function save_webhooks(int $formId, array $webhooks): void
    {
        $this->db->where('form_id', $formId)->delete($this->webhooksTable);

        foreach ($webhooks as $webhook) {
            $url = trim($webhook['url'] ?? '');
            if ($url === '') {
                continue;
            }

            $this->db->insert($this->webhooksTable, [
                'form_id'    => $formId,
                'event'      => $webhook['event'] ?? 'on_submit',
                'url'        => $url,
                'headers'    => json_encode($webhook['headers'] ?? []),
                'is_active'  => isset($webhook['is_active']) ? (int) $webhook['is_active'] : 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function initialize_record_steps(int $recordId, array $steps): void
    {
        foreach ($steps as $step) {
            $this->db->insert($this->recordStepsTable, [
                'record_id' => $recordId,
                'step_id'   => $step['id'],
                'status'    => 'pending',
            ]);
        }

        $this->activate_next_record_step($recordId);
    }

    private function activate_next_record_step(int $recordId): void
    {
        $steps = $this->db
            ->where('record_id', $recordId)
            ->order_by('id', 'ASC')
            ->get($this->recordStepsTable)
            ->result_array();

        foreach ($steps as $step) {
            if ($step['status'] === 'pending') {
                $this->db->where('id', $step['id'])->update($this->recordStepsTable, [
                    'status' => 'active',
                ]);
                $definition = $this->db
                    ->where('id', $step['step_id'])
                    ->get($this->approvalStepsTable)
                    ->row_array();
                $this->notify_step_assignee($definition, $recordId);

                return;
            }

            if ($step['status'] === 'rejected') {
                return;
            }
        }

        $this->update_record_status($recordId, 'approved');
    }

    private function notify_step_assignee(?array $definition, int $recordId): void
    {
        if (empty($definition)) {
            return;
        }

        $recipient = null;
        if ($definition['assignee_type'] === 'staff' && ! empty($definition['assignee_value'])) {
            $staff = $this->db
                ->select('email, firstname, lastname')
                ->where('staffid', (int) $definition['assignee_value'])
                ->get(db_prefix() . 'staff')
                ->row();

            if ($staff) {
                $recipient = [
                    'email' => $staff->email,
                    'name'  => $staff->firstname . ' ' . $staff->lastname,
                ];
            }
        }

        if (! $recipient && $definition['assignee_type'] === 'role' && $definition['assignee_value'] === 'staff') {
            $recipient = [
                'email' => get_option('smtp_email'),
                'name'  => 'Workflow',
            ];
        }

        if (! $recipient) {
            return;
        }

        $this->load->model('emails_model');
        $subject = 'Approval required: ' . $definition['label'];
        $message = 'Record #' . $recordId . ' is waiting for your approval step: ' . $definition['label'];

        $this->emails_model->send_simple_email($recipient['email'], $subject, nl2br($message));
    }

    private function update_record_status(int $recordId, string $status): void
    {
        $this->db->where('id', $recordId)->update($this->recordsTable, [
            'status'              => $status,
            'last_status_change'  => date('Y-m-d H:i:s'),
        ]);
    }

    private function execute_logic(string $event, int $formId, array &$payload, array $context)
    {
        $script = $this->db
            ->select('script')
            ->where('form_id', $formId)
            ->where('event', $event)
            ->get($this->logicTable)
            ->row_array();

        if (! $script || trim($script['script']) === '') {
            return true;
        }

        return $this->run_script($script['script'], $payload, $context);
    }

    private function run_script(string $script, array &$payload, array $context)
    {
        $runtime = new CcxCreatorScriptRuntime($payload);
        $ccx     = $runtime;
        $form    = $context['form'] ?? null;
        $record  = $context['record'] ?? null;

        try {
            eval($script);
        } catch (\Throwable $exception) {
            return $exception->getMessage();
        }

        if (! empty($runtime->errors)) {
            return implode("\n", $runtime->errors);
        }

        $payload = $runtime->export();

        return true;
    }

    private function log_audit(?int $formId, ?int $recordId, string $action, array $details = []): void
    {
        $this->db->insert($this->auditTable, [
            'form_id'    => $formId,
            'record_id'  => $recordId,
            'action'     => $action,
            'details'    => json_encode($details),
            'staff_id'   => is_staff_logged_in() ? get_staff_user_id() : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
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

    private function dispatch_webhooks(string $event, array $form, int $recordId, array $payload): void
    {
        $webhooks = $this->db
            ->where('form_id', $form['id'])
            ->where('event', $event)
            ->where('is_active', 1)
            ->get($this->webhooksTable)
            ->result_array();

        if (empty($webhooks)) {
            return;
        }

        foreach ($webhooks as $webhook) {
            $this->send_webhook_request($webhook, $form, $recordId, $payload);
        }
    }

    private function send_webhook_request(array $webhook, array $form, int $recordId, array $payload): void
    {
        $body = [
            'event'  => $webhook['event'],
            'form'   => [
                'id'   => $form['id'],
                'name' => $form['name'],
                'slug' => $form['slug'],
            ],
            'record' => [
                'id'        => $recordId,
                'submitted' => date('c'),
                'data'      => $payload,
            ],
        ];

        $headers = ['Content-Type: application/json'];
        $custom  = json_decode($webhook['headers'] ?? '[]', true);
        if (is_array($custom)) {
            foreach ($custom as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
        }

        $ch = curl_init($webhook['url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_TIMEOUT        => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function evaluate_dashboard_widgets(array $widgets): array
    {
        $results = [];

        foreach ($widgets as $widget) {
            $type = $widget['type'] ?? 'stat';
            if ($type === 'table') {
                $results[] = $this->compute_table_widget($widget);
            } else {
                $results[] = $this->compute_stat_widget($widget);
            }
        }

        return $results;
    }

    private function compute_stat_widget(array $widget): array
    {
        $formId    = (int) ($widget['form_id'] ?? 0);
        $metric    = $widget['metric'] ?? 'count';
        $fieldKey  = $widget['field_key'] ?? null;
        $records   = $this->db->where('form_id', $formId)->get($this->recordsTable)->result_array();
        $values    = [];

        foreach ($records as $record) {
            $data = json_decode($record['data'] ?? '[]', true) ?? [];
            if ($fieldKey === null) {
                $values[] = 1;
            } elseif (isset($data[$fieldKey]) && is_numeric($data[$fieldKey])) {
                $values[] = (float) $data[$fieldKey];
            }
        }

        $value = 0;
        if ($metric === 'sum') {
            $value = array_sum($values);
        } elseif ($metric === 'avg') {
            $value = count($values) ? array_sum($values) / count($values) : 0;
        } else {
            $value = count($values);
        }

        return [
            'type'   => 'stat',
            'title'  => $widget['title'] ?? 'Stat',
            'value'  => round($value, 2),
            'color'  => $widget['color'] ?? '#4a90e2',
            'metric' => $metric,
        ];
    }

    private function compute_table_widget(array $widget): array
    {
        $formId  = (int) ($widget['form_id'] ?? 0);
        $limit   = (int) ($widget['limit'] ?? 5);
        $records = $this->db
            ->where('form_id', $formId)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get($this->recordsTable)
            ->result_array();

        $rows = [];
        foreach ($records as $record) {
            $rows[] = [
                'id'         => $record['id'],
                'status'     => $record['status'],
                'created_at' => _dt($record['created_at']),
                'data'       => json_decode($record['data'] ?? '[]', true) ?? [],
            ];
        }

        return [
            'type'  => 'table',
            'title' => $widget['title'] ?? 'Recent records',
            'rows'  => $rows,
        ];
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

    private function generate_unique_slug(string $value, ?int $ignoreId = null, ?string $table = null): string
    {
        $slug = slug_it($value);
        if ($slug === '') {
            $slug = 'ccx-form-' . uniqid();
        }
        $candidate = $slug;
        $suffix    = 1;

        while ($this->slug_exists($candidate, $ignoreId, $table)) {
            $candidate = $slug . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function slug_exists(string $slug, ?int $ignoreId = null, ?string $table = null): bool
    {
        $tableName = $table ?? $this->formsTable;

        $this->db->where('slug', $slug);
        if ($ignoreId) {
            $this->db->where('id !=', $ignoreId);
        }

        return $this->db->count_all_results($tableName) > 0;
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

class CcxCreatorScriptRuntime
{
    private $payload;
    public $errors = [];

    public function __construct(array &$payload)
    {
        $this->payload = &$payload;
    }

    public function set(string $key, $value): void
    {
        $this->payload[$key] = $value;
    }

    public function get(string $key)
    {
        return $this->payload[$key] ?? null;
    }

    public function ensure(string $key, callable $callback, string $message): void
    {
        if (! $callback($this->get($key))) {
            $this->error($message);
        }
    }

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }

    public function export(): array
    {
        return $this->payload;
    }
}
