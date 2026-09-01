<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // $this->only_admin();
        $this->load->model('Supplier_model');
        $this->load->library('form_validation');
    }
    public function index() {
        $data['title'] = 'Supplier';
        $data['status'] = $this->Supplier_model->get_status();
        $this->load->view('templates/header', $data);
        $this->load->view('supplier/index', $data);
        $this->load->view('templates/footer');
    }

    public function create(){
        $data['title'] = 'Tambah Supplier';
        $data['status'] = $this->Supplier_model->get_status();
        $data['supplier_code_preview'] = $this->Supplier_model->generate_supplier_code();

        $this->load->view('templates/header', $data);
        $this->load->view('supplier/form', $data);
        $this->load->view('templates/footer');
    }

    public function store(){
        $this->form_validation->set_rules('nama_supplier', 'Nama Supplier', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $code = $this->Supplier_model->generate_supplier_code();
        $this->Supplier_model->insert([
            'code_supplier' => $code,
            'created_by'    => $this->session->userdata('user_id'),
            'nama_supplier' => $this->input->post('nama_supplier', TRUE),
            'status' => (int) $this->input->post('status')
        ]);
        $this->session->set_flashdata('success', 'Supplier ' . $code . ' berhasil ditambahkan.');
        echo json_encode([
            'status' => $this->db->affected_rows() > 0 ? 'success' : 'failed',
            'message' => $this->db->affected_rows() > 0
                ? 'Supplier ' . $code . ' berhasil ditambahkan.'
                : 'Gagal menyimpan data supplier.'
        ]);
    }

    public function edit($id = null){
        $supplier = $this->Supplier_model->get_by_id($id);

        if (!$supplier) {
            $this->session->set_flashdata('error', 'Supplier tidak ditemukan.');
            redirect('supplier');
            return;
        }

        $data['title'] = 'Edit Supplier';
        $data['status'] = $this->Supplier_model->get_status();
        $data['supplier'] = $supplier;

        $this->load->view('templates/header', $data);
        $this->load->view('supplier/edit', $data);
        $this->load->view('templates/footer');
    }

    public function update($id = null){
        $supplier = $this->Supplier_model->get_by_id($id);

        if (!$supplier) {
            echo json_encode(['status' => 'failed', 'message' => 'Supplier tidak ditemukan.']);
            return;
        }

        $this->form_validation->set_rules('nama_supplier', 'Nama Supplier', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $this->Supplier_model->update($id, [
            'created_by'    => $this->session->userdata('user_id'),
            'nama_supplier' => $this->input->post('nama_supplier', TRUE),
            'status' => (int) $this->input->post('status')
        ]);

        $this->session->set_flashdata('success', 'Supplier berhasil diperbarui.');
        echo json_encode(['status' => 'success', 'message' => 'Supplier berhasil diperbarui.']);
    }

    public function delete(){
        $id = $this->input->post('id');
        $supplier = $this->Supplier_model->get_by_id($id);

        if (!$supplier) {
            echo json_encode(['status' => 'failed', 'message' => 'Supplier tidak ditemukan.']);
            return;
        }

        $this->Supplier_model->delete($id);
        $this->session->set_flashdata('success', 'Supplier ' . $supplier->code_supplier . ' berhasil dihapus.');
        echo json_encode([
            'status' => $this->db->affected_rows() > 0 ? 'success' : 'failed',
            'message' => $this->db->affected_rows() > 0
                ? 'Supplier berhasil dihapus.'
                : 'Gagal menghapus supplier.'
        ]);
    }

    public function get_data(){
        $filter = [
            'status' => $this->input->post('status'),
        ];

        $data = $this->Supplier_model->get_data($filter);

        header('Content-Type: application/json');
        echo json_encode(array("data" => $data));
    }
}