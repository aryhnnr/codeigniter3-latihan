<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model{
    protected $table = 'roles';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_data(){
        return $this->db->order_by('id', 'ASC')->get($this->table)->result();
    }

    public function get_all_active(){
        return $this->db->where('status', 1)->order_by('name', 'ASC')->get($this->table)->result();
    }

    public function get_by_id($id){
        $this->db->where('id', $id);
        return $this->db->get($this->table)->row();
    }

    public function insert($data){
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data){
        // $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id){
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }


    public function is_slug_exists($slug, $exclude_id = null){
        $this->db->where('slug', $slug);
        if($exclude_id){
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function is_used_in_users($role_id) {
        return $this->db->where('role_id', $role_id)->count_all_results('users');
    }

    public function get_all_menus() {
        return $this->db->order_by('order_no', 'ASC')->order_by('id', 'ASC')->get('menus')->result();
    }

    public function is_unrestricted_role($role) {
        $slug = is_object($role) ? $role->slug : (string) $role;
        $id = is_object($role) && isset($role->id) ? (int) $role->id : 0;

        return in_array(strtolower($slug), ['superadmin', 'admin'], true) || in_array($id, [1, 2], true);
    }

    public function get_permissions_by_role($role_id){
        $rows = $this->db->where('role_id', $role_id)->get('role_menu_permissions')->result();
        $map = [];
        foreach ($rows as $row) {
            $map[$row->menu_id] = $row;
        }
        return $map;
    }

    public function save_permissions($role_id, array $permissions){
        $this->db->trans_start();
 
        $this->db->where('role_id', $role_id)->delete('role_menu_permissions');

        $role = $this->get_by_id($role_id);
        if ($role && $this->is_unrestricted_role($role)) {
            $this->db->trans_complete();
            return $this->db->trans_status();
        }
 
        $insert_rows = [];
        foreach ($permissions as $menu_id => $perm) {
            // $can_view   = !empty($perm['view'])   ? 1 : 0;
            // $can_add    = !emputy($perm['add'])    ? 1 : 0;
            // $can_edit   = !empty($perm['edit'])   ? 1 : 0;
            // $can_delete = !empty($perm['delete']) ? 1 : 0;
 
            // // Baris yang semua izinnya kosong tidak perlu disimpan
            // if (!$can_view && !$can_add && !$can_edit && !$can_delete) {
            //     continue;
            // }

            $can_view   = !empty($perm['view']) ? 1 : 0;
            $can_add    = 1;
            $can_edit   = 1;
            $can_delete = 1;

            if (!$can_view) {
                continue;
            }
 
            $insert_rows[] = [
                'role_id'    => $role_id,
                'menu_id'    => (int) $menu_id,
                'can_view'   => $can_view,
                'can_add'    => $can_add,
                'can_edit'   => $can_edit,
                'can_delete' => $can_delete,
            ];
        }
 
        if (!empty($insert_rows)) {
            $this->db->insert_batch('role_menu_permissions', $insert_rows);
        }
 
        $this->db->trans_complete();
 
        return $this->db->trans_status();
    }
}