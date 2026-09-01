<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Department extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // $this->only_admin();
        $this->load->model('Department_model');
        $this->load->library('form_validation');
    }
    public function index() {
        $data['title'] = 'Department';
        $data['status'] = $this->Department_model->get_status();
        $this->load->view('templates/header', $data);
        $this->load->view('department/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data(){
        $rows = $this->Department_model->get_data();
        $data = [];

        foreach ($rows as $index => $row) {
            $data[] = [
                'no' => $index + 1,
                'department_id' => $row->department_id,
                'department_name' => $row->department_name,
                'status' => $row->status,
                'product_status_name' => $row->product_status_name,
                'action' => ''
            ];
        }

        echo json_encode(['data' => $data]);
    }

    public function get_by_id($id = null){
        $department = $this->Department_model->get_by_id($id);
        if (!$department) {
            echo json_encode([
                'status' => 'failed',
                'message' => 'Department tidak ditemukan.'
            ]);
            return;
        }
        echo json_encode($department);
    }


    public function create(){
        $data['title'] = 'Tambah Department';
        $data['status'] = $this->Department_model->get_status();
        $this->load->view('templates/header', $data);
        $this->load->view('department/create', $data);
        $this->load->view('templates/footer');
    }

    public function store(){
        $id = $this->input->post('id');

        $this->form_validation->set_rules('department_name', 'Nama Department', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $data = [
            'department_name' => $this->input->post('department_name', TRUE),
            'status' => (int) $this->input->post('status')
        ];

        if (!empty($id)) {
            $this->Department_model->update($id, $data);
            $this->session->set_flashdata('success', 'Department berhasil diperbarui.');
            echo json_encode([
                'status' => $this->db->affected_rows() >= 0 ? 'success' : 'failed',
                'message' => 'Department berhasil diperbarui.'
            ]);
            return;
        }

        $this->Department_model->insert($data);
        $this->session->set_flashdata('success', 'Department berhasil ditambahkan.');
        echo json_encode([
            'status' => $this->db->affected_rows() > 0 ? 'success' : 'failed',
            'message' => $this->db->affected_rows() > 0
                ? 'Department berhasil ditambahkan.'
                : 'Gagal menyimpan data department.'
        ]);
    }

    public function edit($id = null){
        $department = $this->Department_model->get_by_id($id);

        if (!$department) {
            $this->session->set_flashdata('error', 'Department tidak ditemukan.');
            redirect('department');
            return;
        }

        $data['title'] = 'Edit Department';
        $data['status'] = $this->Department_model->get_status();
        $data['department'] = $department;

        $this->load->view('templates/header', $data);
        $this->load->view('department/edit', $data);
        $this->load->view('templates/footer');
    }

    public function update($id = null){
        $department = $this->Department_model->get_by_id($id);

        if (!$department) {
            echo json_encode(['status' => 'failed', 'message' => 'Department tidak ditemukan.']);
            return;
        }

        $this->form_validation->set_rules('department_name', 'Nama Department', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $this->Department_model->update($id, [
            'department_name' => $this->input->post('department_name', TRUE),
            'status' => (int) $this->input->post('status')
        ]);

        $this->session->set_flashdata('success', 'Department berhasil diperbarui.');
        echo json_encode([
            'status' => 'success',
            'message' => 'Department berhasil diperbarui.'
        ]);
    }

    public function delete(){
        $id = $this->input->post('id');

        if (empty($id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID department tidak valid.']);
            return;
        }

        $department = $this->Department_model->get_by_id($id);
        if (!$department) {
            echo json_encode(['status' => 'failed', 'message' => 'Department tidak ditemukan.']);
            return;
        }

        $this->Department_model->delete($id);
        $this->session->set_flashdata('success', 'Department ' . $department->department_name . ' berhasil dihapus.');
        echo json_encode([
            'status' => $this->db->affected_rows() >= 0 ? 'success' : 'failed',
            'message' => 'Department berhasil dihapus.'
        ]);
    }

}