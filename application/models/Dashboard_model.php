<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function count_tickets_total(){
        return $this->db->count_all('tickets');
    }

    public function count_tickets_by_status($status){
        $this->db->select('COUNT(*) as total');
        $this->db->from('tickets');
        $this->db->where('status', $status);
        return $this->db->get()->row();
    }

    public function count_employee_total(){
        return $this->db->count_all('employees');
    }

    public function count_employee_by_status($status){
        $this->db->select('COUNT(*) as total');
        $this->db->from('employees');
        $this->db->where('status', $status);
        return $this->db->get()->row();
    }

    public function count_product_total(){
        return $this->db->count_all('products');
    }

    public function count_product_by_status($status){
        $this->db->select('COUNT(*) as total');
        $this->db->from('products');
        $this->db->where('status', $status);
        return $this->db->get()->row();
    }


}