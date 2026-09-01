<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends MY_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->library('form_validation');
    }

    public function index(){
        $data = $this->data;
        $data['title'] = 'Product';

        $data['categories']             = $this->Product_model->get_category();
        $data['brands']                 = $this->Product_model->get_brand();
        $data['units']                  = $this->Product_model->get_unit();
        $data['product_type']           = $this->Product_model->get_product_type();
        $data['product_status']         = $this->Product_model->get_status();
        $data['product_code_preview']   = $this->Product_model->generate_product_code();

        $this->load->view('templates/header', $data);
        $this->load->view('product/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data(){
        $filter = [
            'status'       => $this->input->post('status'),
            'category_id'  => $this->input->post('category_id'),
            'brand_id'     => $this->input->post('brand_id'),
            'product_type' => $this->input->post('product_type'),
        ];

        $list = $this->Product_model->get_datatables($filter);

        $data = [];
        $no = $_POST['start'] + 1;

        foreach ($list as $row) {
            $data[] = [
                'no'                => $no++,
                'created_by_username' => $row->created_by_username,
                'product_code'      => $row->product_code,
                'product_name'      => $row->product_name,
                'category_name'     => $row->category_name,
                'brand_name'        => $row->brand_name,
                'unit_name'         => $row->unit_name,
                'product_type_name' => $row->product_type_name,
                'status'            => $row->status,
                'product_id'        => $row->product_id, // dibutuhkan buat tombol Edit/Delete
                'DT_RowIndex'       => $no - 1,
            ];
        }

        $output = [
            "draw"            => intval($_POST['draw']),
            "recordsTotal"    => $this->Product_model->count_all(),
            "recordsFiltered" => $this->Product_model->count_filtered($filter),
            "data"            => $data,
        ];

        echo json_encode($output);
    }

    public function get_detail($id){
        $data = $this->Product_model->get_by_id($id);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function create(){
        $data = $this->data;
        $data['categories']           = $this->Product_model->get_category();
        $data['brands']               = $this->Product_model->get_brand();
        $data['units']                = $this->Product_model->get_unit();
        $data['product_type']         = $this->Product_model->get_product_type();
        $data['product_status']       = $this->Product_model->get_status();
        $data['product_code_preview'] = $this->Product_model->generate_product_code();
        $data['mode']                 = 'create';
        $data['product']              = null;

        $this->load->view('templates/header', $data);
        $this->load->view('product/form', $data);
        $this->load->view('templates/footer');
    }

    public function store(){
        // $this->load->library('form_validation');

        $this->form_validation->set_rules('product_name', 'Nama Product', 'required|max_length[150]|trim');
        $this->form_validation->set_rules('category_id', 'Category', 'required|numeric');
        $this->form_validation->set_rules('brand_id', 'Brand', 'required|numeric');
        $this->form_validation->set_rules('unit_id', 'Unit', 'required|numeric');
        $this->form_validation->set_rules('product_type', 'Product Type', 'required|numeric');
        $this->form_validation->set_rules('description', 'Deskripsi', 'trim');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $product_code = $this->Product_model->generate_product_code();

        $data = array(
            'product_code' => $product_code,
            'created_by'   => $this->session->userdata('user_id'),
            'product_name' => $this->input->post('product_name'),
            'category_id'  => $this->input->post('category_id'),
            'brand_id'     => $this->input->post('brand_id'),
            'unit_id'      => $this->input->post('unit_id'),
            'product_type' => $this->input->post('product_type'),
            'description'  => $this->input->post('description'),
            'status'       => $this->input->post('status'),
        );

        $this->Product_model->insert($data);

        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Product ' . $product_code . ' berhasil ditambahkan.');
            echo json_encode([
                'status'  => 'success',
                'message' => 'Product ' . $product_code . ' berhasil ditambahkan.'
            ]);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data product.');
            echo json_encode([
                'status'  => 'failed',
                'message' => 'Gagal menyimpan data.'
            ]);
        }
    }

    public function edit($id){
        $product = $this->Product_model->get_by_id($id);

        if (!$product) {
            $this->session->set_flashdata('error', 'Data product tidak ditemukan.');
            redirect('product');
            return;
        }

        $data = $this->data;
        $data['categories']     = $this->Product_model->get_category();
        $data['brands']         = $this->Product_model->get_brand();
        $data['units']          = $this->Product_model->get_unit();
        $data['product_type']   = $this->Product_model->get_product_type();
        $data['product_status'] = $this->Product_model->get_status();
        $data['product']        = $product;
        $data['mode']           = 'edit';

        $this->load->view('templates/header', $data);
        $this->load->view('product/edit', $data);
        $this->load->view('templates/footer');
    }

    public function update(){
        $product_id = $this->input->post('product_id');

        if (empty($product_id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID product tidak valid.']);
            return;
        }

        $this->form_validation->set_rules('product_name', 'Nama Product', 'required|max_length[150]|trim');
        $this->form_validation->set_rules('category_id', 'Category', 'required|numeric');
        $this->form_validation->set_rules('brand_id', 'Brand', 'required|numeric');
        $this->form_validation->set_rules('unit_id', 'Unit', 'required|numeric');
        $this->form_validation->set_rules('product_type', 'Product Type', 'required|numeric');
        $this->form_validation->set_rules('description', 'Deskripsi', 'trim');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'status' => 'failed',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $data = array(
            'created_by'   => $this->session->userdata('user_id'),
            'product_name' => $this->input->post('product_name'),
            'category_id'  => $this->input->post('category_id'),
            'brand_id'     => $this->input->post('brand_id'),
            'unit_id'      => $this->input->post('unit_id'),
            'product_type' => $this->input->post('product_type'),
            'description'  => $this->input->post('description'),
            'status'       => $this->input->post('status'),
        );
        // catatan: product_code TIDAK diubah saat edit, biarkan tetap seperti data lama

        $this->Product_model->update($product_id, $data);
        $this->session->set_flashdata('success', 'Product berhasil diperbarui.');
        echo json_encode([
            'status'  => 'success',
            'message' => 'Product berhasil diperbarui.'
        ]);
    }

    public function delete(){
        $id = $this->input->post('id');

        if (empty($id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID product tidak valid.']);
            return;
        }

        // pastikan data memang ada sebelum dihapus
        $product = $this->Product_model->get_by_id($id);
        if (!$product) {
            echo json_encode(['status' => 'failed', 'message' => 'Data tidak ditemukan.']);
            return;
        }

        $this->Product_model->delete($id);
        $this->session->set_flashdata('success', 'Product ' . $product->product_code . ' berhasil dihapus.');
        echo json_encode([
            'status'  => 'success',
            'message' => 'Product ' . $product->product_code . ' berhasil dihapus.'
        ]);
    }
}