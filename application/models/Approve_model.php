<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approve_model extends CI_Model{
    public function __construct() {
        parent::__construct();
    }

    public function get_menu(){
        $this->db->select('id, name');
        $this->db->from('menus');
        $this->db->where('parent_id !=', 0);
        $this->db->where('status', 1);
        $this->db->order_by('order_no', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result();
    }

    public function get_employee(){
        $this->db->select('employees.employee_id as id, employees.employee_name as name, positions.position_name as position, departments.department_name as department');
        $this->db->from('employees');
        $this->db->join('positions', 'positions.position_id = employees.position_id', 'left');
        $this->db->join('departments', 'departments.department_id = employees.department_id', 'left');
        $this->db->where('employees.status', 1);
        $this->db->order_by('employees.employee_name', 'ASC');
        return $this->db->get()->result();
    }
}