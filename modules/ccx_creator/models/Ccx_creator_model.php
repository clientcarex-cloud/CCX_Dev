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
        }

        return $form ?? [];
    }

    public function save_form(
        array $formData,
        array $fields,
        array $workflow,
        array $logic,
        array $approvalSteps,
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
        $this->log_audit($formId, null, 'form_saved', [
            'name'  => $formData['name'],
            'fields_count' => count($fields),
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

        $workflowIds = $this->db
            ->select('id')
            ->where('form_id', $formId)
            ->get($this->workflowsTable)
            ->result_array();

        if (! empty($workflowIds)) {
            $ids = array_column($workflowIds, 'id');
            $this->db->where_in('workflow_id', $ids)->delete($this->workflowActionsTable);
        }

        $recordIds = $this->db->select('id')->where('form_id', $formId)->get($this->recordsTable)->result_array();
        if (! empty($recordIds)) {
            $ids = array_column($recordIds, 'id');
            $this->db->where_in('record_id', $ids)->delete($this->recordStepsTable);
            $this->db->where_in('record_id', $ids)->delete($this->auditTable);
        }

        $this->db->where('form_id', $formId)->delete($this->workflowsTable);
        $this->db->where('form_id', $formId)->delete($this->fieldsTable);
        $this->db->where('form_id', $formId)->delete($this->recordsTable);
        $this->db->where('form_id', $formId)->delete($this->logicTable);
        $this->db->where('form_id', $formId)->delete($this->approvalStepsTable);

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

        $recordIds = array_column($records, 'id');
        $steps     = $this->get_record_steps($recordIds);

        foreach ($records as &$record) {
            $record['data']   = json_decode($record['data'] ?? '[]', true) ?? [];
            $record['steps']  = $steps[$record['id']] ?? [];
        }

        return $records;
    }

    /**
     * Save a record and trigger workflows + approvals.
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

            $payload[$key]          = $this->sanitize_field_value($field['type'], $value);
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

    public function get_audit_logs(int $formId, int $limit = 50): array
    {
        return $this->db
            ->where('form_id', $formId)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get($this->auditTable)
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
            'definition'  =>
                json_encode(array_values($definition)),
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

    public function get_record_steps(array $recordIds): array
    {
        if (empty($recordIds)) {
            return [];
        }

        $rows = $this->db
            ->where_in('record_id', $recordIds)
            ->order_by('id', 'ASC')
            ->get($this->recordStepsTable)
            ->result_array();

        if (empty($rows)) {
            return [];
        }

        $stepDefinitions = $this->db
            ->where_in('id', array_column($rows, 'step_id'))
            ->get($this->approvalStepsTable)
            ->result_array();

        $definitions = [];
        foreach ($stepDefinitions as $definition) {
            $definitions[$definition['id']] = $definition;
        }

        $grouped = [];
        foreach ($rows as $row) {
            $row['definition'] = $definitions[$row['step_id']] ?? null;
            $grouped[$row['record_id']][] = $row;
        }

        return $grouped;
    }

    public function can_staff_act_on_step(array $step): bool
    {
        $definition = $step['definition'] ?? [];

        if (empty($definition)) {
            return is_admin();
        }

        if (is_admin()) {
            return true;
        }

        $type  = $definition['assignee_type'] ?? 'any';
        $value = $definition['assignee_value'] ?? null;

        switch ($type) {
            case 'staff':
                return (int) $value === get_staff_user_id();
            case 'role':
                return $value === 'staff';
            default:
                return true;
        }
    }

    public function complete_step(int $stepEntryId, string $action, ?string $comment = null): array
    {
        $stepEntry = $this->db
            ->where('id', $stepEntryId)
            ->get($this->recordStepsTable)
            ->row_array();

        if (! $stepEntry) {
            return [false, 'Approval step not found.'];
        }

        if (in_array($stepEntry['status'], ['approved', 'rejected'], true)) {
            return [false, 'This step is already completed.'];
        }

        $definition = $this->db
            ->where('id', $stepEntry['step_id'])
            ->get($this->approvalStepsTable)
            ->row_array();

        $stepEntry['definition'] = $definition;

        if (! $this->can_staff_act_on_step($stepEntry)) {
            return [false, 'You are not allowed to act on this step.'];
        }

        $status = $action === 'reject' ? 'rejected' : 'approved';

        $this->db->where('id', $stepEntryId)->update($this->recordStepsTable, [
            'status'    => $status,
            'acted_by'  => get_staff_user_id(),
            'acted_at'  => date('Y-m-d H:i:s'),
            'comment'   => $comment,
        ]);

        $record = $this->db
            ->where('id', $stepEntry['record_id'])
            ->get($this->recordsTable)
            ->row_array();

        if ($status === 'approved') {
            $this->activate_next_record_step($stepEntry['record_id']);
        } else {
            $this->update_record_status($stepEntry['record_id'], 'rejected');
        }

        $this->log_audit($record['form_id'] ?? null, $stepEntry['record_id'], 'step_' . $status, [
            'step'    => $definition['label'] ?? '',
            'comment' => $comment,
        ]);

        return [true, null];
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
