<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approve extends MY_Controller {

    public function __construct(){
        parent::__construct();

        $this->load->model('Approve_model');
        $this->load->library('form_validation');
    }

    public function index(){
        $data['title'] = 'Setting Approve';
        $this->load->view('templates/header', $data);
        $this->load->view('approve/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data(){
        $approvals = $this->Approve_model->get_data();
        header('Content-Type: application/json');
        echo json_encode($approvals);
    }

    public function create(){
        $data['title'] = 'Tambah Approval';
        $data['preview_code'] = $this->Approve_model->generate_approval_code(); 
        $data['statuses'] = $this->Approve_model->get_status();
        $data['menus'] = $this->Approve_model->get_menu();
        $data['employees'] = $this->Approve_model->get_employee();
        $this->load->view('templates/header', $data);
        $this->load->view('approve/form', $data);
        $this->load->view('templates/footer');
    }

    public function store()
    {
        $this->form_validation->set_rules('approval_name', 'Nama Approval', 'required');
        $this->form_validation->set_rules('approval_menu', 'Menu', 'required|integer');
        $this->form_validation->set_rules('approval_description', 'Deskripsi', 'required');
        $this->form_validation->set_rules('approval_user_id[]', 'Employee', 'required');

        if ($this->form_validation->run() === FALSE) {
            $errors = [];
            $errors['approval_name'] = strip_tags(form_error('approval_name', '', ''));
            $errors['approval_menu'] = strip_tags(form_error('approval_menu', '', ''));
            $errors['approval_description'] = strip_tags(form_error('approval_description', '', ''));
            $errors['approval_user_id'] = strip_tags(form_error('approval_user_id[]', '', ''));
            
            // Hapus error yang kosong
            $errors = array_filter($errors);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => 'failed',
                    'errors' => $errors
                ]));
        }

        $approval_users     = $this->input->post('approval_user_id');
        $approval_sequences = $this->input->post('approval_sequence');
        $approval_required  = $this->input->post('approval_is_required');

        $valid_users = array_filter($approval_users, function($v) {
            return !empty($v);
        });

        if (empty($valid_users)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => 'failed',
                    'errors' => [
                        'approval_user_id' => 'Minimal 1 employee harus dipilih'
                    ]
                ]));
        }

        $this->db->trans_begin();

        $header_approval = [
            'approval_code'   => $this->Approve_model->generate_approval_code(),
            'approval_name'   => $this->input->post('approval_name'),
            'approval_menu'   => $this->input->post('approval_menu'),
            'approval_description' => $this->input->post('approval_description'),
            'approval_status' => 1,
            'created_by'      => $this->session->userdata('user_id'),
            'created_at'      => date('Y-m-d H:i:s')
        ];

        $approval_id = $this->Approve_model->insert_header($header_approval);

        $detail_approval = [];

        foreach ($approval_users as $index => $user_id) {
            if (empty($user_id)) continue; // skip kalau employee kosong

            $detail_approval[] = [
                'approval_id'          => $approval_id,
                'approval_sequence'    => $approval_sequences[$index] ?? ($index + 1),
                'approval_role_id'     => null,
                'approval_user_id'     => $user_id,
                'approval_type'        => 'user',
                'approval_is_required' => isset($approval_required[$index]) ? 1 : 0,
                'approval_status'      => 1,
                'created_at'           => date('Y-m-d H:i:s')
            ];
        }

        $this->Approve_model->insert_detail($detail_approval);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'errors' => [
                        'general' => 'Gagal menyimpan data approval. Silakan coba lagi.'
                    ]
                ]));
        }

        $this->db->trans_commit();

        // Set flashdata untuk ditampilkan di halaman berikutnya
        $this->session->set_flashdata('success', 'Data approval berhasil disimpan');

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Data approval berhasil disimpan'
            ]));
    }
}