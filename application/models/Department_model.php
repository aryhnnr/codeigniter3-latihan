<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Department_model extends CI_Model{
    protected $table = 'departments';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_data(){
        $this->db->select('departments.*, product_status.product_status_name');
        $this->db->from($this->table);
        $this->db->join('product_status', 'product_status.product_status_id = departments.status', 'left');
        $this->db->order_by('departments.department_id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id){
        $this->db->select('departments.*, product_status.product_status_name');
        $this->db->from($this->table);
        $this->db->join('product_status', 'product_status.product_status_id = departments.status', 'left');
        $this->db->where('departments.department_id', $id);
        return $this->db->get()->row();
    }

    public function get_status(){
        $this->db->where('module', 'product');
        return $this->db->get('product_status')->result();
    }

    public function insert($data){
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data){
        $this->db->where('department_id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id){
        $this->db->where('department_id', $id);
        return $this->db->delete($this->table);
    }
}