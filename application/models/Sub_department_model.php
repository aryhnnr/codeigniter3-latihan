<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sub_department_model extends CI_Model{
    protected $table = 'sub_departments';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_data($filter = []){
        $this->db->select('sub_departments.*, departments.department_name,product_status.product_status_name');
        $this->db->from($this->table);
        $this->db->join('product_status', 'product_status.product_status_id = sub_departments.status', 'left');
        $this->db->join('departments', 'departments.department_id = sub_departments.department_id', 'left');

        if (!empty($filter['department_id'])) {
            $this->db->where('departments.department_id', $filter['department_id']);
        }

        $this->db->order_by('sub_departments.sub_department_id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id){
        $this->db->select('sub_departments.*, departments.department_name,product_status.product_status_name');
        $this->db->from($this->table);
        $this->db->join('product_status', 'product_status.product_status_id = sub_departments.status', 'left');
        $this->db->join('departments', 'departments.department_id = sub_departments.department_id', 'left');
        $this->db->where('sub_departments.sub_department_id', $id);
        return $this->db->get()->row();
    }

    public function get_status(){
        $this->db->where('module', 'product');
        return $this->db->get('product_status')->result();
    }

    public function get_department(){
        $this->db->where('status', 1);
        return $this->db->get('departments')->result();
    }

    public function get_by_department($department_id){
        $this->db->select('sub_departments.*, departments.department_name, product_status.product_status_name');
        $this->db->from($this->table);
        $this->db->join('departments', 'departments.department_id = sub_departments.department_id', 'left');
        $this->db->join('product_status', 'product_status.product_status_id = sub_departments.status', 'left');
        $this->db->where('sub_departments.department_id', $department_id);
        $this->db->where('sub_departments.status', 1);
        $this->db->order_by('sub_departments.sub_department_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_department_active($department_id){
        return $this->get_by_department($department_id);
    }

    public function get_by_department_id($department_id){
        return $this->get_by_department($department_id);
    }

    public function insert($data){
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data){
        $this->db->where('sub_department_id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id){
        $this->db->where('sub_department_id', $id);
        return $this->db->delete($this->table);
    }
}