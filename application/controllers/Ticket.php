<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket extends MY_Controller{
    private $valid_status = ['OPEN', 'IN PROGRESS', 'DONE', 'CANCELLED'];
    private $prioritas_filter = ['Low', 'Normal', 'High', 'Urgent'];
    public function __construct(){
        parent::__construct();
        $this->load->model('Ticket_model');
        $this->load->helper('ticket');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $filter = array(
            'status'     => $this->input->get('status'),
            'prioritas'  => $this->input->get('prioritas'),
            'teknisi_id' => $this->input->get('teknisi_id'),
        );

        $data['title']         = 'Ticket';
        $data['user_role']     = $this->session->userdata('role');
        $data['tickets']       = $this->Ticket_model->get_data($filter);
        $data['teknisi']       = $this->Ticket_model->get_teknisi();
        $data['status_list']   = $this->valid_status;
        $data['prioritas_list']= $this->prioritas_filter;

        $this->load->view('templates/header', $data);
        $this->load->view('ticket/index', $data);
        $this->load->view('templates/footer');
    }


    public function create(){
        $data['title']                 = 'Tambah Ticket';
        $data['user_role']             = $this->session->userdata('role');
        $data['departments']           = $this->Ticket_model->get_departemen();
        $data['ticket_number_preview'] = $this->Ticket_model->generate_ticket_number();
        $data['mode']                  = 'create';
        $this->load->view('templates/header', $data);
        $this->load->view('ticket/form', $data);
        $this->load->view('templates/footer');
    }
    public function store(){
        // $this->load->library('form_validation');
        // $data['departments'] = $this->Ticket_model->get_departemen();

        $this->form_validation->set_rules('nama_pemohon', 'Nama Pemohon', 'required');
        $this->form_validation->set_rules('departemen_id', 'Departemen', 'required');
        $this->form_validation->set_rules('judul', 'Judul Masalah', 'required');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi Masalah', 'required');
        $this->form_validation->set_rules('prioritas', 'Prioritas', 'required');

        if($this->form_validation->run() == FALSE){
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $ticket_number = $this->Ticket_model->generate_ticket_number();

        
        $data = array (
            'ticket_number' => $ticket_number,
            'created_by'    => $this->session->userdata('user_id'),
            'nama_pemohon'  => $this->input->post('nama_pemohon'),
            'departemen_id' => $this->input->post('departemen_id'),
            'judul'         => $this->input->post('judul'),
            'deskripsi'     => $this->input->post('deskripsi'),
            'prioritas'     => $this->input->post('prioritas'),
            'status'        => 'OPEN',
            'teknisi_id'    => NULL,
            'created_at'    => date('Y-m-d H:i:s'),
        );

        $this->Ticket_model->insert($data);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Ticket ' . $ticket_number . ' berhasil ditambahkan.');
            echo json_encode([
                'status'  => 'success',
                'message' => 'Ticket ' . $ticket_number . ' berhasil ditambahkan.'
            ]);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data ticket.');
            echo json_encode([
                'status'  => 'failed',
                'message' => 'Gagal menyimpan data.'
            ]);
        }
    }

    public function edit($id){
        $ticket = $this->Ticket_model->get_by_id($id);

        if(!$ticket){
            $this->session->set_flashdata('error', 'Ticket tidak ditemukan.');
            redirect('ticket');
        }

        if($ticket->status == 'DONE'){
            $this->session->set_flashdata('error', 'Ticket yang sudah DONE tidak dapat diedit.');
            redirect('ticket');
        }

        $data['ticket'] = $ticket;
        $data['departments'] = $this->Ticket_model->get_departemen();

        $this->load->view('templates/header', $data);
        $this->load->view('ticket/edit', $data);
        $this->load->view('templates/footer');
    }

    public function update($id = null){
        if (empty($id)) {
            $id = $this->input->post('id');
        }

        if (empty($id)) {
            echo json_encode([
                'status'  => 'failed',
                'message' => 'ID Ticket tidak valid.'
            ]);
            return;
        }

        $ticket = $this->Ticket_model->get_by_id($id);

        if(!$ticket){
            echo json_encode([
                'status'  => 'failed',
                'message' => 'Ticket tidak ditemukan.'
            ]);
            return;
        }

        if($ticket->status == 'DONE'){
            echo json_encode([
                'status'  => 'failed',
                'message' => 'Ticket yang sudah DONE tidak dapat diedit.'
            ]);
            return;
        }

        $this->form_validation->set_rules('nama_pemohon', 'Nama Pemohon', 'required');
        $this->form_validation->set_rules('departemen_id', 'Departemen', 'required');
        $this->form_validation->set_rules('judul', 'Judul Masalah', 'required');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi Masalah', 'required');
        $this->form_validation->set_rules('prioritas', 'Prioritas', 'required');

        if($this->form_validation->run() == FALSE){
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;

        }

        $data = array (
            'created_by'    => $this->session->userdata('user_id'),
            'nama_pemohon'  => $this->input->post('nama_pemohon'),
            'departemen_id' => $this->input->post('departemen_id'),
            'judul'         => $this->input->post('judul'),
            'deskripsi'     => $this->input->post('deskripsi'),
            'prioritas'     => $this->input->post('prioritas'),
        );

        $this->Ticket_model->update($id, $data);
        $this->session->set_flashdata('success', 'Ticket berhasil diperbarui.');

        echo json_encode([
            'status'  => 'success',
            'message' => 'Ticket berhasil diperbarui.'
        ]);
    }


    public function get_detail_json($id){
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket) {
            echo json_encode(['status' => false, 'message' => 'Ticket tidak ditemukan']);
            return;
        }

        echo json_encode(['status' => true, 'data' =>$ticket]);
    }

    public function get_data_json(){
        $filter = array(
            'status' => $this->input->get('status'),
            'prioritas' => $this->input->get('prioritas'),
            'teknisi_id' => $this->input->get('teknisi_id'),
        );

        $tickets = $this->Ticket_model->get_data($filter);

        echo json_encode(['status' => true, 'data' => $tickets]);
    }


    public function update_status_ajax($id){
        $ticket = $this->Ticket_model->get_by_id($id);

        if (!$ticket) {
            echo json_encode(['status' => false, 'message' => 'Ticket tidak ditemukan']);
            return;
        }

        $status_baru = $this->input->post('status');
        $catatan_baru = $this->input->post('catatan_teknisi');

        $result = $this->Ticket_model->validate_status_change($ticket, $status_baru, $catatan_baru);

        if (!$result['valid']) {
            echo json_encode(['status' => false, 'message' => $result['message']]);
            return;
        }

        $data = array(
            'status' => $status_baru,
            'catatan_teknisi' => $catatan_baru,
        );

        if ($status_baru == 'DONE' || $status_baru == 'CANCELLED') {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->Ticket_model->update($id, $data);

        echo json_encode(['status' => true, 'message' => 'Status berhasil diubah menjadi ' . $status_baru]); // ✅ echo, bukan return
    }

    public function assign_teknisi_ajax(){
        $id = $this->input->post('ticket_id');
        $teknisi_id = $this->input->post('teknisi_id');

        $ticket = $this->Ticket_model->get_by_id($id);

        if (!$ticket) {
            echo json_encode(['status' => false, 'message' => 'Ticket tidak ditemukan']);
            return;
        }

        if ($ticket->status == 'DONE' || $ticket->status == 'CANCELLED') {
            echo json_encode(['status' => false, 'message' => 'Ticket dengan status ' . $ticket->status . ' tidak dapat diubah']);
            return;
        }

        if (empty($teknisi_id)) {
            echo json_encode(['status' => false, 'message' => 'Teknisi wajib dipilih.']);
            return;
        }

        $data = array('teknisi_id' => $teknisi_id);
        if ($ticket->status == 'OPEN') {
            $data['status'] = 'IN PROGRESS';
        }

        $this->Ticket_model->update($id, $data);

        $teknisi = $this->Ticket_model->get_teknisi_by_id($teknisi_id);

        echo json_encode(array(
            'status'       => true,
            'message'      => 'Ticket berhasil di-assign ke ' . $teknisi->nama,
            'teknisi_nama' => $teknisi->nama,
            'status_baru'  => $ticket->status == 'OPEN' ? 'IN PROGRESS' : $ticket->status
        ));
    }
}