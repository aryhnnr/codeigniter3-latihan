<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_model extends CI_Model
{
    protected $table = 'supplier';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_data($filter = []){
        $this->db->select('supplier.*, users.username as created_by_username');
        $this->db->from($this->table);
        $this->db->join('users', 'users.user_id = supplier.created_by', 'left');

        if (isset($filter['status']) && $filter['status'] !== '' && $filter['status'] !== null) {
            $this->db->where('supplier.status', $filter['status']);
        }

        $this->db->order_by('supplier.id', 'DESC');

        $query = $this->db->get();

        return $query->result();
    }

    public function get_by_id($id){
        $this->db->select('supplier.*, users.username as created_by_username');
        $this->db->from($this->table);
        $this->db->join('users', 'users.user_id = supplier.created_by', 'left');
        $this->db->where('supplier.id', $id);

        $query = $this->db->get();

        return $query->row();
    }

    public function get_status(){
        $this->db->where('module','product');
        return $this->db->get('product_status')->result();
    }

    public function generate_supplier_code(){
        $this->db->like('code_supplier', 'SUP', 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get($this->table)->row();

        $next_number = $last ? ((int) substr($last->code_supplier, -6)) + 1 : 1;

        return 'SUP' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
    }

    public function insert($data){
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data){
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id){
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
}