<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sub_department extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // $this->only_admin();
        $this->load->model('Sub_department_model');
        // $this->load->model('Department_model');
        $this->load->library('form_validation');
    }
    public function index() {
        $data['title'] = 'Sub Department';
        $data['status'] = $this->Sub_department_model->get_status();
        $data['department'] = $this->Sub_department_model->get_department();
        $this->load->view('templates/header', $data);
        $this->load->view('sub_department/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data(){
        $filter['department_id'] = $this->input->post('department_id', TRUE);
        $rows = $this->Sub_department_model->get_data($filter);
        $data = [];

        foreach ($rows as $index => $row) {
            $data[] = [
                'no' => $index + 1,
                'sub_department_id' => $row->sub_department_id,
                'sub_department_name' => $row->sub_department_name,
                'department_id' => $row->department_id,
                'department_name' => $row->department_name,
                'status' => $row->status,
                'product_status_name' => $row->product_status_name,
                'action' => ''
            ];
        }

        echo json_encode(['data' => $data]);
    }

    public function get_by_department($department_id = null){
        if (empty($department_id)) {
            echo json_encode([]);
            return;
        }

        $data = $this->Sub_department_model->get_by_department_active($department_id);
        echo json_encode($data);
    }

    public function get_by_id($id = null){
        $subdepartment = $this->Sub_department_model->get_by_id($id);
        if (!$subdepartment) {
            echo json_encode([
                'status' => 'failed',
                'message' => 'Sub Department tidak ditemukan.'
            ]);
            return;
        }
        echo json_encode($subdepartment);
    }

    public function create(){
        $data['title'] = 'Tambah Sub Department';
        $data['status'] = $this->Sub_department_model->get_status();
        $data['department'] = $this->Sub_department_model->get_department();
        $this->load->view('templates/header', $data);
        $this->load->view('sub_department/create', $data);
        $this->load->view('templates/footer');
    }

    public function store(){
        $id = $this->input->post('id');

        $this->form_validation->set_rules('sub_department_name', 'Nama Sub Department', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('department_id', 'Department', 'required|numeric');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $data = [
            'sub_department_name' => $this->input->post('sub_department_name', TRUE),
            'department_id' => (int) $this->input->post('department_id'),
            'status' => (int) $this->input->post('status')
        ];

        if (!empty($id)) {
            $this->Sub_department_model->update($id, $data);
            $this->session->set_flashdata('success', 'Department berhasil diperbarui.');
            echo json_encode([
                'status' => $this->db->affected_rows() >= 0 ? 'success' : 'failed',
                'message' => 'Department berhasil diperbarui.'
            ]);
            return;
        }

        $this->Sub_department_model->insert($data);
        $this->session->set_flashdata('success', 'Department berhasil ditambahkan.');
        echo json_encode([
            'status' => $this->db->affected_rows() > 0 ? 'success' : 'failed',
            'message' => $this->db->affected_rows() > 0
                ? 'Department berhasil ditambahkan.'
                : 'Gagal menyimpan data department.'
        ]);
    }

    public function edit($id = null){
        $subdepartment = $this->Sub_department_model->get_by_id($id);

        if (!$subdepartment) {
            $this->session->set_flashdata('error', 'Sub Department tidak ditemukan.');
            redirect('sub_department');
            return;
        }

        $data['title'] = 'Edit Sub Department';
        $data['status'] = $this->Sub_department_model->get_status();
        $data['department'] = $subdepartment;

        $this->load->view('templates/header', $data);
        $this->load->view('sub_department/edit', $data);
        $this->load->view('templates/footer');
    }

    public function update($id = null){
        $subdepartment = $this->Sub_department_model->get_by_id($id);

        if (!$subdepartment) {
            echo json_encode(['status' => 'failed', 'message' => 'Sub Department tidak ditemukan.']);
            return;
        }

        $this->form_validation->set_rules('sub_department_name', 'Nama Sub Department', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('department_id', 'Department', 'required|numeric');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $this->Sub_department_model->update($id, [
            'sub_department_name' => $this->input->post('sub_department_name', TRUE),
            'department_id' => (int) $this->input->post('department_id'),
            'status' => (int) $this->input->post('status')
        ]);

        $this->session->set_flashdata('success', 'Sub Department berhasil diperbarui.');
        echo json_encode([
            'status' => 'success',
            'message' => 'Sub Department berhasil diperbarui.'
        ]);
    }

    public function delete(){
        $id = $this->input->post('id');

        if (empty($id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID sub department tidak valid.']);
            return;
        }

        $subdepartment = $this->Sub_department_model->get_by_id($id);
        if (!$subdepartment) {
            echo json_encode(['status' => 'failed', 'message' => 'Sub Department tidak ditemukan.']);
            return;
        }

        $this->Sub_department_model->delete($id);
        $this->session->set_flashdata('success', 'Sub Department ' . $subdepartment->sub_department_name . ' berhasil dihapus.');
        echo json_encode([
            'status' => $this->db->affected_rows() >= 0 ? 'success' : 'failed',
            'message' => 'Sub Department berhasil dihapus.'
        ]);
    }

}