<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $data = [];

    public function __construct() {
        parent::__construct();

        if (!$this->session->userdata('user_id')) {
            redirect('auth');
        }

        // Selalu inject info user ke semua view
        $this->data['logged_user'] = [
            'user_id'     => $this->session->userdata('user_id'),
            'employee_id' => $this->session->userdata('employee_id'),
            'username'    => $this->session->userdata('username'),
            'email'       => $this->session->userdata('email'),
            'role_id'     => $this->session->userdata('role_id'),
            'role'        => $this->session->userdata('role'),
            'role_name'   => $this->session->userdata('role_name'),
        ];
        $this->data['user_role'] = $this->session->userdata('role');
    }

    protected function render($view, $title = '') {
        $this->data['title'] = $title;
        $this->load->view('templates/header', $this->data);
        $this->load->view($view, $this->data);
        $this->load->view('templates/footer');
    }

    protected function only_admin() {
        $role = $this->session->userdata('role');
        $role_id = $this->session->userdata('role_id');
        if ($role !== 'admin' && $role_id != 1) {
            $this->session->set_flashdata('error', 'Anda tidak punya akses ke halaman ini.');
            redirect('dashboard');
        }
    }

    protected function only_staff_or_admin() {
        $role = $this->session->userdata('role');
        $role_id = $this->session->userdata('role_id');
        if (!in_array($role, ['admin', 'staff']) && $role_id != 1) {
            $this->session->set_flashdata('error', 'Anda tidak punya akses ke halaman ini.');
            redirect('dashboard');
        }
    }
}