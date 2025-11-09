<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Public_view extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Public_token_model'); // Lightweight public model
		$this->load->library('session');
    }

    public function index($counter_id = null)
    {
        if (!$counter_id) {
            show_404();
        }
        $counter = $this->Public_token_model->get_counter_by_id($counter_id);
        if (!$counter) {
            show_404();
        }

        // Bypass Prefix view loaderz
        $view_path = FCPATH . 'modules/token_system/views/public_view_page.php';
        $data = ['counter' => $counter];
        extract($data);
        include($view_path);
    }

    public function display_tokens_public_view($counter_id = NULL)
	{
		if (!$counter_id) {
			show_404();
		}
		if (!$this->session->userdata('public')) {
			redirect('admin/token_system/Public_view/index/' . $counter_id);
			exit;
		}
		$this->load->model('Public_token_model');

		$counter = $this->Public_token_model->get_counter_by_id($counter_id);
		if (!$counter) {
			show_404();
		}

		$data['title']            = 'Token Display';
		$data['counter']          = $counter;
		$data['queued_patients']  = $this->Public_token_model->queued_patients($counter_id, "Pending");
		$data['current_patient']  = $this->Public_token_model->queued_patients($counter_id, "Serving");
		$data['display_config']   = $this->Public_token_model->get_display_config($counter->display_id);
		function get_display_config($display_id){
			$ci = &get_instance();
			return $ci->Public_token_model->get_display_config($display_id);
		}
		// Load the final display view
		$view_path = FCPATH . 'modules/token_system/views/display_tokens_public_view.php';
		extract($data);
		include($view_path);
	}
	
	 public function get_token_tables_ajax($counter_id = NULL)
	{
		if (!$counter_id) {
			show_404();
		}

		$this->load->model('Public_token_model');

		$counter = $this->Public_token_model->get_counter_by_id($counter_id);
		if (!$counter) {
			show_404();
		}

		$current_patient = $this->Public_token_model->queued_patients($counter_id, "Serving");
		$queued_patients = $this->Public_token_model->queued_patients($counter_id, "Pending");
		$display_config = $this->Public_token_model->get_display_config($counter->display_id);

		// Columns to show
		if (!$display_config || empty($display_config->display_patient_info)) {
			$columns = ['Image', 'Name', 'Token number', 'Status', 'Doctor Name'];
		} else {
			$columns = explode(',', $display_config->display_patient_info);
		}
		$columns = array_map('trim', $columns);

		$column_map = [
			'Image' => 'Image',
			'Name' => 'Client\'s Name',
			'Token number' => 'Queue No',
			'Status' => 'Status',
			'Doctor Name' => 'Doctor Name'
		];

		// Merge serving + queued
		$patients = [];
		$serving_patient = null;

		foreach ($current_patient as $p) {
			$p['status'] = 'Serving';
			$patients[] = $p;
			$serving_patient = $p; // only one is serving at a time
		}

		foreach ($queued_patients as $p) {
			$p['status'] = 'Pending';
			$patients[] = $p;
		}

		$table1 = array_slice($patients, 0, 12);
		$table2 = array_slice($patients, 12, 12);

		// Build response
		header('Content-Type: application/json');
		echo json_encode([
			'table1' => $this->render_table_html($table1, $columns, $column_map),
			'table2' => $this->render_table_html($table2, $columns, $column_map),
			'serving_token' => isset($serving_patient['token_number']) ? $serving_patient['token_number'] : null,
			'serving_name' => isset($serving_patient['company']) ? $serving_patient['salutation'] . ' ' . $serving_patient['company'] : null,
		]);
	}


    private function render_table_html($table_data, $columns, $column_map)
    {
        ob_start();
        echo "<table class='token-table'><thead><tr>";
        foreach ($columns as $col) {
            echo "<th>" . ($column_map[$col] ?? '') . "</th>";
        }
        echo "</tr></thead><tbody>";

        $serving_token_number = null;
        foreach ($table_data as $row) {
            if (isset($row['status']) && strtolower($row['status']) === 'serving') {
                $serving_token_number = (int)$row['token_number'];
                break;
            }
        }

        for ($i = 0; $i < 12; $i++) {
            $row_class = '';
            $badge = '';

            if (isset($table_data[$i])) {
                $row = $table_data[$i];
                $row_status = strtolower($row['status'] ?? '');
                $token_number = (int)($row['token_number'] ?? 0);

                if ($row_status === 'serving') {
                    $badge = '<span class="badge bg-white text-success border border-success">Consulting</span>';
                    $row_class = 'text-success';
                } elseif ($row_status === 'pending' && isset($serving_token_number) && $token_number === $serving_token_number + 1) {
                    $badge = '<span class="badge bg-white text-primary border border-primary">Next</span>';
                    $row_class = 'text-primary';
                } else {
                    $badge = '<span class="badge bg-white text-warning border border-warning">Waiting</span>';
                    $row_class = 'text-warning';
                }

                echo "<tr class='{$row_class}'>";
                foreach ($columns as $col) {
                    switch ($col) {
                        case 'Image':
                            echo "<td><img src='https://images.vexels.com/media/users/3/134789/isolated/lists/aa4c5ca0e2a83abbf167e49c8a50e834-happy-smile-emoji-emoticon-icon.png' class='rounded-circle' width='40' height='40'></td>";
                            break;
                        case 'Name':
                            echo "<td style='font-weight: 900'>{$row['salutation']} {$row['company']}</td>";
                            break;
                        case 'Token number':
                            echo "<td>#{$row['token_number']}</td>";
                            break;
                        case 'Status':
                            echo "<td>{$badge}</td>";
                            break;
                        case 'Doctor Name':
                            echo "<td>" . ($row['doctor_name'] ?? '-') . "</td>";
                            break;
                        default:
                            echo "<td>-</td>";
                    }
                }
                echo "</tr>";
            } else {
                echo "<tr>";
                foreach ($columns as $col) {
                    echo "<td>-</td>";
                }
                echo "</tr>";
            }
        }

        echo "</tbody></table>";
        return ob_get_clean();
    }


	



	public function verify_password()
	{
		$counter_id = $this->input->post('counter_id');
		$password   = $this->input->post('password');

		if (!$counter_id || !$password) {
			echo '<script>alert("Missing password or counter ID."); window.location.reload();</script>';
			return;
		}

		$this->load->model('Public_token_model');
		$counter = $this->Public_token_model->get_counter_by_id($counter_id);

		if (!$counter) {
			echo '<script>alert("Invalid counter ID."); window.location.reload();</script>';
			return;
		}

		// Match password with auth_code (plain 4-digit code)
		if ($password === $counter->auth_code) {
			$this->session->set_userdata('public', 'yes');

			// ✅ Redirect to display view
			echo '<script>window.location.href = "' . admin_url("token_system/Public_view/display_tokens_public_view/$counter_id") . '";</script>';
		} else {
			echo '<script>alert("Invalid password."); window.location.reload();</script>';
		}
	}

}
