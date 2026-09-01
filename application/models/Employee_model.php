<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_model extends CI_Model
{
    protected $table = 'employees';

    public function __construct()
    {
        parent::__construct();
    }

    // NO 41
    public function get_data($filter = []){
        $this->db->select('employees.*, departments.department_name, positions.position_name, sub_departments.sub_department_name, roles.name as role_name');
        $this->db->from($this->table);
        $this->db->join('departments', 'employees.department_id = departments.department_id', 'left');
        $this->db->join('positions', 'employees.position_id = positions.position_id', 'left');
        $this->db->join('sub_departments', 'sub_departments.sub_department_id = employees.sub_department_id', 'left');
        $this->db->join('users', 'users.employee_id = employees.employee_id','left');
        $this->db->join('roles','roles.id = users.role_id','left');

        if (isset($filter['status']) && $filter['status'] !== '' && $filter['status'] !== null) {
            $this->db->where('employees.status', $filter['status']);
        }

        if (!empty($filter['department_id'])) {
            $this->db->where('employees.department_id', $filter['department_id']);
        }

        if (!empty($filter['position_id'])) {
            $this->db->where('employees.position_id', $filter['position_id']);
        }

        $this->db->order_by('employees.employee_id', 'DESC');

        $query = $this->db->get();

        return $query->result();
    }

    // NO 42
    public function get_by_id($id){
        $this->db->select('employees.*, departments.department_name, positions.position_name, sub_departments.sub_department_name, roles.name as role_name');
        $this->db->from($this->table);
        $this->db->join('departments', 'employees.department_id = departments.department_id', 'left');
        $this->db->join('positions', 'employees.position_id = positions.position_id', 'left');
        $this->db->join('sub_departments', 'sub_departments.sub_department_id = employees.sub_department_id', 'left');
        $this->db->join('users', 'users.employee_id = employees.employee_id','left');
        $this->db->join('roles','roles.id = users.role_id','left');
        $this->db->where('employees.employee_id', $id);

        $query = $this->db->get();

        return $query->row();
    }
    

    public function insert($data){
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data){
        $this->db->where('employee_id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Insert employee + user account in a single transaction.
     * @param array $emp_data  Employee fields
     * @param array $user_data User fields (must include employee_id after insert)
     * @return array ['success'=>bool, 'employee_id'=>int|null, 'user_id'=>int|null, 'message'=>string]
     */
    public function insert_with_user($emp_data, $user_data) {
        $this->db->trans_begin();

        $this->db->insert($this->table, $emp_data);
        $employee_id = $this->db->insert_id();

        if (!$employee_id) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Gagal menyimpan data employee.'];
        }

        $user_data['employee_id'] = $employee_id;
        $this->db->insert('users', $user_data);
        $user_id = $this->db->insert_id();

        if ($this->db->trans_status() === FALSE || !$user_id) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Gagal menyimpan akun user.'];
        }

        $this->db->trans_commit();
        return ['success' => true, 'employee_id' => $employee_id, 'user_id' => $user_id];
    }

    /**
     * Update employee and optionally upsert the linked user account in a single transaction.
     * @param int   $employee_id
     * @param array $emp_data
     * @param array|null $user_data   null = no user change; array with 'user_id' = update; array without 'user_id' = insert new
     * @return array ['success'=>bool, 'message'=>string]
     */
    public function update_with_user($employee_id, $emp_data, $user_data = null) {
        $this->db->trans_begin();

        $this->db->where('employee_id', $employee_id);
        $this->db->update($this->table, $emp_data);

        if ($user_data !== null) {
            if (!empty($user_data['user_id'])) {
                // Update existing user
                $uid = $user_data['user_id'];
                unset($user_data['user_id']);
                $this->db->where('user_id', $uid);
                $this->db->update('users', $user_data);
            } else {
                // Insert new user linked to this employee
                unset($user_data['user_id']);
                $user_data['employee_id'] = $employee_id;
                $this->db->insert('users', $user_data);
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Terjadi kesalahan, data tidak tersimpan.'];
        }

        $this->db->trans_commit();
        return ['success' => true];
    }


    public function generate_employee_code(){
        $this->db->like('employee_code', 'EMP', 'after');
        $this->db->order_by('employee_id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get($this->table);
        $last = $query->row();


        if($last){
            $last_number = (int) substr($last->employee_code, -3);
            $next_number = $last_number + 1;
        }else{
            $next_number = 1;
        }

        $formatted = str_pad($next_number, 3, '0', STR_PAD_LEFT);

        return 'EMP'. $formatted;
    }

    // NO 43 (https://www.petanikode.com/codeigniter-search/)
    public function search($keyword)
    {
        $this->db->select('employees.*, departments.department_name, positions.position_name');
        $this->db->from($this->table);
        $this->db->join('departments', 'employees.department_id = departments.department_id', 'left');
        $this->db->join('positions', 'employees.position_id = positions.position_id', 'left');
        $this->db->like('employee_name', $keyword);
        $this->db->or_like('employee_code', $keyword);
        $query = $this->db->get();
        return $query->result();
    }

    // NO 44
    public function get_by_department($department_id){
        return $this->db->get_where('departments', array('department_id' => $department_id))->row();
    }

    public function get_departemen(){
        return $this->db->get('departments')->result();
    }

    public function get_position(){
        return $this->db->get('positions')->result();
    }

    // NO 45 (https://stackoverflow.com/questions/28958307/how-to-count-the-number-of-rows-in-a-database-table-with-codeigniter)
    public function count_all(){
        $this->db->select('COUNT(*) AS jumlah_total');
        $query = $this->db->get($this->table);
        $result = $query->row();
        return $result->jumlah_total;
    }

    // NO 46 
    // (https://chatgpt.com/s/t_6a7d291043648191a0ba35d67db135a3)
    // (https://chatgpt.com/s/t_6a7d292db1f48191968dab894d91fbb3)
    public function get_data_filter($filter = []){
        $this->db->select('employees.*, departments.department_name, positions.position_name');
        $this->db->from($this->table);
        $this->db->join('departments', 'employees.department_id = departments.department_id', 'left');
        $this->db->join('positions', 'employees.position_id = positions.position_id', 'left');
        // Filter

        // departemen_id
        if(!empty($filter['department_id'])){
            $this->db->where('employees.department_id', $filter['department_id']);
        }

        // status
        if ($filter['status'] !== '') {
            $this->db->where('employees.status', $filter['status']);
        }

        // Keyword
        if(!empty($filter['keyword'])){
            $this->db->group_start();
            $this->db->like('employees.employee_name', $keyword);
            $this->db->or_like('employees.employee_code', $keyword);
            $this->db->group_end();
        }

        // date_from
        if (!empty($filter['date_from'])) {
            $this->db->where('created_at >=', $filter['date_from']);
        }

        // date_to
        if (!empty($filter['date_to'])) {
            $this->db->where('created_at <=', $filter['date_to']);
        }

        $query = $this->db->get();

        return $query->result();
    }

    // NO 47
    public function get_join_employee_departement(){
        $this->db->select('employees.*, departments.department_name');
        $this->db->from($this->table);
        $this->db->join('departments', 'employees.department_id = departments.department_id', 'left');
        $query = $this->db->get();

        return $query->result();
    }

    // NO 48
    public function get_gropby_employee_departement(){
        $this->db->select('departments.department_name, COUNT(employees.employee_id) AS total_employee');
        $this->db->from($this->table);
        $this->db->join('departments', 'employees.department_id = departments.department_id', 'left');
        $this->db->group_by('departments.department_id');
        $query = $this->db->get();

        return $query->result();
    }

    // NO 49 
    public function get_having()
    {
        $this->db->select('departments.department_name, COUNT(employees.employee_id) AS total_employee');
        $this->db->from($this->table);
        $this->db->join('departments', 'employees.department_id = departments.department_id', 'left');
        $this->db->group_by('departments.department_id');
        $this->db->having('COUNT(employees.employee_id) >', 10);
        $query = $this->db->get();

        return $query->result();
    }

}