<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    protected $table = 'users';

    public function __construct(){
        parent::__construct();
    }

    public function get_all(){
        $this->db->select('users.*, employees.employee_name, employees.employee_code, departments.department_name, positions.position_name');
        $this->db->from($this->table);
        $this->db->join('employees', 'employees.employee_id = users.employee_id', 'left');
        $this->db->join('departments', 'departments.department_id = employees.department_id', 'left');
        $this->db->join('positions', 'positions.position_id = employees.position_id', 'left');
        $this->db->order_by('users.user_id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($user_id){
        $this->db->select('users.*, employees.employee_name, employees.employee_code');
        $this->db->from($this->table);
        $this->db->join('employees', 'employees.employee_id = users.employee_id', 'left');
        $this->db->where('users.user_id', $user_id);
        return $this->db->get()->row();
    }

    public function get_by_employee_id($employee_id){
        return $this->db->get_where($this->table, ['employee_id' => $employee_id])->row();
    }

    public function username_exists($username, $exclude_user_id = null){
        $this->db->where('username', $username);
        if ($exclude_user_id) {
            $this->db->where('user_id !=', $exclude_user_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function email_exists($email, $exclude_user_id = null){
        $this->db->where('email', $email);
        if ($exclude_user_id) {
            $this->db->where('user_id !=', $exclude_user_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function nomor_hp_exists($nomor_hp, $exclude_user_id = null){
        if ($nomor_hp === '') {
            return false;
        }

        $this->db->where('nomor_hp', $nomor_hp);
        if ($exclude_user_id) {
            $this->db->where('user_id !=', $exclude_user_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function insert($data){
        return $this->db->insert($this->table, $data);
    }

    public function update($user_id, $data){
        return $this->db->where('user_id', $user_id)->update($this->table, $data);
    }

    public function toggle_status($user_id){
        $user = $this->get_by_id($user_id);
        if (!$user) return false;
        $new_status = $user->status == 1 ? 0 : 1;
        return $this->db->where('user_id', $user_id)->update($this->table, ['status' => $new_status]);
    }
}
