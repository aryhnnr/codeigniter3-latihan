<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Position extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // $this->only_admin();
        $this->load->model('Position_model');
        $this->load->library('form_validation');
    }
    public function index() {
        $data['title'] = 'Position';
        $data['status'] = $this->Position_model->get_status();
        $this->load->view('templates/header', $data);
        $this->load->view('position/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data(){
        $rows = $this->Position_model->get_data();
        $data = [];

        foreach ($rows as $index => $row) {
            $data[] = [
                'no' => $index + 1,
                'position_id' => $row->position_id,
                'position_name' => $row->position_name,
                'status' => $row->status,
                'product_status_name' => $row->product_status_name,
                'action' => ''
            ];
        }

        echo json_encode(['data' => $data]);
    }

    public function get_by_id($id = null){
        $position = $this->Position_model->get_by_id($id);
        if (!$position) {
            echo json_encode([
                'status' => 'failed',
                'message' => 'Position tidak ditemukan.'
            ]);
            return;
        }
        echo json_encode($position);
    }


    public function create(){
        $data['title'] = 'Tambah Position';
        $data['status'] = $this->Position_model->get_status();
        $this->load->view('templates/header', $data);
        $this->load->view('position/create', $data);
        $this->load->view('templates/footer');
    }

    public function store(){
        $id = $this->input->post('id');

        $this->form_validation->set_rules('position_name', 'Nama Position', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $data = [
            'position_name' => $this->input->post('position_name', TRUE),
            'status' => (int) $this->input->post('status')
        ];

        if (!empty($id)) {
            $this->Position_model->update($id, $data);
            $this->session->set_flashdata('success', 'Position berhasil diperbarui.');
            echo json_encode([
                'status' => $this->db->affected_rows() >= 0 ? 'success' : 'failed',
                'message' => 'Position berhasil diperbarui.'
            ]);
            return;
        }

        $this->Position_model->insert($data);
        $this->session->set_flashdata('success', 'Position berhasil ditambahkan.');
        echo json_encode([
            'status' => $this->db->affected_rows() > 0 ? 'success' : 'failed',
            'message' => $this->db->affected_rows() > 0
                ? 'Position berhasil ditambahkan.'
                : 'Gagal menyimpan data position.'
        ]);
    }

    public function edit($id = null){
        $position = $this->Position_model->get_by_id($id);

        if (!$position) {
            $this->session->set_flashdata('error', 'Position tidak ditemukan.');
            redirect('position');
            return;
        }

        $data['title'] = 'Edit Position';
        $data['status'] = $this->Position_model->get_status();
        $data['position'] = $position;

        $this->load->view('templates/header', $data);
        $this->load->view('position/edit', $data);
        $this->load->view('templates/footer');
    }

    public function update($id = null){
        $position = $this->Position_model->get_by_id($id);

        if (!$position) {
            echo json_encode(['status' => 'failed', 'message' => 'Position tidak ditemukan.']);
            return;
        }

        $this->form_validation->set_rules('position_name', 'Nama Position', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $this->Position_model->update($id, [
            'position_name' => $this->input->post('position_name', TRUE),
            'status' => (int) $this->input->post('status')
        ]);

        $this->session->set_flashdata('success', 'Position berhasil diperbarui.');
        echo json_encode([
            'status' => 'success',
            'message' => 'Position berhasil diperbarui.'
        ]);
    }

    public function delete(){
        $id = $this->input->post('id');

        if (empty($id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID position tidak valid.']);
            return;
        }

        $position = $this->Position_model->get_by_id($id);
        if (!$position) {
            echo json_encode(['status' => 'failed', 'message' => 'Position tidak ditemukan.']);
            return;
        }

        $this->Position_model->delete($id);
        $this->session->set_flashdata('success', 'Position ' . $position->position_name . ' berhasil dihapus.');
        echo json_encode([
            'status' => $this->db->affected_rows() >= 0 ? 'success' : 'failed',
            'message' => 'Position berhasil dihapus.'
        ]);
    }

}