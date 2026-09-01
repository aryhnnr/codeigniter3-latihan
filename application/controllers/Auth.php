<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model('Auth_model');
        $this->load->library('form_validation');
    }

    public function index(){
        if($this->session->userdata('is_logged_in')){
            redirect('dashboard');
            return;
        }

        $this->load->view('auth/login', ['captcha_image' => $this->_generate_captcha()]);
    }
    private function _generate_captcha(){
        $this->load->helper('captcha');

        $img_path = FCPATH . 'captcha-images/';
        if (!is_dir($img_path)) {
            mkdir($img_path, 0755, true);
        }

        $vals = array(
            'img_path'      => $img_path,
            'img_url'       => base_url('captcha-images/'),
            // 'font_path'   => FCPATH . 'assets/fonts/texb.ttf',
            'img_width'     => 150,
            'img_height'    => 30,
            'expiration'    => 7200,
            'word_length'   => 5,
            'font_size'     => 16,
            'img_id'        => 'Imageid',
            'pool'          => '0123456789',
            // 'pool'          => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'colors'        => array(
                'background' => array(255, 255, 255),
                'border'     => array(255, 255, 255),
                'text'       => array(0, 0, 0),
                'grid'       => array(255, 40, 40)
            )
        );

        $cap = create_captcha($vals);

        $this->session->set_userdata([
            'captcha_word' => $cap['word'],
            'captcha_time' => $cap['time'],
        ]);
        return $cap['image'];
    }

    public function login(){
        header('Content-Type: application/json');

        $this->form_validation->set_rules('identifier', 'Username/Email/No. HP', 'required', [
            'required' => '%s wajib diisi'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'required', [
            'required' => '%s wajib diisi'
        ]);
        $this->form_validation->set_rules('captcha', 'Captcha', 'required|callback_check_captcha', [
            'required' => '%s wajib diisi'
        ]);

        if($this->form_validation->run() == FALSE){
            $errors = [];

            if(form_error('identifier')){
                $errors['identifier'] = strip_tags(form_error('identifier'));
            }
            if(form_error('password')){
                $errors['password'] = strip_tags(form_error('password'));
            }
            if(form_error('captcha')){
                $errors['captcha'] = strip_tags(form_error('captcha'));
            }
            $new_captcha = $this->_generate_captcha();

            echo json_encode([
                'status' => false,
                'errors' => $errors,
                'captcha_image' => $new_captcha
            ]);
            return;
        }

        $identifier = $this->input->post('identifier');
        $password   = $this->input->post('password');

        $user = $this->Auth_model->get_user($identifier);

        if (!$user) {
            $new_captcha = $this->_generate_captcha();
            echo json_encode([
                'status' => false,
                'errors' => ['identifier' => 'Akun tidak ditemukan'],
                'captcha_image' => $new_captcha
            ]);
            return;
        }

        if (isset($user->status) && $user->status == 0) {
            $new_captcha = $this->_generate_captcha();
            echo json_encode([
                'status' => false,
                'errors' => ['identifier' => 'Akun Anda telah dinonaktifkan'],
                'captcha_image' => $new_captcha
            ]);
            return;
        }

        if (!password_verify($password, $user->password)) {
            $new_captcha = $this->_generate_captcha();
            echo json_encode([
                'status' => false,
                'errors' => ['password' => 'Password salah'],
                'captcha_image' => $new_captcha
            ]);
            return;
        }

        $this->session->unset_userdata(['captcha_word', 'captcha_time']);

        $role_slug = !empty($user->role_slug) ? $user->role_slug : (!empty($user->role) ? $user->role : 'staff');
        $role_name = !empty($user->role_name) ? $user->role_name : ucfirst($role_slug);

        $this->session->set_userdata([
            'user_id'      => $user->user_id,
            'employee_id'  => $user->employee_id,
            'username'     => $user->username,
            'email'        => $user->email,
            'nomor_hp'     => $user->nomor_hp ?? '',
            'role_id'      => $user->role_id ?? null,
            'role'         => $role_slug,
            'role_name'    => $role_name,
            'is_logged_in' => TRUE
        ]);

        echo json_encode([
            'status'   => true,
            'redirect' => base_url('dashboard')
        ]);
    }

    public function check_captcha($cap){
        $captcha_word = $this->session->userdata('captcha_word');
        $captcha_time = $this->session->userdata('captcha_time');

        if (!$captcha_word || !$captcha_time) {
            $this->form_validation->set_message('check_captcha', 'Captcha sudah kadaluarsa, silakan coba lagi.');
            return FALSE;
        }

        if (strcasecmp($cap, $captcha_word) !== 0) {
            $this->form_validation->set_message('check_captcha', 'Kode captcha tidak sesuai.');
            return FALSE;
        }

        return TRUE;
    }

    public function forgot_password(){
        if($this->input->method() === 'get'){
            $this->load->view('auth/forgot_password');
            return;
        }
 
        header('Content-Type: application/json');
 
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email', [
            'required'    => '%s wajib diisi',
            'valid_email' => 'Format %s tidak valid'
        ]);
 
        if($this->form_validation->run() == FALSE){
            $errors = [];
            if(form_error('email')){
                $errors['email'] = strip_tags(form_error('email'));
            }
            echo json_encode(['status' => false, 'errors' => $errors]);
            return;
        }
 
        $email = $this->input->post('email');
        $user  = $this->Auth_model->get_user($email);
 
        $generic_message = 'Jika email terdaftar, link reset password sudah dikirim. Silakan cek inbox/spam.';
 
        if($user){
            $raw_token    = bin2hex(random_bytes(32));
            $hashed_token = hash('sha256', $raw_token);
 
            $this->Auth_model->create_token([
                'token'   => $hashed_token,
                'user_id' => $user->user_id,
                'created' => date('Y-m-d H:i:s'),
                'expired' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                'used'    => 0
            ]);
 
            $reset_link = base_url('auth/reset-password?token=' . $raw_token);
 
            $this->_send_reset_email($user->email, $user->username, $reset_link);
        }
 
        echo json_encode([
            'status'  => true,
            'message' => $generic_message
        ]);
    }
 
    private function _send_reset_email($to_email, $username, $reset_link){
        $this->load->library('email');
        $message = $this->load->view('emails/send_email', [
            'username'   => $username,
            'reset_link' => $reset_link
        ], true);
 
        $this->email->from('ytminecraft93@gmail.com', 'Sistem Tiket');
        $this->email->to($to_email);
        $this->email->subject('Reset Password Akun Anda');
        $this->email->set_mailtype('html');
        $this->email->message($message);
 
        return $this->email->send();
    }


     public function reset_password(){
        if($this->input->method() === 'get'){
            $raw_token = $this->input->get('token');
 
            $valid_token = false;
            $token_row   = null;
 
            if($raw_token){
                $hashed_token = hash('sha256', $raw_token);
                $token_row    = $this->Auth_model->get_token($hashed_token);
 
                if($token_row
                    && $token_row->used == 0
                    && strtotime($token_row->expired) > time()){
                    $valid_token = true;
                }
            }
 
            $this->load->view('auth/reset_password', [
                'valid_token' => $valid_token,
                'token'       => $raw_token
            ]);
            return;
        }
 
        header('Content-Type: application/json');
 
        $this->form_validation->set_rules('token', 'Token', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]', [
            'required'   => '%s wajib diisi',
            'min_length' => '%s minimal 8 karakter'
        ]);
        $this->form_validation->set_rules('password_confirm', 'Konfirmasi password', 'required|matches[password]', [
            'required' => '%s wajib diisi',
            'matches'  => '%s tidak sama dengan password baru'
        ]);
 
        if($this->form_validation->run() == FALSE){
            $errors = [];
            if(form_error('password')){
                $errors['password'] = strip_tags(form_error('password'));
            }
            if(form_error('password_confirm')){
                $errors['password_confirm'] = strip_tags(form_error('password_confirm'));
            }
            echo json_encode(['status' => false, 'errors' => $errors]);
            return;
        }
 
        $raw_token    = $this->input->post('token');
        $hashed_token = hash('sha256', $raw_token);
        $token_row    = $this->Auth_model->get_token($hashed_token);
 
        // Validasi ulang token di server, jangan percaya form yang sudah tampil
        if(!$token_row || $token_row->used != 0 || strtotime($token_row->expired) <= time()){
            echo json_encode([
                'status'  => false,
                'message' => 'Link reset password sudah tidak valid atau kadaluarsa. Silakan minta link baru.'
            ]);
            return;
        }
 
        $new_password = $this->input->post('password');
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
 
        $this->Auth_model->update_password($token_row->user_id, $hashed_password);
        $this->Auth_model->mark_token_used($token_row->id);
 
        echo json_encode([
            'status'  => true,
            'message' => 'Password berhasil diubah, silakan login.'
        ]);
    }
 
    public function logout(){
        $this->session->sess_destroy();
        redirect('auth');
    }

}