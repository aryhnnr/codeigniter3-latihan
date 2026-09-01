<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('Auth_model');
        $this->load->library('form_validation');
    }

    public function index(){
        $user_id = $this->session->userdata('user_id');
        $user    = $this->Auth_model->get_by_id($user_id);

        if (!$user){
            $this->session->set_flashdata('error', 'Data user tidak ditemukan.');
            redirect('dashboard');
        }

        $this->data['title'] = 'Profile Saya';
        $this->data['user']  = $user;
        $this->load->view('templates/header', $this->data);
        $this->load->view('profile/index', $this->data);
        $this->load->view('templates/footer');
    }

    public function change_password(){
        header('Content-Type: application/json');

        $user_id = $this->session->userdata('user_id');
        $user    = $this->Auth_model->get_by_id($user_id);

        if (!$user){
            echo json_encode(['status' => false, 'message' => 'User tidak ditemukan.']);
            return;
        }

        $this->form_validation->set_rules('current_password', 'Password Saat Ini', 'required');
        $this->form_validation->set_rules('new_password', 'Password Baru', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|matches[new_password]', [
            'matches' => 'Konfirmasi password tidak sama dengan password baru.'
        ]);

        if ($this->form_validation->run() == FALSE){
            echo json_encode(['status' => false, 'errors' => $this->form_validation->error_array()]);
            return;
        }

        $current = $this->input->post('current_password');
        if (!password_verify($current, $user->password)){
            echo json_encode(['status' => false, 'errors' => ['current_password' => 'Password saat ini tidak sesuai.']]);
            return;
        }

        $new_hashed = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
        $this->Auth_model->update_password($user_id, $new_hashed);

        echo json_encode(['status' => true, 'message' => 'Password berhasil diubah.']);
    }
}
