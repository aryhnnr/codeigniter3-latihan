<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approve_model extends CI_Model{
    protected $table = 'approval';


    public function __construct() {
        parent::__construct();
    }

    public function get_data(){
        $this->db->select('approval.*, menus.name as approval_menu, product_status.product_status_name, users.username as created_by_name, updated_user.username as updated_by_name');
        $this->db->from($this->table);
        $this->db->join('product_status', "product_status.product_status_id = approval.approval_status AND product_status.module = 'product'", 'left');
        $this->db->join('users', 'users.user_id = approval.created_by', 'left');
        $this->db->join('users as updated_user', 'updated_user.user_id = approval.updated_by', 'left');
        $this->db->join('menus', 'menus.id = approval.approval_menu', 'left');
        $this->db->order_by('approval.approval_id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id){
        $this->db->select('approval.*, menus.name as approval_menu, product_status.product_status_name, users.username as created_by_name');
        $this->db->from($this->table);
        $this->db->join('product_status', "product_status.product_status_id = approval.approval_status AND product_status.module = 'product'", 'left');
        $this->db->join('users', 'users.user_id = approval.created_by', 'left');
        $this->db->join('menus', 'menus.id = approval.approval_menu', 'left');
        $this->db->where('approval.approval_id', $id);
        return $this->db->get()->row();
    }

    public function get_status(){
        $this->db->order_by('product_status_id', 'ASC');
        $this->db->where('module', 'product');
        return $this->db->get('product_status')->result();
    }

    public function status_exists($status_id){
        return $this->db
            ->where('product_status_id', $status_id)
            ->where('module', 'product')
            ->count_all_results('product_status') > 0;
    }

    public function get_active_status_id(){
        $statuses = $this->get_status();

        foreach ($statuses as $status) {
            $status_name = strtolower(trim($status->product_status_name));
            if (strpos($status_name, 'nonaktif') === false && strpos($status_name, 'inactive') === false &&
                (strpos($status_name, 'aktif') !== false || strpos($status_name, 'active') !== false)) {
                return $status->product_status_id;
            }
        }

        return $statuses[0]->product_status_id ?? null;
    }

    public function generate_approval_code(){
        $year = date('Y');
        $this->db->like('approval_code', 'PR-'.$year.'-', 'after');
        $this->db->order_by('approval_id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get($this->table);
        $last = $query->row();


        if($last){
            $last_number = (int) substr($last->approval_code, -6);
            $next_number = $last_number + 1;
        }else{
            $next_number = 1;
        }

        $formatted = str_pad($next_number, 6, '0', STR_PAD_LEFT);

        return 'PR-' . $year . '-' . $formatted;
    }

    public function insert_header($data)
    {
        $this->db->insert('approval', $data);
        return $this->db->insert_id();
    }

    public function insert_detail($data)
    {
        $this->db->insert_batch('approval_detail', $data);
    }

    public function update_header($id, $data)
    {
        $this->db->where('approval_id', $id);
        $this->db->update('approval', $data);
    }

    public function update_detail($approval_id, $data)
    {
        $this->db->where('approval_id', $approval_id);
        $this->db->update_batch('approval_detail', $data, 'approval_detail_id');
    }

    public function insert_single_detail($data) {
        $this->db->insert('approval_detail', $data);
        return $this->db->insert_id();
    }

    public function update_single_detail($id, $data) {
        $this->db->where('approval_detail_id', $id);
        return $this->db->update('approval_detail', $data);
    }

    public function delete_single_detail($id) {
        $this->db->where('approval_detail_id', $id);
        return $this->db->delete('approval_detail');
    }

    public function get_approval_items($approval_id){
        $this->db->select('approval_detail.*, roles.name as role_name, users.username as user_name');
        $this->db->from('approval_detail');
        $this->db->join('roles', 'roles.id = approval_detail.approval_role_id', 'left');
        $this->db->join('users', 'users.user_id = approval_detail.approval_user_id', 'left');
        $this->db->where('approval_detail.approval_id', $approval_id);
        $this->db->order_by('approval_detail.approval_sequence', 'ASC');
        return $this->db->get()->result();
    }

    public function get_approval_detail($approval_id){
        $this->db->select('approval.*, menus.name as menu_name, product_status.product_status_name, users.username as created_by_name, updated_user.username as updated_by_name');
        $this->db->from($this->table);
        $this->db->join('product_status', "product_status.product_status_id = approval.approval_status AND product_status.module = 'product'", 'left');
        $this->db->join('users', 'users.user_id = approval.created_by', 'left');
        $this->db->join('users as updated_user', 'updated_user.user_id = approval.updated_by', 'left');
        $this->db->join('menus', 'menus.id = approval.approval_menu', 'left');
        $this->db->where('approval.approval_id', $approval_id);
        
        $header = $this->db->get()->row();

        if (!$header) {
            return null;
        }

        $this->db->select('approval_detail.*, employees.employee_name, positions.position_name, departments.department_name, roles.name as role_name');
        $this->db->from('approval_detail');
        $this->db->join('employees', 'employees.employee_id = approval_detail.approval_user_id', 'left');
        $this->db->join('positions', 'positions.position_id = employees.position_id', 'left');
        $this->db->join('departments', 'departments.department_id = employees.department_id', 'left');
        $this->db->join('roles', 'roles.id = approval_detail.approval_role_id', 'left');
        $this->db->where('approval_detail.approval_id', $approval_id);
        $this->db->order_by('approval_detail.approval_sequence', 'ASC');
        $detail = $this->db->get()->result();

        return (object) [
            'header' => $header,
            'detail' => $detail
        ];
    }

    public function is_user_already_used($approval_id, $user_id, $exclude_detail_id = null) {
        $this->db->where('approval_id', $approval_id);
        $this->db->where('approval_user_id', $user_id);

        if (!empty($exclude_detail_id)) {
            $this->db->where('approval_detail_id !=', $exclude_detail_id);
        }

        $query = $this->db->get('approval_detail');
        return $query->num_rows() > 0;
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
        $this->db->select('employees.employee_id, employees.employee_name , positions.position_name, departments.department_name');
        $this->db->from('employees');
        $this->db->join('positions', 'positions.position_id = employees.position_id', 'left');
        $this->db->join('departments', 'departments.department_id = employees.department_id', 'left');
        $this->db->where('employees.status', 1);
        $this->db->order_by('employees.employee_name', 'ASC');
        return $this->db->get()->result();
    }
}