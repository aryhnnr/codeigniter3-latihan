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
            'approval_status' => $this->Approve_model->get_active_status_id(),
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

        $this->session->set_flashdata('success', 'Data approval berhasil disimpan');

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Data approval berhasil disimpan'
            ]));
    }


    public function edit($approval_id) {
        $data['title'] = 'Edit Approval';
        $approval_data = $this->Approve_model->get_approval_detail($approval_id);

        if (!$approval_data) {
            $this->session->set_flashdata('error', 'Data approval tidak ditemukan.');
            redirect('approve');
            return;
        }
        
        

        $data['approval']       = $approval_data->header;
        $data['approval_users'] = $approval_data->detail;
        $data['menus']          = $this->Approve_model->get_menu();
        $data['employees']      = $this->Approve_model->get_employee();
        $statuses = $this->Approve_model->get_status();
        $data['statuses'] = $statuses;

        $active_status = null;
        $inactive_status = null;
        foreach ($statuses as $status) {
            $status_name = strtolower(trim($status->product_status_name));
            if (strpos($status_name, 'nonaktif') !== false || strpos($status_name, 'inactive') !== false) {
                $inactive_status = $status;
            } elseif (strpos($status_name, 'aktif') !== false || strpos($status_name, 'active') !== false) {
                $active_status = $status;
            }
        }

        $active_status = $active_status ?: ($statuses[0] ?? null);
        $inactive_status = $inactive_status ?: ($statuses[1] ?? null);

        $data['active_status'] = $active_status;
        $data['inactive_status'] = $inactive_status;

        $this->load->view('templates/header', $data);
        $this->load->view('approve/edit', $data);
        $this->load->view('templates/footer');
    }

    // ===== UPDATE HEADER SAJA (dipanggil terakhir, setelah semua detail tersimpan) =====
    public function update($approval_id) {
        $this->form_validation->set_rules('approval_name', 'Nama Approval', 'required');
        $this->form_validation->set_rules('approval_menu', 'Menu', 'required|integer');
        $this->form_validation->set_rules('approval_description', 'Deskripsi', 'required');
        $this->form_validation->set_rules('approval_status', 'Status Approval', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => 'failed',
                    'errors' => [
                        'approval_name'        => strip_tags(form_error('approval_name', '', '')),
                        'approval_menu'        => strip_tags(form_error('approval_menu', '', '')),
                        'approval_description' => strip_tags(form_error('approval_description', '', '')),
                    ]
                ]));
        }

        $header_approval = [
            'approval_name'        => $this->input->post('approval_name'),
            'approval_menu'        => $this->input->post('approval_menu'),
            'approval_description' => $this->input->post('approval_description'),
            'approval_status'      => $this->input->post('approval_status'),
            'updated_by'           => $this->session->userdata('user_id'),
            'updated_at'           => date('Y-m-d H:i:s'),
        ];

        if (!$this->Approve_model->status_exists($header_approval['approval_status'])) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode([
                    'status' => 'failed',
                    'errors' => ['approval_status' => 'Status approval tidak valid.']
                ]));
        }

        $this->Approve_model->update_header($approval_id, $header_approval);

        $this->session->set_flashdata('success', 'Data approval berhasil diperbarui');

        echo json_encode([
            'status'  => 'success',
            'message' => 'Data approval berhasil diperbarui'
        ]);
    }
    public function save_detail() {
        $approval_id = $this->input->post('approval_id');
        $detail_id   = $this->input->post('detail_id');
        $user_id     = $this->input->post('user_id');
        $sequence    = $this->input->post('sequence');
        $is_required = $this->input->post('is_required') ? 1 : 0;

        if (empty($approval_id) || empty($user_id)) {
            echo json_encode(['status' => 'failed', 'message' => 'Data tidak lengkap.']);
            return;
        }

        if ($this->Approve_model->is_user_already_used($approval_id, $user_id, $detail_id)) {
            echo json_encode([
                'status'  => 'failed',
                'message' => 'Employee ini sudah digunakan di baris lain pada approval ini.'
            ]);
            return;
        }

        $data = [
            'approval_id'          => $approval_id,
            'approval_sequence'    => $sequence,
            'approval_user_id'     => $user_id,
            'approval_type'        => 'user',
            'approval_role_id'     => null,
            'approval_is_required' => $is_required,
            'approval_status'      => 1,
        ];

        if (!empty($detail_id)) {
            // baris lama -> UPDATE
            $this->Approve_model->update_single_detail($detail_id, $data);
            $id = $detail_id;
        } else {
            // baris baru -> INSERT
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->Approve_model->insert_single_detail($data);
        }

        echo json_encode([
            'status'  => 'success',
            'id'      => $id,
            'message' => 'Baris berhasil disimpan.'
        ]);
    }

    public function delete_detail($id) {
        if (empty($id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID tidak valid.']);
            return;
        }

        $this->Approve_model->delete_single_detail($id);

        echo json_encode([
            'status'  => 'success',
            'message' => 'Baris berhasil dihapus.'
        ]);
    }



    public function detail($approval_id)
    {
        $data['title'] = 'Detail Approval';
        $approval_data = $this->Approve_model->get_approval_detail($approval_id);

        if (!$approval_data) {
            $this->session->set_flashdata('error', 'Data approval tidak ditemukan.');
            redirect('approve');
            return;
        }

        $data['approval'] = $approval_data->header;
        $data['approval_users'] = $approval_data->detail;

        $this->load->view('templates/header', $data);
        $this->load->view('approve/detail', $data);
        $this->load->view('templates/footer');
    }
    public function get_detail($approval_id)
    {
        $approval = $this->Approve_model->get_approval_detail($approval_id);

        if (!$approval) {
            $this->session->set_flashdata('error', 'Data approval tidak ditemukan.');
            redirect('approve');
            return;
        }

        echo json_encode([
            'header' => $approval->header,
            'detail' => $approval->detail
        ]);
    }


    
    


    
}