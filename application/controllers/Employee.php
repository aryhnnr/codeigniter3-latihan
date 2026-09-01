<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // $this->only_admin();
        $this->load->model('Employee_model');
        $this->load->model('Sub_department_model');
        $this->load->model('User_model');
        $this->load->model('Role_model');
        $this->load->library('form_validation');
    }

    // Index
    public function index() {
        $data['title']            = 'Employee';
        $data['user_role']        = $this->session->userdata('role');
        $data['departement_list'] = $this->Employee_model->get_departemen();
        $data['position_list']    = $this->Employee_model->get_position();
        $this->load->view('templates/header', $data);
        $this->load->view('employee/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_by_department($department_id){
        $data = $this->Sub_department_model->get_by_department_id($department_id);
        echo json_encode($data);
    }

    // Create — form tambah employee + akun user
    public function create() {
        $data['title']                 = 'Tambah Employee';
        $data['user_role']             = $this->session->userdata('role');
        $data['departments']           = $this->Employee_model->get_departemen();
        $data['positions']             = $this->Employee_model->get_position();
        $data['roles']                 = $this->Role_model->get_all_active();
        $data['employee_code_preview'] = $this->Employee_model->generate_employee_code();
        $data['mode']                  = 'create';
        $this->load->view('templates/header', $data);
        $this->load->view('employee/form', $data);
        $this->load->view('templates/footer');
    }

    // Store — simpan employee + user dalam 1 transaction
    public function store() {
        // ---- Validasi Employee ----
        $this->form_validation->set_rules('employee_name', 'Nama Employee', 'required|trim');
        $this->form_validation->set_rules('departemen_id',  'Departemen',    'required');
        $this->form_validation->set_rules('position_id',    'Posisi',        'required');
        $this->form_validation->set_rules('salary',         'Salary',        'required');

        // ---- Validasi User ----
        $this->form_validation->set_rules('username', 'Username', 'required|min_length[4]|max_length[50]');
        $this->form_validation->set_rules('email',    'Email',    'required|valid_email');
        $this->form_validation->set_rules('nomor_hp', 'Nomor HP', 'trim|max_length[20]');
        $this->form_validation->set_rules('role_id',  'Role',     'required');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $username = trim($this->input->post('username'));
        $email    = trim($this->input->post('email'));
        $nomor_hp = trim((string) $this->input->post('nomor_hp'));

        // Cek duplikasi user
        if ($this->User_model->username_exists($username)) {
            echo json_encode(['status' => 'failed', 'errors' => ['username' => 'Username sudah digunakan.']]);
            return;
        }
        if ($this->User_model->email_exists($email)) {
            echo json_encode(['status' => 'failed', 'errors' => ['email' => 'Email sudah digunakan.']]);
            return;
        }
        if ($this->User_model->nomor_hp_exists($nomor_hp)) {
            echo json_encode(['status' => 'failed', 'errors' => ['nomor_hp' => 'Nomor HP sudah digunakan.']]);
            return;
        }

        $employee_code = $this->Employee_model->generate_employee_code();

        $emp_data = [
            'employee_code'    => $employee_code,
            'employee_name'    => $this->input->post('employee_name'),
            'department_id'    => $this->input->post('departemen_id'),
            'sub_department_id'=> !empty($this->input->post('sub_department_id')) ? (int) $this->input->post('sub_department_id') : null,
            'position_id'      => $this->input->post('position_id'),
            'salary'           => $this->input->post('salary'),
            'status'           => 1,
            'join_date'        => date('Y-m-d'),
        ];

        $user_data = [
            'username'  => $username,
            'email'     => $email,
            'nomor_hp'  => $nomor_hp,
            'password'  => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'role_id'   => (int) $this->input->post('role_id'),
            'status'    => 1,
        ];

        $result = $this->Employee_model->insert_with_user($emp_data, $user_data);

        if ($result['success']) {
            $this->session->set_flashdata('success', 'Employee ' . $employee_code . ' berhasil ditambahkan beserta akun user.');
            echo json_encode(['status' => 'success', 'message' => 'Employee berhasil ditambahkan.']);
        } else {
            echo json_encode(['status' => 'failed', 'message' => $result['message']]);
        }
    }

    // Edit — form edit employee + tampilkan akun user (read-only jika ada)
    public function edit($id = null) {
        if (empty($id)) {
            $this->session->set_flashdata('error', 'Employee tidak ditemukan.');
            redirect('employee');
        }

        $employee = $this->Employee_model->get_by_id($id);
        if (!$employee) {
            $this->session->set_flashdata('error', 'Employee tidak ditemukan.');
            redirect('employee');
        }

        $data['employee']    = $employee;
        $data['departments'] = $this->Employee_model->get_departemen();
        $data['positions']   = $this->Employee_model->get_position();
        $data['roles']       = $this->Role_model->get_all_active();
        $data['account']     = $this->User_model->get_by_employee_id($id); // null jika belum punya akun
        $data['mode']        = 'edit';
        $data['user_role']   = $this->session->userdata('role');

        $this->load->view('templates/header', $data);
        $this->load->view('employee/edit', $data);
        $this->load->view('templates/footer');
    }

    // Update — update employee + insert akun user jika belum ada (transaction)
    public function update($id = null) {
        if (empty($id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID Employee tidak valid.']);
            return;
        }

        $employee = $this->Employee_model->get_by_id($id);
        if (!$employee) {
            echo json_encode(['status' => 'failed', 'message' => 'Employee tidak ditemukan.']);
            return;
        }

        // Cek apakah employee sudah punya akun
        $existing_account = $this->User_model->get_by_employee_id($id);

        // ---- Validasi Employee ----
        $this->form_validation->set_rules('employee_name', 'Nama Employee', 'required|trim');
        $this->form_validation->set_rules('departemen_id',  'Departemen',    'required');
        $this->form_validation->set_rules('sub_department_id', 'Sub Department', 'numeric');
        $this->form_validation->set_rules('position_id',    'Posisi',        'required');
        $this->form_validation->set_rules('salary',         'Salary',        'required');
        $this->form_validation->set_rules('status',         'Status',        'required');

        // ---- Validasi User ----
        $this->form_validation->set_rules('username',    'Username',          'required|min_length[4]|max_length[50]');
        $this->form_validation->set_rules('email',       'Email',             'required|valid_email');
        $this->form_validation->set_rules('nomor_hp',    'Nomor HP',          'trim|max_length[20]');
        $this->form_validation->set_rules('role_id',     'Role / Hak Akses',  'required');
        $this->form_validation->set_rules('user_status', 'Status Akun User',  'required|in_list[0,1]');

        $password = (string) $this->input->post('password');
        if (!$existing_account) {
            // Jika akun baru, password wajib diisi
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        } else {
            // Jika akun sudah ada, password opsional (hanya divalidasi jika diisi)
            if ($password !== '') {
                $this->form_validation->set_rules('password', 'Password', 'min_length[6]');
            }
        }

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $username    = trim($this->input->post('username'));
        $email       = trim($this->input->post('email'));
        $nomor_hp    = trim((string) $this->input->post('nomor_hp'));
        $user_status = (int) $this->input->post('user_status');
        $role_id     = (int) $this->input->post('role_id');
        $exclude_uid = $existing_account ? $existing_account->user_id : null;

        // Cek duplikasi username, email, nomor hp
        if ($this->User_model->username_exists($username, $exclude_uid)) {
            echo json_encode(['status' => 'failed', 'errors' => ['username' => 'Username sudah digunakan.']]);
            return;
        }
        if ($this->User_model->email_exists($email, $exclude_uid)) {
            echo json_encode(['status' => 'failed', 'errors' => ['email' => 'Email sudah digunakan.']]);
            return;
        }
        if ($this->User_model->nomor_hp_exists($nomor_hp, $exclude_uid)) {
            echo json_encode(['status' => 'failed', 'errors' => ['nomor_hp' => 'Nomor HP sudah digunakan.']]);
            return;
        }

        $emp_data = [
            'employee_name'     => $this->input->post('employee_name'),
            'department_id'     => $this->input->post('departemen_id'),
            'sub_department_id' => !empty($this->input->post('sub_department_id')) ? (int) $this->input->post('sub_department_id') : null,
            'position_id'       => $this->input->post('position_id'),
            'salary'            => $this->input->post('salary'),
            'status'            => (int) $this->input->post('status'),
        ];

        $user_data = [
            'username' => $username,
            'email'    => $email,
            'nomor_hp' => $nomor_hp,
            'role_id'  => $role_id,
            'status'   => $user_status,
        ];

        if ($existing_account) {
            $user_data['user_id'] = $existing_account->user_id;
        }

        if ($password !== '') {
            $user_data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $result = $this->Employee_model->update_with_user($id, $emp_data, $user_data);

        if ($result['success']) {
            $this->session->set_flashdata('success', 'Employee dan Akun User berhasil diperbarui.');
            echo json_encode(['status' => 'success', 'message' => 'Employee dan Akun User berhasil diperbarui.']);
        } else {
            echo json_encode(['status' => 'failed', 'message' => $result['message']]);
        }
    }

    // AJAX helpers
    public function get_data() {
        $filter = [
            'department_id' => $this->input->post('department_id'),
            'status'        => $this->input->post('status'),
            'position_id'   => $this->input->post('position_id'),
        ];

        $data = $this->Employee_model->get_data($filter);

        header('Content-Type: application/json');
        echo json_encode(['data' => $data]);
    }

    public function get_detail($id) {
        $data = $this->Employee_model->get_by_id($id);

        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
