<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{
    protected $table = 'products';
    protected $column_search = ['products.product_code', 'products.product_name', 'categories.category_name', 'brands.brand_name'];
    protected $column_order = [null, 'products.product_code', 'products.product_name', 'categories.category_name', 'brands.brand_name', 'units.unit_name', 'product_type.product_type_name', 'products.status', null];
    protected $order = ['products.product_id' => 'DESC'];

    public function __construct() {
        parent::__construct();
    }

    private function _base_query($filter = []) {
        $this->db->select('
            products.*, 
            categories.category_name, 
            brands.brand_name, 
            units.unit_name, 
            product_type.product_type_name, 
            product_status.product_status_name,
            users.username as created_by_username
        ');
        $this->db->from($this->table);
        $this->db->join('categories', 'categories.category_id = products.category_id', 'left');
        $this->db->join('brands', 'brands.brand_id = products.brand_id', 'left');
        $this->db->join('units', 'units.unit_id = products.unit_id', 'left');
        $this->db->join('product_type', 'product_type.product_type_id = products.product_type', 'left');
        $this->db->join('product_status', 'product_status.product_status_id = products.status', 'left');
        $this->db->join('users', 'users.user_id = products.created_by', 'left');

        // filter dropdown (sama seperti sebelumnya)
        if (isset($filter['status']) && $filter['status'] !== '' && $filter['status'] !== null) {
            $this->db->where('products.status', $filter['status']);
        }
        if (!empty($filter['category_id'])) {
            $this->db->where('products.category_id', $filter['category_id']);
        }
        if (!empty($filter['brand_id'])) {
            $this->db->where('products.brand_id', $filter['brand_id']);
        }
        if (!empty($filter['product_type'])) {
            $this->db->where('products.product_type', $filter['product_type']);
        }

        // search box DataTables (kotak pencarian bawaan)
        if (isset($_POST['search']['value']) && $_POST['search']['value'] !== '') {
            $search = $_POST['search']['value'];
            $this->db->group_start();
            foreach ($this->column_search as $i => $item) {
                if ($i === 0) {
                    $this->db->like($item, $search);
                } else {
                    $this->db->or_like($item, $search);
                }
            }
            $this->db->group_end();
        }
    }

    public function get_datatables($filter = []) {
        $this->_base_query($filter);

        // sorting
        if (isset($_POST['order']['0']['column']) && isset($this->column_order[$_POST['order']['0']['column']]) && $this->column_order[$_POST['order']['0']['column']] !== null) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $this->db->order_by(key($this->order), $this->order[key($this->order)]);
        }

        // paging
        if (isset($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit($_POST['length'], isset($_POST['start']) ? $_POST['start'] : 0);
        }

        return $this->db->get()->result();
    }

    public function count_filtered($filter = []) {
        $this->_base_query($filter);
        return $this->db->get()->num_rows();
    }

    public function count_all() {
        return $this->db->count_all($this->table);
    }


    public function get_data($filter = []){
        $this->db->select('
            products.*, 
            categories.category_name, 
            brands.brand_name, 
            units.unit_name, 
            product_type.product_type_name, 
            product_status.product_status_name,
            users.username as created_by_username
        ');
        $this->db->from($this->table);
        $this->db->join('categories', 'categories.category_id = products.category_id', 'left');
        $this->db->join('brands', 'brands.brand_id = products.brand_id', 'left');
        $this->db->join('units', 'units.unit_id = products.unit_id', 'left');
        $this->db->join('product_type', 'product_type.product_type_id = products.product_type', 'left');
        $this->db->join('product_status', 'product_status.product_status_id = products.status', 'left');
        $this->db->join('users', 'users.user_id = products.created_by', 'left');

        if (isset($filter['status']) && $filter['status'] !== '' && $filter['status'] !== null) {
            $this->db->where('products.status', $filter['status']);
        }

        if (!empty($filter['category_id'])) {
            $this->db->where('categories.category_id', $filter['category_id']);
        }

        if (!empty($filter['brand_id'])) {
            $this->db->where('brands.brand_id', $filter['brand_id']);
        }

        if (!empty($filter['product_type'])) {
            $this->db->where('product_type.product_type_id', $filter['product_type']);
        }


        $this->db->order_by('products.product_id', 'DESC');

        $query = $this->db->get();

        return $query->result();
    }

    public function get_category(){
        return $this->db->get('categories')->result();
    }
    public function get_brand(){
        return $this->db->get('brands')->result();
    }
    public function get_unit(){
        return $this->db->get('units')->result();
    }
    public function get_product_type(){
        return $this->db->get('product_type')->result();
    }
    public function get_status(){
        $this->db->where('module', 'product');
        return $this->db->get('product_status')->result();
    }

    public function generate_product_code(){
        $this->db->like('product_code', 'PRD', 'after');
        $this->db->order_by('product_id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get($this->table);
        $last = $query->row();


        if($last){
            $last_number = (int) substr($last->product_code, -6);
            $next_number = $last_number + 1;
        }else{
            $next_number = 1;
        }

        $formatted = str_pad($next_number, 6, '0', STR_PAD_LEFT);

        return 'PRD' . $formatted;
    }

    public function insert($data){
        return $this->db->insert($this->table, $data);
    }


    // public function get_product_status(){
    //     $this->db->where('module', 'purchase');
    //     return $this->db->get('product_status')->result();
    // }


    public function get_by_id($id){
        $this->db->select('
            products.*, 
            categories.category_name, 
            brands.brand_name, 
            units.unit_name, 
            product_type.product_type_name, 
            product_status.product_status_name,
            users.username as created_by_username
        ');
        $this->db->from($this->table);
        $this->db->join('categories', 'categories.category_id = products.category_id', 'left');
        $this->db->join('brands', 'brands.brand_id = products.brand_id', 'left');
        $this->db->join('units', 'units.unit_id = products.unit_id', 'left');
        $this->db->join('product_type', 'product_type.product_type_id = products.product_type', 'left');
        $this->db->join('product_status', 'product_status.product_status_id = products.status', 'left');
        $this->db->join('users', 'users.user_id = products.created_by', 'left');
        $this->db->where('products.product_id', $id);

        $query = $this->db->get();

        return $query->row();
    }

    public function update($id, $data){
        $this->db->where('product_id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id) {
        $this->db->where('product_id', $id);
        return $this->db->delete('products');
    }
}