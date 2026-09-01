<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->only_admin();
        $this->load->model('User_model');
        $this->load->model('Employee_model');
        $this->load->library('form_validation');
    }

    public function index(){
        $this->data['title'] = 'Manajemen User';
        $this->data['users'] = $this->User_model->get_all();
        $this->load->view('templates/header', $this->data);
        $this->load->view('user/index', $this->data);
        $this->load->view('templates/footer');
    }

    public function create(){
        // Ambil employee yang belum punya akun
        $all_employees = $this->Employee_model->get_data(['status' => 1]);
        $has_account = [];
        foreach ($this->User_model->get_all() as $u) {
            if ($u->employee_id) $has_account[] = $u->employee_id;
        }
        $available = array_filter($all_employees, function($e) use ($has_account){
            return !in_array($e->employee_id, $has_account);
        });

        $this->data['title']      = 'Tambah User';
        $this->data['employees']  = array_values($available);
        $this->load->view('templates/header', $this->data);
        $this->load->view('user/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function edit($id = null){
        if (empty($id)) {
            $this->session->set_flashdata('error', 'User tidak ditemukan.');
            redirect('user');
        }

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            $this->session->set_flashdata('error', 'User tidak ditemukan.');
            redirect('user');
        }

        $this->data['title'] = 'Edit Akun User';
        $this->data['user']  = $user;
        $this->data['mode']  = 'edit';
        $this->load->view('templates/header', $this->data);
        $this->load->view('user/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function store(){
        $this->form_validation->set_rules('employee_id', 'Employee', 'required');
        $this->form_validation->set_rules('username', 'Username', 'required|min_length[4]|max_length[50]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('nomor_hp', 'Nomor HP', 'trim|max_length[20]');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,staff]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');

        if ($this->form_validation->run() == FALSE){
            echo json_encode(['status' => 'failed', 'errors' => $this->form_validation->error_array()]);
            return;
        }

        $username = $this->input->post('username');
        $email    = $this->input->post('email');
        $nomor_hp = trim((string) $this->input->post('nomor_hp'));

        if ($this->User_model->username_exists($username)){
            echo json_encode(['status' => 'failed', 'errors' => ['username' => 'Username sudah digunakan.']]);
            return;
        }
        if ($this->User_model->email_exists($email)){
            echo json_encode(['status' => 'failed', 'errors' => ['email' => 'Email sudah digunakan.']]);
            return;
        }
        if ($this->User_model->nomor_hp_exists($nomor_hp)){
            echo json_encode(['status' => 'failed', 'errors' => ['nomor_hp' => 'Nomor HP sudah digunakan user lain.']]);
            return;
        }

        $data = [
            'employee_id' => $this->input->post('employee_id'),
            'username'    => $username,
            'email'       => $email,
            'nomor_hp'    => $nomor_hp,
            'password'    => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'role'        => $this->input->post('role'),
            'status'      => 1,
        ];

        $this->User_model->insert($data);
        if ($this->db->affected_rows() > 0){
            echo json_encode(['status' => 'success', 'message' => 'User ' . $username . ' berhasil ditambahkan.']);
        } else {
            echo json_encode(['status' => 'failed', 'message' => 'Gagal menyimpan data.']);
        }
    }

    public function update($id = null){
        if (empty($id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID user tidak valid.']);
            return;
        }

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            echo json_encode(['status' => 'failed', 'message' => 'User tidak ditemukan.']);
            return;
        }

        $this->form_validation->set_rules('username', 'Username', 'required|min_length[4]|max_length[50]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('nomor_hp', 'Nomor HP', 'trim|max_length[20]');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,staff]');
        $password = (string) $this->input->post('password');
        if ($password !== '') {
            $this->form_validation->set_rules('password', 'Password', 'min_length[6]');
        }

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'failed', 'errors' => $this->form_validation->error_array()]);
            return;
        }

        $username = $this->input->post('username');
        $email    = $this->input->post('email');
        $role     = $this->input->post('role');
        $nomor_hp = trim((string) $this->input->post('nomor_hp'));

        if ($this->User_model->username_exists($username, $id)) {
            echo json_encode(['status' => 'failed', 'errors' => ['username' => 'Username sudah digunakan.']]);
            return;
        }
        if ($this->User_model->email_exists($email, $id)) {
            echo json_encode(['status' => 'failed', 'errors' => ['email' => 'Email sudah digunakan.']]);
            return;
        }
        if ($this->User_model->nomor_hp_exists($nomor_hp, $id)) {
            echo json_encode(['status' => 'failed', 'errors' => ['nomor_hp' => 'Nomor HP sudah digunakan user lain.']]);
            return;
        }
        if ($user->user_id == $this->session->userdata('user_id') && $role !== $user->role) {
            echo json_encode(['status' => 'failed', 'errors' => ['role' => 'Role akun sendiri tidak dapat diubah.']]);
            return;
        }

        $data = [
            'username' => $username,
            'email'    => $email,
            'nomor_hp' => $nomor_hp,
            'role'     => $role,
        ];

        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->User_model->update($id, $data);
        echo json_encode(['status' => 'success', 'message' => 'Akun user berhasil diperbarui.']);
    }

    public function toggle_status($id){
        if (empty($id)){
            echo json_encode(['status' => false, 'message' => 'ID tidak valid.']);
            return;
        }

        $user = $this->User_model->get_by_id($id);
        if (!$user){
            echo json_encode(['status' => false, 'message' => 'User tidak ditemukan.']);
            return;
        }

        // Jangan nonaktifkan diri sendiri
        if ($user->user_id == $this->session->userdata('user_id')){
            echo json_encode(['status' => false, 'message' => 'Tidak dapat mengubah status akun sendiri.']);
            return;
        }

        $this->User_model->toggle_status($id);
        $new_status = $user->status == 1 ? 'Nonaktif' : 'Aktif';
        echo json_encode(['status' => true, 'message' => 'Status user diubah menjadi ' . $new_status . '.', 'new_status' => $user->status == 1 ? 0 : 1]);
    }
}
